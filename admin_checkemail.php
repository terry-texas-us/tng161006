<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

$query = "SELECT userId FROM users WHERE LOWER(email) = LOWER(\"$checkemail\")";
$result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");

if ($result && tng_num_rows($result)) {
  $message = uiTextSnippet('isinuse');
  $success = 'msgerror';
} else {
  $message = uiTextSnippet('isok');
  $success = 'msgapproved';
}
tng_free_result($result);

header('Content-type:text/html; charset=' . $sessionCharset);
echo "{\"result\":\"$success\",\"message\":\"$message\"}";
