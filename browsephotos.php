<?php
require 'begin.php';
include("genlib.php");

if ($_GET['showdocs'] == 1) {
  header("Location: mediaShow.php?mediatypeID=documents");
} else {
  header("Location: mediaShow.php?mediatypeID=photos");
}
