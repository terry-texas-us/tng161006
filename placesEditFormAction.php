<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
require 'adminlog.php';

$place = addslashes($place);
$placelevel = addslashes($placelevel);
$zoom = addslashes($zoom);
$notes = addslashes($notes);
$orgplace = addslashes($orgplace);

$latitude = preg_replace("/,/", ".", addslashes($latitude));
$longitude = preg_replace("/,/", ".", addslashes($longitude));

if ($latitude && $longitude && $placelevel && !$zoom) {
  $zoom = 13;
}
if (!$zoom) {
  $zoom = 0;
}
if (!$placelevel) {
  $placelevel = 0;
}
if (!$temple) {
  $temple = 0;
}
if (!$tngconfig['places1tree'] && $newtree) {
  $newtreestr = ",gedcom=\"$newtree\"";
  $tree = $newtree;
} else {
  $newtreestr = "";
}
$query = "UPDATE $places_table SET place=\"$place\",placelevel=\"$placelevel\",temple=\"$temple\",latitude=\"$latitude\",longitude=\"$longitude\",zoom=\"$zoom\",notes=\"$notes\",geoignore=\"0\"$newtreestr WHERE ID=\"$ID\"";
$result = tng_query($query);
if (!$result) {
  $message = uiTextSnippet('duplicate');
  header("Location: placesBrowse.php?message=" . urlencode($message));
  exit;
}
if ($tngconfig['places1tree']) {
  $updatetreestr = "";
  $treeurl = "";
} else {
  $updatetreestr = " AND gedcom=\"$tree\"";
  $treeurl = "&tree=$tree";
}
if ($propagate && trim($orgplace)) {
  //people
  $query = "UPDATE $people_table SET birthplace=\"$place\" WHERE birthplace=\"$orgplace\"$updatetreestr";
  $result = tng_query($query);
  $query = "UPDATE $people_table SET altbirthplace=\"$place\" WHERE altbirthplace=\"$orgplace\"$updatetreestr";
  $result = tng_query($query);
  $query = "UPDATE $people_table SET deathplace=\"$place\" WHERE deathplace=\"$orgplace\"$updatetreestr";
  $result = tng_query($query);
  $query = "UPDATE $people_table SET burialplace=\"$place\" WHERE burialplace=\"$orgplace\"$updatetreestr";
  $result = tng_query($query);
  $query = "UPDATE $people_table SET baptplace=\"$place\" WHERE baptplace=\"$orgplace\"$updatetreestr";
  $result = tng_query($query);
  $query = "UPDATE $people_table SET confplace=\"$place\" WHERE confplace=\"$orgplace\"$updatetreestr";
  $result = tng_query($query);
  $query = "UPDATE $people_table SET initplace=\"$place\" WHERE initplace=\"$orgplace\"$updatetreestr";
  $result = tng_query($query);
  $query = "UPDATE $people_table SET endlplace=\"$place\" WHERE endlplace=\"$orgplace\"$updatetreestr";
  $result = tng_query($query);

  //families
  $query = "UPDATE $families_table SET marrplace=\"$place\" WHERE marrplace=\"$orgplace\"$updatetreestr";
  $result = tng_query($query);
  $query = "UPDATE $families_table SET divplace=\"$place\" WHERE divplace=\"$orgplace\"$updatetreestr";
  $result = tng_query($query);
  $query = "UPDATE $families_table SET sealplace=\"$place\" WHERE sealplace=\"$orgplace\"$updatetreestr";
  $result = tng_query($query);

  //events
  $query = "UPDATE $events_table SET eventplace=\"$place\" WHERE eventplace=\"$orgplace\"$updatetreestr";
  $result = tng_query($query);

  //children
  $query = "UPDATE $children_table SET sealplace=\"$place\" WHERE sealplace=\"$orgplace\"$updatetreestr";
  $result = tng_query($query);

  //media
  $query = "UPDATE $medialinks_table SET personID=\"$place\" WHERE personID=\"$orgplace\"$updatetreestr";
  $result = tng_query($query);
}
adminwritelog("<a href=\"placesEdit.php?ID=$ID$treeurl\">" . uiTextSnippet('modifyplace') . ": $place</a>");

if ($newscreen == "return") {
  header("Location: placesEdit.php?ID=$ID$treeurl");
} elseif ($newscreen == "close") {
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
} else {
  $message = uiTextSnippet('changestoplace') . " $place " . uiTextSnippet('succsaved') . '.';
  header("Location: placesBrowse.php?message=" . urlencode($message));
}