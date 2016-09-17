<?php

function reorderMedia($query, $plink, $mediatypeID) {
  global $medialinks_table;
  global $media_table;

  $eventID = $plink['eventID'];
  $result3 = tng_query($query);
  while ($personrow = tng_fetch_assoc($result3)) {
    $query = "SELECT medialinkID FROM ($medialinks_table, $media_table) WHERE personID = \"{$personrow['personID']}\" AND $media_table.mediaID = $medialinks_table.mediaID AND eventID = \"$eventID\" AND mediatypeID = \"$mediatypeID\" ORDER BY ordernum";
    $result4 = tng_query($query);

    $counter = 1;
    while ($medialinkrow = tng_fetch_assoc($result4)) {
      $query = "UPDATE $medialinks_table SET ordernum = \"$counter\" WHERE medialinkID = \"{$medialinkrow['medialinkID']}\"";
      tng_query($query);
      $counter++;
    }
    tng_free_result($result4);
  }
  tng_free_result($result3);
}

function resortMedia($mediaID) {
  global $media_table;
  global $medialinks_table;
  global $people_table;
  global $families_table;

  $query = "SELECT $media_table.mediaID AS mediaID, personID, mediatypeID FROM $medialinks_table, $media_table WHERE $medialinks_table.mediaID = '$mediaID' AND $medialinks_table.mediaID = $media_table.mediaID";
  $result2 = tng_query($query);
  if ($result2) {
    while ($plink = tng_fetch_assoc($result2)) {
      $query = "DELETE FROM $medialinks_table WHERE mediaID = {$plink['mediaID']}";
      tng_query($query);

      $query = "SELECT personID FROM $people_table WHERE personID = \"{$plink['personID']}\"";
      reorderMedia($query, $plink, $plink['mediatypeID']);

      $query = "SELECT familyID AS personID FROM $families_table WHERE familyID = \"{$plink['personID']}\"";
      reorderMedia($query, $plink, $plink['mediatypeID']);

      $query = "SELECT sourceID AS personID FROM sources WHERE sourceID = \"{$plink['personID']}\"";
      reorderMedia($query, $plink, $plink['mediatypeID']);

      $query = "SELECT repoID AS personID FROM repositories WHERE repoID = \"{$plink['personID']}\"";
      reorderMedia($query, $plink, $plink['mediatypeID']);
    }
    tng_free_result($result2);
  }
}

function removeImages($mediaID) {
  global $media_table, $rootpath, $mediatypes_assoc;

  $query = "SELECT path, thumbpath, usecollfolder, mediatypeID FROM $media_table WHERE mediaID = \"$mediaID\"";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $mediatypeID = $row['mediatypeID'];
  $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;

  //now look for any records with path still the same. if none, go ahead and delete.
  $query = "SELECT count(mediaID) AS mcount FROM $media_table WHERE path = \"{$row['path']}\"";
  $result3 = tng_query($query);
  $row3 = tng_fetch_assoc($result3);
  tng_free_result($result3);

  if ($row['path'] && !$row3['mcount'] && file_exists("$rootpath$usefolder/" . $row['path'])) {
    unlink("$rootpath$usefolder/" . $row['path']);
  }

  //now look for any records with thumbpath still the same. if none, go ahead and delete.
  $query = "SELECT count(mediaID) AS mcount FROM $media_table WHERE thumbpath = \"$row[thumbpath]\"";
  $result3 = tng_query($query);
  $row3 = tng_fetch_assoc($result3);
  tng_free_result($result3);

  if ($row['thumbpath'] && !$row3['mcount'] && file_exists("$rootpath$usefolder/" . $row['thumbpath'])) {
    unlink("$rootpath$usefolder/" . $row['thumbpath']);
  }
}