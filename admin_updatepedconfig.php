<?php

require 'begin.php';
require 'adminlib.php';

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

$fp = fopen($subroot . "pedconfig.php", "w", 1);
if (!$fp) {
  die(uiTextSnippet('cannotopen') . " pedconfig.php");
}

if (!$vwidth) {
  $vwidth = 100;
}
if (!$vheight) {
  $vheight = 42;
}
if (!$vspacing) {
  $vspacing = 20;
}
if (!$vfontsize) {
  $vfontsize = 7;
}

flock($fp, LOCK_EX);

fwrite($fp, "<?php\n");
fwrite($fp, "\$pedigree['leftindent'] = \"$leftindent\";\n");
fwrite($fp, "\$pedigree['boxnamesize'] = \"$boxnamesize\";\n");
fwrite($fp, "\$pedigree['boxdatessize'] = \"$boxdatessize\";\n");
fwrite($fp, "\$pedigree['boxcolor'] = \"$boxcolor\";\n");
fwrite($fp, "\$pedigree['colorshift'] = \"$colorshift\";\n");
fwrite($fp, "\$pedigree['emptycolor'] = \"$emptycolor\";\n");
fwrite($fp, "\$pedigree['hideempty'] = \"$hideempty\";\n");
fwrite($fp, "\$pedigree['bordercolor'] = \"$bordercolor\";\n");
fwrite($fp, "\$pedigree['shadowcolor'] = \"$shadowcolor\";\n");
fwrite($fp, "\$pedigree['boxwidth'] = \"$boxwidth\";\n");
fwrite($fp, "\$pedigree['boxheight'] = \"$boxheight\";\n");
fwrite($fp, "\$pedigree['boxalign'] = \"$boxalign\";\n");
fwrite($fp, "\$pedigree['boxheightshift'] = \"$boxheightshift\";\n");
fwrite($fp, "\$pedigree['vwidth'] = \"$vwidth\";\n");
fwrite($fp, "\$pedigree['vheight'] = \"$vheight\";\n");
fwrite($fp, "\$pedigree['vspacing'] = \"$vspacing\";\n");
fwrite($fp, "\$pedigree['vfontsize'] = \"$vfontsize\";\n");
fwrite($fp, "\$pedigree['boxHsep'] = \"$boxHsep\";\n");
fwrite($fp, "\$pedigree['boxVsep'] = \"$boxVsep\";\n");
fwrite($fp, "\$pedigree['pagesize'] = \"$pagesize\";\n");
fwrite($fp, "\$pedigree['shadowoffset'] = \"$shadowoffset\";\n");
fwrite($fp, "\$pedigree['linewidth'] = \"$linewidth\";\n");
fwrite($fp, "\$pedigree['borderwidth'] = \"$borderwidth\";\n");
fwrite($fp, "\$pedigree['usepopups'] = \"$usepopups\";\n");
fwrite($fp, "\$pedigree['popupcolor'] = \"$popupcolor\";\n");
fwrite($fp, "\$pedigree['popupinfosize'] = \"$popupinfosize\";\n");
fwrite($fp, "\$pedigree['popupspouses'] = \"$popupspouses\";\n");
fwrite($fp, "\$pedigree['popupkids'] = \"$popupkids\";\n");
fwrite($fp, "\$pedigree['popupchartlinks'] = \"$popupchartlinks\";\n");
fwrite($fp, "\$pedigree['popuptimer'] = \"$popuptimer\";\n");
fwrite($fp, "\$pedigree['event'] = \"$pedevent\";\n");
fwrite($fp, "\$pedigree['puboxwidth'] = \"$puboxwidth\";\n");
fwrite($fp, "\$pedigree['puboxheight'] = \"$puboxheight\";\n");
fwrite($fp, "\$pedigree['puboxalign'] = \"$puboxalign\";\n");
fwrite($fp, "\$pedigree['puboxheightshift'] = \"$puboxheightshift\";\n");
fwrite($fp, "\$pedigree['inclphotos'] = \"$inclphotos\";\n");
fwrite($fp, "\$pedigree['maxgen'] = \"$maxgen\";\n");
fwrite($fp, "\$pedigree['initpedgens'] = \"$initpedgens\";\n");
fwrite($fp, "\$pedigree['maxupgen'] = \"$maxupgen\";\n");
fwrite($fp, "\$pedigree['maxrels'] = \"$maxrels\";\n");
fwrite($fp, "\$pedigree['initrels'] = \"$initrels\";\n");

fwrite($fp, "\$pedigree['maxdesc'] = \"$maxdesc\";\n");
fwrite($fp, "\$pedigree['initdescgens'] = \"$initdescgens\";\n");
fwrite($fp, "\$pedigree['defdesc'] = \"$defdesc\";\n");
fwrite($fp, "\$pedigree['stdesc'] = \"$stdesc\";\n");
fwrite($fp, "\$pedigree['regnotes'] = \"$regnotes\";\n");
fwrite($fp, "\$pedigree['regnosp'] = \"$regnosp\";\n");

if (!$tcwidth) {
  $tcwidth = 800;
}
if (!$tcheight) {
  $tcheight = 200;
}
if (!$mpct) {
  $mpct = 0;
}
if (!$ypct) {
  $ypct = 100 - $mpct;
}
if (!$ypixels) {
  $ypixels = 10;
}
if (!$ymult) {
  $ymult = 10;
}
if (!$mpixels) {
  $mpixels = 50;
}
if (!$tcevents) {
  $tcevents = 0;
}

fwrite($fp, "\$pedigree['tcwidth'] = \"$tcwidth\";\n");
fwrite($fp, "\$pedigree['simile'] = \"$simile\";\n");
fwrite($fp, "\$pedigree['tcheight'] = \"$tcheight\";\n");
fwrite($fp, "\$pedigree['ypct'] = \"$ypct\";\n");
fwrite($fp, "\$pedigree['ypixels'] = \"$ypixels\";\n");
fwrite($fp, "\$pedigree['ymult'] = \"$ymult\";\n");
fwrite($fp, "\$pedigree['mpct'] = \"$mpct\";\n");
fwrite($fp, "\$pedigree['mpixels'] = \"$mpixels\";\n");
fwrite($fp, "\$pedigree['tcevents'] = \"$tcevents\";\n");

fwrite($fp, "?>\n");

flock($fp, LOCK_UN);
fclose($fp);

adminwritelog(uiTextSnippet('modifypedsettings'));

header("Location: admin_setup.php");
