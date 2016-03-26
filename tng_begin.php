<?php

require 'begin.php';

require 'genlib.php';
require 'getlang.php';

require 'tngdblib.php';

if (strpos($_SERVER['SCRIPT_NAME'], "/changelanguage.php") === false && (strpos($_SERVER['SCRIPT_NAME'], "/mixedSuggest.php") === false || $enttype)) {
  include 'checklogin.php';
} else {
  $currentuser = $_SESSION['currentuser'];
  $currentuserdesc = $_SESSION['currentuserdesc'];
}
require 'log.php';