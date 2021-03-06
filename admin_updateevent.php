<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

if (!$allowEdit) {
  exit;
}
require 'datelib.php';
require 'adminlog.php';

require 'geocodelib.php';

if ($sessionCharset != 'UTF-8') {
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

if ($addressID) {
  if ($address1 || $address2 || $city || $state || $zip || $country || $phone || $email || $www) {
    $query = "UPDATE addresses SET address1 = '$address1', address2 = '$address2', city = '$city', state = '$state', zip = '$zip\", country = '$country', gedcom = '', phone = '$phone', email = '$email', www = '$www' WHERE addressID = '$addressID'";
  } else {
    $query = "DELETE FROM addresses WHERE addressID = '$addressID'";
    $addressID = '';
  }
  $result = tng_query($query);
} elseif ($address1 || $address2 || $city || $state || $zip || $country || $phone || $email || $www) {
  $query = "INSERT INTO addresses (address1, address2, city, state, zip, country, phone, email, www) VALUES('$address1', '$address2', '$city', '$state', '$zip', '$country', '$phone', '$email', '$www')";
  $result = tng_query($query);
  $addressID = tng_insert_id();
}

$query = "UPDATE events SET eventdate=\"$eventdate\", eventdatetr=\"$eventdatetr\", eventplace=\"$eventplace\", age=\"$age\", agency=\"$agency\", cause=\"$cause\", addressID=\"$addressID\", info=\"$info\" WHERE eventID=\"$eventID\"";
$result = tng_query($query);

if (trim($eventplace)) {
  $query = "INSERT IGNORE INTO places (place, placelevel, zoom) VALUES ('$eventplace', '0', '0')";
  $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
  if ($tngconfig['autogeo'] && tng_affected_rows()) {
    $ID = tng_insert_id();
    $message = geocode($eventplace, 0, $ID);
  }
}
adminwritelog(uiTextSnippet('modifyevent') . ": $eventID");

$query = "SELECT display FROM eventtypes, events WHERE eventtypes.eventtypeID = events.eventtypeID AND eventID = \"$eventID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);

$display = htmlspecialchars(getEventDisplay($row['display']), ENT_QUOTES, $sessionCharset);

$info = str_replace("\r", ' ', $info);
$info = htmlspecialchars(str_replace("\n", ' ', $info), ENT_QUOTES, $sessionCharset);
$truncated = substr($info, 0, 90);
$info = strlen($info) > 90 ? substr($truncated, 0, strrpos($truncated, ' ')) . '&hellip;' : $info;

header('Content-type:text/html; charset=' . $sessionCharset);
echo "{\"display\":\"$display\",\"eventdate\":\"$eventdate\",\"eventplace\":\"" . stripslashes($eventplace) . '","info":"' . stripslashes($info) . '"}';