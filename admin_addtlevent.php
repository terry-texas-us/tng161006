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

$evdetail = addslashes($evdetail);
$evtitle = addslashes($evtitle);

if (!$evday) {
  $evday = "0";
}
if (!$evmonth) {
  $evmonth = "0";
}
if (!$endday) {
  $endday = "0";
}
if (!$endmonth) {
  $endmonth = "0";
}
$query = "INSERT INTO $tlevents_table (evday,evmonth,evyear,endday,endmonth,endyear,evtitle,evdetail) VALUES (\"$evday\",\"$evmonth\",\"$evyear\",\"$endday\",\"$endmonth\",\"$endyear\",\"$evtitle\",\"$evdetail\")";
$result = tng_query($query);
$tleventID = tng_insert_id();

adminwritelog(uiTextSnippet('addnewtlevent') . ": $tleventID - $evdetail");

$message = uiTextSnippet('tlevent') . " $tleventID " . uiTextSnippet('succadded') . '.';
header("Location: admin_timelineevents.php?message=" . urlencode($message));
