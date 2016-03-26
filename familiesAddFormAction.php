<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

$tree = $tree1;
if (!$allowAdd || ($assignedtree && $assignedtree != $tree)) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

require 'adminlog.php';
require 'datelib.php';

require 'geocodelib.php';
require 'deletelib.php';

$familyID = ucfirst($familyID);

if ($newfamily == "ajax" && $session_charset != "UTF-8") {
  $marrplace = tng_utf8_decode($marrplace);
  $divplace = tng_utf8_decode($divplace);
  $sealplace = tng_utf8_decode($sealplace);
  $marrtype = tng_utf8_decode($marrtype);
}
$marrplace = addslashes($marrplace);
$divplace = addslashes($divplace);
$sealplace = addslashes($sealplace);
$marrtype = addslashes($marrtype);

$marrdatetr = convertDate($marrdate);
$divdatetr = convertDate($divdate);
$sealdatetr = convertDate($sealdate);

$newdate = date("Y-m-d H:i:s", time() + (3600 * $time_offset));

$query = "SELECT familyID FROM $families_table WHERE familyID = \"$familyID\" AND gedcom = \"$tree\"";
$result = tng_query($query);

if ($result && tng_num_rows($result)) {
  $message = uiTextSnippet('family') . " $familyID " . uiTextSnippet('idexists');
  header("Location: familiesBrowse.php?message=$message");
  exit;
}

//delete all notes, citations & children linked to this person
deleteCitations($familyID, $tree);
deleteNoteLinks($familyID, $tree);
deleteChildren($familyID, $tree);

$places = array();
if (trim($marrplace) && !in_array($marrplace, $places)) {
  array_push($places, $marrplace);
}
if (trim($divplace) && !in_array($divplace, $places)) {
  array_push($places, $divplace);
}
if (trim($sealplace) && !in_array($sealplace, $places)) {
  array_push($places, $sealplace);
}
$placetree = $tngconfig['places1tree'] ? "" : $tree;
foreach ($places as $place) {
  $temple = strlen($place) == 5 && $place == strtoupper($place) ? 1 : 0;
  $query = "INSERT IGNORE INTO $places_table (gedcom,place,placelevel,zoom,geoignore,temple) VALUES (\"$placetree\",\"$place\",\"0\",\"0\",\"0\",\"$temple\")";
  $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
  if ($tngconfig['autogeo'] && tng_affected_rows()) {
    $ID = tng_insert_id();
    $message = geocode($place, 0, $ID);
  }
}

//get living from husband, wife
if ($husband) {
  $spquery = "SELECT living FROM $people_table WHERE personID = \"$husband\" AND gedcom = \"$tree\"";
  $spouselive = tng_query($spquery) or die(uiTextSnippet('cannotexecutequery') . ": $spquery");
  $spouserow = tng_fetch_assoc($spouselive);
  $husbliving = $spouserow['living'];

  $query = "SELECT husborder FROM $families_table WHERE gedcom = \"$tree\" AND husband = \"$husband\" ORDER BY husborder DESC";
  $husbresult = tng_query($query);
  $husbrow = tng_fetch_assoc($husbresult);
  tng_free_result($husbresult);

  $husborder = $husbrow['husborder'] + 1;
} else {
  $husbliving = 0;
  $husborder = 0;
}

if ($wife) {
  $spquery = "SELECT living FROM $people_table WHERE personID = \"$wife\" AND gedcom = \"$tree\"";
  $spouselive = tng_query($spquery) or die(uiTextSnippet('cannotexecutequery') . ": $spquery");
  $spouserow = tng_fetch_assoc($spouselive);
  $wifeliving = $spouserow['living'];

  $query = "SELECT wifeorder FROM $families_table WHERE gedcom = \"$tree\" AND wife = \"$wife\" ORDER BY wifeorder DESC";
  $wiferesult = tng_query($query);
  $wiferow = tng_fetch_assoc($wiferesult);
  tng_free_result($wiferesult);

  $wifeorder = $wiferow['wifeorder'] + 1;
} else {
  $wifeliving = 0;
  $wifeorder = 0;
}
$familyliving = ($living || $husbliving || $wifeliving) ? 1 : 0;
if (!$private) {
  $private = 0;
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
$query = "INSERT INTO $families_table (familyID,husband,husborder,wife,wifeorder,living,private,marrdate,marrdatetr,marrplace,marrtype,divdate,divdatetr,divplace,sealdate,sealdatetr,sealplace,changedate,gedcom,branch,changedby,status,edituser,edittime) VALUES(\"$familyID\",\"$husband\",\"$husborder\",\"$wife\",\"$wifeorder\",\"$familyliving\",\"$private\",\"$marrdate\",\"$marrdatetr\",\"$marrplace\",\"$marrtype\",\"$divdate\",\"$divdatetr\",\"$divplace\",\"$sealdate\",\"$sealdatetr\",\"$sealplace\",\"$newdate\",\"$tree\",\"$allbranches\",\"$currentuser\",\"\",\"\",\"0\")";
$result = tng_query($query);

$branchlist = explode(',', $allbranches);
foreach ($branchlist as $b) {
  $query = "INSERT IGNORE INTO $branchlinks_table (branch,gedcom,persfamID) VALUES(\"$b\",\"$tree\",\"$familyID\")";
  $result = tng_query($query);
}

if ($lastperson) {
  $haskids = getHasKids($tree, $lastperson);

  $query = "INSERT INTO $children_table (familyID,personID,ordernum,gedcom,mrel,frel,haskids,parentorder,sealdate,sealdatetr,sealplace) VALUES (\"$familyID\",\"$lastperson\",1,\"$tree\",\"\",\"\",$haskids,0,\"\",\"0000-00-00\",\"\")";
  $result = tng_query($query);

  if ($husband) {
    $query = "UPDATE $children_table SET haskids=\"1\" WHERE personID = \"$husband\" AND gedcom = \"$tree\"";
    $result2 = tng_query($query);
  }
  if ($wife) {
    $query = "UPDATE $children_table SET haskids=\"1\" WHERE personID = \"$wife\" AND gedcom = \"$tree\"";
    $result2 = tng_query($query);
  }

  $query = "UPDATE $people_table SET famc=\"$familyID\" WHERE personID = \"$lastperson\" AND gedcom = \"$tree\"";
  $result = tng_query($query);
}
adminwritelog("<a href=\"familiesEdit.php?familyID=$familyID&amp;tree=$tree\">" . uiTextSnippet('addnewfamily') . ": $tree/$familyID</a>");

if ($newfamily == "ajax") {
  echo "1";
} else {
  header("Location: familiesEdit.php?familyID=$familyID&tree=$tree&cw=$cw&added=1");
}