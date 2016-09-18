<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

//permissions
if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
switch ($entity) {
  case 'person':
    $url = "peopleEdit.php?personID=$newID";

    $query = "UPDATE $people_table SET gedcom=\"$newtree\", personID=\"$newID\" WHERE gedcom=\"$oldtree\" AND personID=\"$entityID\"";
    $result = tng_query($query);

    $query = "UPDATE mostwanted SET gedcom=\"$newtree\", personID=\"$newID\" WHERE gedcom=\"$oldtree\" AND personID=\"$entityID\"";
    $result = tng_query($query);

    $query = "UPDATE temp_events SET gedcom=\"$newtree\", personID=\"$newID\" WHERE gedcom=\"$oldtree\" AND personID=\"$entityID\"";
    $result = tng_query($query);

    $query = "UPDATE users SET mygedcom='$newtree', personID='$newID' WHERE mygedcom='$oldtree' AND personID='$entityID'";
    $result = tng_query($query);

    $query = "UPDATE $families_table SET husband=\"\" WHERE gedcom=\"$oldtree\" AND husband=\"$entityID\"";
    $result = tng_query($query);

    $query = "UPDATE $families_table SET wife=\"\" WHERE gedcom=\"$oldtree\" AND wife=\"$entityID\"";
    $result = tng_query($query);

    $query = "DELETE FROM branchlinks WHERE gedcom=\"$oldtree\" AND persfamID=\"$entityID\"";
    $result = tng_query($query);

    $query = "DELETE FROM citations WHERE gedcom=\"$oldtree\" AND persfamID=\"$entityID\"";
    $result = tng_query($query);

    $query = "DELETE FROM children WHERE gedcom=\"$oldtree\" AND personID=\"$entityID\"";
    $result = tng_query($query);

    $query = "DELETE FROM associations WHERE gedcom=\"$oldtree\" AND (personID=\"$entityID\" OR passocID=\"$entityID\")";
    $result = tng_query($query);

    break;
  case 'source':
    $url = "admin_editsource.php?sourceID=$newID&tree=$newtree";

    $query = "UPDATE sources SET gedcom=\"$newtree\", sourceID=\"$newID\" WHERE gedcom=\"$oldtree\" AND sourceID=\"$entityID\"";
    $result = tng_query($query);

    $query = "DELETE FROM citations WHERE gedcom=\"$oldtree\" AND persfamID=\"$entityID\"";
    $result = tng_query($query);

    break;
  case 'repo':
    $url = "repositoriesEdit.php?repoID=$newID";

    $query = "UPDATE repositories SET gedcom=\"$newtree\", repoID=\"$newID\" WHERE gedcom=\"$oldtree\" AND repoID=\"$entityID\"";
    $result = tng_query($query);

    $query = "UPDATE sources SET repoID=\"\" WHERE gedcom=\"$oldtree\" AND repoID=\"$entityID\"";
    $result = tng_query($query);

    break;
}

$query = "SELECT addressID FROM events WHERE gedcom=\"$oldtree\" AND persfamID=\"$entityID\" AND addressID!=\"\"";
$result = tng_query($query);
while ($row = tng_fetch_assoc($result)) {
  $query = "UPDATE addresses SET gedcom=\"$newtree\" WHERE addressID=\"{$row['addressID']}\"";
  $result2 = tng_query($query);
}
tng_free_result($result);

$query = "UPDATE events SET gedcom=\"$newtree\", persfamID=\"$newID\" WHERE gedcom=\"$oldtree\" AND persfamID=\"$entityID\"";
$result = tng_query($query);

$query = "UPDATE albumplinks SET gedcom=\"$newtree\", entityID=\"$newID\" WHERE gedcom=\"$oldtree\" AND entityID=\"$entityID\"";
$result = tng_query($query);

$query = "UPDATE medialinks SET gedcom=\"$newtree\", personID=\"$newID\" WHERE gedcom=\"$oldtree\" AND personID=\"$entityID\"";
$result = tng_query($query);

$query = "SELECT xnoteID FROM notelinks WHERE gedcom=\"$oldtree\" AND persfamID=\"$entityID\" AND xnoteID!=\"\"";
$result = tng_query($query);
while ($row = tng_fetch_assoc($result)) {
  $query = "UPDATE xnotes SET gedcom=\"$newtree\" WHERE ID=\"{$row['xnoteID']}\"";
  $result2 = tng_query($query);
}
tng_free_result($result);

$query = "UPDATE notelinks SET gedcom=\"$newtree\", persfamID=\"$newID\" WHERE gedcom=\"$oldtree\" AND persfamID=\"$entityID\"";
$result = tng_query($query);

header("Location: $url");
