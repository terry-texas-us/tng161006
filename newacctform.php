<?php
include("begin.php");

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
<body id='public'>
<?php echo $publicHeaderSection->build(); ?>
<h2><img class='icon-md' src='svg/lock-open.svg' alt=""><?php echo uiTextSnippet('regnewacct'); ?></h2>
<br clear='left'>
<?php

if (!$tngconfig['disallowreg']) {
  echo "<p><strong>*" . uiTextSnippet('required') . "</strong></p>\n";
  ?>
  <table>
    <tr>
      <td>
        <?php
        $onsubmit = $ucount ? "return validateForm(this);" : "alert('" . uiTextSnippet('nousers') . "');return false;";
        beginFormElement("addnewacct", "post", "form1", "", $onsubmit);
        ?>
        <table>
          <tr>
            <td><?php echo uiTextSnippet('username'); ?>*:</td>
            <td><input name='username' type='text' maxlength='20'></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('password'); ?>*:</td>
            <td><input name='password' type='password' maxlength='20'></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('pwdagain'); ?>*:</td>
            <td><input name='password2' type='password' maxlength='20'></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('realname'); ?>*:</td>
            <td><input name='realname' type='text' size='50' maxlength='50'/></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('phone'); ?>:</td>
            <td><input name='phone' type='text' size='30' maxlength='30'/></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('email'); ?>*:</td>
            <td><input name="<?php echo $_SESSION['tng_email']; ?>" type='text' size='50' maxlength='100'/>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('emailagain'); ?>*:</td>
            <td><input name='em2' type='text' size='50' maxlength='100'/></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('website'); ?>:</td>
            <td><input name='website' type='text' size='50' maxlength='100' value="http://"/></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('address'); ?>:</td>
            <td><input name='address' type='text' size='50' maxlength='100'/></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('city'); ?>:</td>
            <td><input name='city' type='text' size='50' maxlength='64'/></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('state'); ?>:</td>
            <td><input name='state' type='text' size='50' maxlength='64'/></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('zip'); ?>:</td>
            <td><input name='zip' type='text' maxlength='10'></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('country'); ?>:</td>
            <td><input name='country' type='text' size='50' maxlength='64'/></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('acctcomments'); ?>:</td>
            <td><textarea cols="50" rows="4" name="notes"></textarea></td>
          </tr>
        </table>
        <p><?php echo uiTextSnippet('accmail'); ?></p>
        <br>
        <input name="fingerprint" type='hidden' value='realperson'>
        <input name='submit' type='submit' value="<?php echo uiTextSnippet('submit'); ?>"/>
        <?php endFormElement(); ?>
        <br>
      </td>
    </tr>
  </table>
  <?php
} else {
  echo "<p>" . uiTextSnippet('noregs') . "</p>\n";
}
echo $publicFooterSection->build();
echo scriptsManager::buildScriptElements($flags, 'public');
?>
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
