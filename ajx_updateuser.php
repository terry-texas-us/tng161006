<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

require 'adminlog.php';

if (!$currentuser) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
$description = addslashes($description);
$username = addslashes($username);
$gedcom = addslashes($gedcom);
$branches = addslashes($branch);
$realname = addslashes($realname);
$phone = addslashes($phone);
$email = addslashes($email);
$address = addslashes($address);
$notes = addslashes($notes);
$website = addslashes($website);
$city = addslashes($city);
$state = addslashes($state);
$zip = addslashes($zip);
$country = addslashes($country);

$proceed = true;
if ($password != $orgpwd) {
  $query = "SELECT username FROM $users_table WHERE username = \"$checkuser\"";
  $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");

  if ($result && tng_num_rows($result)) {
    $proceed = false;
  }
  tng_free_result($result);
}

if ($proceed) {
  if (($password != $orgpwd) || $newuser) {
    $password = PasswordEncode($password);
    $password_type = PasswordType();
    $pwd_str = "password=\"$password\",password_type=\"$password_type\",";
  } else {
    $pwd_str = '';
  }

  $query = "UPDATE $users_table SET username=\"$username\",{$pwd_str}realname=\"$realname\",phone=\"$phone\",email=\"$email\",website=\"$website\",address=\"$address\",city=\"$city\",state=\"$state\",zip=\"$zip\",country=\"$country\" WHERE userID=\"$userID\"";
  $result = tng_query($query);

  adminwritelog("<a href=\"usersEdit.php?userID=$userID\">" . uiTextSnippet('modifyuser') . ": $userID</a>");

  if (tng_affected_rows() != -1 && ($password != $orgpwd || $username != $currentuser)) {
    $_SESSION['currentuser'] = $username;
    $newroot = preg_replace('/\//', '', $rootpath);
    $newroot = preg_replace('/ /', '', $newroot);
    $newroot = preg_replace('/\./', '', $newroot);
    setcookie("tnguser_$newroot", '', time() - 31536000, "/");
    setcookie("tngpass_$newroot", '', time() - 31536000, "/");
  }
}
header('Location: ' . $_SESSION['destinationpage8']);