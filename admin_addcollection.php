<?php

include("begin.php");
include("adminlib.php");

include("checklogin.php");

if (!$allow_add) {
  exit;
}

require("adminlog.php");

$display_org = stripslashes($display);

if ($session_charset != "UTF-8") {
  $display = tng_utf8_decode($display);
}

$display = addslashes($display);

$stdcolls = array("photos", "histories", "headstones", "documents", "recordings", "videos");
$collid = cleanID($collid);
$newcollid = 0;
if (!in_array($collid, $stdcolls)) {
  $query = "INSERT IGNORE INTO $mediatypes_table (mediatypeID,display,path,liketype,icon,thumb,exportas,ordernum) VALUES (\"$collid\",\"$display\",\"$path\",\"$liketype\",\"$icon\",\"$thumb\",\"$exportas\",\"$ordernum\")";
  $result = tng_query($query);

  if (tng_affected_rows() > 0) {
    adminwritelog(uiTextSnippet('addnewcoll') . ": $display_org");
    $newcollid = $collid;
  }
}
echo $newcollid;
