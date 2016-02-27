<?php
include("begin.php");
require($subroot . "logconfig.php");
include("genlib.php");
include("getlang.php");

include("checklogin.php");

header("Content-type:text/html; charset=" . $session_charset);
$lines = file($logfile);
foreach ($lines as $line) {
  echo "$line<br>\n";
}