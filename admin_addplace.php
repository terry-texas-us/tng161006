<?php

include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if (!$allow_add) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
require("adminlog.php");
include("geocodelib.php");

$place = addslashes($place);
$placelevel = addslashes($placelevel);
$zoom = addslashes($zoom);
$notes = addslashes($notes);

$latitude = preg_replace("/,/", ".", addslashes($latitude));
$longitude = preg_replace("/,/", ".", addslashes($longitude));

if ($latitude && $longitude && $placelevel && !$zoom) {
  $zoom = 13;
}
if (!$zoom) {
  $zoom = 0;
}
if (!$placelevel) {
  $placelevel = 0;
}
if (!$temple) {
  $temple = 0;
}
if ($tngconfig['places1tree']) {
  $tree = "";
}
$query = "INSERT IGNORE INTO $places_table (gedcom,place,placelevel,temple,latitude,longitude,zoom,notes,geoignore) VALUES (\"$tree\",\"$place\",\"$placelevel\",\"$temple\",\"$latitude\",\"$longitude\",\"$zoom\",\"$notes\",\"0\")";
$result = tng_query($query);
$success = tng_affected_rows();

if ($success) {
  $placeID = tng_insert_id();
  if ($tngconfig['autogeo']) {
    $message = geocode($place, 0, $placeID);
  }
  adminwritelog("<a href=\"admin_editplace.php?ID=$placeID\">" . uiTextSnippet('addnewplace') . ": $placeID - " . stripslashes($place) . "</a>");

  // [ts] testing before and after stuff. trivial example here. cleaner just to do many inline appends?
  $message = uiTextSnippet('place', ['after' => ' ']) . stripslashes($place) . uiTextSnippet('succadded', ['before' => ' ', 'after' => '.']);
} else {
  $message = uiTextSnippet('place') . ' ' . stripslashes($place) . ' ' . uiTextSnippet('idexists');
}
header("Location: admin_places.php?message=" . urlencode($message));
