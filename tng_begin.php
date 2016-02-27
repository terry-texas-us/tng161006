<?php

include("begin.php");

include("genlib.php");
include("getlang.php");

include("tngdblib.php");

if (strpos($_SERVER['SCRIPT_NAME'], "/changelanguage.php") === false && (strpos($_SERVER['SCRIPT_NAME'], "/suggest.php") === false || $enttype)) {
  include("checklogin.php");
} else {
  $currentuser = $_SESSION['currentuser'];
  $currentuserdesc = $_SESSION['currentuserdesc'];
}
include("log.php");