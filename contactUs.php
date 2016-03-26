<?php
require 'tng_begin.php';
require 'suggest.php';

$_SESSION['tng_email'] = generatePassword(1);
$_SESSION['tng_comments'] = generatePassword(1);
$_SESSION['tng_yourname'] = generatePassword(1);

$righttree = checktree($tree);
$preemail = getCurrentUserEmail($currentuser, $users_table);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();
header("Content-type: text/html; charset=" . $session_charset);

$headTitle = uiTextSnippet('contactus');
$headSection->setTitle($headTitle);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body class='form-suggest'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/mail.svg'><?php echo $headTitle; ?></h2>
    <br clear='left'>
    <?php echoResponseMessage($message, $sowner, $ssendemail); ?>
    <form action='contactUsFormAction.php' method='post' name='suggest' id='suggest' data-email-control='#email' data-confirm-email-control='#confirm-email'>
      <div class='form-container'>
        <div class='form-group yourname'>
          <label><?php echo uiTextSnippet('yourname'); ?>:</label>
          <input class='form-control' name="<?php echo $_SESSION['tng_yourname']; ?>" type='text' required>
        </div>  
        <div class='form-group'>
          <label><?php echo uiTextSnippet('email'); ?>:</label>
          <input class='form-control' id='email' name="<?php echo $_SESSION['tng_email']; ?>" type='email' value="<?php echo $preemail; ?>" required>
          <input class='form-control' id='confirm-email' name='em2' type='email' value="<?php echo $preemail; ?>" placeholder="<?php echo uiTextSnippet('emailagain'); ?>" required>
          <input name='mailme' type='checkbox' value='1'><?php echo uiTextSnippet('mailme'); ?>
        </div>
        <label><?php echo uiTextSnippet('subject'); ?>:</label>
        <div><?php echo stripslashes($page); ?></div>
        <hr>
        <?php echo uiTextSnippet('yourcomments'); ?>
        <textarea class='form-control' name="<?php echo $_SESSION['tng_comments']; ?>" rows='4' required></textarea>
        <input name='tree' type='hidden' value="<?php echo $tree; ?>">
        <input name='page' type='hidden' value="<?php echo $page; ?>">
        <br>
        <button class="btn btn-primary btn-block" type="submit"><?php echo uiTextSnippet('sendmsg'); ?></button>
      </div>
    </form>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
<script src='js/suggest.js'></script>
</body>
</html>
