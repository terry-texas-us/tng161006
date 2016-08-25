<?php
require 'tng_begin.php';

require 'personlib.php';

$result = getPersonFullPlusDates($personID);
if (!tng_num_rows($result)) {
  tng_free_result($result);
  header("Location: thispagedoesnotexist.html");
  exit;
}
$row = tng_fetch_assoc($result);
$rights = determineLivingPrivateRights($row);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];
$namestr = getName($row);

tng_free_result($result);

header("Content-type:text/html; charset=" . $session_charset);

initMediaTypes();
//echo "<div style=\"position:absolute;top:50%;margin-top:-200px\">\n";
$photostr = showSmallPhoto($personID, $namestr, $rights['both'], 0, false, $row['sex']);
echo tng_DrawHeading($photostr, $namestr, getYears($row));

$persontext = "";
$persontext .= "<ul>\n";

$persontext .= beginListItem('info');
$persontext .= "<table>\n";
resetEvents();
if ($rights['both']) {
  if ($row['nickname']) {
    $persontext .= showEvent(["text" => uiTextSnippet('nickname'), "fact" => $row['nickname'], "event" => "NICK", "entity" => $personID, "type" => 'I']);
  }
  setEvent(["text" => uiTextSnippet('birth'), "fact" => $stdex['BIRT'], "date" => $row['birthdate'], "place" => $row['birthplace'], "event" => "BIRT", "entity" => $personID, "type" => 'I', "np" => 1], $row['birthdatetr']);
  setEvent(["text" => uiTextSnippet('christened'), "fact" => $stdex['CHR'], "date" => $row['altbirthdate'], "place" => $row['altbirthplace'], "event" => "CHR", "entity" => $personID, "type" => 'I', "np" => 1], $row['altbirthdatetr']);
  setEvent(["text" => uiTextSnippet('died'), "fact" => $stdex['DEAT'], "date" => $row['deathdate'], "place" => $row['deathplace'], "event" => "DEAT", "entity" => $personID, "type" => 'I', "np" => 1], $row['deathdatetr']);
  $burialmsg = $row['burialtype'] ? uiTextSnippet('cremated') : uiTextSnippet('buried');
  setEvent(["text" => $burialmsg, "fact" => $stdex['BURI'], "date" => $row['burialdate'], "place" => $row['burialplace'], "event" => "BURI", "entity" => $personID, "type" => 'I', "np" => 1], $row['burialdatetr']);
}
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

if (count($events)) {
  ksort($events);
  foreach ($events as $event) {
    $persontext .= showEvent($event);
  }

  $persontext .= showBreak("smallbreak");
}

//do parents
$parents = getChildParentsFamily($personID);

if ($parents && tng_num_rows($parents)) {
  while ($parent = tng_fetch_assoc($parents)) {
    resetEvents();
    $gotfather = getParentData($parent['familyID'], 'husband');

    if ($gotfather) {
      $fathrow = tng_fetch_assoc($gotfather);
      $birthinfo = getBirthInfo($fathrow, 1);
      $frights = determineLivingPrivateRights($fathrow);
      $fathrow['allow_living'] = $frights['living'];
      $fathrow['allow_private'] = $frights['private'];
      if ($fathrow['firstname'] || $fathrow['lastname']) {
        $fatherlink = getName($fathrow);
      } else {
        $fatherlink = "";
      }
      if ($frights['both']) {
        $fatherlink .= $birthinfo;
      }
      $persontext .= showEvent(["text" => uiTextSnippet('father'), "fact" => $fatherlink]);
      if ($rights['both'] && $parent['frel']) {
        $rel = $parent['frel'];
        $relstr = uiTextSnippet($rel) ? uiTextSnippet($rel) : $rel;
        $persontext .= showEvent(["text" => uiTextSnippet('relationship2'), "fact" => $relstr]);
      }
      tng_free_result($gotfather);
    }

    $gotmother = getParentData($parent['familyID'], 'wife');

    if ($gotmother) {
      $mothrow = tng_fetch_assoc($gotmother);
      $birthinfo = getBirthInfo($mothrow, 1);
      $mrights = determineLivingPrivateRights($mothrow);
      $mothrow['allow_living'] = $mrights['living'];
      $mothrow['allow_private'] = $mrights['private'];
      if ($mothrow['firstname'] || $mothrow['lastname']) {
        $motherlink = getName($mothrow);
      } else {
        $motherlink = "";
      }
      if ($mrights['both']) {
        $motherlink .= $birthinfo;
      }
      $persontext .= showEvent(["text" => uiTextSnippet('mother'), "fact" => $motherlink]);
      if ($rights['both'] && $parent['mrel']) {
        $rel = $parent['mrel'];
        $relstr = uiTextSnippet($rel) ? uiTextSnippet($rel) : $rel;
        $persontext .= showEvent(["text" => uiTextSnippet('relationship2'), "fact" => $relstr]);
      }

      tng_free_result($gotmother);
    }

    $gotparents = getFamilyData($parent['familyID']);
    $parentrow = tng_fetch_assoc($gotparents);
    tng_free_result($gotparents);
    $parent['personID'] = "";

    if ($tngconfig['pardata'] < 2) {
      $prights = determineLivingPrivateRights($parentrow);

      if ($prights['both']) {
        setEvent(["text" => uiTextSnippet('married'), "fact" => $stdexf['MARR'], "date" => $parentrow['marrdate'], "place" => $parentrow['marrplace'], "event" => "MARR", "entity" => $parentrow['familyID'], "type" => 'F', "nomap" => 1, "np" => 1], $parentrow['marrdatetr']);
        setEvent(["text" => uiTextSnippet('divorced'), "fact" => $stdexf['DIV'], "date" => $parentrow['divdate'], "place" => $parentrow['divplace'], "event" => "DIV", "entity" => $parentrow['familyID'], "type" => 'F', "nomap" => 1, "np" => 1], $parentrow['divdatetr']);

        ksort($events);
        foreach ($events as $event) {
          $persontext .= showEvent($event);
        }
      }
    }

    $persontext .= showBreak("smallbreak");
  }
  tng_free_result($parents);
}

//do marriages
if ($spouseorder) {
  $marriages = getSpouseFamilyFull($self, $personID, $spouseorder);
} else {
  $marriages = getSpouseFamilyFullUnion($personID);
}
$marrtot = tng_num_rows($marriages);
if (!$marrtot) {
  if ($spouseorder) {
    $marriages = getSpouseFamilyFullUnion($personID);
    $marrtot = tng_num_rows($marriages);
  }
  $spouseorder = 0;
}
$marrcount = 1;

while ($marriagerow = tng_fetch_assoc($marriages)) {
  $stdexf = getStdExtras($marriagerow['familyID']);
  if ($marriagerow['marrtype']) {
    if (!is_array($stdexf['MARR'])) {
      $stdexf['MARR'] = [];
    }
    array_unshift($stdexf['MARR'], uiTextSnippet('type') . ": " . $marriagerow['marrtype']);
  }

  if (!$spouseorder) {
    $spouse = $marriagerow['husband'] == $personID ? wife : husband;
  }
  unset($spouserow);
  unset($birthinfo);
  if ($marriagerow[$spouse]) {
    $spouseresult = getPersonData($marriagerow[$spouse]);
    $spouserow = tng_fetch_assoc($spouseresult);
    $birthinfo = getBirthInfo($spouserow, 1);
    $srights = determineLivingPrivateRights($spouserow);
    $spouserow['allow_living'] = $srights['living'];
    $spouserow['allow_private'] = $srights['private'];
    if ($spouserow['firstname'] || $spouserow['lastname']) {
      $spouselink = getName($spouserow);
    } else {
      $spouselink = "";
    }
    if ($srights['both']) {
      $spouselink .= $birthinfo;
    }
  } else {
    $spouselink = "";
  }
  $marrstr = $marrtot > 1 ? " $marrcount" : "";
  if ($spouserow['allow_living']) {
    $persontext .= showEvent(["text" => uiTextSnippet('family') . "$marrstr", "fact" => $spouselink, "entity" => $marriagerow['familyID'], "type" => 'F']);
  } else {
    $persontext .= showEvent(["text" => uiTextSnippet('family') . "$marrstr", "fact" => $spouselink]);
  }

  $marrights = determineLivingPrivateRights($marriagerow);
  $marriagerow['allow_living'] = $marrights['living'];
  $marriagerow['allow_private'] = $marrights['private'];
  if ($marrights['both']) {
    resetEvents();

    setEvent(["text" => uiTextSnippet('married'), "fact" => $stdexf['MARR'], "date" => $marriagerow['marrdate'], "place" => $marriagerow['marrplace'], "event" => "MARR", "entity" => $marriagerow['familyID'], "type" => 'F', "np" => 1], $marriagerow['marrdatetr']);
    setEvent(["text" => uiTextSnippet('divorced'), "fact" => $stdexf['DIV'], "date" => $marriagerow['divdate'], "place" => $marriagerow['divplace'], "event" => "DIV", "entity" => $marriagerow['familyID'], "type" => 'F', "np" => 1], $marriagerow['divdatetr']);


    ksort($events);
    foreach ($events as $event) {
      $persontext .= showEvent($event);
    }
  }
  $marrcount++;

  //do children
  $children = getChildrenData($marriagerow['familyID']);

  if ($children && tng_num_rows($children)) {
    $persontext .= "<tr>\n";
    $persontext .= "<td>" . uiTextSnippet('children') . "</td>\n";
    $persontext .= "<td colspan='2'>\n";

    $kidcount = 1;
    $persontext .= "<table cellpadding = \"0\" cellspacing = \"0\">\n";
    while ($child = tng_fetch_assoc($children)) {
      $ifkids = $child['haskids'] ? "<strong>+</strong>" : "&nbsp;";
      $birthinfo = getBirthInfo($child, 1);
      $crights = determineLivingPrivateRights($child);
      $child['allow_living'] = $crights['living'];
      $child['allow_private'] = $crights['private'];
      if ($child['firstname'] || $child['lastname']) {
        $childname = getName($child);
        $persontext .= "<tr><td>$ifkids</td><td id=\"child{$child['pID']}\">$kidcount. $childname";
        if ($crights['both']) {
          $persontext .= $birthinfo;
        }
        $persontext .= "</td></tr>\n";
        $kidcount++;
      }
    }
    $persontext .= "</table>\n";
    $persontext .= "</td>\n";
    $persontext .= "</tr>\n";

    tng_free_result($children);
  }
  $persontext .= showBreak("smallbreak");
}
tng_free_result($marriages);

$persontext .= "</table>\n";
$persontext .= endListItem('info');

$persontext .= "</ul>\n";

echo $persontext;
//echo "</div>\n";