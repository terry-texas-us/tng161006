<?php

function get_item_id($result, $offset, $itemname) {
  if (!tng_data_seek($result, $offset)) {
    return (0);
  }
  $row = tng_fetch_assoc($result);

  return $row[$itemname];
}

function get_item_id_pair($result, $offset, $itemname, $needamp) {
  $item = get_item_id($result, $offset, $itemname);
  if ($item) {
    $str = $itemname . '=' . $item;
    if ($needamp) {
      $str = '&amp;' . $str;
    }
  } else {
    $str = '';
  }

  return $str;
}

function get_media_offsets($result, $mediaID) {
  tng_data_seek($result, 0);
  $found = 0;
  for ($i = 0; $i < tng_num_rows($result); $i++) {
    $row = tng_fetch_assoc($result);
    if ($row['mediaID'] == $mediaID) {
      $found = 1;
      break;
    }
  }
  if (!$found && $i) {
    $i--;
  }
  $nexttolast = tng_num_rows($result) - 1;
  $prev = $i ? $i - 1 : $nexttolast;
  $next = $i < $nexttolast ? $i + 1 : 0;

  return [$i, $prev, $next, $nexttolast];
}

function buildMediaPaginationLinkHtml($result, $address, $page, $jumpfunc, $title, $label, $allstr, $showlinks) {
  global $cemeteryID;

  $mediaID = get_item_id($result, $page - 1, 'mediaID');
  $medialinkID = get_item_id($result, $page - 1, 'medialinkID');
  $albumlinkID = get_item_id($result, $page - 1, 'albumlinkID');

  if ($showlinks) {
    $href = $mediaID ? '&amp;mediaID=' . $mediaID : '';
    $href .= $medialinkID ? '&amp;medialinkID=' . $medialinkID : '';
    $href .= $albumlinkID ? '&amp;albumlinkID=' . $albumlinkID : '';
    $href .= $cemeteryID ? '&amp;cemeteryID=' . get_item_id($result, $page - 1, 'cemeteryID') : '';
    $href .= $allstr . "&amp;tngpage=$page";
    if (substr($href, 0, 5) == '&amp;') {
      $href = substr($href, 5);
    }
    $link = "<a class='page-link' href=\"$address$href\" title=\"$title\">$label</a> ";
  } else {
    $link = "<a class='page-link' href='#' onclick=\"return $jumpfunc('$mediaID','$medialinkID','$albumlinkID')\" title=\"$title\">$label</a> ";
  }

  return $link;
}

function doMedia($mediatypeID) {
  global $change_limit;
  global $cutoffstr;
  global $wherestr;
  global $nonames;
  global $currentuser;
  global $rootpath;
  global $mediapath;
  global $header;
  global $footer;
  global $mediatypes_assoc;
  global $mediatypes_display;
  global $whatsnew;
  global $thumbmaxw;
  global $altstr;
  global $tngconfig;

  if ($mediatypeID == 'headstones') {
    $hsfields = ', media.cemeteryID, cemname, city';
    $hsjoin = 'LEFT JOIN cemeteries ON media.cemeteryID = cemeteries.cemeteryID';
  } else {
    $hsfields = $hsjoin = '';
  }

  $query = "SELECT distinct media.mediaID AS mediaID, description $altstr, media.notes, thumbpath, path, form, mediatypeID, alwayson, usecollfolder, DATE_FORMAT(changedate,'%e %b %Y') AS changedatef, changedby, status, plot, abspath, newwindow $hsfields FROM media $hsjoin";
  if ($wherestr) {
    $query .= ' LEFT JOIN medialinks ON media.mediaID = medialinks.mediaID';
  }
  $query .= " WHERE $cutoffstr $wherestr mediatypeID = \"$mediatypeID\" ORDER BY ";
  if (strpos($_SERVER['SCRIPT_NAME'], 'placesearch') !== false) {
    $query .= 'ordernum';
  } else {
    $query .= 'changedate DESC, description';
  }
  $query .= " LIMIT $change_limit";
  $mediaresult = tng_query($query);

  $titlemsg = uiTextSnippet($mediatypeID) ? uiTextSnippet($mediatypeID) : $mediatypes_display[$mediatypeID];
  $mediaheader = "<div class='card'>\n";
  $mediaheader .= "<div class='card-header'><h4>$titlemsg</h4></div>\n";
  $mediaheader .= "<div class='card-block'>\n";
  $mediaheader .= $header;

  $mediatext = '';
  $thumbcount = 0;
  $gotImageJpeg = function_exists(imageJpeg);

  while ($row = tng_fetch_assoc($mediaresult)) {
    $mediatypeID = $row['mediatypeID'];
    $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;

    $status = $row['status'];
    if ($status && uiTextSnippet($status)) {
      $row['status'] = uiTextSnippet($status);
    }

    $query = "SELECT medialinkID, medialinks.personID AS personID, medialinks.eventID, people.personID AS personID2, familyID, people.living AS living, people.private AS private, people.branch AS branch, families.branch as fbranch, families.living as fliving, families.private as fprivate, husband, wife, people.lastname as lastname, people.lnprefix as lnprefix, people.firstname as firstname, people.prefix as prefix, people.suffix as suffix, nameorder, sources.title, sources.sourceID, repositories.repoID,reponame, deathdate, burialdate, linktype FROM medialinks LEFT JOIN people AS people ON (medialinks.personID = people.personID) LEFT JOIN families ON (medialinks.personID = families.familyID) LEFT JOIN sources ON (medialinks.personID = sources.sourceID) LEFT JOIN repositories ON (medialinks.personID = repositories.repoID) WHERE mediaID = \"{$row['mediaID']}\"$Wherestr2 ORDER BY lastname, lnprefix, firstname, medialinks.personID";
    $presult = tng_query($query);
    $foundliving = 0;
    $foundprivate = 0;
    $medialinktext = '';
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
      if ($prow['living'] == null && $prow['private'] == null && $prow['linktype'] == 'I') {
        $query = "SELECT count(personID) AS ccount FROM citations, people
          WHERE citations.sourceID = '{$prow['personID']}' AND citations.persfamID = people.personID AND (living = '1' OR private = '1')";
        $presult2 = tng_query($query);
        $prow2 = tng_fetch_assoc($presult2);
        if ($prow2['ccount']) {
          $prow['living'] = 1;
        }
        tng_free_result($presult2);
      }

      $rights = determineLivingPrivateRights($prow);
      $prow['allow_living'] = $rights['living'];
      $prow['allow_private'] = $rights['private'];

      if (!$rights['living']) {
        $foundliving = 1;
      }
      if (!$rights['private']) {
        $foundprivate = 1;
      }

      $hstext = '';
      if ($prow['personID2'] != null) {
        $medialinktext .= "<li><a href=\"peopleShowPerson.php?personID={$prow['personID2']}\">";
        $medialinktext .= getName($prow);
        if ($mediatypeID == 'headstones') {
          $deathdate = $prow['deathdate'] ? $prow['deathdate'] : $prow['burialdate'];
          if ($prow['deathdate']) {
            $abbrev = uiTextSnippet('deathabbr');
          } elseif ($prow['burialdate']) {
            $abbrev = uiTextSnippet('burialabbr');
          }
          $hstext = $deathdate ? " ($abbrev " . displayDate($deathdate) . ')' : '';
        }
      } elseif ($prow['familyID'] != null) {
        $medialinktext .= "<li><a href=\"familiesShowFamily.php?familyID={$prow['familyID']}\">" . uiTextSnippet('family') . ': ' . getFamilyName($prow);
      } elseif ($prow['sourceID'] != null) {
        $sourcetext = $prow['title'] ? uiTextSnippet('source') . ': ' . $prow['title'] : uiTextSnippet('source') . ': ' . $prow['sourceID'];
        $medialinktext .= "<li><a href=\"sourcesShowSource.php?sourceID={$prow['sourceID']}\">$sourcetext";
      } elseif ($prow['repoID'] != null) {
        $repotext = $prow['reponame'] ? uiTextSnippet('repository') . ': ' . $prow['reponame'] : uiTextSnippet('repository') . ': ' . $prow['repoID'];
        $medialinktext .= "<li><a href=\"repositoriesShowItem.php?repoID={$prow['repoID']}\">$repotext";
      } else {
        $medialinktext .= '<li><a href="placesearch.php?psearch=' . urlencode($prow['personID']) . '">' . $prow['personID'];
      }
      if ($prow['eventID']) {
        $query = "SELECT display FROM events, eventtypes WHERE eventID = \"{$prow['eventID']}\" AND events.eventtypeID = eventtypes.eventtypeID";
        $eresult = tng_query($query);
        $erow = tng_fetch_assoc($eresult);
        $event = $erow['display'] && is_numeric($prow['eventID']) ? getEventDisplay($erow['display']) : (uiTextSnippet($prow['eventID']) ? uiTextSnippet($prow['eventID']) : $prow['eventID']);
        tng_free_result($eresult);
        $medialinktext .= " ($event)";
      }
      $medialinktext .= "</a>$hstext\n</li>\n";
    }
    tng_free_result($presult);
    if ($medialinktext) {
      $medialinktext = "<ul>$medialinktext</ul>\n";
    }

    $showPhotoInfo = $row['allow_living'] = $row['alwayson'] || (!$foundprivate && !$foundliving);

    $href = getMediaHREF($row, 0);
    $notes = $wherestr && $row['altnotes'] ? $row['altnotes'] : $row['notes'];
    $notes = nl2br(truncateIt(getXrefNotes($row['notes']), $tngconfig['maxnoteprev']));
    $description = $wherestr && $row['altdescription'] ? $row['altdescription'] : $row['description'];

    if ($row['allow_living']) {
      $description = $showPhotoInfo ? "<a href=\"$href\">$description</a>" : $description;
    } else {
      $nonamesloc = $row['private'] ? $tngconfig['nnpriv'] : $nonames;
      if ($nonamesloc) {
        $description = uiTextSnippet('livingphoto');
        $notes = '';
      } else {
        $notes = $notes ? $notes . '<br>(' . uiTextSnippet('livingphoto') . ')' : '(' . uiTextSnippet('livingphoto') . ')';
      }
      $href = '';
    }

    $mediatext .= '<tr>';
    $row['mediatypeID'] = $mediatypeID;
    $imgsrc = getSmallPhoto($row);
    if ($imgsrc) {
      $mediatext .= "<td style=\"width:$thumbmaxw" . 'px">';
      $mediatext .= "<div class=\"media-img\"><div class=\"media-prev\" id=\"prev{$row['mediaID']}\" style='display: none'></div></div>\n";
      if ($href && $row['allow_living']) {
        $mediatext .= "<a href=\"$href\"";
        if ($gotImageJpeg && isPhoto($row) && checkMediaFileSize("$rootpath$usefolder/" . $row['path'])) {
          $mediatext .= " class=\"media-preview\" id=\"img-{$row['mediaID']}-0-" . urlencode("$usefolder/{$row['path']}") . '"';
        }
        $mediatext .= ">$imgsrc</a>";
      } else {
        $mediatext .= $imgsrc;
      }
      $mediatext .= '</td><td>';
      $thumbcount++;
    } else {
      $mediatext .= '<td>&nbsp;</td><td>';
    }

    $mediatext .= "$description<br>$notes&nbsp;</td>";
    if ($mediatypeID == 'headstones') {
      if (!$row['cemname']) {
        $row['cemname'] = $row['city'];
      }
      $mediatext .= "<td><a href=\"cemeteriesShowCemetery.php?cemeteryID={$row['cemeteryID']}\">{$row['cemname']}</a>";
      if ($row['plot']) {
        $mediatext .= '<br>';
      }
      $mediatext .= nl2br($row['plot']) . '&nbsp;</td>';
      $mediatext .= "<td>{$row['status']}&nbsp;</td>";
      $mediatext .= "<td>\n";
    } else {
      $mediatext .= "<td width=\"175\">\n";
    }
    $mediatext .= $medialinktext;
    $mediatext .= "&nbsp;</td>\n";
    if ($whatsnew) {
      $mediatext .= '<td>' . displayDate($row['changedatef']) . ($currentuser ? " ({$row['changedby']})" : '') . "</td></tr>\n";
    }
    //ereg if no thumbs
  }
  if (!$thumbcount) {
    $mediaheader = str_replace('<td>' . uiTextSnippet('thumb') . '</td>', '', $mediaheader);
    $mediatext = str_replace('<td>&nbsp;</td><td>', '<td>', $mediatext);
  }
  tng_free_result($mediaresult);
  $media = $mediatext ? $mediaheader . $mediatext . $footer . "</div></div>\n<br>\n" : '';

  return $media;
}
