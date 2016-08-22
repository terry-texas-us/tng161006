<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

require 'adminlog.php';

if (!$allowAdd) {
  $message = uiTextSnippet('norights');
  exit;
}
if ($session_charset != "UTF-8") {
  $relationship = tng_utf8_decode($relationship);
}
$relationship = addslashes($relationship);

$query = "INSERT INTO $assoc_table (gedcom, personID, passocID, relationship, reltype)  VALUES('', '$personID', '$passocID', '$relationship', '$reltype')";
$result = tng_query($query);
$assocID = tng_insert_id();

if ($revassoc) {
  $query = "INSERT INTO $assoc_table (gedcom, personID, passocID, relationship, reltype)  VALUES('', '$passocID', '$personID', '$relationship', '$orgreltype')";
  $result = tng_query($query);
}
adminwritelog(uiTextSnippet('addnewassoc') . ": $assocID/$personID::$passocID ($relationship)");

//get name
if ($reltype == 'I') {
  $query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix FROM $people_table WHERE personID = '$passocID'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $rightbranch = checkbranch($row['branch']);
  $rights = determineLivingPrivateRights($row, $rightbranch);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  $name = getName($row) . " ($passocID)";
} else {
  $query = "SELECT husband, wife, gedcom, familyID FROM $families_table WHERE familyID = '$passocID'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $name = getFamilyName($row);
}
$namestr = cleanIt($name . ": " . stripslashes($relationship));
$namestr = truncateIt($namestr, 75);
tng_free_result($result);
header("Content-type:text/html; charset=" . $session_charset);
echo "{\"id\":\"$assocID\",\"persfamID\":\"$personID\",\"display\":\"$namestr\",\"allow_edit\":$allowEdit,\"allow_delete\":$allowDelete}";
