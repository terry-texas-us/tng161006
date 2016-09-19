<?php
set_time_limit(0);
require 'tng_begin.php';

require $subroot . 'pedconfig.php';
require 'personlib.php';

if (!$personID) {
  die('no args');
}
if (isset($generations)) {
  $generations = intval($generations);
}
if (isset($parentset)) {
  $parentset = intval($parentset);
}

function showBlank($pedborder) {
  echo "<td $pedborder><span>&nbsp;</span></td>\n";
  echo "<td><span>&nbsp;</span></td>\n</tr>\n";
  echo "<tr>\n<td $pedborder><span>&nbsp;</span></td>\n";
  echo "<td><span>&nbsp;</span></td>\n</tr>\n";
}

function displayIndividual($key, $generation, $slot) {
  global $generations;
  global $marrdate;
  global $marrplace;
  global $pedmax;
  global $parentset;

  $nextslot = $slot * 2;
  $name = '';
  $row['birthdate'] = '';
  $row['birthplace'] = '';
  $row['altbirthdate'] = '';
  $row['altbirthplace'] = '';
  $row['deathdate'] = '';
  $row['deathplace'] = '';
  $row['burialdate'] = '';
  $row['burialplace'] = '';

  if ($key) {
    $result = getPersonData($key);
    if ($result) {
      $row = tng_fetch_assoc($result);
      $rights = determineLivingPrivateRights($row);
      $row['allow_living'] = $rights['living'];
      $row['allow_private'] = $rights['private'];
      $name = getName($row);
      tng_free_result($result);
    }
  }

  if ($slot > 1 && $slot % 2 != 0) {
    echo "</tr>\n<tr>\n";
  }

  $rowspan = pow(2, $generations - $generation);
  if ($rowspan == 1) {
    $vertfill = 8;
  } else {
    $vertfill = ($rowspan - 1) * 53 + 1;
  }

  if ($slot > 1 && $slot % 2 != 0) {
    echo "<td rowspan='$rowspan'>\n";
  } elseif ($slot % 2 == 0) {
    echo "<td rowspan='$rowspan'>\n";
  } else {
    echo "<td rowspan='$rowspan'>\n";
  }

  if ($slot > 1 && $slot % 2 != 0) {
    echo "<table>\n";
    echo "<tr>\n";
    echo "<td width='1'><img src='img/black.gif' alt='' height='$vertfill' width='1'></td>\n";
    echo "<td></td>\n</tr>\n";
    echo "</table>\n";
  } else {
    echo "<table>\n<tr>\n";
    echo "<td colspan='2'><img src='img/spacer.gif' alt=''  height='$vertfill' width='1'></td>\n</tr>\n</table>\n";
  }

  echo "<table width='100%'>\n";
  echo "<tr>\n";
  $pedborder = $slot % 2 && $slot != 1 ? "class='pedborderleft'" : '';
  echo "<td colspan='2' $pedborder><span>&nbsp;$slot. <a href='peopleShowPerson.php?personID=$key'>$name</a>&nbsp;</span></td>\n";

  //arrow goes here in own cell
  if ($nextslot >= $pedmax && $row['famc']) {
    echo "<td><a href='pedigree.php?personID=$key&amp;display=textonly' title=\"" . uiTextSnippet('popupnote2') . "\">=&gt;</a></td>\n";
  }

  echo "</tr>\n";
  echo "<tr>\n<td colspan='2'><img src='img/black.gif' alt='' width='100%' height='1'></td>\n</tr>\n";
  echo "<tr>\n";

  $pedborder = $slot % 2 ? '' : "class='pedborderleft'";
  if ($rights['both']) {
    if ($row['birthdate'] || $row['altbirthdate'] || $row['altbirthplace'] || $row['deathdate'] || $row['burialdate'] || $row['burialplace'] || ($slot % 2 == 0 && ($marrdate[$slot] || $marrplace[$slot]))) {
      $dataflag = 1;
    } else {
      $dataflag = 0;
    }
    if ($row['altbirthdate'] && !$row['birthdate']) {
      echo "<td $pedborder><span>&nbsp;" . uiTextSnippet('capaltbirthabbr') . ":</span></td>\n";
      echo '<td><span>' . displayDate($row['altbirthdate']) . "&nbsp;</span></td>\n</tr>\n";
      echo "<tr>\n<td $pedborder><span>&nbsp;" . uiTextSnippet('capplaceabbr') . ":&nbsp;</span></td>\n";
      echo "<td><span>{$row['altbirthplace']}&nbsp;</span></td>\n</tr>\n";
    } elseif ($dataflag) {
      echo "<td $pedborder><span>&nbsp;" . uiTextSnippet('capbirthabbr') . ":</span></td>\n";
      echo '<td><span>' . displayDate($row['birthdate']) . "&nbsp;</span></td></tr>\n";
      echo "<tr>\n<td $pedborder><span>&nbsp;" . uiTextSnippet('capplaceabbr') . ":&nbsp;</span></td>\n";
      echo "<td><span>{$row['birthplace']}&nbsp;</span></td>\n</tr>\n";
    } else {
      showBlank($pedborder);
    }
    if ($slot % 2 == 0) {
      if ($dataflag) {
        echo "<tr>\n<td class='pedborderleft'><span>&nbsp;" . uiTextSnippet('capmarrabbr') . ":</span></td>\n";
        echo '<td><span>' . displayDate($marrdate[$slot]) . "&nbsp;</span></td>\n</tr>\n";
        echo "<tr>\n<td class='pedborderleft'><span>&nbsp;" . uiTextSnippet('capplaceabbr') . ":&nbsp;</span></td>\n";
        echo "<td><span>{$marrplace[$slot]}&nbsp;</span></td>\n</tr>\n";
      } else {
        echo "<tr>\n";
        showBlank($pedborder);
      }
    }
    if ($row['burialdate'] && !$row['deathdate']) {
      echo "<tr>\n<td $pedborder><span>&nbsp;" . uiTextSnippet('capburialabbr') . ":</span></td>\n";
      echo '<td><span>' . displayDate($row['burialdate']) . "&nbsp;</span></td>\n</tr>\n";
      echo "<tr>\n<td $pedborder><span>&nbsp;" . uiTextSnippet('capplaceabbr') . ":&nbsp;</span></td>\n";
      echo "<td><span>{$row['burialplace']}&nbsp;</span></td>\n</tr>\n</table>\n";
    } elseif ($dataflag) {
      echo "<tr>\n<td $pedborder><span>&nbsp;" . uiTextSnippet('capdeathabbr') . ":</span></td>\n";
      echo '<td><span>' . displayDate($row['deathdate']) . "&nbsp;</span></td></tr>\n";
      echo "<tr>\n<td $pedborder><span>&nbsp;" . uiTextSnippet('capplaceabbr') . ":&nbsp;</span></td>\n";
      echo "<td><span>{$row['deathplace']}&nbsp;</span></td>\n</tr>\n</table>\n";
    } else {
      echo "<tr>\n";
      showBlank($pedborder);

      echo "</table>\n";
    }
  } else {
    showBlank($pedborder);
    if ($slot % 2 == 0) {
      echo "<tr>\n";
      showBlank($pedborder);
    }
    echo "<tr>\n";
    showBlank($pedborder);
    echo "</table>\n";
  }

  if ($slot % 2 == 0) {
    echo "<table>\n<tr>\n";
    echo "<td width='1'><img src='img/black.gif' alt=''  height='$vertfill' width='1'></td>\n";
    echo "<td></td>\n</tr>\n</table>\n";
  } else {
    echo "<table>\n<tr>\n";
    echo "<td colspan='2'><img src='img/spacer.gif' alt='' height='$vertfill' width='1'></td>\n</tr>\n</table>\n";
  }
  echo "</td>\n";

  $generation++;
  if ($nextslot < $pedmax) {
    $husband = '';
    $wife = '';
    $marrdate[$nextslot] = '';
    $marrplace[$nextslot] = '';

    if ($key) {
      $parentfamID = '';
      $locparentset = $parentset;
      $parentscount = 0;
      $parentfamIDs = [];
      $parents = getChildFamily($key, 'parentorder');
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

      $result2 = getFamilyData($parentfamID);
      if ($result2) {
        $newrow = tng_fetch_assoc($result2);
        $husband = $newrow['husband'];
        $wife = $newrow['wife'];
        $nrights = determineLivingPrivateRights($newrow);
        if ($nrights['both']) {
          $marrdate[$nextslot] = $newrow['marrdate'];
          $marrplace[$nextslot] = $newrow['marrplace'];
        } else {
          $marrdate[$nextslot] = '';
          $marrplace[$nextslot] = '';
        }
        tng_free_result($result2);
      }
    }
    displayIndividual($husband, $generation, $nextslot);
    $nextslot++;
    displayIndividual($wife, $generation, $nextslot);
  }
}

$result = getPersonFullPlusDates($personID);
if ($result) {
  $row = tng_fetch_assoc($result);
  $rightbranch = checkbranch($row['branch']);
  $rights = determineLivingPrivateRights($row, $rightbranch);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  $pedname = getName($row);
  $logname = $tngconfig['nnpriv'] && $row['private'] ? uiTextSnippet('private') : ($nonames && $row['living'] ? uiTextSnippet('living') : $pedname);
  tng_free_result($result);
}
if (!$pedigree['maxgen']) {
  $pedigree['maxgen'] = 6;
}
if ($generations > $pedigree['maxgen']) {
  $generations = intval($pedigree['maxgen']);
} elseif (!$generations) {
  $generations = $pedigree['initpedgens'] >= 2 ? intval($pedigree['initpedgens']) : 2;
} else {
  $generations = intval($generations);
}

$pedmax = pow(2, intval($generations));
$key = $personID;

$gentext = xmlcharacters(uiTextSnippet('generations'));
writelog("<a href='pedigree.php?personID=$personID&amp;generations=$generations&amp;display=textonly'>" . xmlcharacters(uiTextSnippet('pedigreefor') . " $logname ($personID)") . "</a> $generations " . $gentext);
preparebookmark("<a href='pedigree.php?personID=$personID&amp;generations=$generations&amp;display=textonly'>" . xmlcharacters(uiTextSnippet('pedigreefor') . " $pedname ($personID)") . "</a> $generations " . $gentext);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('pedigreefor') . " $pedname");
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
    <?php
    echo $publicHeaderSection->build();

    $photostr = showSmallPhoto($personID, $pedname, $rights['both'], 0, false, $row['sex']);
    echo tng_DrawHeading($photostr, $pedname, getYears($row));

    $innermenu = uiTextSnippet('generations') . ': &nbsp;';
    $innermenu .= "<select name='generations' class='small' onchange=\"window.location.href='pedigreetext.php?personID=$personID&amp;parentset=$parentset&amp;display=$display&amp;generations=' + this.options[this.selectedIndex].value\">\n";
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
    $innermenu .= "<a class='navigation-item' href='ahnentafel.php?personID=$personID&amp;parentset=$parentset&amp;generations=$generations'>" . uiTextSnippet('ahnentafel') . "</a>\n";
    $innermenu .= "<a class='navigation-item' href='extrastree.php?personID=$personID&amp;parentset=$parentset&amp;showall=1&amp;generations=$generations'>" . uiTextSnippet('media') . "</a>\n";
    if ($generations <= 6 && $allowPdf && $rightbranch) {
      $innermenu .= "<a class='navigation-item' href='#' onclick=\"tnglitbox = new ModalDialog('pdfReportOptions.modal.php?type=ped&amp;personID=$personID&amp;generations=$generations');return false;\">PDF</a>\n";
    }
    beginFormElement('pedigree', '', 'form1', 'form1');
    echo buildPersonMenu('pedigree', $personID);
    echo "<div class='pub-innermenu small'>\n";
      echo $innermenu;
    echo "</div>\n";
    echo "<br>\n";
    endFormElement();
    ?>
    <table class="pedigree-chart">
      <tr>
        <?php
        $slot = 1;
        displayIndividual($personID, 1, $slot);
        ?>
      </tr>
    </table>
    <?php echo $publicFooterSection->build(); ?>
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script src="js/rpt_utils.js"></script>
  <script>
    var tnglitbox;
  </script>
</body>
</html>
