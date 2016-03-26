<?php

require 'begin.php';
require 'genlib.php';

$tngprint = 1;
require 'checklogin.php';

header('Content-type: image/jpeg');
$maxsize = 380;

$path = urldecode($path);
$imagename = "$rootpath$path";
$photoinfo = getimagesize($imagename);
switch ($photoinfo[2]) {
  case 1:
    $image = imagecreatefromgif($imagename);
    break;
  case 3:
    $image = imagecreatefrompng($imagename);
    break;
  default:
    $image = imagecreatefromjpeg($imagename);
    break;
}

if ($photoinfo[0] <= $maxsize && $photoinfo[1] <= $maxsize) {
  $photohtouse = $photoinfo[1];
  $photowtouse = $photoinfo[0];
} else {
  if ($photoinfo[0] > $photoinfo[1]) {
    $photowtouse = $maxsize;
    $photohtouse = intval($maxsize * $photoinfo[1] / $photoinfo[0]);
  } else {
    $photohtouse = $maxsize;
    $photowtouse = intval($maxsize * $photoinfo[0] / $photoinfo[1]);
  }
}

// Resample
$image_resized = imagecreatetruecolor($photowtouse, $photohtouse);
imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $photowtouse, $photohtouse, $photoinfo[0], $photoinfo[1]);

// Display resized image
imagejpeg($image_resized);
