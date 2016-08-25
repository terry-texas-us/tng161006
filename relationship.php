<?php
require 'tng_begin.php';

require $subroot . 'pedconfig.php';
require 'pedbox.php';
require 'prefixes.php';

$totalRelationships = 0;
$needmore = true;

if (!isset($disallowspouses)) {
  $disallowspouses = 0;
}
if (!isset($pedigree['maxrels']) || !$pedigree['maxrels']) {
  $pedigree['maxrels'] = 10;
}
if (!$maxrels || $maxrels > $pedigree['maxrels']) {
  $maxrels = $pedigree['maxrels'];
}

if (!isset($pedigree['maxupgen']) || !$pedigree['maxupgen']) {
  $pedigree['maxupgen'] = 15;
}
if (!$generations || $generations > $pedigree['maxupgen']) {
  $generations = $pedigree['maxupgen'];
}
$maxupgen = $generations;

$slot = 0;

class Relationship
{

  var $upcount = 0;
  var $downcount = 0;
  var $downarray = [];
  var $uparray = [];
  var $match = 0;
  var $spouseflag = 0;
  var $spouses = 0;
  var $firstone = 1;
  var $offsetH = 0;
  var $offsetV = -1;
  var $split = 0;
  var $half = false;
  var $multparents = 0;
  var $multparent1, $multspouse1, $multspouse2;
  var $uplist = [];
  var $upptr = 0;

  function reset() {
    $this->match = 0;
    $this->spouseflag = 0;
    $this->spouses = 0;
    $this->firstone = 1;
    $this->offsetH = 0;
    $this->offsetV = -1;
    $this->half = false;
  }

  function printRelationshipSentence() {
    global $gender1;
    global $gender2;
    global $namestr;
    global $namestr2;

    $spousemsg = "";
    $name1 = $namestr;
    $name2 = $namestr2;
    $g1 = $gender1;
    $g2 = $gender2;

    if ($this->spouses) {
      $relmsg = "$namestr " . uiTextSnippet('and') . " $namestr2 " . uiTextSnippet('spouses');
    } else {
      if ($this->split || $this->multparents) { //(tree is split at the top)
        //cousins or siblings or aunt/uncle
        //echo "up=" . $this->upcount . ", down=" . $this->downcount;
        if ($this->downcount == 1 && $this->upcount == 1) {
          $msgarray = $this->half ? [uiTextSnippet('halfbrother'), uiTextSnippet('halfsister'), uiTextSnippet('halfsibling')] : [uiTextSnippet('brother'), uiTextSnippet('sister'), uiTextSnippet('sibling')];
          $relmsg = $this->getRelMsg($this->spouseflag, $name1, $g1, $name2, $g2, $msgarray);
        } elseif (!$this->downcount) {
          if ($this->upcount == 1) {
            if ($g1 == 'M') {
              $reldesc = uiTextSnippet('stepson');
            } elseif ($g1 == 'F') {
              $reldesc = uiTextSnippet('stepdau');
            } else {
              $reldesc = uiTextSnippet('stepchild');
            }
          } elseif ($this->upcount > 1) {
            $greats = $this->upcount - 2;
            if ($g1 == 'M') {
              $reldesc = $greats ? uiTextSnippet('gstepgson') : uiTextSnippet('stepgson');
            } elseif ($g1 == 'F') {
              $reldesc = $greats ? uiTextSnippet('gstepgdau') : uiTextSnippet('stepgdau');
            } else {
              $reldesc = $greats ? uiTextSnippet('gstepgchild') : uiTextSnippet('stepgchild');
            }

            $greatmsg = $greats > 1 ? "$greats x" : "";
            $reldesc = preg_replace("/xxx/", $greatmsg, $reldesc);
          } else {
            $reldesc = "[undetermined relationship]"; //shouldn't ever get here.
          }
          $relmsg = "$name1 " . uiTextSnippet('is') . " $reldesc $name2";
        } elseif ($this->upcount == 1) {
          $greats = $this->downcount - 2;
          $relarray = $greats ? [uiTextSnippet('guncle'), uiTextSnippet('gaunt'), uiTextSnippet('guncleaunt')] : [uiTextSnippet('uncle'), uiTextSnippet('aunt'), uiTextSnippet('uncleaunt')];
          $greatmsg = $greats > 1 ? "$greats x" : "";
          $relmsg = $this->getRelMsg($this->spouseflag, $name1, $g1, $name2, $g2, $relarray);
          $relmsg = preg_replace("/xxx/", $greatmsg, $relmsg);
        } elseif ($this->downcount == 1) {
          $greats = $this->upcount - 2;
          $relarray = $greats ? [uiTextSnippet('gnephew'), uiTextSnippet('gniece'), uiTextSnippet('gnephnc')] : [uiTextSnippet('nephew'), uiTextSnippet('niece'), uiTextSnippet('nephnc')];
          $greatmsg = $greats > 1 ? "$greats x" : "";
          $relmsg = $this->getRelMsg($this->spouseflag, $name1, $g1, $name2, $g2, $relarray);
          $relmsg = preg_replace("/xxx/", $greatmsg, $relmsg);
        } else {
          //they're cousins
          $cousins = $this->downcount <= $this->upcount ? $this->downcount - 1 : $this->upcount - 1;
          //get sex of person1 to determine male cousin or female cousin (for languages with gender)
          $cousinmsg = $cousins > 1 ? "$cousins x" : "";
          $msgarray = $this->half ? [uiTextSnippet('mhalfcousin'), uiTextSnippet('fhalfcousin'), uiTextSnippet('halfcousin')] : [uiTextSnippet('mcousin'), uiTextSnippet('fcousin'), uiTextSnippet('cousin')];
          $relmsg = $this->getRelMsg($this->spouseflag, $name1, $g1, $name2, $g2, $msgarray);
          $relmsg = preg_replace("/xxx/", $cousinmsg, $relmsg);
          $removed = abs($this->downcount - $this->upcount);
          if ($removed > 1) {
            $remmsg = " $removed " . uiTextSnippet('removed');
          } elseif ($removed == 1) {
            $remmsg = " " . uiTextSnippet('oneremoved');
          } else {
            $remmsg = "";
          }
          $relmsg = preg_replace("/yyy/", $remmsg, $relmsg);
        }
      } else {
        //direct relationship
        //echo "up=" . $this->upcount . ", down=" . $this->downcount;
        if ($this->downcount == 2 || $this->upcount == 1) {
          //son/daughter (get sex of person ?)
          $thisgender = $g1;
          if ($this->spouseflag) {
            $reldesc = $g1 == 'M' ? uiTextSnippet('fil') : ($g1 == 'F' ? uiTextSnippet('mil') : uiTextSnippet('fmil'));
          } else {
            if ($this->downcount == 2) {
              if ($thisgender == 'M') {
                $reldesc = uiTextSnippet('fathof');
              } elseif ($thisgender == 'F') {
                $reldesc = uiTextSnippet('mothof');
              } else {
                $reldesc = uiTextSnippet('parof');
              }
            } else {
              if ($thisgender == 'M') {
                $reldesc = uiTextSnippet('son');
              } elseif ($thisgender == 'F') {
                $reldesc = uiTextSnippet('daughter');
              } else {
                $reldesc = uiTextSnippet('child');
              }
            }
          }
          $relmsg = "$name1 " . uiTextSnippet('is') . " $reldesc $name2";
        } else {
          //great grandson/great granddaughter
          if ($this->downcount) {
            $greats = $this->downcount - 3;
            $relarray = $greats ? [uiTextSnippet('ggfath'), uiTextSnippet('ggmoth'), uiTextSnippet('ggpar')] : [uiTextSnippet('gfath'), uiTextSnippet('gmoth'), uiTextSnippet('gpar')];
          } else {
            $greats = $this->upcount - 2;
            $relarray = $greats ? [uiTextSnippet('ggson'), uiTextSnippet('ggdau'), uiTextSnippet('ggsondau')] : [uiTextSnippet('gson'), uiTextSnippet('gdau'), uiTextSnippet('gsondau')];
          }
          $greatmsg = $greats > 1 ? "$greats x" : "";
          $relmsg = $this->getRelMsg($this->spouseflag, $name1, $g1, $name2, $g2, $relarray);
          $relmsg = preg_replace("/xxx/", $greatmsg, $relmsg);
        }
      }
    }
    echo "<p>" . $relmsg . "</p>\n";
  }

  function getRelMsg($spouseflag, $namestr, $gender1, $namestr2, $gender2, $messages) {
    if ($spouseflag == 1) {
      $spousemsg = $gender2 == 'M' ? uiTextSnippet('rwife') : ($gender2 == 'F' ? uiTextSnippet('rhusband') : uiTextSnippet('rspouse'));
    } elseif ($spouseflag == 2) { //same sex relationship
      $spousemsg = $gender2 == 'M' ? uiTextSnippet('rhusband') : ($gender2 == 'F' ? uiTextSnippet('rwife') : uiTextSnippet('rspouse'));
    }
    if ($gender1 == 'M') {
      $reldesc = $messages['0'];
    } elseif ($gender1 = 'F') {
      $reldesc = $messages['1'];
    } else {
      $reldesc = $messages['2'];
    }
    return "$namestr " . uiTextSnippet('is') . " $reldesc $spousemsg $namestr2";
  }

  function switchGender($gender) {
    return $gender == 'M' ? 'F' : ($gender == 'F' ? 'M' : $gender);
  }

  function buildUp[] {
    $this->uparray = [];
    $index = $this->upptr;
    $upcount = $this->upcount - 1;
    while ($upcount >= 0) {
      $nextcouple = $this->uplist[$upcount][$index];
      //$nextcouple = array($couple['person'],$couple['spouse']);
      $index = $nextcouple['childindex'];
      unset($nextcouple['childindex']);
      array_push($this->uparray, $nextcouple);
      $upcount--;
    }
  }

}

if ($secondpersonID) {
  if (is_numeric($secondpersonID)) {
    $secondpersonID = $personprefix . $secondpersonID . $personsuffix;
  }
  $relatepersonID = $_SESSION['relatepersonID'] = cleanIt($secondpersonID);
  $relatetreeID = $_SESSION['relatetreeID'] = $tree;
} else {
  $secondpersonID = $savedpersonID;
}
$secondpersonID = strtoupper($secondpersonID);

if ($altprimarypersonID) {
  $primarypersonID = $altprimarypersonID;
}

if (is_numeric($primarypersonID)) {
  $primarypersonID = $personprefix . $primarypersonID . $personsuffix;
}
$primarypersonID = strtoupper($primarypersonID);

$result = getPersonDataPlusDates($primarypersonID);
if (tng_num_rows($result)) {
  $row = tng_fetch_assoc($result);
  $rightbranch = checkbranch($row['branch']);
  $rights = determineLivingPrivateRights($row, $rightbranch);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  $namestr = getName($row);
  $logname = $tngconfig['nnpriv'] && $row['private'] ? uiTextSnippet('private') : ($nonames && $row['living'] ? uiTextSnippet('living') : $namestr);
  $gender1 = $row['sex'];

  $treeResult = getTreeSimple();
  $treerow = tng_fetch_assoc($treeResult);
  $disallowgedcreate = $treerow['disallowgedcreate'];
  tng_free_result($treeResult);
} else {
  $error = $primarypersonID;
}
tng_free_result($result);

$result2 = getPersonSimple($secondpersonID);
if (tng_num_rows($result2)) {
  $row2 = tng_fetch_assoc($result2);
  $rights2 = determineLivingPrivateRights($row2);
  $row2['allow_living'] = $rights2['living'];
  $row2['allow_private'] = $rights2['private'];
  $namestr2 = getName($row2);
  $logname2 = $tngconfig['nnpriv'] && $row2['private'] ? uiTextSnippet('private') : ($nonames && $row2['living'] ? uiTextSnippet('living') : $namestr2);
  $gender2 = $row2['sex'];
} else {
  $error = $secondpersonID;
}
tng_free_result($result2);

writelog("<a href=\"relationship.php?altprimarypersonID=$primarypersonID&amp;secondpersonID=$secondpersonID\">" .
        uiTextSnippet('relcalc') . ": $logname ($primarypersonID) =&gt;$logname2 ($secondpersonID)</a>");
preparebookmark("<a href=\"relationship.php?altprimarypersonID=$primarypersonID&amp;secondpersonID=$secondpersonID\">" .
        uiTextSnippet('relcalc') . ": $namestr ($primarypersonID) =&gt;$namestr2 ($secondpersonID)</a>");

$pedigree['url'] = "pedigree.php?";
$pedigree['cellpad'] = 5;

if ($pedigree['inclphotos'] && (trim($photopath) == "" || trim($photosext) == "")) {
  $pedigree['inclphotos'] = false;
}
if (file_exists("img/Chart.gif")) {
  $chartlinkimg = getimagesize("img/Chart.gif");
  $pedigree['chartlink'] = "<img src=\"img/Chart.gif\" $chartlinkimg[3] title=\"" . uiTextSnippet('popupnote2') . "\" alt=''>";
} else {
  $pedigree['chartlink'] = "<span><b>P</b></span>";
}
if ($pedigree['usepopups'] == 1) {
  $pedigree['display'] = "standard";
} elseif ($pedigree['usepopups'] == 0) {
  $pedigree['display'] = "box";
} else {
  $pedigree['display'] = "compact";
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
$pedigree['colorshift'] = round($pedigree['colorshift'] / 100 * $extreme / 4);

function drawCouple($couple, $topflag, $linedown) {
  global $pedigree, $gens;

  $drawpersonID = $couple['person'];
  $gens->offsetH = $topflag ? $pedigree['leftindent'] + $pedigree['puboxwidth'] + $pedigree['boxHsep'] : $pedigree['leftindent'];

  if ($drawpersonID) {
    drawBox($drawpersonID, 0, $topflag);
    if ($gens->firstone) {
      //short line below box of first couple
      if ($linedown) {
        echo "<div class=\"boxborder\" style=\"background-color:{$pedigree['bordercolor']}; top:" . ($gens->offsetV + $pedigree['puboxheight'] - intval($pedigree['linewidth'] / 2)) . "px;left:" . ($gens->offsetH + intval($pedigree['puboxwidth'] / 2)) . "px;height:" . (2 * $pedigree['boxVsep']) . "px;width:{$pedigree['linewidth']}px;\"></div>\n";
        echo "<div class=\"boxshadow\" style=\"background-color:{$pedigree['shadowcolor']}; top:" . ($gens->offsetV + $pedigree['puboxheight'] - intval($pedigree['linewidth'] / 2) + $pedigree['shadowoffset'] + 1) . "px;left:" . ($gens->offsetH + intval($pedigree['puboxwidth'] / 2) + $pedigree['shadowoffset'] + 1) . "px;height:" . (2 * $pedigree['boxVsep']) . "px;width:{$pedigree['linewidth']}px;\"></div>\n";
      }
      $gens->firstone = 0;
    } else {
      //line coming up from top of other boxes
      echo "<div class=\"boxborder\" style=\"background-color:{$pedigree['bordercolor']}; top:" . ($gens->offsetV - (2 * $pedigree['boxVsep']) - intval($pedigree['linewidth'] / 2)) . "px;left:" . ($gens->offsetH + intval($pedigree['puboxwidth'] / 2)) . "px;height:" . (2 * $pedigree['boxVsep']) . "px;width:{$pedigree['linewidth']}px;\"></div>\n";
      echo "<div class=\"boxshadow\" style=\"background-color:{$pedigree['shadowcolor']}; top:" . ($gens->offsetV - (2 * $pedigree['boxVsep']) - intval($pedigree['linewidth'] / 2) + $pedigree['shadowoffset'] + 1) . "px;left:" . ($gens->offsetH + intval($pedigree['puboxwidth'] / 2) + $pedigree['shadowoffset'] + 1) . "px;height:" . (2 * $pedigree['boxVsep']) . "px;width:{$pedigree['linewidth']}px;\"></div>\n";
    }
  }
  $spouseID = $couple['spouse'];
  if ($spouseID) {
    echo "<div class=\"boxborder\" style=\"background-color:{$pedigree['bordercolor']}; top:" . ($gens->offsetV + intval($pedigree['puboxheight'] / 2) - intval($pedigree['linewidth'] / 2)) . "px;left:" . ($gens->offsetH + $pedigree['puboxwidth']) . "px;height:{$pedigree['linewidth']}px;width:" . (intval($pedigree['boxHsep'] / 2) + 1) . "px;z-index:3;overflow:hidden;\"></div>\n";
    echo "<div class=\"boxshadow\" style=\"background-color:{$pedigree['shadowcolor']}; top:" . ($gens->offsetV + intval($pedigree['puboxheight'] / 2) - intval($pedigree['linewidth'] / 2) + $pedigree['shadowoffset'] + 1) . "px;left:" . ($gens->offsetH + $pedigree['puboxwidth'] + $pedigree['shadowoffset'] + 1) . "px;height:{$pedigree['linewidth']}px;width:" . (intval($pedigree['boxHsep'] / 2) + 1) . "px;\"></div>\n";
    $gens->offsetH = $gens->offsetH + $pedigree['puboxwidth'] + intval($pedigree['boxHsep'] / 2);
    drawBox($spouseID, 1, $topflag);
  }
  if ($topflag) {
    //long connecting line
    echo "<div class=\"boxborder\" style=\"background-color:{$pedigree['bordercolor']}; top:" . ($gens->offsetV + $pedigree['puboxheight'] + (2 * $pedigree['boxVsep']) - intval($pedigree['linewidth'] / 2)) . "px;left:" . ($pedigree['leftindent'] + intval($pedigree['puboxwidth'] / 2)) . "px;height:{$pedigree['linewidth']}px;width:" . ((2 * $pedigree['puboxwidth']) + $pedigree['boxHsep'] + $pedigree['leftindent'] + 1) . "px;\"></div>\n";
    echo "<div class=\"boxshadow\" style=\"background-color:{$pedigree['shadowcolor']}; top:" . ($gens->offsetV + $pedigree['puboxheight'] + (2 * $pedigree['boxVsep']) - intval($pedigree['linewidth'] / 2) + $pedigree['shadowoffset'] + 1) . "px;left:" . ($pedigree['leftindent'] + intval($pedigree['puboxwidth'] / 2) + $pedigree['shadowoffset'] + 1) . "px;height:{$pedigree['linewidth']}px;width:" . ((2 * $pedigree['puboxwidth']) + $pedigree['boxHsep'] + $pedigree['leftindent'] + 1) . "px;\"></div>\n";
    $gens->offsetV += (2 * $pedigree['boxVsep']);
  }
  echo "\n\n";
}

function drawBox($drawpersonID, $spouseflag, $topflag) {
  global $gens;
  global $pedigree;
  global $personID1;
  global $primarypersonID;
  global $slot;

  if ($spouseflag && !$topflag) {
    $boxcolortouse = getColor(1);
  } else {
    $boxcolortouse = getColor(0);
  }

  $namefontsztouse = $pedigree['boxnamesize'] - 2;
  $pedigree['begnamefont'] = "<span style=\"font-size:$namefontsztouse" . "pt\">";
  $pedigree['endfont'] = "</span>";

  $result = getPersonData($drawpersonID);
  if ($result) {
    $row = tng_fetch_assoc($result);

    $rights = determineLivingPrivateRights($row);
    $row['allow_living'] = $rights['living'];
    $row['allow_private'] = $rights['private'];
    $nameinfo_org = getName($row);
    if ($drawpersonID == $personID1 || $drawpersonID == $primarypersonID) {
      $nameinfo = "<strong>$nameinfo_org</strong>";
    } else {
      $nameinfo = $nameinfo_org;
    }
    $pedigree['namelink'] = "<a href=\"peopleShowPerson.php?personID={$row['personID']}\">$nameinfo</a>";

    $newoffset = $spouseflag ? $gens->offsetV : $gens->offsetV + $pedigree['puboxheight'] + 2 * $pedigree['boxVsep'];
    $gens->offsetV = $gens->offsetV == -1 ? 0 : $newoffset;

    //$gens->offsetV = 200;
    if ($row['famc'] && $pedigree['popupchartlinks']) {
      $iconactions = " onmouseover=\"if($('ic$slot')) $('ic$slot').style.display='';\" onmouseout=\"if($('ic$slot')) $('ic$slot').style.display='none';\"";
      $iconlinks = "<div class=\"floverlr\" id=\"ic$slot\" style=\"left:" . ($pedigree['puboxwidth'] - 35) . "px;top:" . ($pedigree['puboxheight'] - 15) . "px;display:none;background-color:$boxcolortouse\">";
      $iconlinks .= "<a href=\"{$pedigree['url']}personID=$drawpersonID&amp;display=standard&amp;generations=" . $pedigree['initpedgens'] . "\" title=\"" . uiTextSnippet('popupnote2') . "\">{$pedigree['chartlink']}</a>\n";
      $iconlinks .= "</div>\n";
      $slot++;
    } else {
      $iconactions = $iconlinks = "";
    }
    echo "<div class='pedbox rounded10' style=\"background-color:$boxcolortouse; top:" . $gens->offsetV . "px; left:" . $gens->offsetH . "px; height:{$pedigree['puboxheight']}" . "px; width:{$pedigree['puboxwidth']}" . "px;border:{$pedigree['borderwidth']}px solid {$pedigree['bordercolor']};\"$iconactions>\n";
    $tableheight = $pedigree['puboxheight'];
    echo "$iconlinks<table cellpadding=\"5\" align=\"{$pedigree['puboxalign']}\" class=\"pedboxtable\"><tr>";

    // implant a picture (maybe)
    if ($pedigree['inclphotos']) {
      $photohtouse = $pedigree['puboxheight'] - ($pedigree['cellpad'] * 2); // take cellpadding into account
      $photoInfo = getPhotoSrc($row['personID'], $rights['both'], $row['sex']);
      if ($photoInfo['ref']) {
        $imagestr = "<img src=\"{$photoInfo['ref']}\" style=\"max-height:{$photohtouse}px;max-width:{$photohtouse}px\" alt='' class=\"smallimg\">";
        if ($photoInfo['link']) {
          $imagestr = "<a href=\"{$photoInfo['link']}\">$imagestr</a>";
        }
        echo "<td class=\"lefttop\">$imagestr</td>";
      }
    }

    // name info
    echo "<td align=\"{$pedigree['puboxalign']}\" class=\"pboxname\" style=\"height:$tableheight\">{$pedigree['begnamefont']}" . $pedigree['namelink'] . $pedigree['endfont'];

    echo "</td></tr>\n";
    echo "</table></div>\n";
    //end box

    echo "<div class='rounded10' style=\"position:absolute; background-color:{$pedigree['shadowcolor']}; top:" . ($gens->offsetV - $pedigree['borderwidth'] + $pedigree['shadowoffset']) . "px;left:" . ($gens->offsetH - $pedigree['borderwidth'] + $pedigree['shadowoffset']) . "px;height:" . ($pedigree['puboxheight'] + (2 * $pedigree['borderwidth'])) . "px;width:" . ($pedigree['puboxwidth'] + (2 * $pedigree['borderwidth'])) . "px;z-index:1\"></div>\n";
    //keep track of total chart height
    tng_free_result($result);
  }
}

function doMultSpouse($prispouse1, $prispouse2, $otherspouse) {
  global $pedigree, $gens;

  echo "<div style=\"position:absolute;background-color:{$pedigree['bordercolor']}; top:" . ($gens->offsetV - intval($pedigree['linewidth'] / 2)) . "px;left:" . ($pedigree['leftindent'] + intval($pedigree['puboxwidth'] / 2)) . "px;height:{$pedigree['linewidth']}px;width:" . ((2 * $pedigree['puboxwidth']) + $pedigree['boxHsep'] + $pedigree['leftindent'] + 1) . "px;z-index:3;overflow:hidden;\"></div>\n";
  echo "<div style=\"position:absolute;background-color:{$pedigree['shadowcolor']}; top:" . ($gens->offsetV - intval($pedigree['linewidth'] / 2) + $pedigree['shadowoffset'] + 1) . "px;left:" . ($pedigree['leftindent'] + intval($pedigree['puboxwidth'] / 2) + $pedigree['shadowoffset'] + 1) . "px;height:{$pedigree['linewidth']}px;width:" . ((2 * $pedigree['puboxwidth']) + $pedigree['boxHsep'] + $pedigree['leftindent'] + 1) . "px;z-index:1;overflow:hidden;\"></div>\n";
  $gens->offsetV -= $pedigree['puboxheight'];

  $couple['person'] = $prispouse1;
  $couple['spouse'] = $prispouse2;
  $gens->firstone = 0;
  drawCouple($couple, 0, 0);
  $saveindent = $pedigree['leftindent'];
  $pedigree['leftindent'] += (2 * $pedigree['puboxwidth']) + $pedigree['boxHsep'] + $pedigree['leftindent'];
  $couple['spouse'] = $otherspouse;
  $gens->offsetV -= $pedigree['puboxheight'] + 2 * $pedigree['boxVsep'];
  drawCouple($couple, 0, 0);
  $pedigree['leftindent'] = $saveindent;
  $gens->half = true;
}

function finishRelationship($couple) {
  global $pedigree, $totalRelationships, $needmore, $gens, $maxrels;

  if ($totalRelationships) {
    echo "<hr/><br><br>\n";
  }

  echo "<div id=\"tngchart\" align=\"left\" style=\"position:relative;\">\n";

  //if( $gens->upcount || $gens->downcount || $gens->spouses || $gens->split ) {
  $downarray = $gens->downarray;
  $upcount = $gens->upcount;
  $downcount = $gens->downcount;

  if ($gens->split) {
    drawCouple(array_shift($downarray), 1, 1);
  } elseif ($gens->multparents) {
    doMultSpouse($gens->multparent1, $gens->multspouse1, $gens->multspouse2);
  } elseif ($upcount || $gens->spouses) {
    drawCouple($couple, 0, 0);
  } else {
    $downcount -= 1;
  }

  $maxwidth = $pedigree['borderwidth'] + $pedigree['puboxwidth'] + $pedigree['boxHsep'] + $saveindent;
  $maxwidth = $gens->upcount ? 2 * $maxwidth : $maxwidth;
  $maxheight = $pedigree['borderwidth'] + $pedigree['puboxheight'] + (2 * $pedigree['boxVsep']);
  if (!$gens->spouses) {
    $maxheight = $upcount > $downcount ? ($upcount + 1) * $maxheight : ($downcount + 1) * $maxheight;
  }
  if ($gens->split || $gens->multparents) {
    $maxheight += $pedigree['borderwidth'] + (2 * $pedigree['boxVsep']);
  }
  echo "<table style=\"width:{$maxwidth}px; height:{$maxheight}px\"><tr><td></td></tr></table>\n";

  //if they're not spouses of each other, draw the rest of the boxes
  $downarray = array_reverse($downarray);
  $uparray = $gens->uparray;

  $getoffsetV = $gens->offsetV;
  if (!$gens->spouses) {
    $saveindent = $pedigree['leftindent'];
    $index = $gens->upptr;
    if ($upcount) {
      $upcount = $upcount - 1;
      while ($upcount >= 0) {
        $nextcouple = $gens->uplist[$upcount][$index];
        //$nextcouple = array($couple['person'],$couple['spouse']);
        $index = $nextcouple['childindex'];
        drawCouple($nextcouple, 0, 1);
        $upcount--;
      }
      $pedigree['leftindent'] += (2 * $pedigree['puboxwidth']) + $pedigree['boxHsep'] + $pedigree['leftindent'];
      $gens->offsetV = $getoffsetV;
    }

    if (count($downarray)) {
      while ($nextcouple = array_pop($downarray)) {
        drawCouple($nextcouple, 0, 1);
      }
    }

    $pedigree['leftindent'] = $saveindent;
  }

  $gens->printRelationshipSentence();

  $gens->reset();
  $totalRelationships++;
  if ($totalRelationships >= $maxrels) {
    $needmore = false;
  }

  echo "</div>\n";
}

function checkOtherSpouse($parentrow, $parent, $spouse) {
  global $gens;
  global $needmore;

  if ($parentrow[$parent] && $needmore) {
    $osresult = getSpouseFamilyMinimalExcept($parent, $parentrow[$parent], $spouse, $parentrow[$spouse]);

    //save husband and wife in $gens
    $gens->multparent1 = $parentrow[$parent];
    $gens->multspouse1 = $parentrow[$spouse];
    while ($needmore && $osrow = tng_fetch_assoc($osresult)) {
      $gens->multspouse2 = $osrow[$spouse];

      $gens->downarray = [];
      $gens->downcount = 0;

      //is it the new spouse? We already know it isn't the main parent
      checkParent($osrow, $spouse, $parent); //switched here

      $childresult = getChildrenMinimal($osrow['familyID']);

      while ($needmore && $childrow = tng_fetch_assoc($childresult)) {
        checkpersondown($childrow['personID']);
      }
      tng_free_result($childresult);

      $gens->multspouse2 = "";
    }
    $gens->multparent1 = "";
    $gens->multspouse1 = "";
    tng_free_result($osresult);
  }
}

function checkParent($parentrow, $parent, $spouse) {
  global $targetID;

  if ($parentrow[$parent] == $targetID) {
    $couple['person'] = $parentrow[$parent];
    $couple['spouse'] = $parentrow[$spouse];

    finishRelationship($couple);
    $rval = true;
  } else {
    $rval = false;
  }

  return $rval;
}

//check ancestors of person1 to see if you find person2
function checkpersonup($nextcouple) {
  global $maxupgen;
  global $gens;
  global $needmore;

  $checkpersonID = $nextcouple['person'];
  $spouseID = $nextcouple['spouse'];

  $lastup = $gens->upcount;
  $gens->upcount += 1;
  $gens->buildUp[];

  if (!isset($gens->uplist[$gens->upcount])) {
    $gens->uplist[$gens->upcount] = [];
  }
  $gensup = $gens->upcount;

  $familyresult = getChildFamily($checkpersonID, "ordernum");
  while ($familyrow = tng_fetch_assoc($familyresult)) {
    $parentsresult = getFamilyMinimal($familyrow['familyID']);

    $gens->downarray = [];
    $gens->downcount = 0;
    $parentrow = tng_fetch_assoc($parentsresult);
    if (!checkParent($parentrow, 'husband', 'wife') && !checkParent($parentrow, 'wife', 'husband')) {
      //push this couple on the $downarray. should be first one.
      if ($parentrow['husband']) {
        $couple['person'] = $parentrow['husband'];
        $couple['spouse'] = $parentrow['wife'];
      } else {
        $couple['person'] = $parentrow['wife'];
        $couple['spouse'] = "";
      }
      array_push($gens->downarray, $couple);
      //we're going down again, so we're assuming cousins or siblings
      $gens->split = 1;

      $childresult = getChildrenMinimalExcept($familyrow['familyID'], $checkpersonID);
      while ($needmore && $childrow = tng_fetch_assoc($childresult)) {
        checkpersondown($childrow['personID']);
      }
      tng_free_result($childresult);
      $gens->split = 0;

      //now we need to check children of other spouses of each parent
      //find all families where the husband is the same but the wife is different
      $gens->multparents = 1;
      checkOtherSpouse($parentrow, 'husband', 'wife');
      checkOtherSpouse($parentrow, 'wife', 'husband');
      $gens->multparents = 0;
      //if found, then draw original parents on left, other parents to right with line connecting the common spouse
    }
    if ($parentrow['husband'] && ($gensup < $maxupgen)) {
      array_push($gens->uplist[$gensup], ["person" => $parentrow['husband'], "spouse" => $parentrow['wife'], "childindex" => $gens->upptr]);
    }
    if ($parentrow['wife'] && ($gensup < $maxupgen)) {
      array_push($gens->uplist[$gensup], ["person" => $parentrow['wife'], "spouse" => $parentrow['husband'], "childindex" => $gens->upptr]);
    }

    tng_free_result($parentsresult);
  }
  tng_free_result($familyresult);

  /* exp */
  if ($needmore) {
    $gens->upptr++;
    //echo "upx=" . $gens->upcount . ", ptr=" . $gens->upptr . ", lastup=$lastup<br>";
    //debugPrint($gens->uplist[$lastup]);
    if ($gens->upptr < count($gens->uplist[$lastup])) {
      $nextcouple = $gens->uplist[$lastup][$gens->upptr];
      $gens->upcount--;
      //echo "up=" . $gens->upcount . ", ptr=" . $gens->upptr . "<br>";
      //debugPrint($nextcouple);
    } elseif (is_array($gens->uplist[$gensup])) {
      $gens->upptr = 0;
      $nextcouple = $gens->uplist[$gensup][$gens->upptr];
      //debugPrint($nextcouple);
      //  echo "done<br>";
    }

    if ($nextcouple) {
      checkpersonup($nextcouple);
    }
  }
  /* exp */

  //array_pop($gens->uparray);
  //echo "popped it<br>";
  //$gens->upcount -= 1;
}

//check descendants of person1 to see if you find person2
function checkpersondown($checkpersonID) {
  global $targetID;
  global $maxupgen;
  global $gens;
  global $otherID;
  global $disallowspouses;
  global $needmore;

  $gens->downcount += 1;
  $gensdown = $gens->downcount;
  //if( $gensdown > $lowest ) $lowest = $gensdown;
  //echo "down: person=$checkpersonID, target=$targetID, down=$gensdown, up = $gensup<br>\n";
  //check person
  if ($checkpersonID != $targetID) {
    //get sex of each individual
    $result = getPersonGender($checkpersonID);
    if ($result) {
      $row = tng_fetch_assoc($result);
      if ($row['sex'] == 'M') {
        $spouse = 'wife';
        $self = 'husband';
        $spouseorder = 'husborder';
      } else {
        if ($row['sex'] == 'F') {
          $spouse = 'husband';
          $self = 'wife';
          $spouseorder = 'wifeorder';
        } else {
          $spouseorder = "";
        }
      }
      if ($spouseorder) {
        //get spouses -- for each spouse
        $spouseresult = getSpousesSimple($self, $checkpersonID, $spouse, $spouseorder);

        while ($needmore && $spouserow = tng_fetch_assoc($spouseresult)) {
          //build couple
          $couple['person'] = $checkpersonID;
          $couple['spouse'] = $spouserow[$spouse];

          $revcouple['person'] = $spouserow[$spouse];
          $revcouple['spouse'] = $checkpersonID;
          //push couple on downarray here
          //don't push couple after, like previously done
          $pushed = false;
          if (!in_array($couple, $gens->uparray) && !in_array($revcouple, $gens->uparray)) {
            array_push($gens->downarray, $couple);
            $pushed = true;
          }
          //pop it off again after all children checked
          //check spouse
          if ($spouserow[$spouse] != $targetID || $disallowspouses) {
            //get children -- for each child
            if ($pushed) {
              if ($gensdown < $maxupgen) {
                $childresult = getChildrenMinimal($spouserow['familyID']);
                while ($needmore && $childrow = tng_fetch_assoc($childresult)) {
                  checkpersondown($childrow['personID']);
                }
                tng_free_result($childresult);
              }
              array_pop($gens->downarray);
            }
          } elseif ($pushed) {
            $gens->match = 1;
            //echo "got match 1, spouse=" . $spouserow[$spouse] . ", lowest=$lowest, sec=$secondpersonID<br>\n";
            $gens->spouseflag = $row['sex'] != $spouserow['sex'] ? 1 : 2;
            if ($gens->downcount == 1 && !$gensup && $spouserow[$spouse] == $targetID && $checkpersonID == $otherID) {
              $gens->spouses = 1;
            } else {
              $couple = $revcouple;
            }
            //echo "d=$gens[down], u=$gens[up], s={$spouserow[$spouse]}, targ=$targetID, check=$checkpersonID, oth=$otherID";
            finishRelationship($couple);
            array_pop($gens->downarray);
          }
        }
        tng_free_result($spouseresult);
      }
      tng_free_result($result);
    }
  } else {
    $gens->downcount = $gensdown;
    //echo "got match 2<br>\n";
    $gens->match = 1;
    $couple['person'] = $checkpersonID;
    $couple['spouse'] = "";
    array_push($gens->downarray, $couple);
    finishRelationship($couple);
    array_pop($gens->downarray);
  }
  $gens->downcount -= 1;
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

function swapPeople() {
  global $namestr, $namestr2, $gender1, $gender2;

  $namestr3 = $namestr2;
  $namestr2 = $namestr;
  $namestr = $namestr3;
  $gender3 = $gender2;
  $gender2 = $gender1;
  $gender1 = $gender3;
}

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('relcalc'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<?php
echo "<body id='public'>\n";
  echo "<section class='container'>\n";
    echo $publicHeaderSection->build();

    $photostr = showSmallPhoto($primarypersonID, $namestr, $rights['both'], 0, false, $row['sex']);
    echo tng_DrawHeading($photostr, $namestr, getYears($row));

    $innermenu = uiTextSnippet('rels') . ": &nbsp;";
    $innermenu .= "<select name=\"maxrels\" class=\"small\">\n";
    for ($i = 1; $i <= $pedigree['maxrels']; $i++) {
      $innermenu .= "<option value=\"$i\"";
      if ($i == $maxrels) {
        $innermenu .= " selected";
      }
      $innermenu .= ">$i</option>\n";
    }
    $innermenu .= "</select>&nbsp;&nbsp;&nbsp;\n";

    $innermenu .= uiTextSnippet('dospouses2') . ": &nbsp;";
    $innermenu .= "<select name=\"disallowspouses\" class=\"small\">\n";
    $innermenu .= "<option value=\"0\"";
    if (!$disallowspouses) {
      $innermenu .= " selected";
    }
    $innermenu .= ">" . uiTextSnippet('yes') . "</option>\n";
    $innermenu .= "<option value='1'";
    if ($disallowspouses) {
      $innermenu .= " selected";
    }
    $innermenu .= ">" . uiTextSnippet('no') . "</option>\n";
    $innermenu .= "</select>&nbsp;&nbsp;&nbsp;\n";

    $innermenu .= uiTextSnippet('generations') . ": &nbsp;";
    $innermenu .= "<select name=\"generations\" class=\"small\">\n";
    for ($i = 1; $i <= $pedigree['maxupgen']; $i++) {
      $innermenu .= "<option value=\"$i\"";
      if ($i == $generations) {
        $innermenu .= " selected";
      }
      $innermenu .= ">$i</option>\n";
    }
    $innermenu .= "</select>&nbsp;&nbsp;&nbsp;\n";
    $innermenu .= "<a href='#' onclick=\"document.form1.submit();\">" . uiTextSnippet('refresh') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
    $innermenu .= "<a href=\"relateform.php?primaryID=$primarypersonID\">" . uiTextSnippet('findanother') . "</a>\n";

    beginFormElement("relationship2", "get", "form1", "form1");
    echo buildPersonMenu("relate", $primarypersonID);
    echo "<div class='pub-innermenu small'>\n";
      echo $innermenu;
    echo "</div>\n";
    echo "<br>\n";
    echo "<input name='primarypersonID' type='hidden' value=\"$primarypersonID\" />\n";
    echo "<input name='savedpersonID' type='hidden' value=\"$secondpersonID\" />\n";
    endFormElement();
    ?>
      <div id="searching"><img src="img/spinner.gif" alt=""> <?php echo uiTextSnippet('searching'); ?></div>
      <h4><?php echo uiTextSnippet('relateto') . " $namestr2"; ?></h4>
    <?php
    if ($error) {
      echo "<p>$error " . uiTextSnippet('notvalid') . "</p>\n";
    } elseif ($primarypersonID == $secondpersonID) {
      echo "<p>" . uiTextSnippet('sameperson') . "</p>\n";
    } else {
      $gens = new Relationship();

      $personID1 = $secondpersonID;
      $targetID = $secondpersonID;
      $otherID = $primarypersonID;

      checkpersondown($primarypersonID);
      $couple['person'] = $primarypersonID;
      $couple['spouse'] = "";
      $couple['childindex'] = 0;
      $gens->uplist[0] = [];
      array_push($gens->uplist[0], $couple); //exp

      checkpersonup($couple);

      if (!$totalRelationships) {
        $gens = new Relationship();

        $personID1 = $primarypersonID;
        $targetID = $primarypersonID;
        $otherID = $secondpersonID;

        swapPeople();

        checkpersondown($secondpersonID);
        $couple['person'] = $secondpersonID;
        $couple['spouse'] = "";
        $couple['childindex'] = 0;
        $gens->uplist[0] = [];
        array_push($gens->uplist[0], $couple);

        checkpersonup($couple);
      }

      if (!$totalRelationships) {
        $newstr = preg_replace("/xxx/", $generations, uiTextSnippet('notrelated'));
        echo "<p>$newstr</p>\n";
      }
    }
    ?>
    <?php echo $publicFooterSection->build(); ?>
  <?php
  echo "</section> <!-- .container -->";
  echo scriptsManager::buildScriptElements($flags, 'public');
  ?>
  <script>
    $(document).ready(function () {
      $('#searching').hide();
    });
  </script>
</body>
</html>
