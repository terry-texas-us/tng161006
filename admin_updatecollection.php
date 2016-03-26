<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowEdit) {
  exit;
}
require 'adminlog.php';

$display_org = stripslashes($display);

if ($session_charset != "UTF-8") {
  $display = tng_utf8_decode($display);
}
$display = addslashes($display);

$query = "UPDATE $mediatypes_table SET display=\"$display\", path=\"$path\", liketype=\"$liketype\", icon=\"$icon\", thumb=\"$thumb\", exportas=\"$exportas\", ordernum=\"$ordernum\" WHERE mediatypeID=\"$collid\"";
$result = tng_query($query);

if (tng_affected_rows()) {
  adminwritelog(uiTextSnippet('editcoll') . ": $display_org");
  echo "1";
} else {
  echo "0";
}
