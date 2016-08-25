<?php
require 'tng_begin.php';

require $subroot . 'pedconfig.php';
require 'personlib.php';

if (!$personID) {
  die("no args");
}
if ($display == "textonly" || (!$display && !$pedigree['defdesc'])) {
  header("Location: descendtext.php?personID=$personID&amp;generations=$generations");
  exit;
} elseif ($display == "register" || (!$display && $pedigree['defdesc'] == 1)) {
  header("Location: register.php?personID=$personID&amp;generations=$generations");
  exit;
}
require 'pedbox.php';

if ($pedigree['defdesc'] == "") {
  $pedigree['defdesc'] = 2;
}
if (!$display) {
  if ($pedigree['defdesc'] == 2) {
    $display = "standard";
  } else {
    $display = "compact";
  }
}

$rounded = $display == "compact" ? "rounded4" : 'rounded10';
$slot = 0;

function setTopMarker($level, $value, $debug) {
  global $topmarker;

  //echo "level=$level, old=" . $topmarker[$level] . ", new=$value ($debug)<br>";
  $topmarker[$level] = $value;
}
$pedigree['cellpad'] = 5;
$topmarker = [];
$botmarker = [];
$spouses_for_next_gen = [];
$maxwidth = 0;
$maxheight = 0;
$starttop = [];
$needtop = [];
$numboxes = 0;

$arrdnpath = $rootpath . $endrootpath . "img/ArrowDown.gif";
if (file_exists($arrdnpath)) {
  $downarrow = getimagesize($arrdnpath);
  $pedigree['downarroww'] = $downarrow[0];
  $pedigree['downarrowh'] = $downarrow[1];
  $pedigree['downarrow'] = "<img src=\"" . "img/ArrowDown.gif\" width=\"{$pedigree['downarroww']}\" height=\"{$pedigree['downarrowh']}\" alt=''>";
} else {
  $pedigree['downarrow'] = "";
}

$arrrtpath = $rootpath . $endrootpath . "img/ArrowRight.gif";
if (file_exists($arrrtpath)) {
  $offpageimg = getimagesize($arrrtpath);
  $pedigree['offpagelink'] = "<img src=\"" . "img/ArrowRight.gif\" $offpageimg[3] alt=\"" . uiTextSnippet('popupnote3') . "\">";
  $pedigree['offpageimgw'] = $offpageimg[0];
  $pedigree['offpageimgh'] = $offpageimg[1];
} else {
  $pedigree['offpagelink'] = "<b>&gt;</b>";
}

$arrltpath = $rootpath . $endrootpath . "img/ArrowRight.gif";
if (file_exists($arrltpath)) {
  $leftarrowimg = getimagesize($arrltpath);
  $pedigree['leftarrowimgw'] = $leftarrowimg[0];
  $pedigree['leftarrowimgh'] = $leftarrowimg[1];
  $pedigree['leftarrowlink'] = "<img src=\"" . "img/ArrowLeft.gif\" $leftarrowimg[3] title=\"" .
          uiTextSnippet('popupnote3') . "\" alt=\"" . uiTextSnippet('popupnote3') . "\" style=\"margin-right:5px\"/>";
  $pedigree['leftindent'] += $pedigree['leftarrowimgw'] + $pedigree['shadowoffset'] + 6;
} else {
  $pedigree['leftarrowlink'] = "<b>&lt;</b>";
  $pedigree['leftindent'] += 16 + $pedigree['shadowoffset'];
}


if ($display == "compact") {
  $pedigree['inclphotos'] = 0;
  $pedigree['usepopups'] = 0;
  $pedigree['boxHsep'] = 15;
  $pedigree['boxheight'] = 16;
  $pedigree['boxnamesize'] = 8;
  $pedigree['cellpad'] = 0;
  $pedigree['boxwidth'] -= 50;
  $pedigree['boxVsep'] = 5;
  $pedigree['shadowoffset'] = 1;
  $pedigree['spacer'] = "&nbsp;";
  $pedigree['gendalign'] = -2;
  $spouseoffset = 20;
  $pedigree['diff'] = $pedigree['boxheight'] + $pedigree['boxVsep'] + $pedigree['linewidth'];
} else {
  $pedigree['boxnamesize'] = 10;
  $pedigree['usepopups'] = 1;
  $pedigree['boxheight'] = $pedigree['puboxheight'];
  $pedigree['boxwidth'] = $pedigree['puboxwidth'];
  $pedigree['boxalign'] = $pedigree['puboxalign'];
  $pedigree['spacer'] = "";
  $pedigree['gendalign'] = -1;
  $spouseoffset = 40;
  $pedigree['diff'] = $pedigree['boxheight'] + $pedigree['boxVsep'] + $pedigree['linewidth'] + $pedigree['downarrowh'];
}

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
$pedigree['colorshift'] = round($pedigree['colorshift'] / 100 * $extreme / 5);
$pedigree['url'] = "pedigree.php?";

//$pedigree[boxcolor] = getColor(1);

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

function getParents($personID) {
  global $pedigree;
  global $display;
  global $generations;

  $parentinfo = "";
  $result = getChildParentsFamilyMinimal($personID);
  while ($parents = tng_fetch_assoc($result)) {
    if ($parents['husband']) {
      $presult = getPersonSimple($parents['husband']);
      $husband = tng_fetch_assoc($presult);
      $rights = determineLivingPrivateRights($husband);
      $husband['allow_living'] = $rights['living'];
      $husband['allow_private'] = $rights['private'];
      $husband['name'] = getName($husband);

      $parentinfo .= "<tr><td><a href=\"descend.php?personID={$parents['husband']}&amp;generations=$generations&amp;display=$display\">{$pedigree['leftarrowlink']}<span>{$husband['name']}</span></a> " . getGenderIcon('M', $pedigree['gendalign']) . "</td></tr>\n";
      tng_free_result($presult);
    }
    if ($parents['wife']) {
      $presult = getPersonSimple($parents['wife']);
      $wife = tng_fetch_assoc($presult);
      $rights = determineLivingPrivateRights($wife);
      $wife['allow_living'] = $rights['living'];
      $wife['allow_private'] = $rights['private'];
      $wife['name'] = getName($wife);

      $parentinfo .= "<tr><td><a href=\"descend.php?personID={$parents['wife']}&amp;generations=$generations&amp;display=$display\">{$pedigree['leftarrowlink']}<span>{$wife['name']}</span></a> " . getGenderIcon('F', $pedigree['gendalign']) . "</td></tr>\n";
      tng_free_result($presult);
    }
  }
  tng_free_result($result);

  return $parentinfo;
}

function getNewChart($personID) {
  global $generations;
  global $display;

  return $kidsflag ? "<a href=\"descend.php?personID=$personID&amp;generations=$generations&amp;display=$display\"><img src=\"img/dchart.gif\" width='10' height='9' alt=\"" . uiTextSnippet('popupnote3') . "\"></a>" : "";
}

function doBox($level, $person, $spouseflag, $kidsflag) {
  global $pedigree;
  global $topmarker;
  global $botmarker;
  global $spouseoffset;
  global $maxwidth;
  global $maxheight;
  global $personID;
  global $generations;
  global $display;
  global $numboxes;
  global $rounded;
  global $slot;

  $numboxes++;
  if (!$topmarker[$level]) {
    setTopMarker($level, 0, "initialize, 183");
  }
  $top = $topmarker[$level];
  if ($top > $maxheight) {
    $maxheight = $top;
  }
  $topmarker[$level] += $pedigree['diff'];
  $left = $pedigree['leftindent'] + ($pedigree['boxwidth'] + $pedigree['boxHsep'] + $spouseoffset) * ($level - 1);
  $farleft = $left + $pedigree['boxwidth'] + $pedigree['boxHsep'] + $spouseoffset;
  if ($spouseflag) {
    $left += $spouseoffset;
    $bgcolor = getColor(3);
  } else {
    $botmarker[$level] = $top;
    $bgcolor = getColor(1);
  }
  if ($farleft > $maxwidth) {
    $maxwidth = $farleft;
  }
  $boxstr = "";
  if ($person['personID'] == $personID) {
    $parentinfo = getParents($personID);
    if ($parentinfo) {
      //do the arrow
      $adjleft = $left - ($pedigree['leftarrowimgw'] + $pedigree['shadowoffset'] + 6);
      $boxstr .= "<div id=\"leftarrow\" style=\"position:absolute; top:" . ($top + intval(($pedigree['boxheight'] - $pedigree['offpageimgh']) / 2) + 1) . "px; left:$adjleft" . "px;z-index:5;\">\n";
      $boxstr .= "<a href=\"javascript:goBack();\">{$pedigree['leftarrowlink']}</a></div>\n";
      //set top
      $boxstr .= "<div id=\"popupleft\" class=\"popup\" style=\"position:absolute; visibility:hidden; background-color:{$pedigree['popupcolor']}; top:" . ($top + $pedigree['borderwidth'] + intval(($pedigree['boxheight'] - $pedigree['offpageimgh']) / 2) + 1) . "px; left:$adjleft" . "px;z-index:8\" onmouseover=\"cancelTimer('left')\" onmouseout=\"setTimer('left')\">\n";
      $boxstr .= "<div>\n<div class=\"popinner\">\n<div class=\"pboxpopupdiv\">\n";
      $boxstr .= "<table><tr><td><table cellpadding=\"1\">\n";
      $boxstr .= "<tr><td class=\"pboxpopup\"><b>" . uiTextSnippet('parents') . "</b></td></tr>\n$parentinfo\n</table></td></tr></table>\n</div>\n</div>\n</div>\n</div>\n";
    }
  }
  if ($person['famc'] && $pedigree['popupchartlinks']) {
    $iconactions = " onmouseover=\"if($('#ic$slot').length) $('#ic$slot').show();\" onmouseout=\"if($('#ic$slot').length) $('#ic$slot').hide();\"";
    $iconlinks = "<div class=\"floverlr\" id=\"ic$slot\" style=\"left:" . ($pedigree['puboxwidth'] - 35) . "px;top:" . ($pedigree['puboxheight'] - 15) . "px;display:none;background-color:$bgcolor\">";
    $iconlinks .= "<a href=\"{$pedigree['url']}personID={$person['personID']}&amp;display=standard&amp;generations=" . $pedigree['initpedgens'] . "\" title=\"" . uiTextSnippet('popupnote1') . "\">{$pedigree['chartlink']}</a>\n";
    $iconlinks .= "</div>\n";
    $slot++;
  } else {
    $iconactions = $iconlinks = "";
  }
  $boxstr .= "<div class=\"pedbox $rounded\" id=\"box$numboxes\" style=\"background-color:$bgcolor; top:" . $top . "px; left:" . ($left - $pedigree['borderwidth']) . "px; height:" . $pedigree['boxheight'] . "px; width:{$pedigree['boxwidth']}" . "px; border:{$pedigree['borderwidth']}px solid {$pedigree['bordercolor']};\"$iconactions>\n";
  $boxstr .= "$iconlinks<table align=\"center\" cellpadding=\"{$pedigree['cellpad']}\" class=\"pedboxtable\"><tr>";

  // implant a picture (maybe)
  if ($pedigree['inclphotos'] && $pedigree['usepopups']) {
    $photohtouse = $pedigree['boxheight'] - ($pedigree['cellpad'] * 2); // take cellpadding into account
    //$photoinfo = showSmallPhoto( $person['personID'], $person['name'], $person['allow_living'], $photohtouse );
    $photoInfo = getPhotoSrc($person['personID'], $person['allow_living'] && $person['allow_private'], $person['sex']);
    if ($photoInfo['ref']) {
      $imagestr = "<img src=\"{$photoInfo['ref']}\" style=\"max-height:{$photohtouse}px;max-width:{$photohtouse}px\" alt='' class=\"smallimg\">";
      if ($photoInfo['link']) {
        $imagestr = "<a href=\"{$photoInfo['link']}\">$imagestr</a>";
      }
      $boxstr .= "<td class=\"lefttop\">$imagestr</td>";
    }
  }
  // name info
  if ($person['name']) {
    $boxstr .= "<td class=\"pboxname\" align=\"{$pedigree['boxalign']}\"><span style=\"font-size:{$pedigree['boxnamesize']}" . "pt;\">{$pedigree['spacer']}<a href=\"peopleShowPerson.php?personID={$person['personID']}" . "\">{$person['name']}</a> " . getGenderIcon($person['sex'], $pedigree['gendalign']) . getNewChart($person['personID']) . "</span></td></tr></table></div>\n";
  } else {
    $boxstr .= "<td class=\"pboxname\"><span style=\"font-size:{$pedigree['boxnamesize']}" . "pt;\">" . uiTextSnippet('unknownlit') . "</span></td></tr></table></div>\n";
  }
  $boxstr .= "<div class=\"shadow $rounded\" style=\"top:" . ($top + $pedigree['shadowoffset']) . "px;left:" . ($left - $pedigree['borderwidth'] + $pedigree['shadowoffset']) . "px;height:" . ($pedigree['boxheight'] + (2 * $pedigree['borderwidth'])) . "px;width:" . ($pedigree['boxwidth'] + (2 * $pedigree['borderwidth'])) . "px;z-index:1\"></div>\n";

  if ($display != "compact" && $pedigree['usepopups']) {
    $vitalinfo = getVitalDates($person);
    if ($vitalinfo) {
      $boxstr .= "<div style=\"position: absolute; top:" . ($top + $pedigree['boxheight'] + (2 * $pedigree['borderwidth']) + $pedigree['shadowoffset'] + 1) . "px;left:" . ($left + intval(($pedigree['boxwidth'] - $pedigree['downarroww']) / 2) - 1) . "px;z-index:7;\" class=\"fakelink\">";
      $boxstr .= "<a href='#' onmouse{$pedigree['event']}=\"showPopup($numboxes,$top," . $pedigree['boxheight'] . ")\">" . $pedigree['downarrow'] . "</a></div>";

      $boxstr .= "<div class=\"popup\" id=\"popup$numboxes\" style=\"position:absolute; visibility:hidden; background-color:{$pedigree['popupcolor']}; left:" . ($left - $pedigree['borderwidth'] + round($pedigree['shadowoffset'] / 2)) . "px;z-index:8\" onmouseover=\"cancelTimer($numboxes)\" onmouseout=\"setTimer($numboxes)\">\n";
      $boxstr .= "<div><div class=\"popinner\"><div class=\"pboxpopupdiv\">\n<table cellpadding=\"1\" width=\"100%\">\n";
      $boxstr .= "$vitalinfo\n</table></div></div></div></div>\n";
    }
  }
  if (!$spouseflag && $person['personID'] != $personID) {
    $boxstr .= "<div class=\"boxborder\" style=\"top:" . ($top + intval($pedigree['boxheight'] / 2) - intval($pedigree['linewidth'] / 2)) . "px;left:" . ($left - intval($pedigree['boxHsep'] / 2)) . "px;height:" . $pedigree['linewidth'] . "px;width:" . (intval($pedigree['boxHsep'] / 2) + 2) . "px;z-index:3;overflow:hidden\"></div>\n";
    $boxstr .= "<div class=\"shadow\" style=\"top:" . ($top + intval($pedigree['boxheight'] / 2) - intval($pedigree['linewidth'] / 2) + $pedigree['shadowoffset'] + 1) . "px;left:" . (($left - intval($pedigree['boxHsep'] / 2)) + $pedigree['shadowoffset'] + 1) . "px;height:" . $pedigree['linewidth'] . "px;width:" . (intval($pedigree['boxHsep'] / 2) + 2) . "px;z-index:1;overflow:hidden\"></div>\n";
  }
  if ($spouseflag) {
    $boxstr .= "<div class=\"boxborder\" style=\"top:" . ($top + intval($pedigree['boxheight'] / 2) - intval($pedigree['linewidth'] / 2)) . "px;left:" . ($left - intval($spouseoffset / 2)) . "px;height:" . $pedigree['linewidth'] . "px;width:" . (intval($spouseoffset / 2) + 2) . "px;z-index:3;overflow:hidden\"></div>\n";
    $boxstr .= "<div class=\"shadow\" style=\"top:" . ($top + intval($pedigree['boxheight'] / 2) - intval($pedigree['linewidth'] / 2) + $pedigree['shadowoffset'] + 1) . "px;left:" . (($left - intval($spouseoffset / 2)) + $pedigree['shadowoffset'] + 1) . "px;height:" . $pedigree['linewidth'] . "px;width:" . (intval($spouseoffset / 2) + 2) . "px;z-index:1;overflow:hidden\"></div>\n";
    if ($kidsflag) {
      if ($level < $generations) {
        $boxstr .= "<div class=\"boxborder\" style=\"top:" . ($top + intval($pedigree['boxheight'] / 2) - intval($pedigree['linewidth'] / 2)) . "px;left:" . ($left + $pedigree['boxwidth']) . "px;height:" . $pedigree['linewidth'] . "px;width:" . (intval($pedigree['boxHsep'] / 2) + 1) . "px;z-index:3;overflow:hidden\"></div>\n";
        $boxstr .= "<div class=\"shadow\" style=\"top:" . ($top + intval($pedigree['boxheight'] / 2) - intval($pedigree['linewidth'] / 2) + $pedigree['shadowoffset'] + 1) . "px;left:" . ($left + $pedigree['boxwidth'] + $pedigree['shadowoffset'] + 1) . "px;height:" . $pedigree['linewidth'] . "px;width:" . (intval($pedigree['boxHsep'] / 2) + 1) . "px;z-index:1;overflow:hidden\"></div>\n";
      } else {
        $boxstr .= "<div style=\"position: absolute; top:" . ($top + $pedigree['borderwidth'] + intval(($pedigree['boxheight'] - $pedigree['offpageimgh']) / 2) + 1) . "px;left:" . ($left + $pedigree['boxwidth'] + $pedigree['borderwidth'] + $pedigree['shadowoffset'] + 3) . "px;z-index:5\">\n";
        $boxstr .= "<a href=\"descend.php?personID=$spouseflag&amp;generations=$generations&amp;display=$display\" title=\"" . uiTextSnippet('popupnote3') . "\">{$pedigree['offpagelink']}</a></div>\n";
      }
    }
  }

  return $boxstr;
}

function doIndividual($person, $level) {
  global $generations;
  global $pedigree;
  global $chart;
  global $topmarker;
  global $botmarker;
  global $vslots;
  global $spouseoffset;
  global $needtop;
  global $starttop;
  global $spouses_for_next_gen;

  //look up person
  $result = getPersonData($person);
  if ($result) {
    $row = tng_fetch_assoc($result);
    $rights = determineLivingPrivateRights($row);
    $row['allow_living'] = $rights['living'];
    $row['allow_private'] = $rights['private'];
    $row['name'] = getName($row);
  }
  tng_free_result($result);

  //get gender-related info
  if ($row['sex'] == 'M') {
    $self = 'husband';
    $spouse = 'wife';
    $spouseorder = 'husborder';
  } else {
    if ($row['sex'] == 'F') {
      $self = 'wife';
      $spouse = 'husband';
      $spouseorder = 'wifeorder';
    } else {
      $self = $spouse = $spouseorder = "";
    }
  }
  //look up spouse-families
  if ($spouse) {
    $result = getSpouseFamilyMinimal($self, $person, $spouseorder);
  } else {
    $result = getSpouseFamilyMinimalUnion($person);
  }
  $marrtot = tng_num_rows($result);
  if ($spouse && !$marrtot) {
    $result = getSpouseFamilyMinimalUnion($person);
    $self = $spouse = $spouseorder = "";
  }
  //for each family
  $needperson = 1;
  $spousecount = 0;
  while ($famrow = tng_fetch_assoc($result)) {
    //get starting offset
    //do box for main spouse (if not already done)
    $spousecount++;
    $originaltop = $topmarker[$level];
    //get children

    $result2 = getChildrenMinimal($famrow['familyID']);
    $numkids = tng_num_rows($result2);
    if ($level < $generations) {

      if ($numkids) {
        $needtop[$level + 1] = 1;
        $childleft = $pedigree['leftindent'] + ($pedigree['boxwidth'] + $pedigree['boxHsep'] + $spouseoffset) * $level;
        while ($crow = tng_fetch_assoc($result2)) {
          //recurse on each child (next level)
          doIndividual($crow['personID'], $level + 1);
        }
        if ($numkids > 1) {
          $vheight = $botmarker[$level + 1] - $starttop[$level + 1];
        } elseif ($needperson) {
          $vheight = $pedigree['diff'] + 1;
        } else {
          $vheight = 0;
        }
        if ($numkids == 1 && $spousecount < 2 && !$spouses_for_next_gen[$level + 1]) {
          for ($i = $level + 1; $i <= $generations; $i++) {
            setTopMarker($i, $topmarker[$i] + $pedigree['diff'], "lowering previous gens, 348");
          }
        }
        if ($vheight) {
          $chart .= "<div class=\"boxborder\" style=\"top:" . ($starttop[$level + 1] + intval($pedigree['boxheight'] / 2) - intval($pedigree['linewidth'] / 2)) . "px;left:" . ($childleft - intval($pedigree['boxHsep'] / 2)) . "px;height:" . $vheight . "px;width:" . $pedigree['linewidth'] . "px;z-index:3\"></div>\n";
          $chart .= "<div class=\"shadow\" style=\"top:" . ($starttop[$level + 1] + intval($pedigree['boxheight'] / 2) - intval($pedigree['linewidth'] / 2) + $pedigree['shadowoffset'] + 1) . "px;left:" . ($childleft - intval($pedigree['boxHsep'] / 2) + $pedigree['shadowoffset'] + 1) . "px;height:" . $vheight . "px;width:" . $pedigree['linewidth'] . "px;z-index:1\"></div>\n";
        }
        tng_free_result($result2);
        setTopMarker($level, $starttop[$level + 1] + intval($vheight / 2), "increasing, half of box height, 356");
      }
    }
    if ($needperson) {
      //set "top"
      //take number of "vslots" for this family
      if ($numkids && $level < $generations) {
        setTopMarker($level, $topmarker[$level] - intval(($pedigree['diff']) / 2), "decreasing, moving down to center,365");
      }
      if ($needtop[$level]) {
        $starttop[$level] = $topmarker[$level];
        $needtop[$level] = 0;
      }
      $thistop = $topmarker[$level];
      $chart .= doBox($level, $row, 0, 0);
      $needperson = 0;
    }
    //get spouse data (if exists)
    $spouserow = [];
    if (!$spouse) {
      $spouse = $famrow['husband'] == $person ? 'wife' : 'husband';
    }
    if ($famrow[$spouse]) {
      $spouseresult = getPersonData($famrow[$spouse]);
      $spouserow = tng_fetch_assoc($spouseresult);
      $rights = determineLivingPrivateRights($spouserow);
      $spouserow['allow_living'] = $rights['living'];
      $spouserow['allow_private'] = $rights['private'];
      $spouserow['name'] = getName($spouserow);
    } else {
      $spouserow = [];
    }
    //do box for other spouse
    //lines down from primary spouse
    $vheight = $topmarker[$level] - $thistop - intval($pedigree['boxheight'] / 2) - intval($pedigree['linewidth'] / 2);
    $childleft = $pedigree['leftindent'] + ($pedigree['boxwidth'] + $pedigree['boxHsep'] + $spouseoffset) * ($level - 1);
    $chart .= "<div class=\"boxborder\" style=\"top:" . ($thistop + $pedigree['boxheight']) . "px;left:" . ($childleft + intval($spouseoffset / 2)) . "px;height:" . $vheight . "px;width:" . $pedigree['linewidth'] . "px;z-index:3\"></div>\n";
    $chart .= "<div class=\"shadow\" style=\"top:" . ($thistop + $pedigree['boxheight'] + $pedigree['shadowoffset'] + 1) . "px;left:" . ($childleft + intval($spouseoffset / 2) + $pedigree['shadowoffset'] + 1) . "px;height:" . $vheight . "px;width:" . $pedigree['linewidth'] . "px;z-index:1\"></div>\n";
    $thistop = $topmarker[$level] - intval($pedigree['boxheight'] / 2) - intval($pedigree['linewidth'] / 2);
    $chart .= doBox($level, $spouserow, $person, $numkids);

    if ($numkids && $level < $generations) {
      $vkey = $famrow['familyID'] . "-$level";
      setTopMarker($level, $originaltop + ($vslots[$vkey] * $pedigree['diff']), "raising, diff=$pedigree[diff], slots=" . $vslots[$vkey] . ", key=$vkey, 401");
    } else {
      for ($i = $level + 1; $i <= $generations; $i++) {
        setTopMarker($i, $topmarker[$level], "lowering previous gens, no kids, 405");
      }
    }
  }
  $spouses_for_next_gen[$level] = $spousecount;
  //if no family, get starting offset and do box for person and return
  if ($needperson) {
    //set top differently
    if ($needtop[$level]) {
      $starttop[$level] = $topmarker[$level];
      $needtop[$level] = 0;
    }
    $chart .= doBox($level, $row, 0, 0);
    for ($i = $level + 1; $i <= $generations; $i++) {
      setTopMarker($i, $topmarker[$level], "lowering all previous gens, 418");
    }
  }
  tng_free_result($result);
}

function getData($key, $sex, $level) {
  global $generations;
  global $vslots;
  global $vendspouses;

  if ($sex == 'M') {
    $self = 'husband';
    $spouseorder = 'husborder';
  } elseif ($sex == 'F') {
    $self = 'wife';
    $spouseorder = 'wifeorder';
  } else {
    $self = $spouseorder = "";
  }
  $gotafamily = 0;
  $stats = [];
  $stats['slots'] = 0;
  $stats['fams'] = 0;
  $stats['es'] = 0; //end spouses

  if ($self) {
    $result = getSpouseFamilyMinimal($self, $key, $spouseorder);
  } else {
    $result = getSpouseFamilyMinimalUnion($key);
  }
  $stats['fams'] = tng_num_rows($result);
  if ($self && !$stats['fams']) {
    $result = getSpouseFamilyMinimalUnion($key);
    $stats['fams'] = tng_num_rows($result);
  }
  if ($result) {
    while ($row = tng_fetch_assoc($result)) {
      $famslots = 0;
      $fam_es = 0;
      if (!$gotafamily) {
        $spouseslots = 2; //for both spouses, even if only one exists
        $gotafamily = 1;
      } else {
        $spouseslots = 1;
      } //for this spouse only; primary individual not included
      $endspouseslots = 1;

      $result2 = getChildrenMinimalPlusGender($row['familyID']);
      $numkids = tng_num_rows($result2);
      if ($numkids) {
        while ($crow = tng_fetch_assoc($result2)) {
          if ($level < $generations) {
            $kidstats = getData($crow['personID'], $crow['sex'], $level + 1);
            $famslots += $kidstats['slots'];
          }
        }
        $fam_es += $kidstats['es'];
      }

      tng_free_result($result2);
      $famslots = $famslots > $spouseslots ? $famslots : $spouseslots;

      $fam_es = $fam_es > $endspouseslots ? $fam_es : $endspouseslots;
      $stats['slots'] += $famslots;
      $vkey = $row['familyID'] . "-$level";
      $vslots[$vkey] = $famslots;
      //echo "key=$vkey, slots=" . $vslots[$vkey] . "<br>";
      $stats['es'] = $fam_es;
      $vendspouses[$vkey] = $stats[es];
      //echo "fam=$row[familyID], stats = $stats[es], fames=$fames, es=$endspouseslots, slots=$famslots <br>";
    }
  }
  tng_free_result($result);
  if (!$stats['slots']) {
    $stats['slots'] = 1;
    $vkey = $key . "-$level";
    $vslots[$vkey] = 1;
    //echo "key=$vkey, slots=" . $vslots[$vkey] . "<br>";
    $stats['es'] = 0; //do I need this?
    $vendspouses[$vkey] = 0;
  }

  return $stats;
}

function getVitalDates($row) {
  $vitalinfo = "";

  if ($row['allow_living'] && $row['allow_private']) {
    if ($row['birthdate'] || $row['altbirthdate'] || $row['altbirthplace'] || $row['deathdate'] || $row['burialdate'] || $row['burialplace']) {
      $dataflag = 1;
    } else {
      $dataflag = 0;
    }

    // get birthdate info
    if ($row['altbirthdate'] && !$row['birthdate']) {
      $bd = $row['altbirthdate'];
      $bp = $row['altbirthplace'];
      $birthabbr = uiTextSnippet('capaltbirthabbr') . ":";
    } elseif ($dataflag) {
      $bd = $row['birthdate'];
      $bp = $row['birthplace'];
      $birthabbr = uiTextSnippet('capbirthabbr') . ":";
    } else {
      $bd = "";
      $bp = "";
      $birthabbr = "";
    }

    // get death/burial date info
    if ($row['burialdate'] && !$row['deathdate']) {
      $dd = $row['burialdate'];
      $dp = $row['burialplace'];
      $deathabbr = uiTextSnippet('capburialabbr') . ":";
    } elseif ($dataflag) {
      $dd = $row['deathdate'];
      $dp = $row['deathplace'];
      $deathabbr = uiTextSnippet('capdeathabbr') . ":";
    } else {
      $dd = "";
      $dp = "";
      $deathabbr = "";
    }
  } else {
    $bd = $bp = $birthabbr = $dd = $dp = $deathabbr = $md = $mp = $marrabbr = "";
  }
  if ($bd) {
    $vitalinfo .= "<tr>\n<td class=\"pboxpopup\" align=\"right\">$birthabbr</td>\n";
    $vitalinfo .= "<td class=\"pboxpopup\">" . displayDate($bd) . "</td></tr>\n";
    $birthabbr = "&nbsp;";
  }
  if ($bp) {
    $vitalinfo .= "<tr>\n<td class=\"pboxpopup\" align=\"right\">$birthabbr</td>\n";
    $vitalinfo .= "<td class=\"pboxpopup\">$bp</td></tr>\n";
  }
  if ($dd) {
    $vitalinfo .= "<tr>\n<td class=\"pboxpopup\" align=\"right\">$deathabbr</td>\n";
    $vitalinfo .= "<td class=\"pboxpopup\">" . displayDate($dd) . "</td></tr>\n";
    $deathabbr = "&nbsp;";
  }
  if ($dp) {
    $vitalinfo .= "<tr>\n<td class=\"pboxpopup\" align=\"right\">$deathabbr</td>\n";
    $vitalinfo .= "<td class=\"pboxpopup\">$dp</td></tr>\n";
  }
  if ($vitalinfo) {
    $vitalinfo = "<tr>\n<td class=\"pboxpopup\" colspan='2'><strong>" . $row['name'] . "</strong></td></tr>\n" . $vitalinfo;
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
  $row['name'] = getName($row);
  $logname = $tngconfig['nnpriv'] && $row['private'] ? uiTextSnippet('private') : ($nonames && $row['living'] ? uiTextSnippet('living') : $row['name']);
}
$treeResult = getTreeSimple();
$treerow = tng_fetch_assoc($treeResult);
$disallowgedcreate = $treerow['disallowgedcreate'];
$allowpdf = !$treerow['disallowpdf'] || ($allow_pdf && $rightbranch);
tng_free_result($treeResult);

writelog("<a href=\"descend.php?personID=$personID&amp;display=$display\">" . xmlcharacters(uiTextSnippet('descendfor') . " $logname ($personID)") . "</a>");
preparebookmark("<a href=\"descend.php?personID=$personID&amp;display=$display\">" . uiTextSnippet('descendfor') . " " . $row['name'] . " ($personID)</a>");

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('descendfor') . " " . $row['name']);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <style>
    .desc {margin: 0 0 10px 0;}
    .spouse {width: 100%;}
    .shadow {background-color: <?php echo $pedigree['shadowcolor']; ?>; position: absolute;}
    .boxborder {background-color: <?php echo $pedigree['bordercolor'] ?>;}
  </style>
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
      $generations = $pedigree['initdescgens'] > 8 ? 8 : $pedigree['initdescgens'];
    }
    if (!$generations) {
      $generations = 6;
    }
    if ($generations > $pedigree['maxdesc']) {
      $generations = $pedigree['maxdesc'];
    } else {
      $generations = intval($generations);
    }
    for ($i = 0; $i < $generations; $i++) {
      setTopMarker($i, 0, "initializing");
    }
    $innermenu = uiTextSnippet('generations') . ": &nbsp;";
    $innermenu .= "<select name=\"generations\" class=\"small\" onchange=\"window.location.href='descend.php?personID=$personID&amp;display=$display&amp;generations=' + this.options[this.selectedIndex].value\">\n";
    for ($i = 1; $i <= $pedigree['maxdesc']; $i++) {
      $innermenu .= "<option value=\"$i\"";
      if ($i == $generations) {
        $innermenu .= " selected";
      }
      $innermenu .= ">$i</option>\n";
    }
    $innermenu .= "</select>&nbsp;&nbsp;&nbsp;\n";
    $innermenu .= "<a href=\"descend.php?personID=$personID&amp;display=standard&amp;generations=$generations\">" . uiTextSnippet('pedstandard') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"descend.php?personID=$personID&amp;display=compact&amp;generations=$generations\">" . uiTextSnippet('pedcompact') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"descendtext.php?personID=$personID&amp;generations=$generations\">" . uiTextSnippet('pedtextonly') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"register.php?personID=$personID&amp;generations=$generations\">" . uiTextSnippet('regformat') . "</a>\n";
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
    <p>
      (<?php
      echo uiTextSnippet('scrollnote');
      if ($pedigree['usepopups_real']) {
        echo ($pedigree['downarrow'] ? " <img src=\"" . "img/ArrowDown.gif\" width=\"{$pedigree['downarroww']}\" height=\"{$pedigree['downarrowh']}\" alt=''>" : " <a href='#'><b>V</b></a>") . uiTextSnippet('popupnote1');
      }
      ?>)
    </p>
    <?php
    $chart = "";
    getData($key, $row['sex'], 1);
    doIndividual($personID, 1);

    $maxheight += $pedigree['boxheight'] + $pedigree['borderwidth'] + $pedigree['downarroww'];
    $maxwidth += $pedigree['boxwidth'] + $pedigree['borderwidth'] + (2 * $pedigee['offpageimgw']) + 6 + $pedigree['leftindent'];
    ?>
    <div align="left" id="outer" style="position:relative; padding-top:8px; width:100%; height:<?php echo $maxheight > 200 ? $maxheight : 200; ?>px;">
      <?php echo $chart; ?>
    </div>

    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script>
    var tnglitbox;
  </script>
  <script>
    var timerleft = false;

    function goBack() {
      var popupleft = document.getElementById("popupleft");
      popupleft.style.visibility = 'visible';
    }

    function setTimer(slot) {
      eval("timer" + slot + "=setTimeout(\"hidePopup('" + slot + "')\",<?php echo $pedigree['popuptimer']; ?>);");
    }

    function cancelTimer(slot) {
      eval("clearTimeout(timer" + slot + ");");
      eval("timer" + slot + "=false;");
    }

    function hidePopup(slot) {
      var ref = document.all ? document.all["popup" + slot] : document.getElementById ? document.getElementById("popup" + slot) : null;
      if (ref) {
        ref.style.visibility = "hidden";
      }
      eval("timer" + slot + "=false;");
    }
  </script>
  <?php if ($display != "compact" && $pedigree['usepopups']) { ?>
    <script>
      var lastpopup = "";
      for (var h = 1; h <= <?php echo $numboxes; ?>; h++) {
        eval('var timer' + h + '=false');
      }
      function showPopup(slot, tall, high) {
        // hide any other currently visible popups
        if (lastpopup) {
          cancelTimer(lastpopup);
          hidePopup(lastpopup);
        }
        lastpopup = slot;

        // show current
        var ref = $("#popup" + slot);
        var box = $("#box" + slot);

        var vOffset, hOffset, hDisplace;

        if (tall + high < 0)
          vOffset = 0;
        else {
          vOffset = tall + high + 2 * <?php echo $pedigree['borderwidth']; ?>;
          var vDisplace = box.position().top + high + 2 * <?php echo $pedigree['borderwidth']; ?> +ref.height() - $('#outer').height() + 20; //20 is for the scrollbar
          if (vDisplace > 0)
            vOffset -= vDisplace;
        }
        hDisplace = box.position().left + ref.width() - $('#outer').width();
        if (hDisplace > 0)
          ref.offset({left: box.offset().left - hDisplace});
        ref.css('top', vOffset);
        ref.css('z-index', 8);
        ref.css('visibility', 'visible');
      }
    </script>
  <?php } ?>
  <script src="js/rpt_utils.js"></script>
</body>
</html>