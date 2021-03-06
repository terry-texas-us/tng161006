<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
require 'adminlog.php';
require 'datelib.php';

$eventdate = addslashes($newdate);
$eventplace = addslashes($newplace);
$info = addslashes($newinfo);

$query = "SELECT * FROM temp_events WHERE tempID = '$tempID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$personID = $row['personID'];
$familyID = $row['familyID'];
$eventID = $row['eventID'];

$persfamID = $personID ? uiTextSnippet('person') . ' ' . $personID : uiTextSnippet('family') . ' ' . $familyID;

$changedate = date('Y-m-d H:i:s', time() + (3600 * $timeOffset));
$eventdatetr = convertDate($eventdate);
//don't forget to save date

if ($choice == uiTextSnippet('savedel')) {
  if (is_numeric($eventID)) {
    $query = "UPDATE events SET eventdate=\"$eventdate\", eventdatetr=\"$eventdatetr\", eventplace=\"$eventplace\", info=\"$info\" WHERE eventID=\"$eventID\"";
    $result = tng_query($query);

    if ($row['type'] == 'F') {
      $query = "UPDATE families SET changedate = \"$changedate\", changedby = \"{$row['user']}\" WHERE familyID = '$familyID'";
    } else {
      $query = "UPDATE people SET changedate = \"$changedate\", changedby = \"{$row['user']}\" WHERE personID = '$personID'";
    }
    $result = tng_query($query);
  } else {
    $needfamilies = 0;
    $needchildren = 0;
    switch ($eventID) {
      case 'TITL':
        $factfield = "title = \"$info\"";
        break;
      case 'NPFX':
        $factfield = "prefix = \"$info\"";
        break;
      case 'NSFX':
        $factfield = "suffix = \"$info\"";
        break;
      case 'NICK':
        $factfield = "nickname = \"$info\"";
        break;
      case 'BIRT':
        $datefield = "birthdate = \"$eventdate\", birthdatetr = \"$eventdatetr\"";
        $placefield = "birthplace = \"$eventplace\"";
        break;
      case 'CHR':
        $datefield = "altbirthdate = \"$eventdate\", altbirthdatetr = \"$eventdatetr\"";
        $placefield = "altbirthplace = \"$eventplace\"";
        break;
      case 'BAPL':
        $datefield = "baptdate = \"$eventdate\", baptdatetr = \"$eventdatetr\"";
        $placefield = "baptplace = \"$eventplace\"";
        break;
      case 'CONF':
        $datefield = "confdate = \"$eventdate\", confdatetr = \"$eventdatetr\"";
        $placefield = "confplace = \"$eventplace\"";
        break;
      case 'INIT':
        $datefield = "initdate = \"$eventdate\", initdatetr = \"$eventdatetr\"";
        $placefield = "initplace = \"$eventplace\"";
        break;
      case 'ENDL':
        $datefield = "endldate = \"$eventdate\", endldatetr = \"$eventdatetr\"";
        $placefield = "endlplace = \"$eventplace\"";
        break;
      case 'DEAT':
        $datefield = "deathdate = \"$eventdate\", deathdatetr = \"$eventdatetr\"";
        $placefield = "deathplace = \"$eventplace\"";
        break;
      case 'BURI':
        $datefield = "burialdate = \"$eventdate\", burialdatetr = \"$eventdatetr\"";
        $placefield = "burialplace = \"$eventplace\"";
        break;
      case 'MARR':
        $datefield = "marrdate = \"$eventdate\", marrdatetr = \"$eventdatetr\"";
        $placefield = "marrplace = \"$eventplace\"";
        $factfield = "marrtype = \"$info\"";
        $needfamilies = 1;
        break;
      case 'DIV':
        $datefield = "divdate = \"$eventdate\", divdatetr = \"$eventdatetr\"";
        $placefield = "divplace = \"$eventplace\"";
        $needfamilies = 1;
        break;
      case 'SLGS':
        $datefield = "sealdate = \"$eventdate\", sealdatetr = \"$eventdatetr\"";
        $placefield = "sealplace = \"$eventplace\"";
        $needfamilies = 1;
        break;
      case 'SLGC':
        $datefield = "sealdate = \"$eventdate\", sealdatetr = \"$eventdatetr\"";
        $placefield = "sealplace = \"$eventplace\"";
        $needchildren = 1;
        break;
    }
    $fieldstr = $needchildren ? '' : "changedate = \"$changedate\", changedby = \"{$row['user']}\"";
    if ($datefield) {
      $fieldstr .= $fieldstr ? ", $datefield" : $datefield;
    }
    if ($placefield) {
      $fieldstr .= $fieldstr ? ", $placefield" : $placefield;
    }
    if ($factfield) {
      $fieldstr .= $fieldstr ? ", $factfield" : $factfield;
    }
    if ($needfamilies) {
      $query = "UPDATE families SET $fieldstr WHERE familyID = '$familyID'";
    } elseif ($needchildren) {
      $query = "UPDATE people SET changedate = \"$changedate\", changedby=\"{$row['user']}\" WHERE personID = '$personID'";
      $result = tng_query($query);
      $query = "UPDATE children SET $fieldstr WHERE familyID = '$familyID' AND personID = '$personID'";
    } else {
      $query = "UPDATE people SET $fieldstr WHERE personID = '$personID'";
    }
    $result = tng_query($query);
  }
  if ($eventplace) {
    $query = "INSERT IGNORE INTO places (place, placelevel, zoom) VALUES ('$eventplace', '0', '0')";
    $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
  }
  $succmsg = uiTextSnippet('tentadd');
}
if ($choice != uiTextSnippet('postpone')) {
  $query = "DELETE FROM temp_events WHERE tempID = '$tempID'";
  $result = tng_query($query);

  if ($choice == uiTextSnippet('igndel')) {
    $succmsg = uiTextSnippet('tentdel');
  }
} else {
  $succmsg = '';
  $message = '';
}
if ($succmsg) {
  if ($row['type'] == 'F') {
    adminwritelog("<a href=\"familiesEdit.php?familyID=$family\">$choice (" . uiTextSnippet('family') . "): {$row['familyID']}</a>");
  } else {
    adminwritelog("<a href=\"peopleEdit.php?personID=$personID\">$choice (" . uiTextSnippet('person') . "): {$row['personID']}</a>");
  }
  $message = uiTextSnippet('tentdata') . " $persfamID $succmsg.";
}

header("Location: admin_findreview.php?type=$type&message=" . urlencode($message));
