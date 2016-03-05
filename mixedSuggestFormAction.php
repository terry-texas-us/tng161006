<?php

include("begin.php");
include("genlib.php");
include("getlang.php");

if ($enttype) {
  include("checklogin.php");
}
include($subroot . "logconfig.php");
require 'mail.php';

$valid_user_agent = isset($_SERVER["HTTP_USER_AGENT"]) && $_SERVER["HTTP_USER_AGENT"] != "";

$emailfield = $_SESSION['tng_email'];
eval("\$youremail = \$$emailfield;");
$_SESSION['tng_email'] = "";

$commentsfield = $_SESSION['tng_comments'];
eval("\$comments = \$$commentsfield;");
$_SESSION['tng_comments'] = "";

$yournamefield = $_SESSION['tng_yourname'];
eval("\$yourname = \$$yournamefield;");
$_SESSION['tng_yourname'] = "";

$tngwebsite = $tngdomain;

if (preg_match("/\n[[:space:]]*(to|bcc|cc|boundary)[[:space:]]*[:|=].*@/i", $youremail) || preg_match("/[\r|\n][[:space:]]*(to|bcc|cc|boundary)[[:space:]]*[:|=].*@/i", $yourname) || !$valid_user_agent) {
  die("sorry!");
}
if (preg_match("/\r/i", $youremail) || preg_match("/\n/i", $youremail) || preg_match("/\r/i", $yourname) || preg_match("/\n/i", $yourname)) {
  die("sorry!");
}

$youremail = strtok($youremail, ",; ");
if (!$youremail || !$comments || !$yourname) {
  die("sorry!");
}
killBlockedAddress($youremail);
killBlockedMessageContent($comments);

if ($enttype == 'I') {
  $typestr = "person";
  $query = "SELECT firstname, lnprefix, lastname, prefix, suffix, sex, nameorder, living, private, branch, disallowgedcreate, IF(birthdatetr !='0000-00-00',YEAR(birthdatetr),YEAR(altbirthdatetr)) as birth, IF(deathdatetr !='0000-00-00',YEAR(deathdatetr),YEAR(burialdatetr)) as death
    FROM $people_table, $trees_table WHERE personID = \"$ID\" AND $people_table.gedcom = \"$tree\" AND $people_table.gedcom = $trees_table.gedcom";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);

  $righttree = checktree($tree);
  $rights = determineLivingPrivateRights($row, $righttree);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];

  $name = getName($row) . " ($ID)";
  $pagelink = "$tngwebsite/" . "peopleShowPerson.php?personID=$ID&tree=$tree";
  tng_free_result($result);
} elseif ($enttype == 'F') {
  $typestr = "family";
  $query = "SELECT familyID, husband, wife, living, private, marrdate, gedcom, branch FROM $families_table WHERE familyID = \"$ID\" AND gedcom = \"$tree\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);

  $righttree = checktree($tree);
  $rights = determineLivingPrivateRights($row, $righttree);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];

  $name = uiTextSnippet('family') . ": " . getFamilyName($row);
  $pagelink = "$tngwebsite/" . "familygroup.php?familyID=$ID&tree=$tree";
  tng_free_result($result);
} elseif ($enttype == 'S') {
  $query = "SELECT title FROM $sources_table WHERE sourceID = \"$ID\" AND gedcom = \"$tree\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $name = uiTextSnippet('source') . ": {$row['title']} ($ID)";
  $pagelink = "$tngwebsite/" . "showsource.php?sourceID=$ID&tree=$tree";
  tng_free_result($result);
} elseif ($enttype == 'R') {
  $query = "SELECT reponame FROM $repositories_table WHERE repoID = \"$ID\" AND gedcom = \"$tree\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $name = uiTextSnippet('repository') . ": {$row['reponame']} ($ID)";
  $pagelink = "$tngwebsite/" . "repositoriesShowItem.php?repoID=$ID&tree=$tree";
  tng_free_result($result);
} elseif ($enttype == 'L') {
  $name = $ID;
  if ($tree && !$tngconfig['places1tree']) {
    $treestr = "tree=$tree&amp;";
  } else {
    $treestr = "";
  }
  $pagelink = "$tngwebsite/" . "placesearch.php?" . "{$treestr}psearch=" . urlencode($name);
}
if ($enttype) {
  $subject = uiTextSnippet('proposed') . ": $name";
  $query = "SELECT treename, email, owner FROM $trees_table WHERE gedcom=\"$tree\"";
  $treeresult = tng_query($query);
  $treerow = tng_fetch_assoc($treeresult);
  tng_free_result($treeresult);

  $body = uiTextSnippet('proposed') . ": $name\n" . uiTextSnippet('tree') . ": {$treerow['treename']}\n" . uiTextSnippet('link') . ": $pagelink\n\n" . uiTextSnippet('description') . ": " . stripslashes($comments) . "\n\n$yourname\n$youremail";

  $sendemail = $treerow['email'] ? $treerow['email'] : $emailaddr;
  $owner = $treerow['owner'] ? $treerow['owner'] : ($sitename ? $sitename : $dbowner);
} else {
  $page = $page ? " ($page)" : "";
  $subject = uiTextSnippet('yourcomments') . $page;
  $body = uiTextSnippet('yourcomments') . $page . ": " . stripslashes($comments) . "\n\n$yourname\n$youremail";

  $sendemail = $emailaddr;
  $owner = $sitename ? $sitename : $dbowner;
}
if ($currentuser) {
  $body .= "\n" . uiTextSnippet('user') . ": $currentuserdesc ($currentuser)";
}
$emailtouse = $tngconfig['fromadmin'] == 1 ? $emailaddr : $youremail;

$success = tng_sendmail($yourname, $emailtouse, $owner, $sendemail, $subject, $body, $emailaddr, $youremail);
if ($success) {
  $message = "mailsent";
  if ($mailme) {
    tng_sendmail($yourname, $emailtouse, $yourname, $youremail, $subject, $body, $emailaddr, $youremail);
  }
} else {
  $message = "mailnotsent&sowner=" . urlencode($owner) . "&ssendemail=" . urlencode($sendemail);
}
header("Location: mixedSuggest.php?enttype=$enttype&ID=$ID&tree=$tree&message=$message");
