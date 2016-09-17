<?php
require 'begin.php';
require 'genlib.php';
require 'getlang.php';

require 'mail.php';

$email = trim($email);

$valid_user_agent = isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] != '';

if (preg_match("/\n[[:space:]]*(to|bcc|cc|boundary)[[:space:]]*[:|=].*@/i", $email) || !$valid_user_agent) {
  die('sorry!');
}
if (preg_match("/\r/i", $email) || preg_match("/\n/i", $email)) {
  die('sorry!');
}
$email = strtok($email, ',; ');
$div = '';

if ($email) {
  $sendmail = 0;

  //if username is there too, then look up based on username and get password
  if ($username) {
    $query = "SELECT realname, allow_profile FROM users WHERE username = '$username'";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    tng_free_result($result);

    $div = 'pwdmsg';
    if ($row['allow_profile']) {
      $newpassword = generatePassword(0);
      $query = "UPDATE users SET password = \"" . PasswordEncode($newpassword) . '", password_type = "' . PasswordType() . "\" WHERE email = '$email' AND username = '$username' AND allow_living != '-1'";
      $result = tng_query($query);
      $success = tng_affected_rows();

      if ($success) {
        $sendmail = 1;
        $content = uiTextSnippet('newpass') . ": $newpassword";
        $message = "<div class='alert alert-success' role='alert'>" . uiTextSnippet('pwdsent') . '</div>';
      } else {
        $message = "<div class='alert alert-warning' role='alert'>" . uiTextSnippet('loginnotsent3') . '</div>';
      }
    } else {
      $message = "<div class='alert alert-warning' role='alert'>" . uiTextSnippet('loginnotsent') . '</div>';
    }
  } else {
    $div = 'usnmsg';
    $query = "SELECT realname, username FROM users WHERE email = '$email'";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    tng_free_result($result);

    if ($row['username']) {
      $sendmail = 1;
      $content = uiTextSnippet('logininfo') . ":\n\n" . uiTextSnippet('username') . ": {$row['username']}";
      $message = "<div class='alert alert-success' role='alert'>" . uiTextSnippet('usersent') . '</div>';
    } else {
      $message = "<div class='alert alert-warning' role='alert'>" . uiTextSnippet('loginnotsent2') . '</div>';
    }
  }
  if ($sendmail) {
    $mailmessage = $content;
    $owner = preg_replace('/,/', '', ($sitename ? $sitename : ($dbowner ? $dbowner : 'TNG')));

    tng_sendmail($owner, $emailaddr, $row['realname'], $email, uiTextSnippet('logininfo'), $mailmessage, $emailaddr, $emailaddr);
  }
}
header('Content-type:text/html; charset=' . $session_charset);
echo "{\"div\":\"$div\", \"msg\":\"$message\"}";
