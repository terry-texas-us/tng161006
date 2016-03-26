<?php

require 'begin.php';
require 'genlib.php';
require 'getlang.php';

require 'checklogin.php';
require 'personlib.php';

$firstsection = 0;
$tableid = "";
$cellnumber = 0;

$totcols = 3;
$factcols = $totcols - 1;

function showFact($text, $fact) {
  global $factcols;

  $facttext = "";
  if ($fact) {
    $facttext .= "<tr>\n";
    $facttext .= "<td>" . $text . "</td>\n";
    $facttext .= "<td colspan=\"$factcols\">$fact</td>\n";
    $facttext .= "</tr>\n";
  }
  return $facttext;
}

function showDatePlace($event) {
  global $cellnumber;

  $dptext = "";
  if ($event['date'] || $event['place']) {
    if (!$cellnumber) {
      $cellid = " id=\"info1\"";
      $cellnumber++;
    } else {
      $cellid = "";
    }

    $dptext .= "<tr>\n";
    $dptext .= "<td $cellid><span>" . $event['text'] . "</span></td>\n";
    $dptext .= "<td><span>" . displayDate($event['date']) . "&nbsp;</span></td>\n";
    $dptext .= "<td width=\"80%\"><span>{$event['place']}&nbsp;</span></td>\n";
    $dptext .= "</tr>\n";
  }
  return $dptext;
}

function displayIndividual($ind, $label, $familyID, $showmarriage) {
  global $tree;
  global $totcols;
  global $personID;
  global $families_table;
  global $people_table;
  global $righttree;

  $indtext = "";
  $rights = determineLivingPrivateRights($ind, $righttree);
  $ind['allow_living'] = $rights['living'];
  $ind['allow_private'] = $rights['private'];

  $restriction = $familyID ? "AND familyID != \"$familyID\"" : "";
  if ($ind['sex'] == 'M') {
    $sex = uiTextSnippet('male');
  } else {
    if ($ind['sex'] == 'F') {
      $sex = uiTextSnippet('female');
    } else {
      $sex = uiTextSnippet('unknown');
    }
  }
  $namestr = getName($ind);
  $personID = $ind['personID'];

  //adjust for same-sex relationships
  if ($ind['sex'] == 'M' && $label == uiTextSnippet('wife')) {
    $label = uiTextSnippet('husband');
  } elseif ($ind['sex'] == 'F' && $label == uiTextSnippet('husband')) {
    $label = uiTextSnippet('wife');
  }

  //show photo & name
  $indtext .= "<tr><td colspan=\"$totcols\">";
  $indtext .= "<span>$label | $sex</span><br><h4><b>";
  if ($ind['haskids']) {
    $indtext .= "> ";
  }
  $indtext .= "$namestr</b>";

  $indtext .= "<br></h4>\n";
  $indtext .= "</td></tr>\n";

  $event = array();
  $event = "";

  $event['text'] = uiTextSnippet('born');
  $event['event'] = "BIRT";
  $event['type'] = 'I';
  $event['ID'] = $personID;
  if ($rights['both']) {
    $event['date'] = $ind['birthdate'];
    $event['place'] = $ind['birthplace'];
  }
  $indtext .= showDatePlace($event);

  $event = "";
  $event['event'] = "CHR";
  $event['type'] = 'I';
  $event['ID'] = $personID;
  if ($rights['both']) {
    $event['date'] = $ind['altbirthdate'];
    $event['place'] = $ind['altbirthplace'];
  }
  if ((isset($event['date']) && $event['date']) || (isset($event['place']) && $event['place'])) {
    $event['text'] = uiTextSnippet('christened');
    $indtext .= showDatePlace($event);
  }

  $event = "";
  $event['text'] = uiTextSnippet('died');
  $event['event'] = "DEAT";
  $event['type'] = 'I';
  $event['ID'] = $personID;
  if ($rights['both']) {
    $event['date'] = $ind['deathdate'];
    $event['place'] = $ind['deathplace'];
  }
  $indtext .= showDatePlace($event);

  $event = "";
  $event['text'] = uiTextSnippet('buried');
  $event['event'] = "BURI";
  $event['type'] = 'I';
  $event['ID'] = $personID;
  if ($rights['both']) {
    $event['date'] = $ind['burialdate'];
    $event['place'] = $ind['burialplace'];
  }
  $indtext .= showDatePlace($event);

  //show marriage & sealing if $showmarriage
  if ($familyID) {
    if ($showmarriage) {
      $query = "SELECT marrdate, marrplace, divdate, divplace, living, private, branch, gedcom FROM $families_table WHERE familyID = \"$familyID\" AND gedcom = \"$tree\"";
      $result = tng_query($query);
      $fam = tng_fetch_assoc($result);
      $frights = determineLivingPrivateRights($fam, $righttree);
      $fam['allow_living'] = $frights['living'];
      $fam['allow_private'] = $frights['private'];
      tng_free_result($result);

      $event = "";
      $eventd = array();
      $event['text'] = uiTextSnippet('married');
      $event['event'] = "MARR";
      $event['type'] = 'F';
      $event['ID'] = $familyID;
      if ($frights['both']) {
        $event['date'] = $fam['marrdate'];
        $event['place'] = $fam['marrplace'];
        $eventd['event'] = "DIV";
        $eventd['text'] = uiTextSnippet('divorced');
        $eventd['date'] = $fam['divdate'];
        $eventd['place'] = $fam['divplace'];
      }
      $indtext .= showDatePlace($event);
      if ($eventd['date'] || $eventd['place']) {
        $indtext .= showDatePlace($eventd);
      }
    }
    $spousetext = uiTextSnippet('otherspouse');
  } else {
    $spousetext = uiTextSnippet('spouse');
  }

  //show other spouses
  $query = "SELECT familyID, personID, firstname, lnprefix, lastname, prefix, suffix, nameorder, $families_table.living as fliving, $families_table.private as fprivate, $families_table.branch as branch, $families_table.gedcom, $people_table.living as living, $people_table.private as private, marrdate, marrplace FROM $families_table ";
  if ($ind['sex'] == 'M') {
    $query .= "LEFT JOIN $people_table on $families_table.wife = $people_table.personID AND $families_table.gedcom = $people_table.gedcom WHERE husband = \"{$ind['personID']}\" AND $people_table.gedcom = \"$tree\" $restriction ORDER BY husborder";
  } else {
    if ($ind['sex'] = 'F') {
      $query .= "LEFT JOIN $people_table on $families_table.husband = $people_table.personID AND $families_table.gedcom = $people_table.gedcom WHERE wife = \"{$ind['personID']}\" AND $people_table.gedcom = \"$tree\" $restriction ORDER BY wifeorder";
    } else {
      $query .= "LEFT JOIN $people_table on ($families_table.husband = $people_table.personID OR $families_table.wife = $people_table.personID) AND $families_table.gedcom = $people_table.gedcom WHERE (wife = \"$ind[personID]\" && husband = \"{$ind['personID']}\") AND $people_table.gedcom = \"$tree\"";
    }
  }
  $spresult = tng_query($query);

  while ($fam = tng_fetch_assoc($spresult)) {
    $frights = determineLivingPrivateRights($fam, $righttree);
    $fam['allow_living'] = $frights['living'];
    $fam['allow_private'] = $frights['private'];
    $spousename = getName($fam);
    $spouselink = $spousename ? "$spousename | " : "";
    $spouselink .= $fam['familyID'];
    $indtext .= showFact($spousetext, $spouselink);

    $event = "";
    $event['text'] = uiTextSnippet('married');
    $event['event'] = "MARR";
    $event['type'] = 'F';
    $event['ID'] = $fam['familyID'];
    $fam['living'] = $fam['fliving'];
    $fam['private'] = $fam['fprivate'];
    $frights = determineLivingPrivateRights($fam, $righttree);
    $fam['allow_living'] = $frights['living'];
    $fam['allow_private'] = $frights['private'];
    if ($frights['both']) {
      $event['date'] = $fam['marrdate'];
      $event['place'] = $fam['marrplace'];
    }
    $indtext .= showDatePlace($event);
  }

  //show parents (for hus&wif)
  if ($familyID) {
    $query = "SELECT familyID, personID, firstname, lnprefix, lastname, prefix, suffix, nameorder, $people_table.living, $people_table.private, $people_table.branch, $people_table.gedcom FROM $families_table, $people_table WHERE $families_table.familyID = \"{$ind['famc']}\" AND $families_table.gedcom = \"$tree\" AND $people_table.personID = $families_table.husband AND $people_table.gedcom = \"$tree\"";
    $presult = tng_query($query);
    $parent = tng_fetch_assoc($presult);
    $prights = determineLivingPrivateRights($parent, $righttree);
    $parent['allow_living'] = $prights['living'];
    $parent['allow_private'] = $prights['private'];
    $fathername = getName($parent);
    tng_free_result($presult);
    $fatherlink = $fathername ? "$fathername | " . $parent['familyID'] : "";
    $indtext .= showFact(uiTextSnippet('father'), $fatherlink);

    $query = "SELECT familyID, personID, firstname, lnprefix, lastname, prefix, suffix, nameorder, $people_table.living, $people_table.private, $people_table.branch, $people_table.gedcom FROM $families_table, $people_table WHERE $families_table.familyID = \"$ind[famc]\" AND $families_table.gedcom = \"$tree\" AND $people_table.personID = $families_table.wife AND $people_table.gedcom = \"$tree\"";
    $presult = tng_query($query);
    $parent = tng_fetch_assoc($presult);
    $prights = determineLivingPrivateRights($parent, $righttree);
    $parent['allow_living'] = $prights['living'];
    $parent['allow_private'] = $prights['private'];
    $mothername = getName($parent);
    tng_free_result($presult);
    $motherlink = $mothername ? "$mothername | " . $parent['familyID'] : "";
    $indtext .= showFact(uiTextSnippet('mother'), $motherlink);
  }

  return $indtext;
}

//get family
$query = "SELECT familyID, husband, wife, living, private, marrdate, gedcom, branch FROM $families_table WHERE familyID = \"$familyID\" AND gedcom = \"$tree\"";
$result = tng_query($query);
$famrow = tng_fetch_assoc($result);
if (!tng_num_rows($result)) {
  tng_free_result($result);
  header("Location: thispagedoesnotexist.html");
  exit;
} else {
  tng_free_result($result);
}

header("Content-type:text/html; charset=" . $session_charset);

initMediaTypes();

$righttree = checktree($tree);

$frights = determineLivingPrivateRights($famrow, $righttree);
$famrow['allow_living'] = $frights['living'];
$famrow['allow_private'] = $frights['private'];
$famname = getFamilyName($famrow);
$namestr = uiTextSnippet('family') . ": " . $famname;

$years = $famrow['marrdate'] && $frights['both'] ? uiTextSnippet('marrabbr') . " " . displayDate($famrow['marrdate']) : "";

$photostr = showSmallPhoto($familyID, $famname, $famrow['allow_living'], 0);
echo tng_DrawHeading($photostr, $namestr, $years);

$famtext = "";
$personID = $famrow['husband'] ? $famrow['husband'] : $famrow['wife'];

$famtext .= "<ul>\n";
$famtext .= beginListItem('info');
$famtext .= "<table>\n";

//get husband & spouses
if ($famrow['husband']) {
  $query = "SELECT * FROM $people_table WHERE personID = \"{$famrow['husband']}\" AND gedcom = \"$tree\"";
  $result = tng_query($query);
  $husbrow = tng_fetch_assoc($result);
  $label = $husbrow['sex'] != 'F' ? uiTextSnippet('husband') : uiTextSnippet('wife');
  $famtext .= displayIndividual($husbrow, $label, $familyID, 1);
  tng_free_result($result);
}

//get wife & spouses
if ($famrow['wife']) {
  $query = "SELECT * FROM $people_table WHERE personID = \"{$famrow['wife']}\" AND gedcom = \"$tree\"";
  $result = tng_query($query);
  $wiferow = tng_fetch_assoc($result);
  $label = $husbrow['sex'] != 'M' ? uiTextSnippet('wife') : uiTextSnippet('husband');
  $famtext .= displayIndividual($wiferow, $label, $familyID, 0);
  tng_free_result($result);
}

//for each child
$query = "SELECT $people_table.personID as personID, branch, firstname, lnprefix, lastname, prefix, suffix, nameorder, living, private, famc, sex, birthdate, birthplace, altbirthdate, altbirthplace, haskids, deathdate, deathplace, burialdate, burialplace FROM $people_table, $children_table WHERE $people_table.personID = $children_table.personID AND $children_table.familyID = \"{$famrow['familyID']}\" AND $people_table.gedcom = \"$tree\" AND $children_table.gedcom = \"$tree\" ORDER BY ordernum";
$children = tng_query($query);

if ($children && tng_num_rows($children)) {
  //put a break here, title "Children"
  $famtext .= showBreak('smallbreak');

  $childcount = 0;
  while ($childrow = tng_fetch_assoc($children)) {
    $childcount++;
    $famtext .= displayIndividual($childrow, uiTextSnippet('child') . " $childcount", "", 1);
  }
}
tng_free_result($children);

$famtext .= "</table>\n";
$famtext .= endListItem('info');

$famtext .= "</ul>\n";

echo $famtext;
