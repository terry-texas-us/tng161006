<?php

include("begin.php");
include("genlib.php");
include("getlang.php");

include($subroot . "logconfig.php");
include("tngmaillib.php");

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
if ($addr_exclude) {
  $bad_addrs = explode(",", $addr_exclude);
  foreach ($bad_addrs as $bad_addr) {
    if ($bad_addr) {
      if (strstr($youremail, trim($bad_addr))) {
        die("sorry");
      }
    }
  }
}
if ($msg_exclude) {
  $bad_msgs = explode(",", $msg_exclude);
  foreach ($bad_msgs as $bad_msg) {
    if ($bad_msg) {
      if (strstr($comments, trim($bad_msg))) {
        die("sorry");
      }
    }
  }
}
$subject = uiTextSnippet('yourcomments') . " (" . $page . ")";
$body = $subject . ": " . stripslashes($comments) . "\n\n$yourname\n$youremail";

$sendemail = $emailaddr;
$owner = $sitename ? $sitename : $dbowner;

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
header("Location: contactUs.php?page=$page&tree=$tree&message=$message");
