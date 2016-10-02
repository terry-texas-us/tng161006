<?php
/**
 * Name history: browsemedia.php
 */

require 'tng_begin.php';

require 'functions.php';

if (isset($mediatypeID)) {
  $mediatypeID = preg_replace('/[<>{};!=]/', '', $mediatypeID);
}

$flags['imgprev'] = true;

$orgmediatypeID = $mediatypeID;
initMediaTypes();

if ($orgmediatypeID) {
  $wherestr = "WHERE mediatypeID = \"$mediatypeID\"";
  $titlestr = uiTextSnippet($mediatypeID) ? uiTextSnippet($mediatypeID) : $mediatypes_display[$mediatypeID];
  if ($orgmediatypeID == 'headstones') {
    $hsfields = ', media.cemeteryID, cemname, city';
    $hsjoin = 'LEFT JOIN cemeteries ON media.cemeteryID = cemeteries.cemeteryID';
  } else {
    $hsfields = $hsjoin = '';
  }
} else {
  $wherestr = 'WHERE 1 = 1';
  $titlestr = uiTextSnippet('allmedia');
}

if ($mediasearch) {
  $mediasearch = trim($mediasearch);
  $_SESSION['tng_mediasearch'] = $mediasearch;

  $mediasearch2 = addslashes($mediasearch);
  $mediasearch = cleanIt($mediasearch);
} else {
  $_SESSION['tng_mediasearch'] = '';
}

if ($tnggallery) {
  if (!$tngconfig['thumbcols']) {
    $tngconfig['thumbcols'] = 8;
  }
  $maxsearchresults *= 2;
  $wherestr .= ' AND thumbpath != ""';
  $gallerymsg = "<a href=\"mediaShow.php?mediatypeID=$orgmediatypeID&amp;mediasearch=$mediasearch\">&raquo; " . uiTextSnippet('regphotos') . '</a>';
} else {
  $gallerymsg = "<a href=\"mediaShow.php?tnggallery=1&amp;mediatypeID=$orgmediatypeID&amp;mediasearch=$mediasearch\">&raquo; " . uiTextSnippet('gallery') . '</a>';
}
$_SESSION['tng_gallery'] = $tnggallery;

function buildMediaSearchForm($instance, $pagenav) {
  global $mediasearch;
  global $orgmediatypeID;
  global $tnggallery;

  $html = "<div>\n";
  $html .= "<form class='form-inline' name='MediaSearch" . $instance . "' action='mediaShow.php' method='get'>\n";
  $html .= "<input class='form-control' name='mediasearch' type='text' value=\"$mediasearch\" /> \n";
  $html .= "<button class='btn btn-outline-primary' type='submit' value='" . uiTextSnippet('search') . "' /><img class='icon-sm' src='svg/magnifying-glass.svg'></button>\n";
  $html .= "<input class='btn btn-outline-secondary' type='button' value='" . uiTextSnippet('reset') . "' onclick=\"window.location.href='mediaShow.php?mediatypeID=$orgmediatypeID&amp;tnggallery=$tnggallery';\" />&nbsp;&nbsp;&nbsp;";
  $html .= "<input name='mediatypeID' type='hidden' value=\"$orgmediatypeID\" />\n";
//  $html .= $pagenav;
  $html .= "<input name='tnggallery' type='hidden' value=\"$tnggallery\" />\n";
  $html .= "</form>\n";
  $html .= "</div>\n";

  return $html;
}

$max_browsemedia_pages = 5;
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = '';
  $page = 1;
}
if ($mediasearch) {
  $wherestr .= " AND (media.description LIKE \"%$mediasearch2%\" OR media.notes LIKE \"%$mediasearch2%\" OR bodytext LIKE \"%$mediasearch2%\")";
}

$query = "SELECT media.mediaID, media.description, media.notes, path, thumbpath, alwayson, usecollfolder, form, mediatypeID, status, plot, newwindow, abspath $hsfields FROM media";
$query .= " $hsjoin $wherestr ORDER BY description LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);
$numrows = tng_num_rows($result);

if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(media.mediaID) AS mcount FROM media $wherestr";

  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  tng_free_result($result2);
  $totrows = $row['mcount'];
} else {
  $totrows = $numrows;
}

$numrowsplus = $numrows + $offset;

$logstring = "<a href=\"mediaShow.php?offset=$offset&amp;mediasearch=$mediasearch&amp;mediatypeID=$mediatypeID\">$titlestr</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle($titlestr);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $sessionCharset); ?>
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
    <br>
    <?php
    $hiddenfields[0] = ['name' => 'mediatypeID', 'value' => $orgmediatypeID];
    $hiddenfields[1] = ['name' => 'tnggallery', 'value' => $tnggallery];

    $toplinks = '<p>';
    if ($totrows) {
      $toplinks .= uiTextSnippet('matches') . ' ' . number_format($offsetplus) . ' ' . uiTextSnippet('to') . ' ' . number_format($numrowsplus) . ' ' . uiTextSnippet('of') . ' ' . number_format($totrows) . ' &nbsp;&nbsp;&nbsp; ';
    }
    $toplinks .= "$gallerymsg";

    $pagenav = buildSearchResultPagination($totrows, "mediaShow.php?mediasearch=$mediasearch&amp;tnggallery=$tnggallery&amp;mediatypeID=$orgmediatypeID&amp;offset", $maxsearchresults, $max_browsemedia_pages);
    $preheader = buildMediaSearchForm(1, $pagenav);
    $preheader .= "<br>\n";

    $tableClass = 'table table-sm';
    if ($tnggallery) {
      $preheader .= "<div class='card'>\n";
      $firstrow = 1;
      $header = '';
    } else {
      $tableClass .= ' table-hover';
      $header = "<thead class='thead-default'>\n";
      $header .= "<tr><th width='10'></th>\n";
      $header .= "<th width=\"$thumbmaxw\">" . uiTextSnippet('thumb') . "</th>\n";
      $width = ($mediatypeID === 'headstones') ? '50%' : '75%';
      $header .= "<th width=\"$width\">" . uiTextSnippet('description') . "</th>\n";
      if ($mediatypeID === 'headstones') {
        $header .= '<th>' . uiTextSnippet('cemetery') . "</th>\n";
        $header .= '<th>' . uiTextSnippet('status') . "</th>\n";
      }
      $header .= '<th>' . uiTextSnippet('indlinked') . "</th>\n";
      $header .= "</tr>\n";
      $header .= "</thead>\n";
    }
    $header = "<table class='$tableClass'>\n" . $header;

    $i = $offsetplus;
    $maxplus = $maxsearchresults + 1;
    $mediatext = '';
    $firsthref = '';
    $thumbcount = 0;
    $gotImageJpeg = function_exists(imageJpeg);
    while ($row = tng_fetch_assoc($result)) {
      $mediatypeID = $row['mediatypeID'];
      $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;

      $status = $row['status'];
      if ($status && uiTextSnippet($status)) {
        $row['status'] = uiTextSnippet($status);
      }
      $query = "SELECT medialinks.mediaID, medialinks.personID AS personID, people.personID AS personID2, people.living AS living, people.private AS private, people.branch AS branch, medialinks.eventID, families.branch AS fbranch, families.living AS fliving, families.private AS fprivate, familyID, husband, wife, people.lastname AS lastname, people.lnprefix AS lnprefix, people.firstname AS firstname, people.prefix AS prefix, people.suffix AS suffix, nameorder, sources.title, sources.sourceID, repositories.repoID, reponame, deathdate, burialdate, linktype FROM medialinks LEFT JOIN people AS people ON medialinks.personID = people.personID LEFT JOIN families ON medialinks.personID = families.familyID LEFT JOIN sources ON medialinks.personID = sources.sourceID LEFT JOIN repositories ON (medialinks.personID = repositories.repoID) WHERE mediaID = '{$row['mediaID']}' ORDER BY lastname, lnprefix, firstname, personID LIMIT $maxplus"; 
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
          $hstext = '';
          if ($prow['personID2'] != null) {
            $medialinktext .= "<li><a href=\"peopleShowPerson.php?personID={$prow['personID2']}\">";
            $medialinktext .= getName($prow);
            if ($orgmediatypeID == 'headstones') {
              $deathdate = $prow['deathdate'] ? $prow['deathdate'] : $prow['burialdate'];
              if ($prow['deathdate']) {
                $abbrev = uiTextSnippet('deathabbr');
              } elseif ($prow['burialdate']) {
                $abbrev = uiTextSnippet('burialabbr');
              }
              $hstext = $deathdate ? " ($abbrev " . displayDate($deathdate) . ')' : '';
            }
          } elseif ($prow['sourceID'] != null) {
            $sourcetext = $prow['title'] ? uiTextSnippet('source') . ': ' . $prow['title'] : uiTextSnippet('source') . ': ' . $prow['sourceID'];
            $medialinktext .= "<li><a href=\"sourcesShowSource.php?sourceID={$prow['personID']}\">$sourcetext\n";
          } elseif ($prow['repoID'] != null) {
            $repotext = $prow['reponame'] ? uiTextSnippet('repository') . ': ' . $prow['reponame'] : uiTextSnippet('repository') . ': ' . $prow['repoID'];
            $medialinktext .= "<li><a href=\"repositoriesShowItem.php?repoID={$prow['personID']}\">$repotext";
          } elseif ($prow['familyID'] != null) {
            $medialinktext .= "<li><a href=\"familiesShowFamily.php?familyID={$prow['personID']}\">" . uiTextSnippet('family') . ': ' . getFamilyName($prow);
          } else {
            $medialinktext .= "<li><a href=\"placesearch.php?psearch={$prow['personID']}\">" . $prow['personID'];
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
        $href = '';
      }
      if ($href && strpos($href, 'showmedia.php') !== false && !$firsthref) {
        $firsthref = $href;
      }
      $notes = nl2br(truncateIt(getXrefNotes($row['notes']), $tngconfig['maxnoteprev']));
      if ($row['allow_living']) {
        $description = $showPhotoInfo ? "<a href=\"$href\">{$row['description']}</a>" : $row['description'];
      } else {
        $nonamesloc = $row['private'] ? $tngconfig['nnpriv'] : $nonames;
        if ($nonamesloc) {
          $description = uiTextSnippet('livingphoto');
          $notes = '';
        } else {
          $description = $row['description'];
          $notes = $notes ? $notes . '<br>(' . uiTextSnippet('livingphoto') . ')' : '(' . uiTextSnippet('livingphoto') . ')';
        }
      }
      if ($row['status'] && ($orgmediatypeID != 'headstones')) {
        $notes = uiTextSnippet('status') . ': ' . $row['status'] . '; ' . $notes;
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
          $mediatext .= '<td style="padding: 10px">';
          $mediatext .= $href ? "<a href=\"$href\">$imgsrc</a></td>\n" : "$imgsrc</td>\n";
          $i++;
        }
      } else {
        $mediatext .= "<tr><td>$i</td>\n";
        if ($imgsrc) {
          $mediatext .= "<td>\n";
          $mediatext .= "<div class=\"media-img\" id=\"mi{$row['mediaID']}\">\n";
          $mediatext .= "<div class=\"media-prev\" id=\"prev{$row['mediaID']}\" style='display: none'></div>\n";
          $mediatext .= "</div>\n";
          if ($href && $row['allow_living']) {
            $mediatext .= "<a href=\"$href\"";
            if ($gotImageJpeg && isPhoto($row) && checkMediaFileSize("$rootpath$usefolder/{$row['path']}")) {
              $mediatext .= " class=\"media-preview\" id=\"img-{$row['mediaID']}-0-" . urlencode("$usefolder/{$row['path']}") . '"';
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
        if ($orgmediatypeID == 'headstones') {
          if (!$row['cemname']) {
            $row['cemname'] = $row['city'];
          }
          $plotstr = $row['plot'] ? '<br>' . nl2br($row['plot']) : '';
          $mediatext .= "<td width=\"30%\"><a href=\"cemeteriesShowCemetery.php?cemeteryID={$row['cemeteryID']}\">{$row['cemname']}</a>$plotstr&nbsp;</td>\n";
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
        $header = str_replace('<td>' . uiTextSnippet('thumb') . '</td>', '', $header);
        $mediatext = str_replace('<td></td><td>', '<td>', $mediatext);
      }
    }
    $toplinks .= "</p>\n";
    //print out the whole shootin' match right here, eh
    echo $toplinks . $preheader . $header . $mediatext;
    echo "</table>\n";

    if ($tnggallery) {
      echo "</div>\n";
    }

    if ($totrows && ($pagenav || $mediasearch)) {
      echo $pagenav;
    }
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>