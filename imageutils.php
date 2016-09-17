<?php

function chkgd2() {
  $testGD = get_extension_funcs('gd'); // Grab function list
  if (!$testGD) {
    echo 'GD not installed.';
    exit;
  }
  if (in_array('imagegd2', $testGD)) {
    return true;
  } else {
    return false;
  }
}

function image_createThumb($src, $dest, $maxWidth, $maxHeight, $quality) {
  if (file_exists($src) && isset($dest)) {
    $destInfo = pathInfo($dest);
    if ($session_charset == 'UTF-8') {
      $dest = utf8_decode($dest);
    }
    $srcSize = getimagesize($src);

    // image dest size $destSize[0] = width, $destSize[1] = height
    if ($srcSize[1]) {
      $srcRatio = $srcSize[0] / $srcSize[1];
    } // width/height ratio
    else {
      return false;
    }
    if (!$maxWidth) {
      $maxWidth = 50;
    }
    if (!$maxHeight) {
      $maxHeight = 50;
    }
    $destRatio = $maxWidth / $maxHeight;
    if ($destRatio > $srcRatio) {
      $destSize[1] = $maxHeight;
      $destSize[0] = $maxHeight * $srcRatio;
    } else {
      $destSize[0] = $maxWidth;
      $destSize[1] = $maxWidth / $srcRatio;
    }

    if (strtoupper($destInfo['extension']) == 'GIF') {
      $dest = substr_replace($dest, 'jpg', -3);
    }

    $gd2 = chkgd2();
    if ($gd2 && function_exists(imageCreateTrueColor)) {
      $destImage = imagecreatetruecolor($destSize[0], $destSize[1]) or $destImage = imagecreate($destSize[0], $destSize[1]);
      if (function_exists(imageantialias)) {
        imageantialias($destImage, true);
      }
    } else {
      $destImage = imagecreate($destSize[0], $destSize[1]);
    }

    switch ($srcSize[2]) {
      case 1:
        $srcImage = imagecreatefromgif($src);
        break;
      case 2:
        $srcImage = imagecreatefromjpeg($src);
        break;
      case 3:
        $srcImage = imagecreatefrompng($src);
        break;
      default:
        return false;
    }
    if (!$srcImage) {
      return false;
    }

    // resampling
    if ($gd2 && function_exists(imagecopyresampled)) {
      if (!imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $destSize[0], $destSize[1], $srcSize[0], $srcSize[1])) {
        imagecopyresized($destImage, $srcImage, 0, 0, 0, 0, $destSize[0], $destSize[1], $srcSize[0], $srcSize[1]);
      }
    } else {
      imagecopyresized($destImage, $srcImage, 0, 0, 0, 0, $destSize[0], $destSize[1], $srcSize[0], $srcSize[1]);
    }

    // generating image
    switch ($srcSize[2]) {
      case 1:
      case 2:
        if ($srcSize[2] == 2) {
          //fix photos taken on cameras that have incorrect
          //dimensions
          if (function_exists('exif_read_data')) {
            $exif = exif_read_data($src);
            if ($exif !== false) {
              $ort = $exif['Orientation'];

              //determine what oreientation the image was taken at
              switch ($ort) {
                case 2: // horizontal flip
                  tngImageFlip($destImage);
                  break;
                case 3: // 180 rotate left
                  $destImage = imagerotate($destImage, 180, -1);
                  break;
                case 4: // vertical flip
                  tngImageFlip($destImage);
                  break;
                case 5: // vertical flip + 90 rotate right
                  tngImageFlip($destImage);
                  $destImage = imagerotate($destImage, -90, -1);
                  break;
                case 6: // 90 rotate right
                  $destImage = imagerotate($destImage, -90, -1);
                  break;
                case 7: // horizontal flip + 90 rotate right
                  tngImageFlip($destImage);
                  $destImage = imagerotate($destImage, -90, -1);
                  break;
                case 8: // 90 rotate left
                  $destImage = imagerotate($destImage, 90, -1);
                  break;
              }
            }
          }
        }
        if (!imagejpeg($destImage, $dest, $quality)) {
          return false;
        }
        break;
      case 3:
        if (!imagepng($destImage, $dest)) {
          return false;
        }
        break;
    }
    return true;
  } else {
    return false;
  }
}

function tngImageFlip(&$image, $x = 0, $y = 0, $width = null, $height = null) {
  if ($width < 1) {
    $width = imagesx($image);
  }
  if ($height < 1) {
    $height = imagesy($image);
  }

  // Truecolor provides better results, if possible.
  if (function_exists('imageistruecolor') && imageistruecolor($image)) {
    $tmp = imagecreatetruecolor(1, $height);
  } else {
    $tmp = imagecreate(1, $height);
  }
  $x2 = $x + $width - 1;
  for ($i = (int) floor(($width - 1) / 2); $i >= 0; $i--) {
    // Backup right stripe.
    imagecopy($tmp, $image, 0, 0, $x2 - $i, $y, 1, $height);

    // Copy left stripe to the right.
    imagecopy($image, $image, $x2 - $i, $y, $x + $i, $y, 1, $height);

    // Copy backuped right stripe to the left.
    imagecopy($image, $tmp, $x + $i, $y, 0, 0, 1, $height);
  }

  imagedestroy($tmp);

  return true;
}
