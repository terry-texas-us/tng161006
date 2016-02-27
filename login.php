<?php

include("begin.php");
$tngconfig['maint'] = "";
include("genlib.php");
include("getlang.php");

include("log.php");

session_start();

$flags['error'] = "";

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('login'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<?php
echo "<body id='public'>\n";
echo $publicHeaderSection->build();

include("loginlib.php");
echo $publicFooterSection->build();
echo scriptsManager::buildScriptElements($flags, 'public');
?>
<script>
  document.form1.tngusername.focus();
</script>
</body>
</html>
