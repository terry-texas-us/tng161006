<?php
require 'tng_begin.php';

require $subroot . 'pedconfig.php';
require 'datelib.php';

$timeline = $_SESSION['timeline'];
if (!is_array($timeline)) {
  $timeline = [];
}

function getTimelineDate($date) {
  $ret = [];

  preg_match('/(\d\d\d\d)-(\d\d)-(\d\d).*/', $date, $matches);
  if ($matches[2] == '00') {
    $matches[2] = '01';
  }
  if ($matches[3] == '00') {
    $matches[3] = '01';
  }
  $ret['year'] = $matches[1];
  $ret['date_gmt'] = strftime("%b %d {$ret['year']}", gmmktime(12, 0, 0, $matches[2], $matches[3], 2000)) . ' GMT';

  return $ret;
}

header('Content-Type: application/xml');
echo '<?xml version="1.0"';
if ($session_charset) {
  echo " encoding=\"$session_charset\"";
}
echo "?>\n";
echo "<data>\n";

$wherestr = $pedigree['tcevents'] ? "WHERE (evyear BETWEEN \"$earliest\" AND \"$latest\") OR (endyear BETWEEN \"$earliest\" AND \"$latest\")" : '';
$tlquery = "SELECT evday, evmonth, evyear, evtitle, evdetail, endday, endmonth, endyear FROM timelineevents $wherestr ORDER BY evyear, evmonth, evday";
$tlresult = tng_query($tlquery) or die(uiTextSnippet('cannotexecutequery') . ": $tlquery");
$tlevents = [];
$tlevents2 = [];
while ($tlrow = tng_fetch_assoc($tlresult)) {
  if ($tlrow['evday'] == '0') {
    $tlrow['evday'] = '1';
  }
  if ($tlrow['evmonth'] == '0') {
    $tlrow['evmonth'] = '1';
  }

  $beg_date_gmt = strftime('%b %d ' . $tlrow['evyear'], gmmktime(12, 0, 0, $tlrow['evmonth'], $tlrow['evday'], 2000)) . ' GMT';
  if ($tlrow['endyear']) {
    if ($tlrow['endmonth'] == '0') {
      $tlrow['endmonth'] = '12';
    }
    if ($tlrow['endday'] == '0') {
      $tlrow['endday'] = cal_days_in_month(CAL_GREGORIAN, $tlrow['endmonth'], $tlrow['endyear']);
    }
    $end_date_gmt = strftime('%b %d ' . $tlrow['endyear'], gmmktime(12, 0, 0, $tlrow['endmonth'], $tlrow['endday'], 2000)) . ' GMT';
    if ($end_date_gmt != $beg_date_gmt) {
      $isduration = 'isDuration="true"';
    }
  } else {
    $end_date_gmt = $beg_date_gmt;
    $isduration = '';
  }
  $evtitle = $tlrow['evtitle'] ? $tlrow['evtitle'] : $tlrow['evdetail'];
  echo '<event start="' . $beg_date_gmt . '" end="' . $end_date_gmt . "\" $isduration icon=\"img/red-circle.png\" title=\" " . htmlspecialchars($evtitle, ENT_QUOTES, $session_charset) . '">' . htmlspecialchars($tlrow['evdetail'], ENT_QUOTES, $session_charset) . "</event>\n";
}
tng_free_result($tlresult);

foreach ($timeline as $timeentry) {
  parse_str($timeentry);
  $query = "SELECT firstname, lnprefix, lastname, prefix, suffix, nameorder, living, private, branch, sex, IF(birthdatetr !='0000-00-00', birthdatetr, altbirthdatetr) as birth, IF(deathdatetr !='0000-00-00', deathdatetr, burialdatetr) as death FROM $people_table WHERE personID = '$timeperson'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);

  $beg_date = getTimelineDate($row['birth']);
  $beg_year = $beg_date['year'];
  $beg_date_gmt = $beg_date['date_gmt'];
  if ($row['death'] != '0000-00-00') {
    $end_date = getTimelineDate($row['death']);
    $end_year = $end_date['year'];
    $end_date_gmt = $end_date['date_gmt'];
  } else {
    $end_date_gmt = date('M d Y') . ' GMT';
    $end_year = '';
  }
  $rights = determineLivingPrivateRights($row);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  $name = xmlcharacters(getName($row));
  echo '<event start="' . $beg_date_gmt . '" end="' . $end_date_gmt . "\" title=\"$name ($beg_year - $end_year)\">$name ($beg_year - $end_year)</event>\n";
  tng_free_result($result);

  if (count($timeline) == 1 && $rights['both']) {
    $query = "SELECT display, eventdate, eventdatetr, eventplace, info FROM ($events_table, eventtypes)
      WHERE persfamID = \"$timeperson\" AND $events_table.eventtypeID = eventtypes.eventtypeID AND keep = \"1\" AND parenttag = \"\"
      ORDER BY ordernum, tag, description, eventdatetr, info, eventID";
    $custevents = tng_query($query);
    while ($custevent = tng_fetch_assoc($custevents)) {
      if ($custevent['eventdatetr'] != '0000-00-00') {
        $displayval = getEventDisplay($custevent['display']);
        $eventDate = displayDate($custevent['eventdate']);

        $beg_date = getTimelineDate($custevent['eventdatetr']);
        $beg_year = $beg_date['year'];
        $beg_date_gmt = $beg_date['date_gmt'];

        $end_date = '';
        $got_to = stripos($custevent['eventdate'], 'to ');
        if ($got_to) {
          $end_date = substr($custevent['eventdate'], $got_to + 3);
        } else {
          $got_and = stripos($custevent['eventdate'], 'and ');
          if ($got_and) {
            $end_date = substr($custevent['eventdate'], $got_and + 4);
          }
        }
        if ($end_date) {
          $end_date_array = getTimelineDate(convertDate($end_date));
          $end_date_gmt = $end_date_array['date_gmt'];
        } else {
          $end_date_gmt = $beg_date_gmt;
        }
        //if eventdate contains "to" or "and", take the rest of that string and do a similar match for the end date

        $info = $custevent['eventplace'];
        $info .= $info && $custevent['info'] ? ': ' . xmlcharacters($custevent['info']) : '';
        $title = xmlcharacters("$displayval ($eventDate)");
        echo '<event start="' . $beg_date_gmt . '" end="' . $end_date_gmt . "\" icon=\"img/green-circle.png\" title=\" $title\">$info</event>\n";
      }
    }
    tng_free_result($custevents);

    if ($row['sex'] == 'M') {
      $self = 'husband';
      $spouse = 'wife';
      $spouseorder = 'husborder';
    } elseif ($row['sex'] == 'F') {
      $self = 'wife';
      $spouse = 'husband';
      $spouseorder = 'wifeorder';
    } else {
      $self = '';
      $spouse = '';
      $spouseorder = '';
    }
    //get and loop through all marriages (link to people table on opposite spouse) for this person based on gender
    if ($spouseorder) {
      $marriages = getSpouseFamilyDataPlusDates($self, $timeperson, $spouseorder);
    } else {
      $marriages = getSpouseFamilyDataUnionPlusDates($timeperson);
    }
    if (!tng_num_rows($marriages) && $spouseorder) {
      $marriages = getSpouseFamilyDataUnionPlusDates($timeperson);
    }

    while ($marriagerow = tng_fetch_assoc($marriages)) {
      //do event for marriage date and person (observe living rights)
      if (substr($marriagerow['marrdatetr'], 0, 4) != '0000') {
        if (!$spouseorder) {
          $spouse = $marriagerow['husband'] == $timeperson ? wife : husband;
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
          }
          tng_free_result($spouseresult);
        }

        $rightfbranch = checkbranch($marriagerow['branch']) ? 1 : 0;
        $mrights = determineLivingPrivateRights($marriagerow, $rightfbranch);
        $marriagerow['allow_living'] = $mrights['living'];
        $marriagerow['allow_private'] = $mrights['private'];
        if ($mrights['both']) {
          $beg_date = getTimelineDate($marriagerow['marrdatetr']);
          $beg_year = $beg_date['year'];
          $beg_date_gmt = $beg_date['date_gmt'];
          $displayDate = displayDate($marriagerow['marrdate']);

          echo '<event start="' . $beg_date_gmt . '" end="' . $beg_date_gmt . '" title="' . xmlcharacters('' . uiTextSnippet('married') . " $spousename") . '">' . xmlcharacters("$displayDate, {$marriagerow['marrplace']}") . "</event>\n";
        }
      }
      //get all children (link to people) born to this marriage
      //loop through and make event for each
      $children = getChildrenDataPlusDates($marriagerow['familyID']);

      while ($child = tng_fetch_assoc($children)) {
        if ($child['birthdate']) {
          $date = $child['birthdatetr'];
          $displayDate = displayDate($child['birthdate']);
          $abbr = uiTextSnippet('birthabbr');
        } elseif ($child['altbirthdate']) {
          $date = $child['altbirthdatetr'];
          $displayDate = displayDate($child['altbirthdate']);
          $abbr = uiTextSnippet('chrabbr');
        }
        if ($date && substr($date, 0, 4) != '0000') {
          $crights = determineLivingPrivateRights($child);
          $child['allow_living'] = $crights['living'];
          $child['allow_private'] = $crights['private'];
          if ($crights['both']) {
            if ($child['firstname'] || $child['lastname']) {
              $childname = getName($child);
            }
            $beg_date = getTimelineDate($date);
            $beg_year = $beg_date['year'];
            $beg_date_gmt = $beg_date['date_gmt'];
            echo '<event start="' . $beg_date_gmt . '" end="' . $beg_date_gmt . '" title="' . xmlcharacters(uiTextSnippet('child') . ': ' . $childname) . '">' . xmlcharacters("$abbr $displayDate") . "</event>\n";
          }
        }
      }
      tng_free_result($children);
    }
    tng_free_result($marriages);
  }
}

echo "</data>\n";
