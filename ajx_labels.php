<?php

include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

require("adminlog.php");
require("deletelib.php");

if ($assignedbranch) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

set_time_limit(0);
$husbgender = array();
$husbgender['self'] = 'husband';
$husbgender['spouse'] = 'wife';
$husbgender['spouseorder'] = 'husborder';
$wifegender = array();
$wifegender['self'] = 'wife';
$wifegender['spouse'] = 'husband';
$wifegender['spouseorder'] = 'wifeorder';

$query = "SELECT treename FROM $trees_table where gedcom = \"$tree\"";
$treeresult = tng_query($query);
$treerow = tng_fetch_assoc($treeresult);
tng_free_result($treeresult);

$counter = $fcounter = 0;
$done = $fdone = array();
$names = $famnames = "";

function getGender($personID) {
  global $tree;
  global $people_table;
  global $husbgender;
  global $wifegender;

  $info = array();
  $query = "SELECT firstname, lastname, sex FROM $people_table WHERE personID = \"$personID\" AND gedcom = \"$tree\"";
  $result = tng_query($query);
  if ($result) {
    $row = tng_fetch_assoc($result);
    if ($row['sex'] == 'M') {
      $info = $husbgender;
    } else {
      if ($row['sex'] == 'F') {
        $info = $wifegender;
      } else {
        $info['spouse'] = "";
        $info['self'] = "";
        $info['spouseorder'] = "";
      }
    }
    $info['firstname'] = $row['firstname'];
    $info['lastname'] = $row['lastname'];
    tng_free_result($result);
  }
  return $info;
}

function clearBranch($table, $branch) {
  global $tree;

  $query = "UPDATE $table SET branch=\"\" WHERE gedcom=\"$tree\" AND branch = \"$branch\"";
  tng_query($query);
  $counter = tng_affected_rows();

  $query = "SELECT branch, ID FROM $table WHERE gedcom=\"$tree\" AND branch LIKE \"%$branch%\"";
  $result = tng_query($query);
  while ($row = tng_fetch_assoc($result)) {
    $oldbranch = trim($row['branch']);

    $newbranch = "";
    $oldbranches = explode(",", $oldbranch);
    foreach ($oldbranches as $tempbranch) {
      if ($tempbranch != $branch) {
        $newbranch .= $newbranch ? ",$tempbranch" : $tempbranch;
      }
    }
    $query = "UPDATE $table SET branch=\"$newbranch\" WHERE ID=\"{$row['ID']}\"";
    tng_query($query);
    $counter++;
  }
  tng_free_result($result);

  return $counter;
}

function deleteBranch($table, $branch) {
  global $tree;
  global $people_table;
  global $children_table;

  $counter = 0;
  if ($table == $people_table) {
    $query = "SELECT ID, personID, branch, sex FROM $table WHERE gedcom=\"$tree\" AND branch LIKE \"%$branch%\"";
    $result = tng_query($query);
    while ($row = tng_fetch_assoc($result)) {
      $branches = explode(",", trim($row['branch']));
      if (in_array($branch, $branches)) {
        deletePersonPlus($row['personID'], $tree, $row['sex']);
        $query = "DELETE FROM $table WHERE ID=\"{$row['ID']}\"";
        tng_query($query);
        $counter++;
      }
    }
    tng_free_result($result);
  } else {
    $query = "SELECT ID, familyID, branch FROM $table WHERE gedcom=\"$tree\" AND branch LIKE \"%$branch%\"";
    $result = tng_query($query);
    while ($row = tng_fetch_assoc($result)) {
      $branches = explode(",", trim($row['branch']));
      if (in_array($branch, $branches)) {
        $familyID = $row['familyID'];
        $query = "DELETE FROM $children_table WHERE ID=\"$familyID\" AND gedcom = \"$tree\"";
        tng_query($query);

        $query = "UPDATE $people_table SET famc=\"\" WHERE famc = \"$familyID\" AND gedcom = \"$tree\"";
        tng_query($query);

        deleteEvents($familyID, $tree);
        deleteCitations($familyID, $tree);
        deleteNoteLinks($familyID, $tree);
        deleteBranchLinks($familyID, $tree);
        deleteMediaLinks($familyID, $tree);
        deleteAlbumLinks($familyID, $tree);

        $query = "DELETE FROM $table WHERE ID=\"{$row['ID']}\"";
        tng_query($query);
        $counter++;
      }
    }
    tng_free_result($result);
  }

  return $counter;
}

function setPersonLabel($personID) {
  global $tree;
  global $people_table;
  global $branch;
  global $branchlinks_table;
  global $overwrite;
  global $branchaction;
  global $done;
  global $names;

  //echo "personID=$personID, tree=$tree, branch=$branch<br>\n";
  if ($personID) {
    $row = "";
    if ($branchaction == "delete") {
      $query = "SELECT firstname, lastname, lnprefix, nameorder, living, private, suffix, title, sex FROM $people_table WHERE personID=\"$personID\" AND gedcom = \"$tree\"";
      $result = tng_query($query);
      $row = tng_fetch_assoc($result);
      tng_free_result($result);

      $query = "DELETE FROM $people_table WHERE personID = \"$personID\" AND gedcom = \"$tree\"";
      tng_query($query);

      //also delete children, events, medialinks, citations, notes, other family references
      deletePersonPlus($personID, $tree, $row['sex']);
      doICounter();
    } elseif (!in_array($personID, $done)) {
      $query = "SELECT firstname, lastname, lnprefix, nameorder, living, private, suffix, title, branch FROM $people_table WHERE personID = \"$personID\" AND gedcom = \"$tree\"";
      $result = tng_query($query);
      $row = tng_fetch_assoc($result);
      tng_free_result($result);

      if ($branch && ($overwrite != 1 || $branchaction == "clear")) { //append or leave
        //appending, so get current value first
        $oldbranch = trim($row['branch']);
        if ($oldbranch && ($overwrite == 2 || $branchaction == "clear")) {
          $oldbranches = explode(",", $oldbranch);
          if ($overwrite == 2) {
            if (!in_array($branch, $oldbranches)) {
              $newbranch = "$oldbranch,$branch";
            } else {
              $newbranch = $oldbranch;
            }
          } else { //clearing this branch
            foreach ($oldbranches as $tempbranch) {
              if ($tempbranch != $branch) {
                $newbranch .= $newbranch ? ",$tempbranch" : $tempbranch;
              }
            }
          }
        } else {
          $newbranch = $branch;
        }
      } else {
        $newbranch = $branch;
        $oldbranch = "";
      }

      if ($overwrite || !$oldbranch) {
        $query = "UPDATE $people_table SET branch = \"$newbranch\" WHERE personID = \"$personID\" AND gedcom = \"$tree\"";
        tng_query($query);
        doICounter();
      }
      array_push($done, $personID);
    }
    if ($row) {
      $rights = determineLivingPrivateRights($row, true, true);
      $row['allow_living'] = $rights['living'];
      $row['allow_private'] = $rights['private'];
      $names .= "<a href=\"peopleEdit.php?personID={$personID}&amp;tree=$tree&amp;cw=1\" target='_blank'>" . getName($row) . " ($personID)</a><br>\n";
    }

    if ($branchaction == "clear" || $branchaction == "delete") {
      $query = "DELETE FROM $branchlinks_table WHERE persfamID = \"$personID\" AND gedcom = \"$tree\" AND branch = \"$branch\"";
      tng_query($query);
    } else {
      if ($overwrite == 1 || !$branch) {
        $query = "DELETE FROM $branchlinks_table WHERE persfamID = \"$personID\" AND gedcom = \"$tree\"";
        tng_query($query);
      }
      if ($branch) {
        $query = "INSERT IGNORE INTO $branchlinks_table (branch,gedcom,persfamID) VALUES(\"$branch\",\"$tree\",\"$personID\")";
        tng_query($query);
      }
    }
  }
}

function doICounter() {
  global $counter;

  $counter++;
}

function doFCounter() {
  global $fcounter;

  $fcounter++;
}

function setFamilyLabel($personID, $gender) {
  global $tree;
  global $families_table;
  global $branch;
  global $overwrite;
  global $branchlinks_table;
  global $branchaction;
  global $people_table;
  global $fdone;
  global $famnames;

  //echo "personID=$personID, tree=$tree, branch=$branch<br>\n";
  if ($gender['self']) {
    $query = "SELECT branch, familyID, husband, wife, gedcom, living, private FROM $families_table WHERE {$gender['self']} = \"$personID\" AND gedcom = \"$tree\"";
    $result = tng_query($query);
    while ($row = tng_fetch_assoc($result)) {
      $oldbranch = trim($row['branch']);
      if (!in_array($row['familyID'], $fdone)) {
        $famnames .= "<a href=\"familiesEdit.php?familyID={$row['familyID']}&amp;tree={$row['gedcom']}&amp;cw=1\" target='_blank'>" . getFamilyName($row) . "</a><br>\n";
      }
      if ($branchaction == "delete") {
        $query = "DELETE FROM $families_table WHERE familyID = \"{$row['familyID']}\" AND gedcom = \"$tree\"";
        tng_query($query);

        $query = "UPDATE $people_table SET famc=\"\" WHERE famc = \"{$row['familyID']}\" AND gedcom = \"$tree\"";
        tng_query($query);

        //also delete children, events, medialinks, citations, notes
        $query = "DELETE FROM $children_table WHERE ID=\"$familyID\" AND gedcom = \"$tree\"";
        tng_query($query);

        deleteEvents($familyID, $tree);
        deleteCitations($familyID, $tree);
        deleteNoteLinks($familyID, $tree);
        deleteBranchLinks($familyID, $tree);
        deleteMediaLinks($familyID, $tree);
        deleteAlbumLinks($familyID, $tree);
        doFCounter();
      } elseif (!in_array($row['familyID'], $fdone)) {
        if ($branch && $oldbranch && ($overwrite == 2 || $branchaction == "clear")) {
          $oldbranches = explode(",", $oldbranch);
          if ($overwrite == 2) {
            if (!in_array($branch, $oldbranches)) {
              $newbranch = "$oldbranch,$branch";
            } else {
              $newbranch = $oldbranch;
            }
          } else { //clearing this branch
            foreach ($oldbranches as $tempbranch) {
              if ($tempbranch != $branch) {
                $newbranch .= $newbranch ? ",$tempbranch" : $tempbranch;
              }
            }
          }
        } else {
          $newbranch = $branch;
        }

        if ($overwrite || !$oldbranch) {
          $query = "UPDATE $families_table SET branch = \"$newbranch\" WHERE familyID = \"{$row['familyID']}\" AND gedcom = \"$tree\"";
          tng_query($query);

          doFCounter();
        }
        array_push($fdone, $row['familyID']);
      }

      if ($branchaction == "clear" || $branchaction == "delete") {
        $query = "DELETE FROM $branchlinks_table WHERE persfamID = \"{$row['familyID']}\" AND gedcom = \"$tree\" AND branch = \"$branch\"";
        tng_query($query);
      } else {
        if ($overwrite == 1 || !$branch) {
          $query = "DELETE FROM $branchlinks_table WHERE persfamID = \"{$row['familyID']}\" AND gedcom = \"$tree\"";
          tng_query($query);
        }
        if ($branch) {
          $query = "INSERT IGNORE INTO $branchlinks_table (branch,gedcom,persfamID) VALUES(\"$branch\",\"$tree\",\"{$row['familyID']}\")";
          tng_query($query);
        }
      }
    }
    tng_free_result($result);
  }
}

function setSpousesLabel($personID, $gender) {
  global $tree;
  global $families_table;

  setFamilyLabel($personID, $gender);
  if ($gender['self']) {
    $query = "SELECT {$gender['spouse']}, familyID FROM $families_table WHERE {$gender['self']} = \"$personID\" AND gedcom = \"$tree\" ORDER BY {$gender['spouseorder']}";
    $spouseresult = tng_query($query);
    while ($spouserow = tng_fetch_assoc($spouseresult)) {
      setPersonLabel($spouserow[$gender['spouse']]);
    }
  }
}

function doAncestors($personID, $gender, $gen) {
  global $dagens;
  global $tree;
  global $agens;
  global $children_table;
  global $families_table;
  global $husbgender;
  global $wifegender;
  global $dospouses;

  setPersonLabel($personID);
  setFamilyLabel($personID, $gender);
  if ($dospouses) {
    setSpousesLabel($personID, $gender);
  }

  $spouses = array();
  if ($gen <= $agens) {
    $query = "SELECT $children_table.familyID as familyID, husband, wife FROM ($children_table, $families_table) WHERE $children_table.familyID = $families_table.familyID AND personID = \"$personID\" AND $children_table.gedcom = \"$tree\" AND $families_table.gedcom = \"$tree\"";
    $familyresult = tng_query($query);

    while ($familyrow = tng_fetch_assoc($familyresult)) {
      if ($dagens) {
        $query = "SELECT personID FROM $children_table WHERE familyID = \"{$familyrow['familyID']}\" AND gedcom = \"$tree\" AND personID != \"$personID\"";
        $childresult = tng_query($query);
        while ($childrow = tng_fetch_assoc($childresult)) {
          $newgender = getGender($childrow['personID']);
          setPersonLabel($childrow['personID']);
          setFamilyLabel($childrow['personID'], $newgender);
          if ($dospouses) {
            setSpousesLabel($childrow['personID'], $newgender);
          }
          doDescendants($childrow['personID'], $newgender, 1, $dagens);
        }
      }
      if ($familyrow['husband'] && !in_array($familyrow['husband'], $spouses)) {
        array_push($spouses, $familyrow['husband']);
        doAncestors($familyrow['husband'], $husbgender, $gen + 1);
      }
      if ($familyrow['wife'] && !in_array($familyrow['wife'], $spouses)) {
        array_push($spouses, $familyrow['wife']);
        doAncestors($familyrow['wife'], $wifegender, $gen + 1);
      }
    }
  }
}

function doDescendants($personID, $gender, $gen, $maxgen) {
  global $tree;
  global $families_table;
  global $children_table;
  global $dospouses;

  $query = $gender['spouseorder'] ? "SELECT familyID FROM $families_table WHERE {$gender['self']} = \"$personID\" AND gedcom = \"$tree\" ORDER BY {$gender['spouseorder']}" : "SELECT familyID FROM $families_table WHERE gedcom = \"$tree\" AND (husband = \"$personID\" OR wife = \"$personID\")";
  $spouseresult = tng_query($query);
  while ($spouserow = tng_fetch_assoc($spouseresult)) {
    //setPersonLabel( $spouserow[$gender['spouse']] );
    $query = "SELECT personID FROM $children_table WHERE familyID = \"{$spouserow['familyID']}\" AND gedcom = \"$tree\" ORDER BY ordernum";
    $childresult = tng_query($query);
    while ($childrow = tng_fetch_assoc($childresult)) {
      $newgender = getGender($childrow['personID']);
      setPersonLabel($childrow['personID']);
      setFamilyLabel($childrow['personID'], $newgender);
      if ($dospouses) {
        setSpousesLabel($childrow['personID'], $newgender);
      }
      if ($gen < $maxgen) {
        doDescendants($childrow['personID'], $newgender, $gen + 1, $maxgen);
      }
    }
    tng_free_result($childresult);
  }
  tng_free_result($spouseresult);
}

if ($branchaction == "clear") {
  $branchtitle = uiTextSnippet('clearingbranch');
  //$branchclause = $set == "all" ? "" : " AND branch = \"$branch\"";
  //$branch = "";
  $overwrite = 1;
} elseif ($branchaction == "delete") {
  $branchtitle = "DELETING BRANCH";
  $overwrite = 0;
} else {
  $branchtitle = uiTextSnippet('addingbranch');
  $branchclause = $overwrite ? "" : " AND branch = \"\"";
}
header("Content-type:text/html; charset=" . $session_charset);
echo "<p><strong>$branchtitle</strong></p>";

if ($set == "all") {
  //all only works for deleting
  if ($branchaction == "clear") {
    $counter = clearBranch($people_table, $branch);
    $fcounter = clearBranch($families_table, $branch);
  } else {    //deleting
    $counter = deleteBranch($people_table, $branch);
    $fcounter = deleteBranch($families_table, $branch);
  }

  $query = "DELETE FROM $branchlinks_table WHERE gedcom = \"$tree\" AND branch = \"$branch\"";
  $result = tng_query($query);
} else {
  $gender = getGender($personID);
  if ($agens > 0) {
    doAncestors($personID, $gender, 1);
  } else {
    setPersonLabel($personID);
    setFamilyLabel($personID, $gender);
    if ($dospouses) {
      setSpousesLabel($personID, $gender);
    }
  }
  if ($dagens > $dgens) {
    $dgens = $dagens;
  }
  if ($dgens > 0) {
    doDescendants($personID, $gender, 1, $dgens);
  }
}
echo "<p>" . uiTextSnippet('totalaffected') . ": $counter " . uiTextSnippet('people') . ", $fcounter " . uiTextSnippet('families') . ".</p>\n";
echo "<p>$names</p>\n";
echo "<p>$famnames</p>\n";

adminwritelog(uiTextSnippet('labelbranches') . ": $tree/$branch ($branchaction/$set)");
