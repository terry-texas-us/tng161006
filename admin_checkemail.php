<?php

include("begin.php");
include("adminlib.php");

include("checklogin.php");

$query = "SELECT userId FROM $users_table WHERE LOWER(email) = LOWER(\"$checkemail\")";
$result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");

if ($result && tng_num_rows($result)) {
  $message = uiTextSnippet('isinuse');
  $success = "msgerror";
} else {
  $message = uiTextSnippet('isok');
  $success = "msgapproved";
}
tng_free_result($result);

header("Content-type:text/html; charset=" . $session_charset);
echo "{\"result\":\"$success\",\"message\":\"$message\"}";