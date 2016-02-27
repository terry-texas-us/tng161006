<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if ($assignedtree) {
  $message = uiTextSnippet('norights');
  $color = 'msgwarning';
} else {
  $whatsnewmsg = stripslashes($whatsnewmsg);

  $file = "$rootpath/whatsnew.txt";

  $fp = fopen($file, "w");
  if (!$fp) {
    die(uiTextSnippet('cannotopen') . " $file");
  }

  flock($fp, LOCK_EX);
  fwrite($fp, $whatsnewmsg);
  flock($fp, LOCK_UN);
  fclose($fp);
  $message = uiTextSnippet('msgsaved');
  $color = "msgapproved";
}
header("Location: admin_whatsnewmsg.php?color=$color&message=" . urlencode($message));
