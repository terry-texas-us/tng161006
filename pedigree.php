<?php
set_time_limit(0);
require 'tng_begin.php';

require $subroot . 'pedconfig.php';
require 'personlib.php';

if (!$personID && !isset($needperson)) {
  die('no args');
}

if ($display == 'textonly' || (!$display && $pedigree['usepopups'] == -1)) {
  header("Location: pedigreetext.php?personID=$personID&amp;generations=$generations");
  exit;
} elseif ($display == 'ahnentafel' || (!$display && $pedigree['usepopups'] == 3)) {
  header("Location: ahnentafel.php?personID=$personID&amp;generations=$generations");
  exit;
} elseif ($display == 'vertical' || (!$display && $pedigree['usepopups'] == 4)) {
  header("Location: verticalchart.php?personID=$personID&amp;generations=$generations");
  exit;
}

$result = getPersonFullPlusDates($personID);
if (!tng_num_rows($result)) {
  if (!$allowAdd && !isset($needperson)) {
    tng_free_result($result);
    header('Location: thispagedoesnotexist.html');
    exit;
  }
} elseif (isset($needperson)) {
  unset($needperson);
}

$row = tng_fetch_assoc($result);
$rightbranch = checkbranch($row['branch']);
$rights = determineLivingPrivateRights($row, $rightbranch);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];
$pedname = getName($row);
$logname = $tngconfig['nnpriv'] && $row['private'] ? uiTextSnippet('private') : ($nonames && $row['living'] ? uiTextSnippet('living') : $pedname);
tng_free_result($result);

if (!$display) {
  if ($pedigree['usepopups'] == 1) {
    $display = 'standard';
  } elseif ($pedigree['usepopups'] == 0) {
    $display = 'box';
  } else {
    $display = 'compact';
  }
}

$rounded = $display == 'compact' ? 'chart-border-radius-sm' : 'chart-border-radius';

if ($display == 'standard') {
  $scrolldown = -200;
} elseif ($display == 'box') {
  $scrolldown = -300;
} else {
  $scrolldown = -200;
}
$arrdnpath = $rootpath . $endrootpath . 'img/ArrowDown.gif';
if (file_exists($arrdnpath)) {
  $downarrow = getimagesize($arrdnpath);
  $pedigree['downarroww'] = $downarrow[0];
  $pedigree['downarrowh'] = $downarrow[1];
  $pedigree['downarrow'] = true;
} else {
  $pedigree['downarrow'] = false;
}
$arrrtpath = $rootpath . $endrootpath . 'img/ArrowRight.gif';
if (file_exists($arrrtpath)) {
  $offpageimg = getimagesize($arrrtpath);
  $offpageimgw = $offpageimg[0];
  $offpageimgh = $offpageimg[1];
  $pedigree['offpagelink'] = "<img src=\"img/ArrowRight.gif\" $offpageimg[3] title=\"" . uiTextSnippet('popupnote2') . '" alt="' . uiTextSnippet('popupnote2') . '">';
} else {
  $pedigree['offpagelink'] = '<b>&gt;</b>';
}
$arrltpath = $rootpath . $endrootpath . 'img/ArrowRight.gif';
if (file_exists($arrltpath)) {
  $leftarrowimg = getimagesize($arrltpath);
  $leftarrowimgw = $leftarrowimg[0];
  $leftarrowimgh = $leftarrowimg[1];
  $pedigree['leftarrowlink'] = "<img src=\"img/ArrowLeft.gif\" $leftarrowimg[3] title=\"" . uiTextSnippet('popupnote2') . '" alt="' . uiTextSnippet('popupnote2') . '">';
} else {
  $pedigree['leftarrowlink'] = '<b>&lt;</b>';
}
if (file_exists($rootpath . $endrootpath . 'img/Chart.gif')) {
  $chartlinkimg = getimagesize($rootpath . $endrootpath . 'img/Chart.gif');
  $pedigree['chartlink'] = "<img src=\"img/Chart.gif\" $chartlinkimg[3] title=\"" . uiTextSnippet('popupnote2') . '" alt="' . uiTextSnippet('popupnote2') . '">';
} else {
  $pedigree['chartlink'] = '<span><b>P</b></span>';
}

$chartBoxContentsAlign = $display === 'box' ? 'left' : 'center';
$chartBoxHorizontalSpacing = $display === 'compact' ? 15 : 31;
$chartBoxVerticalSpacing = $display === 'compact' ? 7 : 15;
if ($display === 'compact') {
  $pedigree['usepopups_real'] = 0;
  $pedigree['boxheight'] = 19;
  $pedigree['boxnamesize'] = 8;
  $pedigree['namesizeshift'] = 0;
  $pedigree['cellpad'] = 0;
  $pedigree['boxwidth'] -= 50;
  $namepad = '&nbsp;';
} else {
  if ($display === 'standard') {
    $pedigree['boxheight'] = $pedigree['puboxheight'];
    $pedigree['boxwidth'] = $pedigree['puboxwidth'];
  }
  $pedigree['boxnamesize'] = 9;
  $pedigree['usepopups_real'] = 1;
  $pedigree['cellpad'] = 5;
  if ($pedigree['boxheight'] < 21) {
    $pedigree['boxheight'] = 21;
  }
  if ($chartBoxVerticalSpacing < 3 + (2 * $pedigree['borderwidth']) + ($pedigree['downarrow'] ? $pedigree['downarrowh'] : 15)) {
    $chartBoxVerticalSpacing = 3 + (2 * $pedigree['borderwidth']) + ($pedigree['downarrow'] ? $pedigree['downarrowh'] : 15);
  }
  $namepad = '';
}
if ($tngprint) {
  $chartBoxHorizontalSpacing = 15;
  if ($pedigree['boxwidth'] > 141) {
    $pedigree['boxwidth'] = 141;
  }
}

// MOST OF THIS COULD BE HANDLED WITH JAVASCRIPT VALIDATION IN editpedconfig.php
// set boundary values if needed    
if ($pedigree['leftindent'] < 0) {
  $pedigree['leftindent'] = 0;
}
if ($pedigree['boxwidth'] < 21) {
  $pedigree['boxwidth'] = 21;
}
if ($pedigree['borderwidth'] < 1) {
  $pedigree['borderwidth'] = 1;
}
if ($pedigree['linewidth'] < 1) {
  $pedigree['linewidth'] = 1;
}

// negative numbers ok for $fontshift)
// some values should be odd numbers ...    
if ($pedigree['boxwidth'] % 2 == 0) {
  $pedigree['boxwidth']++;
}
if ($pedigree['boxheight'] % 2 == 0) {
  $pedigree['boxheight']++;
}

// [ts] hard coded but still clamping to odd box spacing values
if ($chartBoxHorizontalSpacing % 2 == 0) { 
  $chartBoxHorizontalSpacing += 1;
}
if ($chartBoxVerticalSpacing % 2 == 0) {
  $chartBoxVerticalSpacing += 1;
}
// if we are going to include photos, do we have what we need?
if ($pedigree['inclphotos'] && (trim($photopath) == '' || trim($photosext) == '')) {
  $pedigree['inclphotos'] = false;
}

// how many generations to show?
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
$pedmax = pow(2, $generations);

// alternate parent display?
$parentset = $parentset ? intval($parentset) : 0;

// how much vertical real estate will we need?
$pedigree['maxheight'] = pow(2, ($generations - 1)) * (($pedigree['boxheight']) + $chartBoxVerticalSpacing);

// how much horizontal real estate will we need?
$pedigree['maxwidth'] = $generations * ($pedigree['boxwidth'] + $chartBoxHorizontalSpacing);

$key = $personID;

$pedigree['bullet'] = '&bull;';
if (!$pedigree['hideempty']) {
  $pedigree['hideempty'] = 0;
}

function getColor($shifts) {
  global $pedigree;

  return $pedigree['boxcolor'];
}

function showBox($generation, $slot) {
  global $chartStyle;
  global $chartBoxHorizontalSpacing;
  global $chartBoxVerticalSpacing;
  global $display;
  global $pedigree;
  global $generations;
  global $pedmax;
  global $boxes;
  global $offpageimgh;
  global $offpageimgw;
  global $rounded;

  // set pointer to next father/mother pair
  $nextslot = $slot * 2;

  // compute box height to use
  // -  first box height is defined by config parm [$pedigree['boxheight']].
  $boxheighttouse = $pedigree['boxheight'];

  // will we have any popup info?
  $popupinfo = false;

  // compute horizontal box offset
  // -  first box horizontal offset is defined by config parm [$pedigree['leftindent']].
  // -  boxes for each subsequent generation are offset horizontally according to values of [$pedigree['boxwidth'] and [$chartBoxHorizontalSpacing].
  $offsetH = $pedigree['leftindent'] + ($generation - 1) * ($pedigree['boxwidth'] + $chartBoxHorizontalSpacing);

  // compute vertical separation
  // -  the vertical separation between boxes of each generation are different because the box height for each generation may be different, and the boxes need to line up according to father/mother pair of the subsequent generation
  // -  we can back into the vertical separation because we can know, for the *last* generation to be displayed, the box size (computed above) and the vertical separation of those boxes (via config parm [$sepV]). This allows us to  calculate the height of the space to be used for the *last* generation
  //    display (computed as $pedigree['maxheight']). Given this, and the height of *this*  generation's boxes, we can do the following math to derive the amount of space that must exist between *this* generation's boxes to result in their being properly aligned vis-a-vis the *next* generation's boxes
  $sepV = intval($pedigree['maxheight'] - (pow(2, ($generation - 1)) * $boxheighttouse)) / pow(2, ($generation - 1));

  // compute vertical offset for first box per generation
  // -  now we need to calculate the 'base" offset vertically for *this* generation's first (or, top) box.  We computed the separation required above so support proper alignment. This calulation is also necessary to obtain proper vertical alignment
  $offsetV = ($pedigree['maxheight'] - $chartBoxVerticalSpacing - (pow(2, ($generation - 1)) * ($boxheighttouse + $sepV) - $sepV)) / 2;

  // finally, compute the offset for the box we're to build
  // -  finally, we need to figure out where the specific box for *this* generation needs to be placed. This math isn't so bad, since it's a linear equaltion based upon slot # ($slot), initial offset ($offsetV), box height ($boxheighttouse), and vertical separation ($sepV).
  $offsetV = intval($pedigree['borderwidth'] + ($slot - pow(2, ($generation - 1))) * ($boxheighttouse + $sepV) + $offsetV);

  $boxcolortouse = getColor($generation - 1);

  // compute font sizes
  // -  this will adjust font size values for subsequent generation box data
  // -  note that the shift can be different for the names portion and for the dates portion.  (Dates portion is either displayed in the box or in the popup box, depending upon the config parm [$pedigree['usepopups']].)
  // -  while decimal values are allowed for the config parms [$pedigree['namesizeshift']] and [$pedigree['datessizeshift']], rounding is done here so that only integer values will be used in the HTML strings. This means that some side-by-side generations' boxes will have the same font sizes.
  // -  Notwithstanding, the font sizes are never permitted to be less than 6 points
  $namefontsztouse = intval($pedigree['boxnamesize'] + ($generation - 1) * $pedigree['namesizeshift']);
  $datesfontsztouse = intval($pedigree['boxdatessize'] + ($generation - 1) * $pedigree['datessizeshift']);
  $popupinfosizetouse = intval($pedigree['popupinfosize'] + ($generation - 1) * $pedigree['popupinfosizeshift']);
  if ($namefontsztouse < 7) {
    $namefontsztouse = 7;
  }
  if ($datesfontsztouse < 7) {
    $datesfontsztouse = 7;
  }
  if ($popupinfosizetouse < 7) {
    $popupinfosizetouse = 7;
  }

  $boxes .= "\n<!-- slot $slot -->\n";
  if ($slot == 1) {
    $top = ($offsetV + intval(($boxheighttouse - $offpageimgh) / 2) + 1) . 'px';
    $chartStyle .= "#leftarrow {position: absolute; visibility: hidden; top: {$top}; left: $offsetH" . "px; z-index: 5;}\n";
    $boxes .= "<div id='leftarrow'>\n";
    $boxes .= "</div>\n";
    $left = ($offsetH - $pedigree['borderwidth']) . 'px';
    $boxes .= "<div class='popup' id='popupleft' style='top: {$top}; left: {$left};'>\n";
    $boxes .= "</div>\n";

    $pedigree['leftindent'] += $offpageimgw + 3;
    $offsetH += $offpageimgw + 3;
    $chartStyle .= "#popleft {font-size:$popupinfosizetouse" . "pt;}\n";
    $chartStyle .= "#popabbrleft {font-size:$popupinfosizetouse" . "pt;}\n";
  }
  $maxside = $boxheighttouse - ($pedigree['cellpad'] * 2);
  $chartStyle .= "#img$slot {max-width:" . $maxside . 'px; max-height:' . $maxside . "px;}\n";

  //start box
  $top = ($offsetV - $pedigree['borderwidth']) . 'px';
  $left = ($offsetH - $pedigree['borderwidth']) . 'px';
  $boxes .= "<div class='chart-box $rounded' id='box$slot' style=\"background-color: $boxcolortouse; top: {$top}; left: {$left}; height: {$boxheighttouse}px; width: {$pedigree['boxwidth']}px; border: {$pedigree['borderwidth']}px solid {$pedigree['bordercolor']};\" data-slot='{$slot}' data-display='{$display}'></div>\n";
  //end box

  // lay a down arrow below the box to indicate a drop-down has data
  $cancelt = $pedigree['event'] == 'over' ? " onmouseout=\"cancelTimer($slot)\"" : '';
  $onMouse = "onmouse{$pedigree['event']}=\"setPopup($slot, $offsetV, $boxheighttouse)\"$cancelt";

  $top = ($offsetV + $boxheighttouse + $pedigree['borderwidth'] + 1) . 'px';
  $left = ($offsetH - 1) . 'px';
  $boxes .= "<div class='downarrow' id='downarrow$slot' {$onMouse} style='width: {$pedigree['boxwidth']}px; top: {$top}; left: {$left}'>\n";
  $boxes .= "<img src='img/ArrowDown.gif' width=\"{$pedigree['downarroww']}\" height=\"{$pedigree['downarrowh']}\"  alt=''>\n";
  $boxes .= "</div>\n";

  if ($pedigree['usepopups_real']) { // start the block
    $boxes .= "<div class='popup' id='popup$slot' style=\"left:" . ($offsetH - $pedigree['borderwidth']) . "px;\" onmouseover=\"cancelTimer($slot)\" onmouseout=\"setTimer($slot)\">\n";
    $boxes .= "</div>\n";
  } // end popup

  // connectors for the boxes
  $vertboxstart = $offsetV + intval($boxheighttouse / 2) - intval($pedigree['linewidth'] / 2) . 'px';

  if ($generation != $generations) { // horizontal connector from child box (right side) to middle of vertical line
    $left = ($offsetH + $pedigree['boxwidth']) . 'px';
    $width = (intval($chartBoxHorizontalSpacing / 2) + 1) . 'px';
    $boxes .= "<div class='boxborder pedigree-connectors-child' style='top: {$vertboxstart}; left: {$left}; height: {$pedigree['linewidth']}px; width: {$width};'></div>\n";
  }

  if ($generation != 1) {
    $left = ($offsetH - intval($chartBoxHorizontalSpacing / 2)) . 'px';
    $width = (intval($chartBoxHorizontalSpacing / 2) + 1) . 'px';
    if ($slot % 2 == 0) { // vertical line from child horizontal connector up to father horizontal connector
      $top = $vertboxstart;
      $height = intval(1 + ($sepV + $boxheighttouse) / 2) . 'px';
      $boxes .= "<div class='pedigree-connectors-father' style='top: {$top}; left: {$left}; height: {$height}; width: {$width};'></div>\n";
    } else { // vertical line from child horizontal connector down to mother horizontal connector 
      $top = ($offsetV - intval($pedigree['linewidth'] / 2) - intval($sepV / 2)) . 'px';
      $height = intval(($sepV + $boxheighttouse) / 2) . 'px';
      $boxes .= "<div class='pedigree-connectors-mother' style='top: {$top}; left: {$left}; height: {$height}; width: {$width};'></div>\n";
    }
  }

  // see if we should include off-page connector
  if (($nextslot >= $pedmax)) {
    $top = ($offsetV + intval(($boxheighttouse - $offpageimgh) / 2) + 1) . 'px';
    $left = ($offsetH + $pedigree['boxwidth'] + $pedigree['borderwidth'] + 3) . 'px';
    $boxes .= "<div class='offpagearrow' id='offpage$slot' style=\"top: {$top}; left: {$left};\">\n";
    $params = $slot < (pow(2, $generations - 1) * 3 / 2) ? 'topparams, 1, "M"' : 'botparams, 1, "F"';
    $boxes .= "<a href='javascript:getNewFamilies({$params});'>{$pedigree['offpagelink']}</a>\n";
    $boxes .= "</div>\n";
  }
  // do the look-ahead
  $generation++;
  if ($nextslot < $pedmax) {
    showBox($generation, $nextslot);
    $nextslot++;
    showBox($generation, $nextslot);
  }
}

if (!$tngprint) {
  $tngprint = 0;
}
$chartStyle = "<style>\n";
$chartStyle .= ".pedigree-connectors-child {background-color:{$pedigree['bordercolor']};}\n";
$chartStyle .= ".pedigree-connectors-father {border-color: {$pedigree['bordercolor']}; border-left-width: {$pedigree['linewidth']}px; border-top-width: {$pedigree['linewidth']}px;}\n";
$chartStyle .= ".pedigree-connectors-mother {border-color: {$pedigree['bordercolor']}; border-left-width: {$pedigree['linewidth']}px; border-bottom-width: {$pedigree['linewidth']}px;}\n";
$chartStyle .= ".popup {position: absolute; visibility: hidden; background-color: {$pedigree['popupcolor']}; z-index: 8}\n";
$chartStyle .= ".pboxname {font-size: {$pedigree['boxnamesize']}pt; text-align: {$chartBoxContentsAlign};}\n";
$slot = 1;
$boxes = '';
showBox(1, $slot);
$chartStyle .= "</style>\n";

$gentext = xmlcharacters(uiTextSnippet('generations'));
writelog("<a href='pedigree.php?personID=$personID&amp;generations=$generations&amp;display=$display'>" . xmlcharacters(uiTextSnippet('pedigreefor') . " $logname ($personID)") . "</a> $generations " . $gentext);
preparebookmark("<a href='pedigree.php?personID=$personID&amp;generations=$generations&amp;display=$display'>" . xmlcharacters(uiTextSnippet('pedigreefor') . " $pedname ($personID)") . "</a> $generations " . $gentext);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('pedigreefor') . " $pedname");
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php    
    echo $chartStyle;
    echo $publicHeaderSection->build();

    $photostr = showSmallPhoto($personID, $pedname, $rights['both'], 0, false, $row['sex']);
    echo tng_DrawHeading($photostr, $pedname, getYears($row));

    $innermenu = uiTextSnippet('generations') . ': &nbsp;';
    $innermenu .= "<select name='generations' class='small' onchange=\"window.location.href='pedigree.php?personID=' + firstperson + '&amp;parentset=$parentset&amp;display=$display&amp;generations=' + this.options[this.selectedIndex].value\">\n";
    for ($i = 2; $i <= $pedigree['maxgen']; $i++) {
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
    $innermenu .= "<a class='navigation-item' href='pedigreetext.php?personID=$personID&amp;parentset=$parentset&amp;generations=$generations' id='textlnk'>" . uiTextSnippet('pedtextonly') . "</a>\n";
    $innermenu .= "<a class='navigation-item' href='ahnentafel.php?personID=$personID&amp;parentset=$parentset&amp;generations=$generations' id='ahnlnk'>" . uiTextSnippet('ahnentafel') . "</a>\n";
    $innermenu .= "<a class='navigation-item' href='extrastree.php?personID=$personID&amp;parentset=$parentset&amp;showall=1&amp;generations=$generations' id='extralnk'>" . uiTextSnippet('media') . "</a>\n";
    if ($generations <= 6 && $allowPdf && $rightbranch) {
      $innermenu .= "<a class='navigation-item' href='#' onclick=\"tnglitbox = new ModalDialog('pdfReportOptions.modal.php?type=ped&amp;personID=' + firstperson + '&amp;generations=$generations');return false;\">PDF</a>\n";
    }
    beginFormElement('pedigree', '', 'form1', 'form1');
    echo buildPersonMenu('pedigree', $personID);
    echo "<div class='pub-innermenu small'>\n";
    echo $innermenu;
    echo "</div>\n";
    echo "<br>\n";
    endFormElement();

    $height = 20 + $pedigree['borderwidth'] + ($pedigree['maxheight'] - $chartBoxVerticalSpacing); 
    echo "<div id='outer' align='left' style='position: relative; margin-top: 8px; height: {$height}px'>";
    echo $boxes;
    echo '</div>';
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script>
    var tnglitbox;
    var slotceiling = <?php echo $pedmax ?>;
    var slotceiling_minus1 = <?php echo (pow(2, $generations - 1)) ?>;
    var display = '<?php echo $display ?>';
    var usepopups = <?php echo $pedigree['usepopups_real'] ?>;
    var popupchartlinks = <?php echo $pedigree['popupchartlinks'] ?>;
    var popupkids = <?php echo $pedigree['popupkids'] ?>;
    var popupspouses = <?php echo $pedigree['popupspouses'] ?>;
    var popuptimer = <?php echo $pedigree['popuptimer'] ?>;
    var pedborderwidth = <?php echo $pedigree['borderwidth'] ?>;
    var pedbullet = '<?php echo $pedigree['bullet'] ?>';
    var emptycolor = '<?php echo $pedigree['emptycolor'] ?>';
    var hideempty = <?php echo $pedigree['hideempty'] ?>;
    var leftarrowimg = '<?php echo $pedigree['leftarrowlink'] ?>';
    var namepad = '<?php echo $namepad ?>';
    var allow_add = <?php echo $allowAdd ?>;
    var allow_edit = <?php echo $allowEdit ?>;
    var chartlink = '<?php echo $pedigree['chartlink'] ?>';
    var personID = '<?php echo $personID ?>';
    var parentset = <?php echo $parentset ?>;
    var generations = <?php echo $generations ?>;
    var tngprint = <?php echo $tngprint ?>;
    if (allow_edit) {
      var allow_cites = true;
      var allow_notes = true;
    }
    $(function () {
        $('[data-toggle="popover"]').popover();
    });

  </script>
  <script src='js/tngpedigree.js'></script>
  <?php if ($allowEdit || $allowAdd) { ?>
    <script src='js/tngpededit.js'></script>
    <script>
      var preferEuro = <?php echo($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : 'false'); ?>;
      var preferDateFormat = '<?php echo $preferDateFormat; ?>';
    </script>
    <script src="js/selectutils.js"></script>
    <script src="js/associations.js"></script>
    <script src='js/families.js'></script>
    <script src="js/datevalidation.js"></script>
  <?php } ?>
  <script src="js/rpt_utils.js"></script>
  <script>
    getNewChart(personID, generations, parentset);

    <?php if ($needperson && $allowAdd) { ?>
    var nplitbox;
    function openCreatePersonForm() {
      tnglitbox = new ModalDialog('admin_newperson2.php?&amp;needped=1');
      $('#firstname').focus();
      return false;
    }
    <?php } ?>

    $(document).ready(function () {
      <?php if ($generations > 4 && !$tngprint) { ?>
      $('html, body').animate({scrollTop: $('#box1').offset().top<?php echo $scrolldown; ?>}, 'slow');
      <?php 
      }
      if ($needperson && $allowAdd) {
      ?>
      openCreatePersonForm();
      <?php } ?>
    
    });
  </script>

</body>
</html>