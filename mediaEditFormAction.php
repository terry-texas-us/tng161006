<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowMediaEdit && !$allowMediaAdd) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
require 'adminlog.php';
initMediaTypes();

function reorderMedia($query, $plink, $mediatypeID) {
  $ptree = $plink['gedcom'];
  $eventID = $plink['eventID'];
  $result3 = tng_query($query);
  while ($personrow = tng_fetch_assoc($result3)) {
    $query = "SELECT medialinkID FROM (medialinks, media) WHERE personID = \"{$personrow['personID']}\" AND media.mediaID = medialinks.mediaID AND eventID = \"$eventID\" AND mediatypeID = \"$mediatypeID\" ORDER BY ordernum";
    $result4 = tng_query($query);

    $counter = 1;
    while ($medialinkrow = tng_fetch_assoc($result4)) {
      $query = "UPDATE medialinks SET ordernum = \"$counter\" WHERE medialinkID = \"{$medialinkrow['medialinkID']}\"";
      tng_query($query);
      $counter++;
    }
    tng_free_result($result4);
  }
  tng_free_result($result3);
}

$thumbquality = 80;
if (function_exists(imageJpeg)) {
  include 'imageutils.php';
}
$usefolder = $usecollfolder ? $mediatypes_assoc[$mediatypeID] : $mediapath;

if (substr($thumbpath, 0, 1) == '/') {
  $thumbpath = substr($thumbpath, 1);
}
$newthumbpath = "$rootpath$usefolder/$thumbpath";

$description = addslashes($description);
$notes = addslashes($notes);
$datetaken = addslashes($datetaken);
$place = addslashes($place);
$owner = addslashes($owner);
$imagemap = addslashes($imagemap);
$bodytext = addslashes($bodytext);
$zoom = addslashes($zoom);
$width = addslashes($width);
$height = addslashes($height);
$plot = addslashes($plot);

$latitude = preg_replace('/,/', '.', addslashes($latitude));
$longitude = preg_replace('/,/', '.', addslashes($longitude));
$imagemap = trim($imagemap);

if ($latitude && $longitude && !$zoom) {
  $zoom = 13;
}
$fileparts = pathinfo($path);
$form = strtoupper($fileparts['extension']);
$newdate = date('Y-m-d H:i:s', time() + (3600 * $timeOffset));

if ($abspath) {
  $path = $mediaurl;
} else {
  $abspath = 0;
}
if (!$showmap) {
  $showmap = '0';
}
if (!$usenl) {
  $usenl = 0;
}
if (!$alwayson) {
  $alwayson = 0;
}
if (!$newwindow) {
  $newwindow = 0;
}
if (!$usecollfolder) {
  $usecollfolder = 0;
}
if (!$width) {
  $width = 0;
}
if (!$height) {
  $height = 0;
}
if (!$cemeteryID) {
  $cemeteryID = 0;
}
if (!$linktocem) {
  $linktocem = 0;
}
if (!$zoom) {
  $zoom = 0;
}
if ($usecollfolder && $mediatypeID != $mediatypeID_org) {
  $oldmediapath = $mediatypes_assoc[$mediatypeID_org];
  $newmediapath = $mediatypes_assoc[$mediatypeID];
  if ($path_org) {
    $oldpath = "$rootpath$oldmediapath/$path_org";
    $newpath = "$rootpath$newmediapath/$path";
    if (file_exists($oldpath)) {
      rename($oldpath, $newpath);
    }
  }
  if ($thumbpath_org) {
    $oldthumbpath = "$rootpath$oldmediapath/$thumbpath_org";
    $newthumbpath = "$rootpath$newmediapath/$thumbpath";
    if (file_exists($oldthumbpath)) {
      rename($oldthumbpath, $newthumbpath);
    }
  }
}
$mediakey = $path && $path != $path_org ? "$usefolder/$path" : $mediakey_org;
if (!$mediakey) {
  $mediakey = time();
}
if (substr($path, 0, 1) == '/') {
  $path = substr($path, 1);
}
$newpath = "$rootpath$usefolder/$path";

if ($newfile && $newfile != 'none') {
  if (move_uploaded_file($newfile, $newpath)) {
    chmod($newpath, 0644);
  } else {
    //improper permissions or folder doesn't exist (root path may be wrong)
    $message = uiTextSnippet('notcopied') . " $newpath " . uiTextSnippet('improperpermissions') . '.';
    header('Location: mediaBrowse.php?message=' . urlencode($message));
    exit;
  }
}
if (function_exists(imageJpeg) && $thumbcreate == 'auto') {
  if (image_createThumb($newpath, $newthumbpath, $thumbmaxw, $thumbmaxh, $thumbquality)) {
    $destInfo = pathInfo($newthumbpath);
    if (strtoupper($destInfo['extension']) == 'GIF') {
      $thumbpath = substr_replace($thumbpath, 'jpg', -3);
      $newthumbpath = substr_replace($newthumbpath, 'jpg', -3);
    }
    chmod($newthumbpath, 0644);
  } else {
    //could not create thumbnail (size or type problem) or permissions (root path may be wrong)
    $message = uiTextSnippet('thumbnailnotcopied') . " $newthumbpath " . uiTextSnippet('improper2') . '.';
    header('Location: mediaBrowse.php?message=' . urlencode($message));
    exit;
  }
} else {
  if ($newthumb && $newthumb != 'none') {
    if (move_uploaded_file($newthumb, $newthumbpath)) {
      chmod($newthumbpath, 0644);
    } else {
      //improper permissions or folder doesn't exist (root path may be wrong)
      $message = uiTextSnippet('thumbnailnotcopied') . " $newthumbpath " . uiTextSnippet('improperpermissions') . '.';
      header('Location: mediaBrowse.php?message=' . urlencode($message));
      exit;
    }
  }
}
$query = "UPDATE media SET path = '$path', thumbpath = '$thumbpath', description = '$description', notes = '$notes', width = '$width', height = '$height', datetaken = '$datetaken', placetaken = '$place', owner = '$owner', changedate = '$newdate', changedby = '$currentuser', form = '$form', alwayson = '$alwayson', mediatypeID = '$mediatypeID', map = '$imagemap', abspath = '$abspath', gedcom = '', status = '$status', cemeteryID = '$cemeteryID', plot = '$plot', showmap = '$showmap', linktocem = '$linktocem', latitude = '$latitude', longitude= '$longitude', zoom = '$zoom', bodytext = '$bodytext', usenl = '$usenl', newwindow = '$newwindow', usecollfolder = '$usecollfolder', mediakey = '$mediakey'  WHERE mediaID = '$mediaID'";
$result = tng_query($query);

if ($mediatypeID != $mediatypeID_org) {
  $query = "SELECT personID, medialinks.gedcom, eventID FROM (medialinks, media) WHERE medialinks.mediaID = \"$mediaID\" AND medialinks.mediaID = media.mediaID";
  $result2 = tng_query($query);
  if ($result2) {
    while ($plink = tng_fetch_assoc($result2)) {
      $query = "SELECT personID FROM $people_table WHERE personID = \"{$plink['personID']}\"";
      reorderMedia($query, $plink, $mediatypeID_org);
      reorderMedia($query, $plink, $mediatypeID);

      $query = "SELECT familyID AS personID FROM $families_table WHERE familyID = \"{$plink['personID']}\"";
      reorderMedia($query, $plink, $mediatypeID_org);
      reorderMedia($query, $plink, $mediatypeID);

      $query = "SELECT sourceID AS personID FROM sources WHERE sourceID = \"{$plink['personID']}\"";
      reorderMedia($query, $plink, $mediatypeID_org);
      reorderMedia($query, $plink, $mediatypeID);

      $query = "SELECT repoID AS personID FROM repositories WHERE repoID = \"{$plink['personID']}\"";
      reorderMedia($query, $plink, $mediatypeID_org);
      reorderMedia($query, $plink, $mediatypeID);
    }
    tng_free_result($result2);
  }
}
adminwritelog("<a href=\"mediaEdit.php?mediaID=$mediaID\">" . uiTextSnippet('modifymedia') . ": $mediaID</a>");

if ($newmedia == 'return') {
  header("Location: mediaEdit.php?mediaID=$mediaID&cw=$cw");
} else {
  if ($newmedia == 'close') {
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
    $message = uiTextSnippet('changestoitem') . " $mediaID " . uiTextSnippet('succsaved') . '.';
    header('Location: mediaBrowse.php?message=' . urlencode($message));
  }
}