<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

require("adminlog.php");
require 'mail.php';

if ($assignedtree) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
if ($gedcom) {
  $wherestr = " AND gedcom=\"$gedcom\"";
  if ($branch) {
    $wherestr .= " AND branch=\"$branch\"";
  }
}

$recipientquery = "SELECT realname, email FROM $users_table WHERE allow_living != \"-1\" AND email != \"\" AND (no_email is NULL or no_email != \"1\") $wherestr";
$result = tng_query($recipientquery) or die (uiTextSnippet('cannotexecutequery') . ": $recipientquery");
$numrows = tng_num_rows($result);

if (!$numrows) {
  $message = uiTextSnippet('nousers');
  header("Location: admin_users.php?message=" . urlencode($message));
} else {
  $subject = stripslashes($subject);
  $body = stripslashes($messagetext);
  $owner = preg_replace("/,/", "", ($sitename ? $sitename : ($dbowner ? $dbowner : "TNG")));

  while ($row = tng_fetch_assoc($result)) {
    $recipient = $row['email'];
    tng_sendmail($owner, $emailaddr, $row['realname'], $recipient, $subject, $body, $emailaddr, $emailaddr);
  }

  adminwritelog(uiTextSnippet('sentmailmessage'));
  $message = uiTextSnippet('succmail') . ".";
}

tng_free_result($result);

header("Location: admin_mailusers.php?message=" . urlencode($message));
