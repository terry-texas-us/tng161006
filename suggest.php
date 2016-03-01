<?php

function getCurrentUserEmail($currentUser, $users) {
  $out = '';
  if ($currentUser) {
    $query = "SELECT email FROM $users WHERE username='$currentUser'";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    $out .= $row['email'];
    tng_free_result($result);
  }
  return $out;
}

function echoResponseMessage($message, $sowner, $ssendemail) {
  if ($message) {
    $newmessage = uiTextSnippet($message);
    if ($message == "mailnotsent") {
      $newmessage = preg_replace("/xxx/", $sowner, $newmessage);
      $newmessage = preg_replace("/yyy/", $ssendemail, $newmessage);
    }
    echo "<p><font color='red'>$newmessage</font></p>\n";
  }
}