<?php
require 'begin.php';
require 'genlib.php';


session_start();

$query = "SELECT display, folder, charset FROM $languagesTable WHERE languageID = \"$newlanguage\"";
$result = tng_query($query) or die("Cannot execute query: $query"); //message is hardcoded because we haven't included the text file yet
$row = tng_fetch_assoc($result);
tng_free_result($result);

$session_language = $_SESSION['session_language'] = $row['folder'];
$session_charset = $_SESSION['session_charset'] = $row['charset'];

$newroot = preg_replace('/\//', '', $rootpath);
$newroot = preg_replace('/ /', '', $newroot);
$newroot = preg_replace('/\./', '', $newroot);
setcookie("tnglang_$newroot", $row['folder'], time() + 31536000, '/');
setcookie("tngchar_$newroot", $row['charset'], time() + 31536000, '/');

header("Location: $homepage");