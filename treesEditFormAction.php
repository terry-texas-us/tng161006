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

$treenamedisp = stripslashes($treename);
$treename = addslashes($treename);
$description = addslashes($description);
$owner = addslashes($owner);
$email = addslashes($email);
$address = addslashes($address);
$city = addslashes($city);
$state = addslashes($state);
$country = addslashes($country);
$zip = addslashes($zip);
$phone = addslashes($phone);

if (!$disallowgedcreate) {
  $disallowgedcreate = 0;
}
if (!$disallowpdf) {
  $disallowpdf = 0;
}
if (!$private) {
  $private = 0;
}
$query = "UPDATE $trees_table SET treename=\"$treename\",description=\"$description\",owner=\"$owner\",email=\"$email\",address=\"$address\",city=\"$city\",state=\"$state\",country=\"$country\",zip=\"$zip\",phone=\"$phone\",secret=\"$private\",disallowgedcreate=\"$disallowgedcreate\",disallowpdf=\"$disallowpdf\" WHERE gedcom=\"$tree\"";
$result = tng_query($query);

adminwritelog("<a href='treesEdit.php?tree=$tree'>" . uiTextSnippet('modifytree') . ": $tree</a>");

$message = uiTextSnippet('changestotree') . " $treenamedisp " . uiTextSnippet('succsaved') . '.';
header("Location: treesBrowse.php?message=" . urlencode($message));
