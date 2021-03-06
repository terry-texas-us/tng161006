<?php

require 'begin.php';
require 'getlang.php';

$tngconfig['maint'] = '';
require 'genlib.php';

if ($adminLogin) {
  $home_url = 'admin.php';
  $login_url = 'admin_login.php?';
  $dest_url = $_SESSION['destinationpage8'] && $continue ? $_SESSION['destinationpage8'] : $home_url;
} else {
  $home_url = $homepage;
  $dest_url = isset($_SESSION['destinationpage8']) ? $_SESSION['destinationpage8'] : $home_url;
  $login_url = $requirelogin || !isset($_SESSION['destinationpage8']) || strpos($_SESSION['destinationpage8'], $home_url) !== false || substr($_SESSION['destinationpage8'], -1) == '/' ? 'login.php?message=loginfailed' : $dest_url;
}
$query = "SELECT * FROM users WHERE BINARY username = '$tngusername'";
$result = tng_query($query) or die("Cannot execute query: $query");
if (tng_num_rows($result)) {
  $row = tng_fetch_assoc($result);
  $type = $encrypted ? $encrypted : $row['password_type'];
  $check = PasswordCheck($tngpassword, $row['password'], $type);
  if ($check == 2) { // match but the hash type is not the same as PasswordType()
    $password_type = PasswordType();    // update password to the new encoding method specified by PasswordType()
    $password = PasswordEncode($tngpassword, $password_type);

    $query2 = "UPDATE users SET password = '$password', password_type = '$password_type' WHERE userID = \"{$row['userID']}\"";

    $result2 = tng_query($query) or die("Cannot execute query: $query");
  }
} else {
  $check = 0;
}
$headerstr = $login_url;
$newroot = preg_replace('/\//', '', $rootpath);
$newroot = preg_replace('/ /', '', $newroot);
$newroot = preg_replace('/\./', '', $newroot);

if ($check) {
  if ($row['disabled']) {
    setcookie("tngerror_$newroot", 'disabled', 0, '/');
  } elseif ($row['allow_living'] == -1) { // this column uses -1 to indicate an inactive user account
    setcookie("tngerror_$newroot", 'logininactive', 0, '/');
  } else {
    $allow_admin = $row['allow_edit'] || $row['allow_add'] || $row['allow_delete'] ? 1 : 0;
    if (!$adminLogin || $allow_admin) {
      $newdate = date('Y-m-d H:i:s', time() + (3600 * $timeOffset));
      if ($resetpass && $newpassword && $row['allow_profile']) {
        $password_type = PasswordType();
        $query = 'UPDATE users SET password="' . PasswordEncode($newpassword) . "\", lastlogin=\"$newdate\", password_type=\"$password_type\" WHERE userID=\"{$row['userID']}\"";
      } else {
        $query = "UPDATE users SET lastlogin=\"$newdate\" WHERE userID=\"{$row['userID']}\"";
      }
      $uresult = tng_query($query) or die("Cannot execute query: $query");
      if ($remember) {
        setcookie("tnguser_$newroot", $tngusername, time() + 31536000, '/');
        setcookie("tngpass_$newroot", $row['password'], time() + 31536000, '/');
        setcookie("tngpasstype_$newroot", $row['password_type'], time() + 31536000, '/');
      }
      if ($adminLogin) {
        setcookie("tngloggedin_$newroot", '1', 0, '/');
      }
      $logged_in = $_SESSION['logged_in'] = 1;

      $allowEdit = $_SESSION['allow_edit'] = ($row['allow_edit'] == 1 ? 1 : 0);
      $allowAdd = $_SESSION['allow_add'] = ($row['allow_add'] == 1 ? 1 : 0);
      $tentative_edit = $_SESSION['tentative_edit'] = $row['tentative_edit'];
      $allowDelete = $_SESSION['allow_delete'] = ($row['allow_delete'] == 1 ? 1 : 0);

      $allowMediaEdit = $_SESSION['allow_media_edit'] = ($row['allow_edit'] ? 1 : 0);
      $allowMediaAdd = $_SESSION['allow_media_add'] = ($row['allow_add'] ? 1 : 0);
      $allowMediaDelete = $_SESSION['allow_media_delete'] = ($row['allow_delete'] ? 1 : 0);

      $_SESSION['mygedcom'] = $row['mygedcom'];
      $_SESSION['mypersonID'] = $row['personID'];
      $_SESSION['allow_admin'] = $allow_admin;
      $_SESSION['tngrole'] = $row['role'];

      if (!$livedefault) { //depends on permissions
        $allowLiving = $_SESSION['allow_living'] = $row['allow_living'];
      } elseif ($livedefault == 2) { //always do living
        $allowLiving = $_SESSION['allow_living'] = 1;
      } else { //never do living
        $allowLiving = $_SESSION['allow_living'] = 0;
      }
      $allowPrivate = $_SESSION['allow_private'] = $row['allow_private'];

      $allowGed = $_SESSION['allow_ged'] = $row['allow_ged'];
      $allowPdf = $_SESSION['allow_pdf'] = $row['allow_pdf'];
      $allow_profile = $_SESSION['allow_profile'] = $row['allow_profile'];

      if (!$ldsdefault) { //always do lds
        $allowLds = $_SESSION['allow_lds'] = 1;
      } elseif ($ldsdefault == 2) { //depends on permissions
        $allowLds = $_SESSION['allow_lds'] = $row['allow_lds'];
      } else { //never do lds
        $allowLds = $_SESSION['allow_lds'] = 0;
      }
      $assignedbranch = $_SESSION['assignedbranch'] = $row['branch'];
      $currentuser = $_SESSION['currentuser'] = $row['username'];
      $currentuserdesc = $_SESSION['currentuserdesc'] = $row['description'];
      $session_rp = $_SESSION['session_rp'] = $rootpath;

      $headerstr = $dest_url;
    } else {
      setcookie("tngerror_$newroot", 'norights', 0, '/');
    }
  }
} else {
  setcookie("tngerror_$newroot", 'loginfailed', 0, '/');
}
tng_free_result($result);
header("Location: $headerstr");