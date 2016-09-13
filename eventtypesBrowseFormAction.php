<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowEdit || !$allowDelete) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
require 'adminlog.php';

$query = '';
if ($cetaction == uiTextSnippet('ignoreselected')) {
  $query = "UPDATE $eventtypes_table SET keep=\"0\" WHERE 1=0";
} else {
  if ($cetaction == uiTextSnippet('acceptselected')) {
    $query = "UPDATE $eventtypes_table SET keep=\"1\" WHERE 1=0";
  } else {
    if ($cetaction == uiTextSnippet('collapseselected')) {
      $query = "UPDATE $eventtypes_table SET collapse=\"1\" WHERE 1=0";
    } else {
      if ($cetaction == uiTextSnippet('deleteselected')) {
        $query = "DELETE FROM $eventtypes_table WHERE 1=0";
      }
    }
  }
}
if ($query) {
  foreach (array_keys($_POST) as $key) {
    if (substr($key, 0, 2) == 'et') {
      $query .= " OR eventtypeID=\"" . substr($key, 2) . "\"";
    }
  }
  $result = tng_query($query);
}
adminwritelog(uiTextSnippet('modifyeventtype') . ': ' . uiTextSnippet('all'));

$message = uiTextSnippet('changestoallevtypes') . ' ' . uiTextSnippet('succsaved') . '.';
header('Location: eventtypesBrowse.php?message=' . urlencode($message));
