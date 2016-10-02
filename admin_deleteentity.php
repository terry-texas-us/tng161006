<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowDelete) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
require 'adminlog.php';

$original_name = $delitem;
if ($sessionCharset != 'UTF-8') {
  $delitem = tng_utf8_decode($delitem);
}

$newname = addslashes($delitem);
if ($entity == 'state') {
  $query = "DELETE FROM states WHERE state = \"$newname\"";
} elseif ($entity == 'country') {
  $query = "DELETE FROM countries WHERE country = '$newname'";
}
$result = tng_query($query);

adminwritelog(uiTextSnippet('deleted') . ": $entity: $original_name");
echo '1';
