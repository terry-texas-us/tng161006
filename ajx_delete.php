<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowMediaDelete) {
  exit;
}

require 'adminlog.php';
require 'deletelib.php';

function getID($fields, $table, $id, $idname = 'ID') {
  $query = "SELECT $fields FROM $table WHERE $idname = '$id'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  tng_free_result($result);
  return $row;
}

$logmsg = '';

switch ($t) {
  case 'album':
    $query = "DELETE FROM $albums_table WHERE albumID = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $albumlinks_table WHERE albumID = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $album2entities_table WHERE albumID = '$id'";
    $result = tng_query($query);

    $logmsg = uiTextSnippet('deleted') . ': ' . uiTextSnippet('album') . " $id";
    break;
  case 'file':
    if (file_exists($rootpath . $desc)) { // $desc is the file name in filepicker
      $deleted = unlink($rootpath . $desc);
    }
    $logmsg = uiTextSnippet('deleted') . ": $desc";
    break;
  case 'language':
    $query = "DELETE FROM $languagesTable WHERE languageID = '$id'";
    $result = tng_query($query);

    $logmsg = uiTextSnippet('deleted') . ': ' . uiTextSnippet('language') . " $id";
    break;
  case 'media':
    include 'medialib.php';

    resortMedia($id);

    $query = "DELETE FROM $media_table WHERE mediaID = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $albumlinks_table WHERE mediaID = '$id'";
    $result = tng_query($query);

    $logmsg = uiTextSnippet('deleted') . ': ' . uiTextSnippet('media') . " $id";
    break;
  case 'tevent':
    $row = getID('personID, familyID', $temp_events_table, $id, 'tempID');
    $personID = $row['personID'];
    $familyID = $row['familyID'];

    $query = "DELETE FROM $temp_events_table WHERE tempID = '$id'";
    $result = tng_query($query);

    $logmsg = uiTextSnippet('deleted') . ': ' . uiTextSnippet('tentdata') . ' ' . ($row['personID'] ? $row['personID'] : $row['familyID']);
    break;
  case 'tlevent':
    $query = "DELETE FROM $tlevents_table WHERE tleventID = '$id'";
    $result = tng_query($query);

    $logmsg = uiTextSnippet('tlevent') . " $id " . uiTextSnippet('succdeleted');
    break;
  case 'note':
    $query = "DELETE FROM $notelinks_table WHERE xnoteID = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $xnotes_table WHERE ID = '$id'";
    $result = tng_query($query);

    $logmsg = uiTextSnippet('note') . " $id " . uiTextSnippet('succdeleted');
    break;
  case 'person':
    $row = getID('personID, branch, sex', $people_table, $id);
    $personID = $row['personID'];

    if (!checkbranch($row['branch'])) {
      exit;
    }

    $query = "DELETE FROM $people_table WHERE ID = '$id'";
    $result = tng_query($query);

    deletePersonPlus($personID, $row['sex']);

    $logmsg = uiTextSnippet('deleted') . ': ' . uiTextSnippet('person') . " $personID";
    break;
  case 'family':
    $row = getID('familyID, branch', $families_table, $id);
    $familyID = $row['familyID'];

    if (!checkbranch($row['branch'])) {
      exit;
    }

    $query = "DELETE FROM $families_table WHERE ID = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $children_table WHERE familyID = '$familyID'";
    $result = tng_query($query);

    $query = "UPDATE $people_table SET famc='' WHERE famc = '$familyID'";
    $result = tng_query($query);

    updateHasKidsFamily($familyID);

    deleteEvents($familyID);
    deleteCitations($familyID);
    deleteNoteLinks($familyID);
    deleteBranchLinks($familyID);
    deleteMediaLinks($familyID);
    deleteAlbumLinks($familyID);

    $logmsg = uiTextSnippet('deleted') . ': ' . uiTextSnippet('family') . " $familyID";
    break;
  case 'source':
    $row = getID('sourceID', $sources_table, $id);
    $sourceID = $row['sourceID'];

    $query = "DELETE FROM $sources_table WHERE ID = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $citations_table WHERE sourceID = '$sourceID'";
    $result = tng_query($query);

    deleteEvents($sourceID);
    deleteCitations($sourceID);
    deleteNoteLinks($sourceID);
    deleteMediaLinks($sourceID);
    deleteAlbumLinks($sourceID);

    $logmsg = uiTextSnippet('deleted') . ': ' . uiTextSnippet('source') . " $sourceID";
    break;
  case 'repository':
    $row = getID('repoID', $repositories_table, $id);
    $repoID = $row['repoID'];

    $query = "SELECT addressID FROM $repositories_table WHERE repoID = '$repoID'";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    tng_free_result($result);

    $query = "DELETE FROM $address_table WHERE addressID = '{$row['addressID']}'";
    $result = tng_query($query);

    $query = "DELETE FROM $repositories_table WHERE ID = '$id'";
    $result = tng_query($query);

    $query = "UPDATE $sources_table SET repoID = '' WHERE repoID = '$repoID'";
    $result = tng_query($query);

    deleteEvents($repoID);
    deleteNoteLinks($repoID);
    deleteMediaLinks($repoID);
    deleteAlbumLinks($repoID);

    $logmsg = uiTextSnippet('deleted') . ': ' . uiTextSnippet('person') . " $personID";
    break;
  case 'place':
    $row = getID('place', $places_table, $id);
    $place = $row['place'];

    $query = "DELETE FROM $places_table WHERE ID = '$id'";
    $result = tng_query($query);

    deleteMediaLinks($place);
    deleteAlbumLinks($place);

    $logmsg = uiTextSnippet('deleted') . ': ' . uiTextSnippet('place') . " $place";
    break;
  case 'cemetery':
    $query = "SELECT maplink FROM $cemeteries_table WHERE cemeteryID = '$id'";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    tng_free_result($result);

    $query = "DELETE FROM $cemeteries_table WHERE cemeteryID = '$id'";
    $result = tng_query($query);

    $logmsg = uiTextSnippet('deleted') . ': ' . uiTextSnippet('cemetery') . " $id";
    break;
  case 'user':
    $query3 = "SELECT username FROM $users_table WHERE userID = '$id'";
    $result3 = tng_query($query3);
    $urow = tng_fetch_assoc($result3);
    tng_free_result($result3);

    $query = "DELETE FROM $users_table WHERE userID = '$id'";
    $result = tng_query($query);

    $logmsg = uiTextSnippet('deleted') . ': ' . uiTextSnippet('user') . " {$urow['username']}";
    break;
  case 'branch':
    $branch = $id;
    include 'branchlib.php';

    $logmsg = uiTextSnippet('deleted') . ': ' . uiTextSnippet('branch') . " $id";
    break;
  case 'eventtype':
    $query = "DELETE FROM $eventtypes_table WHERE eventtypeID = '$id'";
    $result = tng_query($query);

    $logmsg = uiTextSnippet('deleted') . ': ' . uiTextSnippet('eventtype') . " $id";
    break;
  case 'report':
    $query = "DELETE FROM $reports_table WHERE reportID = '$id'";
    $result = tng_query($query);

    $logmsg = uiTextSnippet('deleted') . ': ' . uiTextSnippet('report') . " $id";
    break;
case 'entity':
    $newname = addslashes($delitem);
    if ($entity == 'state') {
      $query = "DELETE FROM $states_table WHERE state = '$newname'";
    } elseif ($entity == 'country') {
      $query = "DELETE FROM $countries_table WHERE country = '$newname'";
    }
    $result = tng_query($query);

    $logmsg = uiTextSnippet('deleted') . ": $entity: $delitem";
    break;
  case 'tree':
    $query = "DELETE FROM $people_table WHERE gedcom = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $families_table WHERE gedcom = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $children_table WHERE gedcom = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $sources_table WHERE gedcom = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $repositories_table WHERE gedcom = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $events_table WHERE gedcom = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $notelinks_table WHERE gedcom = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $xnotes_table WHERE gedcom = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $citations_table WHERE gedcom = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $places_table WHERE gedcom = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $assoc_table WHERE gedcom = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $address_table WHERE gedcom = '$id'";
    $result = tng_query($query);

    if ($id) {
      $query = "SELECT mediaID FROM $media_table";
      $result = tng_query($query);
      while ($row = tng_fetch_assoc($result)) {
        $delquery = "DELETE FROM $albumlinks_table WHERE mediaID = '{$row['mediaID']}'";
        $delresult = tng_query($delquery);
      }
      tng_free_result($result);

      $query = "DELETE FROM $media_table WHERE gedcom = '$id'";
      $result = tng_query($query);

      $query = "DELETE FROM $medialinks_table WHERE gedcom = '$id'";
      $result = tng_query($query);
    }

    $query = "DELETE FROM $treesTable WHERE gedcom='$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $branches_table WHERE gedcom = '$id'";
    $result = tng_query($query);

    $query = "DELETE FROM $branchlinks_table WHERE gedcom = '$id'";
    $result = tng_query($query);

    $query = "UPDATE $users_table SET allow_living = '-1' WHERE gedcom = '$id'";
    $result = tng_query($query);

    $logmsg = uiTextSnippet('deleted') . " $id " . uiTextSnippet('succdeleted') . '.';
    break;
  case 'child_unlink':
    $query = "DELETE FROM $children_table WHERE familyID = '$familyID' AND personID = '$personID'";
    $result = tng_query($query);

    $query = "UPDATE $people_table SET famc='' WHERE personID = '$personID'";
    $result = tng_query($query);

    updateHasKidsFamily($familyID);

    $logmsg = uiTextSnippet('chunlinked') . ": $personID/$familyID.";
    break;
  case 'child_delete':
    $query = "SELECT sex FROM $people_table WHERE personID = '$personID'";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    tng_free_result($result);

    $query = "DELETE FROM $people_table WHERE personID = '$personID'";
    $result = tng_query($query);

    deletePersonPlus($personID, $row['sex']);

    $logmsg = uiTextSnippet('deleted') . ": $personID/$familyID.";
    break;
  case 'mediatype':
    $query = "DELETE FROM $mediatypes_table WHERE mediatypeID = '$id'";
    $result = tng_query($query);

    $logmsg = uiTextSnippet('deleted') . ": $id.";
    break;
  case 'cemlink':
    $query = "UPDATE $cemeteries_table SET place='' WHERE cemeteryID = '$id'";
    $result = tng_query($query);
    break;
}
if ($logmsg) {
  adminwritelog($logmsg);
}
echo $id;
