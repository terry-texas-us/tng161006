<?php

include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

$tng_search_preview = $_SESSION['tng_search_preview'];

require("adminlog.php");

$query = "SELECT type FROM $temp_events_table WHERE tempID=\"$tempID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

$query = "DELETE FROM $temp_events_table WHERE tempID=\"$tempID\"";
$result = tng_query($query);

adminwritelog(uiTextSnippet('deleted') . ": " . uiTextSnippet('tentdata') . " $tempID");

$message = uiTextSnippet('tentdata') . " $tempID " . uiTextSnippet('succdeleted') . ".";

header("Location: admin_findreview.php?type={$row['type']}&message=" . urlencode($message) . "&time=" . microtime());
