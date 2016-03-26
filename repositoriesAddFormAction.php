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

$repoID = ucfirst($repoID);

$reponame = addslashes($reponame);
$address1 = addslashes($address1);
$address2 = addslashes($address2);
$city = addslashes($city);
$state = addslashes($state);
$zip = addslashes($zip);
$country = addslashes($country);
$phone = addslashes($phone);
$email = addslashes($email);
$www = addslashes($www);

$newdate = date("Y-m-d H:i:s", time() + (3600 * $time_offset));

if ($address1 || $address2 || $city || $state || $zip || $country || $phone || $email || $www) {
  $query = "INSERT INTO $address_table (address1, address2, city, state, zip, country, gedcom, phone, email, www)  VALUES(\"$address1\", \"$address2\", \"$city\", \"$state\", \"$zip\", \"$country\", \"$tree\", \"$phone\", \"$email\", \"$www\")";
  $result = tng_query($query);
  $addressID = tng_insert_id();
} else {
  $addressID = "";
}

if (!$addressID) {
  $addressID = 0;
}
$query = "INSERT INTO $repositories_table (repoID,reponame,addressID,changedate,gedcom,changedby) VALUES (\"$repoID\",\"$reponame\",\"$addressID\",\"$newdate\",\"$tree1\",\"$currentuser\")";
$result = tng_query($query);

adminwritelog("<a href=\"repositoriesEdit.php?repoID=$repoID&tree=$tree\">" . uiTextSnippet('addnewrepo') . ": $tree/$repoID</a>");

header("Location: repositoriesEdit.php?repoID=$repoID&tree=$tree&added=1");
