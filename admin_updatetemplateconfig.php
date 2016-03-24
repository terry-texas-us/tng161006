<?php

require 'begin.php';
include($subroot . "templateconfig.php");
require 'adminlib.php';

if (!count($_POST)) {
  header("Location: admin.php");
  exit;
}
if ($link) {
  $adminLogin = 1;
  require 'checklogin.php';
  include("version.php");

  if ($assignedtree || !$allowEdit) {
    $message = uiTextSnippet('norights');
    header("Location: admin_login.php?message=" . urlencode($message));
    exit;
  }
}
require("adminlog.php");

$fp = fopen($subroot . "templateconfig.php", "w", 1);
if (!$fp) {
  die(uiTextSnippet('cannotopen') . " templateconfig.php");
}
flock($fp, LOCK_EX);

fwrite($fp, "<?php\n");

unset($_POST['form_templatenum']);
unset($_POST['form_templateswitching']);
unset($_POST['save']);

foreach ($_FILES as $key => $file) {
  $newfile = $file['tmp_name'];
  if ($newfile && $newfile != "none") {
    $newkey = substr($key, 7);
    $foldername = is_numeric($form_templatenum) ? "template" . $form_templatenum : $form_templatenum;
    $newpath = $rootpath . "templates/$foldername/" . $_POST['form_' . $newkey];

    if (move_uploaded_file($newfile, $newpath)) {
      chmod($newpath, 0644);
    }
  }
}
$lastkey = "";
$holdarr = array();
foreach ($_POST as $newkey => $newvalue) {
  $newvalue = addslashes($newvalue);

  $newvalue = str_replace("\\'", "'", $newvalue);
  $newvalue = str_replace("\n", "", $newvalue);
  $key = substr($newkey, 5);
  if ($lastkey && strpos($key, $lastkey) === 0) {
    $holdarr[$key] = $newvalue;
  } else {
    if (count($holdarr)) {
      ksort($holdarr);
      foreach ($holdarr as $holdkey => $holdvalue) {
        fwrite($fp, "\$tmp['" . $holdkey . "'] = \"$holdvalue\";\n");
      }
      $holdarr = array();
    }
    fwrite($fp, "\$tmp['" . $key . "'] = \"$newvalue\";\n");
    $lastkey = $key;
  }
}
fwrite($fp, "?>\n");

flock($fp, LOCK_UN);
fclose($fp);

adminwritelog(uiTextSnippet('modifytemplatesettings') . " - " . uiTextSnippet('template') . " " . $form_templatenum . " - " .
        uiTextSnippet('templateswitching') . " = " . $form_templateswitching);

header("Location: admin_setup.php");