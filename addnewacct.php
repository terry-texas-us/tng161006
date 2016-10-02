<?php
require 'begin.php';
require 'genlib.php';
$deftext = $text;
require 'getlang.php';

require $subroot . 'logconfig.php';
require 'mail.php';
require 'suggest.php';

$valid_user_agent = isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] != '';

$emailfield = $_SESSION['tng_email'];
if (!$emailfield) {
  header('location:newacctform.php');
  exit;
}
eval("\$email = \$$emailfield;");
$_SESSION['tng_email'] = '';

if (preg_match("/\n[[:space:]]*(to|bcc|cc|boundary)[[:space:]]*[:|=].*@/i", $email) || !$valid_user_agent) {
  die('sorry!');
}
if (preg_match("/\r/i", $email) || preg_match("/\n/i", $email) || !preg_match('/@/i', $email)) {
  die('sorry!');
}
if (preg_match("/\n[[:space:]]*(to|bcc|cc|boundary)[[:space:]]*[:|=].*@/i", $username) || !$valid_user_agent) {
  die('sorry!');
}
if (preg_match("/\n/i", $username)) {
  die('sorry!');
}

$realname = strtok($realname, ',;');
if (strpos($email, ',') !== false || strpos($email, ';') !== false || !$email) {
  die('sorry!');
}

killBlockedAddress($email);
if ($msg_exclude) {
  $bad_msgs = explode(',', $msg_exclude);
  foreach ($bad_msgs as $bad_msg) {
    if ($bad_msg) {
      if (strstr($username, trim($bad_msg)) || strstr($password, trim($bad_msg)) || strstr($realname, trim($bad_msg)) || strstr($notes, trim($bad_msg))) {
        die('sorry');
      }
    }
  }
}
$username = addslashes($username);
$password = addslashes($password);
$realname = addslashes($realname);
$phone = addslashes($phone);
$email = addslashes($email);
$website = addslashes($website);
$address = addslashes($address);
$city = addslashes($city);
$state = addslashes($state);
$zip = addslashes($zip);
$country = addslashes($country);
$notes = addslashes($notes);

$username = trim($username);
$password = trim($password);
$realname = trim($realname);
$email = trim($email);
$today = date('Y-m-d H:i:s', time() + (3600 * $timeOffset));

$gedcom = '';

if ($username && $password && $realname && $email && $fingerprint == 'realperson') {
  if ($tngconfig['autoapp']) {
    $allow_living_val = 0;
    $moreinfo = $deftext['accactive'];
    $org_password = $password;
    $password = PasswordEncode($password);
  } else {
    $allow_living_val = -1;
    $moreinfo = $deftext['accinactive'];
  }
  $password_type = PasswordType();
  $query = "INSERT IGNORE INTO users (description, username, password, password_type, realname, phone, email, website, address, city, state, zip, country, notes, role, allow_living, dt_registered) VALUES ('$realname', '$username', '$password', '$password_type', '$realname', '$phone', '$email', '$website', '$address', '$city', '$state', '$zip', '$country', '$notes', 'guest', '$allow_living_val', '$today')";
  $result = tng_query($query);
  $success = tng_affected_rows();
} else {
  $success = 0;
}

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('regnewacct'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $sessionCharset); ?>
<?php
echo "<body id='public'>\n";
echo $publicHeaderSection->build();

echo "<p class='header'>" . uiTextSnippet('regnewacct') . "</span></p><br>\n";
echo "<span>\n";
if ($success) {
  echo '<p>' . uiTextSnippet('successfulregistration') . '</p>';
  if ($emailaddr) {
    $emailtouse = $tngconfig['fromadmin'] == 1 ? $emailaddr : $email;
    $message = "{$deftext['name']}: $realname\n{$deftext['username']}: $username\n\n{$deftext['emailmsg']} $moreinfo\n\n" . uiTextSnippet('administration') . ": $tngdomain/admin.php";
    $owner = preg_replace('/,/', '', ($sitename ? $sitename : ($dbowner ? $dbowner : 'TNG')));
    tng_sendmail($owner, $emailtouse, $dbowner, $emailaddr, $deftext['emailsubject'], $message, $emailaddr, $email);

    $welcome = '';
    if ($tngconfig['autoapp']) {
      //send email to user saying they're ready to go
      //include password if that feature not turned off
      $welcome = uiTextSnippet('hello') . " $realname,\r\n\r\n" . uiTextSnippet('activated');
      if (!$tngconfig['omitpwd']) {
        $welcome .= uiTextSnippet('password') . ": $org_password\r\n";
      }
      $welcome .= "\r\n$dbowner\r\n$tngdomain";
      $subject = uiTextSnippet('subjectline');
    } elseif ($tngconfig['ackemail']) {
      //send email to user saying that we're working on it
      $welcome = uiTextSnippet('hello') . " $realname,\r\n\r\n" . uiTextSnippet('ackmessage') . "\r\n$dbowner\r\n$tngdomain";
      $subject = uiTextSnippet('acksubject');
    }
    if ($welcome) {
      tng_sendmail($owner, $emailaddr, $realname, $email, $subject, $welcome, $emailaddr, $emailaddr);
    }
  }
} else {
  echo '<p>' . uiTextSnippet('failure') . '</p>';
}
echo '</span>';

echo $publicFooterSection->build();
echo scriptsManager::buildScriptElements($flags, 'public');
?>
</body>
</html>