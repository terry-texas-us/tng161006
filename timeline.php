<?php
require 'tng_begin.php';

$timeline = $_SESSION['timeline'];
if (!is_array($timeline)) {
  $timeline = array();
}

$tng_message = $_SESSION['tng_message'] = "";
if ($newwidth) {
  $_SESSION['timeline_chartwidth'] = $newwidth;
}

if ($primaryID) {
  $newentry = "timeperson=$primaryID" . "&timetree=$tree";
  if (!in_array($newentry, $timeline)) {
    array_push($timeline, $newentry);
    $_SESSION['timeline'] = $timeline;
  }
}
for ($i = 2; $i < 6; $i++) {
  $nextpersonID = "nextpersonID$i";
  $nexttree = "nexttree$i";
  if ($$nextpersonID != "") {
    $newentry2 = "timeperson=" . strtoupper($$nextpersonID) . "&timetree=" . $$nexttree;
    if (!in_array($newentry2, $timeline)) {
      array_push($timeline, $newentry2);
      $_SESSION['timeline'] = $timeline;
    }
  }
}

$righttree = checktree($timetree);

$finalarray = array();
foreach ($timeline as $timeentry) {
  parse_str($timeentry);
  $todelete = $timetree . "_" . $timeperson;
  if ($$todelete != "1") {
    $result2 = getPersonDataPlusDates($timetree, $timeperson);
    if ($result2) {
      $row2 = tng_fetch_assoc($result2);
      $rights = determineLivingPrivateRights($row2, $timetree);
      $row2['allow_living'] = $rights['living'];
      $row2['allow_private'] = $rights['private'];
      if (($row2['living'] && !$rights['living']) || ($row2['private'] && !$rights['private'])) {
        $tng_message .= uiTextSnippet('noliving') . ": " . getName($row2) . " ($timeperson)<br>\n";
      } elseif (!$row2['birth']) {
        $tng_message .= uiTextSnippet('nobirth') . ": " . getName($row2) . " ($timeperson)<br>\n";
      } else {
        array_push($finalarray, $timeentry);
      }
      tng_free_result($result2);
    }
  }
}
$timeline = $_SESSION['timeline'] = $finalarray;
$_SESSION['tng_message'] = $tng_message;

header("Location: timeline2.php?primaryID=$primaryID&tree=$tree&chartwidth=$newwidth");
