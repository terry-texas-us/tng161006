<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowEdit) {
  exit;
}

require 'adminlog.php';

$query = "SELECT place, latitude, longitude, placelevel, zoom, notes FROM places WHERE ID = \"$keep\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
$newplace = addslashes($row['place']);
$keeplat = $row['latitude'];
$keeplong = $row['longitude'];
$keeplevel = $row['placelevel'];
$keepzoom = $row['zoom'];
$keepnotes = $row['notes'];
$latlongstr = ', latitude, longitude, placelevel, zoom';
tng_free_result($result);

$dquery = 'DELETE FROM places WHERE ';

$addtoquery = '';
$mergelist = explode(',', $places);

foreach ($mergelist as $val) {
  if ($addtoquery) {
    $addtoquery .= ' OR ';
  }
  $addtoquery .= "ID=\"$val\"";

  $query = "SELECT place, notes$latlongstr FROM places WHERE ID = \"$val\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  tng_free_result($result);
  $oldplace = addslashes($row['place']);

  if ($oldplace) {
    if ($latlongstr) {
      if ($row['latitude'] || $row['longitude'] || $row['placelevel'] || $row['zoom']) {
        if (!$keeplat && $row['latitude']) {
          $keeplat = $row['latitude'];
        }
        if (!$keeplong && $row['longitude']) {
          $keeplong = $row['longitude'];
        }
        if (!$keeplevel && $row['placelevel']) {
          $keeplevel = $row['placelevel'];
        }
        if (!$keepzoom && $row['zoom']) {
          $keepzoom = $row['zoom'];
        }
        $query = "UPDATE places SET latitude = \"$keeplat\", longitude = \"$keeplong\", placelevel = \"$keeplevel\", zoom = \"$keepzoom\" WHERE ID = \"$keep\"";
        $result = tng_query($query);
        $latlongstr = '';  //just do the first one we get
      }
    }
    if ($row['notes']) {
      $keepnotes .= $lineending . $row['notes'];
      $query = 'UPDATE places SET notes = "' . addslashes($keepnotes) . "\" WHERE ID = \"$keep\"";
      $result = tng_query($query);
    }

    $query = "UPDATE people SET birthplace = '$newplace' WHERE birthplace = '$oldplace'";
    $result = tng_query($query);
    $query = "UPDATE people SET altbirthplace = '$newplace' WHERE altbirthplace = '$oldplace'";
    $result = tng_query($query);
    $query = "UPDATE people SET deathplace = '$newplace' WHERE deathplace = '$oldplace'";
    $result = tng_query($query);
    $query = "UPDATE people SET burialplace = '$newplace' WHERE burialplace = '$oldplace'";
    $result = tng_query($query);
    $query = "UPDATE people SET baptplace = '$newplace' WHERE baptplace = '$oldplace'";
    $result = tng_query($query);
    $query = "UPDATE people SET confplace = '$newplace' WHERE confplace = '$oldplace'";
    $result = tng_query($query);
    $query = "UPDATE people SET initplace = '$newplace' WHERE initplace = '$oldplace'";
    $result = tng_query($query);
    $query = "UPDATE people SET endlplace = '$newplace' WHERE endlplace = '$oldplace'";
    $result = tng_query($query);

    //families
    $query = "UPDATE families SET marrplace = '$newplace' WHERE marrplace = '$oldplace'";
    $result = tng_query($query);
    $query = "UPDATE families SET divplace = '$newplace' WHERE divplace = '$oldplace'";
    $result = tng_query($query);
    $query = "UPDATE families SET sealplace = '$newplace' WHERE sealplace = '$oldplace'";
    $result = tng_query($query);

    //events
    $query = "UPDATE events SET eventplace = '$newplace' WHERE eventplace = '$oldplace'";
    $result = tng_query($query);

    //children
    $query = "UPDATE children SET sealplace = '$newplace' WHERE sealplace = '$oldplace'";
    $result = tng_query($query);

    //media (this is quick & dirty. would be better to cycle through each link and try the update, then delete the old if the update is not successful,
    //since that would indicate a key collision and the old record would remain, but it shouldn't come up very often and it wouldn't be critical in any case)
    $query = "UPDATE medialinks SET personID = '$newplace' WHERE personID = '$oldplace'";
    $result = tng_query($query);

    if (!tng_affected_rows()) {
      $query = "DELETE FROM medialinks WHERE personID = '$oldplace'";
      $result = tng_query($query);
    }

    //cemeteries
    $query = "UPDATE cemeteries SET place = '$newplace' WHERE place = '$oldplace'";
    $result = tng_query($query);
  }
}
if ($addtoquery) {
  $dquery .= $addtoquery;
  $result = tng_query($dquery) or die(uiTextSnippet('cannotexecutequery') . ": $dquery");

  adminwritelog(uiTextSnippet('mergeplaces') . ": $newplace");

  $message = uiTextSnippet('pmsucc') . ": $newplace.";
}
header('Content-Type: application/json; charset=' . $session_charset);
echo "{\"latitude\":\"$keeplat\", \"longitude\":\"$keeplong\"}";