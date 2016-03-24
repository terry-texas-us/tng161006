<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

require("adminlog.php");
require 'mail.php';

if ($assignedtree || !$allowAdd) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$description = addslashes($description);
$username = addslashes($username);
$gedcom = addslashes($gedcom);
$branch = addslashes($branch);
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

$orgpwd = $password;
$password = PasswordEncode($password);
$password_type = PasswordType();

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
$today = date("Y-m-d H:i:s", time() + (3600 * $time_offset));

$duplicate = false;
$emailstr = $email ? " OR LOWER(email) = LOWER(\"$email\")" : "";
$query = "SELECT username FROM $users_table WHERE LOWER(username) = LOWER(\"$username\")$emailstr";
$result = tng_query($query);

if ($result && tng_num_rows($result)) {
  $duplicate = true;
}

if (!$duplicate) {
  $query = "INSERT IGNORE INTO $users_table (description,username,password,password_type,realname,phone,email,website,address,city,state,zip,country,notes,gedcom,mygedcom,personID,role,allow_edit,allow_add,tentative_edit,allow_delete,allow_lds,allow_living,allow_private,allow_ged,allow_pdf,allow_profile,branch,dt_activated,no_email,disabled)
    VALUES (\"$description\",\"$username\",\"$password\",\"$password_type\",\"$realname\",\"$phone\",\"$email\",\"$website\",\"$address\",\"$city\",\"$state\",\"$zip\",\"$country\",\"$notes\",\"$gedcom\",\"$mynewgedcom\",\"$personID\",\"$role\",\"$form_allow_edit\",\"$form_allow_add\",\"$form_tentative_edit\",\"$form_allow_delete\",\"$form_allow_lds\",\"$form_allow_living\",\"$form_allow_private\",\"$form_allow_ged\",\"$form_allow_pdf\",\"$form_allow_profile\",\"$branch\",\"$today\",\"$no_email\",\"$disabled\")";
  $result = tng_query($query);

  if ($notify && $email) {
    $owner = preg_replace("/,/", "", ($sitename ? $sitename : ($dbowner ? $dbowner : "TNG")));

    tng_sendmail($owner, $emailaddr, $realname, $email, uiTextSnippet('activated'), $welcome, $emailaddr, $emailaddr);
  }

  if (tng_affected_rows()) {
    $userID = tng_insert_id();
    adminwritelog("<a href=\"usersEdit.php?userID=$userID\">" . uiTextSnippet('addnewuser') . ": $username</a>");
    $message = uiTextSnippet('user') . " $username " . uiTextSnippet('succadded') . ".";
    if ($currentuser == "Administrator-No-Users-Yet") {
      $_SESSION['currentuser'] = $username;
      $_SESSION['currentuserdesc'] = $description;
    }
  } else {
    $message = uiTextSnippet('userfailed') . ".";
  }
} else {
  $message = uiTextSnippet('duplicate');
}
header("Location: usersBrowse.php?message=" . urlencode($message));
