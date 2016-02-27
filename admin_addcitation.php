<?php

include("begin.php");
include("adminlib.php");

include("checklogin.php");

require("datelib.php");
require("adminlog.php");

if (!$allow_add) {
  $message = uiTextSnippet('norights');
  exit;
}

if ($session_charset != "UTF-8") {
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

$query = "INSERT INTO $citations_table (gedcom, persfamID, eventID, sourceID, page, quay, citedate, citedatetr, citetext, note, description, ordernum)  VALUES(\"$tree\", \"$persfamID\", \"$eventID\", \"$sourceID\", \"$citepage\", \"$quay\", \"$citedate\", \"$citedatetr\", \"$citetext\", \"$citenote\",\"\", 999)";
$result = tng_query($query);
$citationID = tng_insert_id();

$_SESSION['lastcite'] = $tree . "|" . $citationID;

adminwritelog(uiTextSnippet('addnewcite') . ": $citationID/$tree/$persfamID/$eventID/$sourceID");

$query = "SELECT title FROM $sources_table WHERE sourceID = \"$sourceID\" AND gedcom = \"$tree\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);

$citationsrc = "[$sourceID] " . $row['title'];
$citationsrc = cleanIt($citationsrc);
$truncated = truncateIt($citationsrc, 75);
header("Content-type:text/html; charset=" . $session_charset);
echo "{\"id\":\"$citationID\",\"persfamID\":\"$persfamID\",\"tree\":\"$tree\",\"eventID\":\"$eventID\",\"display\":\"$truncated\",\"allow_edit\":$allow_edit,\"allow_delete\":$allow_delete}";
