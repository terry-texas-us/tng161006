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

$evdetail = addslashes($evdetail);
$evtitle = addslashes($evtitle);

if (!$evday) {
  $evday = '0';
}
if (!$evmonth) {
  $evmonth = '0';
}
if (!$endday) {
  $endday = '0';
}
if (!$endmonth) {
  $endmonth = '0';
}
$query = "INSERT INTO timelineevents (evday,evmonth,evyear,endday,endmonth,endyear,evtitle,evdetail) VALUES (\"$evday\",\"$evmonth\",\"$evyear\",\"$endday\",\"$endmonth\",\"$endyear\",\"$evtitle\",\"$evdetail\")";
$result = tng_query($query);
$tleventID = tng_insert_id();

adminwritelog(uiTextSnippet('addnewtlevent') . ": $tleventID - $evdetail");

$message = uiTextSnippet('tlevent') . " $tleventID " . uiTextSnippet('succadded') . '.';
header('Location: timelineeventsBrowse.php?message=' . urlencode($message));
