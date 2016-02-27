<?php

include("begin.php");
include("adminlib.php");

include("checklogin.php");

if (!$allow_add) {
  $message = uiTextSnippet('norights');
  exit;
}

require("datelib.php");
require("adminlog.php");

include("geocodelib.php");

$persfamID = ucfirst($persfamID);

$orgplace = $eventplace;
if ($session_charset != "UTF-8") {
  $eventplace = tng_utf8_decode($eventplace);
  $info = tng_utf8_decode($info);
  $age = tng_utf8_decode($age);
  $agency = tng_utf8_decode($agency);
  $cause = tng_utf8_decode($cause);
  $address1 = tng_utf8_decode($address1);
  $address2 = tng_utf8_decode($address2);
  $city = tng_utf8_decode($city);
  $state = tng_utf8_decode($state);
  $zip = tng_utf8_decode($zip);
  $country = tng_utf8_decode($country);
  $phone = tng_utf8_decode($phone);
  $email = tng_utf8_decode($email);
  $www = tng_utf8_decode($www);
}

$eventdate = addslashes($eventdate);
$eventplace = addslashes($eventplace);
$info = addslashes($info);
$age = addslashes($age);
$agency = addslashes($agency);
$cause = addslashes($cause);
$address1 = addslashes($address1);
$address2 = addslashes($address2);
$city = addslashes($city);
$state = addslashes($state);
$zip = addslashes($zip);
$country = addslashes($country);
$phone = addslashes($phone);
$email = addslashes($email);
$www = addslashes($www);

$eventdatetr = convertDate($eventdate);

if (trim($eventplace)) {
  $placetree = $tngconfig['places1tree'] ? "" : $tree;
  $query = "INSERT IGNORE INTO $places_table (gedcom,place,placelevel,zoom) VALUES (\"$placetree\",\"$eventplace\",\"0\",\"0\")";
  $result = @tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
  if ($tngconfig['autogeo'] && tng_affected_rows()) {
    $ID = tng_insert_id();
    $message = geocode($eventplace, 0, $ID);
  }
}

if ($address1 || $address2 || $city || $state || $zip || $country || $phone || $email || $www) {
  $query = "INSERT INTO $address_table (address1, address2, city, state, zip, country, gedcom, phone, email, www)  VALUES(\"$address1\", \"$address2\", \"$city\", \"$state\", \"$zip\", \"$country\", \"$tree\", \"$phone\", \"$email\", \"$www\")";
  $result = tng_query($query);
  $addressID = tng_insert_id();
} else {
  $addressID = "";
}

$query = "INSERT INTO $events_table (eventtypeID, persfamID, eventdate, eventdatetr, eventplace, age, agency, cause, addressID, info, gedcom, parenttag)  VALUES(\"$eventtypeID\", \"$persfamID\", \"$eventdate\", \"$eventdatetr\", \"$eventplace\", \"$age\", \"$agency\", \"$cause\", \"$addressID\", \"$info\", \"$tree\", \"\")";
$result = tng_query($query);
$eventID = tng_insert_id();

adminwritelog(uiTextSnippet('addnewevent') . ": $eventtypeID/$tree/$persfamID");

$query = "SELECT display FROM $eventtypes_table WHERE eventtypeID = \"$eventtypeID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);

$display = htmlspecialchars(getEventDisplay($row['display']), ENT_QUOTES, $session_charset);

$info = str_replace("\r", " ", $info);
$info = htmlspecialchars(str_replace("\n", " ", $info), ENT_QUOTES, $session_charset);
$truncated = substr($info, 0, 90);
$info = strlen($info) > 90 ? substr($truncated, 0, strrpos($truncated, ' ')) . '&hellip;' : $info;

header("Content-type:text/html; charset=" . $session_charset);
$eventplace = stripslashes($eventplace);
if ($eventID) {
  echo "{\"id\":\"$eventID\",\"persfamID\":\"$persfamID\",\"tree\":\"$tree\",\"display\":\"$display\",\"eventdate\":\"$eventdate\",\"eventplace\":\"$eventplace\",\"info\":\"" . stripslashes($info) . "\",\"allow_edit\":$allow_edit,\"allow_delete\":$allow_delete}";
} else {
  echo "{\"id\":0}";
}