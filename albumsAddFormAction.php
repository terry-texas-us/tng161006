<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowMediaAdd) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
require 'adminlog.php';

$albumname = addslashes($albumname);
$description = addslashes($description);
$keywords = addslashes($keywords);

if (!$alwayson) {
  $alwayson = 0;
}
$query = "INSERT INTO albums (albumname,description,keywords,active,alwayson) VALUES (\"$albumname\",\"$description\",\"$keywords\",\"$active\",\"$alwayson\")";
$result = tng_query($query);
$albumID = tng_insert_id();

adminwritelog(uiTextSnippet('addnewalbum') . ": $albumname");

header("Location: albumsEdit.php?albumID=$albumID&added=1");
