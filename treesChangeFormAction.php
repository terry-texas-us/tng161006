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

    $query = "UPDATE $mostwanted_table SET gedcom=\"$newtree\", personID=\"$newID\" WHERE gedcom=\"$oldtree\" AND personID=\"$entityID\"";
    $result = tng_query($query);

    $query = "UPDATE $temp_events_table SET gedcom=\"$newtree\", personID=\"$newID\" WHERE gedcom=\"$oldtree\" AND personID=\"$entityID\"";
    $result = tng_query($query);

    $query = "UPDATE users SET mygedcom='$newtree', personID='$newID' WHERE mygedcom='$oldtree' AND personID='$entityID'";
    $result = tng_query($query);

    $query = "UPDATE $families_table SET husband=\"\" WHERE gedcom=\"$oldtree\" AND husband=\"$entityID\"";
    $result = tng_query($query);

    $query = "UPDATE $families_table SET wife=\"\" WHERE gedcom=\"$oldtree\" AND wife=\"$entityID\"";
    $result = tng_query($query);

    $query = "DELETE FROM $branchlinks_table WHERE gedcom=\"$oldtree\" AND persfamID=\"$entityID\"";
    $result = tng_query($query);

    $query = "DELETE FROM $citations_table WHERE gedcom=\"$oldtree\" AND persfamID=\"$entityID\"";
    $result = tng_query($query);

    $query = "DELETE FROM $children_table WHERE gedcom=\"$oldtree\" AND personID=\"$entityID\"";
    $result = tng_query($query);

    $query = "DELETE FROM $assoc_table WHERE gedcom=\"$oldtree\" AND (personID=\"$entityID\" OR passocID=\"$entityID\")";
    $result = tng_query($query);

    break;
  case 'source':
    $url = "admin_editsource.php?sourceID=$newID&tree=$newtree";

    $query = "UPDATE $sources_table SET gedcom=\"$newtree\", sourceID=\"$newID\" WHERE gedcom=\"$oldtree\" AND sourceID=\"$entityID\"";
    $result = tng_query($query);

    $query = "DELETE FROM $citations_table WHERE gedcom=\"$oldtree\" AND persfamID=\"$entityID\"";
    $result = tng_query($query);

    break;
  case 'repo':
    $url = "repositoriesEdit.php?repoID=$newID";

    $query = "UPDATE $repositories_table SET gedcom=\"$newtree\", repoID=\"$newID\" WHERE gedcom=\"$oldtree\" AND repoID=\"$entityID\"";
    $result = tng_query($query);

    $query = "UPDATE $sources_table SET repoID=\"\" WHERE gedcom=\"$oldtree\" AND repoID=\"$entityID\"";
    $result = tng_query($query);

    break;
}

$query = "SELECT addressID FROM $events_table WHERE gedcom=\"$oldtree\" AND persfamID=\"$entityID\" AND addressID!=\"\"";
$result = tng_query($query);
while ($row = tng_fetch_assoc($result)) {
  $query = "UPDATE $address_table SET gedcom=\"$newtree\" WHERE addressID=\"{$row['addressID']}\"";
  $result2 = tng_query($query);
}
tng_free_result($result);

$query = "UPDATE $events_table SET gedcom=\"$newtree\", persfamID=\"$newID\" WHERE gedcom=\"$oldtree\" AND persfamID=\"$entityID\"";
$result = tng_query($query);

$query = "UPDATE $album2entities_table SET gedcom=\"$newtree\", entityID=\"$newID\" WHERE gedcom=\"$oldtree\" AND entityID=\"$entityID\"";
$result = tng_query($query);

$query = "UPDATE $medialinks_table SET gedcom=\"$newtree\", personID=\"$newID\" WHERE gedcom=\"$oldtree\" AND personID=\"$entityID\"";
$result = tng_query($query);

$query = "SELECT xnoteID FROM $notelinks_table WHERE gedcom=\"$oldtree\" AND persfamID=\"$entityID\" AND xnoteID!=\"\"";
$result = tng_query($query);
while ($row = tng_fetch_assoc($result)) {
  $query = "UPDATE xnotes SET gedcom=\"$newtree\" WHERE ID=\"{$row['xnoteID']}\"";
  $result2 = tng_query($query);
}
tng_free_result($result);

$query = "UPDATE $notelinks_table SET gedcom=\"$newtree\", persfamID=\"$newID\" WHERE gedcom=\"$oldtree\" AND persfamID=\"$entityID\"";
$result = tng_query($query);

header("Location: $url");
