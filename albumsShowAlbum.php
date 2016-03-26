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
  global $noneliving, $noneprivate, $album2entities_table, $people_table, $families_table, $sources_table, $repositories_table, $events_table, $eventtypes_table, $wherestr2, $maxsearchresults;
  global $tngconfig;

  $links = "";

  if ($ioffset) {
    $ioffsetstr = "$ioffset, ";
    $newioffset = $ioffset + 1;
  } else {
    $ioffsetstr = "";
    $newioffset = "";
  }
  $query = "SELECT $album2entities_table.alinkID, $album2entities_table.entityID as personID, people.living as living, people.private as private, people.branch as branch, $album2entities_table.eventID,
    $families_table.branch as fbranch, $families_table.living as fliving, $families_table.private as fprivate, people.lastname as lastname, people.lnprefix as lnprefix, people.firstname as firstname, people.prefix as prefix, people.suffix as suffix, people.nameorder, $album2entities_table.gedcom,
    familyID, people.personID as personID2, wifepeople.personID as wpersonID, wifepeople.firstname as wfirstname, wifepeople.lnprefix as wlnprefix, wifepeople.lastname as wlastname,
    wifepeople.prefix as wprefix, wifepeople.suffix as wsuffix, husbpeople.personID as hpersonID, husbpeople.firstname as hfirstname, husbpeople.lnprefix as hlnprefix, husbpeople.lastname as hlastname,
    husbpeople.prefix as hprefix, husbpeople.suffix as hsuffix, $sources_table.title, $sources_table.sourceID, $repositories_table.repoID, reponame
    FROM $album2entities_table
    LEFT JOIN $people_table AS people ON $album2entities_table.entityID = people.personID AND $album2entities_table.gedcom = people.gedcom
    LEFT JOIN $families_table ON $album2entities_table.entityID = $families_table.familyID AND $album2entities_table.gedcom = $families_table.gedcom
    LEFT JOIN $people_table AS husbpeople ON $families_table.husband = husbpeople.personID AND $families_table.gedcom = husbpeople.gedcom
    LEFT JOIN $people_table AS wifepeople ON $families_table.wife = wifepeople.personID AND $families_table.gedcom = wifepeople.gedcom
    LEFT JOIN $sources_table ON $album2entities_table.entityID = $sources_table.sourceID AND $album2entities_table.gedcom = $sources_table.gedcom
    LEFT JOIN $repositories_table ON ($album2entities_table.entityID = $repositories_table.repoID AND $album2entities_table.gedcom = $repositories_table.gedcom)
    WHERE albumID = \"$albumID\"$wherestr2 ORDER BY people.lastname, people.lnprefix, people.firstname, hlastname, hlnprefix, hfirstname  LIMIT $ioffsetstr" . ($maxsearchresults + 1);
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
      $links .= ", ";
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
      $links .= "<a href=\"peopleShowPerson.php?personID={$prow['personID2']}&amp;tree={$prow['gedcom']}\">";
      $links .= getName($prow) . "</a>";
    } elseif ($prow['sourceID'] != null) {
      $sourcetext = $prow['title'] ? $prow['title'] : uiTextSnippet('source') . ": {$prow['sourceID']}";
      $links .= "<a href=\"showsource.php?sourceID={$prow['sourceID']}&amp;tree={$prow['gedcom']}\">" . $sourcetext . "</a>";
    } elseif ($prow['repoID'] != null) {
      $repotext = $prow['reponame'] ? $prow['reponame'] : uiTextSnippet('repository') . ": {$prow['repoID']}";
      $links .= "<a href=\"repositoriesShowItem.php?repoID={$prow['repoID']}&amp;tree={$prow['gedcom']}\">" . $repotext . "</a>";
    } elseif ($prow['familyID'] != null) {
      $familyname = trim("{$prow['hlnprefix']} {$prow['hlastname']}") . "/" . trim("{$prow['wlnprefix']} {$prow['wlastname']}") . " ({$prow['familyID']})";
      $links .= "<a href=\"familiesShowFamily.php?familyID={$prow['familyID']}&amp;tree={$prow['gedcom']}\">" . uiTextSnippet('family') . ": $familyname</a>";
    } else {
      $treestr = $tngconfig['places1tree'] ? "" : "&amp;tree={$prow['gedcom']}";
      $links .= "<a href=\"placesearch.php?psearch={$prow['personID']}$treestr\">" . $prow['personID'] . "</a>";
    }
    if ($prow['eventID']) {
      $query = "SELECT description from $events_table, $eventtypes_table WHERE eventID = \"{$prow['eventID']}\" AND $events_table.eventtypeID = $eventtypes_table.eventtypeID";
      $eresult = tng_query($query);;
      $erow = tng_fetch_assoc($eresult);
      $event = $erow['description'] ? $erow['description'] : $prow['eventID'];
      tng_free_result($eresult);
      $links .= " ($event)";
    }
    $count++;
  }
  tng_free_result($presult);
  if ($numrows > $maxsearchresults) {
    $links .= "\n[<a href=\"albumsShowAlbum.php?albumID=$albumID&amp;ioffset=" . ($newioffset + $maxsearchresults) . "\">" . uiTextSnippet('morelinks') . "</a>]";
  }

  return $links;
}

$albumlinktext = getAlbumLinkText($albumID);
if ($albumlinktext) {
  $altext = $albumlinktext;
  $albumlinktext = "<table class='table'>\n";
  $albumlinktext .= "<tr>\n";
  $albumlinktext .= "<td width=\"100\">" . uiTextSnippet('indlinked') . "</td>\n";
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
  $wherestr .= " AND thumbpath != \"\"";
  $gallerymsg = "<a href=\"albumsShowAlbum.php?albumID=$albumID\">&raquo; " . uiTextSnippet('regphotos') . "</a>&nbsp;";
} else {
  $gallerymsg = "<a href=\"albumsShowAlbum.php?albumID=$albumID&amp;tnggallery=1\">&raquo; " . uiTextSnippet('gallery') . "</a>&nbsp;";
}

$_SESSION['tng_gallery'] = $tnggallery;

$max_browsemedia_pages = 5;
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $page = 1;
}

$query = "SELECT albumname, description, active FROM $albums_table WHERE albumID = \"$albumID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
if (!tng_num_rows($result) || (!$row['active'] && !$allow_admin)) {
  tng_free_result($result);
  header("Location: thispagedoesnotexist.html");
  exit;
}
$albumname = $row['albumname'];
$description = $row['description'];
tng_free_result($result);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('albums') . ": " . $albumname);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
<?php
if (!$noneliving && !$noneprivate) {
    echo $publicHeaderSection->build();
    echo tng_DrawHeading("", uiTextSnippet('albums') . ": " . $albumname, $description);
    echo "<p>" . uiTextSnippet('livingphoto') . "</p>\n";
    echo $publicFooterSection->build();
  echo "</body>\n";
  echo "</html>\n";
  exit;
}
if ($tree) {
  $wherestr = " AND ($media_table.gedcom = \"$tree\" || $media_table.gedcom = \"\")";
  $wherestr2 = " AND $medialinks_table.gedcom = \"$tree\"";
} else {
  $wherestr = $wherestr2 = "";
}
$query = "SELECT DISTINCT $media_table.mediaID, albumlinkID, $media_table.description, $media_table.notes, thumbpath, alwayson, usecollfolder, mediatypeID, path, form, abspath, newwindow
    FROM ($albumlinks_table, $media_table) LEFT JOIN $medialinks_table
    ON $media_table.mediaID = $medialinks_table.mediaID
    WHERE albumID = \"$albumID\" AND $albumlinks_table.mediaID = $media_table.mediaID $wherestr
    ORDER BY $albumlinks_table.ordernum, description LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);
$numrows = tng_num_rows($result);

if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(distinct $media_table.mediaID) as mcount FROM ($albumlinks_table, $media_table) LEFT JOIN $medialinks_table
    ON $media_table.mediaID = $medialinks_table.mediaID WHERE albumID = \"$albumID\" AND $albumlinks_table.mediaID = $media_table.mediaID $wherestr";
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
    $hiddenfields[0] = array('name' => 'albumID', 'value' => $albumID);
    echo treeDropdown(array('startform' => true, 'endform' => true, 'action' => 'albumsShowAlbum', 'method' => 'get', 'name' => 'form1', 'id' => 'form1', 'hidden' => $hiddenfields));

    $toplinks = "<p>";
    $toplinks .= $totrows ? uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows &nbsp;&nbsp; " : "";
    $toplinks .= $gallerymsg;
    $toplinks .= $allow_admin && $allowEdit ? "<a href=\"albumsEdit.php?albumID=$albumID&amp;cw=1\" target='_blank'>&raquo; " . uiTextSnippet('editalbum') . "</a> " : "";

    $pagenav = buildSearchResultPagination($totrows, "albumsShowAlbum.php?albumID=$albumID&amp;tnggallery=$tnggallery&amp;offset", $maxsearchresults, $max_browsemedia_pages);
    $preheader = $pagenav . "</p>\n";

    if ($tnggallery) {
      $preheader .= "<div class=\"titlebox\">\n";
      $firstrow = 1;
      $tablewidth = "";
      $header = "";
    } else {
      $header = "<tr><td></td>\n";
      $header .= "<td width=\"$thumbmaxw\">" . uiTextSnippet('thumb') . "</td>\n";
      $header .= "<td width=\"80%\">" . uiTextSnippet('description') . "</td>\n";
      $header .= "<td>" . uiTextSnippet('indlinked') . "</td>\n";
      $header .= "</tr>\n";
      $tablewidth = " width=\"100%\"";
    }

    $header = "<table class='table' $tablewidth>\n" . $header;

    $i = $offsetplus;
    $maxplus = $maxsearchresults + 1;
    $mediatext = "";
    $firsthref = "";
    $thumbcount = 0;
    $gotImageJpeg = function_exists(imageJpeg);
    while ($row = tng_fetch_assoc($result)) {
      $mediatypeID = $row['mediatypeID'];
      $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
      $query = "SELECT $medialinks_table.mediaID, $medialinks_table.personID as personID, people.personID as personID2, people.living as living, people.private, people.branch as branch, $families_table.branch as fbranch,
        $families_table.living as fliving, familyID, husband, wife, people.lastname as lastname, people.lnprefix as lnprefix, people.firstname as firstname, people.prefix as prefix, people.suffix as suffix, nameorder,
        $medialinks_table.gedcom, $sources_table.title, $sources_table.sourceID, $repositories_table.repoID, reponame, deathdate, burialdate, linktype
        FROM $medialinks_table
        LEFT JOIN $people_table AS people ON $medialinks_table.personID = people.personID AND $medialinks_table.gedcom = people.gedcom
        LEFT JOIN $families_table ON $medialinks_table.personID = $families_table.familyID AND $medialinks_table.gedcom = $families_table.gedcom
        LEFT JOIN $sources_table ON $medialinks_table.personID = $sources_table.sourceID AND $medialinks_table.gedcom = $sources_table.gedcom
        LEFT JOIN $repositories_table ON ($medialinks_table.personID = $repositories_table.repoID AND $medialinks_table.gedcom = $repositories_table.gedcom)
        WHERE mediaID = \"{$row['mediaID']}\" $wherestr2 ORDER BY lastname, lnprefix, firstname, personID LIMIT $maxplus";
      $presult = tng_query($query);
      $numrows = tng_num_rows($presult);
      $medialinktext = "";
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
          $query = "SELECT count(personID) as ccount FROM $citations_table, $people_table
            WHERE $citations_table.sourceID = '{$prow['personID']}' AND $citations_table.persfamID = $people_table.personID AND $citations_table.gedcom = $people_table.gedcom
            AND (living = '1' OR private = '1')";
          $presult2 = tng_query($query);
          $prow2 = tng_fetch_assoc($presult2);
          if ($prow2['ccount']) {
            $prow['living'] = 1;
          }
          tng_free_result($presult2);
        }
        if ($prow['living'] == null && $prow['private'] == null && $prow['linktype'] == 'F') {
          $query = "SELECT count(familyID) as ccount FROM $citations_table, $families_table
            WHERE $citations_table.sourceID = '{$prow['personID']}' AND $citations_table.persfamID = $families_table.familyID AND $citations_table.gedcom = $families_table.gedcom
            AND living = '1'";
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
          $hstext = "";
          if ($prow['personID2'] != null) {
            $medialinktext .= "<li><a href=\"peopleShowPerson.php?personID={$prow['personID2']}&amp;tree={$prow['gedcom']}\">";
            $medialinktext .= getName($prow);
            if ($mediatypeID == "headstones") {
              $deathdate = $prow['deathdate'] ? $prow['deathdate'] : $prow['burialdate'];
              if ($prow['deathdate']) {
                $abbrev = uiTextSnippet('deathabbr');
              } elseif ($prow['burialdate']) {
                $abbrev = uiTextSnippet('burialabbr');
              }
              $hstext = $deathdate ? " ($abbrev " . displayDate($deathdate) . ")" : "";
            }
          } elseif ($prow['sourceID'] != null) {
            $sourcetext = $prow['title'] ? $prow['title'] : uiTextSnippet('source') . ": " . $prow['sourceID'];
            $medialinktext .= "<li><a href=\"showsource.php?sourceID={$prow['sourceID']}&amp;tree={$prow['gedcom']}\">$sourcetext";
          } elseif ($prow['repoID'] != null) {
            $repotext = $prow['reponame'] ? $prow['reponame'] : uiTextSnippet('repository') . ": " . $prow['repoID'];
            $medialinktext .= "<li><a href=\"repositoriesShowItem.php?repoID={$prow['repoID']}&amp;tree={$prow['gedcom']}\">$repotext";
          } elseif ($prow['familyID'] != null) {
            $medialinktext .= "<li><a href=\"familiesShowFamily.php?familyID={$prow['personID']}&amp;tree={$prow['gedcom']}\">" . uiTextSnippet('family') . ": " . getFamilyName($prow);
          } else {
            $treestr = $tngconfig['places1tree'] ? "" : "&amp;tree={$prow['gedcom']}";
            $medialinktext .= "<li><a href=\"placesearch.php?psearch={$prow['personID']}$treestr\">" . $prow['personID'];
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
        $href = "";
      }
      if ($href && !$firsthref) {
        $firsthref = $href;
      }
      if ($row['allow_living'] || !$nonamesloc) {
        $description = $showAlbumInfo ? "<a href=\"$href\">{$row['description']}</a>" : $row['description'];
        $notes = nl2br(truncateIt(getXrefNotes($row['notes']), $tngconfig['maxnoteprev']));
        if (!$showAlbumInfo) {
          $notes .= "<br>(" . uiTextSnippet('livingphoto') . ")";
        }
      } else {
        $description = uiTextSnippet('living');
        $notes = uiTextSnippet('livingphoto');
      }
      if ($row['status']) {
        $notes = uiTextSnippet('status') . ": " . $row['status'] . $notes;
      }

      if (!$row['allow_living']) {
        $row['description'] = uiTextSnippet('livingphoto');
      }

      if ($tnggallery) {
        if ($imgsrc) {
          if ($firstrow) {
            $firstrow = 0;
            $mediatext .= "<tr>";
          } else {
            if (($i - 1) % $tngconfig['thumbcols'] == 0) {
              $mediatext .= "</tr>\n<tr>";
            }
          }
          $mediatext .= "<td style=\"padding:10px\">";
          $mediatext .= $href ? "<a href=\"$href\">$imgsrc</a></td>\n" : "$imgsrc</td>\n";
          $i++;
        }
      } else {
        $mediatext .= "<tr><td><span>$i</span></td>";
        if ($imgsrc) {
          $mediatext .= "<td>";
          $mediatext .= "<div class=\"media-img\"><div class=\"media-prev\" id=\"prev{$row['mediaID']}\" style=\"display:none\"></div></div>\n";
          if ($href) {
            $mediatext .= "<a href=\"$href\"";
            if ($gotImageJpeg && isPhoto($row) && checkMediaFileSize("$rootpath$usefolder/{$row['path']}")) {
              $mediatext .= " class=\"media-preview\" id=\"img-{$row['mediaID']}-0-" . urlencode("$usefolder/{$row['path']}") . "\"";
            }
            $mediatext .= ">$imgsrc</a>";
          } else {
            $mediatext .= $imgsrc;
          }
          $mediatext .= "</td><td>";
          $thumbcount++;
        } else {
          $mediatext .= "<td>&nbsp;</td><td>";
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
        $header = str_replace("<td>" . uiTextSnippet('thumb') . "</td>", "", $header);
        $mediatext = str_replace("<td>&nbsp;</td><td>", "<td>", $mediatext);
      }
    }

    if ($firsthref) {
      $toplinks .= " &nbsp;&nbsp; <a href=\"$firsthref&amp;ss=1\">&raquo; " . uiTextSnippet('slidestart') . "</a>";
    }
    $toplinks .= "</p>";
    //print out the whole shootin' match right here, eh
    echo $toplinks . $preheader . $header . $mediatext;
    echo "</table>\n";

    if ($tnggallery) {
      echo "</div>\n";
    }

    echo "<br>\n";
    if ($pagenav) {
      echo $pagenav;
      echo "<br>";
    }
    echo $albumlinktext;

    echo $publicFooterSection->build();
    ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
