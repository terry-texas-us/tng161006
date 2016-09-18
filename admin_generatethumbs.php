<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
set_time_limit(0);

require 'adminlog.php';

if (!$allowMediaAdd) {
  echo uiTextSnippet('norights');
  exit;
}
header('Content-type:text/html; charset=' . $session_charset);

initMediaTypes();

$thumbquality = 80;
$maxsizeallowed = 10000000; // [ts] 10 Mbytes
if (function_exists(imageJpeg)) {
  include 'imageutils.php';
}
$query = 'SELECT mediaID, path, thumbpath, mediatypeID, usecollfolder, form FROM media where path != ""';
$result = tng_query($query);

$count = 0;
$conflicts = 0;
$conflictstr = '';
$updated = 0;

while ($row = tng_fetch_assoc($result)) {
  $needsupdate = 0;
  $newthumbpath = '';
  $mediatypeID = $row['mediatypeID'];
  $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
  if (!$row['form']) {
    $path = $row['thumbpath'] ? $row['thumbpath'] : $row['path'];
    preg_match('/\.([^.]*?)$/', $path, $matches);
    $ext = strtoupper($matches[1]);
  } else {
    $ext = trim($row['form']);
  }
  if (trim($row['thumbpath']) && !$repath) {
    if ((!$regenerate && file_exists("$rootpath$usefolder/" . $row['thumbpath'])) || !in_array($ext, $imagetypes)) {
      $newthumbpath = '';
    } else {
      $newthumbpath = "$rootpath$usefolder/" . $row['thumbpath'];
    }
  } elseif ($row['path'] && in_array($ext, $imagetypes)) {
    //insert prefix in path directly before file name
    $thumbparts = pathinfo($row['path']);
    $thumbpath = $thumbparts['dirname'];
    if ($thumbpath == '.') {
      $thumbpath = '';
    }
    if ($thumbpath) {
      $thumbpath .= '/';
    }
    $lastperiod = strrpos($thumbparts['basename'], '.');
    $base = substr($thumbparts['basename'], 0, $lastperiod);
    $thumbpath .= $thumbprefix . $base . $thumbsuffix . '.' . $thumbparts['extension'];
    $newthumbpath = "$rootpath$usefolder/$thumbpath";
    if (file_exists($newthumbpath)) {
      $newthumbpath = '';
    }
    $needsupdate = 1;
  }
  if ($newthumbpath) {
    $path = "$rootpath$usefolder/" . trim($row['path']);
    if (file_exists($path)) {
      if (ceil(filesize($path)) > $maxsizeallowed) {
        $needsupdate = 0;
        $conflicts++;
        $conflictstr .= $row['path'] . ' ' . uiTextSnippet('thumbsize') . "<br>\n"; //file is too big
      } else {
        if (function_exists(imageJpeg) && image_createThumb($path, $newthumbpath, $thumbmaxw, $thumbmaxh, $thumbquality)) {
          $destInfo = pathinfo($newthumbpath);
          if (strtoupper($destInfo['extension']) == 'GIF') {
            $thumbpath = substr_replace($thumbpath, 'jpg', -3);
            $newthumbpath = substr_replace($newthumbpath, 'jpg', -3);
          }
          chmod($newthumbpath, 0644);
          $count++;
        } else {
          $needsupdate = 0;
          $conflicts++;
          $conflictstr .= $newthumbpath . ' ' . uiTextSnippet('thumbinv') . "<br>\n"; //thumb couldn't be created
        }
      }
    } else {
      $needsupdate = 0;
      $conflicts++;
      $conflictstr .= $row['path'] . ' ' . uiTextSnippet('thumblost') . "<br>\n"; //original doesn't exist
    }
  }
  if ($needsupdate) {
    $changedate = date('Y-m-d H:i:s', time() + (3600 * $timeOffset));
    $query = "UPDATE media SET thumbpath=\"$thumbpath\", changedate=\"$changedate\", changedby=\"$currentuser\" WHERE mediaID=\"{$row['mediaID']}\"";
    $result2 = tng_query($query);
    $updated++;
  }
}
tng_free_result($result);

adminwritelog(uiTextSnippet('genthumbs') . ': ' . uiTextSnippet('thumbsgenerated') . ": $count; " . uiTextSnippet('recsupdated') . ": $updated; " . uiTextSnippet('thumbconflicts') . ": $conflicts");

echo '<p><strong>' . uiTextSnippet('thumbsgenerated') . ":</strong> $count<br><strong>" . uiTextSnippet('recsupdated') . ":</strong> $updated</p>";
if ($conflicts) {
  echo '<p><strong>' . uiTextSnippet('thumbconflicts') . ":</strong> $conflicts</p><p>$conflictstr</p>";
}