<?php
require 'begin.php';
require 'adminlib.php';

header('Content-type:text/html; charset=' . $session_charset);
if ($link) {
  $adminLogin = 1;
  include 'checklogin.php';
}
if (mkdir($folder, 0777)) {
  echo uiTextSnippet('success');
} elseif (file_exists($folder)) {
  echo uiTextSnippet('fexists');
} else {
  echo uiTextSnippet('fmanual');
}
