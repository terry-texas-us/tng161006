<?php
/**
 * Name history: admin_updatecemetery.php
 */

require 'begin.php';
require $subroot . 'mapconfig.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
require 'adminlog.php';

if ($newfile && $newfile != 'none') {
  if (substr($maplink, 0, 1) == '/') {
    $maplink = substr($maplink, 1);
  }
  $newpath = "$rootpath$headstonepath/$maplink";

  if (move_uploaded_file($newfile, $newpath)) {
    chmod($newpath, 0644);
  } else {
    $message = uiTextSnippet('mapnotcopied') . " $newpath " . uiTextSnippet('improperpermissions') . '.';
    header('Location: cemeteriesBrowse.php?message=' . urlencode($message));
    exit;
  }
}
$cemname = addslashes($cemname);
$city = addslashes($city);
$county = addslashes($county);
$state = addslashes($state);
$country = addslashes($country);
$zoom = addslashes($zoom);
$notes = addslashes($notes);
$place = addslashes($place);

$latitude = preg_replace('/,/', '.', addslashes($latitude));
$longitude = preg_replace('/,/', '.', addslashes($longitude));

if ($latitude && $longitude && !$zoom) {
  $zoom = 13;
}
if (!$zoom) {
  $zoom = 0;
}
$query = "UPDATE cemeteries SET cemname = '$cemname', maplink = '$maplink', city = '$city', county = '$county', state = '$state', country = '$country', latitude = '$latitude', longitude = '$longitude', zoom = '$zoom', notes = '$notes', place = '$place' WHERE cemeteryID = '$cemeteryID'";
$result = tng_query($query);

$place = trim($place);
if ($place) {
  // First check to see if any place exists with new place name.
  $query = "SELECT * FROM places WHERE place = '$place'";
  $result = tng_query($query);

  if (!tng_num_rows($result)) {
    if (!isset($usecoords)) {
      $latitude = $longitude = '';
      $zoom = 0;
    }
    $query = "INSERT IGNORE INTO places (place, placelevel, latitude, longitude, zoom, notes) VALUES ('$place', '0', '$latitude', '$longitude', '$zoom', '$notes')";
    $result3 = tng_query($query);
  } elseif (isset($usecoords)) {
    $query = "UPDATE places SET latitude = '$latitude', longitude = '$longitude', zoom='$zoom' WHERE place = '$place'";
    $result3 = tng_query($query);
  }
  tng_free_result($result);
}
adminwritelog("<a href=\"cemeteriesEdit.php?cemeteryID=$cemeteryID\">" . uiTextSnippet('modifycemetery') . ": $cemeteryID</a>");

if ($newscreen == 'return') {
  header("Location: cemeteriesEdit.php?cemeteryID=$cemeteryID");
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
    $message = uiTextSnippet('changestocem') . " $cemeteryID " . uiTextSnippet('succsaved') . '.';
    header('Location: cemeteriesBrowse.php?message=' . urlencode($message));
  }
}