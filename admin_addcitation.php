<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

require 'datelib.php';
require 'adminlog.php';

if (!$allowAdd) {
  $message = uiTextSnippet('norights');
  exit;
}
if ($sessionCharset != 'UTF-8') {
  $citepage = tng_utf8_decode($citepage);
  $citetext = tng_utf8_decode($citetext);
  $citenote = tng_utf8_decode($citenote);
}
$citedate = addslashes($citedate);
$citepage = addslashes($citepage);
$citetext = addslashes($citetext);
$citenote = addslashes($citenote);

$citedatetr = convertDate($citedate);
$sourceID = strtoupper($sourceID);

$query = "INSERT INTO citations (persfamID, eventID, sourceID, page, quay, citedate, citedatetr, citetext, note, description, ordernum) VALUES('$persfamID', '$eventID', '$sourceID', '$citepage', '$quay', '$citedate', '$citedatetr', '$citetext', '$citenote', '', 999)";
$result = tng_query($query);
$citationID = tng_insert_id();

$_SESSION['lastcite'] = $citationID;

adminwritelog(uiTextSnippet('addnewcite') . ": $citationID/$persfamID/$eventID/$sourceID");

$query = "SELECT title FROM sources WHERE sourceID = '$sourceID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);

$citationsrc = "[$sourceID] " . $row['title'];
$citationsrc = cleanIt($citationsrc);
$truncated = truncateIt($citationsrc, 75);
header('Content-type:text/html; charset=' . $sessionCharset);
echo "{\"id\":\"$citationID\",\"persfamID\":\"$persfamID\",\"eventID\":\"$eventID\",\"display\":\"$truncated\",\"allow_edit\":$allowEdit,\"allow_delete\":$allowDelete}";
