<?php

require 'begin.php';
require 'adminlib.php';

if (!count($_POST)) {
  header("Location: admin.php");
  exit;
}
if ($link) {
  include 'checklogin.php';

  if (!$allowEdit) {
    $message = uiTextSnippet('norights');
    header("Location: admin_login.php?message=" . urlencode($message));
    exit;
  }
}
require 'adminlog.php';

$fp = fopen($subroot . "importconfig.php", "w", 1);
if (!$fp) {
  die(uiTextSnippet('cannotopen') . " importconfig.php");
}
$localphotopathdisplay = addslashes($localphotopathdisplay);
$localhistorypathdisplay = addslashes($localhistorypathdisplay);
$localdocumentpathdisplay = addslashes($localdocumentpathdisplay);
$localotherpathdisplay = addslashes($localotherpathdisplay);
$localhspathdisplay = addslashes($localhspathdisplay);

flock($fp, LOCK_EX);

fwrite($fp, "<?php\n");
fwrite($fp, "\$gedpath = \"$gedpath\";\n");
fwrite($fp, "\$saveimport = \"$saveimport\";\n");

fwrite($fp, "\$assignnames = \"$assignnames\";\n");
fwrite($fp, "\$tngimpcfg['defimpopt'] = \"$defimpopt\";\n");
fwrite($fp, "\$tngimpcfg['chdate'] = \"$blankchangedt\";\n");
fwrite($fp, "\$tngimpcfg['livingreqbirth'] = \"$livingreqbirth\";\n");
fwrite($fp, "\$tngimpcfg['maxlivingage'] = \"$maxlivingage\";\n");
fwrite($fp, "\$tngimpcfg['maxprivyrs'] = \"$maxprivyrs\";\n");
fwrite($fp, "\$locimppath['photos'] = \"$localphotopathdisplay\";\n");
fwrite($fp, "\$locimppath['histories'] = \"$localhistorypathdisplay\";\n");
fwrite($fp, "\$locimppath['documents'] = \"$localdocumentpathdisplay\";\n");
fwrite($fp, "\$locimppath['headstones'] = \"$localhspathdisplay\";\n");
fwrite($fp, "\$locimppath['other'] = \"$localotherpathdisplay\";\n");
fwrite($fp, "\$wholepath = \"$wholepath\";\n");
fwrite($fp, "\$tngimpcfg['privnote'] = \"$privnote\";\n");
fwrite($fp, "?>\n");

flock($fp, LOCK_UN);
fclose($fp);

adminwritelog(uiTextSnippet('modifyimportsettings'));

header("Location: admin_setup.php");