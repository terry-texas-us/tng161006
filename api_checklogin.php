<?php

$assignedbranch = $_SESSION['assignedbranch'];
$currentuser = $_SESSION['currentuser'];

if ($_SESSION['logged_in'] && $_SESSION['session_rp'] == $rootpath && $currentuser) {
  $allowLiving = $_SESSION['allow_living'];
  $allowPrivate = $_SESSION['allow_private'];
  $allowLds = $_SESSION['allow_lds'];
} else {
  $query = "SELECT * FROM users WHERE BINARY username = '$tngusername'";
  $result = tng_query($query) or die("Cannot execute query: $query");
  if (tng_num_rows($result)) {
    $allowLiving = $allowPrivate = $allowLds = 0;
  } else {
    $allowLiving = $allowPrivate = $allowLds = 1;
  }
  tng_free_result($result);
}