<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

require 'datelib.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
require 'adminlog.php';

if ($session_charset != 'UTF-8') {
  $citepage = tng_utf8_decode($citepage);
  $citetext = tng_utf8_decode($citetext);
  $citenote = tng_utf8_decode($citenote);
}
$description = addslashes($description);
$citedate = addslashes($citedate);
$citepage = addslashes($citepage);
$citetext = addslashes($citetext);
$citenote = addslashes($citenote);

$citedatetr = convertDate($citedate);
$sourceID = strtoupper($sourceID);

$query = "UPDATE $citations_table SET sourceID = '$sourceID', description = '$description', page= '$citepage', quay = '$quay', citedate = '$citedate', citedatetr = '$citedatetr', citetext = '$citetext', note = '$citenote' WHERE citationID = '$citationID'";
$result = tng_query($query);

$_SESSION['lastcite'] = $citationID;

adminwritelog(uiTextSnippet('modifycite') . ": $citationID/$sourceID");

if ($sourceID) {
  $query = "SELECT title, shorttitle FROM sources WHERE sourceID = '$sourceID'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $title = $row['title'] ? $row['title'] : $row['shorttitle'];
  $citationsrc = "[$sourceID] " . addslashes($title);
} else {
  $citationsrc = "$description";
}

$citationsrc = cleanIt($citationsrc);
$truncated = truncateIt($citationsrc, 75);
header('Content-Type: application/json; charset=' . $session_charset);
echo "{\"display\":\"$truncated\"}";