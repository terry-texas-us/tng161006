<?php
require 'tng_begin.php';

require 'personlib.php';

$result = getPersonFullPlusDates($personID);
if (!tng_num_rows($result)) {
  tng_free_result($result);
  header('Location: thispagedoesnotexist.html');
  exit;
}
$row = tng_fetch_assoc($result);
$rights = determineLivingPrivateRights($row);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];
$namestr = getName($row);

tng_free_result($result);

header('Content-type:text/html; charset=' . $sessionCharset);

initMediaTypes();

echo "<div class='title'>\n";
$photostr = showSmallPhoto($personID, $namestr, $rights['both'], 0, false, $row['sex']);

$years = getYears($row);

if ($photostr) {
  $outputstr = "<div style='float: left; padding-right: 5px'>$photostr</div>\n";
  $outputstr .= $namestr;
} else {
  $outputstr = $namestr;
}
if ($years) {
  $outputstr .= "<br><span class='years'>$years</span>";
}
echo $outputstr;

echo '</div>';

$persontext = '';

$persontext .= "<table class='table table-sm'>\n";
resetEvents();
if ($rights['both']) {
  if ($row['nickname']) {
    $persontext .= showEvent(['text' => uiTextSnippet('nickname'), 'fact' => $row['nickname'], 'event' => 'NICK', 'entity' => $personID, 'type' => 'I']);
  }
  setEvent(['text' => uiTextSnippet('birth'), 'fact' => $stdex['BIRT'], 'date' => $row['birthdate'], 'place' => $row['birthplace'], 'event' => 'BIRT', 'entity' => $personID, 'type' => 'I', 'np' => 1], $row['birthdatetr']);
  setEvent(['text' => uiTextSnippet('christened'), 'fact' => $stdex['CHR'], 'date' => $row['altbirthdate'], 'place' => $row['altbirthplace'], 'event' => 'CHR', 'entity' => $personID, 'type' => 'I', 'np' => 1], $row['altbirthdatetr']);
  setEvent(['text' => uiTextSnippet('died'), 'fact' => $stdex['DEAT'], 'date' => $row['deathdate'], 'place' => $row['deathplace'], 'event' => 'DEAT', 'entity' => $personID, 'type' => 'I', 'np' => 1], $row['deathdatetr']);
  $burialmsg = $row['burialtype'] ? uiTextSnippet('cremated') : uiTextSnippet('buried');
  setEvent(['text' => $burialmsg, 'fact' => $stdex['BURI'], 'date' => $row['burialdate'], 'place' => $row['burialplace'], 'event' => 'BURI', 'entity' => $personID, 'type' => 'I', 'np' => 1], $row['burialdatetr']);
}

if (count($events)) {
  ksort($events);
  foreach ($events as $event) {
    $persontext .= showEvent($event);
  }
}
$persontext .= "</table>\n";

$persontext .= "<a class='btn btn-sm btn-outline-primary' href='pedigree.php?personID={$personID}'><img class='icon-sm icon-muted' src='svg/flow-split-horizontal.svg'> Tree</a> ";
$persontext .= "<a class='btn btn-sm btn-outline-primary' href='peopleShowPerson.php?personID={$personID}' id='p{$personID}_t'><img class='icon-sm icon-muted' src='svg/person.svg'> Person</a>";

echo $persontext;