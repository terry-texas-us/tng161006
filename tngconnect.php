<?php

function tng_db_connect($dbhost, $dbname, $dbusername, $dbpassword) {
  global $textpart;
  global $session_charset;
  global $tng_notinstalled;

  $link = tng_connect($dbhost, $dbusername, $dbpassword);
  if ($link && tng_select_db($link, $dbname)) {
    if ($session_charset == 'UTF-8') {
      tng_set_charset($link, 'utf8');
    }
    return $link;
  } else {
    if ($textpart != 'setup' && $textpart != 'index') {
      if (isset($tng_notinstalled) && $tng_notinstalled) {
        header('Location:readme.html');
        exit;
      } else {
        echo 'Error: Please check your database settings and try again.';
      }
      exit;
    }
  }
  return (false);
}

function tng_affected_rows() {
  global $link;
  return mysqli_affected_rows($link);
}

function tng_connect($dbhost, $dbusername, $dbpassword) {
  return mysqli_connect($dbhost, $dbusername, $dbpassword);
}

function tng_data_seek($result, $offset) {
  return mysqli_data_seek($result, $offset);
}

function tng_error() {
  return mysqli_error();
}

function tng_fetch_assoc($result) {
  return mysqli_fetch_assoc($result);
}

function tng_fetch_array($result, $resulttype = null) {
  if ($resulttype == 'assoc') {
    $usetype = MYSQLI_ASSOC;
  } elseif ($resulttype == 'num') {
    $usetype = MYSQLI_NUM;
  } else {
    $usetype = null;
  }
  return $usetype ? mysqli_fetch_array($result, $usetype) : mysqli_fetch_array($result);
}

function tng_field_info($result, $fieldnr, $info) {
  $fielddef = mysqli_fetch_field_direct($result, $fieldnr);

  eval("\$fieldinfo = \$fielddef->$info;");
  return $fieldinfo;
}

function tng_get_client_info() {
  global $link;
  return mysqli_get_client_info($link);
}

function tng_get_server_info() {
  global $link;
  return mysqli_get_server_info($link);
}

function tng_free_result($result) {
  mysqli_free_result($result);
}

function tng_insert_id() {
  global $link;
  return mysqli_insert_id($link);
}

function tng_real_escape_string($escapestr) {
  global $link;
  return mysqli_real_escape_string($link, $escapestr);
}

function tng_num_fields($result) {
  return mysqli_num_fields($result);
}

function tng_num_rows($result) {
  return mysqli_num_rows($result);
}

function tng_set_charset($link, $charset) {
  return mysqli_set_charset($link, $charset);
}

function tng_select_db($link, $dbname) {
  return mysqli_select_db($link, $dbname);
}

function tng_query($query) {
  global $link;

  $result = mysqli_query($link, $query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
  return $result;
}

function tng_query_noerror($query) {
  global $link;

  $result = mysqli_query($link, $query);
  return $result;
}

function tng_next_result() {
  global $link;
  return mysqli_next_result($link);
}
