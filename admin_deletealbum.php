<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowMediaDelete) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}

require 'adminlog.php';

$query = "DELETE FROM $albums_table WHERE albumID=\"$albumID\"";
$result = tng_query($query);

$query = "DELETE FROM $albumlinks_table WHERE albumID=\"$albumID\"";
$result = tng_query($query);

adminwritelog(uiTextSnippet('deleted') . ': ' . uiTextSnippet('album') . " $albumID");

$message = uiTextSnippet('album') . " $albumID " . uiTextSnippet('succdeleted') . '.';
header('Location: albumsBrowse.php?message=' . urlencode($message));
