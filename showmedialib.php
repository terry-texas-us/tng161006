<?php

function displaySize($file_size) {
  if ($file_size >= 1073741824) {
    $file_size = round($file_size / 1073741824 * 100) / 100 . 'g';
  } elseif ($file_size >= 1048576) {
    $file_size = round($file_size / 1048576 * 100) / 100 . 'm';
  } elseif ($file_size >= 1024) {
    $file_size = round($file_size / 1024 * 100) / 100 . 'k';
  } else {
    $file_size = $file_size . ' bytes';
  }

  return $file_size;
}

function output_iptc_data($info) {
  global $session_charset;

  $outputtext = '';
  if (is_array($info)) {
    $iptc = iptcparse($info['APP13']);
    if (is_array($iptc)) {
      $ucharset = strtoupper($session_charset);
      $enc = isset($iptc['1#090']) && $iptc['1#090'][0] == "\x1B%G" ? 'UTF-8' : 'ISO-8859-1';
      foreach (array_keys($iptc) as $key) {
        $count = count($iptc[$key]);
        for ($i = 0; $i < $count; $i++) {
          $tempkey = substr($key, 0);
          $newkey = substr($key, 2);
          if (!$i) {
            $iptc[$key][0] = str_replace("\000", '', $iptc[$key][0]);
          }
          if ($newkey != '000') {
            if ($tempkey == '1#090') {
              continue;
            }
            $newkey = 'iptc' . $newkey;
            $keytext = uiTextSnippet($newkey) ? uiTextSnippet($newkey) : $key;
            $fact = $iptc[$key][$i];

            if ($enc == 'UTF-8' && $ucharset != 'UTF-8') {
              $fact = utf8_decode($fact);
              $str = ', decoded';
            } elseif ($enc != 'UTF-8' && $ucharset == 'UTF-8') {
              $fact = utf8_encode($fact);
              $str = ', encoded';
            } else {
              $str = ', NONE';
            }
            //echo "key=$keytext, data encoding=$enc, TNG charset=$ucharset$str<br>";

            $outputtext .= tableRow($keytext, $fact);
          }
        }
      }
    }
  }
  return $outputtext;
}

function getMediaInfo($mediatypeID, $mediaID, $personID, $albumID, $albumlinkID, $cemeteryID, $eventID) {
  global $wherestr;
  global $tnggallery;
  global $mediasearch;
  global $all;
  global $showall;
  global $ordernum;
  global $media_table;
  global $medialinks_table;
  global $albumlinks_table;

  $info = [];

  if ($albumlinkID) {
    if ($tnggallery) {
      $wherestr = ' AND thumbpath != ""';
    }
    $query = "SELECT $media_table.mediaID, albumlinkID, ordernum, path, map, description, notes, width, height, datetaken, placetaken, owner, alwayson, abspath, usecollfolder, status, plot, cemeteryID, showmap, bodytext, form, newwindow, usenl, latitude, longitude, mediatypeID FROM ($albumlinks_table, $media_table) WHERE albumID = '$albumID' AND $albumlinks_table.mediaID = $media_table.mediaID $wherestr ORDER BY ordernum, description";
    $result = tng_query($query);
    $offsets = get_media_offsets($result, $mediaID);
    $info['page'] = $offsets[0] + 1;
    tng_data_seek($result, $offsets[0]);

    $imgrow = tng_fetch_assoc($result);
    $info['mediaID'] = $imgrow['mediaID'];
    $info['ordernum'] = $imgrow['ordernum'];
    $info['mediadescription'] = $imgrow['description'];
    $info['medianotes'] = $imgrow['notes'];
  } elseif (!$personID) {
    $mediasearch = $_SESSION['tng_mediasearch'];
    $tnggallery = $_SESSION['tng_gallery'];
    if ($all) {
      $wherestr = 'WHERE 1=1';
      $showall = '';
    } else {
      $wherestr = "WHERE mediatypeID = '$mediatypeID'";
      $showall = "mediatypeID=$mediatypeID&amp;";
    }
    $join = 'LEFT JOIN';
    if ($mediasearch) {
      $wherestr .= " AND ($media_table.description LIKE \"%$mediasearch%\" OR $media_table.notes LIKE \"%$mediasearch%\" OR bodytext LIKE \"%$mediasearch%\")";
    }
    if ($tnggallery) {
      $wherestr .= ' AND thumbpath != ""';
    }
    $cemwhere = $cemeteryID ? " AND cemeteryID = '$cemeteryID'" : '';

    $query = "SELECT DISTINCT $media_table.mediaID, path, map, description, notes, width, height, datetaken, placetaken, owner, alwayson, abspath, usecollfolder, status, plot, cemeteryID, showmap, bodytext, form, newwindow, usenl, latitude, longitude, mediatypeID FROM $media_table $wherestr $cemwhere ORDER BY description";

    $result = tng_query($query);
    $offsets = get_media_offsets($result, $mediaID);
    $info['page'] = $offsets[0] + 1;
    tng_data_seek($result, $offsets[0]);

    $imgrow = tng_fetch_assoc($result);
    $info['mediadescription'] = $imgrow['description'];
    $info['medianotes'] = $imgrow['notes'];
    $info['mediaID'] = $mediaID;
    $info['ordernum'] = $ordernum;
  } else {
    $query = "SELECT medialinkID, path, map, description, notes, altdescription, altnotes, width, height, datetaken, placetaken, owner, ordernum, alwayson, abspath, $media_table.mediaID AS mediaID, usecollfolder, status, plot, cemeteryID, showmap, bodytext, form, newwindow, usenl, latitude, longitude, mediatypeID FROM ($media_table, $medialinks_table) WHERE personID = '$personID' AND mediatypeID = '$mediatypeID' AND eventID = '$eventID' AND $media_table.mediaID = $medialinks_table.mediaID ORDER by ordernum";
    $result = tng_query($query);
    $offsets = get_media_offsets($result, $mediaID);
    $info['page'] = $offsets[0] + 1;
    if ($result) {
      tng_data_seek($result, $offsets[0]);
    }

    $imgrow = tng_fetch_assoc($result);
    $info['mediaID'] = $imgrow['mediaID'];
    $info['ordernum'] = $imgrow['ordernum'];
    $info['mediadescription'] = $imgrow['altdescription'] ? $imgrow['altdescription'] : $imgrow['description'];
    $info['medianotes'] = $imgrow['altnotes'] ? $imgrow['altnotes'] : $imgrow['notes'];
  }
  $info['gotmap'] = $imgrow['map'] ? 1 : 0;
  $info['result'] = $result;
  $info['imgrow'] = $imgrow;

  return $info;
}

function findLivingPrivate($mediaID) {
  global $medialinks_table;
  global $people_table;
  global $families_table;
  global $citations_table;

  $info = [];
  //select all medialinks for this mediaID, joined with people
  //loop through looking for living
  //if any are living, don't show media
  $query = "SELECT $medialinks_table.medialinkID, $medialinks_table.personID AS personID, linktype, people.living AS living, people.private AS private, people.branch AS branch, $families_table.branch AS fbranch, $families_table.living AS fliving, $families_table.private AS fprivate FROM $medialinks_table LEFT JOIN $people_table AS people ON $medialinks_table.personID = people.personID LEFT JOIN $families_table ON $medialinks_table.personID = $families_table.familyID WHERE $medialinks_table.mediaID = \"$mediaID\"";

  $presult = tng_query($query);
  $noneliving = 1;
  $noneprivate = 1;
  $info['private'] = $info['living'] = '';

  while ($prow = tng_fetch_assoc($presult)) {
    if ($prow['private']) {
      $info['private'] = 1;
    }
    if ($prow['living']) {
      $info['living'] = 1;
    }
    if ($prow['fbranch']) {
      $prow['branch'] = $prow['fbranch'];
    }
    if ($prow['fliving'] == 1) {
      $prow['living'] = $prow['fliving'];
    }
    if ($prow['fprivate'] == 1) {
      $prow['private'] = $prow['fprivate'];
    }
    if (!$prow['living'] && !$prow['private'] && $prow['linktype'] == 'I') {
      $query = "SELECT count(*) AS ccount FROM $citations_table, $people_table WHERE $citations_table.sourceID = '{$prow['personID']}' AND $citations_table.persfamID = $people_table.personID AND (living = '1' OR private = '1')";
      $presult2 = tng_query($query);
      $prow2 = tng_fetch_assoc($presult2);
      if ($prow2['ccount']) {
        $prow['living'] = 1;
      }
      tng_free_result($presult2);
    }
    $prights = determineLivingPrivateRights($prow);
    if (!$prights['both']) {
      if ($prow['private']) {
        $noneprivate = 0;
      }
      if ($prow['living']) {
        $noneliving = 0;
      }
      break;
    }
  }
  tng_free_result($presult);

  $info['noneliving'] = $noneliving;
  $info['noneprivate'] = $noneprivate;

  return $info;
}

function getMediaNavigation($mediaID, $personID, $albumlinkID, $result, $showlinks = true) {
  global $allow_admin;
  global $allowMediaEdit;
  global $albumname;
  global $albumID;
  global $offset;
  global $page;
  global $maxsearchresults;
  global $linktype;
  global $showall;
  global $tnggallery;
  global $totalpages;
  global $all;

  $mediaperpage = 1;
  $max_showmedia_pages = 5;
  $pagenum = ceil($page / $maxsearchresults);
  $pagenam = '';

  if ($showlinks) {
    if ($allow_admin && $allowMediaEdit) {
      $pagenav .= "<a href=\"mediaEdit.php?mediaID=$mediaID&amp;cw=1\" target='_blank'>&raquo; " . uiTextSnippet('editmedia') . '</a> &nbsp;&nbsp;&nbsp;';
    }
    if ($albumlinkID) {
      $offset = floor($page / $maxsearchresults) * $maxsearchresults;
      $pagenav .= "<a href=\"albumsShowAlbum.php?albumID=$albumID&amp;offset=$offset&amp;tngpage=$pagenum&amp;tnggallery=$tnggallery\">&raquo; $albumname</a>  &nbsp;&nbsp;&nbsp;";
    } elseif (!$personID) {
      $offset = floor($page / $maxsearchresults) * $maxsearchresults;
      $pagenav .= '<a href="mediaShow.php?' . $showall . "offset=$offset&amp;tngpage=$pagenum&amp;tnggallery=$tnggallery\">&raquo; " . uiTextSnippet('showall') . '</a>  &nbsp;&nbsp;&nbsp;';
    } else {
      if ($linktype == 'F') {
        $pagenav .= "<a href=\"familiesShowFamily.php?familyID=$personID\">&raquo; " . uiTextSnippet('groupsheet') . '</a>  &nbsp;&nbsp;&nbsp;';
      } elseif ($linktype == 'S') {
        $pagenav .= "<a href=\"sourcesShowSource.php?sourceID=$personID\">&raquo; " . uiTextSnippet('source') . '</a>  &nbsp;&nbsp;&nbsp;';
      } elseif ($linktype == 'R') {
        $pagenav .= "<a href=\"repositoriesShowItem.php?repoID=$personID\">&raquo; " . uiTextSnippet('repository') . '</a>  &nbsp;&nbsp;&nbsp;';
      } elseif ($linktype == 'L') {
        $pagenav .= "<a href=\"placesearch.php?psearch=$personID\">&raquo; " . uiTextSnippet('place') . ": $personID</a>  &nbsp;&nbsp;&nbsp;";
      }
    }
  }

  $total = tng_num_rows($result);

  if (!$page) {
    $page = 1;
  }
  if ($total > $mediaperpage) {
    $totalpages = ceil($total / $mediaperpage);
    if ($page > $totalpages) {
      $page = $totalpages;
    }
    $allstr = $all ? '&amp;all=1' : '';

    if ($page > 1) {
      $prevpage = $page - 1;
      $prevlink = get_media_link($result, 'showmedia.php?', $prevpage, 'jump', uiTextSnippet('prev'), '&laquo;' . uiTextSnippet('prev'), $allstr, $showlinks);
    }
    if ($page < $totalpages) {
      $nextpage = $page + 1;
      $nextlink = get_media_link($result, 'showmedia.php?', $nextpage, 'jumpnext', uiTextSnippet('next'), uiTextSnippet('next') . '&raquo;', $allstr, $showlinks);
    }
    $curpage = 0;
    $numlinks = '';
    while ($curpage++ < $totalpages) {
      if (($curpage <= $page - $max_showmedia_pages || $curpage >= $page + $max_showmedia_pages) && $max_showmedia_pages != 0) {
        if ($curpage == 1) {
          $firstlink = get_media_link($result, 'showmedia.php?', $curpage, 'jump', uiTextSnippet('firstpage'), '&laquo;1', $allstr, $showlinks) . '...';
        }
        if ($curpage == $totalpages) {
          $lastlink = '...' . get_media_link($result, 'showmedia.php?', $curpage, 'jump', uiTextSnippet('lastpage'), "$totalpages&raquo;", $allstr, $showlinks);
        }
      } else {
        if ($curpage == $page) {
          $numlinks .= " <span>$curpage</span> ";
        } else {
          $numlinks .= get_media_link($result, 'showmedia.php?', $curpage, 'jump', $curpage, $curpage, $allstr, $showlinks);
        }
      }
    }
    $pagenav .= "<span>$prevlink $firstlink $numlinks $lastlink $nextlink</span>";
  }

  return $pagenav;
}

function getAlbumLinkText($mediaID) {
  global $albums_table;
  global $albumlinks_table;

  $albumlinktext = '';
  //get all albumlink records for this mediaID, joined with album tables
  $query = "SELECT $albums_table.albumID, albumname FROM ($albumlinks_table, $albums_table) WHERE mediaID = \"$mediaID\" AND $albumlinks_table.albumID = $albums_table.albumID";
  $result = tng_query($query);
  while ($row = tng_fetch_assoc($result)) {
    if ($albumlinktext) {
      $albumlinktext .= ', ';
    }
    $albumlinktext .= "<a href=\"albumsShowAlbum.php?albumID={$row['albumID']}\">" . $row['albumname'] . '</a>';
  }
  tng_free_result($result);

  return $albumlinktext;
}

function getMediaLinkText($mediaID, $ioffset) {
  global $medialinks_table;
  global $people_table;
  global $families_table;
  global $repositories_table;
  global $events_table;
  global $eventtypes_table;
  global $wherestr2;
  global $maxsearchresults;

  if ($ioffset) {
    $ioffsetstr = "$ioffset, ";
    $newioffset = $ioffset + 1;
  } else {
    $ioffsetstr = '';
    $newioffset = '';
  }
  $query = "SELECT $medialinks_table.medialinkID, $medialinks_table.personID AS personID, people.living AS living, people.private AS private, people.branch AS branch, $medialinks_table.eventID, $families_table.branch AS fbranch, $families_table.living AS fliving, $families_table.private AS fprivate, people.lastname AS lastname, people.lnprefix AS lnprefix, people.firstname AS firstname, people.prefix AS prefix, people.suffix AS suffix, people.nameorder, altdescription, altnotes, familyID, people.personID AS personID2, wifepeople.personID AS wpersonID, wifepeople.firstname AS wfirstname, wifepeople.lnprefix AS wlnprefix, wifepeople.lastname AS wlastname, wifepeople.prefix AS wprefix, wifepeople.suffix AS wsuffix, husbpeople.personID AS hpersonID, husbpeople.firstname AS hfirstname, husbpeople.lnprefix AS hlnprefix, husbpeople.lastname AS hlastname, husbpeople.prefix AS hprefix, husbpeople.suffix AS hsuffix, sources.title, sources.sourceID, $repositories_table.repoID, reponame FROM $medialinks_table LEFT JOIN $people_table AS people ON $medialinks_table.personID = people.personID LEFT JOIN $families_table ON $medialinks_table.personID = $families_table.familyID LEFT JOIN $people_table AS husbpeople ON $families_table.husband = husbpeople.personID LEFT JOIN $people_table AS wifepeople ON $families_table.wife = wifepeople.personID LEFT JOIN sources ON $medialinks_table.personID = sources.sourceID LEFT JOIN $repositories_table ON ($medialinks_table.personID = $repositories_table.repoID) WHERE mediaID = \"$mediaID\"$wherestr2 ORDER BY people.lastname, people.lnprefix, people.firstname, hlastname, hlnprefix, hfirstname  LIMIT $ioffsetstr" . ($maxsearchresults + 1);
  $presult = tng_query($query);
  $numrows = tng_num_rows($presult);
  $medialinktext = '';
  $count = 0;
  while ($count < $maxsearchresults && $prow = tng_fetch_assoc($presult)) {
    if ($prow['fbranch'] != null) {
      $prow['branch'] = $prow['fbranch'];
    }
    if ($prow['fliving'] != null) {
      $prow['living'] = $prow['fliving'];
    }
    if ($prow['fprivate'] != null) {
      $prow['private'] = $prow['fprivate'];
    }
    if ($medialinktext) {
      $medialinktext .= '; ';
    }

    $prights = determineLivingPrivateRights($prow);
    $prow['allow_living'] = $prights['living'];
    $prow['allow_private'] = $prights['private'];

    if ($prow['personID2'] != null) {
      $medialinktext .= "<a href=\"peopleShowPerson.php?personID={$prow['personID2']}\">";
      $medialinktext .= getName($prow) . '</a>';
    } elseif ($prow['sourceID'] != null) {
      $sourcetext = $prow['title'] ? $prow['title'] : uiTextSnippet('source') . ': ' . $prow['sourceID'];
      $medialinktext .= "<a href=\"sourcesShowSource.php?sourceID={$prow['sourceID']}\">" . $sourcetext . '</a>';
    } elseif ($prow['repoID'] != null) {
      $repotext = $prow['reponame'] ? $prow['reponame'] : uiTextSnippet('repository') . ': ' . $prow['repoID'];
      $medialinktext .= "<a href=\"repositoriesShowItem.php?repoID={$prow['repoID']}\">" . $repotext . '</a>';
    } elseif ($prow['familyID'] != null) {
      $familyname = trim($prow['hlnprefix'] . ' ' . $prow['hlastname']) . '/' . trim($prow['wlnprefix'] . ' ' . $prow['wlastname']) . " ({$prow['familyID']})";
      $medialinktext .= "<a href=\"familiesShowFamily.php?familyID={$prow['familyID']}\">" . uiTextSnippet('family') . ": $familyname</a>";
    } else {
      $medialinktext .= '<a href="placesearch.php?psearch=' . urlencode($prow['personID']) . '">' . $prow['personID'] . '</a>';
    }
    if ($prow['eventID']) {
      $query = "SELECT display FROM $events_table, $eventtypes_table WHERE eventID = \"{$prow['eventID']}\" AND $events_table.eventtypeID = $eventtypes_table.eventtypeID";
      $eresult = tng_query($query);;
      $erow = tng_fetch_assoc($eresult);
      $event = $erow['display'] && is_numeric($prow['eventID']) ? getEventDisplay($erow['display']) : (uiTextSnippet($prow['eventID']) ? uiTextSnippet($prow['eventID']) : $prow['eventID']);
      tng_free_result($eresult);
      if ($event) {
        $medialinktext .= " ($event)";
      }
    }
    $count++;
  }
  tng_free_result($presult);
  if ($numrows > $maxsearchresults) {
    $medialinktext .= "\n['<a href=\"showmedia.php?mediaID=$mediaID&amp;ioffset=" . ($newioffset + $maxsearchresults) . '">' . uiTextSnippet('morelinks') . "</a>']";
  }

  return $medialinktext;
}

function showMediaSource($imgrow, $ss = false) {
  global $usefolder;
  global $size;
  global $imagetypes;
  global $tngconfig;
  global $videotypes;
  global $recordingtypes;
  global $description;
  global $medialinkID;
  global $albumlinkID;
  global $mediatypes_like;

  if ($imgrow['form']) {
    $imgrow['form'] = strtoupper($imgrow['form']);
  } else {
    preg_match('/\.(.+)$/', $imgrow['path'], $matches);
    $imgrow['form'] = strtoupper($matches[1]);
  }
  if ($ss) {
    echo "<div class='slidepane'>\n";
  }
  if (!$ss && $imgrow['map']) {
    echo "<map name=\"tngmap_{$imgrow['mediaID']}\" id=\"tngmap_{$imgrow['mediaID']}\">{$imgrow['map']}</map>\n";
    $mapstr = " usemap=\"#tngmap_{$imgrow['mediaID']}\"";
  } else {
    $mapstr = '';
  }
  if ($imgrow['abspath'] || substr($imgrow['path'], 0, 4) == 'http' || substr($imgrow['path'], 0, 1) == '/') {
    $mediasrc = $imgrow['path'];
  } else {
    $mediasrc = "$usefolder/" . str_replace('%2F', '/', rawurlencode($imgrow['path']));
  }

  $targettext = $imgrow['newwindow'] ? " target='_blank'" : '';
  if ($imgrow['path']) {
    if ($imgrow['abspath']) {
      if ($imgrow['newwindow']) {
        echo "<form><input type='button' value=\"" . uiTextSnippet('viewitem') . "...\" onClick=\"window.open('$mediasrc');\"/></form>\n";
      } else {
        echo "<form><input type='button' value=\"" . uiTextSnippet('viewitem') . "...\" onClick=\"window.location.href='$mediasrc';\"/></form>\n";
      }
    } else {
      if (!$imgrow['form']) {
        preg_match('/\.(.+)$/', $imgrow['path'], $matches);
        $imgrow['form'] = $matches[1];
      }
      if (in_array($imgrow['form'], $imagetypes)) {
        $width = $size[0];
        $height = $size[1];
        if ($ss) {
          $maxw = 860;
          $maxh = 550;
          $medialinkstr = $medialinkID ? "&medialinkID=$medialinkID" : '';
          $albumlinkstr = $albumlinkID ? "&albumlinkID=$albumlinkID" : '';
        }
        if ($width && $height) {
          if ($ss) {
            if ($width > $height) {
              $height = floor($height * $maxw / $width);
              $width = $maxw;
            } else {
              $width = floor($width * $maxh / $height);
              $height = $maxh;
            }
          } else {
            $maxw = $tngconfig['imgmaxw'];
            $maxh = $tngconfig['imgmaxh'];
          }
          if ($maxw && ($width > $maxw)) {
            $width = $maxw;
            $height = floor($width * $size[1] / $size[0]);
          }
          if ($maxh && ($height > $maxh)) {
            $height = $maxh;
            $width = floor($height * $size[0] / $size[1]);
          }
        } elseif ($ss) {
          $height = $maxh;
        }
        if ($width && $width != '0') {
          $widthstr = "width=\"$width\"";
        }
        if ($height && $height != '0') {
          $heightstr = "height=\"$height\"";
        }
        if ($ss) {    //slideshow
          $img = "<img src=\"$mediasrc\" $mapstr alt=\"$description\">";
          echo "<div id=\"slidearea\"><a href=\"showmedia.php?mediaID={$imgrow['mediaID']}$medialinkstr$albumlinkstr\" title=\"" . uiTextSnippet('moreinfo') . "\">$img</a></div>\n";
        } else {
          $imgviewer = $tngconfig['imgviewer'];
          if (!$imgviewer || in_array($imgrow['mediatypeID'], $mediatypes_like[$imgviewer])) {
            $maxvh = $tngconfig['imgvheight'];
            $calcHeight = $maxvh ? ($height > $maxvh ? $maxvh : $height) : 1;
            echo '<div id="loadingdiv2" style="position: static;">' . uiTextSnippet('loading') . '</div>';
            echo '<iframe name="iframe1" id="iframe1" src="' . "img_viewer.php?mediaID={$imgrow['mediaID']}&amp;medialinkID={$imgrow['medialinkID']}\" width=\"100%\" height=\"1\" onload=\"calcHeight($calcHeight)\" frameborder=\"0\" marginheight=\"0\" marginwidth=\"0\" scrolling=\"no\"></iframe>";
          } else {
            echo "<div class=\"titlebox mediaalign\" id=\"imgdiv\"><img src=\"$mediasrc\" id=\"theimage\" $mapstr alt=\"$description\"></div>\n";
          }
        }
      } elseif (in_array($imgrow['form'], $videotypes) || in_array($imgrow['form'], $recordingtypes)) {
        $widthstr = $imgrow['width'] ? " width=\"{$imgrow['width']}\"" : '';
        $heightstr = $imgrow['height'] ? " height=\"{$imgrow['height']}\"" : '';

        if ($imgrow['form'] == 'FLV') {
          $flvheight = $imgrow['height'] ? $imgrow['height'] : 300;
          $flvwidth = $imgrow['width'] ? $imgrow['width'] : 400;
          $preview_img = str_replace('.flv', '.jpg', $mediasrc);
          echo '<script src="flvsupport/flowplayer-3.2.8.min.js"></script>';
          echo "<a href=\"$mediasrc\"";
          echo "style=\"display:block;width:{$flvwidth}px;height:{$flvheight}px;\" id=\"videoplayer\">";
          if (file_exists(str_replace('%20', ' ', $preview_img))) {
            echo "<img src='$preview_img'style=\"display:block;width:{$flvwidth}px;height:{$flvheight}px;\" alt=\"Click here to play this video...\">";
          } elseif (file_exists('flvsupport/flvicon.png')) {
            echo "<img src='flvsupport/flvicon.png' alt='Click here to play this video...'>";
          }
          echo '</a>';
          echo "<script>flowplayer('videoplayer','flvsupport/flowplayer-3.2.9.swf');</script>";
        } else {
          echo "<embed src=\"$mediasrc\"$widthstr$heightstr>\n";
        }
      } else {
        if ($imgrow['newwindow']) {
          echo "<form><input type='button' value=\"" . uiTextSnippet('viewitem') . "...\" onClick=\"window.open('$mediasrc');\"/></form>\n";
        } else {
          echo "<form><input type='button' value=\"" . uiTextSnippet('viewitem') . "...\" onClick=\"window.location.href='$mediasrc';\"/></form>\n";
        }
      }
    }
  }
  if ($imgrow['bodytext']) {
    if ($imgrow['path']) {
      echo "<br><br>\n";
    }
    echo '<div>' . ($imgrow['usenl'] ? nl2br($imgrow['bodytext']) : $imgrow['bodytext']) . '</div>';
  }
  if ($ss) {
    echo "</div>\n";
  }
}

function tableRow($label, $fact) {
  return "<tr><td style=\"width:100px\">$label</td><td>" . insertLinks($fact) . "</td></tr>\n";
}

function showTable($imgrow, $medialinktext, $albumlinktext) {
  global $rootpath;
  global $usefolder;
  global $showextended;
  global $imagetypes;
  global $size;
  global $info;

  $tabletext = '';
  $filename = basename($imgrow['path']);
  $tabletext .= "<table class=\"table\">\n";

  if ($imgrow['owner']) {
    $tabletext .= tableRow(uiTextSnippet('photoowner'), $imgrow['owner']);
  }
  if ($imgrow['datetaken']) {
    $tabletext .= tableRow(uiTextSnippet('date'), displayDate($imgrow['datetaken']));
  }
  if ($imgrow['placetaken']) {
    $tabletext .= tableRow(uiTextSnippet('place'), $imgrow['placetaken']);
  }
  if ($imgrow['latitude']) {
    $tabletext .= tableRow(uiTextSnippet('latitude'), $imgrow['latitude']);
  }
  if ($imgrow['longitude']) {
    $tabletext .= tableRow(uiTextSnippet('longitude'), $imgrow['longitude']);
  }

  if ($showextended) {
    if ($filename) {
      $tabletext .= tableRow(uiTextSnippet('filename'), $filename);
      $filesize = $imgrow['path'] && file_exists("$rootpath$usefolder/" . $imgrow['path']) ? displaySize(filesize("$rootpath$usefolder/" . $imgrow['path'])) : '';
      $tabletext .= tableRow(uiTextSnippet('filesize'), $filesize);
    }
    if (in_array($imgrow['form'], $imagetypes)) {
      $tabletext .= tableRow(uiTextSnippet('photosize'), "$size[0] x $size[1]");
    }
    $tabletext .= output_iptc_data($info);
  }

  if ($medialinktext) {
    $tabletext .= tableRow(uiTextSnippet('indlinked'), $medialinktext);
  }
  if ($albumlinktext) {
    $tabletext .= tableRow(uiTextSnippet('albums'), $albumlinktext);
  }
  $tabletext .= "</table>\n";

  return $tabletext;
}

function doCemPlusMap($imgrow) {
  global $cemeteries_table;
  global $media_table;
  global $rootpath;
  global $headstonepath;
  global $mediatypes_assoc;
  global $mediapath;
  global $thumbmaxw;

  $query = "SELECT cemname, city, county, state, country, maplink, notes FROM $cemeteries_table WHERE cemeteryID = \"{$imgrow['cemeteryID']}\"";
  $cemresult = tng_query($query);
  $cemetery = tng_fetch_assoc($cemresult);
  tng_free_result($cemresult);

  echo "<p><span class='h4'>\n";
  $location = $cemetery['cemname'];
  if ($cemetery['city']) {
    if ($location) {
      $location .= ', ';
    }
    $location .= $cemetery['city'];
  }
  if ($cemetery['county']) {
    if ($location) {
      $location .= ', ';
    }
    $location .= $cemetery['county'];
  }
  if ($cemetery['state']) {
    if ($location) {
      $location .= ', ';
    }
    $location .= $cemetery['state'];
  }
  if ($cemetery['country']) {
    if ($location) {
      $location .= ', ';
    }
    $location .= $cemetery['country'];
  }
  echo "<a href=\"cemeteriesShowCemetery.php?cemeteryID={$imgrow['cemeteryID']}\">$location</a>";
  echo "</span></p>\n";
  if ($cemetery['notes']) {
    echo '<p><strong>' . uiTextSnippet('notes') . ':</strong> ' . nl2br($cemetery['notes']) . '</p>';
  }

  if ($imgrow['showmap']) {
    if ($cemetery['maplink'] && file_exists("$rootpath$headstonepath/" . $cemetery['maplink'])) {
      $mapsize = GetImageSize("$rootpath$headstonepath/" . $cemetery['maplink']);
      echo "<img src=\"$headstonepath/{$cemetery['maplink']}\" $mapsize[3] alt=\"{$cemetery['cemname']}\"><br><br>\n";
    }
  }

  $query = "SELECT mediaID, mediatypeID, path, thumbpath, description, notes, usecollfolder, abspath, newwindow FROM $media_table WHERE cemeteryID = \"{$imgrow['cemeteryID']}\" AND linktocem = \"1\" ORDER BY mediatypeID, description";
  $hsresult = tng_query($query);
  if (tng_num_rows($hsresult)) {
    $i = 1;
    echo "<div class=\"titlebox\">\n";
    echo '<h4><b>' . uiTextSnippet('cemphotos') . '</b></h4>';

    echo "<table class=\"table\">\n";
    echo "<tr><th width='10'></th>\n";
    echo "<th width=\"$thumbmaxw\">" . uiTextSnippet('thumb') . "</th>\n";
    echo '<th>' . uiTextSnippet('description') . "</th></tr>\n";

    while ($hs = tng_fetch_assoc($hsresult)) {
      $description = $hs['description'];
      $notes = nl2br($hs['notes']);
      $hsmediatypeID = $hs['mediatypeID'];
      $usehsfolder = $hs['usecollfolder'] ? $mediatypes_assoc[$hsmediatypeID] : $mediapath;
      $hs['allow_living'] = 1;

      $imgsrc = getSmallPhoto($hs);
      if ($hs['abspath'] || substr($hs['path'], 0, 4) == 'http' || substr($hs['path'], 0, 1) == '/') {
        $href = $hs['path'];
      } else {
        $href = 'showmedia.php?mediaID=' . $hs['mediaID'];
      }

      $targettext = $hs['newwindow'] ? " target='_blank'" : '';
      echo "<tr><td>$i</td>";
      echo "<td width=\"$thumbmaxw\">";
      echo $imgsrc ? "<a href=\"$href\"$targettext>$imgsrc</a>" : "</td>\n";
      echo '<td>';
      echo "<a href=\"$href\">$description</a><br>$notes</td></tr>\n";
      $i++;
    }
    echo "</table>\n</div>\n";
  }
  tng_free_result($hsresult);
}
