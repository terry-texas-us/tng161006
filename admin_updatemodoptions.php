<?php

include("begin.php");
include("adminlib.php");
include("getlang.php");

if (!count($_POST['options'])) {
  header("Location: admin.php");
  exit;
}
$options = $_POST['options'];

$admin_login = 1;
include("checklogin.php");
include("version.php");

if ($assignedtree || !$allow_edit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

// when saving options revert to sort order specified in options
if (isset($_SESSION['sortby'])) {
  unset($_SESSION['sortby']);
}

require("adminlog.php");
$optionsfile = $subroot . 'mmconfig.php';
if (!is_writeable($optionsfile)) {
  $_SESSION['err_msg'] = uiTextSnippet('checkwrite') . " " . uiTextSnippet('cantwrite') . " $optionsfile !";
  header("Location: admin_modhandler.php"); // restored to new Mod Manager screen KCR 140504
} else {
  //$optionsfile = "classes/mod.class.config.php";

  $optionstring = "<?php";
  foreach ($options as $key => $value) {
    $optionstring .= "\n\$options['$key'] = \"$value\";";
  }
  $optionstring .= "\n?>";
  file_put_contents($optionsfile, $optionstring);

  adminwritelog(uiTextSnippet('modifyoptions'));

  header("Location: admin_modhandler.php"); // restored to new Mod Manager screen KCR 140504
}
