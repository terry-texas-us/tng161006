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
  $query = "DELETE FROM branchlinks WHERE persfamID = '$id'";
  tng_query($query);
}

function deleteMediaLinks($id) {
  $query = "DELETE FROM medialinks WHERE personID = '$id'";
  tng_query($query);
}

function deleteAlbumLinks($id) {
  $query = "DELETE FROM albumplinks WHERE entityID = '$id'";
  tng_query($query);
}

function deleteEvents($id) {
  $query = "DELETE FROM events WHERE persfamID = '$id'";
  tng_query($query);
}

function deleteCitations($id) {
  $query = "DELETE FROM citations WHERE persfamID = '$id'";
  tng_query($query);
}

function deleteChildren($id) {
  $query = "DELETE FROM children WHERE familyID = '$id'";
  tng_query($query);
}

function deleteAssociations($id) {
  $query = "DELETE FROM associations WHERE personID = '$id' OR passocID = '$id'";
  tng_query($query);
}

function deletePersonPlus($personID, $gender) {
  $query = "DELETE FROM children WHERE personID = '$personID'";
  tng_query($query);

  deleteEvents($personID);
  deleteCitations($personID);
  deleteNoteLinks($personID);
  deleteBranchLinks($personID);
  deleteAssociations($personID);

  if ($gender == 'M') {
    $query = "SELECT familyID FROM families WHERE husband = '$personID'";
  } else {
    if ($gender == 'F') {
      $query = "SELECT familyID FROM families WHERE wife = '$personID'";
    } else {
      $query = "SELECT familyID FROM families WHERE (husband = '$personID' OR wife = '$personID')";
    }
  }

  $result = tng_query($query);
  while ($frow = tng_fetch_assoc($result)) {
    updateHasKidsFamily($frow['familyID']);
  }
  tng_free_result($result);

  $query = "UPDATE families SET husband = '', husborder = 0 WHERE husband = '$personID'";
  tng_query($query);

  $query = "UPDATE families SET wife = '', wifeorder = 0 WHERE wife = '$personID'";
  tng_query($query);

  deleteMediaLinks($personID);
  deleteAlbumLinks($personID);
}

function updateHasKids($spouseID, $spousestr) {
  $query = "SELECT familyID FROM families WHERE $spousestr = '$spouseID'";
  $result = tng_query($query);
  $numkids = 0;
  while (!$numkids && $row = tng_fetch_assoc($result)) {
    $query = "SELECT count(ID) AS ccount FROM children WHERE familyID = '$row[familyID]'";
    $result2 = tng_query($query);
    $crow = tng_fetch_assoc($result2);
    $numkids = $crow['ccount'];
    tng_free_result($result2);
  }
  tng_free_result($result);
  if (!$numkids) {
    $query = "UPDATE children SET haskids = '0' WHERE personID = '$spouseID'";
    tng_query($query);
  }
}

function updateHasKidsFamily($familyID) {
  $query = "SELECT husband, wife FROM families WHERE familyID = '$familyID'";
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
