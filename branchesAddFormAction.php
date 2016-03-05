<?php

include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if ($assignedbranch || !$allow_add) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
require("adminlog.php");

$branch = addslashes($branch);
$description = addslashes($description);

if (!$dospouses) {
  $dospouses = 0;
}
$query = "INSERT INTO $branches_table (gedcom,branch,description,personID,agens,dgens,dagens,inclspouses,action) VALUES (\"$tree\",\"$branch\",\"$description\",\"$personID\",\"$agens\",\"$dgens\",\"$dagens\",\"$dospouses\",\"2\")";
$result = tng_query($query);
$success = tng_affected_rows();

adminwritelog(uiTextSnippet('addnewbranch') . " : $gedcom/$description");

if ($submitx) {
  $message = uiTextSnippet('branch') . " $description " . uiTextSnippet('succadded') . ".";
  header("Location: branchesBrowse.php?message=" . urlencode($message));
} else {
  header("Location: branchesEdit.php?branch=$branch&tree=$tree");
}