<?php
include("begin.php");
include("genlib.php");

if ($_GET['showdocs'] == 1) {
  header("Location: browsemedia.php?mediatypeID=documents");
} else {
  header("Location: browsemedia.php?mediatypeID=photos");
}
