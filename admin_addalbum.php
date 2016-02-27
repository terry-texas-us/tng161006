<?php

include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if (!$allow_media_add) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
require("adminlog.php");

$albumname = addslashes($albumname);
$description = addslashes($description);
$keywords = addslashes($keywords);

if (!$alwayson) {
  $alwayson = 0;
}
$query = "INSERT INTO $albums_table (albumname,description,keywords,active,alwayson) "
        . "VALUES (\"$albumname\",\"$description\",\"$keywords\",\"$active\",\"$alwayson\")";
$result = tng_query($query);
$albumID = tng_insert_id();

adminwritelog(uiTextSnippet('addnewalbum') . ": $albumname");

header("Location: admin_editalbum.php?albumID=$albumID&added=1");
