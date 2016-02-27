<?php

include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if (!$allow_media_add) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
require("adminlog.php");

initMediaTypes();

function getMediaFolder($usecollfolder, $mediatypeID) {
  global $mediatypes_assoc, $mediapath;

  return $usecollfolder ? $mediatypes_assoc[$mediatypeID] : $mediapath;
}

$lastslash = strrpos($thumbprefix, '/');
if ($lastslash !== false) {
  $thumb_folder = substr($thumbprefix, 0, $lastslash);
  $thumb_prefix = substr($thumbprefix, $lastslash + 1);
} else {
  $thumb_folder = "";
  $thumb_prefix = $thumbprefix;
}
$endslash = strpos($tngdomain, strlen($tngdomain)) == "/" ? "" : "/";
$mediafolder = $mediatypes_assoc[$mediatypeID];
if ($folder) {
  $mediafolder .= "/$folder";
}

/* [ts] construct using options which are part of the application environment
        this will alter the default behaviour of the sample handler from blueimp */

$options = array(
        'mediapath' => $rootpath . $endrootpath . $mediafolder . "/",
        'mediaurl' => $tngdomain . $endslash . $mediafolder . "/",
        'thumb_folder' => $thumb_folder,
        'thumb_prefix' => $thumb_prefix,
        'thumb_suffix' => $thumbsuffix,
        'thumb_maxwidth' => $thumbmaxw,
        'thumb_maxheight' => $thumbmaxh,
        'tree' => $tree, // [ts] undefined .. is this ok?
        'media_table' => $media_table,
        'medialinks_table' => $medialinks_table,
        'mediatypes_table' => $mediatypes_table,
        'currentuser' => $currentuser,
        'time_offset' => $time_offset,
        'mediatypeID' => $mediatypeID,
        'media_folder' => $mediafolder,
        'subfolder' => $folder,
        'added' => uiTextSnippet('addnewmedia')
);

error_reporting(E_ALL | E_STRICT);

require_once './classes/tsUploadHandler.php';

$upload_handler = new tsUploadHandler($options);