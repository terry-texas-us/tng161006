<?php

require 'begin.php';
require 'processvars.php';
require 'adminlib.php';

if (!count($_POST)) {
  header('Location: admin.php');
  exit;
}

if (!$safety) {
  header('Location: admin_login.php');
  exit;
}

if ($link) {
  $adminLogin = 1;
  include 'checklogin.php';
  include 'version.php';

  if (!$allowEdit) {
    $message = uiTextSnippet('norights');
    header('Location: admin_login.php?message=' . urlencode($message));
    exit;
  }
}

$sitename = stripslashes($sitename);
$site_desc = stripslashes($site_desc);
$dbowner = stripslashes($dbowner);

$sitename = preg_replace('/\"/', '\\\"', $sitename);
$site_desc = preg_replace('/\"/', '\\\"', $site_desc);
$dbowner = preg_replace('/\"/', '\\\"', $dbowner);

$doctype = addslashes($doctype);

require 'adminlog.php';

$fp = fopen($subroot . 'config.php', 'w', 1);
if (!$fp) {
  die(uiTextSnippet('cannotopen') . ' config.php');
}

flock($fp, LOCK_EX);

if ($new_database_username) {
  $tng_notinstalled = '';
}

fwrite($fp, "<?php\n");
fwrite($fp, "\$database_host = \"$new_database_host\";\n");
fwrite($fp, "\$database_name = \"$new_database_name\";\n");
fwrite($fp, "\$database_username = \"$new_database_username\";\n");
fwrite($fp, "\$database_password = '$new_database_password';\n");
fwrite($fp, "\$tngconfig['maint'] = \"$maint\";\n");
fwrite($fp, "\n");
fwrite($fp, "\$people_table = \"$people_table\";\n");
fwrite($fp, "\$families_table = \"$families_table\";\n");
fwrite($fp, "\$children_table = \"$children_table\";\n");
fwrite($fp, "\$languagesTable = \"$languagesTable\";\n");

fwrite($fp, "\$rectangles_table = \"$rectangles_table\";\n");
fwrite($fp, "\$reports_table = \"$reports_table\";\n");
fwrite($fp, "\$saveimport_table = \"$saveimport_table\";\n");
fwrite($fp, "\$branches_table = \"$branches_table\";\n");
fwrite($fp, "\$branchlinks_table = \"$branchlinks_table\";\n");
fwrite($fp, "\$mostwanted_table = \"$mostwanted_table\";\n");
fwrite($fp, "\n");
if ($rootpath != $newrootpath) {
  $_SESSION['session_rp'] = $newrootpath;
}
fwrite($fp, "\$rootpath = \"$newrootpath\";\n");
fwrite($fp, "\$homepage = \"$homepage\";\n");
fwrite($fp, "\$tngdomain = \"$tngdomain\";\n");
fwrite($fp, "\$sitename = \"$sitename\";\n");
fwrite($fp, "\$site_desc = \"$site_desc\";\n");
fwrite($fp, "\$tngconfig['doctype'] = \"$doctype\";\n");
if (!$target) {
  $target = '_self';
}
fwrite($fp, "\$target = \"$target\";\n");
fwrite($fp, "\$language = \"$language\";\n");
fwrite($fp, "\$charset = \"$charset\";\n");
fwrite($fp, "\$maxsearchresults = \"$maxsearchresults\";\n");

$lineending = addslashes($lineending);

fwrite($fp, "\$lineendingdisplay = \"$lineending\";\n");
fwrite($fp, "\$lineending = \"" . stripslashes($lineending) . "\";\n");
fwrite($fp, "\$gendexfile = \"$gendexfile\";\n");
fwrite($fp, "\$mediapath = \"$mediapath\";\n");
fwrite($fp, "\$headstonepath = \"$headstonepath\";\n");
fwrite($fp, "\$historypath = \"$historypath\";\n");
fwrite($fp, "\$backuppath = \"$backuppath\";\n");
fwrite($fp, "\$documentpath = \"$documentpath\";\n");
fwrite($fp, "\$photopath = \"$photopath\";\n");
fwrite($fp, "\$photosext = \"$photosext\";\n");
fwrite($fp, "\$showextended = \"$showextended\";\n");
fwrite($fp, "\$tngconfig['imgmaxh'] = \"$imgmaxh\";\n");
fwrite($fp, "\$tngconfig['imgmaxw'] = \"$imgmaxw\";\n");
fwrite($fp, "\$thumbprefix = \"$thumbprefix\";\n");
fwrite($fp, "\$thumbsuffix = \"$thumbsuffix\";\n");
fwrite($fp, "\$thumbmaxh = \"$thumbmaxh\";\n");
fwrite($fp, "\$thumbmaxw = \"$thumbmaxw\";\n");
fwrite($fp, "\$tngconfig['usedefthumbs'] = \"$tng_usedefthumbs\";\n");
fwrite($fp, "\$tngconfig['thumbcols'] = \"$tng_thumbcols\";\n");
fwrite($fp, "\$tngconfig['maxnoteprev'] = \"$tng_maxnoteprev\";\n");
fwrite($fp, "\$tngconfig['ssdisabled'] = \"$tng_ssdisabled\";\n");
fwrite($fp, "\$tngconfig['ssrepeat'] = \"$tng_ssrepeat\";\n");
fwrite($fp, "\$tngconfig['imgviewer'] = \"$tng_imgviewer\";\n");
fwrite($fp, "\$tngconfig['imgvheight'] = \"$tng_imgvheight\";\n");
fwrite($fp, "\$tngconfig['hidemedia'] = \"$hidemedia\";\n");
fwrite($fp, "\$customheader = \"$customheader\";\n");
fwrite($fp, "\$custommeta = \"$custommeta\";\n");
fwrite($fp, "\$tngconfig['tabs'] = \"$tng_tabs\";\n");
fwrite($fp, "\$tngconfig['menu'] = \"$tng_menu\";\n");
fwrite($fp, "\$tngconfig['istart'] = \"$tng_istart\";\n");
fwrite($fp, "\$tngconfig['showhome'] = \"$showhome\";\n");
fwrite($fp, "\$tngconfig['showsearch'] = \"$showsearch\";\n");
fwrite($fp, "\$tngconfig['searchchoice'] = \"$searchchoice\";\n");
fwrite($fp, "\$tngconfig['showlogin'] = \"$showlogin\";\n");
fwrite($fp, "\$tngconfig['showshare'] = \"$showshare\";\n");
fwrite($fp, "\$tngconfig['showprint'] = \"$showprint\";\n");
fwrite($fp, "\$tngconfig['showbmarks'] = \"$showbmarks\";\n");
fwrite($fp, "\$tngconfig['hidechr'] = \"$hidechr\";\n");
fwrite($fp, "\$tngconfig['password_type'] = \"$password_type\";\n");
fwrite($fp, "\$tngconfig['autogeo'] = \"$autogeo\";\n");

fwrite($fp, "\$dbowner = \"$dbowner\";\n");
fwrite($fp, "\$timeOffset = \"$timeOffset\";\n");
fwrite($fp, "\$tngconfig['edit_timeout'] = \"$edit_timeout\";\n");
fwrite($fp, "\$requirelogin = \"$requirelogin\";\n");
fwrite($fp, "\$livedefault = \"$livedefault\";\n");
fwrite($fp, "\$ldsdefault = \"$ldsdefault\";\n");
fwrite($fp, "\$chooselang = \"$chooselang\";\n");
if (!$chooselang) {
  $session_language = $_SESSION['session_language'] = $language;
  $session_charset = $_SESSION['session_charset'] = $charset;
  setcookie('tnglangfolder', $language, time() + 31536000, '/');
  setcookie('tngcharset', $charset, time() + 31536000, '/');
}
fwrite($fp, "\$nonames = \"$nonames\";\n");
fwrite($fp, "\$tngconfig['nnpriv'] = \"$nnpriv\";\n");
fwrite($fp, "\$notestogether = \"$notestogether\";\n");
fwrite($fp, "\$tngconfig['scrollcite'] = \"$scrollcite\";\n");
fwrite($fp, "\$nameorder = \"$nameorder\";\n");
fwrite($fp, "\$tngconfig['ucsurnames'] = \"$ucsurnames\";\n");
fwrite($fp, "\$lnprefixes = \"$lnprefixes\";\n");
fwrite($fp, "\$lnpfxnum = \"$lnpfxnum\";\n");
fwrite($fp, "\$specpfx = \"" . stripslashes($specpfx) . "\";\n");

fwrite($fp, "\$tngconfig['cemrows'] = \"$cemrows\";\n");
fwrite($fp, "\$tngconfig['cemblanks'] = \"$cemblanks\";\n");

fwrite($fp, "\$emailaddr = \"$emailaddr\";\n");
fwrite($fp, "\$tngconfig['fromadmin'] = \"$fromadmin\";\n");
fwrite($fp, "\$tngconfig['disallowreg'] = \"$disallowreg\";\n");
fwrite($fp, "\$tngconfig['revmail'] = \"$revmail\";\n");
fwrite($fp, "\$tngconfig['autoapp'] = \"$autoapp\";\n");
fwrite($fp, "\$tngconfig['ackemail'] = \"$ackemail\";\n");
fwrite($fp, "\$tngconfig['omitpwd'] = \"$omitpwd\";\n");
fwrite($fp, "\$tngconfig['usesmtp'] = \"$usesmtp\";\n");
fwrite($fp, "\$tngconfig['mailhost'] = \"$mailhost\";\n");
fwrite($fp, "\$tngconfig['mailuser'] = \"$mailuser\";\n");
fwrite($fp, "\$tngconfig['mailpass'] = \"$mailpass\";\n");
fwrite($fp, "\$tngconfig['mailport'] = \"$mailport\";\n");
fwrite($fp, "\$tngconfig['mailenc'] = \"$mailenc\";\n");

fwrite($fp, "\$maxgedcom = \"$maxgedcom\";\n");
fwrite($fp, "\$change_cutoff = \"$change_cutoff\";\n");
fwrite($fp, "\$change_limit = \"$change_limit\";\n");
fwrite($fp, "\$tngconfig['preferEuro'] = \"$prefereuro\";\n");
fwrite($fp, "\$tngconfig['calstart'] = \"$calstart\";\n");
fwrite($fp, "\$tngconfig['pardata'] = \"$pardata\";\n");
fwrite($fp, "\$tngconfig['oldids'] = \"$oldids\";\n");
fwrite($fp, "\$tngconfig['lastimport'] = \"$lastimport\";\n");
fwrite($fp, "\$tng_notinstalled = \"$tng_notinstalled\";\n");
fwrite($fp, "\n");

flock($fp, LOCK_UN);
fclose($fp);

$fp = fopen('subroot.php', 'w', 1);
if ($fp) {
  flock($fp, LOCK_EX);
  fwrite($fp, "<?php\n");
  fwrite($fp, "@ini_set('error_reporting','2039');\n");
  fwrite($fp, "\$tngconfig['subroot'] = \"$newsubroot\";\n");
  fwrite($fp, "\$subroot = \$tngconfig['subroot'] ? \$tngconfig['subroot'] : \"\";\n");
  fwrite($fp, "?>\n");
  flock($fp, LOCK_UN);
  fclose($fp);
}
adminwritelog(uiTextSnippet('modifysettings'));

$oldsubroot = $newsubroot != $subroot ? "?sr=$subroot" : '';
header("Location: admin_setup.php$oldsubroot");