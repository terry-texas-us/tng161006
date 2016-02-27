<?php
include("begin.php");
include("adminlib.php");

header("Content-type:text/html; charset=" . $session_charset);
if ($link) {
  $admin_login = 1;
  include("checklogin.php");
  if ($assignedtree) {
    echo uiTextSnippet('norights');
    exit;
  }
}

if (@mkdir($folder, 0777)) {
  echo uiTextSnippet('success');
} elseif (file_exists($folder)) {
  echo uiTextSnippet('fexists');
} else {
  echo uiTextSnippet('fmanual');
}
