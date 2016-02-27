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

$original_name = $newitem;
if ($session_charset != "UTF-8") {
  $newitem = tng_utf8_decode($newitem);
}
$newname = addslashes($newitem);

if ($entity == "state") {
  $query = "INSERT INTO $states_table (state) VALUES (\"$newname\")";
} elseif ($entity == "country") {
  $query = "INSERT INTO $countries_table (country) VALUES (\"$newname\")";
}
$result = tng_query($query);

adminwritelog(uiTextSnippet('enternew') . " $entity: $original_name");

if ($result == false) {
  echo "$original_name " . uiTextSnippet('alreadyexists');
} else {
  echo "$original_name " . uiTextSnippet('added');
}