<?php
require 'begin.php';
require($subroot . "logconfig.php");
include("genlib.php");
include("getlang.php");

require 'checklogin.php';

header("Content-type:text/html; charset=" . $session_charset);
$lines = file($logfile);
foreach ($lines as $line) {
  echo "$line<br>\n";
}