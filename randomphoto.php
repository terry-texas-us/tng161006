<?php
//Change these vars to affect max width & height of your photo. Aspect ratio will be maintained. Leaving
//these values blank will cause your photo to be displayed actual size.

if (!$rp_maxwidth) {
  $rp_maxwidth = "175";
}
if (!$rp_maxheight) {
  $rp_maxheight = "175";
}
if (!isset($rp_mediatypeID) || !$rp_mediatypeID) {
  $rp_mediatypeID = "photos";
}
$query = "SELECT DISTINCT $media_table.mediaID, $media_table.description, path, alwayson, usecollfolder, mediatypeID FROM $media_table WHERE mediatypeID = \"$rp_mediatypeID\" AND (abspath is NULL OR abspath = \"0\") ORDER BY RAND()";
$result = tng_query($query);
while ($imgrow = tng_fetch_assoc($result)) {

  // if the picture is alwayson or we are allowing living to be displayed, we don't need to bother
  // with any further checking
  if ($imgrow['alwayson']) {
    break;

    // otherwise, let's check for living
  } else {

    // this query will return rows of personIDs on the photo that are living
    $query = "SELECT $medialinks_table.personID FROM ($medialinks_table, $people_table) WHERE $medialinks_table.personID = $people_table.personID AND $medialinks_table.mediaID = {$imgrow['mediaID']} AND ($people_table.living = '1' OR $people_table.private = '1')";
    $presult = tng_query($query);
    $rows = tng_num_rows($presult);
    tng_free_result($presult);

    $query = "SELECT $medialinks_table.personID FROM ($medialinks_table, $families_table) WHERE $medialinks_table.personID = $families_table.familyID AND $medialinks_table.mediaID = {$imgrow['mediaID']} AND ($families_table.living = '1' OR $families_table.private = '1')";
    $presult = tng_query($query);
    $rows = $rows + tng_num_rows($presult);
    tng_free_result($presult);

    // if no rows are returned, there are no living on the photo, so let's display it
    if ($rows == 0) {
      break;
    }
  }
}
tng_free_result($result);

$usefolder = $imgrow['usecollfolder'] ? $mediatypes_assoc[$rp_mediatypeID] : $mediapath;
$photoinfo = getimagesize("$rootpath$usefolder/" . $imgrow['path']);
$photowtouse = $photoinfo[0];
$photohtouse = $photoinfo[1];

//these lines do the resizing
if ($rp_maxheight && $photohtouse > $rp_maxheight) {
  $photowtouse = intval($rp_maxheight * $photowtouse / $photohtouse);
  $photohtouse = $rp_maxheight;
}
if ($rp_maxwidth && $photowtouse > $rp_maxwidth) {
  $photohtouse = intval($rp_maxwidth * $photohtouse / $photowtouse);
  $photowtouse = $rp_maxwidth;
}

//these lines restrict the table width so the caption will not be wider than the photo
$width = "width=\"" . ($photowtouse + 10) . "\"";

echo "<table class=\"indexphototable\">";
echo "<tr><td><a href=\"${showmedia_url}mediaID={$imgrow['mediaID']}\"><img class=\"indexphoto\" src=\"$usefolder/" . str_replace("%2F", "/", rawurlencode($imgrow['path'])) . "\" width=\"$photowtouse\" height=\"$photohtouse\" alt=\"{$imgrow['description']}\" title=\"{$imgrow['description']}\"></a></td></tr>";
echo "<tr><td $width><a href=\"${showmedia_url}mediaID={$imgrow['mediaID']}\">{$imgrow['description']}</a></td></tr>";
echo "</table>";
