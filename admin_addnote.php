<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

if (!$allowAdd) {
  exit;
}

require 'adminlog.php';

if ($sessionCharset != 'UTF-8') {
  $note = tng_utf8_decode($note);
}
$orgnote = preg_replace("/$lineending/", ' ', stripslashes($note));
$note = addslashes($note);

$query = "INSERT INTO xnotes (noteID, note)  VALUES('', '$note')";
$result = tng_query($query);
$xnoteID = tng_insert_id();

if (!$private) {
  $private = '0';
}
$query = "INSERT INTO notelinks (persfamID, xnoteID, eventID, secret, ordernum) VALUES ('$persfamID', '$xnoteID', '$eventID', '$private', 999)";
$result = tng_query($query);
$ID = tng_insert_id();

adminwritelog(uiTextSnippet('addnewnote') . ": $persfamID/$xnoteID/$eventID");

$orgnote = cleanIt($orgnote);
$truncated = truncateIt($orgnote, 75);
header('Content-type:text/html; charset=' . $sessionCharset);
echo "{\"id\":\"$ID\",\"persfamID\":\"$persfamID\",\"eventID\":\"$eventID\",\"display\":\"$truncated\",\"allow_edit\":$allowEdit,\"allow_delete\":$allowDelete}";
