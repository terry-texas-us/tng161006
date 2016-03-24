<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowEdit || ($assignedtree && $assignedtree != $tree)) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

require("adminlog.php");

$description = addslashes($description);

if (!$dospouses) {
  $dospouses = 0;
}
$query = "UPDATE $branches_table SET description=\"$description\", personID=\"$personID\", agens=\"$agens\", dgens=\"$dgens\", dagens=\"$dagens\", inclspouses=\"$dospouses\" WHERE gedcom=\"$tree\" AND branch = \"$branch\"";
$result = tng_query($query);

adminwritelog(uiTextSnippet('modifybranch') . ": $branch");

if ($submitx) {
  $message = uiTextSnippet('changestobranch') . ' ' . stripslashes($description) . ' ' . uiTextSnippet('succsaved') . '.';
  header("Location: branchesBrowse.php?message=" . urlencode($message));
} else {
  header("Location: branchesEdit.php?branch=$branch&tree=$tree");
}
