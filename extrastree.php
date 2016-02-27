<?php
include("tng_begin.php");

include($subroot . "pedconfig.php");

if (!$generations) {
  $generations = 12;
}

function displayIndividual($key, $generation, $slot, $column) {
  global $columns;
  global $tree;
  global $pedmax;
  global $media_table;
  global $medialinks_table;
  global $col1fam;
  global $col2fam;
  global $showall;
  global $parentset;
  global $righttree;

  $nextslot = $slot * 2;
  $name = "";

  if ($key) {
    $result = getPersonDataPlusDates($tree, $key);
    if ($result) {
      $row = tng_fetch_assoc($result);
      $rights = determineLivingPrivateRights($row, $righttree);
      $row['allow_living'] = $rights['living'];
      $row['allow_private'] = $rights['private'];
      $lastname = trim($row['lnprefix'] . " " . $row['lastname']);

      if ($generation == 2) {
        if ($slot == 2) {
          $col1fam = $lastname ? $lastname : uiTextSnippet('paternal');
        } else {
          $col2fam = $lastname ? $lastname : uiTextSnippet('maternal');
        }
      }

      //if( $rights['both'] ) {
      $mediaquery = "SELECT count($medialinks_table.medialinkID) as mediacount FROM ($medialinks_table, $media_table) WHERE $medialinks_table.mediaID = $media_table.mediaID AND personID = \"$key\" AND $medialinks_table.gedcom = \"$tree\"";
      $mediaresult = tng_query($mediaquery) or die(uiTextSnippet('cannotexecutequery') . ": $mediaquery");
      if ($mediaresult) {
        $mediarow = tng_fetch_assoc($mediaresult);
        tng_free_result($mediaresult);
      } else {
        $mediarow['mediacount'] = 0;
      }

      if ($mediarow['mediacount'] || $showall) {
        if (!isset($columns[$column][$generation])) {
          $gentext = "gen$generation";
          $columns[$column][$generation] = "<span>" . uiTextSnippet($gentext) . "<br></span>\n<ul>\n";
        }
        $namestr = getNameRev($row);
        $columns[$column][$generation] .= "<li><span><a href=\"getperson.php?tng_extras=1&amp;personID=$key&amp;tree=$tree\">$namestr</a> (" . trim(getYears($row)) . ")";
        if ($mediarow['mediacount']) {
          $columns[$column][$generation] .= " <a href=\"getperson.php?tng_extras=1&amp;personID=$key&amp;tree=$tree\" title=\"" . uiTextSnippet('mediaavail') . "\">\n";
          $columns[$column][$generation] .= "<img class='icon-sm' src='svg/camera.svg' alt=\"" . uiTextSnippet('mediaavail') . "\"></a>";
        }
        $columns[$column][$generation] .= "</span></li>\n";
      }
      //}
      tng_free_result($result);
    }
  }

  $generation++;
  if ($nextslot < $pedmax) {
    $husband = "";
    $wife = "";

    if ($key) {
      $parentfamID = "";
      $locparentset = $parentset;
      $parentfamIDs = array();
      $parents = getChildFamily($tree, $key, "parentorder");
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
      $result2 = getFamilyMinimal($tree, $parentfamID);
      if ($result2) {
        $newrow = tng_fetch_assoc($result2);
        $husband = $newrow['husband'];
        $wife = $newrow['wife'];
        tng_free_result($result2);
      }
    }
    if (!$column) {
      $leftcolumn = 1;
      $rightcolumn = 2;
    } else {
      $leftcolumn = $rightcolumn = $column;
    }
    displayIndividual($husband, $generation, $nextslot, $leftcolumn);
    $nextslot++;
    displayIndividual($wife, $generation, $nextslot, $rightcolumn);
  }
}

$result = getPersonDataPlusDates($tree, $personID);
if ($result) {
  $row = tng_fetch_assoc($result);
  $righttree = checktree($tree);
  $rightbranch = $righttree ? checkbranch($row['branch']) : false;
  $rights = determineLivingPrivateRights($row, $righttree, $rightbranch);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  $pedname = getName($row);
  $logname = $tngconfig['nnpriv'] && $row['private'] ? uiTextSnippet('private') : ($nonames && $row['living'] ? uiTextSnippet('living') : $pedname);
  tng_free_result($result);
}

$treeResult = getTreeSimple($tree);
$treerow = tng_fetch_assoc($treeResult);
$disallowgedcreate = $treerow['disallowgedcreate'];
$allowpdf = !$treerow['disallowpdf'] || ($allow_pdf && $rightbranch);
tng_free_result($treeResult);

$columns = array();

$pedmax = pow(2, intval($generations));
$key = $personID;

writelog("<a href=\"extrastree.php?personID=$personID&amp;tree=$tree\">" . uiTextSnippet('familyof') . " $logname ($personID)</a>");
preparebookmark("<a href=\"extrastree.php?personID=$personID&amp;tree=$tree\">" . uiTextSnippet('familyof') . " $pedname ($personID)</a>");

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('media') . ": " . uiTextSnippet('familyof') . " $pedname");
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();

    $photostr = showSmallPhoto($personID, $pedname, $rights['both'], 0, false, $row['sex']);
    echo tng_DrawHeading($photostr, $pedname, getYears($row));

    $innermenu = uiTextSnippet('generations') . ": &nbsp;";
    if ($generations > $pedigree['maxgen']) {
      $generations = $pedigree['maxgen'];
    }
    $innermenu .= "<select name=\"generations\" class=\"small\" onchange=\"window.location.href='extrastree.php?personID=$personID&amp;tree=$tree&amp;showall=$showall&amp;generations=' + this.options[this.selectedIndex].value\">\n";
    for ($i = 1; $i <= $pedigree['maxgen']; $i++) {
      $innermenu .= "<option value=\"$i\"";
      if ($i == $generations) {
        $innermenu .= " selected";
      }
      $innermenu .= ">$i</option>\n";
    }
    $innermenu .= "</select>&nbsp;&nbsp;&nbsp;\n";
    $innermenu .= "<a href=\"pedigree.php?personID=$personID&amp;tree=$tree&amp;parentset=$parentset&amp;display=standard&amp;generations=$generations\" id=\"stdpedlnk\">" .
            uiTextSnippet('pedstandard') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"verticalchart.php?personID=$personID&amp;tree=$tree&amp;parentset=$parentset&amp;display=vertical&amp;generations=$generations\" id=\"pedchartlnk\">" .
            uiTextSnippet('pedvertical') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"pedigree.php?personID=$personID&amp;tree=$tree&amp;parentset=$parentset&amp;display=compact&amp;generations=$generations\" id=\"compedlnk\">" .
            uiTextSnippet('pedcompact') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"pedigree.php?personID=$personID&amp;tree=$tree&amp;parentset=$parentset&amp;display=box&amp;generations=$generations\" id=\"boxpedlnk\">" .
            uiTextSnippet('pedbox') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"pedigreetext.php?personID=$personID&amp;tree=$tree&amp;parentset=$parentset&amp;generations=$generations\">" .
            uiTextSnippet('pedtextonly') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"ahnentafel.php?personID=$personID&amp;tree=$tree&amp;parentset=$parentset&amp;generations=$generations\">" .
            uiTextSnippet('ahnentafel') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"extrastree.php?personID=$personID&amp;tree=$tree&amp;parentset=$parentset&amp;showall=1&amp;generations=$generations\">" .
            uiTextSnippet('media') . "</a>\n";
    if ($generations <= 6 && $allowpdf) {
      $innermenu .= " &nbsp;&nbsp; | &nbsp;&nbsp; <a href='#' onclick=\"tnglitbox = new ModalDialog('rpt_pdfform.php?pdftype=ped&amp;personID=$personID&amp;tree=$tree&amp;generations=$generations');return false;\">PDF</a>\n";
    }

    beginFormElement("pedigree", "", "form1", "form1");
    echo tng_menu('I', "pedigree", $personID, $innermenu);
    endFormElement();

    echo "<h4>" . uiTextSnippet('media') . ": " . uiTextSnippet('familyof') . " $pedname</h4>";

    if ($showall) {
      echo "<p><img class='icon-sm' src='svg/camera.svg' alt=\"" . uiTextSnippet('mediaavail') . "\"> " . uiTextSnippet('extrasexpl') . "</p>";
    }
    $slot = 1;
    displayIndividual($personID, 1, $slot, 0);

    //echo $columns['0']['1'];
    ?>
    <table>
      <tr>
        <td>
          <h4><?php echo "$col1fam " . uiTextSnippet('side'); ?></h4>
          <?php
          for ($nextgen = 2; $nextgen <= $generations; $nextgen++) {
            if ($columns[1][$nextgen]) {
              echo $columns[1][$nextgen];
              echo "</ul>\n<br>\n";
            }
          }
          ?>
        </td>
        <td></td>
        <td>
          <h4><?php echo "$col2fam " . uiTextSnippet('side'); ?></h4>
          <?php
          for ($nextgen = 2; $nextgen <= $generations; $nextgen++) {
            if ($columns[2][$nextgen]) {
              echo $columns[2][$nextgen];
              echo "</ul>\n<br>\n";
            }
          }
          ?>
        </td>
      </tr>
    </table>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script src="js/rpt_utils.js"></script>
  <script>
    var tnglitbox;
  </script>
</body>
</html>
