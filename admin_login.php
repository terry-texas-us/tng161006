<?php
require 'begin.php';
$tngconfig['maint'] = '';
require 'adminlib.php';

require 'mail.php';

if ($_SESSION['logged_in'] && $_SESSION['session_rp'] == $rootpath && $_SESSION['allow_admin'] && $currentuser) {
  header('Location: admin.php');
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
    $query = "SELECT username, realname FROM users WHERE username = \"$username\"";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    tng_free_result($result);

    $newpassword = generatePassword(0);
    $query = "UPDATE users SET password = \"" . PasswordEncode($newpassword) . '", password_type = "' . PasswordType() . "\" WHERE email = \"$email\" AND username = \"$username\" AND allow_living != \"-1\"";
    $result = tng_query($query);
    $success = tng_affected_rows();

    if ($success) {
      $sendmail = 1;
      $content = uiTextSnippet('newpass') . ": $newpassword";
      $message = "<div class='alert alert-success' role='alert'>" . uiTextSnippet('pwdsent') . '</div>';
    } else {
      $message = "<div class='alert alert-warning' role='alert'>" . uiTextSnippet('loginnotsent3') . '</div>';
    }
  } else {
    $query = "SELECT username, realname FROM users WHERE email = \"$email\"";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    tng_free_result($result);

    if ($row['username']) {
      $sendmail = 1;
      $content = uiTextSnippet('logininfo') . ":\n\n" . uiTextSnippet('username') . ": {$row['username']}";
      $message = "<div class='alert alert-success' role='alert'>" . uiTextSnippet('usersent') . '</div>';
    } else {
      $message = "<div class='alert alert-warning' role='alert'>" . uiTextSnippet('loginnotsent2') . '</div>';
    }
  }
  if ($sendmail) {
    $mailmessage = $content;
    $owner = preg_replace('/,/', '', ($sitename ? $sitename : ($dbowner ? $dbowner : 'TNG')));

    tng_sendmail($owner, $emailaddr, $row['realname'], $email, uiTextSnippet('logininfo'), $mailmessage, $emailaddr, $emailaddr);
  }
}
$home_url = $homepage;

$newroot = preg_replace('/\//', '', $rootpath);
$newroot = str_replace(' ', '', $newroot);
$newroot = preg_replace('/\./', '', $newroot);
$loggedin = "tngloggedin_$newroot";
?>
<!DOCTYPE html>
<html>
<?php
if (!$_SESSION['logged_in'] && $_COOKIE[$loggedin] && !$reset) {
  if (strpos($_SESSION['destinationpage8'], 'admin.php') !== false) {
    $continue = '';
  }
  session_start();
  session_unset();
  session_destroy();
  setcookie("tngloggedin_$newroot", '');

  header('Content-type: text/html; charset=' . $session_charset);
  $headSection->setTitle(uiTextSnippet('login'));

  echo $headSection->build('', 'admin', $session_charset);
  $message = uiTextSnippet('sessexp');
}
header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('login'));

echo $headSection->build('', 'admin', $session_charset);

if ($reset) {
  $_COOKIE[$loggedin] = '';
}
?>
<body class='admin-login'>
  <section class='container'>
    <p>
      <a href="<?php echo $home_url; ?>">
        <img class='icon-sm' src='svg/home.svg' alt="">
        <?php echo uiTextSnippet('publichome'); ?>
      </a>
    </p>
    <hr>
    <?php if ($message) { ?>
      <span style="color: rgb(255, 0, 0)"><em><?php echo $message; ?></em></span>
    <?php } ?>
    <div class='row'>
      <div class='col-md-6'>
        <form action='processlogin.php' name='form1' method='post'>
          <div class='form-admin-login'>
            <div class='form-admin-login-heading'>
              <h4><?php echo uiTextSnippet('login') . ': ' . uiTextSnippet('administration'); ?></h4>
            </div>
            <?php $label = uiTextSnippet('username'); ?>
            <label class='sr-only' for='tngusername'><?php echo $label; ?></label>
            <input class='form-control' id='username' name='tngusername' type='text' placeholder='<?php echo $label; ?>'>
            <?php $label = uiTextSnippet('password'); ?>
            <label class='sr-only' for='tngpassword'><?php echo $label; ?></label>
            <input class='form-control' name='tngpassword' type="password" placeholder='<?php echo $label; ?>'>
            <div class='checkbox'>
              <label>
                <input name='remember' type='checkbox' value='1'> <?php echo uiTextSnippet('rempass'); ?>
              </label>
            </div>
            <button class='btn btn-primary btn-block' type='submit'><?php echo uiTextSnippet('login'); ?></button>
            <input name='adminLogin' type='hidden' value='1'>
            <input name='continue' type='hidden' value="<?php echo $continue; ?>">
          </div>
        </form>
      </div>
      <div class='col-md-6'>
        <form action="admin_login.php" name="form2" method='post'>
          <?php echo uiTextSnippet('forgot1'); ?>
          <div class="input-group">
            <input class='form-control' name='email' type='text' placeholder='<?php echo uiTextSnippet('email'); ?>'>
            <span class="input-group-btn">
              <button class='btn btn-secondary' type='submit'><?php echo uiTextSnippet('go'); ?></button>
            </span>
          </div>
          <br>
          <?php echo uiTextSnippet('forgot2'); ?>
          <div class='input-group'>
            <input class='form-control' name='username' type='text' placeholder='<?php echo uiTextSnippet('username'); ?>'>
            <span class='input-group-btn'>
              <button class="btn btn-secondary" type="submit"><?php echo uiTextSnippet('go'); ?></button>
            </span>
          </div>
        </form>
      </div>
    </div>
  </section> <!-- .container -->
<script>
  document.form1.tngusername.focus();
</script>
</body>
</html>
