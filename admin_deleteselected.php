<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowDelete) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
require 'adminlog.php';
require 'deletelib.php';

function getID($fields, $table, $id) {
  $query = "SELECT $fields FROM $table WHERE ID = '$id'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  tng_free_result($result);
  return $row;
}

if ($xsrcaction) {
  $query = 'DELETE FROM sources';
  $modmsg = 'sources';
  $id = 'ID';
  $location = 'sourcesBrowse.php';
} elseif ($xrepoaction) {
  $query = 'DELETE FROM repositories';
  $modmsg = 'repositories';
  $id = 'ID';
  $location = 'repositoriesBrowse.php';
} elseif ($xperaction) {
  $query = "DELETE FROM $people_table";
  $modmsg = 'people';
  $id = 'ID';
  $location = 'peopleBrowse.php';
} elseif ($xfamaction) {
  $query = "DELETE FROM $families_table";
  $modmsg = 'families';
  $id = 'ID';
  $location = 'familiesBrowse.php';
} elseif ($xplacaction) {
  $query = 'DELETE FROM places';
  $modmsg = 'places';
  $id = 'ID';
  $location = 'placesBrowse.php';
} elseif ($xtimeaction) {
  $query = 'DELETE FROM timelineevents';
  $modmsg = 'tlevents';
  $id = 'tleventID';
  $location = 'timelineeventsBrowse.php';
} elseif ($xbranchaction) {
  $query = 'DELETE FROM branches';
  $modmsg = 'branches';
  $id = 'branch';
  $location = 'branchesBrowse.php';
} elseif ($xcemaction) {
  $query = 'DELETE FROM cemeteries';
  $modmsg = 'cemeteries';
  $id = 'cemeteryID';
  $location = 'cemeteriesBrowse.php';
} elseif ($xnoteaction) {
  $query = 'DELETE FROM xnotes';
  $modmsg = 'notes';
  $id = 'ID';
  $location = 'admin_notelist.php';
}
$modifymsg = uiTextSnippet($modmsg);
$count = 0;
$items = [];

$whereClause = '';

foreach (array_keys($_POST) as $key) {
  if (substr($key, 0, 3) == 'del') {
    $count++;
    $thisid = substr($key, 3);
    $whereClause .= $whereClause ? ' OR ' : ' WHERE ';
    $whereClause .= "$id = '$thisid'";

    if ($xperaction) {
      $row = getID('personID, branch, sex', $people_table, $thisid);
      $personID = $row['personID'];
      $items[] = $row['personID'];

      deletePersonPlus($personID, $row['sex']);
    } elseif ($xfamaction) {
      $row = getID('familyID, branch', $families_table, $thisid);
      $familyID = $row['familyID'];
      $items[] = $row['familyID'];

      $fquery = "DELETE FROM $children_table WHERE familyID = '$familyID'";
      $result = tng_query($fquery);

      $pquery = "UPDATE $people_table SET famc = '' WHERE famc = '$familyID'";
      $result = tng_query($pquery);

      updateHasKidsFamily($familyID);

      deleteEvents($familyID);
      deleteCitations($familyID);
      deleteNoteLinks($familyID);
      deleteBranchLinks($familyID);
      deleteMediaLinks($familyID);
      deleteAlbumLinks($familyID);
    } elseif ($xsrcaction) {
      $row = getID('sourceID', 'sources', $thisid);
      $sourceID = $row['sourceID'];
      $items[] = $row['sourceID'];

      $squery = "DELETE FROM citations WHERE sourceID = '$sourceID'";
      $result = tng_query($squery);

      deleteEvents($sourceID);
      deleteCitations($sourceID);
      deleteNoteLinks($sourceID);
      deleteMediaLinks($sourceID);
      deleteAlbumLinks($sourceID);
    } elseif ($xrepoaction) {
      $row = getID('repoID', 'repositories', $thisid);
      $repoID = $row['repoID'];
      $items[] = $row['repoID'];

      $rquery = "SELECT addressID FROM repositories WHERE repoID = '$repoID'";
      $result = tng_query($rquery);
      $row = tng_fetch_assoc($result);
      tng_free_result($result);

      $rquery = "DELETE FROM addresses WHERE addressID = '{$row['addressID']}'";
      $result = tng_query($rquery);

      $rquery = "UPDATE sources SET repoID = '' WHERE repoID = '$repoID'";
      $result = tng_query($rquery);

      deleteEvents($repoID);
      deleteNoteLinks($repoID);
      deleteMediaLinks($repoID);
      deleteAlbumLinks($repoID);
    } elseif ($xplacaction) {
      $row = getID('place', 'places', $thisid);
      $place = $row['place'];
      $items[] = $row['place'];

      deleteMediaLinks($place);
      deleteAlbumLinks($place);
    } elseif ($xtimeaction) {
      $query3 = "DELETE FROM timelineevents WHERE tleventID = '$thisid'";
      $result3 = tng_query($query3) or die(uiTextSnippet('cannotexecutequery') . ": $query3");
      $items[] = $thisid;
    } elseif ($xbranchaction) {
      $branch = $thisid;
      $items[] = $branch;
      include 'branchlib.php';
    } elseif ($xcemaction) {
      $query3 = "SELECT cemname FROM cemeteries WHERE cemeteryID = '$thisid'";
      $result3 = tng_query($query3);
      $crow = tng_fetch_assoc($result3);
      tng_free_result($result3);
      $items[] = $crow['cemname'];
    } elseif ($xnoteaction) {
      $nquery = "DELETE FROM notelinks WHERE xnoteID = '$thisid'";
      $result = tng_query($nquery);
      $items[] = $thisid;
    }
  }
}
$query .= $whereClause;
$result = tng_query($query);

adminwritelog(uiTextSnippet('deleted') . ': ' . $modifymsg . ' ' . implode(', ', $items));

if ($count) {
  $message = uiTextSnippet('changestoallitems') . ' ' . uiTextSnippet('succsaved') . '.';
} else {
  $message = uiTextSnippet('nochanges');
}
header("Location: $location" . '?message=' . urlencode($message));
