<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

require 'adminlog.php';
require 'datelib.php';

require 'geocodelib.php';

$query = "SELECT branch, edituser, edittime FROM families WHERE familyID = '$familyID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

if (!$allowEdit || !checkbranch($row['branch'])) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
$editconflict = determineConflict($row, families);

if (!$editconflict) {
  if ($newfamily == 'ajax' && $sessionCharset != 'UTF-8') {
    $marrplace = tng_utf8_decode($marrplace);
    $divplace = tng_utf8_decode($divplace);
    $sealplace = tng_utf8_decode($sealplace);
    $marrtype = tng_utf8_decode($marrtype);
  }

  $marrplace = addslashes($marrplace);
  $divplace = addslashes($divplace);
  $sealplace = addslashes($sealplace);
  $marrtype = addslashes($marrtype);

  $marrdatetr = convertDate($marrdate);
  $divdatetr = convertDate($divdate);
  $sealdatetr = convertDate($sealdate);

  //get living from husband, wife
  if ($husband) {
    $spquery = "SELECT living FROM people WHERE personID = '$husband'";
    $spouselive = tng_query($spquery) or die(uiTextSnippet('cannotexecutequery') . ": $spquery");
    $spouserow = tng_fetch_assoc($spouselive);
    $husbliving = $spouserow['living'];
  } else {
    $husbliving = 0;
  }
  if ($wife) {
    $spquery = "SELECT living FROM people WHERE personID = '$wife'";
    $spouselive = tng_query($spquery) or die(uiTextSnippet('cannotexecutequery') . ": $spquery");
    $spouserow = tng_fetch_assoc($spouselive);
    $wifeliving = $spouserow['living'];
  } else {
    $wifeliving = 0;
  }
  $familyliving = $living ? $living : 0;
  if (!$private) {
    $private = 0;
  }
  $newdate = date('Y-m-d H:i:s', time() + (3600 * $timeOffset));

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
    $oldbranches = explode(',', $orgbranch);
    foreach ($oldbranches as $b) {
      if ($b && !in_array($b, $branch)) {
        $query = "DELETE FROM branchlinks WHERE persfamID = '$familyID' AND branch = \"$b\"";
        $result = tng_query($query);
      }
    }
    foreach ($branch as $b) {
      if ($b && !in_array($b, $oldbranches)) {
        $query = "INSERT IGNORE INTO branchlinks (branch, persfamID) VALUES('$b', '$familyID')";
        $result = tng_query($query);
      }
    }
  }
  $places = [];
  if (trim($marrplace) && !in_array($marrplace, $places)) {
    array_push($places, $marrplace);
  }
  if (trim($divplace) && !in_array($divplace, $places)) {
    array_push($places, $divplace);
  }
  if (trim($sealplace) && !in_array($sealplace, $places)) {
    array_push($places, $sealplace);
  }
  foreach ($places as $place) {
    $query = "INSERT IGNORE INTO places (place, placelevel, zoom, geoignore) VALUES ('$place', '0', '0', '0')";
    $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
    if ($tngconfig['autogeo'] && tng_affected_rows()) {
      $ID = tng_insert_id();
      $message = geocode($place, 0, $ID);
    }
  }
  $query = "UPDATE families SET husband=\"$husband\",wife=\"$wife\",living=\"$familyliving\",private=\"$private\",marrdate=\"$marrdate\",marrdatetr=\"$marrdatetr\",marrplace=\"$marrplace\",marrtype=\"$marrtype\",divdate=\"$divdate\",divdatetr=\"$divdatetr\",divplace=\"$divplace\",sealdate=\"$sealdate\",sealdatetr=\"$sealdatetr\",sealplace=\"$sealplace\",changedate=\"$newdate\",branch=\"$allbranches\",changedby=\"$currentuser\",edituser=\"\",edittime=\"0\" WHERE familyID = '$familyID'";
  $result = tng_query($query);

  adminwritelog("<a href=\"familiesEdit.php?familyID=$familyID&cw=$cw\">" . uiTextSnippet('modifyfamily') . ": $familyID</a>");
} else {
  $message = uiTextSnippet('notsaved');
}
if ($media == '1') {
  header("Location: admin_newmedia.php?personID=$familyID&amp;linktype=F&amp;cw=$cw");
} elseif ($newfamily == 'return') {
  header("Location: familiesEdit.php?familyID=$familyID&amp;cw=$cw");
} else {
  if ($newfamily == 'close') {
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
  } elseif ($newfamily == 'ajax') {
    echo 1;
  } else {
    $message = uiTextSnippet('changestofamily') . " $familyID " . uiTextSnippet('succsaved') . '.';
    header('Location: familiesBrowse.php?message=' . urlencode($message));
  }
}