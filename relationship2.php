<?php
require 'begin.php';
include("genlib.php");
include("getlang.php");

require 'checklogin.php';

header("Location: relationship.php?" . $_SERVER['QUERY_STRING']);