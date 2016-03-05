<?php

include("begin.php");
include("genlib.php");
include("getlang.php");

include("checklogin.php");

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

$query = "SELECT title FROM $sources_table WHERE sourceID = \"$ID\" AND gedcom = \"$tree\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
$name = uiTextSnippet('source') . ": {$row['title']} ($ID)";
$pagelink = "$tngwebsite/" . "sourcesShowSource.php?sourceID=$ID&tree=$tree";
tng_free_result($result);

$subject = uiTextSnippet('proposed') . ": $name";
$query = "SELECT treename, email, owner FROM $trees_table WHERE gedcom=\"$tree\"";
$treeresult = tng_query($query);
$treerow = tng_fetch_assoc($treeresult);
tng_free_result($treeresult);

$body = uiTextSnippet('proposed') . ": $name\n" . uiTextSnippet('tree') . ": {$treerow['treename']}\n" . uiTextSnippet('link') . ": $pagelink\n\n" . uiTextSnippet('description') . ": " . stripslashes($comments) . "\n\n$yourname\n$youremail";

$sendemail = $treerow['email'] ? $treerow['email'] : $emailaddr;
$owner = $treerow['owner'] ? $treerow['owner'] : ($sitename ? $sitename : $dbowner);

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
header("Location: sourceSuggest.php?ID=$ID&tree=$tree&message=$message");