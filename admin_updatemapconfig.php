<?php

require 'begin.php';
require 'adminlib.php';

if (!count($_POST)) {
  header("Location: admin.php");
  exit;
}
if ($link) {
  $adminLogin = 1;
  include 'checklogin.php';
  include 'version.php';

  if ($assignedtree || !$allowEdit) {
    $message = uiTextSnippet('norights');
    header("Location: admin_login.php?message=" . urlencode($message));
    exit;
  }
}

require 'adminlog.php';

$fp = fopen($subroot . "mapconfig.php", "w", 1);
if (!$fp) {
  die(uiTextSnippet('cannotopen') . " mapconfig.php");
}

flock($fp, LOCK_EX);

fwrite($fp, "<?php\n");
fwrite($fp, "\$map['key'] = \"$mapkey\";\n");
fwrite($fp, "\$map['displaytype'] = \"$maptype\";\n");
fwrite($fp, "\$map['stlat'] = \"$mapstlat\";\n");
fwrite($fp, "\$map['stlong'] = \"$mapstlong\";\n");
fwrite($fp, "\$map['stzoom'] = \"$mapstzoom\";\n");
fwrite($fp, "\$map['foundzoom'] = \"$mapfoundzoom\";\n");
fwrite($fp, "\$map['indw'] = \"$mapindw\";\n");
fwrite($fp, "\$map['indh'] = \"$mapindh\";\n");
fwrite($fp, "\$map['hstw'] = \"$maphstw\";\n");
fwrite($fp, "\$map['hsth'] = \"$maphsth\";\n");
fwrite($fp, "\$map['admw'] = \"$mapadmw\";\n");
fwrite($fp, "\$map['admh'] = \"$mapadmh\";\n");
fwrite($fp, "\$map['startoff'] = \"$startoff\";\n");
fwrite($fp, "\$map['pstartoff'] = \"$pstartoff\";\n");
fwrite($fp, "\$map['showallpins'] = \"$showallpins\";\n");
fwrite($fp, "\$pinplacelevel0 = \"$pinplacelevel0\";\n");
fwrite($fp, "\$pinplacelevel1 = \"$pinplacelevel1\";\n");
fwrite($fp, "\$pinplacelevel2 = \"$pinplacelevel2\";\n");
fwrite($fp, "\$pinplacelevel3 = \"$pinplacelevel3\";\n");
fwrite($fp, "\$pinplacelevel4 = \"$pinplacelevel4\";\n");
fwrite($fp, "\$pinplacelevel5 = \"$pinplacelevel5\";\n");
fwrite($fp, "\$pinplacelevel6 = \"$pinplacelevel6\";\n");
fwrite($fp, "?>\n");

flock($fp, LOCK_UN);
fclose($fp);

adminwritelog(uiTextSnippet('modifymapsettings'));

header("Location: admin_setup.php");
