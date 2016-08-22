<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

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

header("Location: admin_whatsnewmsg.php?color=$color&message=" . urlencode($message));
