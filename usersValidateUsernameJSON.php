<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

$query = "SELECT username FROM users WHERE username = '$checkuser'";
$result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");

if ($result && tng_num_rows($result)) {
  $message = "<b>$checkuser</b> " . uiTextSnippet('idinuse');
  $success = 'false';
} else {
  $message = "<b>$checkuser</b> " . uiTextSnippet('idok');
  $success = 'true';
}
tng_free_result($result);

header('Content-Type: application/json; charset=' . $sessionCharset);
echo "{\"rval\":$success,\"html\":\"$message\"}";
