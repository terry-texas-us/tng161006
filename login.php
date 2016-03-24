<?php

require 'begin.php';
$tngconfig['maint'] = "";
include("genlib.php");
include("getlang.php");

include("log.php");
include("loginlib.php");

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
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <?php if ($message) { ?>
      <span style="color: red; "><em><?php echo uiTextSnippet($message); ?></em></span>
    <?php } ?>
    <div class='row'>
      <div class='col-md-6'>
        <?php injectLoginForm(); ?>
      </div>
      <div class='col-md-6'>
        <?php injectForgotCredentialsForm(); ?>
      </div>
    </div>
    <?php if (!$tngconfig['disallowreg']) { ?>
      <p><?php echo uiTextSnippet('nologin'); ?> <a href='newacctform.php'><?php echo uiTextSnippet('regnewacct'); ?></a></p>
    <?php } ?>
    <?php // echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
<script src="js/login.js"></script>
<script>
  document.form1.tngusername.focus();
</script>
</body>
</html>
