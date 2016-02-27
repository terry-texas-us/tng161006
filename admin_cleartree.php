<?php

include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if (!$allow_delete) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

require("adminlog.php");

include("treelib.php");

$message = uiTextSnippet('tree') . " $gedcom " . uiTextSnippet('succcleared') . '.';

adminwritelog(uiTextSnippet('deleted') . ": " . uiTextSnippet('tree') . " $tree");

header("Location: admin_trees.php?message=" . urlencode($message));
