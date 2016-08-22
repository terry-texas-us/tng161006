<?php
require 'tng_begin.php';

require $subroot . 'pedconfig.php';
require 'personlib.php';

$timeline = $_SESSION['timeline'];
$tng_message = $_SESSION['tng_message'];
if (!is_array($timeline)) {
  header("Location: timeline.php?primaryID=$primaryID");
  exit;
}
$tlmonths[0] = "";
$tlmonths[1] = uiTextSnippet('JAN');
$tlmonths[2] = uiTextSnippet('FEB');
$tlmonths[3] = uiTextSnippet('MAR');
$tlmonths[4] = uiTextSnippet('APR');
$tlmonths[5] = uiTextSnippet('MAY');
$tlmonths[6] = uiTextSnippet('JUN');
$tlmonths[7] = uiTextSnippet('JUL');
$tlmonths[8] = uiTextSnippet('AUG');
$tlmonths[9] = uiTextSnippet('SEP');
$tlmonths[10] = uiTextSnippet('OCT');
$tlmonths[11] = uiTextSnippet('NOV');
$tlmonths[12] = uiTextSnippet('DEC');

$minwidth = 100;
$maxwidth = 1600;
$lineoffset = 44; //starting column for vertical gray lines (pixels from left)
if ($chartwidth && is_numeric($chartwidth)) {
  if ($chartwidth < $minwidth) {
    $chartwidth = $minwidth;
  } elseif ($chartwidth > $maxwidth) {
    $chartwidth = $maxwidth;
  }
} elseif ($_SESSION['timeline_chartwidth']) {
  $chartwidth = $_SESSION['timeline_chartwidth'];
} elseif ($pedigree['tcwidth']) {
  $chartwidth = $pedigree['tcwidth'];
} else {
  $chartwidth = 500;
} //chart width in pixels (from first to last gray line)
$checkboxcellwidth = 48; //width of table cell holding "delete" checkboxes. If bars do not line up with gray lines, adjust this value up or down accordingly
$divisions = 5; //number of chart segments

$result = getPersonDataPlusDates($primaryID);
if ($result) {
  $row = tng_fetch_assoc($result);
  $rightbranch = checkbranch($row['branch']);
  $rights = determineLivingPrivateRights($row, $rightbranch);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  $namestr = getName($row);
  $logname = $tngconfig['nnpriv'] && $row['private'] ? uiTextSnippet('private') : ($nonames && $row['living'] ? uiTextSnippet('living') : $namestr);
  tng_free_result($result);
}
$treeResult = getTreeSimple();
$treerow = tng_fetch_assoc($treeResult);
$disallowgedcreate = $treerow['disallowgedcreate'];
tng_free_result($treeResult);

function getEvents($person) {
  global $ratio;

  $personID = $person['personID'];
  $tree = $person['tree'];
  $events = array();
  $eventstr = "";
  $leftoffset = 3;
  $maxleft = 0;
  $perswidth = 300;
  $eventwidth = 170;

  //born OR christened
  if ($person['birthdate']) {
    $index = $person['birthdatetr'];
    $events[$index]['date'] = displayDate($person['birthdate']);
    $events[$index]['text'] = uiTextSnippet('born') . ":";
  } elseif ($person['altbirthdate']) {
    $index = $person['altbirthdatetr'];
    $events[$index]['date'] = displayDate($person['altbirthdate']);
    $events[$index]['text'] = uiTextSnippet('christened') . ":";
  }
  $events[$index]['year'] = $person['birth'];
  $events[$index]['left'] = $leftoffset;
  if ($events[$index]['left'] + $eventwidth > $maxleft) {
    $maxleft = $events[$index]['left'] + $eventwidth;
  }
  //custom events
  //marriages
  //get person's gender
  if ($person['sex'] == 'M') {
    $self = 'husband';
    $spouse = 'wife';
    $spouseorder = 'husborder';
  } elseif ($person['sex'] == 'F') {
    $self = 'wife';
    $spouse = 'husband';
    $spouseorder = 'wifeorder';
  } else {
    $self = "";
    $spouse = "";
    $spouseorder = "";
  }
  //get and loop through all marriages (link to people table on opposite spouse) for this person based on gender
  if ($spouseorder) {
    $marriages = getSpouseFamilyDataPlusDates($self, $personID, $spouseorder);
  } else {
    $marriages = getSpouseFamilyDataUnionPlusDates($personID);
  }
  if (!tng_num_rows($marriages) && $spouseorder) {
    $marriages = getSpouseFamilyDataUnionPlusDates($personID);
  }
  while ($marriagerow = tng_fetch_assoc($marriages)) {
    //do event for marriage date and person (observe living rights)
    if (!$spouseorder) {
      $spouse = $marriagerow['husband'] == $personID ? wife : husband;
    }
    unset($spouserow);
    if ($marriagerow[$spouse]) {
      $spouseresult = getPersonSimple($marriagerow[$spouse]);
      $spouserow = tng_fetch_assoc($spouseresult);
      $srights = determineLivingPrivateRights($spouserow);
      $spouserow['allow_living'] = $srights['living'];
      $spouserow['allow_private'] = $srights['private'];
      if ($spouserow['firstname'] || $spouserow['lastname']) {
        $spousename = getName($spouserow);
        $spouselink = "<a href=\"peopleShowPerson.php?personID={$spouserow['personID']}\">$spousename</a>";
      }
      tng_free_result($spouseresult);
    } else {
      $spouselink = "";
    }
    $rightfbranch = checkbranch($marriagerow['branch']) ? 1 : 0;
    $mrights = determineLivingPrivateRights($marriagerow, $rightfbranch);
    $marriagerow['allow_living'] = $mrights['living'];
    $marriagerow['allow_private'] = $mrights['private'];

    if ($mrights['both']) {
      $index = str_replace("/", "-", $marriagerow['marrdatetr']);
      if ($index != "0000-00-00") {
        $events[$index]['date'] = displayDate($marriagerow['marrdate']);
        $events[$index]['text'] = uiTextSnippet('married') . " $spouselink:";
        $events[$index]['year'] = $marriagerow['marryear'];
        $marriagerow['marryear'] = strtok($marriagerow['marryear'], '/');
        $events[$index]['left'] = intval($ratio * ($marriagerow['marryear'] - $person['birth'])) + $leftoffset;
        if ($events[$index]['left'] + $perswidth > $maxleft) {
          $maxleft = $events[$index]['left'] + $perswidth;
        }
      }
    }
    //get all children (link to people) born to this marriage
    //loop through and make event for each
    $children = getChildrenDataPlusDates($marriagerow['familyID']);

    while ($child = tng_fetch_assoc($children)) {
      $crights = determineLivingPrivateRights($child);
      $child['allow_living'] = $crights['living'];
      $child['allow_private'] = $crights['private'];
      if ($crights['both']) {
        if ($child['firstname'] || $child['lastname']) {
          $childname = getName($child);
          $childlink = "<a href=\"peopleShowPerson.php?personID={$child['personID']}\">$childname</a>";
        } else {
          $childlink = "";
        }
        if ($child['birthdate']) {
          $index = str_replace("/", "-", $child['birthdatetr']) . sprintf("%2d", $child['ordernum']);
          $events[$index]['date'] = displayDate($child['birthdate']);
          $events[$index]['text'] = uiTextSnippet('child') . " $childlink " . uiTextSnippet('birthabbr');
        } elseif ($child['altbirthdate']) {
          $index = str_replace("/", "-", $child['altbirthdatetr']) . sprintf("%2d", $child['ordernum']);
          $events[$index]['date'] = displayDate($child['altbirthdate']);
          $events[$index]['text'] = uiTextSnippet('child') . " $childlink " . uiTextSnippet('chrabbr');
        } else {
          $index = "";
        }
        if ($index) {
          $events[$index]['year'] = $child['birth'];
          $child['birth'] = strtok($child['birth'], '/');
          $events[$index]['left'] = intval($ratio * ($child['birth'] - $person['birth'])) + $leftoffset;
          if ($events[$index]['left'] + $perswidth > $maxleft) {
            $maxleft = $events[$index]['left'] + $perswidth;
          }
        }
      }
    }
    tng_free_result($children);
  }
  tng_free_result($marriages);

  //died OR buried
  if ($person['deathdate'] || $person['burialdate']) {
    if ($person['deathdate']) {
      $index = str_replace("/", "-", $person['deathdatetr']);
      $events[$index]['date'] = displayDate($person['deathdate']);
      $events[$index]['text'] = uiTextSnippet('died') . ":";
    } elseif ($person['burialdate']) {
      $index = $person['burialdatetr'];
      $events[$index]['date'] = displayDate($person['burialdate']);
      $events[$index]['text'] = uiTextSnippet('buried') . ":";
    } else {
      $index = "";
    }
    if ($index) {
      $events[$index]['year'] = $person['death'];
      $events[$index]['left'] = intval($ratio * ($person['death'] - $person['birth'])) + $leftoffset;
      if ($events[$index]['left'] + $eventwidth > $maxleft) {
        $maxleft = $events[$index]['left'] + $eventwidth;
      }
    }
  }
  //loop through and format
  ksort($events);
  foreach ($events as $event) {
    //$eventstr .= "<div style=\"position:relative; top:0px; left:$event['left']px; width:$maxleft" . "px;\">\n";
    $eventstr .= "<div class=\"tlevent\" style=\"margin-left:{$event['left']}px;\">\n";
    $eventstr .= "<table cellpadding=\"1\"><tr><td class=\"pboxpopup\"><span>&gt; ";
    $eventstr .= "{$event['year']} - {$event['text']} {$event['date']} &nbsp;</span></td></tr></table></div>\n";
  }

  return $eventstr;
}

writelog("<a href='timeline.php?primaryID=$primaryID'>" . uiTextSnippet('timeline') . " ($logname)</a>");
preparebookmark("<a href='timeline.php?primaryID=$primaryID'>" . uiTextSnippet('timeline') . " ($namestr)</a>");

$keeparray = array();
$earliest = date('Y');
$latest = 0;
foreach ($timeline as $timeentry) {
  parse_str($timeentry);
  $query = "SELECT firstname, lnprefix, lastname, prefix, suffix, nameorder, living, private, branch, sex, IF(birthdatetr !='0000-00-00',YEAR(birthdatetr),YEAR(altbirthdatetr)) as birth,
    IF(deathdatetr !='0000-00-00',YEAR(deathdatetr),YEAR(burialdatetr)) as death, birthdate, birthdatetr, altbirthdate, altbirthdatetr, deathdate, deathdatetr, burialdate, burialdatetr
    FROM $people_table WHERE personID = \"$timeperson\" AND gedcom = \"$timetree\"";
  $result2 = tng_query($query);
  if ($result2) {
    $row2 = tng_fetch_assoc($result2);
    $newtimeentry = array();
    $newtimeentry['personID'] = $timeperson;
    $newtimeentry['tree'] = $timetree;
    $displaydeath = $row2['death'] ? $row2['death'] : "";
    $rights2 = determineLivingPrivateRights($row2);
    $row2['allow_living'] = $rights2['living'];
    $row2['allow_private'] = $rights2['private'];
    $namestr2 = getName($row2);
    if ($rights2['both']) {
      $namestr2 .= " ({$row2['birth']} - $displaydeath)";

      $newtimeentry['birth'] = $row2['birth'];
      if ($row2['death']) {
        $newtimeentry['death'] = $row2['death'];
      } else {
        $defaultage = intval(date("Y")) - intval($row2['birth']);
        $newtimeentry['death'] = intval($row2['birth']) + ($defaultage < 110 ? $defaultage : 110);
      }
      $newtimeentry['lifespan'] = $newtimeentry['death'] - $newtimeentry['birth'];
      $newtimeentry['birthdate'] = $row2['birthdate'];
      $newtimeentry['birthdatetr'] = $row2['birthdatetr'];
      $newtimeentry['altbirthdate'] = $row2['altbirthdate'];
      $newtimeentry['altbirthdatetr'] = $row2['altbirthdatetr'];
      $newtimeentry['deathdate'] = $row2['deathdate'];
      $newtimeentry['deathdatetr'] = $row2['deathdatetr'];
      $newtimeentry['burialdate'] = $row2['burialdate'];
      $newtimeentry['burialdatetr'] = $row2['burialdatetr'];

      if ($newtimeentry['birth'] < $earliest) {
        $earliest = $newtimeentry['birth'];
      }
      if ($newtimeentry['death'] > $latest) {
        $latest = $newtimeentry['death'];
      }
    }
    $newtimeentry['name'] = "<a href=\"peopleShowPerson.php?personID=$timeperson\">$namestr2</a>";
    array_push($keeparray, $newtimeentry);
    tng_free_result($result2);
  }
}

//get all events that fall in time period
//loop through and use year as index in array
//append if duplicate years
$tlquery = "SELECT evday, evmonth, evyear, evtitle, evdetail, endday, endmonth, endyear
    FROM $tlevents_table
    WHERE (evyear <= \"$latest\" AND endyear >= \"$earliest\") OR (endyear = \"\" AND (evyear BETWEEN \"$earliest\" AND \"$latest\"))
    ORDER BY evyear, evmonth, evday";
//WHERE (evyear BETWEEN \"$earliest\" AND \"$latest\") OR (endyear BETWEEN \"$earliest\" AND \"$latest\") OR ((evyear <= \"$earliest\") AND (endyear >= \"$latest\"))

$tlresult = tng_query($tlquery) or die(uiTextSnippet('cannotexecutequery') . ": $tlquery");
$tlevents = array();
$tlevents2 = array();
while ($tlrow = tng_fetch_assoc($tlresult)) {
  $evyear = $tlrow['evyear'];
  if ($evyear < $earliest) {
    $earliest = $evyear;
  }
  if ($tlrow['endyear'] > $latest) {
    $latest = $tlrow['endyear'];
  }
  if ($tlrow['evday'] == "0") {
    $tlrow['evday'] = "";
  }
  if ($tlrow['endday'] == "0") {
    $tlrow['endday'] = "";
  }

  $daymonth = trim($tlrow['evday'] . " " . $tlmonths[$tlrow['evmonth']]);
  $daymonth .= $daymonth ? " " . $evyear : $evyear;

  $enddate = trim($tlrow['endday'] . " " . $tlmonths[$tlrow['endmonth']] . " " . $tlrow['endyear']);
  $enddate = $enddate ? "&mdash;$enddate" : "";

  $newentry = $tlevents[$evyear] ? $tlevents[$evyear] . "\n - " : " - ";
  if ($daymonth || $enddate) {
    $newentry .= $daymonth . $enddate . " ";
  }
  if ($tlrow['evtitle']) {
    $evtitle = $tlrow['evtitle'];
    $evdetail = $tlrow['evdetail'] ? "<br>" . $tlrow['evdetail'] : "";
  } else {
    $evtitle = $tlrow['evdetail'];
    $evdetail = "";
  }
  $tlevents[$evyear] = $newentry . preg_replace("/\"/", "&#34;", $evtitle);
  $newstring = $daymonth || $enddate ? "<li>$daymonth$enddate" . ": $evtitle$evdetail</li>" : "<li>$evtitle$evdetail</li>";
  $tlevents2[$evyear] = $tlevents2[$evyear] ? $tlevents2[$evyear] . "\n$newstring" : $newstring;
}
tng_free_result($tlresult);

if (!$latest && !count($timeline)) {
  $latest = $earliest;
}
$totalspan = $latest - $earliest;
$ratio = $totalspan ? $chartwidth / $totalspan : 0;
$spanheight = 30 + count($keeparray) * 29;

$flags['styles'] = "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/timeline.css\"/>\n";

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('timeline') . ": $namestr");
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<?php
echo "<body id='public'>\n";
echo $publicHeaderSection->build();

$photostr = showSmallPhoto($primaryID, $namestr, $rights['both'], 0, false, $row['sex']);
echo tng_DrawHeading($photostr, $namestr, getYears($row));

beginFormElement("timeline", "post", "form1", "form1");

$innermenu = uiTextSnippet('chartwidth') . ": &nbsp;";
$innermenu .= "<input class='small' name='newwidth' type='text' value=\"$chartwidth\" maxlength='4' size='4'> &nbsp;&nbsp; ";
$innermenu .= "<a href='#' onclick=\"document.form1.submit();\">" . uiTextSnippet('refresh') . "</a>\n";

echo buildPersonMenu("timeline", $primaryID);
echo "<div class='pub-innermenu small'>\n";
  echo $innermenu;
echo "</div>\n";
echo "<br>\n";

echo "<h4>" . uiTextSnippet('timeline') . "</h4><br>\n";

if ($pedigree['simile']) {
  echo "<div id=\"tngtimeline\" style=\"height: {$pedigree['tcheight']}px;\"></div>\n";
}

if ($tng_message) {
  echo "<p><span><strong>$tng_message</strong></span></p>";
  $tng_message = $_SESSION['tng_message'] = "";
}
echo "<div id=\"tngchart\">";

$year = $earliest;
$displayyear = $year;
for ($i = $lineoffset; $i <= ($lineoffset + $chartwidth); $i+=($chartwidth / $divisions)) {
  $iadj = $i - 12;
  echo "<div class=\"yeardiv\" style=\"left:$iadj" . "px;\">";
  if ($pedigree['simile']) {
    echo "<a href='#' onclick=\"return centerTimeline($displayyear);\">$displayyear</a>";
  } else {
    echo $displayyear;
  }
  echo "</div>\n";
  echo "<div class='vertlines cgray' style=\"left:$i" . "px; height:$spanheight" . "px\"></div>\n";
  $year += $totalspan * .2;
  $displayyear = intval($year + .5);
}

$linklevel = 0;
$highestll = 0;
$linkoffset = 35;
$lastyo = -6;

$counter = 0;
foreach ($tlevents as $key => $value) {
  $yearoffset = $lineoffset + ($key - $earliest) * $ratio;
  if ($lastyo + 5 >= $yearoffset) {
    if ($linklevel == 1) {
      $linklevel = 2;
      $linkoffset = 35;
      $highestll = 2;
    } else {
      $linklevel = 1;
      $linkoffset = 50;
    }
  } else {
    $linklevel = 0;
    $linkoffset = 35;
  }
  $lastyo = $yearoffset;
  $linkpos = $linkoffset + $spanheight;
  $eadj = $yearoffset - 3;
  $counter++;
  echo "<div class='vertlines dgray' style=\"left:$yearoffset" . "px; height:$spanheight" . "px\"></div>\n";
  echo "<div class='footnote' style=\"top:$linkpos" . "px;left:$eadj" . "px;\">\n";
    echo "<a href=\"#events\" title=\"$key:\n$value\">$counter</a>\n";
  echo "</div>\n";
}

$enddiv = "</div>";

echo "<span><br><br>\n";
if (count($timeline) > 1) {
  echo uiTextSnippet('delete');
} else {
  echo "&nbsp;";
}
echo "</span>\n";

$top = 20;
$numlines = 0;
foreach ($keeparray as $timeentry) {
  $numlines++;
  $top += 30;
  $spanleft = $lineoffset + intval($ratio * ( $timeentry['birth'] - $earliest ));
  $spanwidth = intval($ratio * $timeentry['lifespan']);
  echo "<div id=\"cb$numlines\" class=\"tlbar cb\" style=\"top:$top" . "px;width:$spanwidth" . "px;\">\n";
  if ($timeentry['personID'] == $primaryID && $timeentry['tree'] == $tree) {
    echo "&nbsp;";
  } else {
    echo "<input name=\"{$timeentry['tree']}_{$timeentry['personID']}\" type='checkbox' value='1'>\n";
  }
  echo "</div>\n";

  echo "<div id=\"bar$numlines\"  class=\"tlbar\" style=\"top:$top" . "px;left:$spanleft" . "px;width:$spanwidth" . "px;\" onmouse{$pedigree['event']}=\"setTimerShow($numlines,'{$pedigree['event']}');\" onmouseout=\"setTimerHide($numlines)\">\n";
  echo "<table><tr><td><span>{$timeentry['name']}</span></td></tr><tr><td><div style=\"font-size:0;height:10px;width:$spanwidth" . "px;z-index:3\"></div></td></tr>\n";
  echo "</table>\n";
  echo "</div>\n";

  echo "<div id=\"popup$numlines\" class=\"popup\" style=\"background-color:{$pedigree['popupcolor']}; top:" . ($top + 25) . "px; left:" . ($spanleft - 5) . "px;\" onmouseover=\"cancelTimer($numlines)\" onmouseout=\"setTimer($numlines)\">\n";
  echo "<table class=\"popuptable\" style=\"border-color: {$pedigree['bordercolor']};\" cellpadding=\"1\"><tr><td>\n";

  $eventinfo = getEvents($timeentry);
  echo "$eventinfo</td></tr></table></div>\n";
}
if ($highestll == 1) {
  echo "<br><br>";
} elseif ($highestll == 2) {
  echo "<br><br><br>";
}
echo "<table width=\"" . ($chartwidth + $lineoffset + 20) . "\" style=\"height:$top" . "px\"><tr><td>&nbsp;</td></tr></table>";
?>

<br><br>
<input name='lines' type='button' value="<?php echo uiTextSnippet('togglelines'); ?>" 
       onclick="toggleLines();" />
<input name='addmore' type='button' value="<?php echo uiTextSnippet('timelineinstr'); ?>" 
       onclick="toggleAddMore();" />
<input type='submit' value="<?php echo uiTextSnippet('refresh'); ?>" />
<div id="addmorediv" style="display:none;">
<?php
echo "<span><br><br>";
$query = "SELECT gedcom, treename FROM $treesTable ORDER BY treename";
$treeresult = tng_query($query);
$numrows = tng_num_rows($treeresult);
$newtime = time();
for ($x = 2; $x < 6; $x++) {
  echo uiTextSnippet('addperson') . ": ";
  if ($numrows > 1) {
    echo "<select name=\"nexttree$x\">\n";
    while ($treerow = tng_fetch_assoc($treeresult)) {
      echo "  <option value=\"{$treerow['gedcom']}\"";
      if ($treerow['gedcom'] == $tree) {
        echo " selected";
      }
      echo ">{$treerow['treename']}</option>\n";
    }
    echo "</select>\n";
    $treestr = "' + document.form1.nexttree$x.options[document.form1.nexttree$x.selectedIndex].value + '";
  } else {
    $treestr = "$tree";
  }
  echo "<input id=\"nextpersonID$x\" name=\"nextpersonID$x\" type='text' size=\"10\" />  \n";
  echo "<input id=\"find$x\" name=\"find$x\" type='button' value=\"" . uiTextSnippet('find') . "\" onclick=\"findItem('I','nextpersonID$x',null);\" /><br>\n";
  if ($x < 5) {
    $treeresult = tng_query($query);
  }
}
?>
  <input name='primaryID' type='hidden' value="<?php echo $primaryID; ?>" />
  <br>
  </span>
</div>
<br>
</div>
  <?php endFormElement(); ?>
<br>

  <?php
  if ($counter) {
    ?>
  <a id="events"></a>
  <table class='table table-sm'>
    <tr>
      <th width="20"></th>
      <th width="50"><?php echo uiTextSnippet('date'); ?></th>
      <th><?php echo uiTextSnippet('event'); ?></th>
    </tr>
    <?php
    $counter = 0;
    foreach ($tlevents2 as $key => $value) {
      $counter++;
      echo "<tr>\n";
      echo "<td align=\"right\"><span>$counter&nbsp;</span></td>";
      echo "<td>$key&nbsp;</td>";
      echo "<td><ul>$value</ul></td>";
      echo "</tr>\n";
    }
    ?>
  </table><br>
  <?php
}
echo $publicFooterSection->build();
$mpct = $pedigree['mpct'] ? $pedigree['mpct'] : 0;
$ypct = $pedigree['ypct'] ? $pedigree['ypct'] : 100 - $mpct;
$ymult = $pedigree['ymult'] ? $pedigree['ymult'] : 10;
$ypixels = $pedigree['ypixels'] ? $pedigree['ypixels'] : 10;
$mpixels = $pedigree['mpixels'] ? $pedigree['mpixels'] : 50;

if ($row['death']) {
  $deathage = intval($row['death']) - intval($row['birth']);
} else {
  $defaultage = intval(date("Y")) - intval($row['birth']);
  $deathage = $defaultage < 110 ? $defaultage : 110;
}
echo scriptsManager::buildScriptElements($flags, 'public');
?>
<script src='js/selectutils.js'></script>
<?php if ($pedigree['simile']) { ?>
<script>
  var tlstartdate = "<?php echo ($row['birth'] + floor($deathage / 2)) ?>";
  var xmlfile = "ajx_timelinexml.php?earliest=<?php echo $earliest ?>&latest=<?php echo $latest ?>";
  var yearpct = "<?php echo $ypct ?>%";
  var monthpct = "<?php echo $mpct ?>%";
  var yearmultiple = <?php echo $ymult ?>;
  var yearpixels = <?php echo $ypixels ?>;
  var monthpixels = <?php echo $mpixels ?>;
  var Timeline_ajax_url = "timeline_2.3.0/timeline_ajax/simile-ajax-api.js";
  var Timeline_urlPrefix = "timeline_2.3.0/timeline_js/";
  var Timeline_parameters = "bundle=true";
</script>
<?php } ?>
<script src="js/timeline.js"></script>
<script src="timeline_2.3.0/timeline_js/timeline-api.js"></script>
<script>
  var lastpopup = "";
  var tnglitbox;
  for (var h = 1; h <= <?php echo $numlines; ?>; h++) {
    eval('var timer' + h + '=false');
  }

  function setTimerHide(slot) {
    eval("clearTimeout(timer" + slot + ");");
    eval("timer" + slot + "=false;");
    eval("timer" + slot + "=setTimeout(\"hidePopup('" + slot + "')\",<?php echo $pedigree['popuptimer']; ?>);");
  }

  function setTimerShow(slot, ev) {
    if (ev === "over") {
      eval("timer" + slot + "=setTimeout(\"showPopup('" + slot + "')\",<?php echo $pedigree['popuptimer']; ?>);");
    } else {
      showPopup(slot);
    }
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

  function showPopup(slot) {
    if (lastpopup) {
      cancelTimer(lastpopup);
      hidePopup(lastpopup);
    }
    lastpopup = slot;

    var ref = document.all ? document.all["popup" + slot] : document.getElementById ? document.getElementById("popup" + slot) : null;

    if (ref) {
      ref = ref.style;
    }
    if (ref.visibility !== "show" && ref.visibility !== "visible") {
      ref.zIndex = 8;
      ref.visibility = "visible";
    }
  }

  function toggleAddMore(val) {
    $('#addmorediv').toggle(200);
  }

  var lines = 1;
  function toggleLines() {
    if (lines) {
      $('div.vertlines').each(function (index, item) {
        item.style.visibility = 'hidden';
      });
      lines = 0;
    } else {
      $('div.vertlines').each(function (index, item) {
        item.style.visibility = 'visible';
      });
      lines = 1;
    }
  }

  function centerTimeline(year) {
    tl.getBand(0).setCenterVisibleDate(new Date(year, 0, 1));
    return false;
  }
</script>
</body>
</html>
