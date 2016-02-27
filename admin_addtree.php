<?php

include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if ($assignedtree || !$allow_add) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

require("adminlog.php");

$gedcom = str_replace(" ", "", $gedcom);
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
$query = "INSERT IGNORE INTO $trees_table (gedcom,treename,description,owner,email,address,city,state,country,zip,phone,secret,disallowgedcreate,disallowpdf) VALUES (\"$gedcom\",\"$treename\",\"$description\",\"$owner\",\"$email\",\"$address\",\"$city\",\"$state\",\"$country\",\"$zip\",\"$phone\",\"$private\",\"$disallowgedcreate\",\"$disallowpdf\")";
$result = tng_query($query);
$success = tng_affected_rows();
if ($success) {
  adminwritelog("<a href=\"admin_edittree.php?tree=$gedcom\">" . uiTextSnippet('addnewtree') . ": $gedcom/$treename</a>");

  $message = uiTextSnippet('tree') . " $treenamedisp " . uiTextSnippet('succadded') . '.';
  if ($beforeimport == "yes") {
    echo "1";
  } else {
    header("Location: admin_trees.php?message=" . urlencode($message));
  }
} else {
  $message = uiTextSnippet('treeexists');
  if ($beforeimport) {
    echo $message;
  } else {
    header("Location: admin_newtree.php?message=" . urlencode($message) . "&treename=$treename&description=$description&owner=$owner&email=$email&address=$address&city=$city&state=$state&country=$country&zip=$zip&phone=$phone&private=$private&disallowgedcreate=$disallowgedcreate");
  }
}