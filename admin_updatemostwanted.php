<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowEdit && !$allowAdd) {
  exit;
}

require 'adminlog.php';

if ($sessionCharset != 'UTF-8') {
  $title = tng_utf8_decode($title);
  $description = tng_utf8_decode($description);
}

$title = addslashes($title);
$description = addslashes($description);

$cleaned = cleanIt($description);
$truncated = substr($cleaned, 0, 90);
$truncated = strlen($cleaned) > 90 ? substr($truncated, 0, strrpos($truncated, ' ')) . '&hellip;' : $cleaned;
$cleantitle = cleanIt($title);
if ($mediaID == '') {
  $mediaID = 0;
}

if ($ID) {
  $query = "UPDATE mostwanted SET title='$title', description='$description', personID='$personID', mediaID='$mediaID' WHERE ID='$ID'";
  $result = tng_query($query);
} else {
  //get new ordernum
  $query2 = "SELECT max(ordernum) AS maxordernum FROM mostwanted WHERE mwtype = '$mwtype'";
  $result2 = tng_query($query2) or die(uiTextSnippet('cannotexecutequery') . ": $query2");
  $row2 = tng_fetch_assoc($result2);
  $ordernum = $row2['maxordernum'] + 1;
  tng_free_result($result2);

  $query = "INSERT INTO mostwanted (title, description, personID, mediaID, mwtype, ordernum) VALUES ('$title', '$description', '$personID', '$mediaID', '$mwtype', $ordernum)";
  $result = tng_query($query);
  $ID = tng_insert_id();
}

//get thumbnail path
$thumbpath = '';
$size = [0, 0];
if ($mediaID && $mediaID != $orgmediaID) {
  initMediaTypes();
  $query = "SELECT * FROM media WHERE mediaID = '$mediaID'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $mediatypeID = $row['mediatypeID'];
  $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;

  if ($row['thumbpath'] && file_exists("$rootpath$usefolder/" . $row['thumbpath'])) {
    $size = getimagesize("$rootpath$usefolder/" . $row['thumbpath']);
    $thumbpath = "$usefolder/" . str_replace('%2F', '/', rawurlencode($row['thumbpath']));
  }
  tng_free_result($result);
}

adminwritelog(uiTextSnippet('mostwanted') . " : $title");

$truncated = str_replace("\r", '', $truncated);
$truncated = str_replace("\n", '', $truncated);
header('Content-Type: application/json; charset=' . $sessionCharset);
echo "{\n";
$cleaned = stripslashes($cleaned);
$truncated = stripslashes($truncated);
echo "\"ID\":\"$ID\",\"title\":\"$cleantitle\",\"description\":\"$truncated\",\"mwtype\":\"$mwtype\",\"mediaID\":\"$mediaID\",\"thumbpath\":\"$thumbpath\",\"width\":\"{$size[0]}\",\"height\":\"{$size[1]}\",\"edit\":\"$allowEdit\",\"del\":\"$allowDelete\"";
echo '}';