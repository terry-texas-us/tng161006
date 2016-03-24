<?php
require 'begin.php';
require 'adminlib.php';


session_start();
eval("\$newlanguage = preg_replace(\"/[\^0\-9]/\", '', \$newlanguage$instance);");

$query = "SELECT folder, charset FROM $languagesTable WHERE languageID = \"$newlanguage\"";
$result = tng_query($query) or die ("Cannot execute query: $query"); //message is hardcoded because we haven't included the text file yet
$row = tng_fetch_assoc($result);
tng_free_result($result);

$session_language = $_SESSION['session_language'] = $row['folder'];
$session_charset = $_SESSION['session_charset'] = $row['charset'];
if (file_exists($languagesPath . $row['folder'])) {
  setcookie("tnglangfolder", $row['folder'], time() + 31536000, "/");
  setcookie("tngcharset", $row['charset'], time() + 31536000, "/");
}
$adminLogin = 1;
require 'checklogin.php';

header("Location: admin.php");