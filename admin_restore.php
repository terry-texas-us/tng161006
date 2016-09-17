<?php

ini_set('memory_limit', '200M');
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

require 'adminlog.php';

function restore($table) {
  global $rootpath;
  global $backuppath;
  global $largechunk;

  $filename = "$rootpath$backuppath/$table.bak";
  if (!file_exists($filename)) {
    return uiTextSnippet('cannotopen') . " $table.bak";
  }
  $lines = file($filename);
  $query = "DELETE FROM $table";
  tng_query($query);

  $fields = array_shift($lines);
  if (substr($fields, 0, 1) == '"') { // does this line hold the field list?
    array_unshift($lines, $fields);    // no - so put the line back on the lines array and build field list from table
    $query = "SELECT * FROM $table"; // table is empty
    $result = tng_query($query);
    $fields = '';
    $nflds = tng_num_fields($result);
    for ($i = 0; $i < $nflds; $i++) {
      $fields .= tng_field_info($result, $i, 'name') . ',';
    }
    $fields = trim($fields, ',');
  }

  $counter = 0;
  $values = '';
  $saveline = '';
  $prevendquote = 0;

  foreach ($lines as $line) {
    $startquote = substr($line, 0, 1) == '"' ? 1 : 0;
    if ($startquote && $prevendquote) {
      $values .= sprintf('(%s),', rtrim($saveline));
      $counter++;
      if ($counter == $largechunk) {
        writechunk($table, $fields, $values);
        $counter = 0;
        $values = '';
      }
      $saveline = '';
    }
    $prevendquote = substr(rtrim($line), -1) == '"' && (substr(rtrim($line), -3) == "\\\\\"" || substr(rtrim($line), -2) != "\\\"") ? 1 : 0;
    $saveline .= $line;
  }

  if ($saveline) {
    $values .= sprintf('(%s),', rtrim($saveline));
    writechunk($table, $fields, $values);
  }
  return '';
}

function writechunk($table, $fields, $values) {

  $values = trim($values, ',');
  $query = "INSERT INTO $table ($fields) VALUES $values";
  return tng_query($query);
}

$largechunk = 100;
$ajaxmsg = $msg = '';

if ($table == 'struct') {
  $filename = "$rootpath$backuppath/tng_tablestructure.bak";
  $lines = file($filename);
  $query = '';
  foreach ($lines as $line) {
    $query .= $line;
    if (substr(trim($line), -1) == ';') {
      $result = tng_query($query);
      $query = '';
    }
  }

  $message = uiTextSnippet('tablestruct') . ' ' . uiTextSnippet('succrestored') . '.';
  adminwritelog(uiTextSnippet('restore') . ': ' . uiTextSnippet('tablestruct'));
} else {
  if ($table == 'all') {
    $tablelist = [$address_table, $albums_table, $albumlinks_table, $album2entities_table, $assoc_table, $branches_table, $branchlinks_table, 'cemeteries', $people_table, $families_table, $children_table, $languagesTable, 'places', 'states', 'countries', 'sources', 'repositories', $citations_table, $reports_table, 'events', 'eventtypes', 'trees', 'notelinks', 'xnotes', 'users', 'timelineevents', $saveimport_table, 'temp_events', $media_table, $medialinks_table, $mediatypes_table, $mostwanted_table];
    $tablename = uiTextSnippet('alltables');
    $message = '';
    foreach ($tablelist as $table) {
      eval("\$dothistable = \"\$$table\";");
      if ($dothistable) {
        $msg = restore($table);
        if ($msg) {
          $message = $message ? $message . '<br>' . $msg : $msg;
        }
      }
    }
    if (!$message) {
      $message = "$tablename " . uiTextSnippet('succrestored') . '.';
    }
  } else {
    $tablelist = ["$table"];
    $tablename = $table;
    $message = uiTextSnippet('table') . " $tablename " . uiTextSnippet('succrestored') . '.';
    $ajaxmsg = restore($table);
    $ajaxmsg = "$tablename&" . (($ajaxmsg) ? $ajaxmsg : uiTextSnippet('succrestored'));
  }
  adminwritelog(uiTextSnippet('restore') . ": $tablename");
}

header('Content-type:text/html; charset=' . $session_charset);
if ($ajaxmsg) {
  echo $ajaxmsg;
} else {
  header('Location: admin_utilities.php?message=' . urlencode($message));
}
