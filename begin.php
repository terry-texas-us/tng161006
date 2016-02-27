<?php

if (isset($_GET['lang']) || isset($_GET['mylanguage']) || isset($_GET['language']) || isset($_GET['session_language']) || isset($_GET['rootpath'])) {
  die("Sorry!");
}
$tngconfig = "";

if (strpos($_SERVER['SCRIPT_NAME'], "/admin_updateconfig.php") === false) {
  include("processvars.php");
}
include("subroot.php");
include_once("tngconnect.php");
include($tngconfig['subroot'] . "config.php");
$subroot = $tngconfig['subroot'] ? $tngconfig['subroot'] : "";

include($subroot . "templateconfig.php");

if (isset($sitever)) {
  setcookie("tng_siteversion", $sitever, time() + 31536000, "/");
} else {
  if (isset($_COOKIE['tng_siteversion'])) {
    $sitever = $_COOKIE['tng_siteversion'];
  }
}
$sitever = 'standard';

session_start();

$languages_path = "languages/";
include("getlang.php");
$session_language = $_SESSION['session_language'];
$session_charset = $_SESSION['session_charset'];

$link = tng_db_connect($database_host, $database_name, $database_username, $database_password);