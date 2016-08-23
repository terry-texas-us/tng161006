<?php

function getAlbumPhoto($albumID, $albumname) {
  global $rootpath;
  global $media_table;
  global $albumlinks_table;
  global $people_table;
  global $families_table;
  global $citations_table;
  global $medialinks_table;
  global $mediatypes_assoc;
  global $mediapath;
  
  $query2 = "SELECT path, thumbpath, usecollfolder, mediatypeID, $albumlinks_table.mediaID as mediaID, alwayson FROM ($media_table, $albumlinks_table)
    WHERE albumID = \"$albumID\" AND $media_table.mediaID = $albumlinks_table.mediaID AND defphoto=\"1\"";
  $result2 = tng_query($query2) or die(uiTextSnippet('cannotexecutequery') . ": $query2");
  $trow = tng_fetch_assoc($result2);
  $mediaID = $trow['mediaID'];
  $tmediatypeID = $trow['mediatypeID'];
  $tusefolder = $trow['usecollfolder'] ? $mediatypes_assoc[$tmediatypeID] : $mediapath;
  tng_free_result($result2);

  $imgsrc = "";
  if ($trow['thumbpath'] && file_exists("$rootpath$tusefolder/" . $trow['thumbpath'])) {
    $foundliving = 0;
    $foundprivate = 0;
    if (!$trow['alwayson'] && $livedefault != 2) {
      $query = "SELECT people.living as living, people.private as private, people.branch as branch, $families_table.branch as fbranch, $families_table.living as fliving, $families_table.private as fprivate, linktype, $medialinks_table.gedcom as gedcom
        FROM $medialinks_table
        LEFT JOIN $people_table AS people ON $medialinks_table.personID = people.personID
        LEFT JOIN $families_table ON $medialinks_table.personID = $families_table.familyID
        WHERE mediaID = '$mediaID'";
      $presult = tng_query($query);
      while ($prow = tng_fetch_assoc($presult)) {
        if ($prow['fbranch'] != null) {
          $prow['branch'] = $prow['fbranch'];
        }
        if ($prow['fliving'] != null) {
          $prow['living'] = $prow['fliving'];
        }
        if ($prow['fprivate'] != null) {
          $prow['private'] = $prow['fprivate'];
        }
        //if living still null, must be a source

        $rights = determineLivingPrivateRights($prow);
        $prow['allow_living'] == $rights['living'];
        $prow['allow_private'] == $rights['private'];

        if ($prow['living'] == null && $prow['private'] == null && $prow['linktype'] == 'I') {
          $query = "SELECT count(personID) as ccount FROM $citations_table, $people_table
            WHERE $citations_table.sourceID = '{$prow['personID']}' AND $citations_table.persfamID = $people_table.personID
            AND living = '1'";
          $presult2 = tng_query($query);
          $prow2 = tng_fetch_assoc($presult2);
          if ($prow2['ccount']) {
            $prow['living'] = 1;
          }
          tng_free_result($presult2);
        } elseif ($prow['living'] == null && $prow['private'] == null && $prow['linktype'] == 'F') {
          $query = "SELECT count(familyID) as ccount FROM $citations_table, $families_table
            WHERE $citations_table.sourceID = '{$prow['personID']}' AND $citations_table.persfamID = $families_table.familyID
            AND living = '1'";
          $presult2 = tng_query($query);
          $prow2 = tng_fetch_assoc($presult2);
          if ($prow2['ccount']) {
            $prow['living'] = 1;
          }
          tng_free_result($presult2);
        }
        if ($prow['living'] && !$rights['living']) {
          $foundliving = 1;
        }
        if ($prow['private'] && !$rights['private']) {
          $foundprivate = 1;
        }
      }
    }
    if (!$foundliving && !$foundprivate) {
      $size = getimagesize("$rootpath$tusefolder/{$trow['thumbpath']}");
      $imgsrc = "<div class=\"media-img\"><div class=\"media-prev\" id=\"prev$albumID\" style=\"display:none\"></div></div>\n";
      $imgsrc .= "<a href=\"albumsShowAlbum.php?albumID=$albumID\" title=\"" . uiTextSnippet('albclicksee') . "\"";
      if (function_exists(imageJpeg)) {
        $imgsrc .= " onmouseover=\"showPreview('$albumID','" . urlencode("$tusefolder/{$trow['path']}") . "','');\" onmouseout=\"closePreview('$albumID','');\" onclick=\"closePreview('$albumID','');\"";
      }
      $imgsrc .= "><img src=\"$tusefolder/" . str_replace("%2F", "/", rawurlencode($trow['thumbpath'])) . "\" class=\"thumb\" $size[3] alt=\"$albumname\"></a>";
    }
  }
  return $imgsrc;
}
