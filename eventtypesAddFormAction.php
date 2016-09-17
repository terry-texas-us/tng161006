<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

if (!$allowAdd) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}

require 'adminlog.php';

$type = addslashes($type);
$tag2 = addslashes($tag2);
$defdisplay = addslashes($defdisplay);

if ($tag2) {
  $tag = $tag2;
} else {
  $tag = $tag1;
}
if (!$ordernum) {
  $ordernum = 0;
}
if (!$display) {
  $display = $defdisplay;
}
$query = "INSERT INTO eventtypes (tag,description,display,type,keep,collapse,ordernum) VALUES (\"$tag\",\"$description\",\"$display\",\"$type\",\"$keep\",\"$collapse\",\"$ordernum\")";
$result = tng_query($query);
if (tng_affected_rows() == 1) {
  $eventtypeID = tng_insert_id();
  $message = uiTextSnippet('eventtype') . " $eventtypeID " . uiTextSnippet('succadded') . '.';

  adminwritelog(uiTextSnippet('addnewevtype') . ": $tag $type - $display");
} else {
  $message = uiTextSnippet('eventtype') . " $eventtypeID " . uiTextSnippet('idexists') . '.';
}
header('Location: eventtypesBrowse.php?message=' . urlencode($message));
