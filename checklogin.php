<?php

$assignedbranch = $_SESSION['assignedbranch'];
$currentuser = $_SESSION['currentuser'];
$currentuserdesc = $_SESSION['currentuserdesc'];
$thispage = getScriptName(false);

global $adminLogin;

if (isset($_SESSION['postvars']) && is_array($_SESSION['postvars'])) {
  foreach ($_SESSION['postvars'] as $key => $value) {
    ${$key} = $value;
  }
  $postvars = $_SESSION['postvars'] = '';
} elseif (!$adminLogin) {
  $postvars = $_SESSION['postvars'] = $_POST;
  $nodest_array = ['admi', 'ajx_', 'rpt_', 'find', 'tngr', 'gedc', 'goog', 'img_'];
  if (!$tngprint && !in_array(substr(basename($thispage), 0, 4), $nodest_array) && !$maintenance_mode) {
    $protocol = $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://';
    $destinationpage = $_SESSION['destinationpage8'] = $protocol . $_SERVER['HTTP_HOST'];
    $destinationpage = $_SESSION['destinationpage8'] .= $thispage;
  }
}

if ($_SESSION['logged_in'] && $_SESSION['session_rp'] == $rootpath && (!$adminLogin || ($_SESSION['allow_admin'] && $currentuser))) {
  if ($currentuser == 'Administrator-No-Users-Yet') {
    $query = "SELECT userID FROM $users_table";
    $result = tng_query_noerror($query);
    if ($result && tng_num_rows($result)) {
      echo "$currentuser" . ' is not a valid user';
      exit;
    }
    if ($result) {
      tng_free_result($result);
    }
  }
  $allow_admin = $_SESSION['allow_admin'];

  $allowEdit = $_SESSION['allow_edit'];
  
  $allowAdd = $_SESSION['allow_add'];
  $tentative_edit = $_SESSION['tentative_edit'];
  $allowDelete = $_SESSION['allow_delete'];
  $allowMediaEdit = $_SESSION['allow_media_edit'];
  $allowMediaAdd = $_SESSION['allow_media_add'];
  $allowMediaDelete = $_SESSION['allow_media_delete'];
  $allow_living = $_SESSION['allow_living'];
  $allow_private = $_SESSION['allow_private'];
  $allow_ged = $_SESSION['allow_ged'];
  $allow_pdf = $_SESSION['allow_pdf'];
  $allow_lds = $_SESSION['allow_lds'];
  $allow_profile = $_SESSION['allow_profile'];
  $logged_in = 1;
} else {
  $query = "SELECT userID FROM $users_table";
  $result = tng_query_noerror($query);
  if (!$result || !tng_num_rows($result)) {
    $allow_admin = 1;
    $allowEdit = 1;
    $allowAdd = 1;
    $tentative_edit = 0;
    $allowDelete = 1;
    $allowMediaEdit = 1;
    $allowMediaAdd = 1;
    $allowMediaDelete = 1;
    $allow_living = 1;
    $allow_private = 1;
    $allow_ged = $allow_pdf = $allow_profile = 1;
    $allow_lds = 1;
    $_SESSION['currentuser'] = 'Administrator-No-Users-Yet';
    $_SESSION['currentuserdesc'] = 'Administrator';
    $logged_in = $_SESSION['logged_in'] = 1;
    $_SESSION['session_rp'] = $rootpath;
    $_SESSION['tngrole'] = 'admin';
  } else {
    if ($adminLogin == 1) {
      $postvars = $_SESSION['postvars'] = $_POST;
      $protocol = $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://';
      $destinationpage = $_SESSION['destinationpage8'] = $protocol . $_SERVER['HTTP_HOST'];
      $destinationpage = $_SESSION['destinationpage8'] .= $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
    }
    $newroot = preg_replace('/\//', '', $rootpath);
    $newroot = preg_replace('/ /', '', $newroot);
    $newroot = preg_replace('/\./', '', $newroot);
    $usercookiename = "tnguser_$newroot";
    if ($_COOKIE[$usercookiename]) {
      $passcookiename = "tngpass_$newroot";
      $passtype = "tngpasstype_$newroot";
      $adminloginstr = $adminLogin ? 'adminLogin=1&amp;continue=1&amp;' : '';
      header('Location: ' . "processlogin.php?{$adminloginstr}tngusername=" . $_COOKIE[$usercookiename] . '&tngpassword=' . $_COOKIE[$passcookiename] . '&encrypted=encrypted');
      exit;
    }
    if ($adminLogin) {
      header('Location: admin_login.php?continue=1');
      exit;
    } elseif ($requirelogin) {
      if (!substr_count($_SERVER['SCRIPT_NAME'], '/index.') && !substr_count($_SERVER['SCRIPT_NAME'], '/ajx_tnginstall.php')) {
        header('Location: login.php');
        exit;
      }
    } else {
      $_SESSION['currentuser'] = '';
      $_SESSION['currentuserdesc'] = '';
      $_SESSION['mygedcom'] = '';
      $_SESSION['mypersonID'] = '';
      $_SESSION['tngrole'] = 'guest';

      $allow_admin = 0;
      $allowEdit = $allowAdd = $tentative_edit = $allowDelete = $allowMediaAdd = $allowMediaEdit = $allowMediaDelete = 0;
      $allow_living = $livedefault == 2 ? 1 : 0;
      $allow_private = 0;
      $allow_ged = $allow_pdf = $allow_profile = 0;
      $allow_lds = $ldsdefault ? 0 : 1;

      $currentuser = $_SESSION['currentuser'];
      $currentuserdesc = $_SESSION['currentuserdesc'];
      $_SESSION['session_rp'] = $rootpath;
    }
  }
  if ($result) {
    tng_free_result($result);
  }

  //set session vars here if not previously logged in
  $_SESSION['allow_admin'] = $allow_admin;
  $_SESSION['allow_edit'] = $allowEdit;
  $_SESSION['allow_add'] = $allowAdd;
  $_SESSION['tentative_edit'] = $tentative_edit;
  $_SESSION['allow_delete'] = $allowDelete;
  $_SESSION['allow_media_edit'] = $allowMediaEdit;
  $_SESSION['allow_media_add'] = $allowMediaAdd;
  $_SESSION['allow_media_delete'] = $allowMediaDelete;
  $_SESSION['allow_living'] = $allow_living;
  $_SESSION['allow_private'] = $allow_private;
  $_SESSION['allow_ged'] = $allow_ged;
  $_SESSION['allow_pdf'] = $allow_pdf;
  $_SESSION['allow_lds'] = $allow_lds;
  $_SESSION['allow_profile'] = $allow_profile;
}

$postvars = $_SESSION['postvars'] = '';
unset($_SESSION['postvars']);