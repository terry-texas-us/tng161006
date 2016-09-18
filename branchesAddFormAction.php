<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if ($assignedbranch || !$allowAdd) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
require 'adminlog.php';

$branch = addslashes($branch);
$description = addslashes($description);

if (!$dospouses) {
  $dospouses = 0;
}
$query = "INSERT INTO branches (branch, description, personID, agens, dgens, dagens, inclspouses, action) VALUES ('$branch', '$description', '$personID', '$agens', '$dgens', '$dagens', '$dospouses', '2')";
$result = tng_query($query);
$success = tng_affected_rows();

adminwritelog(uiTextSnippet('addnewbranch') . " : $description");

if ($submitx) {
  $message = uiTextSnippet('branch') . " $description " . uiTextSnippet('succadded') . '.';
  header('Location: branchesBrowse.php?message=' . urlencode($message));
} else {
  header("Location: branchesEdit.php?branch=$branch");
}