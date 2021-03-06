<?php

require 'begin.php';
$tngconfig['maint'] = '';
require 'genlib.php';
require 'getlang.php';

require 'log.php';
require 'loginlib.php';

session_start();

$flags['error'] = '';

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('login'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $sessionCharset); ?>
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
