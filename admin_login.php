<?php
include("begin.php");
$tngconfig['maint'] = "";
include("adminlib.php");

include("tngmaillib.php");

if ($_SESSION['logged_in'] && $_SESSION['session_rp'] == $rootpath && $_SESSION['allow_admin'] && $currentuser) {
  header("Location: admin.php");
  $reset = 1;
}

if ($message) {
  if (uiTextSnippet($message)) {
    $message = uiTextSnippet($message);
  } elseif (uiTextSnippet($message)) {
    $message = uiTextSnippet($message);
  }
}
if ($email) {

  $sendmail = 0;

  //if username is there too, then look up based on username and get password
  if ($username) {
    $query = "SELECT username, realname FROM $users_table WHERE username = \"$username\"";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    tng_free_result($result);

    $newpassword = generatePassword(0);
    $query = "UPDATE $users_table SET password = \"" . PasswordEncode($newpassword) . "\", password_type = \"" . PasswordType() . "\" WHERE email = \"$email\" AND username = \"$username\" AND allow_living != \"-1\"";
    $result = tng_query($query);
    $success = tng_affected_rows();

    if ($success) {
      $sendmail = 1;
      $content = uiTextSnippet('newpass') . ": $newpassword";
      $message = uiTextSnippet('pwdsent');
    } else {
      $message = uiTextSnippet('loginnotsent3');
    }
  } else {
    $query = "SELECT username, realname FROM $users_table WHERE email = \"$email\"";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    tng_free_result($result);

    if ($row['username']) {
      $sendmail = 1;
      $content = uiTextSnippet('logininfo') . ":\n\n" . uiTextSnippet('username') . ": {$row['username']}";
      $message = uiTextSnippet('usersent');
    } else {
      $message = uiTextSnippet('loginnotsent2');
    }
  }

  if ($sendmail) {
    $mailmessage = $content;
    $owner = preg_replace("/,/", "", ($sitename ? $sitename : ($dbowner ? $dbowner : "TNG")));

    tng_sendmail($owner, $emailaddr, $row['realname'], $email, uiTextSnippet('logininfo'), $mailmessage, $emailaddr, $emailaddr);
  }
}

$home_url = $homepage;

$newroot = preg_replace('/\//', '', $rootpath);
$newroot = str_replace(" ", "", $newroot);
$newroot = preg_replace('/\./', '', $newroot);
$loggedin = "tngloggedin_$newroot";
?>
<!DOCTYPE html>
<html>
<?php
if (!$_SESSION['logged_in'] && $_COOKIE[$loggedin] && !$reset) {
  if (strpos($_SESSION['destinationpage8'], "admin.php") !== false) {
    $continue = "";
  }
  session_start();
  session_unset();
  session_destroy();
  setcookie("tngloggedin_$newroot", "");

  header("Content-type: text/html; charset=" . $session_charset);
  $headSection->setTitle(uiTextSnippet('login'));

  echo $headSection->build('', 'admin', $session_charset);
  $message = uiTextSnippet('sessexp');
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('login'));

echo $headSection->build('', 'admin', $session_charset);

if ($reset) {
  $_COOKIE[$loggedin] = "";
}
?>
<body>
  <section class='container'>
    <table class='table table-sm'>
      <tr>
        <td>
          <h4 class="white small"><?php echo uiTextSnippet('login') . ": " . uiTextSnippet('administration'); ?></h4>
        </td>
      </tr>
      <?php if ($message) { ?>
        <tr>
          <td>
            <span style="color: rgb(255, 0, 0)"><em><?php echo $message; ?></em></span>
          </td>
        </tr>
      <?php } ?>
      <tr>
        <td>
          <div id="admlogintable" style="position:relative">
            <div class="altab" style="float:left">
              <form action="processlogin.php" name="form1" method='post'>
                <table class='table table-sm'>
                  <tr>
                    <td><?php echo uiTextSnippet('username'); ?>:</td>
                    <td><input name='tngusername' type='text'></td>
                  </tr>
                  <tr>
                    <td><?php echo uiTextSnippet('password'); ?>:</td>
                    <td><input name='tngpassword' type="password"></td>
                  </tr>
                  <tr>
                    <td colspan='2'>
                      <input name='remember' type='checkbox' value='1' /> <?php echo uiTextSnippet('rempass'); ?>
                    </td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td><input type='submit' value="<?php echo uiTextSnippet('login'); ?>"/></td>
                  </tr>
                </table>
                <p>
                  <a href="<?php echo $home_url; ?>">
                    <img src='svg/home.svg' width="20" height="20" alt="">
                    <?php echo uiTextSnippet('publichome'); ?>
                  </a>
                </p>
                <input name='admin_login' type='hidden' value='1' />
                <input name='continue' type='hidden' value="<?php echo $continue; ?>" />
              </form>
            </div>
            <div class="altab" style="float:left; width:50px;">&nbsp;&nbsp;&nbsp;</div>
            <div class="altab">
              <form action="admin_login.php" name="form2" method='post'>
                <table class='table table-sm'>
                  <tr>
                    <td colspan='2'><span><?php echo uiTextSnippet('forgot1'); ?></span></td>
                  </tr>
                  <tr>
                    <td><?php echo uiTextSnippet('email'); ?>:</td>
                    <td>
                      <input name='email' type='text'>
                      <input type='submit' value="<?php echo uiTextSnippet('go'); ?>">
                    </td>
                  </tr>
                  <tr>
                    <td colspan='2'><span><br><?php echo uiTextSnippet('forgot2'); ?></span></td>
                  </tr>
                  <tr>
                    <td><?php echo uiTextSnippet('username'); ?>:</td>
                    <td>
                      <input name='username' type='text'>
                      <input type='submit' value="<?php echo uiTextSnippet('go'); ?>">
                    </td>
                  </tr>
                </table>
              </form>
            </div>
          </div>
        </td>
      </tr>
    </table>
  </section> <!-- .container -->
<script>
  document.form1.tngusername.focus();
</script>
</body>
</html>
