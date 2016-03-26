<?php
require 'begin.php';
require $subroot . 'logconfig.php';
require 'genlib.php';
require 'getlang.php';

require 'checklogin.php';

header("Content-type:text/html; charset=" . $session_charset);
$lines = file($logfile);
foreach ($lines as $line) {
  echo "$line<br>\n";
}