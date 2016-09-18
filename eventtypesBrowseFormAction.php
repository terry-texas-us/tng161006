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
  $query = 'UPDATE eventtypes SET keep="0"';
} elseif ($cetaction == uiTextSnippet('acceptselected')) {
  $query = 'UPDATE eventtypes SET keep="1"';
} elseif ($cetaction == uiTextSnippet('collapseselected')) {
  $query = 'UPDATE eventtypes SET collapse="1"';
} elseif ($cetaction == uiTextSnippet('expandselected')) {
  $query = 'UPDATE eventtypes SET collapse="0"';
} elseif ($cetaction == uiTextSnippet('deleteselected')) {
  $query = 'DELETE FROM eventtypes';
}
$count = 0;
$whereClause = '';

foreach (array_keys($_POST) as $key) {
  if (substr($key, 0, 2) == 'et') {
    $count += 1;
    $whereClause .= $whereClause ? ' OR ' : ' WHERE ';
    $whereClause .= 'eventtypeID="' . substr($key, 2) . '"';
  }
}
if ($count > 0) {
  tng_query($query . $whereClause);
  adminwritelog(uiTextSnippet('modifyeventtype') . ': ' . uiTextSnippet('all'));
  $message = uiTextSnippet('changestoallevtypes') . ' ' . uiTextSnippet('succsaved') . '.';
} else {
  $message = uiTextSnippet('nochanges');
}
header('Location: eventtypesBrowse.php?message=' . urlencode($message));
