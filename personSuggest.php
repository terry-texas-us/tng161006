<?php
require 'tng_begin.php';
require 'suggest.php';
require 'personlib.php';

$_SESSION['tng_email'] = generatePassword(1);
$_SESSION['tng_comments'] = generatePassword(1);
$_SESSION['tng_yourname'] = generatePassword(1);

$preemail = getCurrentUserEmail($currentuser);

$result = getPersonDataPlusDates($ID);
if ($result) {
  $row = tng_fetch_assoc($result);
  $rights = determineLivingPrivateRights($row);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  $name = getName($row) . " ($ID)";
  tng_free_result($result);
}
$years = getYears($row);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();
header('Content-type: text/html; charset=' . $session_charset);

$headTitle = uiTextSnippet('suggestchange') . ": $name";
$headSection->setTitle($headTitle);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body class='form-suggest'>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();

    $photostr = showSmallPhoto($ID, $name, $row['allow_living'] && $row['allow_private'], 0, false, $row['sex']);
    echo tng_DrawHeading($photostr, $name, $years);

    echo buildPersonMenu('suggest', $ID);
    echo "<br style='clear: both;'>\n";
    echoResponseMessage($message, $sowner, $ssendemail);
    ?>
    <form action='personSuggestFormAction.php' method='post' name='suggest' id='suggest' data-email-control='#email' data-confirm-email-control='#confirm-email'>
      <div class='form-container'>
        <h4 class="form-heading"><?php echo uiTextSnippet('suggestchange'); ?></h4>
        <input name='personID' type='hidden' value="<?php echo $ID; ?>">
        <div class='form-group yourname'>
          <label><?php echo uiTextSnippet('yourname'); ?>:</label>
          <input class='form-control' name="<?php echo $_SESSION['tng_yourname']; ?>" type='text' required>
        </div>  
        <div class='form-group'>
          <label><?php echo uiTextSnippet('email'); ?>:</label>
          <input class='form-control' id='email' name="<?php echo $_SESSION['tng_email']; ?>" type='email' value="<?php echo $preemail; ?>">
          <input class='form-control' id='confirm-email' name='em2' type='email' value="<?php echo $preemail; ?>" placeholder="<?php echo uiTextSnippet('emailagain'); ?>" required>
          <input name='mailme' type='checkbox' value='1'><?php echo uiTextSnippet('mailme'); ?>
        </div>
        <hr>
        <?php echo uiTextSnippet('proposedchanges'); ?>
        <textarea class='form-control' name="<?php echo $_SESSION['tng_comments']; ?>" rows='4' required></textarea>
        <input name='enttype' type='hidden' value="I">
        <input name='ID' type='hidden' value="<?php echo $ID; ?>">
        <br>
        <button class="btn btn-primary btn-block" type="submit"><?php echo uiTextSnippet('submitsugg'); ?></button>
      </div>
    </form>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
<script src='js/suggest.js'></script>
</body>
</html>
