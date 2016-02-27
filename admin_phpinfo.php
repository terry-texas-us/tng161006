<?php
include("begin.php");
include("adminlib.php");

if ($link) {
  $admin_login = 1;
  include("checklogin.php");
  if ($assignedtree) {
    $message = uiTextSnippet('norights');
    header("Location: admin_login.php?message=" . urlencode($message));
    exit;
  }
}

echo phpinfo();