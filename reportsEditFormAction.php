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

$reportname = addslashes($reportname);
$reportdesc = addslashes($reportdesc);
$criteria = addslashes($criteria);
$sqlselect = addslashes($sqlselect);

$query = "UPDATE reports SET reportname=\"$reportname\",reportdesc=\"$reportdesc\",rank=\"$rank\",active=\"$active\",display=\"$display\",criteria=\"$criteria\",orderby=\"$orderby\",sqlselect=\"$sqlselect\" WHERE reportID=\"$reportID\"";
$result = tng_query($query);

adminwritelog("<a href=\"reportsEdit.php?reportID=$reportID\">" . uiTextSnippet('modifyreport') . ": $reportID</a>");

if ($submitx) {
  $message = uiTextSnippet('changestoreport') . " $reportID " . uiTextSnippet('succsaved') . '.';
  header('Location: reportsBrowse.php?message=' . urlencode($message));
} else {
  header("Location: reportsEdit.php?reportID=$reportID");
}
