<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

require("datelib.php");

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
require("adminlog.php");

$orgrelationship = $relationship;
if ($session_charset != "UTF-8") {
  $relationship = tng_utf8_decode($relationship);
}
$relationship = addslashes($relationship);


$query = "UPDATE $assoc_table SET passocID=\"$passocID\", relationship=\"$relationship\", reltype=\"$reltype\" WHERE assocID=\"$assocID\"";
$result = tng_query($query);

adminwritelog(uiTextSnippet('modifyassoc') . ": $assocID/$tree/$personID::$passocID ($relationship)");

//get name
if ($reltype == 'I') {
  $query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix FROM $people_table
    WHERE personID=\"$passocID\" AND gedcom=\"$tree\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $righttree = checktree($tree);
  $rightbranch = $righttree ? checkbranch($row['branch']) : false;
  $rights = determineLivingPrivateRights($row, $righttree, $rightbranch);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  $name = getName($row) . " ($passocID)";
} else {
  $query = "SELECT husband, wife, gedcom, familyID FROM $families_table
    WHERE familyID=\"$passocID\" AND gedcom=\"$tree\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $name = getFamilyName($row);
}
$namestr = cleanIt($name . ": " . stripslashes($orgrelationship));

$namestr = truncateIt($namestr, 75);
tng_free_result($result);
header("Content-type:text/html; charset=" . $session_charset);
echo "{\"display\":\"$namestr\"}";
