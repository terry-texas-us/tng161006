<?php
require 'begin.php';
require 'genlib.php';
require 'getlang.php';

require 'checklogin.php';
require 'functions.php';
require 'log.php';

require_once 'albums.php';

$flags['imgprev'] = true;

$noneliving = $noneprivate = 1;

function getAlbumLinkText($albumID) {
  global $noneliving;
  global $noneprivate;
  global $maxsearchresults;

  $links = '';

  if ($ioffset) {
    $ioffsetstr = "$ioffset, ";
    $newioffset = $ioffset + 1;
  } else {
    $ioffsetstr = '';
    $newioffset = '';
  }
  $query = "SELECT albumplinks.alinkID, albumplinks.entityID AS personID, people.living AS living, people.private AS private, people.branch AS branch, albumplinks.eventID, families.branch AS fbranch, families.living AS fliving, families.private AS fprivate, people.lastname AS lastname, people.lnprefix AS lnprefix, people.firstname AS firstname, people.prefix AS prefix, people.suffix AS suffix, people.nameorder, familyID, people.personID AS personID2, wifepeople.personID AS wpersonID, wifepeople.firstname AS wfirstname, wifepeople.lnprefix AS wlnprefix, wifepeople.lastname AS wlastname, wifepeople.prefix AS wprefix, wifepeople.suffix AS wsuffix, husbpeople.personID AS hpersonID, husbpeople.firstname AS hfirstname, husbpeople.lnprefix AS hlnprefix, husbpeople.lastname AS hlastname, husbpeople.prefix AS hprefix, husbpeople.suffix AS hsuffix, sources.title, sources.sourceID, repositories.repoID, reponame FROM albumplinks LEFT JOIN people AS people ON albumplinks.entityID = people.personID LEFT JOIN families ON albumplinks.entityID = families.familyID LEFT JOIN people AS husbpeople ON families.husband = husbpeople.personID LEFT JOIN people AS wifepeople ON families.wife = wifepeople.personID LEFT JOIN sources ON albumplinks.entityID = sources.sourceID LEFT JOIN repositories ON (albumplinks.entityID = repositories.repoID) WHERE albumID = '$albumID' ORDER BY people.lastname, people.lnprefix, people.firstname, hlastname, hlnprefix, hfirstname  LIMIT $ioffsetstr" . ($maxsearchresults + 1);
  $presult = tng_query($query);
  $numrows = tng_num_rows($presult);

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
    if ($links) {
      $links .= ', ';
    }

    $prights = determineLivingPrivateRights($prow);
    $prow['allow_living'] = $prights['living'];
    $prow['allow_private'] = $prights['private'];

    if (!$prights['both']) {
      if ($prow['private']) {
        $noneprivate = 0;
      }
      if ($prow['living']) {
        $noneliving = 0;
      }
    }

    if ($prow['personID2'] != null) {
      $links .= "<a href=\"peopleShowPerson.php?personID={$prow['personID2']}\">";
      $links .= getName($prow) . '</a>';
    } elseif ($prow['sourceID'] != null) {
      $sourcetext = $prow['title'] ? $prow['title'] : uiTextSnippet('source') . ": {$prow['sourceID']}";
      $links .= "<a href=\"sourcesShowSource.php?sourceID={$prow['sourceID']}\">" . $sourcetext . '</a>';
    } elseif ($prow['repoID'] != null) {
      $repotext = $prow['reponame'] ? $prow['reponame'] : uiTextSnippet('repository') . ": {$prow['repoID']}";
      $links .= "<a href=\"repositoriesShowItem.php?repoID={$prow['repoID']}\">" . $repotext . '</a>';
    } elseif ($prow['familyID'] != null) {
      $familyname = trim("{$prow['hlnprefix']} {$prow['hlastname']}") . '/' . trim("{$prow['wlnprefix']} {$prow['wlastname']}") . " ({$prow['familyID']})";
      $links .= "<a href=\"familiesShowFamily.php?familyID={$prow['familyID']}\">" . uiTextSnippet('family') . ": $familyname</a>";
    } else {
      $links .= "<a href=\"placesearch.php?psearch={$prow['personID']}\">" . $prow['personID'] . '</a>';
    }
    if ($prow['eventID']) {
      $query = "SELECT description FROM events, eventtypes WHERE eventID = \"{$prow['eventID']}\" AND events.eventtypeID = eventtypes.eventtypeID";
      $eresult = tng_query($query);
      $erow = tng_fetch_assoc($eresult);
      $event = $erow['description'] ? $erow['description'] : $prow['eventID'];
      tng_free_result($eresult);
      $links .= " ($event)";
    }
    $count++;
  }
  tng_free_result($presult);
  if ($numrows > $maxsearchresults) {
    $links .= "\n[<a href=\"albumsShowAlbum.php?albumID=$albumID&amp;ioffset=" . ($newioffset + $maxsearchresults) . '">' . uiTextSnippet('morelinks') . '</a>]';
  }

  return $links;
}

$albumlinktext = getAlbumLinkText($albumID);
if ($albumlinktext) {
  $altext = $albumlinktext;
  $albumlinktext = "<table class='table'>\n";
  $albumlinktext .= "<tr>\n";
  $albumlinktext .= '<td width="100">' . uiTextSnippet('indlinked') . "</td>\n";
  $albumlinktext .= "<td width=\"90%\">$altext</td>\n";
  $albumlinktext .= "</tr>\n";
  $albumlinktext .= "</table>\n<br>";
}

if (!$thumbmaxw) {
  $thumbmaxw = 80;
}

if ($tnggallery) {
  if (!$tngconfig['thumbcols']) {
    $tngconfig['thumbcols'] = 10;
  }
  $maxsearchresults *= 2;
  $gallerymsg = "<a href=\"albumsShowAlbum.php?albumID=$albumID\">&raquo; " . uiTextSnippet('regphotos') . '</a>&nbsp;';
} else {
  $gallerymsg = "<a href=\"albumsShowAlbum.php?albumID=$albumID&amp;tnggallery=1\">&raquo; " . uiTextSnippet('gallery') . '</a>&nbsp;';
}

$_SESSION['tng_gallery'] = $tnggallery;

$max_browsemedia_pages = 5;
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = '';
  $page = 1;
}

$query = "SELECT albumname, description, active FROM albums WHERE albumID = \"$albumID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
if (!tng_num_rows($result) || (!$row['active'] && !$allow_admin)) {
  tng_free_result($result);
  header('Location: thispagedoesnotexist.html');
  exit;
}
$albumname = $row['albumname'];
$description = $row['description'];
tng_free_result($result);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('albums') . ': ' . $albumname);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $sessionCharset); ?>
<body id='public'>
<?php
if (!$noneliving && !$noneprivate) {
    echo $publicHeaderSection->build();
    echo tng_DrawHeading('', uiTextSnippet('albums') . ': ' . $albumname, $description);
    echo '<p>' . uiTextSnippet('livingphoto') . "</p>\n";
    echo $publicFooterSection->build();
  echo "</body>\n";
  echo "</html>\n";
  exit;
}
$query = "SELECT DISTINCT media.mediaID, albumlinkID, media.description, media.notes, thumbpath, alwayson, usecollfolder, mediatypeID, path, form, abspath, newwindow FROM (albumlinks, media) LEFT JOIN medialinks ON media.mediaID = medialinks.mediaID WHERE albumID = '$albumID' AND albumlinks.mediaID = media.mediaID ORDER BY albumlinks.ordernum, description LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);
$numrows = tng_num_rows($result);

if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(distinct media.mediaID) AS mcount FROM (albumlinks, media) LEFT JOIN medialinks
    ON media.mediaID = medialinks.mediaID WHERE albumID = \"$albumID\" AND albumlinks.mediaID = media.mediaID";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  tng_free_result($result2);
  $totrows = $row['mcount'];
} else {
  $totrows = $numrows;
}
$numrowsplus = $numrows + $offset;

$logstring = "<a href=\"albumsShowAlbum.php?albumID=$albumID\">$albumname</a>";
writelog($logstring);
preparebookmark($logstring);
?>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();

    $imgsrc = getAlbumPhoto($albumID, $albumname);
    if (!$imgsrc) {
    ?>
      <h2><img class='icon-md' src='svg/album.svg'><?php echo $albumname; ?><span><?php echo $description; ?></span></h2>
      <br clear='left'>
    <?php
    } else {
      echo tng_DrawHeading($imgsrc, $albumname, $description);
    }
    $hiddenfields[0] = ['name' => 'albumID', 'value' => $albumID];

    $toplinks = '<p>';
    $toplinks .= $totrows ? uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows &nbsp;&nbsp; " : '';
    $toplinks .= $gallerymsg;
    $toplinks .= $allow_admin && $allowEdit ? "<a href=\"albumsEdit.php?albumID=$albumID&amp;cw=1\" target='_blank'>&raquo; " . uiTextSnippet('editalbum') . '</a> ' : '';

    $pagenav = buildSearchResultPagination($totrows, "albumsShowAlbum.php?albumID=$albumID&amp;tnggallery=$tnggallery&amp;offset", $maxsearchresults, $max_browsemedia_pages);
    $preheader = $pagenav . "</p>\n";

    if ($tnggallery) {
      $preheader .= "<div class='card'>\n";
      $firstrow = 1;
      $tablewidth = '';
      $header = '';
    } else {
      $header = "<tr><td></td>\n";
      $header .= "<td width=\"$thumbmaxw\">" . uiTextSnippet('thumb') . "</td>\n";
      $header .= '<td width="80%">' . uiTextSnippet('description') . "</td>\n";
      $header .= '<td>' . uiTextSnippet('indlinked') . "</td>\n";
      $header .= "</tr>\n";
      $tablewidth = ' width="100%"';
    }

    $header = "<table class='table' $tablewidth>\n" . $header;

    $i = $offsetplus;
    $maxplus = $maxsearchresults + 1;
    $mediatext = '';
    $firsthref = '';
    $thumbcount = 0;
    $gotImageJpeg = function_exists('imageJpeg');
    while ($row = tng_fetch_assoc($result)) {
      $mediatypeID = $row['mediatypeID'];
      $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
      $query = "SELECT medialinks.mediaID, medialinks.personID AS personID, people.personID AS personID2, people.living AS living, people.private, people.branch AS branch, families.branch AS fbranch, families.living AS fliving, familyID, husband, wife, people.lastname AS lastname, people.lnprefix AS lnprefix, people.firstname AS firstname, people.prefix AS prefix, people.suffix AS suffix, nameorder, sources.title, sources.sourceID, repositories.repoID, reponame, deathdate, burialdate, linktype FROM medialinks LEFT JOIN people AS people ON medialinks.personID = people.personID LEFT JOIN families ON medialinks.personID = families.familyID LEFT JOIN sources ON medialinks.personID = sources.sourceID LEFT JOIN repositories ON (medialinks.personID = repositories.repoID) WHERE mediaID = '{$row['mediaID']}' ORDER BY lastname, lnprefix, firstname, personID LIMIT $maxplus";
      $presult = tng_query($query);
      $numrows = tng_num_rows($presult);
      $medialinktext = '';
      $foundliving = 0;
      $foundprivate = 0;
      $count = 0;
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
        if ($prow['living'] == null && $prow['private'] == null && $prow['linktype'] == 'F') {
          $query = "SELECT count(familyID) AS ccount FROM citations, families
            WHERE citations.sourceID = '{$prow['personID']}' AND citations.persfamID = families.familyID AND living = '1'";
          $presult2 = tng_query($query);
          $prow2 = tng_fetch_assoc($presult2);
          if ($prow2['ccount']) {
            $prow['living'] = 1;
          }
          tng_free_result($presult2);
        }

        $prights = determineLivingPrivateRights($prow);
        $prow['allow_living'] = $prights['living'];
        $prow['allow_private'] = $prights['private'];

        if (!$prights['living']) {
          $foundliving = 1;
        }
        if (!$prights['private']) {
          $foundprivate = 1;
        }

        if (!$tnggallery) {
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
          } elseif ($prow['sourceID'] != null) {
            $sourcetext = $prow['title'] ? $prow['title'] : uiTextSnippet('source') . ': ' . $prow['sourceID'];
            $medialinktext .= "<li><a href=\"sourcesShowSource.php?sourceID={$prow['sourceID']}\">$sourcetext";
          } elseif ($prow['repoID'] != null) {
            $repotext = $prow['reponame'] ? $prow['reponame'] : uiTextSnippet('repository') . ': ' . $prow['repoID'];
            $medialinktext .= "<li><a href=\"repositoriesShowItem.php?repoID={$prow['repoID']}\">$repotext";
          } elseif ($prow['familyID'] != null) {
            $medialinktext .= "<li><a href=\"familiesShowFamily.php?familyID={$prow['personID']}\">" . uiTextSnippet('family') . ': ' . getFamilyName($prow);
          } else {
            $medialinktext .= "<li><a href=\"placesearch.php?psearch={$prow['personID']}\">" . $prow['personID'];
          }
          $medialinktext .= "</a>$hstext\n</li>\n";
        }
        $count++;
      }
      tng_free_result($presult);
      if ($medialinktext) {
        $medialinktext = "<ul>$medialinktext</ul>\n";
      }
      if ($numrows == $maxplus) {
        $medialinktext .= "\n['<a href=\"showmedia.php?mediaID={$row['mediaID']}&amp;albumID=$albumID&amp;ioffset=$maxsearchresults\">" . uiTextSnippet('morelinks') . "</a>']";
      }
      $uselink = getMediaHREF($row, 2);
      if (!$noneliving && $row['alwayson']) {
        $noneliving = 1;
      }
      $showAlbumInfo = $row['allow_living'] = $row['alwayson'] || (!$foundprivate && !$foundliving);
      $nonamesloc = $foundprivate ? $tngconfig['nnpriv'] : $nonames;

      $imgsrc = getSmallPhoto($row);
      if ($showAlbumInfo) {
        $href = $uselink;
      } else {
        $href = '';
      }
      if ($href && !$firsthref) {
        $firsthref = $href;
      }
      if ($row['allow_living'] || !$nonamesloc) {
        $description = $showAlbumInfo ? "<a href=\"$href\">{$row['description']}</a>" : $row['description'];
        $notes = nl2br(truncateIt(getXrefNotes($row['notes']), $tngconfig['maxnoteprev']));
        if (!$showAlbumInfo) {
          $notes .= '<br>(' . uiTextSnippet('livingphoto') . ')';
        }
      } else {
        $description = uiTextSnippet('living');
        $notes = uiTextSnippet('livingphoto');
      }
      if ($row['status']) {
        $notes = uiTextSnippet('status') . ': ' . $row['status'] . $notes;
      }

      if (!$row['allow_living']) {
        $row['description'] = uiTextSnippet('livingphoto');
      }

      if ($tnggallery) {
        if ($imgsrc) {
          if ($firstrow) {
            $firstrow = 0;
            $mediatext .= '<tr>';
          } else {
            if (($i - 1) % $tngconfig['thumbcols'] == 0) {
              $mediatext .= "</tr>\n<tr>";
            }
          }
          $mediatext .= '<td style = "padding:10px">';
          $mediatext .= $href ? "<a href=\"$href\">$imgsrc</a></td>\n" : "$imgsrc</td>\n";
          $i++;
        }
      } else {
        $mediatext .= "<tr><td><span>$i</span></td>";
        if ($imgsrc) {
          $mediatext .= '<td>';
          $mediatext .= "<div class=\"media-img\"><div class=\"media-prev\" id=\"prev{$row['mediaID']}\" style='display: none'></div></div>\n";
          if ($href) {
            $mediatext .= "<a href=\"$href\"";
            if ($gotImageJpeg && isPhoto($row) && checkMediaFileSize("$rootpath$usefolder/{$row['path']}")) {
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

        $mediatext .= "<span>$description<br>$notes&nbsp;</span></td>";
        $mediatext .= "<td>\n";
        $mediatext .= $medialinktext;
        $mediatext .= "&nbsp;</td></tr>\n";
        $i++;
      }
    }
    tng_free_result($result);
    if ($tnggallery) {
      if (!$firstrow) {
        $mediatext .= "</tr>\n";
      }
    } else {
      if (!$thumbcount) {
        $header = str_replace('<td>' . uiTextSnippet('thumb') . '</td>', '', $header);
        $mediatext = str_replace('<td>&nbsp;</td><td>', '<td>', $mediatext);
      }
    }

    if ($firsthref) {
      $toplinks .= " &nbsp;&nbsp; <a href=\"$firsthref&amp;ss=1\">&raquo; " . uiTextSnippet('slidestart') . '</a>';
    }
    $toplinks .= '</p>';
    //print out the whole shootin' match right here, eh
    echo $toplinks . $preheader . $header . $mediatext;
    echo "</table>\n";

    if ($tnggallery) {
      echo "</div>\n";
    }

    echo "<br>\n";
    if ($pagenav) {
      echo $pagenav;
      echo '<br>';
    }
    echo $albumlinktext;

    echo $publicFooterSection->build();
    ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
