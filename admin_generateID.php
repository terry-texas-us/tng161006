<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

function getNewID($type, $table) {
  global $tngconfig;
  include 'prefixes.php';

  if (isset($_COOKIE['tng_' . $type . '_lastid'])) {
    $lastid = $_COOKIE['tng_' . $type . '_lastid'];
  } else {
    $lastid = 1;
  }
  if (!trim($lastid)) {
    $lastid = 0;
  }
  $found = false;
  eval("\$prefix = \$$type" . 'prefix;');
  eval("\$suffix = \$$type" . 'suffix;');

  if (!isset($tngconfig['oldids'])) {
    $tngconfig['oldids'] = '';
  }
  if ($tngconfig['oldids']) {
    while (!$found) {
      $query = "SELECT ID FROM $table WHERE {$type}ID = \"$prefix$lastid$suffix\"";
      $result = tng_query($query);
      if (!tng_num_rows($result)) {
        $found = true;
      } else {
        $lastid += 1;
      }
      mysqli_free_result($result);
    }
    $newnum = $lastid;
  } else {
    $typestr = $type . 'ID';
    if ($prefix) {
      $preflen = strlen($prefix);
      $numpart = "CAST(SUBSTRING($typestr," . ($preflen + 1) . ') as SIGNED)';
      $wherestr = "$numpart >= $lastid";
    } elseif ($suffix) {
      $suflen = strlen($suffix);
      $numpart = "CAST(SUBSTRING($typestr,0,LENGTH($typestr - " . ($sufflen + 1) . ')) as SIGNED)';
      $wherestr = "$numpart >= $lastid";
    } else {
      $numpart = $typestr;
      $wherestr = '';
    }

    $maxrows = 10000;
    $nextone = 0;
    $newnum = '';
    do {
      $query = "SELECT $typestr FROM $table WHERE $wherestr
        ORDER BY $numpart
        LIMIT $nextone, $maxrows";
      $result = tng_query($query);
      $numrows = tng_num_rows($result);

      while (($row = tng_fetch_array($result)) && !$found) {
        if ($prefix) {
          $number = intval(substr($row[$typestr], $preflen));
        } elseif ($suffix) {
          $number = intval(substr($row[$typestr], 0, -$suflen));
        } else {
          $number = intval($row[$typestr]);
        }

        if ($number > $lastid) {
          $found = true;
          $newnum = $lastid;
          break;
        } elseif ($number == $lastid) {
          $lastid += 1;
        }
      }
      $nextone += $maxrows;
    } while (!$found && $numrows == $maxrows);
  }

  $newID = $prefix . $lastid . $suffix;
  setcookie('tng_' . $type . '_lastid', $newnum, time() + 60 * 60 * 24 * 365);

  return $newID;
}

switch ($type) {
  case 'person':
    $newID = getNewID('person', $people_table);
    break;
  case 'family':
    $newID = getNewID('family', $families_table);
    break;
  case 'source':
    $newID = getNewID('source', 'sources');
    break;
  case 'repo':
    $newID = getNewID('repo', $repositories_table);
    break;
}
echo $newID;
