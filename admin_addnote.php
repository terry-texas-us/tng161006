<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

if (!$allowAdd || ($assignedtree && $assignedtree != $tree)) {
  exit;
}

require("adminlog.php");

if ($session_charset != "UTF-8") {
  $note = tng_utf8_decode($note);
}
$orgnote = preg_replace("/$lineending/", " ", stripslashes($note));
$note = addslashes($note);

$query = "INSERT INTO $xnotes_table (noteID, gedcom, note)  VALUES(\"\", \"$tree\", \"$note\")";
$result = tng_query($query);
$xnoteID = tng_insert_id();

if (!$private) {
  $private = "0";
}
$query = "INSERT INTO $notelinks_table (persfamID, gedcom, xnoteID, eventID, secret, ordernum) VALUES (\"$persfamID\", \"$tree\", \"$xnoteID\", \"$eventID\", \"$private\", 999)";
$result = tng_query($query);
$ID = tng_insert_id();

adminwritelog(uiTextSnippet('addnewnote') . ": $tree/$persfamID/$xnoteID/$eventID");

$orgnote = cleanIt($orgnote);
$truncated = truncateIt($orgnote, 75);
header("Content-type:text/html; charset=" . $session_charset);
echo "{\"id\":\"$ID\",\"persfamID\":\"$persfamID\",\"tree\":\"$tree\",\"eventID\":\"$eventID\",\"display\":\"$truncated\",\"allow_edit\":$allowEdit,\"allow_delete\":$allowDelete}";
