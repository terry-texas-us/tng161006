<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

require 'adminlog.php';

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('secondarymaint'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
<body>
  <?php
  echo $adminHeaderSection->build('datamaint-secondarymaint-' . $secaction, $message);
  $navList = new navList('');
  $navList->appendItem([true, 'dataImportGedcom.php', uiTextSnippet('import'), 'import']);
  $navList->appendItem([true, 'dataExportGedcom.php', uiTextSnippet('export'), 'export']);
  //  $navList->appendItem([true, 'dataSecondaryProcesses.php', uiTextSnippet('secondarymaint'), 'second']);
  echo $navList->build('second');
  ?>
  <div>
    <div>

      <?php
      set_time_limit(0);
      if ($secaction == uiTextSnippet('sortchildren')) {
        echo '<p>' . uiTextSnippet('sortingchildren') . '</p>';
        echo uiTextSnippet('families') . ":<br>\n";
        $fcount = 0;
        $query = 'SELECT familyID FROM families';
        $result = tng_query($query);
        while ($family = tng_fetch_assoc($result)) {
          $query = "SELECT children.ID AS ID, IF(birthdatetr !='0000-00-00', birthdatetr, altbirthdatetr) AS birth FROM children, people WHERE children.familyID = '{$family['familyID']}' AND people.personID = children.personID ORDER BY birth, ordernum";
          $fresult = tng_query($query);
          $order = 0;
          while ($child = tng_fetch_assoc($fresult)) {
            $order++;
            $query = "UPDATE children SET ordernum=\"$order\" WHERE ID=\"{$child['ID']}\"";
            $cresult = tng_query($query);
          }
          $fcount++;
          if ($fcount % 100 == 0) {
            echo "<strong>$fcount</strong> ";
          }
          tng_free_result($fresult);
        }
        tng_free_result($result);
        echo '<br><br>' . uiTextSnippet('finishedsortingchildren') . '<br>';
      } elseif ($secaction == uiTextSnippet('sortspouses')) {

        echo '<p>' . uiTextSnippet('sortingspouses') . '</p>';
        echo uiTextSnippet('people') . ":<br>\n";
        $fcount = 0;
        //first do husbands
        $query = 'SELECT personID FROM families, people WHERE people.personID = families.husband';
        $result = tng_query($query);
        while ($husband = tng_fetch_assoc($result)) {
          $query = "SELECT ID FROM families WHERE husband = '{$husband['personID']}' ORDER BY marrdatetr, husborder";
          $fresult = tng_query($query);
          $order = 0;
          while ($spouse = tng_fetch_assoc($fresult)) {
            $order++;
            $query = "UPDATE families SET husborder=\"$order\" WHERE ID=\"{$spouse['ID']}\"";
            $cresult = tng_query($query);
          }
          $fcount++;
          if ($fcount % 100 == 0) {
            echo "<strong>$fcount</strong> ";
          }
          tng_free_result($fresult);
        }
        tng_free_result($result);

        //now do wives
        $query = 'SELECT personID FROM families, people WHERE people.personID = families.wife';
        $result = tng_query($query);
        while ($wife = tng_fetch_assoc($result)) {
          $query = "SELECT ID FROM families WHERE wife = '{$wife['personID']}' ORDER BY marrdatetr, wifeorder";
          $fresult = tng_query($query);
          $order = 0;
          while ($spouse = tng_fetch_assoc($fresult)) {
            $order++;
            $query = "UPDATE families SET wifeorder=\"$order\" WHERE ID=\"{$spouse['ID']}\"";
            $cresult = tng_query($query);
          }
          $fcount++;
          if ($fcount % 100 == 0) {
            echo "<strong>$fcount</strong> ";
          }
          tng_free_result($fresult);
        }
        tng_free_result($result);
        echo '<br><br>' . uiTextSnippet('finishedsortingspouses') . '<br>';
      } elseif ($secaction == uiTextSnippet('creategendex')) {

        //create gendex file

        function getVitals($person) {
          if ($person['birthdate']) {
            $info = $person['birthdate'] . '|' . $person['birthplace'] . '|';
          } else {
            $info = $person['altbirthdate'] . '|' . $person['altbirthplace'] . '|';
          }
          if ($person['deathdate']) {
            $info .= $person['deathdate'] . '|' . $person['deathplace'] . '|';
          } else {
            $info .= $person['burialdate'] . '|' . $person['burialplace'] . '|';
          }
          return $info;
        }
        
        echo '<p>' . uiTextSnippet('creatinggendex') . '</p>';
        $gendexout = "$rootpath$gendexfile/gendex.txt";
        $gendexURL = "$tngdomain/$gendexfile/gendex.txt";

        $query = 'SELECT personID, firstname, lnprefix, lastname, living, private, birthdate, birthplace, altbirthdate, altbirthplace, deathdate, deathplace, burialdate, burialplace FROM people ORDER BY lastname, firstname';
        $result = tng_query($query);
        if ($result) {
          //open file (overwrite any contents)
          $fp2 = fopen($gendexout, 'w');
          if (!$fp2) {
            die(uiTextSnippet('cannotopen') . " $gendexout");
          }

          flock($fp2, LOCK_EX);
          $tcount = 0;
          while ($person = tng_fetch_assoc($result)) {
            if (!$person['private'] && (!$person['living'] || $nonames != 1)) {
              $uclast = tng_strtoupper(trim($person['lnprefix'] . ' ' . $person['lastname']));
              $person['lastname'] = $uclast;
              $info = $person['living'] ? '||||' : getVitals($person);
              if ($person['living'] && $nonames == 2) {
                $line = $person['personID'] . "&tree=master|$uclast|" . initials($person['firstname']) . " /$uclast/|$info\n";
              } else {
                $line = $person['personID'] . "&tree=master|$uclast|{$person['firstname']} /$uclast/|$info\n";
              }
              if ($sessionCharset == 'UTF-8') {
                $line = utf8_decode($line);
              }
              fwrite($fp2, "$line");

              $tcount++;
              if ($tcount % 100 == 0) {
                echo "<strong>$tcount</strong> ";
              }
            }
          }
          flock($fp2, LOCK_UN);
          fclose($fp2);
        }
        tng_free_result($result);
        ?>
        <br><br>
        <?php
        echo '<p>' . uiTextSnippet('finishedgendex') . "<br>\n";
        echo uiTextSnippet('filename') . ": $gendexURL</p>\n";
        ?>
        <p><?php echo uiTextSnippet('postgdx'); ?>:<br>
          &raquo; <a href="http://www.gendexnetwork.org" target="_blank">GenDexNetwork</a><br>
          &raquo; <a href="http://www.familytreeseeker.com" target="_blank">FamilyTreeSeeker.com</a>
        </p>
        <?php
      } elseif ($secaction == uiTextSnippet('tracklines')) {
        echo '<p>' . uiTextSnippet('trackinglines') . '</p>';
        echo uiTextSnippet('families') . ":<br>\n";

        $query = 'UPDATE children SET haskids = 0';
        $result2 = tng_query($query);

        $fcount = 0;
        $query = 'SELECT distinct (families.familyID), husband, wife FROM (children, families) WHERE families.familyID = children.familyID';
        $result = tng_query($query);
        while ($family = tng_fetch_assoc($result)) {
          if ($family['husband'] != '') {
            $query = "UPDATE children SET haskids = 1 WHERE personID = '{$family['husband']}'";
            $result2 = tng_query($query);
          }
          if ($family['wife'] != '') {
            $query = "UPDATE children SET haskids = 1 WHERE personID = '{$family['wife']}'";
            $result2 = tng_query($query);
          }
          $fcount++;
          if ($fcount % 100 == 0) {
            echo "<strong>$fcount</strong> ";
          }
        }
        tng_free_result($result);
        echo '<br><br>' . uiTextSnippet('finishedtracking') . '<br>';
      } elseif ($secaction == uiTextSnippet('relabelbranches')) {
        echo '<p>' . uiTextSnippet('relabeling') . '</p>';
        $query = 'SELECT branch, persfamID FROM branchlinks';
        $result = tng_query($query);
        while ($branch = tng_fetch_assoc($result)) {
          $success = 0;
          if (substr($branch['persfamID'], 0, 1) != 'F') {
            $query = "SELECT branch FROM people WHERE personID = \"{$branch['persfamID']}\"";
            $result2 = tng_query($query);
            if (tng_num_rows($result2)) {
              $row = tng_fetch_assoc($result2);
              $oldbranches = explode(',', $row['branch']);
              if ($row['branch']) {
                if (in_array($branch['branch'], $oldbranches)) {
                  $label = $row['branch'];
                } else {
                  $label = $row['branch'] . ',' . $branch['branch'];
                }
              } else {
                $label = $branch['branch'];
              }
              $query = "UPDATE people SET branch = \"$label\" WHERE personID = \"{$branch['persfamID']}\"";
              $result3 = tng_query($query);
              $success = 1;
            }
            tng_free_result($result2);
          }
          if (!$success) {
            $query = "SELECT branch FROM families WHERE familyID = \"{$branch['persfamID']}\"";
            $result2 = tng_query($query);
            if (tng_num_rows($result2)) {
              $row = tng_fetch_assoc($result2);
              $oldbranches = explode(',', $row['branch']);
              if ($row['branch']) {
                if (in_array($branch['branch'], $oldbranches)) {
                  $label = $row['branch'];
                } else {
                  $label = $row['branch'] . ',' . $branch['branch'];
                }
              } else {
                $label = $branch['branch'];
              }
              $query = "UPDATE families SET branch = \"$label\" WHERE familyID = \"{$branch['persfamID']}\"";
              $result3 = tng_query($query);
              $success = 1;
            }
            tng_free_result($result2);
          }
          if ($success) {
            $fcount++;
            if ($fcount % 100 == 0) {
              echo "<strong>$fcount</strong> ";
            }
          }
        }
        tng_free_result($result);
      } elseif ($secaction == uiTextSnippet('evalmedia')) {
        echo '<p>' . uiTextSnippet('evaluating') . '...</p>';
        //loop through each media type
        $query = 'SELECT * FROM mediatypes ORDER BY ordernum, display';
        $result = tng_query($query);

        while ($row = tng_fetch_assoc($result)) {
          $query2 = "SELECT count(*) AS counter FROM media WHERE mediatypeID = \"{$row['mediatypeID']}\"";
          $result2 = tng_query($query2);
          $row2 = tng_fetch_assoc($result2);
          $display = $row['display'] ? $row['display'] : uiTextSnippet($row['mediatypeID']);
          echo "$display: " . number_format($row2['counter']);
          if (!$row2['counter']) {
            echo ' ... ' . uiTextSnippet('disabled');
            $disabled = 1;
          } else {
            $disabled = 0;
          }
          $query3 = "UPDATE mediatypes SET disabled=\"$disabled\" WHERE mediatypeID=\"{$row['mediatypeID']}\"";
          $result3 = tng_query($query3);
          echo "<br>\n";
          tng_free_result($result2);
        }
        tng_free_result($result);
        echo '<br><br>' . uiTextSnippet('finished') . '<br>';
      }

      adminwritelog(uiTextSnippet('secondary') . ": $secaction");
      ?>

      <p>&raquo; <a href="dataSecondaryProcesses.php"><?php echo uiTextSnippet('backtosecondary'); ?></a></p>

    </div>
  </div>

  <?php
  echo $adminFooterSection->build();
  echo scriptsManager::buildScriptElements($flags, 'admin');
  ?>
</body>
</html>
