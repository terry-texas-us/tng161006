<?php

$mylanguage = "";
if ($session_language) {
  $mylanguage = $languagesPath . $session_language;
} else {
  $newroot = preg_replace("/\//", "", $rootpath); // [ts] no backslashes
  $newroot = preg_replace("/ /", "", $newroot);   // [ts] no spaces
  $newroot = preg_replace("/\./", "", $newroot);  // [ts] no dots
  $langcookiename = "tnglang_$newroot";
  $charcookiename = "tngchar_$newroot";

  if ($_COOKIE[$langcookiename]) {
    $mylanguage = $languagesPath . $_COOKIE[$langcookiename];
    $_SESSION['session_language'] = $_COOKIE[$langcookiename];
    $session_charset = $_SESSION['session_charset'] = $_COOKIE[$charcookiename];
  } elseif ($lang) {
    $mylanguage = $languagesPath . $lang;
    $_SESSION['session_language'] = $lang;
  }
}
if (!$mylanguage) {
    $mylanguage = $languagesPath . $language;
    $_SESSION['session_language'] = $language;
}
$session_language = $_SESSION['session_language'];

if (!$session_charset) {
  $session_charset = $_SESSION['session_charset'] = ($charset ? $charset : "ISO-8859-1");
}