<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

$error_pfx = $ajax ? "error:" : "";

$tree = $tree1;
if (!$allow_add || ($assignedtree && $assignedtree != $tree)) {
  $message = uiTextSnippet('norights');
  if ($ajax) {
    echo $error_pfx . $message;
  } else {
    header("Location: admin_login.php?message=" . urlencode($message));
  }
  exit;
}
require("adminlog.php");

$sourceID = ucfirst($sourceID);

if ($session_charset != "UTF-8") {
  $shorttitle = tng_utf8_decode($shorttitle);
  $title = tng_utf8_decode($title);
  $author = tng_utf8_decode($author);
  $callnum = tng_utf8_decode($callnum);
  $publisher = tng_utf8_decode($publisher);
  $actualtext = tng_utf8_decode($actualtext);
}
$shorttitle = addslashes($shorttitle);
$title = addslashes($title);
$author = addslashes($author);
$callnum = addslashes($callnum);
$publisher = addslashes($publisher);
$actualtext = addslashes($actualtext);

$newdate = date("Y-m-d H:i:s", time() + (3600 * $time_offset));

if (!$repoID) {
  $repoID = 0;
}
$query = "INSERT INTO $sources_table (sourceID,shorttitle,title,author,callnum,publisher,repoID,actualtext,changedate,gedcom,changedby,type,other,comments) VALUES (\"$sourceID\",\"$shorttitle\",\"$title\",\"$author\",\"$callnum\",\"$publisher\",\"$repoID\",\"$actualtext\",\"$newdate\",\"$tree1\",\"$currentuser\",\"\",\"\",\"\")";
$result = tng_query($query) or die($error_pfx . uiTextSnippet('cannotexecutequery') . ": $query");

adminwritelog("<a href=\"admin_editsource.php?sourceID=$sourceID&amp;tree=$tree\">" . uiTextSnippet('addnewsource') . ": $tree/$sourceID</a>");

if (isset($ajax)) {
  echo $sourceID;
} else {
  header("Location: admin_editsource.php?sourceID=$sourceID&tree=$tree&added=1");
}