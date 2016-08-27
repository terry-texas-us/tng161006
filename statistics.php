<?php

require 'tng_begin.php';

$logstring = "<a href='statistics.php'>" . xmlcharacters(uiTextSnippet('databasestatistics')) . "</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('databasestatistics'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/bar-graph.svg'><?php echo uiTextSnippet('databasestatistics'); ?></h2>
    <table class='table table-sm'>
      <thead>
        <tr>
          <th><?php echo uiTextSnippet('description'); ?></th>
          <th><?php echo uiTextSnippet('quantity'); ?></th>
        </tr>
      </thead>
      <?php
      $query = "SELECT lastimportdate, treename, secret FROM $treesTable";
      $result = tng_query($query);
      $treerow = tng_fetch_array($result, 'assoc');
      $lastimportdate = $treerow['lastimportdate'];

      $query = "SELECT count(id) AS pcount FROM $people_table";
      $result = tng_query($query);
      $row = tng_fetch_assoc($result);
      $totalpeople = $row['pcount'];
      tng_free_result($result);

      $query = "SELECT count(id) AS fcount FROM $families_table";
      $result = tng_query($query);
      $row = tng_fetch_assoc($result);
      $totalfamilies = $row['fcount'];
      tng_free_result($result);

      $query = "SELECT count(DISTINCT ucase(lastname)) AS lncount FROM $people_table";
      $result = tng_query($query);
      $row = tng_fetch_array($result);
      $uniquesurnames = number_format($row['lncount']);
      tng_free_result($result);

      $totalmedia = [];
      foreach ($mediatypes as $mediatype) {
        $mediatypeID = $mediatype['ID'];
        $query = "SELECT count(mediaID) AS mcount FROM $media_table WHERE mediatypeID = '$mediatypeID'";

        $result = tng_query($query);
        $row = tng_fetch_assoc($result);
        $totalmedia[$mediatypeID] = number_format($row['mcount']);
        tng_free_result($result);
      }
      $query = "SELECT count(id) AS scount FROM $sources_table";
      $result = tng_query($query);
      $row = tng_fetch_assoc($result);
      $totalsources = number_format($row['scount']);
      tng_free_result($result);

      $query = "SELECT count(id) AS pcount FROM $people_table WHERE sex = 'M'";
      $result = tng_query($query);
      $row = tng_fetch_assoc($result);
      $males = $row['pcount'];
      tng_free_result($result);

      $query = "SELECT count(id) AS pcount FROM $people_table WHERE sex = 'F'";
      $result = tng_query($query);
      $row = tng_fetch_assoc($result);
      $females = $row['pcount'];
      tng_free_result($result);

      $unknownsex = $totalpeople - $males - $females;

      $query = "SELECT count(id) AS pcount FROM $people_table WHERE living != 0";
      $result = tng_query($query);
      $row = tng_fetch_assoc($result);
      $numliving = number_format($row['pcount']);
      tng_free_result($result);

      $query = "SELECT personID, firstname, lnprefix, lastname, birthdate, gedcom, living, private, branch FROM $people_table WHERE birthdatetr != '0000-00-00' ORDER BY birthdatetr LIMIT 1";
      $result = tng_query($query);
      $firstbirth = tng_fetch_array($result);
      $firstbirthpersonid = $firstbirth['personID'];
      $firstbirthfirstname = $firstbirth['firstname'];
      $firstbirthlnprefix = $firstbirth['lnprefix'];
      $firstbirthlastname = $firstbirth['lastname'];
      $firstbirthdate = $firstbirth['birthdate'];
      $firstbirthgedcom = $firstbirth['gedcom'];

      $rights = determineLivingPrivateRights($firstbirth);
      $firstallowed = $rights['both'];

      tng_free_result($result);

      $query = "SELECT YEAR( deathdatetr ) - YEAR( birthdatetr ) AS yearsold, DAYOFYEAR( deathdatetr ) - DAYOFYEAR( birthdatetr ) AS daysold, IF(DAYOFYEAR(deathdatetr) and DAYOFYEAR(birthdatetr), TO_DAYS(deathdatetr) - TO_DAYS(birthdatetr), (YEAR(deathdatetr) - YEAR(birthdatetr)) * 365) as totaldays FROM $people_table 
        WHERE birthdatetr != '0000-00-00' AND deathdatetr != '0000-00-00' AND birthdate not like 'AFT%' AND deathdate not like 'AFT%' AND birthdate not like 'BEF%' AND deathdate not like 'BEF%' AND birthdate not like 'ABT%' AND deathdate not like 'ABT%' AND birthdate not like 'BET%' AND deathdate not like 'BET%' AND birthdate not like 'CAL%' AND deathdate not like 'CAL%' ORDER BY totaldays DESC";
      $result = tng_query($query);
      $numpeople = tng_num_rows($result);
      $avgyears = 0;
      $avgdays = 0;
      $totyears = 0;
      $totdays = 0;

      while ($line = tng_fetch_array($result, 'assoc')) {
        $yearsold = $line['yearsold'];
        $daysold = $line['daysold'];

        if ($daysold < 0) {
          if ($yearsold > 0) {
            $yearsold--;
            $daysold = 365 + $daysold;
          }
        }
        $totyears += $yearsold;
        $totdays += $daysold;
      }
      $avgyears = $numpeople ? $totyears / $numpeople : 0;

      // convert the remainder from $avgyears to days
      $avgdays = ($avgyears - floor($avgyears)) * 365;

      // add the number of averge days calculated from $totdays
      $avgdays += $numpeople ? $totdays / $numpeople : 0;

      // if $avgdays is more than a year, we've got to adjust things!
      if ($avgdays > 365) {
        // add the number of additional years $avgdaysgives us
        $avgyears += floor($avgdays / 365);

        //change $avgdays to days left after removing multiple
        //years' worth of days.
        $avgdays = $avgdays - (floor($avgdays / 365) * 365);
      }
      $avgyears = floor($avgyears);
      $avgdays = floor($avgdays);

      tng_free_result($result);

      $percentmales = $totalpeople ? round(100 * $males / $totalpeople, 2) : 0;
      $percentfemales = $totalpeople ? round(100 * $females / $totalpeople, 2) : 0;
      $percentunknownsex = $totalpeople ? round(100 * $unknownsex / $totalpeople, 2) : 0;

      $totalpeople = number_format($totalpeople);
      $totalfamilies = number_format($totalfamilies);
      $males = number_format($males);
      $females = number_format($females);
      $unknownsex = number_format($unknownsex);

      echo "<tr><td>" . uiTextSnippet('totindividuals') . "</td>\n";
      echo "<td>$totalpeople</td></tr>\n";

      echo "<tr><td>" . uiTextSnippet('totmales') . "</td>\n";
      echo "<td>$males ($percentmales%)</td></tr>\n";

      echo "<tr><td>" . uiTextSnippet('totfemales') . "</td>\n";
      echo "<td>$females ($percentfemales%)</td></tr>\n";

      echo "<tr><td>" . uiTextSnippet('totunknown') . "</td>\n";
      echo "<td>$unknownsex ($percentunknownsex%)</td></tr>\n";

      echo "<tr><td>" . uiTextSnippet('totliving') . "</td>\n";
      echo "<td>$numliving</td></tr>\n";

      echo "<tr><td>" . uiTextSnippet('totfamilies') . "</td>\n";
      echo "<td>$totalfamilies</td></tr>\n";

      echo "<tr><td>" . uiTextSnippet('totuniquesn') . "</td>\n";
      echo "<td>$uniquesurnames</td></tr>\n";

      foreach ($mediatypes as $mediatype) {
        $mediatypeID = $mediatype['ID'];
        $titlestr = uiTextSnippet($mediatypeID) ? uiTextSnippet($mediatypeID) : $mediatypes_display[$mediatypeID];
        echo "<tr><td>" . uiTextSnippet('total') . " $titlestr</td>\n";
        echo "<td>" . $totalmedia[$mediatypeID] . "</td></tr>\n";
      }

      echo "<tr><td>" . uiTextSnippet('totsources') . "</td>\n";
      echo "<td>$totalsources</td></tr>\n";

      echo "<tr><td>" . uiTextSnippet('avglifespan') . "<sup><font size=\"1\">1</font></sup></td>\n";
      echo "<td>$avgyears " . uiTextSnippet('years') . ", $avgdays " . uiTextSnippet('days') . "</td></tr>\n";

      echo "<tr><td>" . uiTextSnippet('earliestbirth');
      if ($firstallowed) {
        echo " (<a href=\"peopleShowPerson.php?personID=$firstbirthpersonid\">$firstbirthfirstname $firstbirthlnprefix $firstbirthlastname</a>)";
      }
      echo "</td>\n";
      echo "<td>" . displayDate($firstbirthdate) . "</td></tr>\n";

      if ($tngconfig['lastimport'] && $treerow['treename'] && $lastimportdate) {
        echo "<tr><td>" . uiTextSnippet('lastimportdate') . "</td>\n";

        $importtime = strtotime($lastimportdate);
        if (substr($lastimport, 11, 8) != "00:00:00") {
          $importtime += ($timeOffset * 3600);
        }
        $importdate = strftime("%d %b %Y %H:%M:%S", $importtime);

        echo "<td>" . displayDate($importdate) . "</td></tr>\n";
      }
      ?>
    </table>
    <br>
    <table class="table table-sm">
      <thead>
        <tr>
          <th><?php echo uiTextSnippet('longestlived'); ?><sup><font size="1">1</font></sup></th>
          <th><?php echo uiTextSnippet('age'); ?></th>
        </tr>
        </thead>
      <?php
      $query = "SELECT personID, firstname, lnprefix, lastname, gedcom, living, private, branch, YEAR(deathdatetr) - YEAR(birthdatetr) AS yearsold, DAYOFYEAR(deathdatetr) - DAYOFYEAR(birthdatetr) AS daysold, IF(DAYOFYEAR(deathdatetr) and DAYOFYEAR(birthdatetr), TO_DAYS(deathdatetr) - TO_DAYS(birthdatetr), (YEAR(deathdatetr) - YEAR(birthdatetr)) * 365) as totaldays FROM $people_table "
          . "WHERE birthdatetr != '0000-00-00' AND deathdatetr != '0000-00-00' AND birthdate not like 'AFT%' AND deathdate not like 'AFT%' AND birthdate not like 'BEF%' AND deathdate not like 'BEF%' AND birthdate not like 'ABT%' AND deathdate not like 'ABT%' AND birthdate not like 'BET%' AND deathdate not like 'BET%' AND birthdate not like 'CAL%' AND deathdate not like 'CAL%' ORDER BY totaldays DESC LIMIT 10";
      $result = tng_query($query);
      $numpeople = tng_num_rows($result);

      while ($line = tng_fetch_array($result, 'assoc')) {
        $personid = $line['personID'];
        $firstname = $line['firstname'];
        $lnprefix = $line['lnprefix'];
        $lastname = $line['lastname'];
        $yearsold = $line['yearsold'];
        $daysold = $line['daysold'];
        $gedcom = $line['gedcom'];

        $rights = determineLivingPrivateRights($line);
        $allowed = $rights['both'];

        if ($daysold < 0) {
          if ($yearsold > 0) {
            $yearsold--;
            $daysold = 365 + $daysold;
          }
        }
        echo "<tr><td><a href=\"peopleShowPerson.php?personID=$personid\">";
        if ($allowed) {
          echo "$firstname $lnprefix $lastname";
        } elseif ($line['private']) {
          echo uiTextSnippet('private');
        } else {
          echo uiTextSnippet('living');
        }
        echo "</a></td>\n";
        echo "<td>";
        if ($yearsold) {
          echo number_format($yearsold) . " " . uiTextSnippet('years');
        }
        if ($daysold) {
          echo " $daysold " . uiTextSnippet('days');
        }
        echo "</td></tr>\n";
      }
      ?>
    </table>
    <br><br>
    <table class='table'>
      <tr>
        <td><sup><font size='1'>1</font></sup></td>
        <td><?php echo uiTextSnippet('agedisclaimer'); ?></td>
      </tr>
    </table>
    <?php
    tng_free_result($result);

    echo "<br>\n";
    echo "<span><a href='showtree.php'>" . uiTextSnippet('treedetail') . "</a></span>\n";
    echo "<br>\n";

    echo "<br>\n";
    echo $publicFooterSection->build();
    ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>