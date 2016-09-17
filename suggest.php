<?php

function getCurrentUserEmail($currentUser)
{
  $out = '';
  if ($currentUser) {
    $query = "SELECT email FROM users WHERE username='$currentUser'";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    $out .= $row['email'];
    tng_free_result($result);
  }
  return $out;
}

function killBlockedAddress($address)
{
  global $addr_exclude;
  
  if ($addr_exclude) {
    $blockedAddresses = explode(',', $addr_exclude);
    foreach ($blockedAddresses as $blockedAddress) {
      if ($blockedAddress) {
        if (strstr($address, trim($blockedAddress))) {
          die('sorry');
        }
      }
    }
  }
  return false;
}

function killBlockedMessageContent($comments)
{
  global $msg_exclude;
  
  if ($msg_exclude) {
    $snippets = explode(',', $msg_exclude);
    foreach ($snippets as $snippet) {
      if ($snippet) {
        if (strstr($comments, trim($snippet))) {
          die('sorry');
        }
      }
    }
  }
}

function echoResponseMessage($message, $sowner, $ssendemail)
{
  if ($message) {
    $newmessage = uiTextSnippet($message);
    if ($message == 'mailsent') {
      $newmessage = "<div class='alert alert-success' role='alert'>$newmessage</div>";
    } else if ($message == 'mailnotsent') {
      $newmessage = "<div class='alert alert-danger' role='alert'>" . sprintf($newmessage, $sowner, $ssendemail) . '</div>';
    }
    echo "$newmessage\n";
  }
}