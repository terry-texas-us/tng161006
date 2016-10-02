<?php
require 'begin.php';
require 'genlib.php';
require_once 'tngdblib.php';
require 'checklogin.php';
require 'mail.php';

if ($sessionCharset != 'UTF-8') {
  $newplace = tng_utf8_decode($newplace);
  $newinfo = tng_utf8_decode($newinfo);
  $usernote = tng_utf8_decode($usernote);
}
$postdate = date('Y-m-d H:i:s', time() + (3600 * $timeOffset));
$query = "INSERT INTO temp_events (type, personID, familyID, eventID, eventdate, eventplace, info, note, user, postdate) VALUES ('$type', '$personID', '$familyID', '$eventID', '$newdate', '$newplace', '$newinfo', '$usernote', '$currentuser', '$postdate')";
$result = tng_query($query);

if ($tngconfig['revmail']) {
  if ($personID) {
    $result = getPersonSimple($personID);
    $namerow = tng_fetch_assoc($result);
    $rights = determineLivingPrivateRights($namerow);
    $namerow['allow_living'] = $rights['living'];
    $namerow['allow_private'] = $rights['private'];
    $namestr = getName($namerow) . " ($personID)";
    tng_free_result($result);
  } else {
    $result = getFamilyData($familyID);
    $frow = tng_fetch_assoc($result);
    $hname = $wname = '';
    $frights = determineLivingPrivateRights($frow);
    $frow['allow_living'] = $frights['living'];
    $frow['allow_private'] = $frights['private'];
    if ($frow['husband']) {
      $presult = getPersonSimple($frow['husband']);
      $prow = tng_fetch_assoc($presult);
      tng_free_result($presult);
      $prights = determineLivingPrivateRights($prow);
      $prow['allow_living'] = $prights['living'];
      $prow['allow_private'] = $prights['private'];
      $hname = getName($prow);
    }
    if ($frow['wife']) {
      $presult = getPersonSimple($frow['wife']);
      $prow = tng_fetch_assoc($presult);
      tng_free_result($presult);
      $prights = determineLivingPrivateRights($prow);
      $prow['allow_living'] = $prights['living'];
      $prow['allow_private'] = $prights['private'];
      $wname = getName($prow);
    }
    tng_free_result($result);

    $persfamID = $familyID;
    $plus = $hname && $wname ? ' + ' : '';
    $namestr = uiTextSnippet('family') . ": $hname$plus$wname ($familyID)";
  }
  $query = 'SELECT email, owner FROM trees';
  $treeresult = tng_query($query);
  $treerow = tng_fetch_assoc($treeresult);
  $sendemail = $treerow['email'] ? $treerow['email'] : $emailaddr;
  $owner = $treerow['owner'] ? $treerow['owner'] : ($sitename ? $sitename : $dbowner);
  tng_free_result($treeresult);

  $message = uiTextSnippet('reviewmsg') . "\n\n$namestr\n" . uiTextSnippet('user') . ": $currentuser\n\n" . uiTextSnippet('administration') . ": $tngdomain/admin.php";
  tng_sendmail('TNG', $emailaddr, $owner, $sendemail, uiTextSnippet('revsubject'), $message, $emailaddr, $emailaddr);
}
echo 1;
