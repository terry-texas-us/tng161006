<?php
include("begin.php");
include("adminlib.php");

include("checklogin.php");

$query = "SELECT * FROM $citations_table WHERE citationID = \"$citationID\"";
$result = @tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

$query = "SELECT title FROM $sources_table WHERE sourceID = \"{$row['sourceID']}\" AND gedcom = \"{$row['gedcom']}\"";
$result = @tng_query($query);
$srow = tng_fetch_assoc($result);
tng_free_result($result);

$title = addslashes(truncateIt($srow['title'], 100));

header("Content-type:text/html; charset=" . $session_charset);
echo "{\"sourceID\":\"{$row['sourceID']}\",\"title\":\"{$title}\",\"citepage\":" . json_encode($row['page']) . ",\"quay\":\"{$row['quay']}\",\"citedate\":\"{$row['citedate']}\",\"citetext\":" . json_encode($row['citetext']) . ",\"citenote\":" . json_encode($row['note']) . "}";