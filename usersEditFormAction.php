<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

require 'adminlog.php';
require 'mail.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$description = addslashes($description);
$username = addslashes($username);
$orguser = addslashes($orguser);
$orgemail = addslashes($orgemail);
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

if (($password != $orgpwd) || $newuser) {
  $password = PasswordEncode($password);
  $password_type = PasswordType();
  $pwd_str = "password=\"$password\",password_type=\"$password_type\",";
} else {
  $pwd_str = "";
}
if (!$form_allow_add) {
  $form_allow_add = 0;
}
if (!$form_allow_delete) {
  $form_allow_delete = 0;
}
if ($form_allow_edit == 2) {
  $form_tentative_edit = 1;
  $form_allow_edit = 0;
} else {
  $form_tentative_edit = 0;
}
if (!$form_allow_ged) {
  $form_allow_ged = 0;
}
if (!$form_allow_pdf) {
  $form_allow_pdf = 0;
}
if (!$form_allow_living) {
  $form_allow_living = 0;
}
if (!$form_allow_private) {
  $form_allow_private = 0;
}
if (!$form_allow_lds) {
  $form_allow_lds = 0;
}
if (!$form_allow_profile) {
  $form_allow_profile = 0;
}
if (!$no_email) {
  $no_email = 0;
}
if (!$disabled) {
  $disabled = 0;
}
$today = date("Y-m-d H:i:s", time() + (3600 * $timeOffset));

//if the username has changed, we must look up the new name to see if it exists
//if it exists, "duplicate"
$duplicate = false;
if ($username != $orguser) {
  $query = "SELECT username FROM $users_table WHERE LOWER(username) = LOWER(\"$username\")";
  $result = tng_query($query);

  if ($result && tng_num_rows($result)) {
    $duplicate = true;
  }
}
if (!$duplicate && $email && $email != $orgemail) {
  $query = "SELECT username FROM $users_table WHERE LOWER(email) = LOWER(\"$email\")";
  $result = tng_query($query);

  if ($result && tng_num_rows($result)) {
    $duplicate = true;
  }
}
if (!$duplicate) {
  $activatedstr = $newuser ? ", dt_activated=\"$today\"" : "";
  $query = "UPDATE $users_table SET description=\"$description\",username=\"$username\",{$pwd_str}realname=\"$realname\",phone=\"$phone\",email=\"$email\",website=\"$website\",address=\"$address\",city=\"$city\",state=\"$state\",zip=\"$zip\",country=\"$country\",notes=\"$notes\",gedcom=\"$gedcom\",mygedcom=\"$mynewgedcom\",personID=\"$personID\",role=\"$role\",allow_edit=\"$form_allow_edit\",allow_add=\"$form_allow_add\",tentative_edit=\"$form_tentative_edit\",allow_delete=\"$form_allow_delete\",allow_lds=\"$form_allow_lds\",allow_living=\"$form_allow_living\",allow_private=\"$form_allow_private\",allow_ged=\"$form_allow_ged\",allow_pdf=\"$form_allow_pdf\",allow_profile=\"$form_allow_profile\",branch=\"$branch\"{$activatedstr},no_email=\"$no_email\",disabled=\"$disabled\"
    WHERE userID=\"$userID\"";

  $result = tng_query($query);

  if ($notify && $email) {
    $owner = preg_replace("/,/", "", ($sitename ? $sitename : ($dbowner ? $dbowner : "TNG")));

    tng_sendmail($owner, $emailaddr, $realname, $email, uiTextSnippet('subjectline'), stripslashes($welcome), $emailaddr, $emailaddr);
  }
  adminwritelog("<a href=\"usersEdit.php?userID=$userID\">" . uiTextSnippet('modifyuser') . ": $userID</a>");

  $message = uiTextSnippet('changestouser') . " $userID " . uiTextSnippet('succsaved') . '.';
} else {
  $message = uiTextSnippet('duplicate');
}
if ($newuser) {
  if ($tngconfig['autotree'] && !$tngconfig['autoapp']) {
    $query = "INSERT IGNORE INTO $treesTable (gedcom, treename, description, owner, email, address, city, state, country, zip, phone, secret, disallowgedcreate) "
        . "VALUES ('', '$realname', '', '$realname', '$email', '$address', '$city', '$state', '$country', '$zip', '$phone', '0', '0')";
    $result = tng_query($query);
  }
  header("Location: usersReview.php?message=" . urlencode($message));
} else {
  header("Location: usersBrowse.php?message=" . urlencode($message));
}