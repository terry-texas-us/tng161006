<?php
require 'begin.php';
require 'adminlib.php';
require 'checklogin.php';

//if no rights, just throw up a message. don't redirect
//remove javascript. put that somewhere global

if (!$currentuser) {
  header('Location: login.php');
  exit;
}
header('Content-type:text/html; charset=' . $session_charset);

$query = "SELECT userID, username, password, realname, phone, email, website, address, city, state, country, zip FROM $users_table WHERE username = \"$currentuser\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

$row['realname'] = preg_replace('/\"/', '&#34;', $row['realname']);
$row['phone'] = preg_replace('/\"/', '&#34;', $row['phone']);
$row['email'] = preg_replace('/\"/', '&#34;', $row['email']);
$row['website'] = preg_replace('/\"/', '&#34;', $row['website']);
$row['address'] = preg_replace('/\"/', '&#34;', $row['address']);
$row['city'] = preg_replace('/\"/', '&#34;', $row['city']);
$row['state'] = preg_replace('/\"/', '&#34;', $row['state']);
$row['country'] = preg_replace('/\"/', '&#34;', $row['country']);

$allow_user_change = true;
?>
<div id='editprof'>
  <form name='editprofile' action='ajx_updateuser.php' method='post'
        onsubmit='if(!this.username.value.length) {
          alert("<?php echo htmlentities(uiTextSnippet('enterusername'), ENT_QUOTES); ?>");
          this.username.focus();
          return false;
        } else if(!this.password.value.length) {
          alert("<?php echo htmlentities(uiTextSnippet('enterpassword'), ENT_QUOTES); ?>");
          this.password.focus();
          return false;
        } else if(!this.password2.value.length) {
          alert("<?php echo htmlentities(uiTextSnippet('enterpassword2'), ENT_QUOTES); ?>");
          this.password2.focus();
          return false;
        } else if(this.password.value != this.password2.value) {
          alert("<?php echo htmlentities(uiTextSnippet('pwdsmatch'), ENT_QUOTES); ?>");
          this.password.focus();
          return false;
        } else if(!this.realname.value.length) {
          alert("<?php echo htmlentities(uiTextSnippet('enterrealname'), ENT_QUOTES); ?>");
          this.realname.focus();
          return false;
        } else if(!this.email.value.length || !checkEmail(this.email.value)) {
          alert("<?php echo htmlentities(uiTextSnippet('enteremail'), ENT_QUOTES); ?>");
          this.email.focus();
          return false;
        } else if(this.em2.value.length == 0) {
          alert("<?php echo htmlentities(uiTextSnippet('enteremail2'), ENT_QUOTES); ?>");
          this.em2.focus();
          return false;
        } else if(this.email.value != this.em2.value) {
          alert("<?php echo htmlentities(uiTextSnippet('emailsmatch'), ENT_QUOTES); ?>");
          this.em2.focus();
          return false;
        }
        if(!newuserok) {
          return checkNewUser(document.editprofile.username,document.editprofile.orguser,true);
        }'>
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('editprofile'); ?></h4>
    </header>
    <div class='modal-body'>
      <table class='table table-sm'>
        <tr>
          <td>
            <?php echo uiTextSnippet('username');
            if ($allow_user_change) {
              echo '*';
            } ?>:
          </td>
          <td>
            <?php if ($allow_user_change) { ?>
              <input name='username' type='text' maxlength='100' value="<?php echo $row['username']; ?>"
                     onblur="checkNewUser(this,document.editprofile.orguser);">
              <span id="checkmsg"></span>
            <?php
            } else {
              echo '<strong>' . $row['username'] . "</strong>\n";
              ?>
              <input name='username' type='hidden' value="<?php echo $row['username']; ?>">
            <?php } ?>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('password'); ?>*:</td>
          <td><input name='password' type='password' maxlength="100" value="<?php echo $row['password']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('pwdagain'); ?>*:</td>
          <td><input name='password2' type='password' maxlength='100' value="<?php echo $row['password']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('realname'); ?>*:</td>
          <td><input name='realname' type='text' size='50' maxlength='50' value="<?php echo $row['realname']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('phone'); ?>:</td>
          <td><input name='phone' type='text' size='30' maxlength='30' value="<?php echo $row['phone']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('email'); ?>*:</td>
          <td><input name='email' type='text' size='50' maxlength='100' value="<?php echo $row['email']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('emailagain'); ?>*:</td>
          <td><input name='em2' type='text' size='50' maxlength='100' value="<?php echo $row['email']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('website'); ?>:</td>
          <td><input name='website' type='text' size='50' maxlength='128' value="<?php echo $row['website']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('address'); ?>:</td>
          <td><input name='address' type='text' size='50' maxlength='100' value="<?php echo $row['address']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('city'); ?>:</td>
          <td><input name='city' type='text' size='50' maxlength='64' value="<?php echo $row['city']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('stateprov'); ?>:</td>
          <td><input name='state' type='text' size='50' maxlength='64' value="<?php echo $row['state']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('zip'); ?>:</td>
          <td><input name='zip' type='text' maxlength='10' value="<?php echo $row['zip']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('country'); ?>:</td>
          <td><input name='country' type='text' size='50' maxlength='64' value="<?php echo $row['country']; ?>"></td>
        </tr>
      </table>
      <br>
      <p>*<?php echo uiTextSnippet('required'); ?></p>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <input name='userID' type='hidden' value="<?php echo $row['userID']; ?>">
      <input name='orguser' type='hidden' value="<?php echo $row['username']; ?>">
      <input name='orgpwd' type='hidden' value="<?php echo $row['password']; ?>">
      <input name='ajax' type='hidden' value='1'>
      <input id='saveprofile' name='saveprofile' type='submit' value="<?php echo uiTextSnippet('savechanges'); ?>">
    </footer>
  </form>
</div>