<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowAdd) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
require 'adminlog.php';
require 'geocodelib.php';

$place = addslashes($place);
$placelevel = addslashes($placelevel);
$zoom = addslashes($zoom);
$notes = addslashes($notes);

$latitude = preg_replace('/,/', '.', addslashes($latitude));
$longitude = preg_replace('/,/', '.', addslashes($longitude));

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
$query = "INSERT IGNORE INTO $places_table (place, placelevel, temple, latitude, longitude, zoom, notes, geoignore) VALUES ('$place', '$placelevel', '$temple', '$latitude', '$longitude', '$zoom', '$notes', '0')";
$result = tng_query($query);
$success = tng_affected_rows();

if ($success) {
  $placeID = tng_insert_id();
  if ($tngconfig['autogeo']) {
    $message = geocode($place, 0, $placeID);
  }
  adminwritelog("<a href=\"placesEdit.php?ID=$placeID\">" . uiTextSnippet('addnewplace') . ": $placeID - " . stripslashes($place) . '</a>');

  // [ts] testing before and after stuff. trivial example here. cleaner just to do many inline appends?
  $message = uiTextSnippet('place', ['after' => ' ']) . stripslashes($place) . uiTextSnippet('succadded', ['before' => ' ', 'after' => '.']);
} else {
  $message = uiTextSnippet('place') . ' ' . stripslashes($place) . ' ' . uiTextSnippet('idexists');
}
header('Location: placesBrowse.php?message=' . urlencode($message));
