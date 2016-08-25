<?php

function getSpouses($personID, $sex) {
  $spouses = [];
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
    $result = getSpouseFamilyData($self, $personID, $spouseorder);
  } else {
    $result = getSpouseFamilyDataUnion($personID);
  }
  $marrtot = tng_num_rows($result);
  if (!$marrtot) {
    $result = getSpouseFamilyDataUnion($personID);
    $self = $spouse = $spouseorder = "";
  }
  while ($row = tng_fetch_assoc($result)) {
    if (!$spouse) {
      $spouse = $row['husband'] == $personID ? 'wife' : 'husband';
    }
    $result2 = getPersonData($row[$spouse]);
    $spouserow = tng_fetch_assoc($result2);
    $spouserow['familyID'] = $row['familyID'];
    $spouserow['marrdate'] = $row['marrdate'];
    $spouserow['marrplace'] = $row['marrplace'];
    $spouserow['marrtype'] = $row['marrtype'];
    $spouserow['divdate'] = $row['divdate'];
    $spouserow['divplace'] = $row['divplace'];
    $spouserow['fliving'] = $row['living'];

    $famrights = determineLivingPrivateRights($row);
    $sprights = determineLivingPrivateRights($spouserow);
    $spouserow['allow_living'] = $sprights['living'] && $famrights['living'];
    $spouserow['allow_private'] = $sprights['private'] && $famrights['private'];

    $spouserow['name'] = getName($spouserow);
    array_push($spouses, $spouserow);
  }
  tng_free_result($result);

  return $spouses;
}

function getSpouseParents($personID, $sex) {
  if ($sex == 'M') {
    $childtext = uiTextSnippet('sonof');
  } else {
    if ($sex == 'F') {
      $childtext = uiTextSnippet('daughterof');
    } else {
      $childtext = uiTextSnippet('childof');
    }
  }

  $allparents = "";
  $parents = getChildFamily($personID, "ordernum");

  if ($parents && tng_num_rows($parents)) {
    while ($parent = tng_fetch_assoc($parents)) {
      $parentstr = "";
      $gotfather = getParentData($parent['familyID'], 'husband');

      if ($gotfather) {
        $fathrow = tng_fetch_assoc($gotfather);
        if ($fathrow['firstname'] || $fathrow['lastname']) {
          $frights = determineLivingPrivateRights($fathrow);
          $fathrow['allow_living'] = $frights['living'];
          $fathrow['allow_private'] = $frights['private'];
          $fathname = getName($fathrow);
          if ($fathrow['name'] == uiTextSnippet('living')) {
            $fathrow['firstname'] = uiTextSnippet('living');
          }

          if ($fathrow['name'] == uiTextSnippet('private')) {
            $fathrow['firstname'] = uiTextSnippet('private');
          }
          $parentstr .= "<a href='#' onclick=\"if(jQuery('#p{$fathrow['personID']}').length) {jQuery('html, body').animate({scrollTop: jQuery('#p{$fathrow['personID']}').offset().top-10},'slow');}else{window.location.href='peopleShowPerson.php?personID={$fathrow['personID']}';} return false;\">$fathname</a>";
        }
        tng_free_result($gotfather);
      }

      $gotmother = getParentData($parent['familyID'], 'wife');

      if ($gotmother) {
        $mothrow = tng_fetch_assoc($gotmother);
        if ($mothrow['firstname'] || $mothrow['lastname']) {
          $mrights = determineLivingPrivateRights($mothrow);
          $mothrow['allow_living'] = $mrights['living'];
          $mothrow['allow_private'] = $mrights['private'];
          $mothname = getName($mothrow);
          if ($mothrow['name'] == uiTextSnippet('living')) {
            $mothrow['firstname'] = uiTextSnippet('living');
          }
          if ($mothrow['name'] == uiTextSnippet('private')) {
            $mothrow['firstname'] = uiTextSnippet('private');
          }
          if ($parentstr) {
            $parentstr .= " " . uiTextSnippet('and') . " ";
          }
          $parentstr .= "<a href='#' onclick=\"if(jQuery('#p{$mothrow['personID']}').length) {jQuery('html, body').animate({scrollTop: jQuery('#p{$mothrow['personID']}').offset().top-10},'slow');}else{window.location.href='peopleShowPerson.php?personID={$mothrow['personID']}';} return false;\">$mothname</a>";
        }
        tng_free_result($gotmother);
      }
      if ($parentstr) {
        $parentstr = "$childtext $parentstr";
        $allparents .= $allparents ? ", $parentstr" : $parentstr;
      }
    }
    tng_free_result($parents);
  }
  if ($allparents) {
    $allparents = "($allparents)";
  }

  return $allparents;
}

function getVitalDates($row, $needparents = null) {
  $vitalinfo = "";

  if ($row['allow_living'] && $row['allow_private']) {
    if ($row['birthdate'] || $row['birthplace']) {
      $vitalinfo .= " " . uiTextSnippet('wasborn') . " " . displayDate($row['birthdate']);
      if ($row['birthdate'] && $row['birthplace']) {
        $vitalinfo .= ", ";
      }
      $vitalinfo .= $row['birthplace'];
    }
    if ($row['altbirthdate'] || $row['altbirthplace']) {
      if ($vitalinfo) {
        $vitalinfo .= ";";
      }
      $vitalinfo .= " " . tng_strtolower(uiTextSnippet('waschristened')) . " " . displayDate($row['altbirthdate']);
      if ($row['altbirthdate'] && $row['altbirthplace']) {
        $vitalinfo .= ", ";
      }
      $vitalinfo .= $row['altbirthplace'];
    }
    if ($needparents) {
      $spparents = getSpouseParents($row['personID'], $row['sex']);
      if ($spparents) {
        $vitalinfo .= " " . $spparents;
      }
    }

    if ($row['deathdate'] || $row['deathplace']) {
      if ($vitalinfo) {
        $vitalinfo .= ";";
      }
      $vitalinfo .= " " . tng_strtolower(uiTextSnippet('anddied')) . " " . displayDate($row['deathdate']);
      if ($row['deathdate'] && $row['deathplace']) {
        $vitalinfo .= ", ";
      }
      $vitalinfo .= $row['deathplace'];
    }
    if ($row['burialdate'] || $row['burialplace']) {
      if ($vitalinfo) {
        $vitalinfo .= ";";
      }
      $burialmsg = $row['burialtype'] ? uiTextSnippet('wascremated') : uiTextSnippet('wasburied');
      $vitalinfo .= " " . tng_strtolower($burialmsg) . " " . displayDate($row['burialdate']);
      if ($row['burialdate'] && $row['burialplace']) {
        $vitalinfo .= ", ";
      }
      $vitalinfo .= $row['burialplace'];
    }
  }
  if ($vitalinfo) {
    $vitalinfo .= ". ";
  }
  return $vitalinfo;
}

function getSpouseDates($row) {
  $spouseinfo = "";

  if ($row['allow_living'] && $row['allow_private']) {
    if ($row['marrdate'] || $row['marrplace'] || $row['marrtype']) {
      $spouseinfo .= " " . displayDate($row['marrdate']);
      if ($row['marrtype']) {
        $spouseinfo .= " ({$row['marrtype']})";
      }
      if (($row['marrdate'] || $row['marrtype']) && $row['marrplace']) {
        $spouseinfo .= ", ";
      }
      $spouseinfo .= $row['marrplace'];
    }
    if ($row['divdate'] || $row['divplace']) {
      $spouseinfo .= "; " . strtolower(uiTextSnippet('divorced')) . " ";
      if ($row['divdate']) {
        $spouseinfo .= displayDate($row['divdate']);
        if ($row['divplace']) {
          $spouseinfo .= ", ";
        }
      }
      $spouseinfo .= $row['divplace'];
    }
  }
  if ($spouseinfo) {
    $spouseinfo .= ".";
  }
  return $spouseinfo;
}

function getOtherEvents($row) {
  global $eventtypes_table;
  global $events_table;
  global $pedigree;

  $otherEvents = "";
  if ($pedigree['regnotes'] && $row['allow_living'] && $row['allow_private']) {
    $query = "SELECT display, eventdate, eventdatetr, eventplace, age, agency, cause, addressID, info, tag, description, eventID FROM ($events_table, $eventtypes_table) WHERE persfamID = \"{$row['personID']}\" AND $events_table.eventtypeID = $eventtypes_table.eventtypeID AND keep = \"1\" AND parenttag = \"\" ORDER BY eventdatetr, ordernum, tag, description, info, eventID";
    $custevents = tng_query($query);
    while ($custevent = tng_fetch_assoc($custevents)) {
      $displayval = getEventDisplay($custevent['display']);
      $fact = [];
      if ($custevent['info']) {
        $fact = checkXnote($custevent['info']);
        if ($fact[1]) {
          $xnote = $fact[1];
          array_pop($fact);
        }
      }
      $extras = getFact($custevent);
      $fact = (count($fact) && $fact[0] != "") ? array_merge($fact, $extras) : $extras;
      $thisEvent = $custevent['eventdate'] ? displayDate($custevent['eventdate']) : "";
      if ($custevent['eventplace']) {
        if ($thisEvent) {
          $thisEvent .= ", ";
        }
        $thisEvent .= $custevent['eventplace'];
      }
      if (count($fact)) {
        foreach ($fact as $f) {
          if ($thisEvent) {
            $thisEvent .= "; ";
          }
          $thisEvent .= $f;
        }
      }
      $otherEvents .= "<li>$displayval: " . $thisEvent . "</li>\n";
    }
    tng_free_result($custevents);
  }
  if ($otherEvents) {
    $otherEvents = "<p>" . uiTextSnippet('otherevents') . ":\n<ul class=\"regevents\">$otherEvents</ul></p>\n";
  }
  return $otherEvents;
}

function getRegNotes($persfamID, $flag) {
  global $notelinks_table;
  global $xnotes_table;
  global $eventtypes_table;
  global $events_table;

  $custnotes = [];
  $gennotes = [];
  $precustnotes = [];
  $postcustnotes = [];

  if ($flag == 'I') {
    $precusttitles = ["BIRT" => uiTextSnippet('birth'), "CHR" => uiTextSnippet('christened'), "NAME" => uiTextSnippet('name'),
            "TITL" => uiTextSnippet('title'), "NSFX" => uiTextSnippet('suffix'), "NICK" => uiTextSnippet('nickname'),
            "BAPL" => uiTextSnippet('baptizedlds'), "CONL" => uiTextSnippet('conflds'), "INIT" => uiTextSnippet('initlds'), "ENDL" => uiTextSnippet('endowedlds')];
    $postcusttitles = ["DEAT" => uiTextSnippet('died'), "BURI" => uiTextSnippet('buried'), "SLGC" => uiTextSnippet('sealedplds')];
  } elseif ($flag == 'F') {
    $precusttitles = ["MARR" => uiTextSnippet('married'), "SLGS" => uiTextSnippet('sealedslds'), "DIV" => uiTextSnippet('divorced')];
    $postcusttitles = [];
  } else {
    $precusttitles = ["ABBR" => uiTextSnippet('shorttitle'), "CALN" => uiTextSnippet('callnum'), "AUTH" => uiTextSnippet('author'),
            "PUBL" => uiTextSnippet('publisher'), "TITL" => uiTextSnippet('title')];
    $postcusttitles = [];
  }

  $query = "SELECT display, $xnotes_table.note as note, $notelinks_table.eventID as eventID FROM $notelinks_table
    LEFT JOIN  $xnotes_table ON $notelinks_table.xnoteID = $xnotes_table.ID 
    LEFT JOIN $events_table ON $notelinks_table.eventID = $events_table.eventID 
    LEFT JOIN $eventtypes_table ON $eventtypes_table.eventtypeID = $events_table.eventtypeID 
    WHERE $notelinks_table.persfamID = '$persfamID' AND secret!=\"1\"
    ORDER BY eventdatetr, $eventtypes_table.ordernum, tag";
  $notelinks = tng_query($query);

  $currevent = "";
  $type = 0;
  while ($note = tng_fetch_assoc($notelinks)) {
    if (!$note['eventID']) {
      $note['eventID'] = "--x-general-x--";
    }
    if ($note['eventID'] != $currevent) {
      $currevent = $note['eventID'];
      $currtitle = "";
    }
    if (!$currtitle) {
      if ($note['display']) {
        $currtitle = getEventDisplay($note['display']);
        $key = "cust$currevent";
        $custnotes[$key] = ["title" => $currtitle, "text" => ""];
        $type = 2;
      } else {
        if ($postcusttitles[$currevent]) {
          $currtitle = $postcusttitles[$currevent];
          $postcustnotes[$currevent] = ["title" => $postcusttitles[$currevent], "text" => ""];
          $type = 3;
        } else {
          $currtitle = $precusttitles[$currevent] ? $precusttitles[$currevent] : " ";
          if ($note['eventID'] == "--x-general-x--") {
            $gennotes[$currevent] = ["title" => $precusttitles[$currevent], "text" => ""];
            $type = 0;
          } else {
            $precustnotes[$currevent] = ["title" => $precusttitles[$currevent], "text" => ""];
            $type = 1;
          }
        }
      }
    }
    switch ($type) {
      case 0:
        if ($gennotes[$currevent]['text']) {
          $gennotes[$currevent]['text'] .= "<br><br>";
        }
        $gennotes[$currevent]['text'] .= nl2br($note['note']) . "\n";
        break;
      case 1:
        if ($precustnotes[$currevent]['text']) {
          $precustnotes[$currevent]['text'] .= "<br><br>";
        }
        $precustnotes[$currevent]['text'] .= nl2br($note['note']) . "\n";
        break;
      case 2:
        if ($custnotes[$key]['text']) {
          $custnotes[$key]['text'] .= "<br><br>";
        }
        $custnotes[$key]['text'] .= nl2br($note['note']) . "\n";
        break;
      case 3:
        if ($postcustnotes[$currevent]['text']) {
          $postcustnotes[$currevent]['text'] .= "<br><br>";
        }
        $postcustnotes[$currevent]['text'] .= nl2br($note['note']) . "\n";
        break;
    }
  }
  $finalnotesarray = array_merge($gennotes, $precustnotes, $custnotes, $postcustnotes);
  tng_free_result($notelinks);

  return $finalnotesarray;
}

function buildRegNotes($notearray) {
  $notes = "";
  foreach ($notearray as $key => $note) {
    if ($notes) {
      $notes .= "<br><br>\n";
    }
    if ($note['title']) {
      $notes .= $note['title'] . ":<br>\n";
    }
    $notes .= $note['text'] . "\n";
  }
  return $notes;
}
