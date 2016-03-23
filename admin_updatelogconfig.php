<?php

include("begin.php");
include("adminlib.php");

if (!count($_POST)) {
  header("Location: admin.php");
  exit;
}

if ($link) {
  $admin_login = 1;
  include("checklogin.php");
  include("version.php");

  if ($assignedtree || !$allow_edit) {
    $message = uiTextSnippet('norights');
    header("Location: admin_login.php?message=" . urlencode($message));
    exit;
  }
}

require("adminlog.php");

$fp = fopen($subroot . "logconfig.php", "w", 1);
if (!$fp) {
  die(uiTextSnippet('cannotopen') . " logconfig.php");
}

flock($fp, LOCK_EX);

fwrite($fp, "<?php\n");
fwrite($fp, "\$logname = \"$logname\";\n");
fwrite($fp, "\$logfile = \$rootpath . \$logname;\n");
fwrite($fp, "\$maxloglines = \"$maxloglines\";\n");
fwrite($fp, "\$badhosts = \"$badhosts\";\n");
fwrite($fp, "\$exusers = \"$exusers\";\n");
fwrite($fp, "\$adminlogfile = \"$adminlogfile\";\n");
fwrite($fp, "\$adminmaxloglines = \"$adminmaxloglines\";\n");
fwrite($fp, "\$addr_exclude = \"$addr_exclude\";\n");
fwrite($fp, "\$msg_exclude = \"$msg_exclude\";\n");
fwrite($fp, "?>\n");

flock($fp, LOCK_UN);
fclose($fp);

adminwritelog(uiTextSnippet('modifylogsettings'));

header("Location: admin_setup.php");
