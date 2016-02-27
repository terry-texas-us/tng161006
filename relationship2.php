<?php
include("begin.php");
include("genlib.php");
include("getlang.php");

include("checklogin.php");

header("Location: relationship.php?" . $_SERVER['QUERY_STRING']);