<?php
include("begin.php");
include("genlib.php");
include("getlang.php");

include("tngmaillib.php");

//look up email
$email = trim($email);

$valid_user_agent = isset($_SERVER["HTTP_USER_AGENT"]) && $_SERVER["HTTP_USER_AGENT"] != "";

if (preg_match("/\n[[:space:]]*(to|bcc|cc|boundary)[[:space:]]*[:|=].*@/i", $email) || !$valid_user_agent) {
  die("sorry!");
}
if (preg_match("/\r/i", $email) || preg_match("/\n/i", $email)) {
  die("sorry!");
}

$email = strtok($email, ",; ");
$div = "";

if ($email) {
  $sendmail = 0;

  //if username is there too, then look up based on username and get password
  if ($username) {
    $query = "SELECT realname, allow_profile FROM $users_table WHERE username = \"$username\"";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    tng_free_result($result);

    $div = "pwdmsg";
    if ($row['allow_profile']) {
      $newpassword = generatePassword(0);
      $query = "UPDATE $users_table SET password = \"" . PasswordEncode($newpassword) . "\", password_type = \"" . PasswordType() . "\" WHERE email = \"$email\" AND username = \"$username\" AND allow_living != \"-1\"";
      $result = tng_query($query);
      $success = tng_affected_rows();

      if ($success) {
        $sendmail = 1;
        $content = uiTextSnippet('newpass') . ": $newpassword";
        $message = uiTextSnippet('pwdsent');
      } else {
        $message = uiTextSnippet('loginnotsent3');
      }
    } else {
      $message = uiTextSnippet('loginnotsent');
    }
  } else {
    $div = "usnmsg";
    $query = "SELECT realname, username FROM $users_table WHERE email = \"$email\"";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    tng_free_result($result);

    if ($row['username']) {
      $sendmail = 1;
      $content = uiTextSnippet('logininfo') . ":\n\n" . uiTextSnippet('username') . ": {$row['username']}";
      $message = uiTextSnippet('usersent');
    } else {
      $message = uiTextSnippet('loginnotsent2');
    }
  }

  if ($sendmail) {
    $mailmessage = $content;
    $owner = preg_replace("/,/", "", ($sitename ? $sitename : ($dbowner ? $dbowner : "TNG")));

    tng_sendmail($owner, $emailaddr, $row['realname'], $email, uiTextSnippet('logininfo'), $mailmessage, $emailaddr, $emailaddr);
  }
}
header("Content-type:text/html; charset=" . $session_charset);
echo "{\"div\":\"$div\", \"msg\":\"$message\"}";