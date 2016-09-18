<?php
require 'begin.php';
require 'genlib.php';
require 'getlang.php';

require 'api_checklogin.php';
require 'personlib.php';
require 'api_library.php';
require 'log.php';

header('Content-Type: application/json; charset=' . $session_charset);

//get family
$query = "SELECT familyID, husband, wife, living, private, marrdate, gedcom, branch FROM families WHERE familyID = '$familyID'";
$result = tng_query($query);
$famrow = tng_fetch_assoc($result);
if (!tng_num_rows($result)) {
  tng_free_result($result);
  echo '{"error":"No one in database with that ID"}';
  exit;
} else {
  tng_free_result($result);
}
echo "{\n";

$rightbranch = checkbranch($famrow['branch']);
$rights = determineLivingPrivateRights($famrow, $rightbranch);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];

$famname = getFamilyName($famrow);
$namestr = uiTextSnippet('family') . ': ' . $famname;

$logstring = "<a href=\"familiesShowFamily.php?familyID=$familyID\">" . uiTextSnippet('familygroupfor') . " $famname</a>";
writelog($logstring);

$family = "\"id\":\"{$famrow['familyID']}\"";
//get husband & spouses
if ($famrow['husband']) {
  $query = "SELECT * FROM $people_table WHERE personID = \"{$famrow['husband']}\"";
  $result = tng_query($query);
  $husbrow = tng_fetch_assoc($result);

  $hrights = determineLivingPrivateRights($husbrow);
  $husbrow['allow_living'] = $hrights['living'];
  $husbrow['allow_private'] = $hrights['private'];

  $events = [];
  $family .= ',"father":{' . api_person($husbrow, $fullevents) . '}';
  tng_free_result($result);
}

//get wife & spouses
if ($famrow['wife']) {
  $query = "SELECT * FROM $people_table WHERE personID = \"{$famrow['wife']}\"";
  $result = tng_query($query);
  $wiferow = tng_fetch_assoc($result);

  $wrights = determineLivingPrivateRights($wiferow);
  $wiferow['allow_living'] = $wrights['living'];
  $wiferow['allow_private'] = $wrights['private'];

  $events = [];
  $family .= ',"mother":{' . api_person($wiferow, $fullevents) . '}';
  tng_free_result($result);
}

$events = [];
if ($rights['both']) {
  setMinEvent(['date' => $famrow['marrdate'], 'place' => $famrow['marrplace'], 'event' => 'MARR'], $famrow['marrdatetr']);
  setMinEvent(['date' => $famrow['divdate'], 'place' => $famrow['divplace'], 'event' => 'DIV'], $famrow['divdatetr']);

  if ($fullevents && $rights['lds']) {
    setMinEvent(['date' => $famrow['sealdate'], 'place' => $famrow['sealplace'], 'event' => 'SLGS'], $famrow['sealdatetr']);
  }

  if ($fullevents) {
    doCustomEvents($familyID, 'F');
  }
}
$eventstr = processEvents($events);
if ($eventstr) {
  $family .= ',' . $eventstr;
}

//for each child
$query = "SELECT $people_table.personID AS personID, branch, firstname, lnprefix, lastname, prefix, suffix, nameorder, living, private, famc, sex, birthdate, birthplace, altbirthdate, altbirthplace, haskids, deathdate, deathplace, burialdate, burialplace, baptdate, baptplace, confdate, confplace, initdate, initplace, endldate, endlplace, sealdate, sealplace FROM $people_table, children WHERE $people_table.personID = children.personID AND children.familyID = \"{$famrow['familyID']}\" ORDER BY ordernum";
$children = tng_query($query);

if ($children && tng_num_rows($children)) {
  $childcount = 0;
  $family .= ',"children":[';
  while ($childrow = tng_fetch_assoc($children)) {
    if ($childcount) {
      $family .= ',';
    }
    $childcount++;

    $crights = determineLivingPrivateRights($childrow);
    $childrow['allow_living'] = $crights['living'];
    $childrow['allow_private'] = $crights['private'];

    $events = [];
    $family .= '{' . api_person($childrow, $fullevents) . '}';
  }
  $family .= ']';
}
tng_free_result($children);

echo $family;

echo '}';