<?php

include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if ($assignedtree || !$allow_add) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

require("adminlog.php");

$reportname = addslashes($reportname);
$reportdesc = addslashes($reportdesc);
$criteria = addslashes($criteria);
$sqlselect = addslashes($sqlselect);

$query = "INSERT INTO $reports_table (reportname, reportdesc, rank, active, display, criteria, orderby, sqlselect) VALUES (\"$reportname\",\"$reportdesc\",\"$rank\",\"$active\",\"$display\",\"$criteria\",\"$orderby\",\"$sqlselect\")";
$result = tng_query($query);
$reportID = tng_insert_id();

adminwritelog("<a href=\"reportsEdit.php?reportID=$reportID\">" . uiTextSnippet('addnewreport') . ": $reportID/$reportname</a>");

$message = uiTextSnippet('report') . " $reportID " . uiTextSnippet('succadded') . '.';
header("Location: reportsBrowse.php?message=" . urlencode($message));