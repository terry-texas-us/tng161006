<?php
include("tng_begin.php");

include("functions.php");

if (isset($mediatypeID)) {
  $mediatypeID = preg_replace("/[<>{};!=]/", '', $mediatypeID);
}

$flags['imgprev'] = true;

$orgmediatypeID = $mediatypeID;
initMediaTypes();

if (!in_array($mediatypeID, $mediatypes_like['photos']) && !in_array($mediatypeID, $mediatypes_like['headstones'])) {
  $tngconfig['ssdisabled'] = 1;
}

if ($orgmediatypeID) {
  $wherestr = "WHERE mediatypeID = \"$mediatypeID\"";
  $titlestr = uiTextSnippet($mediatypeID) ? uiTextSnippet($mediatypeID) : $mediatypes_display[$mediatypeID];
  if ($orgmediatypeID == "headstones") {
    $hsfields = ", $media_table.cemeteryID, cemname, city";
    $hsjoin = "LEFT JOIN $cemeteries_table ON $media_table.cemeteryID = $cemeteries_table.cemeteryID";
  } else {
    $hsfields = $hsjoin = "";
  }
} else {
  $wherestr = "WHERE 1 = 1";
  $titlestr = uiTextSnippet('allmedia');
}

if ($mediasearch) {
  $mediasearch = trim($mediasearch);
  $_SESSION['tng_mediasearch'] = $mediasearch;

  $mediasearch2 = addslashes($mediasearch);
  $mediasearch = cleanIt($mediasearch);
} else {
  $_SESSION['tng_mediasearch'] = "";
}

if ($tnggallery) {
  if (!$tngconfig['thumbcols']) {
    $tngconfig['thumbcols'] = 10;
  }
  $maxsearchresults *= 2;
  $wherestr .= " AND thumbpath != \"\"";
  $gallerymsg = "<a href=\"browsemedia.php?tree=$tree&amp;mediatypeID=$orgmediatypeID&amp;mediasearch=$mediasearch\">&raquo; " . uiTextSnippet('regphotos') . "</a>";
} else {
  $gallerymsg = "<a href=\"browsemedia.php?tnggallery=1&amp;tree=$tree&amp;mediatypeID=$orgmediatypeID&amp;mediasearch=$mediasearch\">&raquo; " . uiTextSnippet('gallery') . "</a>";
}

$_SESSION['tng_gallery'] = $tnggallery;
$_SESSION['tng_mediatree'] = $tree;

function doMediaSearch($instance, $pagenav) {
  global $mediasearch;
  global $orgmediatypeID;
  global $tree, $tnggallery;

  $str = buildFormElement("browsemedia", "get", "MediaSearch$instance");
  $str .= "<input name='mediasearch' type='text' value=\"$mediasearch\" /> \n";
  $str .= "<input type='submit' value=\"" . uiTextSnippet('search') . "\" /> \n";
  $str .= "<input type='button' value=\"" . uiTextSnippet('tng_reset') . "\" onclick=\"window.location.href='browsemedia.php?mediatypeID=$orgmediatypeID&amp;tree=$tree&amp;tnggallery=$tnggallery';\" />&nbsp;&nbsp;&nbsp;";
  $str .= "<input name='mediatypeID' type='hidden' value=\"$orgmediatypeID\" />\n";
  $str .= $pagenav;
  $str .= "<input name='tree' type='hidden' value=\"$tree\" />\n";
  $str .= "<input name='tnggallery' type='hidden' value=\"$tnggallery\" />\n";
  $str .= "</form>\n";

  return $str;
}

$max_browsemedia_pages = 5;
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $page = 1;
}

if ($tree) {
  $wherestr .= " AND ($media_table.gedcom = \"$tree\" || $media_table.gedcom = \"\")";
  $wherestr2 = " AND $medialinks_table.gedcom = \"$tree\"";
} else {
  $wherestr2 = "";
}

if ($mediasearch) {
  $wherestr .= " AND ($media_table.description LIKE \"%$mediasearch2%\" OR $media_table.notes LIKE \"%$mediasearch2%\" OR bodytext LIKE \"%$mediasearch2%\")";
}

$query = "SELECT $media_table.mediaID, $media_table.description, $media_table.notes, path, thumbpath, alwayson, usecollfolder, form, mediatypeID, status, plot, newwindow, abspath, $media_table.gedcom $hsfields FROM $media_table";
$query .= " $hsjoin $wherestr ORDER BY description LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);
$numrows = tng_num_rows($result);

if ($numrows == $maxsearchresults || $offsetplus > 1) {
  if ($tree) {
    $query = "SELECT count($media_table.mediaID) as mcount FROM $media_table";
    $query .= " $hsjoin $wherestr";
  } else {
    $query = "SELECT count($media_table.mediaID) as mcount FROM $media_table $wherestr";
  }
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  tng_free_result($result2);
  $totrows = $row['mcount'];
} else {
  $totrows = $numrows;
}

$numrowsplus = $numrows + $offset;

$treestr = $tree ? " " . uiTextSnippet('tree') . ": $tree" : "";
$treestr = trim("$mediasearch $treestr");
$treestr = $treestr ? " ($treestr)" : "";
$logstring = "<a href=\"browsemedia.php?tree=$tree&amp;offset=$offset&amp;mediasearch=$mediasearch&amp;mediatypeID=$mediatypeID\">$titlestr$treestr</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle($titlestr);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();
    if ($orgmediatypeID) {
      if ($mediatypes_icons[$mediatypeID]) {
        $icon = "<img class='icon-md' src='{$mediatypes_icons[$mediatypeID]}' alt=''>";
      } else {
        $icon = "<span class='icon-md 'icon-{$mediatypeID}'></span>";
      }
    } else {
      $icon = "<img class='icon-md' src='svg/media-mixed.svg'>";
    }
    ?>
    <h2><?php echo $icon . $titlestr; ?></h2>
    <br clear='all'>
    <?php
    $hiddenfields[0] = array('name' => 'mediatypeID', 'value' => $orgmediatypeID);
    $hiddenfields[1] = array('name' => 'tnggallery', 'value' => $tnggallery);
    echo treeDropdown(array('startform' => true, 'endform' => true, 'action' => 'browsemedia', 'method' => 'get', 'name' => 'form1', 'id' => 'form1', 'hidden' => $hiddenfields));

    $toplinks = "<p>";
    if ($totrows) {
      $toplinks .= uiTextSnippet('matches') . " " . number_format($offsetplus) . " " . uiTextSnippet('to') . " " . number_format($numrowsplus) . " " . uiTextSnippet('of') . " " . number_format($totrows) . " &nbsp;&nbsp;&nbsp; ";
    }
    $toplinks .= "$gallerymsg";

    $pagenav = buildSearchResultPagination($totrows, "browsemedia.php?mediasearch=$mediasearch&amp;tnggallery=$tnggallery&amp;mediatypeID=$orgmediatypeID&amp;offset", $maxsearchresults, $max_browsemedia_pages);
    $preheader = doMediaSearch(1, $pagenav);
    $preheader .= "<br>\n";

    if ($tnggallery) {
      $preheader .= "<div class=\"titlebox\">\n";
      $firstrow = 1;
      $tablewidth = "";
      $header = "";
    } else {
      $header = "<tr><td width='10'></td>\n";
      $header .= "<td width=\"$thumbmaxw\">" . uiTextSnippet('thumb') . "</td>\n";
      $width = $mediatypeID == "headstones" ? "50%" : "75%";
      $header .= "<td width=\"$width\">" . uiTextSnippet('description') . "</td>\n";
      if ($mediatypeID == "headstones") {
        $header .= "<td>" . uiTextSnippet('cemetery') . "</td>\n";
        $header .= "<td>" . uiTextSnippet('status') . "</td>\n";
      }
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

      $status = $row['status'];
      if ($status && uiTextSnippet($status)) {
        $row['status'] = uiTextSnippet($status);
      }
      $query = "SELECT $medialinks_table.mediaID, $medialinks_table.personID as personID, people.personID as personID2, people.living as living, people.private as private, people.branch as branch, $medialinks_table.eventID, $families_table.branch as fbranch,
        $families_table.living as fliving, $families_table.private as fprivate, familyID, husband, wife, people.lastname as lastname, people.lnprefix as lnprefix, people.firstname as firstname, people.prefix as prefix, people.suffix as suffix, nameorder,
        $medialinks_table.gedcom, $sources_table.title, $sources_table.sourceID, $repositories_table.repoID, reponame, deathdate, burialdate, linktype
        FROM $medialinks_table
        LEFT JOIN $people_table AS people ON $medialinks_table.personID = people.personID AND $medialinks_table.gedcom = people.gedcom
        LEFT JOIN $families_table ON $medialinks_table.personID = $families_table.familyID AND $medialinks_table.gedcom = $families_table.gedcom
        LEFT JOIN $sources_table ON $medialinks_table.personID = $sources_table.sourceID AND $medialinks_table.gedcom = $sources_table.gedcom
        LEFT JOIN $repositories_table ON ($medialinks_table.personID = $repositories_table.repoID AND $medialinks_table.gedcom = $repositories_table.gedcom)
        WHERE mediaID = \"{$row['mediaID']}\"$wherestr2 ORDER BY lastname, lnprefix, firstname, personID LIMIT $maxplus";
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
        $rights = determineLivingPrivateRights($prow);
        $prow['allow_living'] = $rights['living'];
        $prow['allow_private'] = $rights['private'];

        if (!$rights['living']) {
          $foundliving = 1;
        }
        if (!$rights['private']) {
          $foundprivate = 1;
        }
        if (!$tnggallery) {
          $hstext = "";
          if ($prow['personID2'] != null) {
            $medialinktext .= "<li><a href=\"peopleShowPerson.php?personID={$prow['personID2']}&amp;tree={$prow['gedcom']}\">";
            $medialinktext .= getName($prow);
            if ($orgmediatypeID == "headstones") {
              $deathdate = $prow['deathdate'] ? $prow['deathdate'] : $prow['burialdate'];
              if ($prow['deathdate']) {
                $abbrev = uiTextSnippet('deathabbr');
              } elseif ($prow['burialdate']) {
                $abbrev = uiTextSnippet('burialabbr');
              }
              $hstext = $deathdate ? " ($abbrev " . displayDate($deathdate) . ")" : "";
            }
          } elseif ($prow['sourceID'] != null) {
            $sourcetext = $prow['title'] ? uiTextSnippet('source') . ": " . $prow['title'] : uiTextSnippet('source') . ": " . $prow['sourceID'];
            $medialinktext .= "<li><a href=\"showsource.php?sourceID={$prow['personID']}&amp;tree={$prow['gedcom']}\">$sourcetext\n";
          } elseif ($prow['repoID'] != null) {
            $repotext = $prow['reponame'] ? uiTextSnippet('repository') . ": " . $prow['reponame'] : uiTextSnippet('repository') . ": " . $prow['repoID'];
            $medialinktext .= "<li><a href=\"repositoriesShowItem.php?repoID={$prow['personID']}&amp;tree={$prow['gedcom']}\">$repotext";
          } elseif ($prow['familyID'] != null) {
            $medialinktext .= "<li><a href=\"familygroup.php?familyID={$prow['personID']}&amp;tree={$prow['gedcom']}\">" . uiTextSnippet('family') . ": " . getFamilyName($prow);
          } else {
            $treestr = $tngconfig['places1tree'] ? "" : "&amp;tree={$prow['gedcom']}";
            $medialinktext .= "<li><a href=\"placesearch.php?psearch={$prow['personID']}$treestr\">" . $prow['personID'];
          }
          if ($prow['eventID']) {
            $query = "SELECT display from $events_table, $eventtypes_table WHERE eventID = \"{$prow['eventID']}\" AND $events_table.eventtypeID = $eventtypes_table.eventtypeID";
            $eresult = tng_query($query);
            $erow = tng_fetch_assoc($eresult);
            $event = $erow['display'] && is_numeric($prow['eventID']) ? getEventDisplay($erow['display']) : (uiTextSnippet($prow['eventID']) ? uiTextSnippet($prow['eventID']) : $prow['eventID']);
            tng_free_result($eresult);
            $medialinktext .= " ($event)";
          }
          $medialinktext .= "</a>$hstext\n</li>\n";
        }
        $count++;
      }
      $showPhotoInfo = $row['allow_living'] = $row['alwayson'] || (!$foundprivate && !$foundliving);

      //if extension is in "showdirect" then link = folder (depends on usecollfolder) + / + path
      //else showmedia
      tng_free_result($presult);
      if ($medialinktext) {
        $medialinktext = "<ul>$medialinktext</ul>\n";
      }
      $row['all'] = $orgmediatypeID ? 0 : 1;
      $uselink = getMediaHREF($row, 0);

      if ($numrows == $maxplus) {
        $medialinktext .= "\n['<a href=\"showmedia.php?mediaID={$row['mediaID']}&amp;ioffset=$maxsearchresults\">" . uiTextSnippet('morelinks') . "</a>']";
      }
      $imgsrc = getSmallPhoto($row);
      if ($showPhotoInfo) {
        $href = $uselink;
      } else {
        $href = "";
      }
      if ($href && strpos($href, "showmedia.php") !== false && !$firsthref) {
        $firsthref = $href;
      }
      $notes = nl2br(truncateIt(getXrefNotes($row['notes'], $row['gedcom']), $tngconfig['maxnoteprev']));
      if ($row['allow_living']) {
        $description = $showPhotoInfo ? "<a href=\"$href\">{$row['description']}</a>" : $row['description'];
      } else {
        $nonamesloc = $row['private'] ? $tngconfig['nnpriv'] : $nonames;
        if ($nonamesloc) {
          $description = uiTextSnippet('livingphoto');
          $notes = "";
        } else {
          $description = $row['description'];
          $notes = $notes ? $notes . "<br>(" . uiTextSnippet('livingphoto') . ")" : "(" . uiTextSnippet('livingphoto') . ")";
        }
      }
      if ($row['status'] && ($orgmediatypeID != "headstones")) {
        $notes = uiTextSnippet('status') . ": " . $row['status'] . "; " . $notes;
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
        $mediatext .= "<tr><td>$i</td>\n";
        if ($imgsrc) {
          $mediatext .= "<td>\n";
          $mediatext .= "<div class=\"media-img\" id=\"mi{$row['mediaID']}\">\n";
          $mediatext .= "<div class=\"media-prev\" id=\"prev{$row['mediaID']}\" style=\"display:none\"></div>\n";
          $mediatext .= "</div>\n";
          if ($href && $row['allow_living']) {
            $mediatext .= "<a href=\"$href\"";
            if ($gotImageJpeg && isPhoto($row) && checkMediaFileSize("$rootpath$usefolder/{$row['path']}")) {
              $mediatext .= " class=\"media-preview\" id=\"img-{$row['mediaID']}-0-" . urlencode("$usefolder/{$row['path']}") . "\"";
            }
            $mediatext .= ">$imgsrc</a>";
          } else {
            $mediatext .= $imgsrc;
          }
          $mediatext .= "</td>\n";
          $mediatext .= "<td>\n";
          $thumbcount++;
        } else {
          $mediatext .= "<td></td>\n";
          $mediatext .= "<td>\n";
        }
        $mediatext .= "$description<br>$notes&nbsp;</td>\n";
        if ($orgmediatypeID == "headstones") {
          if (!$row['cemname']) {
            $row['cemname'] = $row['city'];
          }
          $plotstr = $row['plot'] ? "<br>" . nl2br($row['plot']) : "";
          $mediatext .= "<td width=\"30%\"><a href=\"showmap.php?cemeteryID={$row['cemeteryID']}\">{$row['cemname']}</a>$plotstr&nbsp;</td>\n";
          $mediatext .= "<td>{$row['status']}</td>\n";
          $mediatext .= "<td width=\"30%\">\n";
        } else {
          $mediatext .= "<td width=\"175\">\n";
        }
        $mediatext .= $medialinktext;
        $mediatext .= "</td>\n";
        $mediatext .= "</tr>\n";
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
        $mediatext = str_replace("<td></td><td>", "<td>", $mediatext);
      }
    }
    if (!$tngconfig['ssdisabled'] && $firsthref && $totrows > 1) {
      $ss = strpos($firsthref, "?") ? "&amp;ss=1" : "?ss=1";
      $toplinks .= " &nbsp;&nbsp; <a href=\"$firsthref$ss\">&raquo; " . uiTextSnippet('slidestart') . "</a>";
    }
    $toplinks .= "</p>";
    //print out the whole shootin' match right here, eh
    echo $toplinks . $preheader . $header . $mediatext;
    echo "</table>\n";

    if ($tnggallery) {
      echo "</div>\n";
    }
    echo "<br>\n";

    if ($totrows && ($pagenav || $mediasearch)) {
      echo doMediaSearch(2, $pagenav);
      echo "<br>";
    }
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>