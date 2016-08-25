<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

require 'adminlog.php';
require 'datelib.php';

require 'geocodelib.php';

$query = "SELECT branch, edituser, edittime FROM $people_table WHERE personID = '$personID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

if ((!$allowEdit && (!$allowAdd || !$added)) || !checkbranch($row['branch'])) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$editconflict = determineConflict($row, $people_table);

if (!$editconflict) {
  if ($newfamily == "ajax" && $session_charset != "UTF-8") {
    $firstname = tng_utf8_decode($firstname);
    $lastname = tng_utf8_decode($lastname);
    $lnprefix = tng_utf8_decode($lnprefix);
    $nickname = tng_utf8_decode($nickname);
    $prefix = tng_utf8_decode($prefix);
    $suffix = tng_utf8_decode($suffix);
    $title = tng_utf8_decode($title);
    $birthplace = tng_utf8_decode($birthplace);
    $altbirthplace = tng_utf8_decode($altbirthplace);
    $deathplace = tng_utf8_decode($deathplace);
    $burialplace = tng_utf8_decode($burialplace);
    $baptplace = tng_utf8_decode($baptplace);
    $confplace = tng_utf8_decode($confplace);
    $initplace = tng_utf8_decode($initplace);
    $endlplace = tng_utf8_decode($endlplace);
  }
  $firstname = addslashes($firstname);
  $lastname = addslashes($lastname);
  $lnprefix = addslashes($lnprefix);
  $nickname = addslashes($nickname);
  $prefix = addslashes($prefix);
  $suffix = addslashes($suffix);
  $title = addslashes($title);
  $birthplace = addslashes($birthplace);
  $altbirthplace = addslashes($altbirthplace);
  $deathplace = addslashes($deathplace);
  $burialplace = addslashes($burialplace);
  $baptplace = addslashes($baptplace);
  $confplace = addslashes($confplace);
  $initplace = addslashes($initplace);
  $endlplace = addslashes($endlplace);

  $birthdatetr = convertDate($birthdate);
  $altbirthdatetr = convertDate($altbirthdate);
  $deathdatetr = convertDate($deathdate);
  $burialdatetr = convertDate($burialdate);
  $baptdatetr = convertDate($baptdate);
  $confdatetr = convertDate($confdate);
  $initdatetr = convertDate($initdate);
  $endldatetr = convertDate($endldate);

  $newdate = date("Y-m-d H:i:s", time() + (3600 * $timeOffset));

  if (is_array($branch)) {
    foreach ($branch as $b) {
      if ($b) {
        $allbranches = $allbranches ? "$allbranches,$b" : $b;
      }
    }
  } else {
    $allbranches = $branch;
    $branch = [$branch];
  }

  if ($allbranches != $orgbranch) {
    $oldbranches = explode(",", $orgbranch);
    foreach ($oldbranches as $b) {
      if ($b && !in_array($b, $branch)) {
        $query = "DELETE FROM $branchlinks_table WHERE persfamID = '$personID' AND branch = \"$b\"";
        $result = tng_query($query);
      }
    }
    foreach ($branch as $b) {
      if ($b && !in_array($b, $oldbranches)) {
        $query = "INSERT IGNORE INTO $branchlinks_table (branch, persfamID) VALUES('$b', '$personID')";
        $result = tng_query($query);
      }
    }
  }
  $places = [];
  if (trim($birthplace) && !in_array($birthplace, $places)) {
    array_push($places, $birthplace);
  }
  if (trim($altbirthplace) && !in_array($altbirthplace, $places)) {
    array_push($places, $altbirthplace);
  }
  if (trim($deathplace) && !in_array($deathplace, $places)) {
    array_push($places, $deathplace);
  }
  if (trim($burialplace) && !in_array($burialplace, $places)) {
    array_push($places, $burialplace);
  }
  if (trim($baptplace) && !in_array($baptplace, $places)) {
    array_push($places, $baptplace);
  }
  if (trim($confplace) && !in_array($conftplace, $places)) {
    array_push($places, $confplace);
  }
  if (trim($initplace) && !in_array($initplace, $places)) {
    array_push($places, $initplace);
  }
  if (trim($endlplace) && !in_array($endlplace, $places)) {
    array_push($places, $endlplace);
  }
  foreach ($places as $place) {
    $query = "INSERT IGNORE INTO $places_table (place, placelevel, zoom, geoignore) VALUES ('$place', '0', '0', '0')";
    $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
    if ($tngconfig['autogeo'] && tng_affected_rows()) {
      $ID = tng_insert_id();
      $message = geocode($place, 0, $ID);
    }
  }
  $query = "SELECT familyID FROM $children_table WHERE personID = '$personID'";
  $parents = tng_query($query);

  $famc = "";
  if ($parents && tng_num_rows($parents)) {
    while ($parent = tng_fetch_assoc($parents)) {
      eval("\$sealpdate = \$sealpdate{$parent['familyID']};");
      eval("\$sealpplace = \$sealpplace{$parent['familyID']};");
      $sealplace = addslashes($sealplace);
      eval("\$sealpdatetr = convertdate( \$sealpdate{$parent['familyID']} );");
      eval("\$frel = \$frel{$parent['familyID']};");
      eval("\$mrel = \$mrel{$parent['familyID']};");
      $query = "UPDATE $children_table SET sealdate=\"$sealpdate\", sealdatetr=\"$sealpdatetr\", sealplace=\"$sealpplace\", frel=\"$frel\", mrel=\"$mrel\" WHERE familyID = \"{$parent['familyID']}\" AND personID = '$personID'";
      $result2 = tng_query($query);
      if (!$famc) {
        $famc = $parent['familyID'];
      }
    }
    tng_free_result($parents);
  }
  $famcstr = $famc ? ", famc = \"$famc\"" : "";
  if (!$living) {
    $living = 0;
  }
  if (!$private) {
    $private = 0;
  }
  if (!$burialtype) {
    $burialtype = 0;
  }
  $meta = metaphone($lnprefix . $lastname);
  $query = "UPDATE $people_table SET firstname=\"$firstname\", lnprefix=\"$lnprefix\", lastname=\"$lastname\", nickname=\"$nickname\", prefix=\"$prefix\", suffix=\"$suffix\", title=\"$title\", nameorder=\"$pnameorder\", living=\"$living\", private=\"$private\",
    birthdate=\"$birthdate\", birthdatetr=\"$birthdatetr\", birthplace=\"$birthplace\", sex=\"$sex\", altbirthdate=\"$altbirthdate\", altbirthdatetr=\"$altbirthdatetr\", altbirthplace=\"$altbirthplace\",
    deathdate=\"$deathdate\", deathdatetr=\"$deathdatetr\", deathplace=\"$deathplace\", burialdate=\"$burialdate\", burialdatetr=\"$burialdatetr\", burialplace=\"$burialplace\", burialtype=\"$burialtype\",
    baptdate=\"$baptdate\", baptdatetr=\"$baptdatetr\", baptplace=\"$baptplace\", confdate=\"$confdate\", confdatetr=\"$confdatetr\", confplace=\"$confplace\", initdate=\"$initdate\", initdatetr=\"$initdatetr\", initplace=\"$initplace\", endldate=\"$endldate\", endldatetr=\"$endldatetr\", endlplace=\"$endlplace\", changedate=\"$newdate\",branch=\"$allbranches\",changedby=\"$currentuser\",edituser=\"\",edittime=\"0\",metaphone=\"$meta\" $famcstr WHERE personID = '$personID'";
  $result = tng_query($query);

  if ($sex == 'M') {
    $self = 'husband';
    $spouseorder = 'husborder';
  } else {
    if ($sex == 'F') {
      $self = 'wife';
      $spouseorder = 'wifeorder';
    } else {
      $self = "";
      $spouseorder = "";
    }
  }
  if ($self) {
    $query = "SELECT familyID, husband, wife FROM $families_table WHERE $families_table.$self = '$personID' ORDER BY $spouseorder";
  } else {
    $query = "SELECT familyID, husband, wife FROM $families_table WHERE ($families_table.husband = \"$personID\" OR $families_table.wife = \"$personID\")";
  }
  $marriages = tng_query($query);

  if ($marriages && tng_num_rows($marriages)) {
    while ($marriagerow = tng_fetch_assoc($marriages)) {
      if ($personID == $marriagerow['husband']) {
        $spquery = "SELECT living, private FROM $people_table WHERE personID = \"{$marriagerow['wife']}\"";
      } else {
        if ($personID == $marriagerow['wife']) {
          $spquery = "SELECT living, private FROM $people_table WHERE personID = \"{$marriagerow['husband']}\"";
        } else {
          $spquery = "";
        }
      }
      if ($spquery) {
        $spouselive = tng_query($spquery) or die(uiTextSnippet('cannotexecutequery') . ": $spquery");
        $spouserow = tng_fetch_assoc($spouselive);
        $spouseliving = $spouserow['living'];
        $spouseprivate = $spouserow['private'];
      } else {
        $spouseliving = $spouseprivate = 0;
      }
      $familyliving = ($living || $spouseliving) ? 1 : 0;
      $familyprivate = ($private || $spouseprivate) ? 1 : 0;
      $query = "UPDATE $families_table SET living = \"$familyliving\", private = \"$familyprivate\", branch = \"$allbranches\" WHERE familyID = \"{$marriagerow['familyID']}\"";
      $spouseresult = tng_query($query);
    }
  }
  adminwritelog("<a href=\"peopleEdit.php?personID=$personID\">" . uiTextSnippet('modifyperson') . ": $personID</a>");
} else {
  $message = uiTextSnippet('notsaved');
}
if ($media == "1") {
  header("Location: admin_newmedia.php?personID=$personID&amp;linktype=I&amp;cw=$cw");
} elseif ($newfamily == "none") {
  $message = uiTextSnippet('changestoperson') . " $personID " . uiTextSnippet('succsaved') . '.';
  header("Location: peopleBrowse.php?message=" . urlencode($message));
} elseif ($newfamily == "return") {
  header("Location: peopleEdit.php?personID=$personID&cw=$cw");
} elseif ($newfamily == "child") {
  header("Location: familiesAdd.php?child=$personID&cw=$cw");
} elseif ($newfamily == "close") {
?>
  <!DOCTYPE html>
  <html>
  <body>
    <script>
      top.close();
    </script>
  </body>
  </html>
<?php
} elseif ($newfamily == "ajax") {
  $row = [
          'personID' => $personID,
          'firstname' => $firstname,
          'lastname' => $lastname,
          'lnprefix' => $lnprefix,
          'prefix' => $prefix,
          'suffix' => $suffix,
          'title' => $title,
          'nameorder' => $pnameorder,
          'living' => $living,
          'private' => $private,
          'branch' => $allbranches,
          'allow_living' => 1
  ];
  $name = $session_charset == "UTF-8" ? getName($row) : utf8_encode(getName($row));
  echo "{\"id\":\"$personID\",\"name\":\"" . $name . "\"}";
} else {
  header("Location: familiesAdd.php?$self=$personID&cw=$cw");
}