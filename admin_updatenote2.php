<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

require 'adminlog.php';

$note = addslashes($note);

$query = "UPDATE $xnotes_table SET note=\"$note\" WHERE ID=\"$xID\"";
$result = tng_query($query);

if (!$private) {
  $private = "0";
}
$query = "UPDATE $notelinks_table SET secret=\"$private\" WHERE ID=\"$ID\"";
$result = tng_query($query);

adminwritelog(uiTextSnippet('modifynote') . ": $ID");

$message = uiTextSnippet('notechanges') . " $ID " . uiTextSnippet('succsaved') . '.';
header("Location: admin_notelist.php?message=" . urlencode($message));