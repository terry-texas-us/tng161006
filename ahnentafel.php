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
$detailUrl = "ahnentafel.php?personID=$personID&amp;parentset=$parentset&amp;generations=$generations";
if ($pedigree['regnotes']) {
  $detailUrl = "<a href='{$detailUrl}&amp;tngless=1'>" . uiTextSnippet('lessdetail') . '</a>';
} else {
  $detailUrl = "<a href='{$detailUrl}&amp;tngmore=1'>" . uiTextSnippet('moredetail') . '</a>';
}
$generation = 1;
$personcount = 1;
$ordinalNumbers = explode(',', uiTextSnippet('ordinalnumbers'));

$currgen = [];
$nextgen = [];
$numbers = [];
$lastgen = [];
$lastlastgen = [];

$result = getPersonFullPlusDates($personID);
if (!tng_num_rows($result)) {
  tng_free_result($result);
  header('Location: thispagedoesnotexist.html');
  exit;
}
$row = tng_fetch_assoc($result);
tng_free_result($result);

$rightbranch = checkbranch($row['branch']);
$rights = determineLivingPrivateRights($row, $rightbranch);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];
$row['name'] = getName($row);

$firstfirstname = getFirstNameOnly($row);

$logname = $tngconfig['nnpriv'] && $row['private'] ? uiTextSnippet('private') : ($nonames && $row['living'] ? uiTextSnippet('living') : $row['name']);
$row['genlist'] = '';
$row['number'] = 1;
$row['spouses'] = getSpouses($personID, $row['sex']);
$lastlastgen[$personID] = 1;

writelog("<a href=\"ahnentafel.php?personID=$personID\">" . xmlcharacters(uiTextSnippet('ahnentafel') . ": $logname ($personID)") . '</a>');
preparebookmark("<a href=\"ahnentafel.php?personID=$personID\">" . xmlcharacters(uiTextSnippet('ahnentafel') . ': ' . $row['name'] . " ($personID)") . '</a>');

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle($row['name']);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='ahnentafel'>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();

    $photostr = showSmallPhoto($personID, $row['name'], $rights['both'], 0, false, $row['sex']);
    echo tng_DrawHeading($photostr, $row['name'], getYears($row));

    if (!$pedigree['maxgen']) {
      $pedigree['maxgen'] = 6;
    }
    if ($generations > $pedigree['maxgen']) {
      $generations = intval($pedigree['maxgen']);
    } elseif (!$generations) {
      $generations = ($pedigree['initpedgens'] >= 2) ? intval($pedigree['initpedgens']) : 2;
    } else {
      $generations = intval($generations);
    }
    $innermenu = uiTextSnippet('generations') . ': &nbsp;';
    $innermenu .= "<select name='generations' class='small' onchange=\"window.location.href='ahnentafel.php?personID=$personID&amp;parentset=$parentset&amp;generations=' + this.options[this.selectedIndex].value\">\n";
    for ($i = 1; $i <= $pedigree['maxgen']; $i++) {
      $innermenu .= "<option value='$i'";
      if ($i == $generations) {
        $innermenu .= ' selected';
      }
      $innermenu .= ">$i</option>\n";
    }
    $innermenu .= "</select>\n";
    $innermenu .= "<a class='navigation-item' href='pedigree.php?personID=$personID&amp;parentset=$parentset&amp;display=standard&amp;generations=$generations' id='stdpedlnk'>" . uiTextSnippet('pedstandard') . "</a>\n";
    $innermenu .= "<a class='navigation-item' href='verticalchart.php?personID=$personID&amp;parentset=$parentset&amp;display=vertical&amp;generations=$generations' id='pedchartlnk'>" . uiTextSnippet('pedvertical') . "</a>\n";
    $innermenu .= "<a class='navigation-item' href='pedigree.php?personID=$personID&amp;parentset=$parentset&amp;display=compact&amp;generations=$generations' id='compedlnk'>" . uiTextSnippet('pedcompact') . "</a>\n";
    $innermenu .= "<a class='navigation-item' href='pedigree.php?personID=$personID&amp;parentset=$parentset&amp;display=box&amp;generations=$generations' id='boxpedlnk'>" . uiTextSnippet('pedbox') . "</a>\n";
    $innermenu .= "<a class='navigation-item' href='pedigreetext.php?personID=$personID&amp;parentset=$parentset&amp;generations=$generations'>" . uiTextSnippet('pedtextonly') . "</a>\n";
    $innermenu .= "<a class='navigation-item' href='extrastree.php?personID=$personID&amp;parentset=$parentset&amp;showall=1&amp;generations=$generations'>" . uiTextSnippet('media') . "</a>\n";
    if ($generations <= 6 && $allowPdf && $rightbranch) {
      $innermenu .= "<a class='navigation-item' href='#' onclick=\"tnglitbox = new ModalDialog('rpt_pdfform.php?pdftype=ped&amp;personID=$personID&amp;generations=$generations');return false;\">PDF</a>\n";
    }
    beginFormElement('pedigree', '', 'form1', 'form1');
    echo buildPersonMenu('pedigree', $personID);
    echo "<div class='pub-innermenu small'>\n";
      echo $innermenu;
    echo "</div>\n";
    echo "<br>\n";
    endFormElement();
    ?>
    <div class="titleboxmedium">
      <div class="pull-xs-right"><?php echo $detailUrl; ?></div>
      <?php
      //do self

      echo '<h4>' . ucfirst($ordinalNumbers[1]) . ' ' . uiTextSnippet('generation') . "</h4>\n";
      echo "<ol class='self'>";
      echo '<li>';
      echo "<table><tr data-sosa-id='{$personcount}'>";
      echo "<td width='40'>$personcount.&nbsp;&nbsp;</td>";
      echo '<td>';
      echo showSmallPhoto($row['personID'], $row['name'], $rights['both'], 0);
      echo "<a href=\"peopleShowPerson.php?personID={$row['personID']}\" name=\"p{$row['personID']}\" id=\"p{$row['personID']}\">{$row['name']}</a>";
      echo getVitalDates($row, 1);
      echo getOtherEvents($row);
      if ($rights['both'] && $pedigree['regnotes']) {
        $notes = buildRegNotes(getRegNotes($row['personID'], 'I'));
        if ($notes) {
          echo '<p>' . uiTextSnippet('notes') . ':<br>';
          echo "<blockquote class=\"blocknote\">\n$notes</blockquote>\n</p>\n";
        }
      } else {
        $notes = '';
      }

      //do spouse
      while ($spouserow = array_shift($row['spouses'])) {

        if ($spouserow['marrdate'] || $spouserow['marrplace']) {
          echo "<p>$firstfirstname " . strtolower(uiTextSnippet('wasmarried')) . " <a href=\"peopleShowPerson.php?personID={$spouserow['personID']}\">{$spouserow['name']}</a>";
          echo getSpouseDates($spouserow);
        } else {
          echo "<p>$firstfirstname &mdash; <a href=\"peopleShowPerson.php?personID={$spouserow['personID']}\">{$spouserow['name']}</a>.";
        }
        $spouseinfo = getVitalDates($spouserow);
        if ($spouseinfo) {
          $spfirstfirstname = getFirstNameOnly($spouserow);
          $spparents = getSpouseParents($spouserow['personID'], $spouserow['sex']);
          echo " $spfirstfirstname $spparents $spouseinfo";
        }
        echo " [<a href=\"familiesShowFamily.php?familyID={$spouserow['familyID']}\">" . uiTextSnippet('groupsheet') . '</a>]';
        echo "</p>\n";

        if ($pedigree['regnotes']) {
          $famrights = determineLivingPrivateRights($spouserow);
          if ($famrights['both']) {
            $notes = buildRegNotes(getRegNotes($spouserow['familyID'], 'F'));
            if ($notes) {
              echo '<p>' . uiTextSnippet('notes') . ':<br>';
              echo "<blockquote class=\"blocknote\">\n$notes</blockquote>\n</p>\n";
            }
          }
        }

        $result2 = getChildrenData($spouserow['familyID']);
        if ($result2 && tng_num_rows($result2)) {
          echo uiTextSnippet('children') . ":\n";
          echo "<ol class='children'>\n";
          while ($childrow = tng_fetch_assoc($result2)) {
            $childrow['genlist'] = $newlist;
            $crights = determineLivingPrivateRights($childrow);
            $childrow['allow_living'] = $crights['living'];
            $childrow['allow_private'] = $crights['private'];
            $childrow['name'] = getName($childrow);
            if ($childrow['name'] == uiTextSnippet('living')) {
              $childrow['firstname'] = uiTextSnippet('living');
            }

            echo "<li><a href=\"peopleShowPerson.php?personID={$childrow['personID']}\">{$childrow['name']}</a>";
            echo getVitalDates($childrow);
            echo "</li>\n";
          }
          echo "</ol>\n";
          tng_free_result($result2);
        }
      }
      echo '</td></tr></table>';
      echo "<br clear='all'>";
      echo '</li>';
      echo "</ol>\n";

      //push famc (family of parents) to nextgen
      $parentfamID = '';
      $locparentset = $parentset;
      $parentscount = 0;
      $parentfamIDs = [];
      $parents = getChildFamily($personID, 'parentorder');
      if ($parents) {
        $parentscount = tng_num_rows($parents);
        if ($parentscount > 0) {
          if ($locparentset > $parentscount) {
            $locparentset = $parentscount;
          }
          $i = 0;
          while ($parentrow = tng_fetch_assoc($parents)) {
            $i++;
            if ($i == $locparentset) {
              $parentfamID = $parentrow['familyID'];
            }
            $parentfamIDs[$i] = $parentrow['familyID'];
          }
          if (!$parentfamID) {
            $parentfamID = $row['famc'];
          }
        }
        tng_free_result($parents);
      }

      array_push($currgen, $parentfamID);
      $generation++;
      $personcount = 1;
      $numbers[$parentfamID] = 1;

      //loop through nextgen
      //while there's one to pop and we're less than maxgen
      while (count($currgen) && $generation <= $generations) {
        echo '<h4>' . ucfirst($ordinalNumbers[$generation]) . ' ' . uiTextSnippet('generation') . "</h4>\n";
        echo "<ol class='nextgen'>";
        while ($nextfamily = array_shift($currgen)) {
          $parents = getFamilyData($nextfamily);
          if ($parents) {
            $parentrow = tng_fetch_assoc($parents);

            $famrights = determineLivingPrivateRights($parentrow);
            $parentrow['allow_living'] = $famrights['living'];
            $parentrow['allow_private'] = $famrights['private'];

            if ($parentrow['husband']) {
              $gotfather = getPersonData($parentrow['husband']);

              if ($gotfather) {
                $fathrow = tng_fetch_assoc($gotfather);
                if ($fathrow['firstname'] || $fathrow['lastname']) {
                  $personcount = $numbers[$nextfamily] * 2;
                  $lastgen[$fathrow['personID']] = $personcount;
                  $frights = determineLivingPrivateRights($fathrow);
                  $fathrow['allow_living'] = $frights['living'];
                  $fathrow['allow_private'] = $frights['private'];
                  $fathrow['name'] = getName($fathrow);
                  if ($fathrow['name'] == uiTextSnippet('living')) {
                    $fathrow['firstname'] = uiTextSnippet('living');
                  }

                  echo '<li>';
                  echo "<table><tr data-sosa-id='{$personcount}'>";
                  echo "<td width='40'>";
                  echo "$personcount.&nbsp;&nbsp;</td><td>";
                  echo showSmallPhoto($fathrow['personID'], $fathrow['name'], $frights['both'], 0);
                  echo "<a href=\"peopleShowPerson.php?personID={$fathrow['personID']}\" name=\"p{$fathrow['personID']}\" id=\"p{$fathrow['personID']}\">{$fathrow['name']}</a>";
                  echo getVitalDates($fathrow, 1);
                  echo getOtherEvents($fathrow);
                  if ($frights['both'] && $pedigree['regnotes']) {
                    $notes = buildRegNotes(getRegNotes($fathrow['personID'], 'I'));
                    if ($notes) {
                      echo '<p>' . uiTextSnippet('notes') . ':<br>';
                      echo "<blockquote class=\"blocknote\">\n$notes</blockquote>\n</p>\n";
                    }
                  } else {
                    $notes = '';
                  }
                  if ($fathrow['famc']) {
                    if (!in_array($fathrow['famc'], $nextgen)) {
                      array_push($nextgen, $fathrow['famc']);
                    }
                    if (!$numbers[$fathrow['famc']]) {
                      $numbers[$fathrow['famc']] = $personcount;
                    }
                  }
                }
                tng_free_result($gotfather);
              }
            }

            if ($parentrow['wife']) {
              $gotmother = getPersonData($parentrow['wife']);

              if ($gotmother) {
                $mothrow = tng_fetch_assoc($gotmother);
                if ($mothrow['firstname'] || $mothrow['lastname']) {
                  $personcount = $numbers[$nextfamily] * 2 + 1;
                  $lastgen[$mothrow['personID']] = $personcount;
                  $mrights = determineLivingPrivateRights($mothrow);
                  $mothrow['allow_living'] = $mrights['living'];
                  $mothrow['allow_private'] = $mrights['private'];
                  $mothrow['name'] = getName($mothrow);
                  if ($mothrow['name'] == uiTextSnippet('living')) {
                    $mothrow['firstname'] = uiTextSnippet('living');
                  }

                  if ($parentrow['husband']) {
                    $firstfirstname = getFirstNameOnly($fathrow);
                    $parentrow['both'] = $mothrow['both'];
                    if ($parentrow['marrdate'] || $parentrow['marrplace']) {
                      echo "<p>$firstfirstname " . strtolower(uiTextSnippet('wasmarried')) . " <a href='#' onclick=\"$('html, body').animate({scrollTop: $('#p{$parentrow['wife']}').offset().top-10},'slow'); return false;\">{$mothrow['name']}</a>";
                      echo getSpouseDates($parentrow);
                    } else {
                      echo "<p>$firstfirstname &mdash; <a href='#' onclick=\"$('html, body').animate({scrollTop: $('#p{$parentrow['wife']}').offset().top-10},'slow'); return false;\">{$mothrow['name']}</a>.";
                    }
                    $spouseinfo = getVitalDates($mothrow);
                    if ($spouseinfo) {
                      $spfirstfirstname = getFirstNameOnly($mothrow);
                      $spparents = getSpouseParents($mothrow['personID'], $mothrow['sex']);
                      echo " $spfirstfirstname $spparents $spouseinfo";
                    }
                    echo " [<a href=\"familiesShowFamily.php?familyID=$nextfamily\">" . uiTextSnippet('groupsheet') . "</a>]</p>\n";
                    echo '</td></tr></table>';
                    echo "<br clear='all'></li>\n";
                  }
                  echo '<li>';
                  echo "<table><tr data-sosa-id='{$personcount}'>";
                  echo "<td width='40'>";
                  echo "$personcount.&nbsp;&nbsp;</td><td>";
                  echo showSmallPhoto($mothrow['personID'], $mothrow['name'], $mrights['both'], 0);
                  echo "<a href=\"peopleShowPerson.php?personID={$mothrow['personID']}\" name=\"p{$mothrow['personID']}\" id=\"p{$mothrow['personID']}\">{$mothrow['name']}</a>";
                  echo getVitalDates($mothrow, 1);
                  echo getOtherEvents($mothrow);
                  if ($mrights['both'] && $pedigree['regnotes']) {
                    $notes = buildRegNotes(getRegNotes($mothrow['personID'], 'I'));
                    if ($notes) {
                      echo '<p>' . uiTextSnippet('notes') . ':<br>';
                      echo "<blockquote class=\"blocknote\">\n$notes</blockquote>\n</p>\n";
                    }
                  } else {
                    $notes = '';
                  }
                  //echo "</li>\n";
                  if ($mothrow['famc']) {
                    if (!in_array($mothrow['famc'], $nextgen)) {
                      array_push($nextgen, $mothrow['famc']);
                    }
                    if (!$numbers[$mothrow['famc']]) {
                      $numbers[$mothrow['famc']] = $personcount;
                    }
                  }
                }
                tng_free_result($gotmother);
              }
              if ($pedigree['regnotes']) {
                $prights = determineLivingPrivateRights($parentrow);
                if ($prights['both']) {
                  $notes = buildRegNotes(getRegNotes($nextfamily, 'F'));
                  if ($notes) {
                    echo '<p>' . uiTextSnippet('notes') . ':<br>';
                    echo "<blockquote class=\"blocknote\">\n$notes</blockquote>\n</p>\n";
                  }
                }
              }
            }

            //get children
            $result2 = getChildrenData($nextfamily);
            if ($result2 && tng_num_rows($result2)) {
              echo '<table><tr><td>' . uiTextSnippet('children') . ":<br>\n";
              echo "<ol class='children'>\n";
              while ($childrow = tng_fetch_assoc($result2)) {
                $crights = determineLivingPrivateRights($childrow);
                $childrow['allow_living'] = $crights['living'];
                $childrow['allow_private'] = $crights['private'];
                $childrow['name'] = getName($childrow);

                echo '<li>';
                if ($lastlastgen[$childrow['personID']]) {
                  echo $lastlastgen[$childrow['personID']] . '. ';
                  echo "<a href='#' onclick=\"$('html, body').animate({scrollTop: $('#p{$childrow['personID']}').offset().top-10},'slow'); return false;\">{$childrow['name']}</a>";
                } else {
                  echo "<a href=\"peopleShowPerson.php?personID={$childrow['personID']}\">{$childrow['name']}</a>";
                }
                echo getVitalDates($childrow);
                echo "</li>\n";
              }
              echo "</ol>\n</td></tr></table>\n";
              tng_free_result($result2);
            }
            echo '</td></tr></table>';
            echo "<br clear='all'></li>\n";
          }
        }

        $currgen = $nextgen;
        $lastlastgen = $lastgen;
        unset($nextgen);
        unset($lastgen);
        $nextgen = [];
        $lastgen = [];
        $generation++;
        echo "</ol>\n<br>\n";
      }
      ?>
    </div>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script>
    var tnglitbox;
  </script>
  <script src="js/rpt_utils.js"></script>
</body>
</html>