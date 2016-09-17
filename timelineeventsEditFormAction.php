<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowEdit) {
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
$query = "UPDATE timelineevents SET evday=\"$evday\", evmonth=\"$evmonth\", evyear=\"$evyear\",endday=\"$endday\", endmonth=\"$endmonth\", endyear=\"$endyear\",evtitle=\"$evtitle\",evdetail=\"$evdetail\" WHERE tleventID=\"$tleventID\"";
$result = tng_query($query);

adminwritelog(uiTextSnippet('modifytlevent') . ": $tleventID");

if ($newscreen == 'return') {
  header("Location: timelineeventsEdit.php?tleventID=$tleventID");
} else {
  $message = uiTextSnippet('changestotlevent') . " $tleventID " . uiTextSnippet('succsaved') . '.';
  header('Location: timelineeventsBrowse.php?message=' . urlencode($message));
}
