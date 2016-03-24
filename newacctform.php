<?php
require 'begin.php';

include("genlib.php");
include("getlang.php");

$query = "SELECT gedcom, treename FROM $trees_table ORDER BY treename";
$treeresult = tng_query($query);
$numtrees = tng_num_rows($treeresult);

$query = "SELECT count(userID) as ucount FROM $users_table";
$userresult = tng_query($query);
$row = tng_fetch_assoc($userresult);
$ucount = $row['ucount'];
tng_free_result($userresult);

$_SESSION['tng_email'] = generatePassword(1);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('regnewacct'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body class='form-newaccount'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/lock-open.svg' alt=""><?php echo uiTextSnippet('regnewacct'); ?></h2>
    <?php if (!$tngconfig['disallowreg']) { ?>
      <?php $onsubmit = $ucount ? "return validateForm(this);" : "alert('" . uiTextSnippet('nousers') . "'); return false;"; ?>
      <form action="addnewacct.php" method="post" name="form1" onsubmit="<?php echo $onsubmit; ?>">
        <div class='form-container'>
          <div class='form-group row'>
            <label class='form-control-label col-sm-3'><?php echo uiTextSnippet('username'); ?>*:</label>
            <div class='col-sm-9'>
              <input class='form-control' name='username' type='text' maxlength='20'>
            </div>
          </div>
          <div class='form-group row'>
            <label class='form-control-label col-sm-3'><?php echo uiTextSnippet('password'); ?>*:</label>
            <div class='col-sm-9'>
              <input class='form-control' name='password' type='password' maxlength='20'>
              <input class='form-control' name='password2' type='password' maxlength='20' placeholder='<?php echo uiTextSnippet('pwdagain'); ?>'>
            </div>
          </div>
          <div class='form-group row'>
            <label class='form-control-label col-sm-3'><?php echo uiTextSnippet('realname'); ?>*:</label>
            <div class='col-sm-9'>
              <input class='form-control' name='realname' type='text' maxlength='50'>
            </div>
          </div> 
          <div class='form-group row'>
            <label class='form-control-label col-sm-3'><?php echo uiTextSnippet('email'); ?>*:</label>
            <div class='col-sm-9'>
              <input class='form-control' id='session-email' name="<?php echo $_SESSION['tng_email']; ?>" type='text' maxlength='100'>
              <input class='form-control' name='em2' type='text' maxlength='100' placeholder='<?php echo uiTextSnippet('emailagain'); ?>'>
            </div>
          </div>
          <hr>
          <div class='form-group row'>
            <label class='form-control-label col-sm-3'><?php echo uiTextSnippet('phone'); ?>:</label>
            <div class='col-sm-9'>
              <input class='form-control' name='phone' type='text' maxlength='30'>
            </div>
          </div>
          <div class='form-group row'>
            <label class='form-control-label col-sm-3'><?php echo uiTextSnippet('website'); ?>:</label>
            <div class='col-sm-9'>
              <input class='form-control' name='website' type='text' maxlength='100' value="http://">
            </div>
          </div>
          <div class='form-group row'>
            <label class='form-control-label col-sm-3'><?php echo uiTextSnippet('address'); ?>:</label>
            <div class='col-sm-9'>
              <input class='form-control' name='address' type='text' maxlength='100'>
            </div>
          </div>
          <div class='form-group row'>
            <label class='form-control-label col-sm-3'><?php echo uiTextSnippet('city'); ?>:</label>
            <div class='col-sm-9'>
              <input class='form-control' name='city' type='text' maxlength='64'>
            </div>
          </div>
          <div class='form-group row'>
            <label class='form-control-label col-sm-3'><?php echo uiTextSnippet('stateprov'); ?>:</label>
            <div class='col-sm-9'>
              <input class='form-control' name='state' type='text' maxlength='64'>
            </div>
          </div>
          <div class='form-group row'>
            <label class='form-control-label col-sm-3'><?php echo uiTextSnippet('zip'); ?>:</label>
            <div class='col-sm-9'>
              <input class='form-control' name='zip' type='text' maxlength='10'>
            </div>
          </div>
          <div class='form-group row'>
            <label class='form-control-label col-sm-3'><?php echo uiTextSnippet('country'); ?>:</label>
            <div class='col-sm-9'>
              <input class='form-control' name='country' type='text' maxlength='64'>
            </div>
          </div>
          <div class='form-group row'>
            <label class='form-control-label col-sm-12'><?php echo uiTextSnippet('acctcomments'); ?>:</label>
            <div class='col-sm-12'>
              <textarea class='form-control' rows="4" name="notes"></textarea>
            </div>
          </div>
<!--
          <p><?php echo uiTextSnippet('accmail'); ?></p>
-->
          <input name="fingerprint" type='hidden' value='realperson'>
          <button class="btn btn-primary btn-block" type="submit"><?php echo uiTextSnippet('submit'); ?></button>
        </div>
      </form>
    <?php } else { ?>
      <p><?php echo uiTextSnippet('noregs'); ?></p>
    <?php } ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
<script>
  function validateForm(form) {
    var rval = true;
    if (form.username.value.length === 0) {
      alert(textSnippet('enterusername'));
      rval = false;
    } else if (form.password.value.length === 0) {
      alert(textSnippet('enterpassword'));
      rval = false;
    } else if (form.password2.value.length === 0) {
      alert(textSnippet('enterpassword2'));
      rval = false;
    } else if (form.password.value !== form.password2.value) {
      alert(textSnippet('pwdsmatch'));
      rval = false;
    } else if (form.realname.value.length === 0) {
      alert(textSnippet('enterrealname'));
      rval = false;
    } else if (form.<?php echo $_SESSION['tng_email']; ?> . value.length === 0 || !checkEmail(form . <?php echo $_SESSION['tng_email']; ?> . value)) {
      alert(textSnippet('enteremail'));
      rval = false;
    } 
    else
    if (form.em2.value.length === 0) {
      alert(textSnippet('enteremail2'));
      rval = false;
    } else if (form.<?php echo $_SESSION['tng_email']; ?> . value !== form.em2.value) {
      alert(textSnippet('emailsmatch'));
      rval = false;
    }
    return rval;
  }
</script>
</body>
</html>
