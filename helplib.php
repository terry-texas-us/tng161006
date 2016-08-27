<?php

function help_header($helptitle) {
  $relpath = "../../";
  include $relpath . "version.php";
  include $relpath . "begin.php";

  $header = "<!DOCTYPE html>\n";
  $header .= "<html>\n";
  $header .= "<head>\n";
  $header .= "<title>$helptitle</title>\n";
  $header .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$session_charset\" />\n";
  $header .= "<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,400italic,700,700italic' rel='stylesheet' type='text/css'>\n";
  $header .= "<!-- Bootstrap styles -->\n";
  $header .= "<link rel='stylesheet' type='text/css' href='{$relpath}_/css/bootstrap.css'>\n";
  $header .= "<link rel='stylesheet' type='text/css' href='{$relpath}_/css/genstyle.css'>\n";
  
  $header .= "</head>\n";

  return $header;
}