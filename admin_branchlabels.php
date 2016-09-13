<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

require 'adminlog.php';
require 'deletelib.php';

if ($assignedbranch) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
set_time_limit(0);
$husbgender = [];
$husbgender['self'] = 'husband';
$husbgender['spouse'] = 'wife';
$husbgender['spouseorder'] = 'husborder';
$wifegender = [];
$wifegender['self'] = 'wife';
$wifegender['spouse'] = 'husband';
$wifegender['spouseorder'] = 'wifeorder';

$counter = $fcounter = 0;
$done = $fdone = [];

function getGender($personID) {
  global $people_table;
  global $husbgender;
  global $wifegender;

  $info = [];
  $query = "SELECT firstname, lastname, sex FROM $people_table WHERE personID = '$personID'";
  $result = tng_query($query);
  if ($result) {
    $row = tng_fetch_assoc($result);
    if ($row['sex'] == 'M') {
      $info = $husbgender;
    } else {
      if ($row['sex'] == 'F') {
        $info = $wifegender;
      } else {
        $info['spouse'] = '';
        $info['self'] = '';
        $info['spouseorder'] = '';
      }
    }
    $info['firstname'] = $row['firstname'];
    $info['lastname'] = $row['lastname'];
    tng_free_result($result);
  }
  return $info;
}

function clearBranch($table, $branch) {
  $query = "UPDATE $table SET branch=\"\" WHERE branch = '$branch'";
  tng_query($query);
  $counter = tng_affected_rows();

  $query = "SELECT branch, ID FROM $table WHERE branch LIKE \"%$branch%\"";
  $result = tng_query($query);
  while ($row = tng_fetch_assoc($result)) {
    $oldbranch = trim($row['branch']);

    $newbranch = '';
    $oldbranches = explode(',', $oldbranch);
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
  global $people_table;
  global $children_table;

  $counter = 0;
  if ($table == $people_table) {
    $query = "SELECT ID, personID, branch, sex FROM $table WHERE branch LIKE \"%$branch%\"";
    $result = tng_query($query);
    while ($row = tng_fetch_assoc($result)) {
      $branches = explode(',', trim($row['branch']));
      if (in_array($branch, $branches)) {
        deletePersonPlus($row['personID'], $row['sex']);
        $query = "DELETE FROM $table WHERE ID=\"{$row['ID']}\"";
        tng_query($query);
        $counter++;
      }
    }
    tng_free_result($result);
  } else {
    $query = "SELECT ID, familyID, branch FROM $table WHERE branch LIKE \"%$branch%\"";
    $result = tng_query($query);
    while ($row = tng_fetch_assoc($result)) {
      $branches = explode(',', trim($row['branch']));
      if (in_array($branch, $branches)) {
        $familyID = $row['familyID'];
        $query = "DELETE FROM $children_table WHERE ID = '$familyID'";
        tng_query($query);

        $query = "UPDATE $people_table SET famc=\"\" WHERE famc = '$familyID'";
        tng_query($query);

        deleteEvents($familyID);
        deleteCitations($familyID);
        deleteNoteLinks($familyID);
        deleteBranchLinks($familyID);
        deleteMediaLinks($familyID);
        deleteAlbumLinks($familyID);
        deleteAssociations($familyID);

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
  global $people_table;
  global $branch;
  global $branchlinks_table;
  global $overwrite;
  global $branchaction;
  global $done;

  if ($personID) {
    if ($branchaction == 'delete') {
      $query = "SELECT sex FROM $people_table WHERE personID='$personID'";
      $result = tng_query($query);
      $row = tng_fetch_assoc($result);
      tng_free_result($result);

      $query = "DELETE FROM $people_table WHERE personID = '$personID'";
      tng_query($query);

      //also delete children, events, medialinks, citations, notes, other family references
      deletePersonPlus($personID, $row['sex']);
      doICounter();
    } elseif (!in_array($personID, $done)) {
      if ($branch && ($overwrite != 1 || $branchaction == 'clear')) { //append or leave
        //appending, so get current value first
        $query = "SELECT branch FROM $people_table WHERE personID = '$personID'";
        $result = tng_query($query);
        $row = tng_fetch_assoc($result);
        $oldbranch = trim($row['branch']);
        if ($oldbranch && ($overwrite == 2 || $branchaction == 'clear')) {
          $oldbranches = explode(',', $oldbranch);
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
        tng_free_result($result);
      } else {
        $newbranch = $branch;
        $oldbranch = '';
      }
      if ($overwrite || !$oldbranch) {
        $query = "UPDATE $people_table SET branch = '$newbranch' WHERE personID = '$personID'";
        tng_query($query);
        doICounter();
      }
      array_push($done, $personID);
    }
    if ($branchaction == 'clear' || $branchaction == 'delete') {
      $query = "DELETE FROM $branchlinks_table WHERE persfamID = '$personID' AND branch = '$branch'";
      tng_query($query);
    } else {
      if ($overwrite == 1 || !$branch) {
        $query = "DELETE FROM $branchlinks_table WHERE persfamID = '$personID'";
        tng_query($query);
      }
      if ($branch) {
        $query = "INSERT IGNORE INTO $branchlinks_table (branch, persfamID) VALUES('$branch', '$personID')";
        tng_query($query);
      }
    }
  }
}

function doICounter() {
  global $counter;

  $counter++;
  if ($counter % 10 == 0) {
    echo "<strong>I$counter</strong> ";
  }
}

function doFCounter() {
  global $fcounter;

  $fcounter++;
  if ($fcounter % 10 == 0) {
    echo "<strong>F$fcounter</strong> ";
  }
}

function setFamilyLabel($personID, $gender) {
  global $families_table;
  global $branch;
  global $overwrite;
  global $branchlinks_table;
  global $branchaction;
  global $people_table;
  global $fdone;

  if ($gender['self']) {
    $query = "SELECT branch, familyID FROM $families_table WHERE {$gender['self']} = '$personID'";
    $result = tng_query($query);
    while ($row = tng_fetch_assoc($result)) {
      $oldbranch = trim($row['branch']);
      if ($branchaction == 'delete') {
        $query = "DELETE FROM $families_table WHERE familyID = \"{$row['familyID']}\"";
        tng_query($query);

        $query = "UPDATE $people_table SET famc=\"\" WHERE famc = \"{$row['familyID']}\"";
        tng_query($query);

        //also delete children, events, medialinks, citations, notes
        $query = "DELETE FROM $children_table WHERE ID = '$familyID'";
        tng_query($query);

        deleteEvents($familyID);
        deleteCitations($familyID);
        deleteNoteLinks($familyID);
        deleteBranchLinks($familyID);
        deleteMediaLinks($familyID);
        deleteAlbumLinks($familyID);
        doFCounter();
      } elseif (!in_array($row['familyID'], $fdone)) {
        if ($branch && $oldbranch && ($overwrite == 2 || $branchaction == 'clear')) {
          $oldbranches = explode(',', $oldbranch);
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
          $query = "UPDATE $families_table SET branch = \"$newbranch\" WHERE familyID = \"{$row['familyID']}\"";
          tng_query($query);

          doFCounter();
        }
        array_push($fdone, $row['familyID']);
      }

      if ($branchaction == 'clear' || $branchaction == 'delete') {
        $query = "DELETE FROM $branchlinks_table WHERE persfamID = \"{$row['familyID']}\" AND branch = '$branch'";
        tng_query($query);
      } else {
        if ($overwrite == 1 || !$branch) {
          $query = "DELETE FROM $branchlinks_table WHERE persfamID = \"{$row['familyID']}\"";
          tng_query($query);
        }
        if ($branch) {
          $query = "INSERT IGNORE INTO $branchlinks_table (branch, persfamID) VALUES('$branch', \"{$row['familyID']}\")";
          tng_query($query);
        }
      }
    }
    tng_free_result($result);
  }
}

function setSpousesLabel($personID, $gender) {
  global $families_table;

  setFamilyLabel($personID, $gender);
  if ($gender['self']) {
    $query = "SELECT {$gender['spouse']}, familyID FROM $families_table WHERE {$gender['self']} = '$personID' ORDER BY {$gender['spouseorder']}";
    $spouseresult = tng_query($query);
    while ($spouserow = tng_fetch_assoc($spouseresult)) {
      setPersonLabel($spouserow[$gender['spouse']]);
    }
  }
}

function doAncestors($personID, $gender, $gen) {
  global $dagens;
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
  $spouses = [];
  if ($gen <= $agens) {
    $query = "SELECT $children_table.familyID AS familyID, husband, wife FROM ($children_table, $families_table) WHERE $children_table.familyID = $families_table.familyID AND personID = '$personID'";
    $familyresult = tng_query($query);

    while ($familyrow = tng_fetch_assoc($familyresult)) {
      if ($dagens) {
        $query = "SELECT personID FROM $children_table WHERE familyID = \"{$familyrow['familyID']}\" AND personID != '$personID'";
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
  global $families_table;
  global $children_table;
  global $dospouses;

  if ($gender['spouseorder']) {
    $query = "SELECT familyID FROM $families_table WHERE {$gender['self']} = '$personID' ORDER BY {$gender['spouseorder']}";
  } else {
    $query = "SELECT familyID FROM $families_table WHERE (husband = \"$personID\" OR wife = \"$personID\")";
  }
  $spouseresult = tng_query($query);
  while ($spouserow = tng_fetch_assoc($spouseresult)) {
    //setPersonLabel( $spouserow[$gender['spouse']] );
    $query = "SELECT personID FROM $children_table WHERE familyID = \"{$spouserow['familyID']}\" ORDER BY ordernum";
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

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('labelbranches'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="admin-branch-labels">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('branches-labelbranches', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'branchesBrowse.php', uiTextSnippet('browse'), 'findbranch']);
    $navList->appendItem([$allowAdd, 'branchesAdd.php', uiTextSnippet('add'), 'addbranch']);
    $navList->appendItem([$allowEdit, '#', uiTextSnippet('labelbranches'), 'label']);
    echo $navList->build('label');
    ?>
    <div>
      <div>
        <?php
        if ($branchaction == 'clear') {
          $branchtitle = uiTextSnippet('clearingbranch');
          //$branchclause = $set == "all" ? '' : " AND branch = \"$branch\"";
          //$branch = '';
          $overwrite = 1;
        } elseif ($branchaction == 'delete') {
          $branchtitle = 'DELETING BRANCH';
          $overwrite = 0;
        } else {
          $branchtitle = uiTextSnippet('addingbranch');
          $branchclause = $overwrite ? '' : " AND branch = \"\"";
        }
        echo "<p><strong>$branchtitle</strong></p>";

        if ($set == 'all') {
          //all only works for deleting
          if ($branchaction == 'clear') {
            $counter = clearBranch($people_table, $branch);
            $fcounter = clearBranch($families_table, $branch);
          } else { //deleting
            $counter = deleteBranch($people_table, $branch);
            $fcounter = deleteBranch($families_table, $branch);
          }

          $query = "DELETE FROM $branchlinks_table WHERE branch = '$branch'";
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
        if ($counter || $fcounter) {
          echo '<br><br>';
        }
        echo '<span>' . uiTextSnippet('totalaffected') . ": $counter " . uiTextSnippet('people') . ", $fcounter " . uiTextSnippet('families') . '.</span>';

        adminwritelog(uiTextSnippet('labelbranches') . ": $branch ($branchaction/$set)");
        ?>

        <p>
          <a href="admin_branchmenu.php?branch=<?php echo "$branch"; ?>"><?php echo uiTextSnippet('labelmore'); ?></a>
        </p>

      </div>
    </div>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
</body>
</html>
