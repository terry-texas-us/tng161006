<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

require 'adminlog.php';
require 'mail.php';

$wherestr = $branch ? " AND branch = '$branch'" : '';

$recipientquery = "SELECT realname, email FROM $users_table WHERE allow_living != '-1' AND email != '' AND (no_email is NULL or no_email != '1') $wherestr";
$result = tng_query($recipientquery) or die(uiTextSnippet('cannotexecutequery') . ": $recipientquery");
$numrows = tng_num_rows($result);

if (!$numrows) {
  $message = uiTextSnippet('nousers');
  header('Location: usersBrowse.php?message=' . urlencode($message));
} else {
  $subject = stripslashes($subject);
  $body = stripslashes($messagetext);
  $owner = preg_replace('/,/', '', ($sitename ? $sitename : ($dbowner ? $dbowner : 'TNG')));

  while ($row = tng_fetch_assoc($result)) {
    $recipient = $row['email'];
    tng_sendmail($owner, $emailaddr, $row['realname'], $recipient, $subject, $body, $emailaddr, $emailaddr);
  }
  adminwritelog(uiTextSnippet('sentmailmessage'));
  $message = uiTextSnippet('succmail') . '.';
}
tng_free_result($result);

header('Location: usersSendMail.php?message=' . urlencode($message));
