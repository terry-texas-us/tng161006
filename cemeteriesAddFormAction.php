<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowAdd) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
require("adminlog.php");

if ($newfile && $newfile != "none") {
  if (substr($maplink, 0, 1) == "/") {
    $maplink = substr($maplink, 1);
  }
  $newpath = "$rootpath$headstonepath/$maplink";

  if (move_uploaded_file($newfile, $newpath)) {
    chmod($newpath, 0644);
  } else {
    $message = uiTextSnippet('mapnotcopied') . " $newpath " . uiTextSnippet('improperpermissions') . ".";
    header("Location: cemeteriesBrowse.php?message=" . urlencode($message));
    exit;
  }
}
$cemname = addslashes($cemname);
$city = addslashes($city);
$county = addslashes($county);
$state = addslashes($state);
$country = addslashes($country);
$zoom = addslashes($zoom);
$notes = addslashes($notes);
$place = addslashes($place);

$latitude = preg_replace('/,/', '.', addslashes($latitude));
$longitude = preg_replace('/,/', '.', addslashes($longitude));

if ($latitude && $longitude && !$zoom) {
  $zoom = 13;
}
if (!$zoom) {
  $zoom = 0;
}
$query = "INSERT INTO $cemeteries_table (cemname,maplink,city,county,state,country,latitude,longitude,zoom,notes,place) "
        . "VALUES (\"$cemname\",\"$maplink\",\"$city\",\"$county\",\"$state\",\"$country\",\"$latitude\",\"$longitude\",\"$zoom\",\"$notes\",\"$place\")";
$result = tng_query($query);
$cemeteryID = tng_insert_id();

$tree = $assignedtree;
if (!$tree) {
  $query = "SELECT gedcom FROM $trees_table LIMIT 2";
  $result2 = tng_query($query);
  if (tng_num_rows($result2) == 1) {
    $row = tng_fetch_assoc($result2);
    $tree = $row['gedcom'];
  }
  tng_free_result($result2);
}
$place = trim($place);
if ($place) {
  //first check to see if any place exists in any tree with new place name
  $query = "SELECT * FROM $places_table WHERE place = \"$place\"";
  $result = tng_query($query);

  if (!tng_num_rows($result)) {
    if (!isset($usecoords)) {
      $latitude = $longitude = "";
      $zoom = 0;
    }
    $placetree = $tngconfig['places1tree'] ? "" : $tree;
    $query = "INSERT IGNORE INTO $places_table (gedcom,place,placelevel,latitude,longitude,zoom,notes) "
            . "VALUES (\"$placetree\",\"$place\",\"0\",\"$latitude\",\"$longitude\",\"$zoom\",\"$notes\")";
    $result3 = tng_query($query);
  } elseif (isset($usecoords)) {
    $treestr = $tree && $tngconfig['places1tree'] ? "gedcom=\"$tree\" AND " : "";
    $query = "UPDATE $places_table SET latitude=\"$latitude\",longitude=\"$longitude\",zoom=\"$zoom\" WHERE {$treestr}place=\"$place\"";
    $result3 = tng_query($query);
  }
  tng_free_result($result);
}
adminwritelog("<a href=\"cemeteriesEdit.php?cemeteryID=$cemeteryID\">" . uiTextSnippet('addnewcemetery') . ": $cemeteryID - $cemname</a>");

$message = uiTextSnippet('cemetery') . " $cemeteryID " . uiTextSnippet('succadded') . ".";
header("Location: cemeteriesBrowse.php?message=" . urlencode($message));
