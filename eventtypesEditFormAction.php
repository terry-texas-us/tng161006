<?php

include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if (!$allow_edit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

require("adminlog.php");

$type = addslashes($type);
$tag2 = addslashes($tag2);
$defdisplay = addslashes($defdisplay);

if ($tag2) {
  $tag = $tag2;
} else {
  $tag = $tag1;
}
if (!$display) {
  $display = $defdisplay;
}
$query = "UPDATE $eventtypes_table SET tag=\"$tag\",type=\"$type\",description=\"$description\",display=\"$display\",keep=\"$keep\",collapse=\"$collapse\",ordernum=\"$ordernum\" WHERE eventtypeID=\"$eventtypeID\"";
$result = tng_query($query);

adminwritelog(uiTextSnippet('modifyeventtype') . ": $eventtypeID");

$message = uiTextSnippet('changestoevtype') . " $eventtypeID " . uiTextSnippet('succsaved') . '.';
header("Location: eventtypesBrowse.php?message=" . urlencode($message));
