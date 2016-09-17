<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowDelete) {
  exit;
}
require 'adminlog.php';

$query = "SELECT addressID FROM events WHERE eventID=\"$eventID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
if ($result) {
  tng_free_result($result);
}
$query = "DELETE FROM $address_table WHERE addressID=\"{$row['addressID']}\"";
$result = tng_query($query);

$query = "DELETE FROM events WHERE eventID=\"$eventID\"";
$result = tng_query($query);

$query = "DELETE FROM $citations_table WHERE eventID=\"$eventID\"";
$result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . "]: $query");

$query = "SELECT xnoteID FROM notelinks WHERE eventID=\"$eventID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
if ($result) {
  tng_free_result($result);
}
$query = "SELECT count(ID) AS xcount FROM notelinks WHERE xnoteID=\"{$row['xnoteID']}\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
if ($result) {
  tng_free_result($result);
}
if ($row['xcount'] == 1) {
  $query = "DELETE FROM xnotes WHERE ID=\"{$row['xnoteID']}\"";
  $result = tng_query($query);
}
$query = "DELETE FROM notelinks WHERE eventID=\"$eventID\"";
$result = tng_query($query);

adminwritelog(uiTextSnippet('deleted') . ': ' . uiTextSnippet('event') . " $eventID");
echo 1;
