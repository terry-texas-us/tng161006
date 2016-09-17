<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowMediaEdit && !$allowMediaAdd && !$allowMediaDelete) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}

$json = false;
initMediaTypes();

function reorderMedia($query, $plink) {
  global $medialinks_table;
  global $media_table;
  global $type;
  global $album2entities_table;

  $eventID = $plink['eventID'];
  $result3 = tng_query($query);
  while ($personrow = tng_fetch_assoc($result3)) {
    $counter = 1;
    if ($type == 'media') {
      $query = "SELECT medialinkID FROM ($medialinks_table, $media_table) WHERE personID = \"{$personrow['personID']}\" AND $media_table.mediaID = $medialinks_table.mediaID AND eventID = \"$eventID\" AND mediatypeID = \"{$plink['mediatypeID']}\" ORDER BY ordernum";
      $result4 = tng_query($query);

      while ($medialinkrow = tng_fetch_assoc($result4)) {
        $query = "UPDATE $medialinks_table SET ordernum = \"$counter\" WHERE medialinkID = \"{$medialinkrow['medialinkID']}\"";
        tng_query($query);
        $counter++;
      }
      tng_free_result($result4);
    } else {
      //do for albums
      $query = "SELECT alinkID FROM $album2entities_table WHERE entityID = \"{$personrow['personID']}\" ORDER BY ordernum";
      $result4 = tng_query($query);

      while ($albumlinkrow = tng_fetch_assoc($result4)) {
        $query = "UPDATE $album2entities_table SET ordernum = \"$counter\" WHERE alinkID = \"{$albumlinkrow['alinkID']}\"";
        tng_query($query);
        $counter++;
      }
      tng_free_result($result4);
    }
  }
  tng_free_result($result3);
}

function setDefault($entity, $media, $album) {
  global $albumlinks_table;
  global $medialinks_table;

  if ($album) {
    $query = "UPDATE $albumlinks_table SET defphoto = '' WHERE defphoto = '1' AND albumID = '$album'";
    tng_query($query);

    $query = "UPDATE $albumlinks_table SET defphoto = '1' WHERE albumID = '$album' AND mediaID = '$media'";
    tng_query($query);
  } else {
    $query = "UPDATE $medialinks_table SET defphoto = '' WHERE defphoto = '1' AND personID = '$entity'";
    tng_query($query);

    $query = "UPDATE $medialinks_table SET defphoto = '1' WHERE personID = '$entity' AND mediaID = '$media'";
    tng_query($query);
  }
}

$rval = 1;
switch ($action) {
  case 'order':
    $links = explode(',', $sequence);
    $count = count($links);
    if ($album) {
      for ($i = 0; $i < $count; $i++) {
        $order = $i + 1;
        $query = "UPDATE $albumlinks_table SET ordernum=\"$order\" WHERE albumlinkID=\"" . $links[$i] . '"';
        $result = tng_query($query);
      }
    } else {
      for ($i = 0; $i < $count; $i++) {
        $order = $i + 1;
        $query = "UPDATE $medialinks_table SET ordernum=\"$order\" WHERE medialinkID=\"" . $links[$i] . '"';
        $result = tng_query($query);
      }
    }
    break;
  case 'alborder':
    $alinks = explode(',', $sequence);
    $count = count($alinks);
    for ($i = 0; $i < $count; $i++) {
      $order = $i + 1;
      $query = "UPDATE $album2entities_table SET ordernum=\"$order\" WHERE alinkID = \"" . $alinks[$i] . '"';
      $result = tng_query($query);
    }
    break;
  case 'mworder':
    $links = explode(',', $sequence);
    $count = count($links);
    for ($i = 0; $i < $count; $i++) {
      $order = $i + 1;
      $query = "UPDATE $mostwanted_table SET ordernum=\"$order\", mwtype=\"$mwtype\" WHERE ID = \"" . $links[$i] . '"';
      $result = tng_query($query);
    }
    break;
  case 'childorder':
    $clinks = explode(',', $sequence);
    $count = count($clinks);
    for ($i = 0; $i < $count; $i++) {
      $order = $i + 1;
      $query = "UPDATE $children_table SET ordernum=\"$order\" WHERE familyID = \"$familyID\" AND personID = \"$clinks[$i]\"";
      $result2 = tng_query($query);
    }
    break;
  case 'parentorder':
    $plinks = explode(',', $sequence);
    $count = count($plinks);
    for ($i = 0; $i < $count; $i++) {
      $order = $i + 1;
      $query = "UPDATE $children_table SET parentorder=\"$order\" WHERE familyID = \"$plinks[$i]\" AND personID = '$personID'";
      $result2 = tng_query($query);
    }
    break;
  case 'spouseorder':
    $slinks = explode(',', $sequence);
    $count = count($slinks);
    for ($i = 0; $i < $count; $i++) {
      $order = $i + 1;
      $query = "UPDATE $families_table SET $spouseorder=\"$order\" WHERE familyID = \"$slinks[$i]\"";
      $result2 = tng_query($query);
    }
    break;
  case 'noteorder':
    $nlinks = explode(',', $sequence);
    $count = count($nlinks);
    for ($i = 0; $i < $count; $i++) {
      $order = $i + 1;
      $query = "UPDATE notelinks SET ordernum=\"$order\" WHERE ID = \"$nlinks[$i]\"";
      $result2 = tng_query($query);
    }
    break;
  case 'citeorder':
    $clinks = explode(',', $sequence);
    $count = count($clinks);
    for ($i = 0; $i < $count; $i++) {
      $order = $i + 1;
      $query = "UPDATE $citations_table SET ordernum=\"$order\" WHERE citationID = \"$clinks[$i]\"";
      $result2 = tng_query($query);
    }
    break;
  case 'spouseunlink':
    $query = "SELECT husband, wife FROM $families_table WHERE familyID = '$familyID'";
    $marriage = tng_query($query);
    $marriagerow = tng_fetch_assoc($marriage);

    if ($personID == $marriagerow['husband']) {
      $delspousestr = 'husband = ""';
    } else {
      if ($personID == $marriagerow['wife']) {
        $delspousestr = 'wife = ""';
      } else {
        $spquery = '';
        $delspousestr = '';
      }
    }
    if ($delspousestr) {
      $query = "UPDATE $families_table SET $delspousestr WHERE familyID = '$familyID'";
      $spouseresult = tng_query($query);
    }
    break;
  case 'parentunlink':
    $query = "DELETE FROM $children_table WHERE familyID = '$familyID' AND personID = '$personID'";
    $result2 = tng_query($query);

    $query = "UPDATE $people_table SET famc=\"\" WHERE personID = '$personID' AND famc = '$familyID'";
    $result2 = tng_query($query);
    break;
  case 'addchild':
    $haskids = getHasKids($personID);

    $query = "INSERT INTO $children_table (familyID, personID, ordernum, mrel, frel, haskids, parentorder, sealdate, sealdatetr, sealplace) VALUES ('$familyID', '$personID', $order, '', '', $haskids, 0, '', '0000-00-00', '')";
    $result = tng_query($query);

    $query = "SELECT husband,wife FROM $families_table WHERE familyID = '$familyID'";
    $result = tng_query($query);
    $famrow = tng_fetch_assoc($result);
    if ($famrow['husband']) {
      $query = "UPDATE $children_table SET haskids=\"1\" WHERE personID = \"{$famrow['husband']}\"";
      $result2 = tng_query($query);
    }
    if ($famrow['wife']) {
      $query = "UPDATE $children_table SET haskids=\"1\" WHERE personID = \"{$famrow['wife']}\"";
      $result2 = tng_query($query);
    }
    tng_free_result($result);

    $query = "UPDATE $people_table SET famc=\"$familyID\" WHERE personID = '$personID' and famc = \"\"";
    $result = tng_query($query);

    $rval = "<div class=\"sortrow\" id=\"child_$personID\" style=\"width:500px;clear:both;\"";
    $rval .= " onmouseover=\"$('#unlinkc_$personID').css('visibility','visible');\" onmouseout=\"$('#unlinkc_$personID').css('visibility','hidden');\">\n";
    $rval .= "<table width='100%'><tr>\n";
    $rval .= "<td class='dragarea'>";
    $rval .= "<img src='img/admArrowUp.gif' alt=''>" . uiTextSnippet('drag') . "\n";
    $rval .= "<img src='img/admArrowDown.gif' alt=''>\n";
    $rval .= "</td>\n";
    $rval .= "<td class=\"childblock\">\n";

    $rval .= "<div id=\"unlinkc_$personID\" class=\"small hide-right\"><a href='#' onclick=\"return unlinkChild('$personID','child_unlink');\">" . uiTextSnippet('remove') . '</a>';
    if ($allowDelete) {
      $rval .= " &nbsp; | &nbsp; <a href='#' onclick=\"return unlinkChild('$personID','child_delete');\">" . uiTextSnippet('delete') . '</a>';
    }
    $rval .= '</div>';
    $display = str_replace('|', '</a>', $display);
    $rval .= "<a href='#' onclick=\"EditChild('$personID');\">$display</div>\n</td>\n</tr>\n</table>\n</div>\n";
    break;
  case 'setdef':
    setDefault($entity, $media, $album);

    $query = "SELECT thumbpath, usecollfolder, mediatypeID FROM $media_table
      WHERE mediaID = \"$media\"";
    $result = tng_query($query);
    if ($result) {
      $row = tng_fetch_assoc($result);
    }
    $thismediatypeID = $row['mediatypeID'];
    $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$thismediatypeID] : $mediapath;
    tng_free_result($result);

    if ($row['thumbpath']) {
      $photoref = "$usefolder/" . $row['thumbpath'];
    } else {
      $photoref = "$photopath/$entity.$photosext";
    }

    if (file_exists("$rootpath$photoref")) {
      $photoinfo = getimagesize("$rootpath$photoref");
      if ($photoinfo[1] <= $thumbmaxh) {
        $photohtouse = $photoinfo[1];
        $photowtouse = $photoinfo[0];
      } else {
        $photohtouse = $thumbmaxh;
        $photowtouse = intval($thumbmaxh * $photoinfo[0] / $photoinfo[1]);
      }
      $rval = '<img src="' . str_replace('%2F', '/', rawurlencode($photoref)) . '?' . time() . "\" alt='' width=\"$photowtouse\" height=\"$photohtouse\" style=\"margin-right:10px\">";
    }
    break;
  case 'setdef2':
    setDefault($entity, $media, $album);
    break;
  case 'setdef3':
    $query = "UPDATE $medialinks_table SET defphoto = '' WHERE defphoto = '1' AND personID = '$entity'";
    $result = tng_query($query);

    $query = "UPDATE $medialinks_table SET defphoto = '$toggle' WHERE medialinkID=\"$medialinkID\"";
    $result = tng_query($query);
    break;
  case 'deldef':
    //look for old style default, delete if exists
    if ($album) {
      $query = "SELECT thumbpath, usecollfolder, mediatypeID, albumlinkID FROM ($media_table, $albumlinks_table)
        WHERE albumID = \"$album\" AND $media_table.mediaID = $albumlinks_table.mediaID AND defphoto = '1'";
    } else {
      $query = "SELECT thumbpath, usecollfolder, mediatypeID, medialinkID FROM ($media_table, $medialinks_table)
        WHERE personID = '$entity' AND $media_table.mediaID = $medialinks_table.mediaID AND defphoto = '1'";
    }
    $result = tng_query($query);
    if ($result) {
      $row = tng_fetch_assoc($result);
    }

    $thismediatypeID = $row['mediatypeID'];
    $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$thismediatypeID] : $mediapath;
    tng_free_result($result);

    if ($album) {
      $query = "UPDATE $albumlinks_table SET defphoto = '' WHERE albumlinkID = '{$row['albumlinkID']}'";
    } else {
      $query = "UPDATE $medialinks_table SET defphoto = '' WHERE medialinkID = '{$row['medialinkID']}'";
    }
    $result = tng_query($query);
    break;
  case 'show':
    $query = "UPDATE $medialinks_table SET dontshow = $toggle WHERE medialinkID=\"$medialinkID\"";
    $result = tng_query($query);
    break;
  case 'remalb':
    $query = "DELETE FROM $albumlinks_table WHERE albumlinkID=\"$albumlink\"";
    $result = tng_query($query);
    $rval = $media . '&' . $albumlink;
    break;
  case 'remmostwanted':
    $query = "DELETE FROM $mostwanted_table WHERE ID=\"$id\"";
    $result = tng_query($query);
    $rval = $id;
    break;
  case 'remsort':
    if ($type == 'album') {
      $query = "DELETE FROM $album2entities_table WHERE alinkID=\"$link\"";
      $result = tng_query($query);
    } elseif ($type == 'media') {
      $query = "DELETE FROM $medialinks_table WHERE medialinkID=\"$link\"";
      $result = tng_query($query);
    }
    $rval = $link;
    break;
  case 'addcemlink':
    $query = "UPDATE $cemeteries_table SET place = \"" . urldecode($place) . "\" WHERE cemeteryID = '$cemeteryID'";
    $result = tng_query($query);

    //get cemname, location from cemetery, pass back in json
    $query = "SELECT cemname, city, county, state, country FROM $cemeteries_table WHERE cemeteryID = '$cemeteryID'";
    $result = tng_query($query);
    $cemrow = tng_fetch_assoc($result);
    $location = $cemrow['cemname'];
    if ($cemrow['city']) {
      if ($location) {
        $location .= ', ';
      }
      $location .= $cemrow['city'];
    }
    if ($cemrow['county']) {
      if ($location) {
        $location .= ', ';
      }
      $location .= $cemrow['county'];
    }
    if ($cemrow['state']) {
      if ($location) {
        $location .= ', ';
      }
      $location .= $cemrow['state'];
    }
    if ($cemrow['country']) {
      if ($location) {
        $location .= ', ';
      }
      $location .= $cemrow['country'];
    }
    $rval = "{\"location\":\"$location\"}";
    tng_free_result($result);
    break;
  case 'geocopy':
    $query = "UPDATE $cemeteries_table SET latitude = '$latitude', longitude = '$longitude', zoom = '$zoom' WHERE cemeteryID = '$cemeteryID'";
    $result = tng_query($query);

    $success = $result ? '1' : '0';
    $rval = "{\"result\":\"$success\"}";
    break;
  case 'add':
    //add photo to album at end
    $query2 = "SELECT max(ordernum) AS maxordernum FROM $albumlinks_table WHERE albumID = \"$album\" GROUP BY albumID";
    $result2 = tng_query($query2) or die(uiTextSnippet('cannotexecutequery') . ": $query2");
    $row2 = tng_fetch_assoc($result2);
    $count = $row2['maxordernum'] + 1;
    tng_free_result($result2);

    if ($count == 1) {
      $query = "INSERT INTO $albumlinks_table (albumID,mediaID,ordernum,defphoto) VALUES (\"$album\", \"$media\", \"$count\", \"1\")";
    } else {
      $query = "INSERT INTO $albumlinks_table (albumID,mediaID,ordernum,defphoto) VALUES (\"$album\", \"$media\", \"$count\",\"0\")";
    }
    $result = tng_query($query);
    $albumlinkID = tng_insert_id();
    $rval = $media . '&' . $albumlinkID;
    break;
  case 'dellink':
    if ($type == 'album') {
      $query = "SELECT entityID FROM $album2entities_table WHERE alinkID = '$linkID'";
    } else {
      $query = "SELECT personID AS entityID, eventID, mediatypeID FROM ($medialinks_table, $media_table) WHERE medialinkID = '$linkID' AND $medialinks_table.mediaID = $media_table.mediaID";
    }
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    $entityID = $row['entityID'];

    tng_free_result($result);

    if ($type == 'album') {
      $query = "DELETE FROM $album2entities_table WHERE alinkID=\"$linkID\"";
    } else {
      $query = "DELETE FROM $medialinks_table WHERE medialinkID=\"$linkID\"";
    }
    $result = tng_query($query);

    $query2 = "SELECT personID FROM $people_table WHERE personID = '$entityID'";
    reorderMedia($query2, $row);

    $query2 = "SELECT familyID AS personID FROM $families_table WHERE familyID = '$entityID'";
    reorderMedia($query2, $row);

    $query2 = "SELECT sourceID AS personID FROM sources WHERE sourceID = '$entityID'";
    reorderMedia($query2, $row);

    $query2 = "SELECT repoID AS personID FROM repositories WHERE repoID = '$entityID'";
    reorderMedia($query2, $row);

    $rval = $linkID . '&' . $entityID;
    break;
  case 'updatelink':
    //check if thumb exists before making default? We used to do that
    if ($type == 'album') {
      $query = "UPDATE $album2entities_table SET eventID = '$eventID' WHERE alinkID = $linkID";
      $result = tng_query($query);
    } else {
      if ($session_charset != 'UTF-8') {
        $altdescription = tng_utf8_decode($altdescription);
        $altnotes = tng_utf8_decode($altnotes);
      }
      $altdescription = addslashes($altdescription);
      $altnotes = addslashes($altnotes);

      $dontshow = $show ? '0' : '1';
      $query = "UPDATE $medialinks_table SET defphoto = '$defphoto', altdescription = '$altdescription', altnotes = '$altnotes', eventID = '$eventID', dontshow = $dontshow WHERE medialinkID = $linkID";
      $result = tng_query($query);

      if ($defphoto) {
        $query = "UPDATE $medialinks_table SET defphoto = '' WHERE personID = '$personID' AND medialinkID != $linkID";
        $result = tng_query($query);
      }
    }
    break;
  case 'addlink':
    include 'prefixes.php';
    switch ($linktype) {
      case 'I':
        $prefix = $personprefix;
        $suffix = $personsuffix;
        break;
      case 'F':
        $prefix = $familyprefix;
        $suffix = $familysuffix;
        break;
      case 'S':
        $prefix = $sourceprefix;
        $suffix = $sourcesuffix;
        break;
      case 'R':
        $prefix = $repoprefix;
        $suffix = $reposuffix;
        break;
      default:
        $prefix = $suffix = '';
        break;
    }
    $entityID = tng_utf8_decode(trim($entityID));
    $prefixlen = strlen($prefix);
    $suffixlen = strlen($suffix);
    $entity_prefix = substr($entityID, 0, $prefixlen);
    $entity_suffix = substr($entityID, -1 * $suffixlen);
    if ($prefix && $entity_prefix != $prefix) {
      $entityID = $prefix . $entityID;
    }
    if ($suffix && $entity_suffix != $suffix) {
      $entityID = $entityID . $suffix;
    }
    if ($type == 'album') {
      $query = "SELECT count(alinkID) AS count FROM $album2entities_table WHERE entityID = \"$entityID\"";
    } else {
      $query = "SELECT count(medialinkID) AS count FROM $medialinks_table WHERE personID = \"$entityID\"";
    }
    $result = tng_query($query);
    if ($result) {
      $row = tng_fetch_assoc($result);
      $newrow = $row['count'] + 1;
      tng_free_result($result);
    } else {
      $newrow = 1;
    }

    $numrows = 0;
    switch ($linktype) {
      case 'I':
        $query = "SELECT firstname, lnprefix, lastname, prefix, suffix, title, living, private, nameorder, branch FROM $people_table WHERE personID = '$entityID'";
        $result = tng_query($query);
        $row = tng_fetch_assoc($result);
        $rights = determineLivingPrivateRights($row);
        $row['allow_living'] = $rights['living'];
        $row['allow_private'] = $rights['private'];
        $name = getName($row);

        $numrows = tng_num_rows($result);
        tng_free_result($result);
        break;
      case 'F':
        $joinonwife = "LEFT JOIN $people_table AS wifepeople ON $families_table.wife = wifepeople.personID";
        $joinonhusb = "LEFT JOIN $people_table AS husbpeople ON $families_table.husband = husbpeople.personID";
        $query = "SELECT wifepeople.personID AS wpersonID, wifepeople.firstname AS wfirstname, wifepeople.lnprefix AS wlnprefix, wifepeople.lastname AS wlastname, wifepeople.prefix AS wprefix, wifepeople.suffix AS wsuffix, wifepeople.nameorder AS wnameorder, wifepeople.branch AS wbranch, husbpeople.personID AS hpersonID, husbpeople.firstname AS hfirstname, husbpeople.lnprefix AS hlnprefix, husbpeople.lastname AS hlastname, husbpeople.prefix AS hprefix, husbpeople.suffix AS hsuffix, husbpeople.nameorder AS hnameorder, husbpeople.branch AS hbranch FROM $families_table $joinonwife $joinonhusb WHERE familyID = '$entityID'";
        $result = tng_query($query);
        $row = tng_fetch_assoc($result);
        $name = '';

        if ($row['hpersonID']) {
          $person['personID'] = $row['hpersonID'];
          $person['firstname'] = $row['hfirstname'];
          $person['lnprefix'] = $row['hlnprefix'];
          $person['lastname'] = $row['hlastname'];
          $person['prefix'] = $row['hprefix'];
          $person['suffix'] = $row['hsuffix'];
          $person['nameorder'] = $row['hnameorder'];
          $person['branch'] = $row['hbranch'];

          $prights = determineLivingPrivateRights($person);
          $person['allow_living'] = $prights['living'];
          $person['allow_private'] = $prights['private'];

          $name .= getName($person);
        }
        $name .= ', ';
        if ($row['wpersonID']) {
          $person['personID'] = $row['wpersonID'];
          $person['firstname'] = $row['wfirstname'];
          $person['lnprefix'] = $row['wlnprefix'];
          $person['lastname'] = $row['wlastname'];
          $person['prefix'] = $row['wprefix'];
          $person['suffix'] = $row['wsuffix'];
          $person['nameorder'] = $row['wnameorder'];
          $person['branch'] = $row['wbranch'];

          $prights = determineLivingPrivateRights($person);
          $person['allow_living'] = $prights['living'];
          $person['allow_private'] = $prights['private'];

          $name .= getName($person);
        }

        $numrows = tng_num_rows($result);
        tng_free_result($result);
        break;
      case 'S':
        $query = "SELECT title FROM sources WHERE sourceID = '$entityID'";
        $result = tng_query($query);
        $row = tng_fetch_assoc($result);
        $name = $row['title'];
        $truncated = substr($row['title'], 0, 90);
        $name = strlen($row['title']) > 90 ? substr($truncated, 0, strrpos($truncated, ' ')) . '&hellip;' : $row['title'];
        $numrows = tng_num_rows($result);
        tng_free_result($result);
        break;
      case 'R':
        $query = "SELECT reponame FROM repositories WHERE repoID = '$entityID'";
        $result = tng_query($query);
        $row = tng_fetch_assoc($result);
        $name = $row['reponame'];
        $truncated = substr($row['reponame'], 0, 90);
        $name = strlen($row['reponame']) > 90 ? substr($truncated, 0, strrpos($truncated, ' ')) . '&hellip;' : $row['reponame'];
        $numrows = tng_num_rows($result);
        tng_free_result($result);
        break;
      case 'L':
        $query = "SELECT place FROM places WHERE place = \"$entityID\"";
        $result = tng_query($query);
        $numrows = tng_num_rows($result);
        tng_free_result($result);

        $name = stripslashes($entityID);

        if (!$numrows) {
          $query = "INSERT IGNORE INTO places (place, placelevel, temple, latitude, longitude, zoom, notes, geoignore) VALUES ('$entityID', '0', '0', '', '', '13', '', '0')";
          $result = tng_query($query);
          $numrows = 1;
        }
        break;
    }

    if ($numrows) {
      if ($type == 'album') {
        $query = "INSERT IGNORE INTO $album2entities_table (entityID, albumID, ordernum, linktype) VALUES ('$entityID', '$albumID', '$newrow', '$linktype')";
      } else {
        $query = "INSERT IGNORE INTO $medialinks_table (personID, mediaID, ordernum, linktype, eventID) VALUES ('$entityID', '$mediaID', '$newrow', '$linktype', '')";
      }

      $result = tng_query($query);
      $success = tng_affected_rows();
      if ($success) {
        $linkID = tng_insert_id();
        $rval = $linkID . '|' . $name;
        $query = "SELECT thumbpath, mediatypeID, usecollfolder FROM $media_table WHERE mediaID = \"$mediaID\"";
        $result = tng_query($query);
        $row = tng_fetch_assoc($result);
        $mediatypeID = $row['mediatypeID'];
        $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
        $rval .= '|';
        $rval .= $row['thumbpath'] && file_exists("$rootpath$usefolder/" . $row['thumbpath']) ? '1' : '0';
        $rval .= '|' . $row['mediatypeID'];
        tng_free_result($result);
      } else {
        $rval = 1;
      }    //duplicate
    } else {
      $rval = 2;
    }    //invalid
    break;
  case 'masslink':
    $entityID = tng_utf8_decode($newlink1);
    $query = "SELECT count(medialinkID) AS count FROM $medialinks_table WHERE personID = \"$entityID\"";
    $result = tng_query($query);
    if ($result) {
      $row = tng_fetch_assoc($result);
      $newrow = $row['count'] + 1;
      tng_free_result($result);
    } else {
      $newrow = 1;
    }

    $newlinks = 0;
    $mediaIDs = explode(',', $medialist);
    foreach ($mediaIDs as $mediaID) {
      $query = "INSERT IGNORE INTO $medialinks_table (personID, mediaID, ordernum, linktype, eventID) VALUES ('$entityID', '$mediaID', '$newrow', '$linktype1', '$event1')";
      $result = tng_query($query);
      if (tng_affected_rows()) {
        $newlinks += 1;
        $newrow += 1;
      }
    }
    $rval = 'Links created: ' . $newlinks;
    break;
  case 'qmedia':
    if ($session_charset != 'UTF-8') {
      $title = tng_utf8_decode($title);
      $description = tng_utf8_decode($description);
    }
    $title = addslashes($title);
    $description = addslashes($description);
    $owner = addslashes($owner);
    $datetaken = addslashes($datetaken);

    $query = "UPDATE $media_table SET description = \"$title\", owner = \"$owner\", datetaken = \"$datetaken\", notes = \"$description\" WHERE mediaID = \"$mediaID\"";
    $result = tng_query($query);
    $rval = 1;
    break;
}

if ($json) {
  header('Content-Type: application/json; charset=' . $session_charset);
} else {
  header('Content-type:text/html; charset=' . $session_charset);
}

echo $rval;