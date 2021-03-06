<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

require 'adminlog.php';
require 'datelib.php';

if (!$allowAdd) {
  exit;
}
require 'deletelib.php';

//this line needed to prevent garbage chars in IS0-8859-2
header('Content-type:text/html; charset=' . $sessionCharset);
$personID = ucfirst($personID);

if ($sessionCharset != 'UTF-8') {
  $firstname = tng_utf8_decode($firstname);
  $lnprefix = tng_utf8_decode($lnprefix);
  $lastname = tng_utf8_decode($lastname);
  $nickname = tng_utf8_decode($nickname);
  $prefix = tng_utf8_decode($prefix);
  $suffix = tng_utf8_decode($suffix);
  $title = tng_utf8_decode($title);
  $birthplace = tng_utf8_decode($birthplace);
  $altbirthplace = tng_utf8_decode($altbirthplace);
  $deathplace = tng_utf8_decode($deathplace);
  $burialplace = tng_utf8_decode($burialplace);
  $baptplace = tng_utf8_decode($baptplace);
  $confplace = tng_utf8_decode($confplace);
  $initplace = tng_utf8_decode($initplace);
  $endlplace = tng_utf8_decode($endlplace);
}
$firstname = addslashes($firstname);
$lnprefix = addslashes($lnprefix);
$lastname = addslashes($lastname);
$nickname = addslashes($nickname);
$prefix = addslashes($prefix);
$suffix = addslashes($suffix);
$title = addslashes($title);
$birthplace = addslashes($birthplace);
$altbirthplace = addslashes($altbirthplace);
$deathplace = addslashes($deathplace);
$burialplace = addslashes($burialplace);
$baptplace = addslashes($baptplace);
$confplace = addslashes($confplace);
$initplace = addslashes($initplace);
$endlplace = addslashes($endlplace);

$birthdatetr = convertDate($birthdate);
$altbirthdatetr = convertDate($altbirthdate);
$deathdatetr = convertDate($deathdate);
$burialdatetr = convertDate($burialdate);
$baptdatetr = convertDate($baptdate);
$confdatetr = convertDate($confdate);
$initdatetr = convertDate($initdate);
$endldatetr = convertDate($endldate);

$newdate = date('Y-m-d H:i:s', time() + (3600 * $timeOffset));

$query = "SELECT personID FROM people WHERE personID = '$personID'";
$result = tng_query($query);

//delete all notes & citations linked to this person
deleteCitations($personID);
deleteNoteLinks($personID);

if ($result && tng_num_rows($result)) {
  echo 'error:' . uiTextSnippet('person') . " $personID " . uiTextSnippet('idexists');
} else {
  $places = [];
  if (trim($birthplace) && !in_array($birthplace, $places)) {
    array_push($places, $birthplace);
  }
  if (trim($altbirthplace) && !in_array($altbirthplace, $places)) {
    array_push($places, $altbirthplace);
  }
  if (trim($deathplace) && !in_array($deathplace, $places)) {
    array_push($places, $deathplace);
  }
  if (trim($burialplace) && !in_array($burialplace, $places)) {
    array_push($places, $burialplace);
  }
  if (trim($baptplace) && !in_array($baptplace, $places)) {
    array_push($places, $baptplace);
  }
  if (trim($confplace) && !in_array($confplace, $places)) {
    array_push($places, $confplace);
  }
  if (trim($initplace) && !in_array($initplace, $places)) {
    array_push($places, $initplace);
  }
  if (trim($endlplace) && !in_array($endlplace, $places)) {
    array_push($places, $endlplace);
  }
  foreach ($places as $place) {
    $temple = strlen($place) == 5 && $place == strtoupper($place) ? 1 : 0;
    $query = "INSERT IGNORE INTO places (place, placelevel, zoom, geoignore, temple) VALUES('$place', '0', '0', '0', '$temple')";
    $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
  }
  if (is_array($branch)) {
    foreach ($branch as $b) {
      if ($b) {
        $allbranches = $allbranches ? "$allbranches,$b" : $b;
      }
    }
  } else {
    $allbranches = $branch;
  }
  if (!$living) {
    $living = 0;
  }
  if (!$private) {
    $private = 0;
  }
  if (!$burialtype) {
    $burialtype = 0;
  }
  //$firstname = preg_replace('/%(['0-9a-f']{2})/ie', 'chr(hexdec($1))', (string) $firstname);
  if ($type != 'child') {
    $familyID = '';
  }
  $meta = metaphone($lnprefix . $lastname);
  $query = "INSERT INTO people (personID, firstname, lnprefix, lastname, nickname, prefix, suffix, title, nameorder, living, private, birthdate, birthdatetr, birthplace, sex, altbirthdate, altbirthdatetr, altbirthplace, deathdate, deathdatetr, deathplace, burialdate, burialdatetr, burialplace, burialtype, baptdate, baptdatetr, baptplace, confdate, confdatetr, confplace, initdate, initdatetr, initplace, endldate, endldatetr, endlplace, changedate, branch, changedby, famc, metaphone, edituser, edittime)
    VALUES('$personID', '$firstname', '$lnprefix', '$lastname', '$nickname', '$prefix', '$suffix', '$title', '0', '$living', '$private', '$birthdate', '$birthdatetr', '$birthplace', '$sex', '$altbirthdate', '$altbirthdatetr', '$altbirthplace', '$deathdate', '$deathdatetr', '$deathplace', '$burialdate', '$burialdatetr', '$burialplace', '$burialtype', '$baptdate', '$baptdatetr', '$baptplace', '$confdate', '$confdatetr', '$confplace', '$initdate', '$initdatetr', '$initplace', '$endldate', '$endldatetr', '$endlplace', '$newdate', '$allbranches', '$currentuser', '$familyID', '$meta', '', '0')";
  $result = tng_query($query);
  $ID = tng_insert_id();

  $query = "SELECT personID, lastname, firstname, lnprefix, birthdate, altbirthdate, prefix, suffix, nameorder FROM people WHERE ID=\"$ID\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  tng_free_result($result);

  $branchlist = explode(',', $allbranches);
  foreach ($branchlist as $b) {
    $query = "INSERT IGNORE INTO branchlinks (branch, persfamID) VALUES('$b', '$personID')";
    $result = tng_query($query);
  }
  $row['allow_living'] = $row['allow_private'] = 1;

  if ($type == 'child') {
    if ($familyID) {
      $query = "SELECT personID FROM children WHERE familyID = '$familyID'";
      $result = tng_query($query);
      $order = tng_num_rows($result) + 1;
      tng_free_result($result);

      $query = "INSERT INTO children (familyID, personID, ordernum, frel, mrel, haskids, parentorder, sealdate, sealdatetr, sealplace) VALUES ('$familyID', '$personID', $order, '$frel', '$mrel', 0, 0, '', '0000-00-00', '')";
      $result = tng_query($query);

      $query = "SELECT husband,wife FROM families WHERE familyID = '$familyID'";
      $result = tng_query($query);
      $famrow = tng_fetch_assoc($result);
      if ($famrow['husband']) {
        $query = "UPDATE children SET haskids=\"1\" WHERE personID = \"{$famrow['husband']}\"";
        $result2 = tng_query($query);
      }
      if ($famrow['wife']) {
        $query = "UPDATE children SET haskids=\"1\" WHERE personID = \"{$famrow['wife']}\"";
        $result2 = tng_query($query);
      }
      tng_free_result($result);
    }
    if ($row['birthdate']) {
      $birthdate = uiTextSnippet('birthabbr') . ' ' . $row['birthdate'];
    } else {
      if ($row['altbirthdate']) {
        $birthdate = uiTextSnippet('chrabbr') . ' ' . $row['altbirthdate'];
      } else {
        $birthdate = '';
      }
    }
    $rval = "<div class='sortrow' id='child_$personID' style='width: 500px; clear: both; display: none'";
    $rval .= " onmouseover=\"$('#unlinkc_$personID').css('visibility','visible');\" onmouseout=\"$('#unlinkc_$personID').css('visibility','hidden');\">\n";
    $rval .= "<table width='100%'><tr>\n";
    $rval .= "<td class='dragarea'>";
    $rval .= "<img src='img/admArrowUp.gif' alt=''>" . uiTextSnippet('drag') . "\n";
    $rval .= "<img src='img/admArrowDown.gif' alt=''>\n";
    $rval .= "</td>\n";
    $rval .= "<td class='childblock'>\n";

    $name = getName($row);
    $rval .= "<div id=\"unlinkc_$personID\" class=\"small hide-right\"><a href='#' onclick=\"return unlinkChild('$personID','child_unlink');\">" . uiTextSnippet('remove') . "</a> &nbsp; | &nbsp; <a href='#' onclick=\"return unlinkChild('$personID','child_delete');\">" . uiTextSnippet('delete') . '</a></div>';
    $rval .= "<a href='#' onclick=\"EditChild('$personID');\">" . $name . "</a> - $personID<br>$birthdate</div>\n</td>\n</tr>\n</table>\n</div>\n";
    echo $rval;
  } elseif ($type == 'spouse') {
    $name = getName($row);
    $name = preg_replace('/\"/', '\\\"', $name);
    echo "{\"id\":\"{$row['personID']}\",\"name\":\"$name\"}";
  }
  adminwritelog("<a href=\"peopleEdit.php?personID=$personID\">" . uiTextSnippet('addnewperson') . ": $personID</a>");
  if ($needped) {
    header("Location: pedigree.php?personID=$personID");
  }
}