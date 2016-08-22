<?php
require 'tng_begin.php';
require 'functions.php';

$_SESSION['tng_mediasearch'] = "";

$flags['imgprev'] = true;

if (!$change_cutoff) {
  $change_cutoff = 0;
}
$pastxdays = $change_cutoff ? " " . preg_replace("/xx/", $change_cutoff, uiTextSnippet('pastxdays')) : "";
$whatsnew = 1;

$logstring = "<a href='whatsnew.php?'>" . xmlcharacters(uiTextSnippet('whatsnew') . $pastxdays) . "</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('whatsnew') . " " . $pastxdays);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/megaphone.svg'><?php echo uiTextSnippet('whatsnew') . " " . $pastxdays; ?></h2>
    <br clear='left'>
    <?php

    $header1 = "<table class='table table-sm'>\n";
    $header1 .= "<tr>\n";
    $header1 .= "<th>" . uiTextSnippet('thumb') . "</th>\n";
    $header1 .= "<th>" . uiTextSnippet('description') . "</th>\n";
    $hsheader = "<th>" . uiTextSnippet('cemetery') . "</th>\n";
    $hsheader .= "<th>" . uiTextSnippet('status') . "</th>\n";
    $header2 = "<th>" . uiTextSnippet('indlinked') . "</th>\n";
    $header2 .= "<th width=\"130\">" . uiTextSnippet('lastmodified') . "</th>\n";
    $header2 .= "</tr>\n";
    $footer = "</table>\n";

    if (!$change_limit) {
      $change_limit = 10;
    }
    if ($change_cutoff) {
      $cutoffstr = "TO_DAYS(NOW()) - TO_DAYS(changedate) <= $change_cutoff AND ";
      $famcutoffstr = "TO_DAYS(NOW()) - TO_DAYS($families_table.changedate) <= $change_cutoff AND ";
    } else {
      $cutoffstr = $famcutoffstr = "";
    }
    //check for custom message
    $file = $rootpath . "whatsnew.txt";
    if (file_exists($file)) {
      $contents = file($file);
      foreach ($contents as $line) {
        if (trim($line)) {
          echo "<p>$line</p>";
        }
      }
    }
    foreach ($mediatypes as $mediatype) {
      $mediatypeID = $mediatype['ID'];
      $header = $mediatypeID == "headstones" ? $header1 . $hsheader . $header2 : $header1 . $header2;
      echo doMedia($mediatypeID);
    }
    $allwhere = "";

    $more = getLivingPrivateRestrictions("p", false, false);
    if ($more) {
      $allwhere .= " AND " . $more;
    }
    //select from people where date later than cutoff, order by changedate descending, limit = 10
    $query = "SELECT p.personID, lastname, lnprefix, firstname, birthdate, prefix, suffix, nameorder, living, private, branch, DATE_FORMAT(changedate,'%e %b %Y') as changedatef, changedby, LPAD(SUBSTRING_INDEX(birthdate, ' ', -1),4,'0') as birthyear, birthplace, altbirthdate, LPAD(SUBSTRING_INDEX(altbirthdate, ' ', -1),4,'0') as altbirthyear, altbirthplace, p.gedcom as gedcom, treename
      FROM $people_table as p, $treesTable WHERE $cutoffstr 1=1 $allwhere
      ORDER BY changedate DESC, lastname, firstname, birthyear, altbirthyear LIMIT $change_limit";
    $result = tng_query($query);
    if (tng_num_rows($result)) {
      ?>
      <div>
        <h4><?php echo uiTextSnippet('individuals'); ?></h4>
        <table class='table table-sm table-striped'>
          <thead>
            <tr>
              <th><?php echo uiTextSnippet('id'); ?></th>
              <th><?php echo uiTextSnippet('lastfirst'); ?></th>
              <th colspan='2'><?php echo($tngconfig['hidechr'] ? uiTextSnippet('born') : uiTextSnippet('bornchr')); ?></th>
              <th><?php echo uiTextSnippet('lastmodified'); ?></th>
            </tr>
          </thead>
          <?php
          $chartlinkimg = getimagesize("img/Chart.gif");
          $chartlink = "<img src='img/Chart.gif' alt='' $chartlinkimg[3]>";
          while ($row = tng_fetch_assoc($result)) {
            $rights = determineLivingPrivateRights($row);
            $row['allow_living'] = $rights['living'];
            $row['allow_private'] = $rights['private'];
            $namestr = getNameRev($row);
            $birthplacestr = "";
            if ($rights['both']) {
              if ($row['birthdate'] || $row['birthplace']) {
                $birthdate = uiTextSnippet('birthabbr') . " " . displayDate($row['birthdate']);
                $birthplace = $row['birthplace'];
              } else {
                if ($row['altbirthdate'] || $row['altbirthplace']) {
                  $birthdate = uiTextSnippet('chrabbr') . " " . displayDate($row['altbirthdate']);
                  $birthplace = $row['altbirthplace'];
                } else {
                  $birthdate = "";
                  $birthplace = "";
                }
              }
            } else {
              $birthdate = $birthplace = "";
            }
            if ($birthplace) {
              $birthplacestr = $birthplace . " <a href=\"placesearch.php?";
              $birthplacestr .= "psearch=" . urlencode($birthplace) . "\"><img class='icon-xs-inline' src='svg/magnifying-glass.svg' alt=''></a>";
            }
            echo "<tr>\n";
              echo "<td><a href=\"peopleShowPerson.php?personID={$row['personID']}\">{$row['personID']}</a></td>";
            echo "<td>\n";
              echo "<div class='person-img' id=\"mi{$row['gedcom']}_{$row['personID']}\">\n";
                echo "<div class='person-prev' id=\"prev{$row['gedcom']}_{$row['personID']}\"></div>\n";
              echo "</div>\n";
              echo "<a href=\"pedigree.php?personID={$row['personID']}\">$chartlink</a>\n";
              echo "<a href=\"peopleShowPerson.php?personID={$row['personID']}\" class='pers' id=\"p{$row['personID']}_t{$row['gedcom']}\">$namestr</a>\n";
            echo "</td>\n";
            echo "<td>$birthdate</td><td>$birthplacestr</td>";
            echo "<td>" . displayDate($row['changedatef']) . ($currentuser ? " ({$row['changedby']})" : "") . "</td></tr>\n";
          }
          tng_free_result($result);
          ?>
        </table>
      </div>
      <?php
    }
    //select husband, wife from families where date later than cutoff, order by changedate descending, limit = 10
    $allwhere = "1=1";

    $more = getLivingPrivateRestrictions($families_table, false, false);
    if ($more) {
      $allwhere .= " AND " . $more;
    }
    $query = "SELECT familyID, husband, wife, marrdate, $families_table.gedcom as gedcom, firstname, lnprefix, lastname, prefix, suffix, nameorder,
        $families_table.living as fliving, $families_table.private as fprivate, $people_table.living as living, $people_table.private as private,
        $people_table.branch as branch, $families_table.gedcom as gedcom, $families_table.branch as fbranch, DATE_FORMAT($families_table.changedate,'%e %b %Y') as changedatef,
        $families_table.changedby, treename
      FROM ($families_table, $treesTable)
      LEFT JOIN $people_table ON $people_table.gedcom = $families_table.gedcom AND $people_table.personID = husband
      WHERE $famcutoffstr $allwhere
      ORDER BY $families_table.changedate DESC, lastname LIMIT $change_limit";
    $famresult = tng_query($query);
    if (tng_num_rows($famresult)) {
    ?>
      <div>
        <h4><?php echo uiTextSnippet('families'); ?></h4>
        <table class="table table-sm table-striped">
          <thead>
            <tr>
              <th><?php echo uiTextSnippet('id'); ?></th>
              <th><?php echo uiTextSnippet('husbid'); ?></th>
              <th><?php echo uiTextSnippet('husbname'); ?></th>
              <th><?php echo uiTextSnippet('wifeid'); ?></th>
              <th><?php echo uiTextSnippet('married'); ?></th>
              <th><?php echo uiTextSnippet('lastmodified'); ?></th>
            </tr>
          </thead>
          <?php
          while ($row = tng_fetch_assoc($famresult)) {
            $rights = determineLivingPrivateRights($row);
            $row['allow_living'] = $rights['living'];
            $row['allow_private'] = $rights['private'];
            $name = getName($row);
            //look up wife
            echo "<tr>\n";
              echo "<td>\n";
                echo "<a href=\"familiesShowFamily.php?familyID={$row['familyID']}\">{$row['familyID']}</a>\n";
              echo "</td>\n";
              echo "<td>\n";
                echo "<a href=\"peopleShowPerson.php?personID={$row['husband']}\">{$row['husband']}</a>\n";
              echo "</td>\n";
            echo "<td>\n";
              echo "<a href=\"peopleShowPerson.php?personID={$row['husband']}\">$name</a>\n";
            echo "</td>\n";
            echo "<td><a href=\"peopleShowPerson.php?personID={$row['wife']}\">{$row['wife']}</a></td>\n";
            echo "<td>";
            if ($rights['both']) {
              $row['branch'] = $row['fbranch'];
              $row['living'] = $row['fliving'];
              $row['private'] = $row['fprivate'];
              $rights = determineLivingPrivateRights($row);
              $row['allow_living'] = $rights['living'];
              $row['allow_private'] = $rights['private'];
              if ($rights['both']) {
                echo displayDate($row['marrdate']);
              }
            }
            echo "</td>\n";
            echo "<td>" . displayDate($row['changedatef']) . ($currentuser ? " ({$row['changedby']})" : "") . "</td></tr>\n";
          }
          tng_free_result($famresult);
          ?>
        </table>
      </div>
    <?php } ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script src="js/search.js"></script>
</body>
</html>
