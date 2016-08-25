<?php
require 'tng_begin.php';

if (!$personID) {
  die("no args");
}
require $subroot . 'pedconfig.php';
require 'personlib.php';

$divctr = 1;
if ($pedigree['stdesc']) {
  $display = "none";
  $excolimg = "tng_plus";
  $imgtitle = uiTextSnippet('expand');
} else {
  $display = "block";
  $excolimg = "tng_minus";
  $imgtitle = uiTextSnippet('collapse');
}

function getIndividual($key, $sex, $level, $trail) {
  global $generations;
  global $divctr;
  global $display;
  global $excolimg;
  global $imgtitle;

  $rval = "";
  if ($sex == 'M') {
    $self = 'husband';
    $spouse = 'wife';
    $spouseorder = 'husborder';
  } else {
    if ($sex == 'F') {
      $self = 'wife';
      $spouse = 'husband';
      $spouseorder = 'wifeorder';
    } else {
      $self = $spouse = $spouseorder = "";
    }
  }

  if ($spouse) {
    $result = getSpouseFamilyMinimal($self, $key, $spouseorder);
  } elseif ($key) {
    $result = getSpouseFamilyMinimalUnion($key);
  }
  $marrtot = tng_num_rows($result);
  if (!$marrtot && $key) {
    $result = getSpouseFamilyMinimalUnion($key);
    $self = $spouse = $spouseorder = "";
  }

  if ($result) {
    while ($row = tng_fetch_assoc($result)) {
      $spouserow = [];
      $spousestr = "";
      if (!$spouse) {
        $spouse = $row['husband'] == $key ? 'wife' : 'husband';
      }
      if ($row[$spouse]) {
        $spouseresult = getPersonData($row[$spouse]);
        if ($spouseresult) {
          $spouserow = tng_fetch_assoc($spouseresult);
          $srights = determineLivingPrivateRights($spouserow);
          $spouserow['allow_living'] = $srights['living'];
          $spouserow['allow_private'] = $srights['private'];
          $spousename = getName($spouserow);
          $vitalinfo = getVitalDates($spouserow);
          $spousestr = "&nbsp;<a href=\"peopleShowPerson.php?personID={$spouserow['personID']}\">$spousename</a>&nbsp; $vitalinfo<br>";
        }
      }

      $result2 = getChildrenData($row['familyID']);
      $numkids = tng_num_rows($result2);
      if ($numkids) {
        $divname = "fc$divctr";
        $divctr++;
        $rval .= str_repeat("  ", ($level - 1) * 8 - 4) . "<li><img src='img/$excolimg.gif' width='9' height='9' title='$imgtitle' id='plusminus$divname' onclick=\"return toggleDescSection('$divname');\" class=\"fakelink\" alt=''> $spousestr";
        $rval .= str_repeat("  ", ($level - 1) * 8 - 2) . "<ul id=\"$divname\" style=\"display:$display;\">\n";

        while ($crow = tng_fetch_assoc($result2)) {
          $newtrail = "$trail,{$row['familyID']},{$crow['personID']}";
          $crights = determineLivingPrivateRights($crow);
          $crow['allow_living'] = $crights['living'];
          $crow['allow_private'] = $crights['private'];
          $cname = getName($crow);
          $vitalinfo = getVitalDates($crow);
          $rval .= str_repeat("  ", ($level - 1) * 8) . "<li>$level &nbsp;<a href=\"peopleShowPerson.php?personID={$crow['personID']}\">$cname</a>&nbsp;<a href=\"desctracker.php?trail=$newtrail\" title=\"" . uiTextSnippet('graphdesc') . "\"><img src=\"img/dchart.gif\" width='10' height='9' alt=\"" . uiTextSnippet('graphdesc') . "\"></a> $vitalinfo\n";
          if ($level < $generations) {
            $ind = getIndividual($crow['personID'], $crow[sex], $level + 1, $newtrail);
            if ($ind) {
              $rval .= str_repeat("  ", ($level - 1) * 8 + 2) . "<ul>\n$ind";
              $rval .= str_repeat("  ", ($level - 1) * 8 + 2) . "</ul>\n";
            }
          } else {
            //do union to check for children where person is either husband or wife
            $nxtfams = getSpouseFamilyMinimalUnion($crow['personID']);
            $nxtkids = 0;
            while ($nxtfam = tng_fetch_assoc($nxtfams)) {
              $result3 = countChildren($nxtfam['familyID']);
              $nxtrow = tng_fetch_assoc($result3);
              $nxtkids += $nxtrow['ccount'];
              tng_free_result($result3);
            }
            if ($nxtkids) {
              //chart continues
              $rval .= "[<a href=\"descendtext.php?personID={$crow['personID']}\" title=\"" . uiTextSnippet('popupnote3') . "\"> =&gt;</a>]";
            }
          }
          $rval .= str_repeat("  ", ($level - 1) * 8) . "</li>\n";
        }
        if ($numkids) {
          $rval .= str_repeat("  ", ($level - 1) * 8 - 2) . "</ul> <!-- end $divname -->\n";
          $rval .= str_repeat("  ", ($level - 1) * 8 - 4) . "</li>\n";
        }
      } elseif ($spousestr) {
        $rval .= str_repeat("  ", ($level - 1) * 8 - 4) . "<li>+ $spousestr</li>\n";
      }
      tng_free_result($result2);
    }
  }
  tng_free_result($result);
  return $rval;
}

function getVitalDates($row)
{
  $vitalinfo = "";

  if ($row['allow_living'] && $row['allow_private']) {
    if ($row['birthdate']) {
      $vitalinfo = uiTextSnippet('birthabbr') . " " . displayDate($row['birthdate']) . " ";
    } else {
      if ($row['altbirthdate']) {
        $vitalinfo = uiTextSnippet('chrabbr') . " " . displayDate($row['altbirthdate']) . " ";
      } else {
        $vitalinfo .= " ";
      }
    }
    if ($row['deathdate']) {
      $vitalinfo .= uiTextSnippet('deathabbr') . " " . displayDate($row['deathdate']);
    } else {
      if ($row['burialdate']) {
        $vitalinfo .= uiTextSnippet('burialabbr') . " " . displayDate($row['burialdate']);
      } else {
        $vitalinfo .= " ";
      }
    }
  }
  return $vitalinfo;
}
$level = 1;
$key = $personID;

$result = getPersonFullPlusDates($personID);
if ($result) {
  $row = tng_fetch_assoc($result);
  $rightbranch = checkbranch($row['branch']);
  $rights = determineLivingPrivateRights($row, $rightbranch);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  $namestr = getName($row);
  $logname = $tngconfig['nnpriv'] && $row['private'] ? uiTextSnippet('private') : ($nonames && $row['living'] ? uiTextSnippet('living') : $namestr);
}
$treeResult = getTreeSimple();
$treerow = tng_fetch_assoc($treeResult);
$disallowgedcreate = $treerow['disallowgedcreate'];
$allowpdf = !$treerow['disallowpdf'] || ($allow_pdf && $rightbranch);
tng_free_result($treeResult);

writelog("<a href=\"descendtext.php?personID=$personID\">" . uiTextSnippet('descendfor') . " $logname ($personID)</a>");
preparebookmark("<a href=\"descendtext.php?personID=$personID\">" . uiTextSnippet('descendfor') . " $namestr ($personID)</a>");

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('descendfor') . " $namestr");
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();

    $photostr = showSmallPhoto($personID, $namestr, $rights['both'], 0, false, $row['sex']);
    echo tng_DrawHeading($photostr, $namestr, getYears($row));

    if (!$pedigree['maxdesc']) {
      $pedigree['maxdesc'] = 12;
    }
    if (!$pedigree['initdescgens']) {
      $pedigree['initdescgens'] = 4;
    }
    if (!$generations) {
      $generations = $pedigree['initdescgens'] > 8 ? 8 : $pedigree['initdescgens'];
    } else {
      if ($generations > $pedigree['maxdesc']) {
        $generations = $pedigree['maxdesc'];
      } else {
        $generations = intval($generations);
      }
    }

    $innermenu = uiTextSnippet('generations') . ": &nbsp;";
    $innermenu .= "<select name=\"generations\" class=\"small\" onchange=\"window.location.href='descendtext.php?personID=$personID&amp;display=$display&amp;generations=' + this.options[this.selectedIndex].value\">\n";
    for ($i = 1; $i <= $pedigree['maxdesc']; $i++) {
      $innermenu .= "<option value=\"$i\"";
      if ($i == $generations) {
        $innermenu .= " selected";
      }
      $innermenu .= ">$i</option>\n";
    }
    $innermenu .= "</select>&nbsp;&nbsp;&nbsp;\n";
    $innermenu .= "<a href=\"descend.php?personID=$personID&amp;display=standard&amp;generations=$generations\">" .
            uiTextSnippet('pedstandard') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"descend.php?personID=$personID&amp;display=compact&amp;generations=$generations\">" .
            uiTextSnippet('pedcompact') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"descendtext.php?personID=$personID&amp;generations=$generations\">" .
            uiTextSnippet('pedtextonly') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"register.php?personID=$personID&amp;generations=$generations\">" .
            uiTextSnippet('regformat') . "</a>\n";
    if ($generations <= 12 && $allowpdf) {
      $innermenu .= " &nbsp;&nbsp; | &nbsp;&nbsp; <a href='#' onclick=\"tnglitbox = new ModalDialog('rpt_pdfform.php?pdftype=desc&amp;personID=$personID&amp;generations=$generations');return false;\">PDF</a>\n";
    }

    beginFormElement("descend", "get", "form1", "form1");
    echo buildPersonMenu("descend", $personID);
    echo "<div class='pub-innermenu small'>\n";
      echo $innermenu;
    echo "</div>\n";
    echo "<br>\n";
    endFormElement();
    ?>
    <div>
      <p>
        (<?php echo "<img src=\"img/dchart.gif\" width='10' height='9' alt=''> = " .
                uiTextSnippet('graphdesc') . ", <img src=\"img/tng_plus.gif\" width='9' height='9' alt=''> = " .
                uiTextSnippet('expand') . ", <img src=\"img/tng_minus.gif\" width='9' height='9' alt=''> = " . uiTextSnippet('collapse'); ?>
        )
      </p>
      <p>
        <a href="#" onclick="return toggleAll('');"><?php echo uiTextSnippet('expandall'); ?></a> | 
        <a href="#" onclick="return toggleAll('none');"><?php echo uiTextSnippet('collapseall'); ?></a>
      </p>
      <div id="descendantchart" align="left">
        <?php
        $vitalinfo = getVitalDates($row);
        echo "<ul class=\"first\">\n";
        echo   "<li>$level &nbsp;<a href=\"peopleShowPerson.php?personID=$personID\">$namestr</a>&nbsp; $vitalinfo\n";

        if ($generations > 1) {
          $ind = getIndividual($key, $row['sex'], $level + 1, $personID);
          if ($ind) {
            echo "<ul>$ind\n";
            echo "</ul>\n";
          }
        }
        echo   "</li>\n";
        echo "</ul>\n";
        ?>
      </div>
      <br>
    </div>
    <?php $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
<script src="js/rpt_utils.js"></script>
<script>
  var tnglitbox;
</script>
<script>

  function toggleDescSection(key) {

    var section = $('#' + key);
    if (section.css('display') === 'none') {
      section.show();
      swap("plusminus" + key, "minus");
    } else {
      section.hide();
      swap("plusminus" + key, "plus");
    }
    return false;
  }

  function toggleAll(disp) {
    var i = 1;

    while ($("#fc" + i).length) {
      $("#fc" + i).css('display', disp);
      if (disp === '')
        swap("plusminusfc" + i, "minus");
      else
        swap("plusminusfc" + i, "plus");
      i++;
    }
    return false;
  }

  plus = new Image;
  plus.src = "img/tng_plus.gif";
  minus = new Image;
  minus.src = "img/tng_minus.gif";

  function swap(x, y) {
    $('#' + x).attr('title', y === "minus" ? textSnippet('collapse') : textSnippet('expand'));
    document.images[x].src = eval(y + '.src');
  }
</script>
</body>
</html>
