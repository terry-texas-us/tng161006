<?php
require 'begin.php';
require 'adminlib.php';

if ($link) {
  $adminLogin = 1;
  require 'checklogin.php';
  if ($assignedtree) {
    $message = uiTextSnippet('norights');
    header("Location: admin_login.php?message=" . urlencode($message));
    exit;
  }
}

echo phpinfo();