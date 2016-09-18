<?php

function getPhotoSrc($persfamID, $living, $gender) {
  global $rootpath;
  global $photopath;
  global $mediapath;
  global $mediatypes_assoc;
  global $photosext;
  global $tngconfig;

  $photo = [];

  $query = "SELECT media.mediaID, medialinkID, alwayson, thumbpath, mediatypeID, usecollfolder FROM (media, medialinks) WHERE personID = '$persfamID' AND media.mediaID = medialinks.mediaID AND defphoto = '1'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);

  $photocheck = '';
  if ($row['thumbpath']) {
    if ($row['alwayson'] || checkLivingLinks($row['mediaID'])) {
      $mediatypeID = $row['mediatypeID'];
      $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
      $photocheck = "$usefolder/" . $row['thumbpath'];
      $photoref = "$usefolder/" . str_replace('%2F', '/', rawurlencode($row['thumbpath']));
      $photolink = xmlcharacters("showmedia.php?mediaID={$row['mediaID']}&amp;medialinkID={$row['medialinkID']}");
    }
  } elseif ($living) {
    $photoref = $photocheck = "$photopath/$persfamID.$photosext";
    $photolink = '';
  }
  $gotfile = $photocheck ? file_exists("$rootpath$photocheck") : false;
  if (!$gotfile) {
    if ($gender && $tngconfig['usedefthumbs']) {
      if ($gender == 'M') {
        $photocheck = 'img/silhouette_male_small.png';
      } elseif ($gender == 'F') {
        $photocheck = 'img/silhouette_female_small.png';
      }
      $photoref = $photocheck;
      $gotfile = file_exists("$rootpath$photocheck");
    }
  }
  if ($gotfile) {
    $photo['ref'] = $photoref;
    $photo['link'] = $photolink;
  } else {
    $photo['ref'] = '';
    $photo['link'] = '';
  }

  return $photo;
}