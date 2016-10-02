<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

$query = "SELECT * FROM citations WHERE citationID = \"$citationID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

$query = "SELECT title FROM sources WHERE sourceID = '{$row['sourceID']}'";
$result = tng_query($query);
$srow = tng_fetch_assoc($result);
tng_free_result($result);

$title = addslashes(truncateIt($srow['title'], 100));

header('Content-type:text/html; charset=' . $sessionCharset);
echo "{\"sourceID\":\"{$row['sourceID']}\",\"title\":\"{$title}\",\"citepage\":" . json_encode($row['page']) . ",\"quay\":\"{$row['quay']}\",\"citedate\":\"{$row['citedate']}\",\"citetext\":" . json_encode($row['citetext']) . ',"citenote":' . json_encode($row['note']) . '}';