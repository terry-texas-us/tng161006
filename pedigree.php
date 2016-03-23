<?php
set_time_limit(0);
include("tng_begin.php");

include($subroot . "pedconfig.php");
require 'personlib.php';

if (!$personID && !isset($needperson)) {
  die("no args");
}

if ($display == "textonly" || (!$display && $pedigree['usepopups'] == -1)) {
  header("Location: pedigreetext.php?personID=$personID&tree=$tree&generations=$generations");
  exit;
} elseif ($display == "ahnentafel" || (!$display && $pedigree['usepopups'] == 3)) {
  header("Location: ahnentafel.php?personID=$personID&tree=$tree&generations=$generations");
  exit;
} elseif ($display == "vertical" || (!$display && $pedigree['usepopups'] == 4)) {
  header("Location: verticalchart.php?personID=$personID&tree=$tree&generations=$generations");
  exit;
}

$result = getPersonFullPlusDates($tree, $personID);
if (!tng_num_rows($result)) {
  if (!$allow_add && !isset($needperson)) {
    tng_free_result($result);
    header("Location: thispagedoesnotexist.html");
    exit;
  }
} elseif (isset($needperson)) {
  unset($needperson);
}

$row = tng_fetch_assoc($result);
$righttree = checktree($tree);
$rightbranch = $righttree ? checkbranch($row['branch']) : false;
$rights = determineLivingPrivateRights($row, $righttree, $rightbranch);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];
$pedname = getName($row);
$logname = $tngconfig['nnpriv'] && $row['private'] ? uiTextSnippet('private') : ($nonames && $row['living'] ? uiTextSnippet('living') : $pedname);
tng_free_result($result);

$treeResult = getTreeSimple($tree);
$treerow = tng_fetch_assoc($treeResult);
$disallowgedcreate = $treerow['disallowgedcreate'];
$allowpdf = !$treerow['disallowpdf'] || ($allow_pdf && $rightbranch);
tng_free_result($treeResult);

if (!$display) {
  if ($pedigree['usepopups'] == 1) {
    $display = "standard";
  } elseif ($pedigree['usepopups'] == 0) {
    $display = "box";
  } else {
    $display = "compact";
  }
}

$rounded = $display == "compact" ? "rounded4" : 'rounded10';

if ($display == "standard") {
  $scrolldown = -200;
} elseif ($display == "box") {
  $scrolldown = -300;
} else {
  $scrolldown = -200;
}
$arrdnpath = $rootpath . $endrootpath . "img/ArrowDown.gif";
if (file_exists($arrdnpath)) {
  $downarrow = getimagesize($arrdnpath);
  $pedigree['downarroww'] = $downarrow[0];
  $pedigree['downarrowh'] = $downarrow[1];
  $pedigree['downarrow'] = true;
} else {
  $pedigree['downarrow'] = false;
}
$arrrtpath = $rootpath . $endrootpath . "img/ArrowRight.gif";
if (file_exists($arrrtpath)) {
  $offpageimg = getimagesize($arrrtpath);
  $offpageimgw = $offpageimg[0];
  $offpageimgh = $offpageimg[1];
  $pedigree['offpagelink'] = "<img src=\"img/ArrowRight.gif\" $offpageimg[3] title=\"" . uiTextSnippet('popupnote2') . "\" alt=\"" . uiTextSnippet('popupnote2') . "\">";
} else {
  $pedigree['offpagelink'] = "<b>&gt;</b>";
}
$arrltpath = $rootpath . $endrootpath . "img/ArrowRight.gif";
if (file_exists($arrltpath)) {
  $leftarrowimg = getimagesize($arrltpath);
  $leftarrowimgw = $leftarrowimg[0];
  $leftarrowimgh = $leftarrowimg[1];
  $pedigree['leftarrowlink'] = "<img src=\"img/ArrowLeft.gif\" $leftarrowimg[3] title=\"" . uiTextSnippet('popupnote2') . "\" alt=\"" . uiTextSnippet('popupnote2') . "\">";
} else {
  $pedigree['leftarrowlink'] = "<b>&lt;</b>";
}
if (file_exists($rootpath . $endrootpath . "img/Chart.gif")) {
  $chartlinkimg = getimagesize($rootpath . $endrootpath . "img/Chart.gif");
  $pedigree['chartlink'] = "<img src=\"img/Chart.gif\" $chartlinkimg[3] title=\"" . uiTextSnippet('popupnote2') . "\" alt=\"" . uiTextSnippet('popupnote2') . "\">";
} else {
  $pedigree['chartlink'] = "<span><b>P</b></span>";
}
if ($display == "standard") {
  $pedigree['boxheight'] = $pedigree['puboxheight'];
  $pedigree['boxwidth'] = $pedigree['puboxwidth'];
  $pedigree['boxalign'] = $pedigree['puboxalign'];
  $pedigree['boxheightshift'] = $pedigree['puboxheightshift'];
}

if ($display == "compact") {
  $pedigree['usepopups_real'] = 0;
  $pedigree['boxHsep'] = 7;
  $pedigree['boxheight'] = 16;
  $pedigree['boxheightshift'] = 0;
  $pedigree['boxnamesize'] = 8;
  $pedigree['namesizeshift'] = 0;
  $pedigree['cellpad'] = 0;
  $pedigree['boxwidth'] -= 50;
  $pedigree['boxVsep'] = 5;
  $pedigree['shadowoffset'] = 1;
  $namepad = "&nbsp;";
} else {
  $pedigree['boxnamesize'] = 9;
  $pedigree['usepopups_real'] = 1;
  $pedigree['cellpad'] = 5;
  if ($pedigree['boxheight'] < 21) {
    $pedigree['boxheight'] = 21;
  }
  if ($pedigree['boxheightshift'] > 0) {
    $pedigree['boxheightshift'] = -1 * $pedigree['boxheightshift'];
  }
  if ($pedigree['boxHsep'] < 7) {
    $pedigree['boxHsep'] = 7;
  }
  if ($pedigree['boxVsep'] < 3 + $pedigree['shadowoffset'] + (2 * $pedigree['borderwidth']) + ($pedigree['downarrow'] ? $pedigree['downarrowh'] : 15)) {
    $pedigree['boxVsep'] = 3 + $pedigree['shadowoffset'] + (2 * $pedigree['borderwidth']) + ($pedigree['downarrow'] ? $pedigree['downarrowh'] : 15);
  }
  $namepad = "";
}
if ($tngprint) {
  if ($pedigree['boxHsep'] > 21) {
    $pedigree['boxHsep'] = 21;
  }
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

// negative numbers ok for $pedigree['shadowoffset'], $pedigree['colorshift'], $fontshift)
// some values should be odd numbers ...    
if ($pedigree['boxwidth'] % 2 == 0) {
  $pedigree['boxwidth']++;
}
if ($pedigree['boxheight'] % 2 == 0) {
  $pedigree['boxheight']++;
}
if ($pedigree['boxHsep'] % 2 == 0) {
  $pedigree['boxHsep']++;
}
if ($pedigree['boxVsep'] % 2 == 0) {
  $pedigree['boxVsep']++;
}
// and some even ...
if ($pedigree['boxheightshift'] % 2 != 0) {
  $pedigree['boxheightshift']++;
}

// if we are going to include photos, do we have what we need?
if ($pedigree['inclphotos'] && (trim($photopath) == "" || trim($photosext) == "")) {
  $pedigree['inclphotos'] = false;
}

// let's not shrink a box into nothingness
// boxheight must support at least 16 generations and not shrink below 16 pixels
if ($pedigree['boxheightshift'] && ($pedigree['boxheight'] < -16 * $pedigree['boxheightshift'] + 16)) {
  $pedigree['boxheight'] = -16 * $pedigree['boxheightshift'] + 16;
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
$pedigree['maxheight'] = pow(2, ($generations - 1)) * (($pedigree['boxheight'] + ($pedigree['boxheightshift'] * ($generations - 1))) + $pedigree['boxVsep']);

// how much horizontal real estate will we need?
$pedigree['maxwidth'] = $generations * ($pedigree['boxwidth'] + $pedigree['boxHsep']);

$key = $personID;
$pedigree['baseR'] = hexdec(substr($pedigree['boxcolor'], 1, 2));
$pedigree['baseG'] = hexdec(substr($pedigree['boxcolor'], 3, 2));
$pedigree['baseB'] = hexdec(substr($pedigree['boxcolor'], 5, 2));

if ($pedigree['colorshift'] > 0) {
  $extreme = $pedigree['baseR'] < $pedigree['baseG'] ? $pedigree['baseR'] : $pedigree['baseG'];
  $extreme = $extreme < $pedigree['baseB'] ? $extreme : $pedigree['baseB'];
} elseif ($pedigree['colorshift'] < 0) {
  $extreme = $pedigree['baseR'] > $pedigree['baseG'] ? $pedigree['baseR'] : $pedigree['baseG'];
  $extreme = $extreme > $pedigree['baseB'] ? $extreme : $pedigree['baseB'];
}
$pedigree['colorshift'] = round($pedigree['colorshift'] / 100 * $extreme / ($generations + 1));
$pedigree['phototree'] = $tree;
if ($tree) {
  $pedigree['phototree'] .= ".";
}

$pedigree['bullet'] = "&bull;";
if (!$pedigree['hideempty']) {
  $pedigree['hideempty'] = 0;
}

function getColor($shifts) {
  global $pedigree;

  $shiftval = $shifts * $pedigree['colorshift'];
  $R = $pedigree['baseR'] + $shiftval;
  $G = $pedigree['baseG'] + $shiftval;
  $B = $pedigree['baseB'] + $shiftval;
  if ($R > 255) {
    $R = 255;
  }
  if ($R < 0) {
    $R = 0;
  }
  if ($G > 255) {
    $G = 255;
  }
  if ($G < 0) {
    $G = 0;
  }
  if ($B > 255) {
    $B = 255;
  }
  if ($B < 0) {
    $B = 0;
  }
  $R = str_pad(dechex($R), 2, "0", STR_PAD_LEFT);
  $G = str_pad(dechex($G), 2, "0", STR_PAD_LEFT);
  $B = str_pad(dechex($B), 2, "0", STR_PAD_LEFT);
  return "#$R$G$B";
}

function showBox($generation, $slot) {
  global $chartStyle;
  
  global $pedigree;
  global $generations;
  global $pedmax;
  global $boxes;
  global $flags;
  global $offpageimgh;
  global $offpageimgw;
  global $rounded;

  // set pointer to next father/mother pair
  $nextslot = $slot * 2;

  // compute box height to use
  // -  first box height is defined by config parm [$pedigree['boxheight']].
  // -  boxes of each subsequent generation shrunk according to config parm [$pedigree['boxheightshift']] (which may be zero, in which case all boxes will be the same height).
  // -  some minimums and defaults are enforced so that we don't get into trouble shrinking the heights to negative numbers (which would be a bad thing).
  $boxheighttouse = $pedigree['boxheight'] + ($pedigree['boxheightshift'] * ($generation - 1));

  // will we have any popup info?
  $popupinfo = false;

  // compute horizontal box offset
  // -  first box horizontal offset is defined by config parm [$pedigree['leftindent']].
  // -  boxes for each subsequent generation are offset horizontally according to config parms [$pedigree['boxwidth'] and [$pedigree['boxHsep']]. The latter value has a minimum setting enforced in the earlier idiot checks so that we don't get negative offsets and so there's at least *some* room for connectors.
  $offsetH = $pedigree['leftindent'] + ($generation - 1) * ($pedigree['boxwidth'] + $pedigree['boxHsep']);

  // compute vertical separation
  // -  the vertical separation between boxes of each generation are different because the box height for each generation may be different, and the boxes need to line up according to father/mother pair of the subsequent generation
  // -  we can back into the vertical separation because we can know, for the *last* generation to be displayed, the box size (computed above) and the vertical separation of those boxes (via config parm [$sepV]). This allows us to  calculate the height of the space to be used for the *last* generation
  //    display (computed as $pedigree['maxheight']). Given this, and the height of *this*  generation's boxes, we can do the following math to derive the amount of space that must exist between *this* generation's boxes to result in their being properly aligned vis-a-vis the *next* generation's boxes
  $sepV = intval($pedigree['maxheight'] - (pow(2, ($generation - 1)) * $boxheighttouse)) / pow(2, ($generation - 1));

  // compute vertical offset for first box per generation
  // -  now we need to calculate the 'base" offset vertically for *this* generation's first (or, top) box.  We computed the separation required above so support proper alignment. This calulation is also necessary to obtain proper vertical alignment
  $offsetV = ($pedigree['maxheight'] - $pedigree['boxVsep'] - (pow(2, ($generation - 1)) * ($boxheighttouse + $sepV) - $sepV)) / 2;

  // finally, compute the offset for the box we're to build
  // -  finally, we need to figure out where the specific box for *this* generation needs to be placed. This math isn't so bad, since it's a linear equaltion based upon slot # ($slot), initial offset ($offsetV), box height ($boxheighttouse), and vertical separation ($sepV).
  $offsetV = intval($pedigree['borderwidth'] + ($slot - pow(2, ($generation - 1))) * ($boxheighttouse + $sepV) + $offsetV);

  // compute box color
  // -  if the config parm [$pedigree['colorshift']] is anything other than zero this math will reduce each primary color value (red,green,blue),  respectively, but the color shift value
  // -  if $pedigree['colorshift'] = 0, all this code spits out the same value as  defined by the config parm [$pedigree['boxcolor']]
  // -  otherwise the color will "shift" up or down (closer to white or closer to black)
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

  //... include trace (maybe)
  $boxes .= "\n<!-- box for slot $slot -->\n";
  if ($slot == 1) {
    $chartStyle .= "#leftarrow {position:absolute; visibility:hidden; top:" . ($offsetV + intval(($boxheighttouse - $offpageimgh) / 2) + 1) . "px; left:$offsetH" . "px;z-index:5;}\n";
    $boxes .= "<div id=\"leftarrow\">\n";
    $boxes .= "</div>\n";

    $boxes .= "<div class=\"popup\" id=\"popupleft\" style=\"top:" . ($offsetV + intval(($boxheighttouse - $offpageimgh) / 2) + 1) . "px; left:" . ($offsetH - $pedigree['borderwidth'] + round($pedigree['shadowoffset'] / 2)) . "px;\" onmouseover=\"cancelTimer('left')\" onmouseout=\"setTimer('left')\">\n";
    $boxes .= "</div>\n";

    $pedigree['leftindent'] += $offpageimgw + $pedigree['shadowoffset'] + 3;
    $offsetH += $offpageimgw + $pedigree['shadowoffset'] + 3;
    $chartStyle .= "#popleft {font-size:$popupinfosizetouse" . "pt;}\n";
    $chartStyle .= "#popabbrleft {font-size:$popupinfosizetouse" . "pt;}\n";
  }
  $maxside = $boxheighttouse - ($pedigree['cellpad'] * 2);
  $chartStyle .= "#img$slot {max-width:" . $maxside . "px; max-height:" . $maxside . "px;}\n";

  //start box
  $icons = $display != "box" ? " onmouseover=\"if($('#ic$slot').length) $('#ic$slot').show();\" onmouseout=\"if($('#ic$slot').length) $('#ic$slot').hide();\"" : "";
  $boxes .= "<div id=\"box$slot\" class=\"pedbox $rounded\" style=\"background-color:$boxcolortouse; top:" . ($offsetV - $pedigree['borderwidth']) . "px; left:" . ($offsetH - $pedigree['borderwidth']) . "px; height:$boxheighttouse" . "px; width:{$pedigree['boxwidth']}px; border:{$pedigree['borderwidth']}px solid {$pedigree['bordercolor']};\"$icons></div>\n";
  //end box
  // build the pop-up information box
  $boxes .= "\n<!-- popup for $name -->\n\n";

  // lay a down arrow below the box to indicate a drop-down has data
  $cancelt = $pedigree['event'] == "over" ? " onmouseout=\"cancelTimer($slot)\"" : "";
  $boxes .= "<div class=\"downarrow\" id=\"downarrow$slot\" onmouse{$pedigree['event']}=\"setPopup($slot, $offsetV,$boxheighttouse)\"$cancelt style=\"width:{$pedigree['boxwidth']}" . "px; text-align:center; top:" . ($offsetV + $boxheighttouse + $pedigree['borderwidth'] + $pedigree['shadowoffset'] + 1) . "px;left:" . ($offsetH - 1) . "px;\">\n";

  $boxes .= "<img src='img/ArrowDown.gif' width=\"{$pedigree['downarroww']}\" height=\"{$pedigree['downarrowh']}\"  alt=''></div>\n";

  if ($pedigree['usepopups_real']) {
    //start the block
    $boxes .= "<div class=\"popup\" id=\"popup$slot\" style=\"left:" . ($offsetH - $pedigree['borderwidth'] + round($pedigree['shadowoffset'] / 2)) . "px;\" onmouseover=\"cancelTimer($slot)\" onmouseout=\"setTimer($slot)\">\n";

    //end popup
    $boxes .= "</div>\n";
  }

  $boxes .= "\n<!-- box outline and shadow for slot $slot -->\n";

  //line & shadow
  //$boxes .= "<div class=\"border\" id=\"border$slot" . "_1\" style=\"top:" . ($offsetV-$pedigree['borderwidth']) . "px; left:" . ($offsetH-$pedigree['borderwidth']) . "px; height:" . ($boxheighttouse+(2*$pedigree['borderwidth'])) . "px; width:" . ($pedigree['boxwidth']+(2*$pedigree['borderwidth'])) . "px; z-index:4;\"></div>\n";
  $boxes .= "<div class=\"boxshadow pedshadow $rounded\" id=\"shadow$slot" . "_1\" style=\"top:" . ($offsetV - $pedigree['borderwidth'] + $pedigree['shadowoffset']) . "px; left:" . ($offsetH - $pedigree['borderwidth'] + $pedigree['shadowoffset']) . "px; height:" . ($boxheighttouse + (2 * $pedigree['borderwidth'])) . "px; width:" . ($pedigree['boxwidth'] + (2 * $pedigree['borderwidth'])) . "px;\"></div>\n";

  // build left horizontal lines & shadows (except for first generation)
  $vertboxstart = $offsetV + intval($boxheighttouse / 2) - intval($pedigree['linewidth'] / 2);
  $halfhorzsep = intval($pedigree['boxHsep'] / 2);

  if ($generation != 1) {
    $boxes .= "<div class=\"boxborder pedborder\" id=\"border$slot" . "_2\" style=\"top:" . $vertboxstart . "px; left:" . ($offsetH - $halfhorzsep) . "px; height:{$pedigree['linewidth']}px; width:" . ($halfhorzsep + 2) . "px;\"></div>\n";
    $boxes .= "<div class=\"boxshadow pedshadow\" id=\"shadow$slot" . "_2\" style=\"top:" . ($vertboxstart + $pedigree['shadowoffset'] + 1) . "px;left:" . (($offsetH - $halfhorzsep) + $pedigree['shadowoffset'] + 1) . "px; height:{$pedigree['linewidth']}px; width:" . ($halfhorzsep + 2) . "px;\"></div>\n";
  }

  // build right horizontal line & shadow (except for last generation)
  if ($generation != $generations) {
    $boxes .= "<div class=\"boxborder pedborder\" id=\"border$slot" . "_3\" style=\"top:" . $vertboxstart . "px; left:" . ($offsetH + $pedigree['boxwidth']) . "px; height:{$pedigree['linewidth']}px; width:" . (intval($pedigree['boxHsep'] / 2) + 1) . "px;\"></div>\n";
    $boxes .= "<div class=\"boxshadow pedshadow\" id=\"shadow$slot" . "_3\" style=\"top:" . ($vertboxstart + $pedigree['shadowoffset'] + 1) . "px; left:" . ($offsetH + $pedigree['boxwidth'] + $pedigree['shadowoffset'] + 1) . "px; height:{$pedigree['linewidth']}px; width:" . ($halfhorzsep + 1) . "px;\"></div>\n";
  }

  // build vertical line & shadow
  if ($generation != 1) {
    if ($slot % 2 == 0) {    //father
      $boxes .= "<div class=\"boxborder pedborder\" id=\"border$slot" . "_4\" style=\"top:" . $vertboxstart . "px; left:" . ($offsetH - intval($pedigree['boxHsep'] / 2)) . "px; height:" . intval(1 + ($sepV + $boxheighttouse) / 2) . "px; width:{$pedigree['linewidth']}px;\"></div>\n";
      $boxes .= "<div class=\"boxshadow pedshadow\" id=\"shadow$slot" . "_4\" style=\"top:" . ($vertboxstart + $pedigree['shadowoffset'] + 1) . "px; left:" . ($offsetH - $halfhorzsep + $pedigree['shadowoffset'] + 1) . "px; height:" . intval(1 + ($sepV + $boxheighttouse) / 2) . "px; width:{$pedigree['linewidth']}px;\"></div>\n";
    } else { //mother
      $boxes .= "<div class=\"boxborder pedborder\" id=\"border$slot" . "_5\" style=\"top:" . ($offsetV - intval($pedigree['linewidth'] / 2) - intval($sepV / 2)) . "px; left:" . ($offsetH - $halfhorzsep) . "px; height:" . intval(($sepV + $boxheighttouse) / 2) . "px; width:{$pedigree['linewidth']}px;\"></div>\n";
      $boxes .= "<div class=\"boxshadow pedshadow\" id=\"shadow$slot" . "_5\" style=\"top:" . ($offsetV - intval($pedigree['linewidth'] / 2) - intval($sepV / 2) + $pedigree['shadowoffset'] + 1) . "px; left:" . ($offsetH - intval($pedigree['boxHsep'] / 2) + $pedigree['shadowoffset'] + 1) . "px; height:" . intval(($sepV + $boxheighttouse) / 2) . "px; width:{$pedigree['linewidth']}px;\"></div>\n";
    }
  }

  // see if we should include off-page connector
  if (($nextslot >= $pedmax)) {
    $boxes .= "<div class=\"offpagearrow\" id=\"offpage$slot\" style=\"top:" . ($offsetV + intval(($boxheighttouse - $offpageimgh) / 2) + 1) . "px; left:" . ($offsetH + $pedigree['boxwidth'] + $pedigree['borderwidth'] + $pedigree['shadowoffset'] + 3) . "px;\"><a href=\"javascript:getNewFamilies(";
    $boxes .= $slot < (pow(2, $generations - 1) * 3 / 2) ? "topparams,1,'M'" : "botparams,1,'F'";
    $boxes .= ");\">{$pedigree['offpagelink']}</a></div>\n";
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
$chartStyle .= ".pedborder {background-color:{$pedigree['bordercolor']};}\n";
$chartStyle .= ".pedshadow {background-color:{$pedigree['shadowcolor']};}\n";
$chartStyle .= ".popup { position:absolute; visibility:hidden; background-color:{$pedigree['popupcolor']}; z-index:8 }\n";
$chartStyle .= ".pboxname { font-size:{$pedigree['boxnamesize']}pt; text-align:{$pedigree['boxalign']}; }\n";
$slot = 1;
$boxes = "";
showBox(1, $slot);
$chartStyle .= "</style>\n";

$gentext = xmlcharacters(uiTextSnippet('generations'));
writelog("<a href=\"pedigree.php?personID=$personID&amp;tree=$tree&amp;generations=$generations&amp;display=$display\">" . xmlcharacters(uiTextSnippet('pedigreefor') . " $logname ($personID)") . "</a> $generations " . $gentext);
preparebookmark("<a href=\"pedigree.php?personID=$personID&amp;tree=$tree&amp;generations=$generations&amp;display=$display\">" . xmlcharacters(uiTextSnippet('pedigreefor') . " $pedname ($personID)") . "</a> $generations " . $gentext);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
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

    $innermenu = uiTextSnippet('generations') . ": &nbsp;";
    $innermenu .= "<select name=\"generations\" class=\"small\" onchange=\"window.location.href='pedigree.php?personID=' + firstperson + '&amp;tree=$tree&amp;parentset=$parentset&amp;display=$display&amp;generations=' + this.options[this.selectedIndex].value\">\n";
    for ($i = 2; $i <= $pedigree['maxgen']; $i++) {
      $innermenu .= "<option value=\"$i\"";
      if ($i == $generations) {
        $innermenu .= " selected";
      }
      $innermenu .= ">$i</option>\n";
    }
    $innermenu .= "</select>&nbsp;&nbsp;&nbsp;\n";
    $innermenu .= "<a href=\"pedigree.php?personID=$personID&amp;tree=$tree&amp;parentset=$parentset&amp;display=standard&amp;generations=$generations\" id=\"stdpedlnk\">" . uiTextSnippet('pedstandard') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"verticalchart.php?personID=$personID&amp;tree=$tree&amp;parentset=$parentset&amp;display=vertical&amp;generations=$generations\" id=\"pedchartlnk\">" . uiTextSnippet('pedvertical') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"pedigree.php?personID=$personID&amp;tree=$tree&amp;parentset=$parentset&amp;display=compact&amp;generations=$generations\" id=\"compedlnk\">" . uiTextSnippet('pedcompact') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"pedigree.php?personID=$personID&amp;tree=$tree&amp;parentset=$parentset&amp;display=box&amp;generations=$generations\" id=\"boxpedlnk\">" . uiTextSnippet('pedbox') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"pedigreetext.php?personID=$personID&amp;tree=$tree&amp;parentset=$parentset&amp;generations=$generations\" id=\"textlnk\">" . uiTextSnippet('pedtextonly') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"ahnentafel.php?personID=$personID&amp;tree=$tree&amp;parentset=$parentset&amp;generations=$generations\" id=\"ahnlnk\">" . uiTextSnippet('ahnentafel') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"extrastree.php?personID=$personID&amp;tree=$tree&amp;parentset=$parentset&amp;showall=1&amp;generations=$generations\" id=\"extralnk\">" . uiTextSnippet('media') . "</a>\n";
    if ($generations <= 6 && $allowpdf) {
      $innermenu .= " &nbsp;&nbsp; | &nbsp;&nbsp; <a href='#' onclick=\"tnglitbox = new ModalDialog('rpt_pdfform.php?pdftype=ped&amp;personID=' + firstperson + '&amp;tree=$tree&amp;generations=$generations');return false;\">PDF</a>\n";
    }
    beginFormElement("pedigree", "", "form1", "form1");
    echo buildPersonMenu("pedigree", $personID);
    echo "<div class='pub-innermenu small'>\n";
      echo $innermenu;
    echo "</div>\n";
    echo "<br>\n";
    endFormElement();

    if (!$tngprint) {
      echo "<span>(" . uiTextSnippet('scrollnote');
      if ($pedigree['usepopups_real']) {
        echo ($pedigree['downarrow'] ? " <img src='img/ArrowDown.gif' width=\"{$pedigree['downarroww']}\" height=\"{$pedigree['downarrowh']}\" alt=''>" : " <a href='#'><span><B>V</B></span></a>") . uiTextSnippet('popupnote1');
        if ($pedigree['popupchartlinks']) {
          echo "&nbsp;&nbsp;{$pedigree['chartlink']} &nbsp; " . uiTextSnippet('popupnote2');
        }
      }
      echo ")</span>";
    }
    ?>
    <br>
    <div align="left" style="position:relative;margin-top:8px" id="outer">
      <div id="loading"><img src="img/spinner.gif" alt=""> <?php echo uiTextSnippet('loading'); ?></div>
      <?php echo $boxes; ?>

      <table width="<?php echo($pedigree['borderwidth'] + ($pedigree['maxwidth'] - $pedigree['boxHsep']) + $pedigree['shadowoffset'] + $pedigree['leftindent'] + $offpageimgw + 3); ?>"
             style="height: <?php echo(20 + $pedigree['borderwidth'] + ($pedigree['maxheight'] - $pedigree['boxVsep']) + $pedigree['shadowoffset']); ?>px;">
        <tr>
          <td></td>
        </tr>
      </table>
    </div>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script>
    var lastpopup = '';
    var tree = '<?php echo $tree ?>';
    var tnglitbox;
    var slotceiling = <?php echo $pedmax ?>;
    var slotceiling_minus1 = <?php echo ( pow( 2, $generations - 1 ) ) ?>;
    var display = '<?php echo $display ?>';
    var pedcellpad = <?php echo $pedigree['cellpad'] ?>;
    var pedboxalign = '<?php echo $pedigree['boxalign'] ?>';
    var usepopups = <?php echo $pedigree['usepopups_real'] ?>;
    var popupchartlinks = <?php echo $pedigree['popupchartlinks'] ?>;
    var popupkids = <?php echo $pedigree['popupkids'] ?>;
    var popupspouses = <?php echo $pedigree['popupspouses'] ?>;
    var popuptimer = <?php echo $pedigree['popuptimer'] ?>;
    var pedborderwidth = <?php echo $pedigree['borderwidth'] ?>;
    var pedbordercolor = '<?php echo $pedigree['bordercolor'] ?>';
    var pedbullet = '<?php echo $pedigree['bullet'] ?>';
    var emptycolor = '<?php echo $pedigree['emptycolor'] ?>';
    var hideempty = <?php echo $pedigree['hideempty'] ?>;
    var leftarrowimg = '<?php echo $pedigree['leftarrowlink'] ?>';
    var namepad = '<?php echo $namepad ?>';
    var allow_add = <?php echo $allow_add ?>;
    var allow_edit = <?php echo $allow_edit ?>;
    var chartlink = '<?php echo $pedigree['chartlink'] ?>';
    var personID = '<?php echo $personID ?>';
    var parentset = <?php echo $parentset ?>;
    var generations = <?php echo $generations ?>;
    var tngprint = <?php echo $tngprint ?>;
    if (allow_edit) {
      var allow_cites = true;
      var allow_notes = true;
    }
    var unknown = textSnippet('unknownlit');

    var families = new Array(),
      people = new Array(),
      endslots = new Array(),
      slots = new Array();
    var endslotctr;
    var firstperson = '',
      topparams = '',
      botparams = '',
      toplinks = '',
      botlinks = '';
  </script>
  <script src='js/tngpedigree.js'></script>
  <?php if($allow_edit || $allow_add) { ?>
    <script src='js/tngpededit.js'></script>
  <?php } ?>
  
  <script src="js/rpt_utils.js"></script>
  <script>
    for (var c = 1; c < slotceiling; c++) {
      var slot = document.getElementById('box' + c);
      slot.oldcolor = slot.style.backgroundColor;
    }
    getNewChart(personID, generations, parentset);

    <?php if ($needperson && $allow_add) { ?>
    var nplitbox;
    function openCreatePersonForm() {
      tnglitbox = new ModalDialog('admin_newperson2.php?tree=<?php echo $tree; ?>&needped=1');
//      generateID('person', document.npform.personID, '');
      $('#firstname').focus();
      return false;
    }
    <?php } ?>

    $(document).ready(function () {
      <?php if ($generations > 4 && !$tngprint) { ?>
      $('html, body').animate({scrollTop: $('#box1').offset().top<?php echo $scrolldown; ?>}, 'slow');
      <?php 
      }
      if ($needperson && $allow_add) {
      ?>
      openCreatePersonForm();
      <?php } ?>
    });
  </script>

  <?php if ($allow_edit || $allow_add) { ?>
    <script>
      var preferEuro = <?php echo($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : "false"); ?>;
      var preferDateFormat = '<?php echo $preferDateFormat; ?>';
    </script>
    <script src="js/selectutils.js"></script>
    <script src="js/associations.js"></script>
    <script src='js/families.js'></script>
    <script src="js/datevalidation.js"></script>
  <?php } ?>
</body>
</html>