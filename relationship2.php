<?php
require 'begin.php';
require 'genlib.php';
require 'getlang.php';

require 'checklogin.php';

header("Location: relationship.php?" . $_SERVER['QUERY_STRING']);