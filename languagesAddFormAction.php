<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if ($assignedtree || !$allowAdd) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
require("adminlog.php");

$display = addslashes($display);
$folder = addslashes($folder);

$query = "INSERT INTO $languagesTable (display,folder,charset) VALUES (\"$display\",\"$folder\",\"$langcharset\")";
$result = tng_query($query);
$languageID = tng_insert_id();

adminwritelog("<a href=\"languagesEdit.php?languageID=$languageID\">" . uiTextSnippet('addnewlanguage') . ": $display/$folder</a>");

$message = uiTextSnippet('language') . " $display " . uiTextSnippet('succadded') . '.';
header("Location: languagesBrowse.php?message=" . urlencode($message));
