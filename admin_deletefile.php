<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowDelete) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

$deleted = false;
if (file_exists($filename)) {
  $deleted = unlink($filename);
}
echo $deleted ? uiTextSnippet('deleted') . "&nbsp;<img src='img/tng_check.gif'>" : uiTextSnippet('notdeleted');
