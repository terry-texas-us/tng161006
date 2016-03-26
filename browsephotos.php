<?php
require 'begin.php';
require 'genlib.php';

if ($_GET['showdocs'] == 1) {
  header("Location: mediaShow.php?mediatypeID=documents");
} else {
  header("Location: mediaShow.php?mediatypeID=photos");
}
