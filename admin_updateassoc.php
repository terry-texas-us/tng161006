<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

require 'datelib.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
require 'adminlog.php';

$orgrelationship = $relationship;
if ($sessionCharset != 'UTF-8') {
  $relationship = tng_utf8_decode($relationship);
}
$relationship = addslashes($relationship);


$query = "UPDATE associations SET passocID=\"$passocID\", relationship=\"$relationship\", reltype=\"$reltype\" WHERE assocID = '$assocID'";
$result = tng_query($query);

adminwritelog(uiTextSnippet('modifyassoc') . ": $assocID/$personID::$passocID ($relationship)");

//get name
if ($reltype == 'I') {
  $query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix FROM people WHERE personID = '$passocID'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $rightbranch = checkbranch($row['branch']);
  $rights = determineLivingPrivateRights($row, $rightbranch);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  $name = getName($row) . " ($passocID)";
} else {
  $query = "SELECT husband, wife, familyID FROM families WHERE familyID = '$passocID'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $name = getFamilyName($row);
}
$namestr = cleanIt($name . ': ' . stripslashes($orgrelationship));

$namestr = truncateIt($namestr, 75);
tng_free_result($result);
header('Content-type:text/html; charset=' . $sessionCharset);
echo "{\"display\":\"$namestr\"}";
