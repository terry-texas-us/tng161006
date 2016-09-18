<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

require 'adminlog.php';

if (!$allowEdit || !$allowDelete) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}

$deleteblankfamilies = 1;

$wherestr = '';

if ($assignedbranch) {
  $branchstr = " AND branch LIKE \"%$assignedbranch%\"";
} else {
  $branchstr = '';
}
$ldsOK = determineLDSRights();

function doRow($field, $textmsg, $boxname) {
  global $p1row;
  global $p2row;

  if ($field == 'living') {
    $p1field = isset($p1row[$field]) && $p1row[$field] ? 'yes' : 'No';
    $p2field = isset($p2row[$field]) && $p2row[$field] ? 'yes' : 'No';
  } else {
    $p1field = isset($p1row[$field]) ? $p1row[$field] : '';
    $p2field = isset($p2row[$field]) ? $p2row[$field] : '';
  }

  if ($p1field || $p2field) {
    echo "<tr>\n";
    echo "<td width='15%'><span>" . uiTextSnippet($textmsg) . ':</span></td>';
    echo "<td width=\"31%\"><span>$p1field&nbsp;</span></td>";
    if (is_array($p2row)) {
      echo "<td width='10'></td>";
      echo "<td width='15%'><span>" . uiTextSnippet($textmsg) . ':</span></td>';
      echo "<td width='5'><span>";
      //if it's a spouse and they're equal, do a hidden field for p1 & p2 and don't do the checkbox
      if ($textmsg == 'spouse') {
        if ($p1field && $p2field) {
          echo "<input name=\"xx$boxname\" type='hidden' value=\"$field\">";
        } elseif ($p2field) {
          echo "<input name=\"yy$boxname\" type='hidden' value=\"$field\">";
        }
      }
      if ($boxname) {
        if ($p2field || $textmsg != 'spouse') {
          echo "<input name=\"$boxname\" type='checkbox' value=\"$field\"";
          if ($p2row[$field] && !$p1row[$field]) {
            echo ' checked';
          }
          echo '>';
        } elseif ($textmsg == 'spouse') {
          echo "<input name=\"zz$boxname\" type='checkbox' value=\"$field\">";
        }
      } else {
        echo '&nbsp;';
      }
      echo '</span></td>';
      if (!$p2field) {
        $p2field = '<span class="msgerror">&laquo; ' . uiTextSnippet('chkdel') . '</span>';
      }
      echo "<td width=\"31%\"><span>$p2field&nbsp;</span></td>";
    } else {
      echo "<td width='10'></td>";
      echo "<td width='15%'><span>" . uiTextSnippet($textmsg) . ':</span></td>';
      echo "<td width='5'><span>&nbsp;</span></td>";
      echo '<td width="31%"><span>&nbsp;</span></td>';
    }
    echo "</tr>\n";
  }
}

function getEvent($event) {
  global $mylanguage, $languagesPath;

  $dispvalues = explode('|', $event['display']);
  $numvalues = count($dispvalues);
  if ($numvalues > 1) {
    $displayval = '';
    for ($i = 0; $i < $numvalues; $i += 2) {
      $lang = $dispvalues[$i];
      if ($mylanguage == $languagesPath . $lang) {
        $displayval = $dispvalues[$i + 1];
        break;
      }
    }
  } else {
    $displayval = $event['display'];
  }

  $eventstr = "<strong>$displayval</strong>: ";
  $eventstr2 = $event['eventdate'];
  if ($eventstr2 && $event['eventplace']) {
    $eventstr2 .= ', ';
  }
  $eventstr2 .= $event['eventplace'];
  if ($eventstr2 && $event['info']) {
    $eventstr2 .= '. ';
  }
  $eventstr2 .= $event['info'] . "<br>\n";
  $eventstr .= $eventstr2;

  return $eventstr;
}

function getSpouse($marriage, $spouse) {
  global $people_table;
  global $ldsOK;

  $spousestr = '';
  if ($marriage[$spouse]) {
    $query = "SELECT personID, lastname, firstname, prefix, suffix, nameorder FROM $people_table WHERE personID = \"{$marriage[$spouse]}\"";
    $gotspouse = tng_query($query);
    $spouserow = tng_fetch_assoc($gotspouse);

    $srights = determineLivingPrivateRights($fathrow);
    $spouserow['allow_living'] = $srights['living'];
    $spouserow['allow_private'] = $srights['private'];

    $spousestr .= getName($spouserow) . ' - ' . $spouserow['personID'] . " ({$marriage['familyID']})<br>\n";
    tng_free_result($gotspouse);
  } else {
    $spousestr = "({$marriage['familyID']})<br>\n";
  }
  if ($marriage['marrdate'] || $marriage['marrplace']) {
    $spousestr .= '<strong>' . uiTextSnippet('MARR') . "</strong>: {$marriage['marrdate']}";
    if ($marriage['marrdate'] && $marriage['marrplace']) {
      $spousestr .= ', ';
    }
    $spousestr .= "{$marriage['marrplace']}<br>\n";
  }
  if ($ldsOK) {
    if ($marriage['sealdate'] || $marriage['sealplace']) {
      $spousestr .= '<strong>' . uiTextSnippet('SLGS') . ":</strong> {$marriage['sealdate']}";
      if ($marriage['sealdate'] && $marriage['sealplace']) {
        $spousestr .= ', ';
      }
      $spousestr .= "{$marriage['sealplace']}<br>\n";
    }
  }
  return $spousestr;
}

function getParents($parent) {
  global $people_table;
  global $families_table;
  global $ldsOK;

  $parentstr = '';
  $query = "SELECT personID, lastname, firstname, prefix, suffix, nameorder FROM $people_table, $families_table WHERE $people_table.personID = $families_table.husband AND $families_table.familyID = \"{$parent['familyID']}\"";
  $gotfather = tng_query($query);

  if ($gotfather) {
    $fathrow = tng_fetch_assoc($gotfather);

    $frights = determineLivingPrivateRights($fathrow);
    $fathrow['allow_living'] = $frights['living'];
    $fathrow['allow_private'] = $frights['private'];

    $parentstr .= getName($fathrow) . ' - ' . $fathrow['personID'] . "<br>\n";
    tng_free_result($gotfather);
  }

  $query = "SELECT personID, lastname, firstname, prefix, suffix, nameorder FROM $people_table, $families_table WHERE $people_table.personID = $families_table.wife AND $families_table.familyID = \"{$parent['familyID']}\"";
  $gotmother = tng_query($query);

  if ($gotmother) {
    $mothrow = tng_fetch_assoc($gotmother);

    $mrights = determineLivingPrivateRights($mothrow);
    $mothrow['allow_living'] = $mrights['living'];
    $mothrow['allow_private'] = $mrights['private'];

    $parentstr .= getName($mothrow) . ' - ' . $mothrow['personID'] . "<br>\n";
    tng_free_result($gotmother);
  }
  if ($ldsOK) {
    if ($parent['sealdate'] || $parent['sealplace']) {
      $parentstr .= '<strong>' . uiTextSnippet('SLGC') . ':</strong> ' . $parent['sealdate'];
      if ($parent['sealdate'] && $parent['sealplace']) {
        $parentstr .= ', ';
      }
      $parentstr .= "{$parent['sealplace']}<br>\n";
    }
  }

  return $parentstr;
}

function addCriteria($row) {
  global $cfirstname, $clastname, $cbirthdate, $cbirthplace, $cdeathdate, $cdeathplace, $cignoreblanks, $csoundex;

  $criteria = '';
  $bsx = $csoundex ? 'SOUNDEX(' : '';
  $esx = $csoundex ? ')' : '';
  if ($cfirstname == 'yes') {
    $criteria .= " AND $bsx" . 'firstname' . "$esx = $bsx\"" . addslashes($row['firstname']) . "\"$esx";
    $criteria .= $cignoreblanks == 'yes' ? ' AND firstname != ""' : '';
  }
  if ($clastname == 'yes') {
    $criteria .= " AND $bsx" . 'lastname' . "$esx = $bsx\"" . addslashes($row['lastname']) . "\"$esx";
    $criteria .= $cignoreblanks == 'yes' ? ' AND lastname != ""' : '';
  }
  if ($cbirthdate == 'yes') {
    $criteria .= ' AND birthdate = "' . addslashes($row['birthdate']) . '"';
    $criteria .= $cignoreblanks == 'yes' ? ' AND birthdate != ""' : '';
  }
  if ($cbirthplace == 'yes') {
    $criteria .= ' AND birthplace = "' . addslashes($row['birthplace']) . '"';
    $criteria .= $cignoreblanks == 'yes' ? ' AND birthplace = ""' : '';
  }
  if ($cdeathdate == 'yes') {
    $criteria .= ' AND deathdate = "' . addslashes($row['deathdate']) . '"';
    $criteria .= $cignoreblanks == 'yes' ? ' AND deathdate != "" AND deathdate != "Y"' : '';
  }
  if ($cdeathplace == 'yes') {
    $criteria .= ' AND deathplace = "' . addslashes($row['deathplace']) . '"';
    $criteria .= $cignoreblanks == 'yes' ? ' AND deathplace = ""' : '';
  }

  return $criteria;
}

function doNotesCitations($persfam1, $persfam2, $varname) {
  global $ccombinenotes;

  if ($varname) {
    if ($varname == 'general') {
      $varname = '';
    }
    $wherestr = " AND eventID = \"$varname\"";
  } else {
    $wherestr = '';
  }
  if ($ccombinenotes != 'yes') {
    $query = "SELECT xnoteID FROM notelinks WHERE persfamID = '$persfam1' $wherestr";
    $noteresult = tng_query($query);
    while ($row = tng_fetch_assoc($noteresult)) {
      $query = "DELETE FROM xnotes WHERE ID=\"{$row['xnoteID']}\"";
      $noteresult = tng_query($query);
    }
    tng_free_result($noteresult);

    $query = "DELETE from notelinks WHERE persfamID = '$persfam1' $wherestr";
    tng_query($query);

    $query = "DELETE from citations WHERE persfamID = '$persfam1' $wherestr";
    tng_query($query);
  }
  $query = "UPDATE notelinks set persfamID = \"$persfam1\" WHERE persfamID = '$persfam2' $wherestr";
  tng_query($query);

  $query = "UPDATE citations set persfamID = \"$persfam1\" WHERE persfamID = '$persfam2' $wherestr";
  tng_query($query);
}

function doAssociations($personID1, $personID2) {
  $query = "UPDATE associations set personID = \"$personID1\" WHERE personID = '$personID2'";
  tng_query($query);

  $query = "UPDATE associations set passocID = \"$personID1\" WHERE personID = '$personID2'";
  tng_query($query);
}

function delAssociations($entity) {
  $query = "DELETE FROM associations WHERE personID = '$entity'";
  tng_query($query);

  $query = "DELETE FROM associations WHERE passocID = '$entity'";
  tng_query($query);
}

$p1row = $p2row = '';
if ($personID1) {
  $query = "SELECT *, DATE_FORMAT(changedate,\"%d %b %Y %H:%i:%s\") AS changedate FROM $people_table WHERE personID = '$personID1'";
  $result = tng_query($query);
  if ($result && tng_num_rows($result)) {
    $p1row = tng_fetch_assoc($result);
    $p1row['name'] = getName($p1row);
    tng_free_result($result);
  } else {
    $personID1 = $personID2 = '';
  }
}

set_time_limit(0);
if (!$mergeaction) {
  $cfirstname = 'yes';
  $clastname = 'yes';
  $ccombinenotes = 'yes';
  $ccombineextras = 'yes';
}
if ($mergeaction == uiTextSnippet('nextmatch') || $mergeaction == uiTextSnippet('nextdup')) {
  if ($mergeaction == uiTextSnippet('nextmatch')) {
    $wherestr2 = $personID2 ? " AND personID > \"$personID2\"" : '';
    $wherestr2 .= $personID1 ? " AND personID > \"$personID1\"" : '';

    $wherestr = $personID1 ? "AND personID > \"$personID1\"" : '';
    $largechunk = 1000;
    $nextchunk = -1;
    $numrows = 0;
    $still_looking = 1;
    $personID2 = '';

    do {
      $nextone = $nextchunk + 1;
      $nextchunk += $largechunk;

      $query = "SELECT * FROM $people_table WHERE 1 $branchstr $wherestr ORDER BY personID, lastname, firstname LIMIT $nextone, $largechunk";
      $result = tng_query($query);
      $numrows = tng_num_rows($result);
      if ($result && $numrows) {
        while ($still_looking && $row = tng_fetch_assoc($result)) {
          //echo "compare $row['firstname'] $row['lastname']<br>\n";
          $wherestr2 = addCriteria($row);

          $query = "SELECT * FROM $people_table WHERE personID > \"{$row['personID']}\" $branchstr $wherestr2 ORDER BY personID, lastname, firstname LIMIT 1";
          //echo "q2: $query<br>\n";
          $result2 = tng_query($query);
          if ($result2 && tng_num_rows($result2)) {
            //set personID1, personID2
            $p1row = $row;
            $personID1 = $p1row['personID'];
            $p2row = tng_fetch_assoc($result2);
            //echo "found $p2row['firstname'] $p2row['lastname']<br>\n";
            $personID2 = $p2row['personID'];
            tng_free_result($result2);
            $still_looking = 0;
          }
        }
        tng_free_result($result);
      }
    } while ($numrows && $still_looking);
    if (!$personID2) {
      $personID1 = $p1row = '';
    }
  } else {
    //search with personID1 for next duplicate
    $wherestr2 = $personID2 ? " AND personID > \"$personID2\"" : '';
    $wherestr2 .= addCriteria($p1row);

    $query = "SELECT * FROM $people_table WHERE personID != \"{$p1row['personID']}\" $branchstr $wherestr2 ORDER BY personID, lastname, firstname LIMIT 1";
    $result2 = tng_query($query);
    if ($result2 && tng_num_rows($result2)) {
      $p2row = tng_fetch_assoc($result2);
      $personID2 = $p2row['personID'];
      $p2row['name'] = getName($p2row);
      tng_free_result($result2);
    } else {
      $personID2 = '';
    }
  }
} elseif ($personID2) {
  $query = "SELECT *, DATE_FORMAT(changedate,\"%d %b %Y %H:%i:%s\") AS changedate FROM $people_table WHERE personID = '$personID2'";
  $result2 = tng_query($query);
  if ($result2 && tng_num_rows($result2) && $personID1 != $personID2) {
    $p2row = tng_fetch_assoc($result2);
    $personID2 = $p2row['personID'];
    tng_free_result($result2);
  } else {
    $mergeaction = uiTextSnippet('comprefresh');
    $personID2 = '';
  }
}
if ($mergeaction == uiTextSnippet('merge')) {
  $updatestr = '';
  $prifamily = 0;

  if ($p1row['sex'] == 'M') {
    $p1spouse = 'wife';
    $p1self = 'husband';
    $p1spouseorder = 'husborder';
  } elseif ($p1row['sex'] == 'F') {
    $p1spouse = 'husband';
    $p1self = 'wife';
    $p1spouseorder = 'wifeorder';
  } else {
    $p1spouse = '';
    $p1self = '';
    $p1spouseorder = '';
  }

  foreach ($_POST as $key => $value) {
    $prefix = substr($key, 0, 2);
    switch ($prefix) {
      case 'p2':
        $varname = substr($key, 2);
        $p1row[$varname] = $p2row[$varname];
        $updatestr .= ", $varname = \"{$p1row[$varname]}\" ";
        if (strpos($varname, 'date')) {
          $truevar = $varname . 'tr';
          $p1row[$truevar] = $p2row[$truevar];
          $updatestr .= ", $truevar = \"{$p1row[$truevar]}\" ";
        } elseif ($varname == 'firstname' || $varname == 'lastname') {
          $varname = 'NAME';
        }
        doNotesCitations($personID1, $personID2, $varname);
        break;
      case 'ev':
        if (strpos($key, '::')) {
          $halves = explode('::', substr($key, 5));
          $varname = substr(strstr($halves[1], '_'), 1);
          $query = "DELETE from events WHERE persfamID = '$personID1' and eventID = \"$varname\"";
          $evresult = tng_query($query);
          $varname = $halves['0'] != 'event' ? substr(strstr($halves['0'], '_'), 1) : '';
        } else {
          $varname = substr(strstr($key, '_'), 1);
        }
        if ($varname) {
          $query = "SELECT eventID FROM events WHERE persfamID = '$personID2' AND eventID = \"$varname\"";
          $evresult = tng_query($query);
          while ($evrow = tng_fetch_assoc($evresult)) {
            doNotesCitations($personID1, $personID2, $evrow['eventID']);
          }
          tng_free_result($evresult);

          $query = "UPDATE events set persfamID = '$personID1' WHERE persfamID = '$personID2' AND eventID = \"$varname\"";
          $evresult = tng_query($query);
        }
        break;
      case 'pa':
        $varname = substr($key, 7);
        $query = "DELETE from children WHERE personID = '$personID1' and familyID = \"$varname\"";
        $evresult = tng_query($query);

        //if not selected, delete child record for person 2

        $query = "UPDATE children set personID = '$personID1' WHERE personID = '$personID2' AND familyID = \"$varname\"";
        $evresult = tng_query($query);
        if (!$prifamily) {
          $updatestr .= ", famc = \"$varname\" ";
          $prifamily = 1;
        }
        break;
      case 'xx':
        $samespouse = substr($key, 2);
        //remove family on right, but move children to left
        if (!$_POST[$samespouse] && $p1self) {
          $varname = substr($key, 8);

          //delete family on right (important to do deleting first)
          $query = "DELETE from $families_table WHERE familyID = '$varname'";
          $famresult = tng_query($query);

          //get families where left person is the husband/wife and right SPOUSE is the wife/husband
          $query = "SELECT familyID FROM $families_table WHERE $p1self = \"$personID1\" AND $p1spouse = \"" . substr($value, 6) . '"';
          $sp1result = tng_query($query);
          $sp1row = tng_fetch_assoc($sp1result);
          tng_free_result($sp1result);

          delAssociations($varname);

          if ($ccombineextras) {
            $query = "UPDATE medialinks set personID = \"{$sp1row['familyID']}\" WHERE personID = '$varname'";
          } else {
            $query = "DELETE FROM medialinks WHERE personID = '$varname'";
          }
          $mediaresult = tng_query($query);

          //update all people records where FAMC = the deleted family, set FAMC = family on left
          $query = "UPDATE $people_table set famc = \"{$sp1row['familyID']}\" WHERE famc = '$varname'";
          $paresult = tng_query($query);

          //move kids from right family to left
          $query = "UPDATE children set familyID = \"{$sp1row['familyID']}\" WHERE familyID = '$varname'";
          $chilresult = tng_query($query);
          if (!$chilresult) {
            $query = "DELETE FROM children WHERE familyID = '$varname'";
            $chilresult = tng_query($query);
          }

          if ($ccombinenotes && $varname) {
            doNotesCitations($sp1row['familyID'], $varname, '');
          }

          $query = "UPDATE events set persfamID = \"{$sp1row['familyID']}\" WHERE persfamID = '$varname'";
          $evresult = tng_query($query);
        }
        break;
      case 'yy':
        $samespouse = substr($key, 2);
        //basically, we're keeping the right family, but we're removing the right person as a spouse. Corresponding box was not checked, so it's not merging left.
        if (!$_POST[$samespouse] && $p1self) {
          $varname = substr($key, 8);

          $query = "UPDATE $families_table set $p1self = \"\", $p1spouseorder = \"\" WHERE familyID = '$varname'";
          $chilresult = tng_query($query);
        }
        break;
      case 'zz':
        $varname = substr($key, 8);

        //remove left person from family.
        $query = "UPDATE $families_table set $p1self = \"\", $p1spouseorder = \"\" WHERE familyID = '$varname'";
        $chilresult = tng_query($query);
        break;
      case 'sp':
        $xx = "xx$key";
        if ($p1self) {
          if ($_POST[$xx]) {
            $varname = substr($key, 6);

            //same spouse, box checked, so we're removing LEFT family, moving kids over to left family
            //get families where left person is the husband/wife and right SPOUSE is the wife/husband
            $query = "SELECT familyID, $p1spouseorder FROM $families_table WHERE $p1self = \"$personID1\" AND $p1spouse = \"" . substr($value, 6) . '"';
            $sp1result = tng_query($query);
            $sp1row = tng_fetch_assoc($sp1result);
            tng_free_result($sp1result);

            $query = "UPDATE $families_table set $p1self = \"$personID1\", $p1spouseorder = \"{$sp1row[$p1spouseorder]}\" WHERE familyID = '$varname'";
            $chilresult = tng_query($query);

            //delete family on LEFT
            $query = "DELETE from $families_table WHERE familyID = \"{$sp1row['familyID']}\"";
            $famresult = tng_query($query);

            doAssociations($varname, $sp1row['familyID']);

            if ($ccombineextras) {
              $query = "UPDATE medialinks set personID = \"$varname\" WHERE personID = \"{$sp1row['familyID']}\"";
            } else {
              $query = "DELETE FROM medialinks WHERE personID = \"{$sp1row['familyID']}\"";
            }
            $mediaresult = tng_query($query);

            //update all people records where FAMC = the deleted family, set FAMC = family on right
            $query = "UPDATE $people_table set famc = \"$varname\" WHERE famc = \"{$sp1row['familyID']}\"";
            $paresult = tng_query($query);

            //move all children from family1 to family2
            $query = "UPDATE children set familyID = \"$varname\" WHERE familyID = \"{$sp1row['familyID']}\"";
            $chilresult = tng_query($query);
            if (!$chilresult) {
              $query = "DELETE FROM children WHERE familyID = \"{$sp1row['familyID']}\"";
              $chilresult = tng_query($query);
            }

            if ($ccombinenotes && $sp1row['familyID']) {
              doNotesCitations($varname, $sp1row['familyID'], '');
            }

            $query = "UPDATE events set persfamID = \"{$sp1row['familyID']}\" WHERE persfamID = '$varname'";
            $evresult = tng_query($query);
          } else {
            //this means spouses are different, the box has been checked, so they want to keep the right spouse + family
            $varname = substr($key, 6);

            //get families where right person is married to right spouse
            $query = "SELECT familyID FROM $families_table WHERE $p1self = \"$personID2\" AND $p1spouse = \"$varname\"";
            $sp1result = tng_query($query);
            $sp1row = tng_fetch_assoc($sp1result);
            tng_free_result($sp1result);

            //get spouse order for left person, add one
            $query = "SELECT $p1spouseorder FROM $families_table WHERE $p1self = \"$personID1\" ORDER BY $p1spouseorder DESC";
            $spresult = tng_query($query);
            $sprow = tng_fetch_assoc($spresult);
            tng_free_result($spresult);
            $sporder = $sprow[$p1spouseorder] + 1;

            //update those families to have left person married to right spouse, change spouse order
            $query = "UPDATE $families_table set $p1self = \"$personID1\", $p1spouseorder = \"$sporder\" WHERE familyID = '$varname'";
            $chilresult = tng_query($query);
          }
        }
        break;
    }
  }
  if ($ccombinenotes) {
    doNotesCitations($personID1, $personID2, 'general');

    //convert all remaining notes and citations
    $query = "UPDATE notelinks set persfamID = \"$personID1\" WHERE persfamID = '$personID2'";
    $noteresult = tng_query($query);

    $query = "UPDATE citations set persfamID = \"$personID1\" WHERE persfamID = '$personID2'";
    $citeresult = tng_query($query);
  }
  if ($updatestr) {
    $updatestr = substr($updatestr, 2);
    $newdate = date('Y-m-d H:i:s', time() + (3600 * $timeOffset));
    $updatestr .= ", changedate = \"$newdate\"";
    $query = "UPDATE $people_table set $updatestr WHERE personID = '$personID1'";
    $combresult = tng_query($query);
  }

  $query = "DELETE from $people_table WHERE personID = '$personID2'";
  $combresult = tng_query($query);

  //delete remaining notes, citations & events for person 2
  $query = "DELETE from events WHERE persfamID = '$personID2'";
  $combresult = tng_query($query);

  $query = "SELECT xnoteID FROM notelinks WHERE persfamID = '$persfam2'";
  $noteresult = tng_query($query);
  while ($row = tng_fetch_assoc($noteresult)) {
    $query = "DELETE FROM xnotes WHERE ID=\"{$row['xnoteID']}\"";
    $noteresult2 = tng_query($query);
  }
  tng_free_result($noteresult);

  $query = "DELETE from notelinks WHERE persfamID = '$personID2'";
  $combresult = tng_query($query);

  $query = "DELETE from citations WHERE persfamID = '$personID2'";
  $combresult = tng_query($query);

  doAssociations($personID1, $personID2);

  //update families: remove person2 as spouse from all families
  if ($p1self) {
    $query = "UPDATE $families_table set $p1self = \"\", $p1spouseorder = \"0\" WHERE $p1self = '$personID2'";
    $chilresult = tng_query($query);
  }

  //remove person2 from children table
  $query = "DELETE FROM children WHERE personID = '$personID2'";
  $chilresult = tng_query($query);

  //construct name for default photo 2
  $defaultphoto2 = "$rootpath$photopath/$personID2.$photosext";
  if ($ccombineextras) {
    $query = "UPDATE medialinks set personID = \"$personID1\", defphoto = \"\" WHERE personID = '$personID2'";
    $mediaresult = tng_query($query);

    //construct name for default photo 1
    if (file_exists($defaultphoto2)) {
      $defaultphoto1 = "$rootpath$photopath/$personID1.$photosext";
      if (!file_exists($defaultphoto1)) {
        rename($defaultphoto2, $defaultphoto1);
      }
      //else
      //unlink( $defaultphoto2 );
    }
  } else {
    $query = "DELETE FROM medialinks WHERE personID = '$personID2'";
    $mediaresult = tng_query($query);
  }
  $personID2 = '';
  $p2row = '';

  //clean up: remove all families with husband blank and wife blank
  //remove all children from those families
  if ($deleteblankfamilies) {
    $query = "SELECT familyID FROM $families_table WHERE husband = \"\" AND wife = \"\"";
    $blankfams = tng_query($query);
    while ($blankrow = tng_fetch_assoc($blankfams)) {
      $query = "DELETE FROM children WHERE familyID = \"{$blankrow['familyID']}\"";
      $chilresult = tng_query($query);

      $query = "UPDATE $people_table SET famc=\"\" WHERE famc = \"{$blankrow['familyID']}\"";
      $result2 = tng_query($query);
    }
    tng_free_result($blankfams);
    $query = "DELETE FROM $families_table WHERE husband = \"\" AND wife = \"\"";
    $famresult = tng_query($query);
  }
  adminwritelog(uiTextSnippet('merge') . ": $personID2 => $personID1");
}
$revstar = checkReview('I');

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('merge'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="people-merge">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('people-merge', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'peopleBrowse.php', uiTextSnippet('browse'), 'findperson']);
    $navList->appendItem([$allowAdd, 'peopleAdd.php', uiTextSnippet('add'), 'addperson']);
    $navList->appendItem([$allowEdit, 'admin_findreview.php?type=I', uiTextSnippet('review') . $revstar, 'review']);
    //    $navList->appendItem([$allowEdit && $allowDelete, 'peopleMerge.php', uiTextSnippet('merge'), 'merge']);
    echo $navList->build('merge');
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <h4><?php echo uiTextSnippet('findmatches'); ?></h4>
          <div><em><?php echo uiTextSnippet('choosemerge'); ?></em><br><br>
            <form action="peopleMerge.php" method='post' name='form1' id='form1'>
              <br>
              <table>
                <tr>
                  <td>
                    <div style="float:left">
                      <?php echo uiTextSnippet('personid'); ?> 1: <input type='text'
                                                                         name="personID1"
                                                                         id="personID1" size='10'
                                                                         value="<?php echo $personID1; ?>">
                      &nbsp;<?php echo uiTextSnippet('or'); ?>&nbsp;
                    </div>
                    <a href="#" onclick="return findItem('I', 'personID1', 'name1', '<?php echo $assignedbranch; ?>');"
                       title="<?php echo uiTextSnippet('find'); ?>">
                      <img class='icon-sm' src='svg/magnifying-glass.svg'>
                    </a>
                  </td>
                  <td width="80">&nbsp;</td>
                  <td>
                    <div style="float:left">
                      <?php echo uiTextSnippet('personid'); ?> 2: <input type='text'
                                                                         name="personID2"
                                                                         id="personID2" size='10'
                                                                         value="<?php echo $personID2; ?>"
                                                                         onkeyup="processEnter();">
                      &nbsp;<?php echo uiTextSnippet('or'); ?>&nbsp;
                    </div>
                    <a href="#" onclick="return findItem('I', 'personID2', 'name2', '<?php echo $assignedbranch; ?>');"
                       title="<?php echo uiTextSnippet('find'); ?>">
                      <img class='icon-sm' src='svg/magnifying-glass.svg'>
                    </a>
                  </td>
                </tr>
                <tr>
                  <td id="name1"><?php if (isset($p1row['reponame'])) {echo truncateIt($r1row['reponame'], 75);} ?></td>
                  <td width="80"></td>
                  <td id="name2"><?php if (isset($p2row['reponame'])) {echo truncateIt($r2row['reponame'], 75);} ?></td>
                </tr>
              </table>
              <br>
              <table>
                <tr>
                  <td colspan='5'>
                    <strong><?php echo uiTextSnippet('matchthese'); ?></strong>
                  </td>
                  <td></td>
                  <td colspan='3'>
                    <span><strong><?php echo uiTextSnippet('otheroptions'); ?></strong></span></td>
                </tr>
                <tr>
                  <td>
                    <span>
                      <input name='cfirstname' type='checkbox' value='yes'<?php if ($cfirstname) {echo ' checked';} ?>> <?php echo uiTextSnippet('firstname'); ?>
                      <br>
                      <input name='clastname' type='checkbox' value='yes'<?php if ($clastname) {echo ' checked';} ?>> <?php echo uiTextSnippet('lastname'); ?>
                    </span>
                  </td>
                  <td></td>
                  <td>
                    <span>
                      <input name='cbirthdate' type='checkbox' value='yes'<?php if ($cbirthdate == 'yes') {echo ' checked';} ?>> <?php echo uiTextSnippet('birthdate'); ?>
                      <br>
                      <input name='cbirthplace' type='checkbox' value='yes'<?php if ($cbirthplace == 'yes') {echo ' checked';} ?>> <?php echo uiTextSnippet('birthplace'); ?>
                    </span>
                  </td>
                  <td></td>
                  <td>
                    <span>
                      <input name='cdeathdate' type='checkbox' value='yes'<?php if ($cdeathdate == 'yes') {echo ' checked';} ?>> <?php echo uiTextSnippet('deathdate'); ?>
                      <br>
                      <input name='cdeathplace' type='checkbox' value='yes'<?php if ($cdeathplace == 'yes') {echo ' checked';} ?>> <?php echo uiTextSnippet('deathplace'); ?>
                    </span>
                  </td>
                  <td></td>
                  <td>
                    <span>
                      <input name='cignoreblanks' type='checkbox' value='yes'<?php if ($cignoreblanks == 'yes') {echo ' checked';} ?>> <?php echo uiTextSnippet('ignoreblanks'); ?>
                      <br>
                      <input name='csoundex' type='checkbox' value='yes'<?php if ($csoundex == 'yes') {echo ' checked';} ?>> <?php echo uiTextSnippet('usesoundex'); ?>*
                    </span>
                  </td>
                  <td></td>
                  <td>
                    <span>
                      <input name='ccombinenotes' type='checkbox' value='yes'<?php if ($ccombinenotes == 'yes') {echo ' checked';} ?>> <?php echo uiTextSnippet('combinenotes'); ?>
                      <br>
                      <input name='ccombineextras' type='checkbox' value='yes'<?php if ($ccombineextras == 'yes') {echo ' checked';} ?>> <?php echo uiTextSnippet('combineextras'); ?>
                    </span>
                  </td>
                </tr>
              </table>
              <br>
              <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('nextmatch'); ?>">
              <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('nextdup'); ?>">
              <input id='compref' name='mergeaction' type='submit' value="<?php echo uiTextSnippet('comprefresh'); ?>">
              <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('mswitch'); ?>"
                     onClick="document.form1.mergeaction.value = '<?php echo uiTextSnippet('comprefresh'); ?>'; return switchpeople();">
              <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('merge'); ?>"
                     onClick="return validateForm();">
              <br><br>
              <table>
                <?php
                if (is_array($p1row)) {
                  $parentsets = [];
                  $spouses = [];
                  $eventlist = [];
                  echo "<tr>\n";
                  echo "<td colspan='3'><input type='button' value=\"" . uiTextSnippet('edit') . "\" onClick=\"deepOpen('peopleEdit.php?personID={$p1row['personID']}&amp;cw=1','edit')\"></td>\n";
                  if (is_array($p2row)) {
                    echo "<td colspan=\"3\"><input type='button' value=\"" . uiTextSnippet('edit') . "\" onClick=\"deepOpen('peopleEdit.php?personID={$p2row['personID']}&amp;cw=1','edit')\"></td>\n";

                    $query = "SELECT display, eventdate, eventplace, info, events.eventtypeID AS eventtypeID, events.eventID AS eventID FROM events, eventtypes WHERE persfamID = \"{$p2row['personID']}\" AND events.eventtypeID = eventtypes.eventtypeID ORDER BY ordernum";
                    $evresult = tng_query($query);
                    $eventcount = tng_num_rows($evresult);

                    if ($evresult && $eventcount) {
                      while ($event = tng_fetch_assoc($evresult)) {
                        $ekey = strtoupper("{$event['eventtypeID']}_{$event['eventdate']}_{$event['eventplace']}_" . substr($event['info'], 0, 100));
                        $ekey = preg_replace('/\"/', '', $ekey);
                        $ename = "event$ekey";
                        $p2row[$ename] .= getEvent($event);
                        $eventlist[$ekey] = "{$event['eventtypeID']}_{$event['eventID']}";
                      }
                      tng_free_result($evresult);
                    }

                    $query = "SELECT personID, familyID, sealdate, sealplace FROM children WHERE personID = \"{$p2row['personID']}\"";
                    $parents2 = tng_query($query);

                    if ($parents2 && tng_num_rows($parents2)) {
                      while ($parent = tng_fetch_assoc($parents2)) {
                        $pname = 'parents' . $parent['familyID'];
                        $p2row[$pname] = getParents($parent);
                        if (!in_array($parent['familyID'], $parentsets)) {
                          array_push($parentsets, $parent['familyID']);
                        }
                      }
                    }

                    if ($p2row['sex']) {
                      if ($p2row['sex'] == 'M') {
                        $p2spouse = 'wife';
                        $p2self = 'husband';
                        $p2spouseorder = 'husborder';
                      } elseif ($p2row['sex'] == 'F') {
                        $p2spouse = 'husband';
                        $p2self = 'wife';
                        $p2spouseorder = 'wifeorder';
                      } else {
                        $p2spouse = '';
                        $p2self = '';
                        $p2spouseorder = '';
                      }

                      if ($p2self) {
                        $query = "SELECT $p2spouse, familyID, marrdate, marrplace, sealdate, sealplace FROM $families_table WHERE $families_table.$p2self = \"{$p2row['personID']}\" ORDER BY $p2spouseorder";
                        $marriages2 = tng_query($query);

                        while ($marriage = tng_fetch_assoc($marriages2)) {
                          $mname = "spouse$marriage[$p2spouse]";
                          $p2row[$mname] = getSpouse($marriage, $p2spouse);
                          if (!in_array($marriage[$p2spouse], $spouses)) {
                            array_push($spouses, $marriage[$p2spouse]);
                            $marriages[$marriage[$p2spouse]] = $marriage['familyID'];
                          }
                        }
                      }
                    }
                  }
                  echo "</tr>\n";
                  doRow('personID', 'personid', '');
                  doRow('firstname', 'givennames', 'p2firstname');
                  if ($lnprefixes) {
                    doRow('lnprefix', 'lnprefix', 'p2lnprefix');
                  }
                  doRow('lastname', 'surname', 'p2lastname');
                  doRow('nickname', 'nickname', 'p2nickname');
                  doRow('prefix', 'prefix', 'p2prefix');
                  doRow('suffix', 'suffix', 'p2suffix');
                  doRow('title', 'title', 'p2title');
                  doRow('living', 'living', 'p2living');
                  doRow('birthdate', 'birthdate', 'p2birthdate');
                  doRow('birthplace', 'birthplace', 'p2birthplace');
                  doRow('sex', 'sex', 'p2sex');
                  doRow('altbirthdate', 'chrdate', 'p2altbirthdate');
                  doRow('altbirthplace', 'chrplace', 'p2altbirthplace');
                  doRow('deathdate', 'deathdate', 'p2deathdate');
                  doRow('deathplace', 'deathplace', 'p2deathplace');
                  doRow('burialdate', 'burialdate', 'p2burialdate');
                  doRow('burialplace', 'burialplace', 'p2burialplace');
                  if ($ldsOK) {
                    doRow('baptdate', 'bapldate', 'p2baptdate');
                    doRow('baptplace', 'baplplace', 'p2baptplace');
                    doRow('confdate', 'confdate', 'p2confdate');
                    doRow('confplace', 'confplace', 'p2confplace');
                    doRow('initdate', 'initdate', 'p2initdate');
                    doRow('initplace', 'initplace', 'p2initplace');
                    doRow('endldate', 'endldate', 'p2endldate');
                    doRow('endlplace', 'endlplace', 'p2endlplace');
                  }
                  $query = "SELECT display, eventdate, eventplace, info, events.eventtypeID AS eventtypeID, events.eventID AS eventID FROM events, eventtypes WHERE persfamID = \"{$p1row['personID']}\" AND events.eventtypeID = eventtypes.eventtypeID ORDER BY ordernum";
                  $evresult = tng_query($query);
                  $eventcount = tng_num_rows($evresult);

                  if ($evresult && $eventcount) {
                    while ($event = tng_fetch_assoc($evresult)) {
                      $ekey = strtoupper("{$event['eventtypeID']}_{$event['eventdate']}_{$event['eventplace']}_" . substr($event['info'], 0, 100));
                      $ekey = preg_replace('/\"/', '', $ekey);
                      $ename = "event$ekey";
                      $p1row[$ename] .= getEvent($event);
                      if ($eventlist[$ekey]) {
                        $eventlist[$ekey] .= '::' . "{$event['eventtypeID']}_{$event['eventID']}";
                      } else {
                        $eventlist[$ekey] = '::' . "{$event['eventtypeID']}_{$event['eventID']}";
                      }
                    }
                    tng_free_result($evresult);
                  }

                  foreach ($eventlist as $key => $event) {
                    //need to pass the eventtype + eventID as the key, perhaps as double key separated by ::
                    //key may only need to be "event" + sequence number
                    $ename = "event$key";
                    $inputname = "event$event";

                    doRow($ename, 'otherevents', $inputname);
                  }

                  $query = "SELECT personID, familyID, sealdate, sealplace FROM children WHERE personID = \"{$p1row['personID']}\"";
                  $parents1 = tng_query($query);

                  if ($parents1 && tng_num_rows($parents1)) {
                    while ($parent = tng_fetch_assoc($parents1)) {
                      $pname = 'parents' . $parent['familyID'];
                      $p1row[$pname] = getParents($parent);
                      if (!in_array($parent['familyID'], $parentsets)) {
                        array_push($parentsets, $parent['familyID']);
                      }
                    }
                  }

                  foreach ($parentsets as $parentset) {
                    $pname = "parents$parentset";
                    $inputname = "parents$parentset";
                    doRow($pname, 'parents', $inputname);
                  }

                  if ($p1row['sex']) {
                    if ($p1row['sex'] == 'M') {
                      $p1spouse = 'wife';
                      $p1self = 'husband';
                      $p1spouseorder = 'husborder';
                    } elseif ($p1row['sex'] == 'F') {
                      $p1spouse = 'husband';
                      $p1self = 'wife';
                      $p1spouseorder = 'wifeorder';
                    } else {
                      $p1spouse = '';
                      $p1self = '';
                      $p1spouseorder = '';
                    }

                    if ($p1self) {
                      $query = "SELECT $p1spouse, familyID, marrdate, marrplace, sealdate, sealplace FROM $families_table WHERE $families_table.$p1self = \"{$p1row['personID']}\" ORDER BY $p1spouseorder";
                      $marriages1 = tng_query($query);

                      while ($marriage = tng_fetch_assoc($marriages1)) {
                        $mname = 'spouse' . $marriage[$p1spouse];
                        $p1row[$mname] = getSpouse($marriage, $p1spouse);
                        if (!in_array($marriage[$p1spouse], $spouses)) {
                          array_push($spouses, $marriage[$p1spouse]);
                          $marriages[$marriage[$p1spouse]] = $marriage['familyID'];
                        }
                      }
                    }
                  }

                  foreach ($spouses as $nextspouse) {
                    $mname = "spouse$nextspouse";
                    $inputname = 'spouse' . $marriages[$nextspouse];
                    doRow($mname, 'spouse', $inputname);
                  }
                } else {
                  echo '<tr><td>' . uiTextSnippet('nomatches') . '</td></tr>';
                }
                ?>
              </table>
              <br>
              <?php
              if ($personID1 || $personID2) {
                ?>
                <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('nextmatch'); ?>">
                <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('nextdup'); ?>">
                <input type='submit' value="<?php echo uiTextSnippet('comprefresh'); ?>"
                       name="mergeaction">
                <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('mswitch'); ?>"
                       onClick="document.form1.mergeaction.value = '<?php echo uiTextSnippet('comprefresh'); ?>'; return switchpeople();">
                <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('merge'); ?>"
                       onClick="return validateForm();">
                <?php
              }
              ?>
            </form>
            <br>
            <span style="font-size: 8pt;">*<?php echo uiTextSnippet('sdxdisclaimer'); ?></span>
          </div>
        </td>
      </tr>

    </table>

    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/admin.js"></script>
  <script src="js/selectutils.js"></script>
  <script>
    function validateForm() {
      var rval = true;

      if (document.form1.personID1.value === '' || document.form1.personID2.value === '' || document.form1.personID1.value === document.form1.personID2.value)
        rval = false;
      else
        rval = confirm(textSnippet('confirmmerge'));

      return rval;
    }

    function switchpeople() {
      var formname = document.form1;

      if (formname.personID1.value && formname.personID2.value) {
        var temp = formname.personID1.value;

        formname.personID1.value = formname.personID2.value;
        formname.personID2.value = temp;

        return true;
      } else
        return false;

    }

    function processEnter() {
      var keycode;
      if (event)
        keycode = event.keyCode;
      else if (e)
        keycode = e.which;
      else
        return true;

      if (keycode === 13) {
        event.preventDefault();
        event.stopPropagation();
        $('#compref').click();
        return false;
      }
    }
  </script>
</body>
</html>
