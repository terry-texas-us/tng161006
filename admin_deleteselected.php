<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = true;
require 'checklogin.php';

if (!$allowDelete) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
require 'adminlog.php';
require 'deletelib.php';

function getID($fields, $table, $id) {
  $query = "SELECT $fields FROM $table WHERE ID = \"$id\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  tng_free_result($result);
  return $row;
}

if ($xsrcaction) {
  $query = "DELETE FROM $sources_table WHERE 1=0";
  $modmsg = "sources";
  $id = "ID";
  $location = "sourcesBrowse.php";
} elseif ($xrepoaction) {
  $query = "DELETE FROM $repositories_table WHERE 1=0";
  $modmsg = "repositories";
  $id = "ID";
  $location = "repositoriesBrowse.php";
} elseif ($xperaction) {
  $query = "DELETE FROM $people_table WHERE 1=0";
  $modmsg = "people";
  $id = "ID";
  $location = "peopleBrowse.php";
} elseif ($xfamaction) {
  $query = "DELETE FROM $families_table WHERE 1=0";
  $modmsg = "families";
  $id = "ID";
  $location = "familiesBrowse.php";
} elseif ($xplacaction) {
  $query = "DELETE FROM $places_table WHERE 1=0";
  $modmsg = "places";
  $id = "ID";
  $location = "placesBrowse.php";
} elseif ($xtimeaction) {
  $query = "DELETE FROM $tlevents_table WHERE 1=0";
  $modmsg = "tlevents";
  $id = "tleventID";
  $location = "timelineeventsBrowse.php";
} elseif ($xbranchaction) {
  $query = "DELETE FROM $branches_table WHERE 1=0";
  $modmsg = "branches";
  $id = "branch";
  $location = "branchesBrowse.php";
} elseif ($xcemaction) {
  $query = "DELETE FROM $cemeteries_table WHERE 1=0";
  $modmsg = "cemeteries";
  $id = "cemeteryID";
  $location = "cemeteriesBrowse.php";
} elseif ($xnoteaction) {
  $query = "DELETE FROM $xnotes_table WHERE 1=0";
  $modmsg = "notes";
  $id = "ID";
  $location = "admin_notelist.php";
}
$modifymsg = uiTextSnippet($modmsg);
$count = 0;
$items = [];

foreach (array_keys($_POST) as $key) {
  if (substr($key, 0, 3) == "del") {
    $count++;
    $thisid = substr($key, 3);
    $query .= " OR $id =\"$thisid\"";

    $itemID = "";
    $tree = "";

    if ($xperaction) {
      $row = getID("personID, gedcom, branch, sex", $people_table, $thisid);
      $personID = $row['personID'];
      $items[] = $row['gedcom'] . "/" . $row['personID'];

      deletePersonPlus($personID, $row['sex']);
    } elseif ($xfamaction) {
      $row = getID("familyID, branch, gedcom", $families_table, $thisid);
      $familyID = $row['familyID'];
      $items[] = $row['gedcom'] . "/" . $row['familyID'];

      $fquery = "DELETE FROM $children_table WHERE familyID='$familyID'";
      $result = tng_query($fquery);

      $pquery = "UPDATE $people_table SET famc=\"\" WHERE famc='$familyID'";
      $result = tng_query($pquery);

      updateHasKidsFamily($familyID);

      deleteEvents($familyID);
      deleteCitations($familyID);
      deleteNoteLinks($familyID);
      deleteBranchLinks($familyID);
      deleteMediaLinks($familyID);
      deleteAlbumLinks($familyID);
    } elseif ($xsrcaction) {
      $row = getID("sourceID, gedcom", $sources_table, $thisid);
      $sourceID = $row['sourceID'];
      $items[] = $row['gedcom'] . "/" . $row['sourceID'];

      $squery = "DELETE FROM $citations_table WHERE sourceID = '$sourceID'";
      $result = tng_query($squery);

      deleteEvents($sourceID);
      deleteCitations($sourceID);
      deleteNoteLinks($sourceID);
      deleteMediaLinks($sourceID);
      deleteAlbumLinks($sourceID);
    } elseif ($xrepoaction) {
      $row = getID("repoID, gedcom", $repositories_table, $thisid);
      $repoID = $row['repoID'];
      $items[] = $row['gedcom'] . "/" . $row['repoID'];

      $rquery = "SELECT addressID FROM $repositories_table WHERE repoID=\"$repoID\"";
      $result = tng_query($rquery);
      $row = tng_fetch_assoc($result);
      tng_free_result($result);

      $rquery = "DELETE FROM $address_table WHERE addressID=\"{$row['addressID']}\"";
      $result = tng_query($rquery);

      $rquery = "UPDATE $sources_table SET repoID = \"\" WHERE repoID = '$repoID'";
      $result = tng_query($rquery);

      deleteEvents($repoID);
      deleteNoteLinks($repoID);
      deleteMediaLinks($repoID);
      deleteAlbumLinks($repoID);
    } elseif ($xplacaction) {
      $row = getID("place, gedcom", $places_table, $thisid);
      $place = $row['place'];
      $items[] = $row['gedcom'] . "/" . $row['place'];

      deleteMediaLinks($place);
      deleteAlbumLinks($place);
    } elseif ($xtimeaction) {
      $query3 = "DELETE FROM $tlevents_table WHERE tleventID = \"$thisid\"";
      $result3 = tng_query($query3) or die(uiTextSnippet('cannotexecutequery') . ": $query3");
      $items[] = $thisid;
    } elseif ($xbranchaction) {
      $args = explode('&', $thisid);
      $branch = $args[0];
      $tree = $args[1];
      $items[] = $tree . "/" . $branch;
      include 'branchlib.php';
    } elseif ($xcemaction) {
      $query3 = "SELECT cemname FROM $cemeteries_table WHERE cemeteryID = \"$thisid\"";
      $result3 = tng_query($query3);
      $crow = tng_fetch_assoc($result3);
      tng_free_result($result3);
      //if( $crow['maplink'] && file_exists( "$rootpath$headstonepath/$crow[maplink]" ) )
      //unlink( "$rootpath$headstonepath/$crow[maplink]" );
      $items[] = $crow['cemname'];
    } elseif ($xnoteaction) {
      $nquery = "DELETE FROM $notelinks_table WHERE xnoteID=\"$thisid\"";
      $result = tng_query($nquery);
      $items[] = $thisid;
    }
  }
}
$result = tng_query($query);

adminwritelog(uiTextSnippet('deleted') . ": " . $modifymsg . " " . implode(', ', $items));

if ($count) {
  $message = uiTextSnippet('changestoallitems') . " " . uiTextSnippet('succsaved') . ".";
} else {
  $message = uiTextSnippet('nochanges');
}
header("Location: $location" . "?message=" . urlencode($message));
