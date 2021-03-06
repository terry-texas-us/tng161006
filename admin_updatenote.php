<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

if (!$allowEdit) {
  exit;
}

require 'adminlog.php';

if ($sessionCharset != 'UTF-8') {
  $note = tng_utf8_decode($note);
}
$orgnote = preg_replace("/$lineending/", ' ', $note);
$note = addslashes($note);

$setnote = "secret=\"$private\"";

if ($xID) {
  $query = "UPDATE xnotes SET note=\"$note\" WHERE ID=\"$xID\"";
  $result = tng_query($query);
}

if (!$private) {
  $private = '0';
}
$query = "UPDATE notelinks SET secret=\"$private\" WHERE ID=\"$ID\"";
$result = tng_query($query);

adminwritelog(uiTextSnippet('modifynote') . ": $persfamID/$ID/$eventID");

$orgnote = cleanIt($orgnote);
$truncated = truncateIt(stripslashes($orgnote), 75);
header('Content-type:text/html; charset=' . $sessionCharset);
echo "{\"display\":\"$truncated\"}";
