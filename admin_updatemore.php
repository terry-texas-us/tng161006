<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

if (!$allowEdit) {
  exit;
}
require 'adminlog.php';

$rval = 0;

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
  $phone = tng_utf8_decode($phone);
  $email = tng_utf8_decode($email);
  $www = tng_utf8_decode($www);
}
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

if ($addressID) {
  if ($address1 || $address2 || $city || $state || $zip || $country || $phone || $email || $www) {
    $query = "UPDATE addresses SET address1='$address1', address2='$address2', city='$city', state='$state', zip='$zip', country='$country', phone='$phone', email='$email', www='$www' WHERE addressID = '$addressID'";
    $result = tng_query($query);
    adminwritelog(uiTextSnippet('modifyaddress') . ": $addressID");
    $rval = 1;
  } else {
    $query = "DELETE FROM addresses WHERE addressID = '$addressID'";
    $result = tng_query($query);
    adminwritelog(uiTextSnippet('deleteaddress') . ": $addressID");
    $addressID = '';
  }
} elseif ($address1 || $address2 || $city || $state || $zip || $country || $phone || $email || $www) {
  $query = "INSERT INTO addresses (address1, address2, city, state, zip, country, phone, email, www) VALUES('$address1', '$address2', '$city', '$state', '$zip', '$country', '$phone', '$email', '$www')";
  $result = tng_query($query);
  $addressID = tng_insert_id();
  adminwritelog(uiTextSnippet('addnewaddress') . ": $addressID");
  $rval = 1;
}
if ($eventID) {
  if ($age || $agency || $cause || $addressID || $info) {
    $query = "UPDATE events SET age='$age', agency='$agency', cause='$cause', addressID='$addressID', info='$info' WHERE eventID='$eventID'";
    $result = tng_query($query);
    adminwritelog(uiTextSnippet('modifyevent') . ": $eventID");
    $rval = 1;
  } else {
    $query = "DELETE FROM events WHERE eventID='$eventID'";
    $result = tng_query($query);
    adminwritelog(uiTextSnippet('deleteevent') . ": $eventID");
  }
} else {
  if ($age || $agency || $cause || $addressID || $info) {
    $query = "INSERT INTO events (eventtypeID, persfamID, age, agency, cause, addressID, info, parenttag, eventdate, eventdatetr, eventplace) VALUES(0, '$persfamID', '$age', '$agency', '$cause', '$addressID', '$info', '$eventtypeID', '', '0000-00-00', '')";
    $result = tng_query($query);
    $eventID = tng_insert_id();
    adminwritelog(uiTextSnippet('addnewevent') . ": $eventID - $eventtypeID/$persfamID");
    $rval = 1;
  }
}
echo json_encode(['status' => $rval, 'eventId' => $eventID, 'eventTypeId' => $eventtypeID]);
