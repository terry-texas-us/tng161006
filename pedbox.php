<?php
function getPhotoSrc($persfamID, $living, $gender) {
  global $rootpath;
  global $photopath;
  global $mediapath;
  global $mediatypes_assoc;
  global $photosext;
  global $medialinks_table;
  global $media_table;
  global $tngconfig;

  $photo = array();

  $query = "SELECT $media_table.mediaID, medialinkID, alwayson, thumbpath, mediatypeID, usecollfolder FROM ($media_table, $medialinks_table)
    WHERE personID = '$persfamID' AND $media_table.mediaID = $medialinks_table.mediaID AND defphoto = '1'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);

  $photocheck = "";
  if ($row['thumbpath']) {
    if ($row['alwayson'] || checkLivingLinks($row['mediaID'])) {
      $mediatypeID = $row['mediatypeID'];
      $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
      $photocheck = "$usefolder/" . $row['thumbpath'];
      $photoref = "$usefolder/" . str_replace("%2F", "/", rawurlencode($row['thumbpath']));
      $photolink = xmlcharacters("showmedia.php?mediaID={$row['mediaID']}&amp;medialinkID={$row['medialinkID']}");
    }
  } elseif ($living) {
    $photoref = $photocheck = "$photopath/$persfamID.$photosext";
    $photolink = "";
  }
  $gotfile = $photocheck ? file_exists("$rootpath$photocheck") : false;
  if (!$gotfile) {
    if ($gender && $tngconfig['usedefthumbs']) {
      if ($gender == 'M') {
        $photocheck = "img/male.jpg";
      } elseif ($gender == 'F') {
        $photocheck = "img/female.jpg";
      }
      $photoref = $photocheck;
      $gotfile = file_exists("$rootpath$photocheck");
    }
  }
  if ($gotfile) {
    $photo['ref'] = $photoref;
    $photo['link'] = $photolink;
  } else {
    $photo['ref'] = "";
    $photo['link'] = "";
  }

  return $photo;
}