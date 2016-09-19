<?php
require 'begin.php';
require 'adminlib.php';

$admin_login = 1;
require 'checklogin.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}

require 'adminlog.php';

$note = addslashes($note);

$query = "UPDATE xnotes SET note='" . $note . "' WHERE ID='" . $xID . "'";
tng_query($query);

if (!$private) {
  $private = '0';
}

$query = "UPDATE notelinks SET secret='" . $private . "' WHERE ID='" . $ID . "'";
tng_query($query);

adminwritelog(uiTextSnippet('modifynote') . ': ' . $ID . '');

$message = uiTextSnippet('notechanges') . ' ' . $ID . ' ' . uiTextSnippet('succsaved') . '.';
header('Location: notesBrowse.php?message=' . urlencode($message));
