<?php
include("begin.php");
$tngconfig['maint'] = "";
include("genlib.php");
include("getlang.php");

$maintenance_mode = true;
include("checklogin.php");

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('sitemaint'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<?php
echo "<body id='public'>\n";
echo $publicHeaderSection->build();
?>
<h2><?php echo uiTextSnippet('sitemaint'); ?></h2>
<br clear='all'>
<?php

echo "<p>" . uiTextSnippet('standby') . "</p><br><br>";

echo $publicFooterSection->build();
echo scriptsManager::buildScriptElements($flags, 'public');
?>
</body>
</html>