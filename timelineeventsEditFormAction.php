<?php

include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if (!$allow_edit) {
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
$query = "UPDATE $tlevents_table SET evday=\"$evday\", evmonth=\"$evmonth\", evyear=\"$evyear\",endday=\"$endday\", endmonth=\"$endmonth\", endyear=\"$endyear\",evtitle=\"$evtitle\",evdetail=\"$evdetail\" WHERE tleventID=\"$tleventID\"";
$result = tng_query($query);

adminwritelog(uiTextSnippet('modifytlevent') . ": $tleventID");

if ($newscreen == "return") {
  header("Location: timelineeventsEdit.php?tleventID=$tleventID");
} else {
  $message = uiTextSnippet('changestotlevent') . " $tleventID " . uiTextSnippet('succsaved') . '.';
  header("Location: timelineeventsBrowse.php?message=" . urlencode($message));
}
