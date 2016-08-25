<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = true;
require 'checklogin.php';

if (!$allowDelete) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
require 'adminlog.php';

$query = "DELETE FROM $users_table WHERE 1=0";
$location = ($xuseraction) ? "usersBrowse.php" : "usersReview.php";

$count = 0;
$items = [];

foreach (array_keys($_POST) as $key) {
  if (substr($key, 0, 3) == "del") {
    $count++;
    $thisid = substr($key, 3);
    $query .= " OR userID =\"$thisid\"";

    $itemID = "";
    $tree = "";

    $query3 = "SELECT username FROM $users_table WHERE userID = \"$thisid\"";
    $result3 = tng_query($query3);
    $urow = tng_fetch_assoc($result3);
    tng_free_result($result3);

    $query3 = "DELETE FROM $users_table WHERE userID = \"$thisid\"";
    $result3 = tng_query($query3) or die(uiTextSnippet('cannotexecutequery') . ": $query3");
    $items[] = $urow['username'];
  }
}
$result = tng_query($query);

adminwritelog(uiTextSnippet('deleted') . ": " . uiTextSnippet("users") . " " . implode(', ', $items));

if ($count) {
  $message = uiTextSnippet('changestoallitems') . " " . uiTextSnippet('succsaved') . ".";
} else {
  $message = uiTextSnippet('nochanges');
}
header("Location: $location" . "?message=" . urlencode($message));
