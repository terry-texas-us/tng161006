<?php
include("begin.php");
$tngconfig['maint'] = "";
include("genlib.php");
include("getlang.php");

include("log.php");
require_once 'loginlib.php';

header("Content-type:text/html; charset=" . $session_charset);
?>

<div>
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
</div>