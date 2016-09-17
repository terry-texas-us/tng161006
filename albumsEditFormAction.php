<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowMediaEdit) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
require 'adminlog.php';

$albumname = addslashes($albumname);
$description = addslashes($description);
$keywords = addslashes($keywords);

if (!$alwayson) {
  $alwayson = 0;
}
$query = "UPDATE albums SET albumname=\"$albumname\",description=\"$description\",keywords=\"$keywords\",active=\"$active\",alwayson=\"$alwayson\" WHERE albumID=\"$albumID\"";
$result = tng_query($query);

adminwritelog(uiTextSnippet('modifyalbum') . ": $albumID");

if ($newscreen == 'return') {
  header("Location: albumsEdit.php?albumID=$albumID");
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
    $message = uiTextSnippet('changestoalbum') . " $albumID " . uiTextSnippet('succsaved') . '.';
    header('Location: albumsBrowse.php?message=' . urlencode($message));
  }
}