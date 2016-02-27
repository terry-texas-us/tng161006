<?php

include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if (!$allow_media_delete) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

require("adminlog.php");

$query = "DELETE FROM $albums_table WHERE albumID=\"$albumID\"";
$result = tng_query($query);

$query = "DELETE FROM $albumlinks_table WHERE albumID=\"$albumID\"";
$result = tng_query($query);

adminwritelog(uiTextSnippet('deleted') . ": " . uiTextSnippet('album') . " $albumID");

$message = uiTextSnippet('album') . " $albumID " . uiTextSnippet('succdeleted') . ".";
header("Location: admin_albums.php?message=" . urlencode($message));
