<?php

global $photopath;
global $documentpath;
global $historypath;
global $headstonepath;
global $mediapath;

$mediatypes = [];
$mediatypes_assoc = [];
$mediatypes_icons = [];
$mediatypes_thumbs = [];
$mediatypes_display = [];
$mediatypes_like = [];
$mediatypeObjs = [];
$mctr = 0;
$maxmediafilesize = 5000000; //5 Mb is too large to create a thumbnail

function setMediaType($newtype) {
  global $mediatypes;
  global $mediatypes_assoc;
  global $mediatypes_icons;
  global $mediatypes_thumbs;
  global $mediatypes_display;
  global $mediatypes_like;
  global $mediatypeObjs;
  global $mctr;

  $ID = $newtype['mediatypeID'];

  $mediatypes[$mctr] = $newtype;
  if (uiTextSnippet($ID) != null) {
    $mediatypes[$mctr]['display'] = uiTextSnippet($ID);
  }
  $mediatypes[$mctr]['ID'] = $ID;

  $mediatypeObjs[$ID] = $mediatypes[$mctr];

  $mediatypes_assoc[$ID] = $newtype['path'];
  $mediatypes_icons[$ID] = $newtype['icon'];
  $mediatypes_thumbs[$ID] = $newtype['thumb'];
  $mediatypes_display[$ID] = isset($newtype['display']) ? $newtype['display'] : "";
  $mediatypes_like[$newtype['liketype']][] = $ID;
  $mctr++;
}

function initMediaTypes() {
  global $photopath;
  global $documentpath;
  global $headstonepath;
  global $historypath;
  global $mediapath;
  global $mediatypes_table;
  global $mediatypes;

  if (count($mediatypes)) {
    return;
  }

  if (!isset($mediatypes_table)) {
    return;
  }
  $query = "SELECT * FROM $mediatypes_table ORDER BY ordernum, display";
  $result = tng_query($query);

  if ($result) {
    while ($row = tng_fetch_assoc($result)) {
      switch ($row['mediatypeID']) {
        case "photos":
          setMediaType(
              ["mediatypeID" => "photos",
              "path" => $photopath,
              "icon" => "svg/images.svg",
              "thumb" => "photos_thumb.png",
              "liketype" => "photos",
              "exportas" => "PHOTO",
              "type" => 0,
              "disabled" => $row['disabled']
              ]
          );
          break;
        case "documents":
          setMediaType(
              ["mediatypeID" => "documents",
              "path" => $documentpath,
              "icon" => "svg/documents.svg",
              "thumb" => "documents_thumb.png",
              "liketype" => "documents",
              "exportas" => "DOCUMENT",
              "type" => 0,
              "disabled" => $row['disabled']
              ]
          );
          break;
        case "headstones":
          setMediaType(
              ["mediatypeID" => "headstones",
              "path" => $headstonepath,
              "icon" => "svg/headstone.svg",
              "thumb" => "headstones_thumb.png",
              "liketype" => "headstones",
              "exportas" => "HEADSTONE",
              "type" => 0,
              "disabled" => $row['disabled']
              ]
          );
          break;
        case "histories":
          setMediaType(
              ["mediatypeID" => "histories",
              "path" => $historypath,
              "icon" => "svg/book.svg",
              "thumb" => "histories_thumb.png",
              "liketype" => "histories",
              "exportas" => "HISTORY",
              "type" => 0,
              "disabled" => $row['disabled']
              ]
          );
          break;
        case "recordings":
          setMediaType(
              ["mediatypeID" => "recordings",
              "path" => $mediapath,
              "icon" => "svg/mic.svg",
              "thumb" => "recordings_thumb.png",
              "liketype" => "recordings",
              "exportas" => "RECORDING",
              "type" => 0,
              "disabled" => $row['disabled']
              ]
          );
          break;
        case "videos":
          setMediaType(
              ["mediatypeID" => "videos",
              "path" => $mediapath,
              "icon" => "svg/video.svg",
              "thumb" => "videos_thumb.png",
              "liketype" => "videos",
              "exportas" => "VIDEO",
              "type" => 0,
              "disabled" => $row['disabled']
              ]
          );
          break;
        default:
          $row['type'] = 1;

          setMediaType($row);
          break;
      }
    }
    tng_free_result($result);
  }
}