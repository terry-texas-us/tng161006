<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
require 'adminlog.php';

$reponame = addslashes($reponame);
$address1 = addslashes($address1);
$address2 = addslashes($address2);
$city = addslashes($city);
$state = addslashes($state);
$zip = addslashes($zip);
$country = addslashes($country);
$phone = addslashes($phone);
$email = addslashes($email);
$www = addslashes($www);

$newdate = date('Y-m-d H:i:s', time() + (3600 * $timeOffset));

if ($addressID) {
  $query = "UPDATE addresses SET address1=\"$address1\", address2=\"$address2\", city=\"$city\", state=\"$state\", zip=\"$zip\", country=\"$country\", phone=\"$phone\", email=\"$email\", www=\"$www\" WHERE addressID = \"$addressID\"";
  $result = tng_query($query);
} elseif ($address1 || $address2 || $city || $state || $zip || $country || $phone || $email || $www) {
  $query = "INSERT INTO addresses (address1, address2, city, state, zip, country, phone, email, www) VALUES('$address1', '$address2', '$city', '$state', '$zip', '$country', '$phone', '$email', '$www')";
  $result = tng_query($query);
  $addressID = tng_insert_id();
}
$query = "UPDATE repositories SET reponame=\"$reponame\",addressID=\"$addressID\",changedate=\"$newdate\",changedby=\"$currentuser\" WHERE repoID = '$repoID'";
$result = tng_query($query);

adminwritelog("<a href=\"editrepo.php?repoID=$repoID\">" . uiTextSnippet('modifyrepo') . ": $repoID</a>");

if ($newscreen == 'return') {
  header("Location: repositoriesEdit.php?repoID=$repoID");
} else {
  if ($newscreen == 'close') {
  ?>
    <!DOCTYPE html>
    <html>
    <body>
      <script>
        top.close();
      </script>
    </body>
    </html>
  <?php
  } else {
    $message = uiTextSnippet('changestorepo') . " $repoID " . uiTextSnippet('succsaved') . '.';
    header('Location: repositoriesBrowse.php?message=' . urlencode($message));
  }
}
