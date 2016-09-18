<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowEdit || !$allowDelete) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}

require 'adminlog.php';
require 'medialib.php';

$count = 0;

initMediaTypes();

$xphaction = stripslashes($xphaction);
if ($xphaction == uiTextSnippet('convto')) {
  //loop through each one
  foreach (array_keys($_POST) as $key) {
    if (substr($key, 0, 2) == 'ph') {
      $count++;
      $mediaID = substr($key, 2);

      $query = "SELECT mediatypeID, usecollfolder, path, thumbpath FROM media WHERE mediaID = \"$mediaID\"";
      $result = tng_query($query);
      $row = tng_fetch_assoc($result);
      tng_free_result($result);

      //get current media type
      $oldmediatype = $row['mediatypeID'];

      if ($oldmediatype != $newmediatype) {
        //change media type
        $query = "UPDATE media SET mediatypeID = \"$newmediatype\" WHERE mediaID = \"$mediaID\"";
        $result = tng_query($query);

        //if usecollfolder then move to new folder
        //else leave in media
        if ($row['usecollfolder']) {
          $oldmediapath = $mediatypes_assoc[$oldmediatype];
          $newmediapath = $mediatypes_assoc[$newmediatype];
          if ($row['path']) {
            $oldpath = "$rootpath$oldmediapath/" . $row['path'];
            $newpath = "$rootpath$newmediapath/" . $row['path'];
            rename($oldpath, $newpath);
          }

          if ($row['thumbpath']) {
            $oldthumbpath = "$rootpath$oldmediapath/" . $row['thumbpath'];
            $newthumbpath = "$rootpath$newmediapath/" . $row['thumbpath'];
            rename($oldthumbpath, $newthumbpath);
          }
        }
        //change ordernum in media link
        //add to end of new media type
        //get all people linked to this item where the item has the same *new* mediatype so we can add one
        $query3 = "SELECT medialinkID, personID, eventID, mediatypeID FROM (medialinks, media) WHERE medialinks.mediaID = \"$mediaID\" AND mediatypeID = \"$newmediatype\" AND medialinks.mediaID = media.mediaID";
        $result3 = tng_query($query3) or die(uiTextSnippet('cannotexecutequery') . ": $query3");
        while ($row3 = tng_fetch_assoc($result3)) {
          $query4 = "SELECT count(medialinkID) AS count FROM (media, medialinks) WHERE personID = \"{$row3['personID']}\" AND mediatypeID = \"$newmediatype\" AND medialinks.mediaID = media.mediaID AND eventID = \"{$row3['eventID']}\"";
          $result4 = tng_query($query4) or die(uiTextSnippet('cannotexecutequery') . ": $query4");
          if ($result4) {
            $row4 = tng_fetch_assoc($result4);
            $newrow = $row4['count'] + 1;
            tng_free_result($result4);
          } else {
            $newrow = 1;
          }

          $query5 = "UPDATE medialinks SET ordernum = \"$newrow\" WHERE medialinkID = \"{$row3['medialinkID']}\"";
          $result5 = tng_query($query5) or die(uiTextSnippet('cannotexecutequery') . ": $query5");

          //reorder old media type for everything linked to item
          $query6 = "SELECT personID FROM $people_table WHERE personID = \"{$row3['personID']}\"";
          reorderMedia($query6, $row3, $row3['mediatypeID']);

          $query6 = "SELECT familyID AS personID FROM $families_table WHERE familyID = \"{$row3['personID']}\"";
          reorderMedia($query6, $row3, $row3['mediatypeID']);

          $query6 = "SELECT sourceID AS personID FROM sources WHERE sourceID = \"{$row3['personID']}\"";
          reorderMedia($query6, $row3, $row3['mediatypeID']);

          $query6 = "SELECT repoID AS personID FROM repositories WHERE repoID = \"{$row3['personID']}\"";
          reorderMedia($query6, $row3, $row3['mediatypeID']);
        }
        tng_free_result($result3);
      }
    }
  }
  if ($count) {
    $query = "UPDATE mediatypes SET disabled=\"0\" WHERE mediatypeID = '$newmediatypeID'";
    $result = tng_query($query);
  }
} elseif ($xphaction == uiTextSnippet('addtoalbum')) {
  foreach (array_keys($_POST) as $key) {
    if (substr($key, 0, 2) == 'ph') {
      $count++;
      $mediaID = substr($key, 2);

      $query = "SELECT count(albumlinkID) AS acount FROM albumlinks WHERE albumID = \"$albumID\" AND mediaID = \"$mediaID\"";
      $result = tng_query($query);
      $row = tng_fetch_assoc($result);
      tng_free_result($result);

      if (!$row['acount']) {
        //get new order number
        $query = "SELECT count(albumlinkID) AS acount FROM albumlinks WHERE albumID = \"$albumID\"";
        $result = tng_query($query);
        $row = tng_fetch_assoc($result);
        tng_free_result($result);

        $neworder = $row['acount'] ? $row['acount'] + 1 : 1;

        $query = "INSERT INTO albumlinks (albumID, mediaID, ordernum, defphoto) VALUES (\"$albumID\", \"$mediaID\", \"$neworder\", \"0\")";
        $result = tng_query($query);
      }
    }
  }
} elseif ($xphaction == uiTextSnippet('deleteselected')) {
  $query = "DELETE FROM media WHERE 1=0";

  foreach (array_keys($_POST) as $key) {
    if (substr($key, 0, 2) == 'ph') {
      $count++;
      $mediaID = substr($key, 2);
      $query .= " OR mediaID=\"$mediaID\"";

      //removeImages($mediaID);

      $aquery = "DELETE FROM albumlinks WHERE mediaID=\"$mediaID\"";
      $aresult = tng_query($aquery) or die(uiTextSnippet('cannotexecutequery') . ": $aquery");

      resortMedia($mediaID);
    }
  }

  $result = tng_query($query);
}

adminwritelog(uiTextSnippet('modifymedia') . ': ' . uiTextSnippet('all'));

if ($count) {
  $message = uiTextSnippet('changestoallmedia') . ' ' . uiTextSnippet('succsaved') . '.';
} else {
  $message = uiTextSnippet('nochanges');
}
header('Location: mediaBrowse.php?message=' . urlencode($message));
