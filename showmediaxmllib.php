<?php
if ($medialinkID) {
  //look up media & medialinks joined
  //get info for linked person/family/source/repo
  $query = "SELECT mediatypeID, personID, linktype, $medialinks_table.gedcom as gedcom, eventID, ordernum FROM ($media_table, $medialinks_table) WHERE medialinkID = \"$medialinkID\" AND $media_table.mediaID = $medialinks_table.mediaID";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $personID = $row['personID'];
  $tree = $row[gedcom];
  $ordernum = $row['ordernum'];
  $mediatypeID = $row['mediatypeID'];
  $linktype = $row['linktype'];
  if ($linktype == "P") {
    $linktype = 'I';
  }
  $eventID = $row['eventID'];
} else {
  if ($albumlinkID) {
    $query = "SELECT albumname, description, ordernum, $albums_table.albumID as albumID FROM ($albums_table, $albumlinks_table)
      WHERE albumlinkID = \"$albumlinkID\" AND $albumlinks_table.albumID = $albums_table.albumID";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    $ordernum = $row['ordernum'];
    $albumID = $row['albumID'];
    $albumname = $row['albumname'];
    $albdesc = $row['description'];
    tng_free_result($result);
  }
  $query = "SELECT mediatypeID, gedcom FROM $media_table WHERE mediaID = \"$mediaID\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $mediatypeID = $row['mediatypeID'];
}
if (!tng_num_rows($result)) {
  tng_free_result($result);
  header("Location: thispagedoesnotexist.html");
  exit;
}
$info = getMediaInfo($mediatypeID, $mediaID, $personID, $albumID, $albumlinkID, $cemeteryID, $eventID);
$ordernum = $info['ordernum'];
$mediaID = $info['mediaID'];
$medianotes = $info['medianotes'];
$mediadescription = xmlcharacters($info['mediadescription']);
$page = $info['page'];
$result = $info['result'];
$imgrow = $info['imgrow'];

$livinginfo = findLivingPrivate($mediaID);
$noneliving = $livinginfo['noneliving'] && $livinginfo['noneprivate'];

$showPhotoInfo = $imgrow['alwayson'] || $noneliving;
$nonamesloc = $livinginfo['private'] ? $tngconfig['nnpriv'] : $nonames;

if ($noneliving || !$nonamesloc || $imgrow['alwayson']) {
  $description = $mediadescription;
  $notes = nl2br(xmlcharacters(getXrefNotes($medianotes)));
  $notes .= $info['gotmap'] ? "<p>" . uiTextSnippet('mediamaptext') . "</p>" : "";
} else {
  $description = $notes = uiTextSnippet('living');
}
$logdesc = $nonamesloc && !$noneliving && !$imgrow['alwayson'] ? ($livinginfo['private'] ? uiTextSnippet('private') : uiTextSnippet('living')) : $description;

$usefolder = $imgrow['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
$size = getimagesize("$rootpath$usefolder/" . $imgrow['path'], $info);
$adjheight = $size[1] - 1;

$pagenav = getMediaNavigation($mediaID, $personID, $albumlinkID, $result, false);