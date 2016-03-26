<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
require 'adminlog.php';

$display = addslashes($display);
$folder = addslashes($folder);

$query = "UPDATE $languagesTable SET display=\"$display\",folder=\"$folder\",charset=\"$langcharset\" WHERE languageID=\"$languageID\"";
$result = tng_query($query);

adminwritelog("<a href=\"editlanguage.php?languageID=$languageID\">" . uiTextSnippet('modifylanguage') . ": $languageID</a>");

$message = uiTextSnippet('changestolanguage') . " $languageID " . uiTextSnippet('succsaved') . '.';
header("Location: languagesBrowse.php?message=" . urlencode($message));
