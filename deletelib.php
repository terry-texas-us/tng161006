<?php

function deleteNoteLinks($id) {
  $query = "SELECT ID FROM notelinks WHERE persfamID = '$id'";
  $nresult = tng_query($query);

  while ($nrow = tng_fetch_assoc($nresult)) {
    deleteNote($nrow['ID'], 0);
  }
  tng_free_result($nresult);

  $query = "DELETE FROM notelinks WHERE persfamID = '$id'";
  tng_query($query);
}

function deleteBranchLinks($id) {
  global $branchlinks_table;

  $query = "DELETE FROM $branchlinks_table WHERE persfamID = '$id'";
  tng_query($query);
}

function deleteMediaLinks($id) {
  global $medialinks_table;

  $query = "DELETE FROM $medialinks_table WHERE personID = '$id'";
  tng_query($query);
}

function deleteAlbumLinks($id) {
  global $album2entities_table;

  $query = "DELETE FROM $album2entities_table WHERE entityID = '$id'";
  tng_query($query);
}

function deleteEvents($id) {
  $query = "DELETE FROM events WHERE persfamID = '$id'";
  tng_query($query);
}

function deleteCitations($id) {
  global $citations_table;

  $query = "DELETE FROM $citations_table WHERE persfamID = '$id'";
  tng_query($query);
}

function deleteChildren($id) {
  global $children_table;

  $query = "DELETE FROM $children_table WHERE familyID = '$id'";
  tng_query($query);
}

function deleteAssociations($id) {
  global $assoc_table;

  $query = "DELETE FROM $assoc_table WHERE personID = '$id' OR passocID = '$id'";
  tng_query($query);
}

function deletePersonPlus($personID, $gender) {
  global $children_table;
  global $families_table;

  $query = "DELETE FROM $children_table WHERE personID = '$personID'";
  tng_query($query);

  deleteEvents($personID);
  deleteCitations($personID);
  deleteNoteLinks($personID);
  deleteBranchLinks($personID);
  deleteAssociations($personID);

  if ($gender == 'M') {
    $query = "SELECT familyID FROM $families_table WHERE husband = '$personID'";
  } else {
    if ($gender == 'F') {
      $query = "SELECT familyID FROM $families_table WHERE wife = '$personID'";
    } else {
      $query = "SELECT familyID FROM $families_table WHERE (husband = '$personID' OR wife = '$personID')";
    }
  }

  $result = tng_query($query);
  while ($frow = tng_fetch_assoc($result)) {
    updateHasKidsFamily($frow['familyID']);
  }
  tng_free_result($result);

  $query = "UPDATE $families_table SET husband = '', husborder = 0 WHERE husband = '$personID'";
  tng_query($query);

  $query = "UPDATE $families_table SET wife = '', wifeorder = 0 WHERE wife = '$personID'";
  tng_query($query);

  deleteMediaLinks($personID);
  deleteAlbumLinks($personID);
}

function updateHasKids($spouseID, $spousestr) {
  global $families_table;
  global $children_table;

  $query = "SELECT familyID FROM $families_table WHERE $spousestr = '$spouseID'";
  $result = tng_query($query);
  $numkids = 0;
  while (!$numkids && $row = tng_fetch_assoc($result)) {
    $query = "SELECT count(ID) AS ccount FROM $children_table WHERE familyID = '$row[familyID]'";
    $result2 = tng_query($query);
    $crow = tng_fetch_assoc($result2);
    $numkids = $crow['ccount'];
    tng_free_result($result2);
  }
  tng_free_result($result);
  if (!$numkids) {
    $query = "UPDATE $children_table SET haskids = '0' WHERE personID = '$spouseID'";
    tng_query($query);
  }
}

function updateHasKidsFamily($familyID) {
  global $families_table;

  $query = "SELECT husband, wife FROM $families_table WHERE familyID = '$familyID'";
  $result = tng_query($query);
  $famrow = tng_fetch_assoc($result);
  tng_free_result($result);
  if ($famrow['husband']) {
    updateHasKids($famrow['husband'], 'husband');
  }
  if ($famrow['wife']) {
    updateHasKids($famrow['wife'], 'wife');
  }
}
