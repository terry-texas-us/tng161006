<?php
include("begin.php");
$tngconfig['maint'] = "";
include("genlib.php");
include("getlang.php");

include("log.php");

header("Content-type:text/html; charset=" . $session_charset);
?>

<div style="margin:10px;border:0">
  <?php include("loginlib.php"); ?>
</div>