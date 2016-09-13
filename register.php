<?php
require 'tng_begin.php';

if (!$personID) {
  die('no args');
}
require $subroot . 'pedconfig.php';
require 'personlib.php';
require 'reglib.php';

if ($tngmore) {
  $pedigree['regnotes'] = 1;
} elseif ($tngless) {
  $pedigree['regnotes'] = 0;
}

$detail_link = "register.php?personID=$personID&amp;generations=$generations";
if ($pedigree['regnotes']) {
  $detail_link = "<a href=\"{$detail_link}&tngless=1\">" . uiTextSnippet('lessdetail') . '</a>';
} else {
  $detail_link = "<a href=\"{$detail_link}&tngmore=1\">" . uiTextSnippet('moredetail') . '</a>';
}
$generation = 1;
$personcount = 1;

$currgen = [];
$nextgen = [];

$result = getPersonFullPlusDates($personID);
if ($result) {
  $row = tng_fetch_assoc($result);
  $rightbranch = checkbranch($row['branch']);
  $rights = determineLivingPrivateRights($row, $rightbranch);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  $row['name'] = getName($row);
  $logname = $tngconfig['nnpriv'] && $row['private'] ? uiTextSnippet('private') : ($nonames && $row['living'] ? uiTextSnippet('living') : $row['name']);
  $row['genlist'] = '';
  $row['trail'] = $personID;
  $row['number'] = 1;
  $row['spouses'] = getSpouses($personID, $row['sex']);
  array_push($currgen, $row);
}
writelog("<a href=\"register.php?personID=$personID\">" . uiTextSnippet('descendfor') . " $logname ($personID)</a>");
preparebookmark("<a href=\"register.php?personID=$personID\">" . uiTextSnippet('descendfor') . " {$row['name']} ($personID)</a>");

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle($row['name']);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();

    $photostr = showSmallPhoto($personID, $row['name'], $rights['both'], 0, false, $row['sex']);
    echo tng_DrawHeading($photostr, $row['name'], getYears($row));

    if (!$pedigree['maxdesc']) {
      $pedigree['maxdesc'] = 12;
    }
    if (!$pedigree['initdescgens']) {
      $pedigree['initdescgens'] = 4;
    }
    if (!$generations) {
      $generations = $pedigree['initdescgens'];
    } else {
      if ($generations > $pedigree['maxdesc']) {
        $generations = $pedigree['maxdesc'];
      } else {
        $generations = intval($generations);
      }
    }
    $innermenu = uiTextSnippet('generations') . ': &nbsp;';
    $innermenu .= "<select name='generations' class='small' onchange=\"window.location.href='register.php?personID=$personID&amp;generations=' + this.options[this.selectedIndex].value\">\n";
    for ($i = 1; $i <= $pedigree['maxdesc']; $i++) {
      $innermenu .= "<option value='$i'";
      if ($i == $generations) {
        $innermenu .= ' selected';
      }
      $innermenu .= ">$i</option>\n";
    }
    $innermenu .= "</select>\n";
    $innermenu .= "<a class='navigation-item' href='descend.php?personID=$personID&amp;display=standard&amp;generations=$generations'>" . uiTextSnippet('pedstandard') . "</a>\n";
    $innermenu .= "<a class='navigation-item' href='descend.php?personID=$personID&amp;display=compact&amp;generations=$generations'>" . uiTextSnippet('pedcompact') . "</a>\n";
    $innermenu .= "<a class='navigation-item' href='descendtext.php?personID=$personID&amp;generations=$generations'>" . uiTextSnippet('pedtextonly') . "</a>\n";
    if ($generations <= 12 && $allow_pdf && $rightbranch) {
      $innermenu .= "<a class='navigation-item' href='#' onclick=\"tnglitbox = new ModalDialog('rpt_pdfform.php?pdftype=desc&amp;personID=$personID&amp;generations=$generations');return false;\">PDF</a>\n";
    }
    beginFormElement('register', 'get', 'form1', 'form1');
    echo buildPersonMenu('descend', $personID);
    echo "<div class='pub-innermenu small'>\n";
      echo $innermenu;
    echo "</div>\n";
    echo "<br>\n";
    endFormElement();
    ?>
    <div class="titleboxmedium">
      <div class="pull-xs-right"><?php echo $detail_link; ?></div>
      <?php
      while (count($currgen) && $generation <= $generations) {
        echo '<h4>' . uiTextSnippet('generation') . ": $generation</h4>\n";
        echo "<ol style='list-style-type:none; padding:0; margin:0;'>\n";
        while ($row = array_shift($currgen)) {
          echo "<li>\n";
          echo "<table>\n";
          echo "<tr>\n";
          echo "<td width='40'>{$row['number']}.</td>\n";
          echo "<td>\n";
          echo showSmallPhoto($row['personID'], $row['name'], $row['allow_living'] && $row['allow_private'], 0, false, $row['sex']);
          echo "<a href=\"peopleShowPerson.php?personID={$row['personID']}\" name=\"p{$row['personID']}\" id=\"p{$row['personID']}\">{$row['name']}</a>";
          if ($row['genlist']) {
            echo " <a href=\"desctracker.php?trail={$row['trail']}\" title=\"" . uiTextSnippet('graphdesc') . "\">\n";
            echo "<img src=\"img/dchart.gif\" width='10' height='9' alt=\"" . uiTextSnippet('graphdesc') . "\">\n";
            echo "</a> ({$row['genlist']})";
          }
          echo getVitalDates($row);
          echo getOtherEvents($row);
          if ($row['allow_living'] && $row['allow_private'] && $pedigree['regnotes']) {
            $notes = buildRegNotes(getRegNotes($row['personID'], 'I'));
            if ($notes) {
              echo '<p>' . uiTextSnippet('notes') . ':<br>';
              echo "<blockquote class=\"blocknote\">\n$notes</blockquote>\n</p>\n";
            }
          } else {
            $notes = '';
          }
          $fname = $row['firstname'];
          $firstfirstname = getFirstNameOnly($row);
          $newlist = $row['number'] . ".<a href='#' onclick=\"$('#p{$row['personID']}').animate({scrollTop: -200},'slow'); return false;\">$firstfirstname</a><sup style=\"font-size:8px;top:-2px\">$generation</sup>";
          if ($row['genlist']) {
            $newlist .= ', ' . $row['genlist'];
          }
          while ($spouserow = array_shift($row['spouses'])) {
            if ($spouserow['marrdate'] || $spouserow['marrplace']) {
              echo "<p>$firstfirstname " . strtolower(uiTextSnippet('wasmarried')) . " <a href=\"peopleShowPerson.php?personID={$spouserow['personID']}\">{$spouserow['name']}</a>";
              echo getSpouseDates($spouserow);
            } else {
              echo "<p>$firstfirstname &mdash; <a href=\"peopleShowPerson.php?personID={$spouserow['personID']}\">{$spouserow['name']}</a>.";
            }
            $spouseinfo = getVitalDates($spouserow);
            $spparents = $spouserow['personID'] ? getSpouseParents($spouserow['personID'], $spouserow['sex']) : uiTextSnippet('unknown');
            if ($spouseinfo) {
              $spname = getName($spouserow);
              $spfirstfirstname = getFirstNameOnly($spouserow);
              echo " $spfirstfirstname $spparents $spouseinfo";
            } else {
              echo " $spparents";
            }
            echo " [<a href=\"familiesShowFamily.php?familyID={$spouserow['familyID']}\">" . uiTextSnippet('groupsheet') . "</a>]</p>\n";

            if ($pedigree['regnotes']) {
              if ($famrights['both']) {
                $notes = buildRegNotes(getRegNotes($spouserow['familyID'], 'F'));
                if ($notes) {
                  echo '<p>' . uiTextSnippet('notes') . ':<br>';
                  echo "<blockquote class=\"blocknote\">\n$notes</blockquote>\n</p>";
                }
              }
            }
            $result2 = getChildrenData($spouserow['familyID']);
            if ($result2 && tng_num_rows($result2)) {
              echo '<table><tr><td>' . uiTextSnippet('children') . ":<br>\n<ol>\n";
              while ($childrow = tng_fetch_assoc($result2)) {
                $childID = $childrow['personID'];
                if ($nextgen[$childID]) {
                  $displaycount = $nextgen[$childID]['number'];
                  $name = $nextgen[$childID]['name'];
                  $vitaldates = getVitalDates($nextgen[$childID]);
                } else {
                  $personcount++;
                  $displaycount = $personcount;
                  $childrow['spouses'] = getSpouses($childID, $childrow['sex']);
                  $childrow['genlist'] = $newlist;
                  $childrow['trail'] = $row['trail'] . ",{$spouserow['familyID']},$childID";
                  $childrow['number'] = $personcount;
                  $crights = determineLivingPrivateRights($childrow);
                  $childrow['allow_living'] = $crights['living'];
                  $childrow['allow_private'] = $crights['private'];
                  $childrow['name'] = $name = getName($childrow);
                  $vitaldates = getVitalDates($childrow);
                  if ($childrow['spouses'] || !$pedigree['regnosp']) {
                    $nextgen[$childID] = $childrow;
                  }
                }
                echo "<li style=\"list-style-type:lower-roman\">$displaycount. <a href='#' onclick=\"if(jQuery('#p$childID').length) {jQuery('html, body').animate({scrollTop: $('#p$childID').offset().top-10},'slow');}else{window.location.href='peopleShowPerson.php?personID=$childID';} return false;\">$name</a> &nbsp;<a href=\"desctracker.php?trail={$childrow['trail']}\"><img src=\"img/dchart.gif\" width='10' height='9' alt=\"" . uiTextSnippet('graphdesc') . "\"></a> $vitaldates</li>\n";
              }
              echo "</ol>\n</td></tr></table>\n";
              tng_free_result($result2);
            }
          }
          echo "</td>\n";
          echo "</tr>\n";
          echo "</table>\n";
          echo "<br clear='all'>\n";
          echo "</li>\n";
        }
        $currgen = $nextgen;
        unset($nextgen);
        $nextgen = [];
        $generation++;
        echo "</ol>\n<br>\n";
      }
      ?>
    </div>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script src="js/rpt_utils.js"></script>
  <script>
    var tnglitbox;
  </script>
</body>
</html>
