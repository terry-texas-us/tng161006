<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowMediaAdd) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}

require 'adminlog.php';
initMediaTypes();

$exptime = 0;
setcookie('lastcoll', $mediatypeID, $exptime);

$thumbquality = 80;
if (function_exists('imageJpeg')) {
  include 'imageutils.php';
}

$path = stripslashes($path);
$thumbpath = stripslashes($thumbpath);
if (substr($path, 0, 1) == '/') {
  $path = substr($path, 1);
}

$usefolder = $usecollfolder ? $mediatypes_assoc[$mediatypeID] : $mediapath;
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

if (substr($thumbpath, 0, 1) == '/') {
  $thumbpath = substr($thumbpath, 1);
}
$newthumbpath = "$rootpath$usefolder/$thumbpath";

if (function_exists('imageJpeg') && $thumbcreate == 'auto') {
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
$description = addslashes($description);
$notes = addslashes($notes);
$datetaken = addslashes($datetaken);
$owner = addslashes($owner);
$status = addslashes($status);
$bodytext = addslashes($bodytext);
$width = addslashes($width);
$height = addslashes($height);
$plot = addslashes($plot);

if ($latitude && $longitude && !$zoom) {
  $zoom = 13;
}
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

$fileparts = pathinfo($path);
$form = strtoupper($fileparts['extension']);
$newdate = date('Y-m-d H:i:s', time() + (3600 * $timeOffset));
$mediakey = $path ? "$usefolder/$path" : time();
$query = "INSERT IGNORE INTO media (mediatypeID, mediakey, path, thumbpath, description, notes, width, height, datetaken, placetaken, owner, changedate, changedby, form, alwayson, map, abspath, status, cemeteryID, plot, showmap, linktocem, latitude, longitude, zoom, bodytext, usenl, newwindow, usecollfolder) VALUES ('$mediatypeID', '$mediakey', '$path', '$thumbpath', '$description', '$notes', '$width', '$height', '$datetaken', '$placetaken', '$owner', '$newdate', '$currentuser', '$form', '$alwayson', '$imagemap', '$abspath', '$status', '$cemeteryID', '$plot', '$showmap', '$linktocem', '$latitude', '$longitude', '$zoom', '$bodytext', '$usenl', '$newwindow', '$usecollfolder')";
$result = tng_query($query);
$success = tng_affected_rows();
if ($result && $success) {
  $mediaID = tng_insert_id();

  if ($link_personID) {
    $query = "SELECT count(medialinkID) AS count FROM medialinks WHERE personID = '$link_personID'";
    $result = tng_query($query);
    if ($result) {
      $row = tng_fetch_assoc($result);
      $newrow = $row['count'] + 1;
      tng_free_result($result);
    } else {
      $newrow = 1;
    }

    $defval = '';

    $query = "INSERT IGNORE INTO medialinks (personID, mediaID, ordernum, linktype, eventID, defphoto) VALUES ('$link_personID', '$mediaID', '$newrow', '$link_linktype', '', '$defval')";
    $result = tng_query($query);
  }
  $query = "UPDATE mediatypes SET disabled=\"0\" WHERE mediatypeID = '$mediatypeID'";
  $result = tng_query($query);

  adminwritelog("<a href=\"mediaEdit.php?mediaID=$mediaID\">" . uiTextSnippet('addnewmedia') . ": $mediaID</a>");

  header("Location: mediaEdit.php?mediaID=$mediaID&newmedia=1&added=1");
} else {
  $message = uiTextSnippet('photonotadded') . '.';
  header('Location: mediaBrowse.php?message=' . urlencode($message));
}