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

$display = addslashes($display);
$folder = addslashes($folder);

$query = "UPDATE $languages_table SET display=\"$display\",folder=\"$folder\",charset=\"$langcharset\" WHERE languageID=\"$languageID\"";
$result = tng_query($query);

adminwritelog("<a href=\"editlanguage.php?languageID=$languageID\">" . uiTextSnippet('modifylanguage') . ": $languageID</a>");

$message = uiTextSnippet('changestolanguage') . " $languageID " . uiTextSnippet('succsaved') . '.';
header("Location: languagesBrowse.php?message=" . urlencode($message));
