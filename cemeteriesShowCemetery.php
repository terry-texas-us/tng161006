<?php
$needMap = true;
include("tng_begin.php");
include($subroot . "mapconfig.php");

if (!$cemeteryID || !is_numeric($cemeteryID)) {
  header("Location: thispagedoesnotexist.html");
  exit;
}
include("functions.php");

$flags['imgprev'] = true;

$treequery = "SELECT count(gedcom) as treecount FROM $trees_table";
$treeresult = tng_query($treequery);
$treerow = tng_fetch_assoc($treeresult);
$numtrees = $treerow['treecount'];
tng_free_result($treeresult);

if (!$thumbmaxw) {
  $thumbmaxw = 80;
}

if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $page = 1;
}

if ($cemeteryID) {
  $query = "SELECT cemname, city, county, state, country, maplink, notes, latitude, longitude, zoom, place FROM $cemeteries_table WHERE cemeteryID = \"$cemeteryID\"";
  $cemresult = tng_query($query);

  if (!tng_num_rows($cemresult)) {
    header("Location: thispagedoesnotexist.html");
    exit;
  }

  $cemetery = tng_fetch_assoc($cemresult);
  tng_free_result($cemresult);

  $location = $cemetery['cemname'];
  if ($cemetery['city']) {
    if ($location) {
      $location .= ", ";
    }
    $location .= $cemetery['city'];
  }
  if ($cemetery['county']) {
    if ($location) {
      $location .= ", ";
    }
    $location .= $cemetery['county'];
  }
  if ($cemetery['state']) {
    if ($location) {
      $location .= ", ";
    }
    $location .= $cemetery['state'];
  }
  if ($cemetery['country']) {
    if ($location) {
      $location .= ", ";
    }
    $location .= $cemetery['country'];
  }
} else {
  $location = uiTextSnippet('nocemetery');
}

$logstring = "<a href=\"cemeteriesShowCemetery.php?cemeteryID=$cemeteryID&amp;tree=$tree\">$location</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle($location);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/headstone.svg'><?php echo $location; ?></h2>
    <br clear='all'>
    <?php
    $hiddenfields[] = array('name' => 'cemeteryID', 'value' => $cemeteryID);
    echo treeDropdown(array('startform' => true, 'endform' => true, 'action' => 'cemeteriesShowCemetery', 'method' => 'get', 'name' => 'form1', 'id' => 'form1', 'hidden' => $hiddenfields));

    $infoblock = "";
    $body = "";
    if ($cemeteryID) {
      if ($cemetery['maplink'] && file_exists("$rootpath$headstonepath/" . $cemetery['maplink'])) {
        $imageSize = getimagesize("$rootpath$headstonepath/" . $cemetery['maplink']);
        $infoblock .= "<img src=\"$headstonepath/{$cemetery['maplink']}\" $imageSize[3] alt=\"{$cemetery['cemname']}\"><br><br>\n";
      }
      if ($allow_admin && $allowEdit) {
        $infoblock .= "<p><a href=\"cemeteriesEdit.php?cemeteryID=$cemeteryID&amp;cw=1\" target='_blank'>" . uiTextSnippet('editcem') . "</a></p>\n";
      }
      if ($cemetery['notes']) {
        $infoblock .= "<p><strong>" . uiTextSnippet('notes') . ":</strong><br>\n" . nl2br(insertLinks($cemetery['notes'])) . "</p>";
      }
      if (!$map['key'] && ($cemetery['latitude'] || $cemetery['longitude'])) {
        $infoblock .= "<p><strong>" . uiTextSnippet('latitude') . ":</strong> {$cemetery['latitude']}, <strong>" . uiTextSnippet('longitude') . ":</strong> {$cemetery['longitude']}</p>";
      }
      $cemcoords = false;
      if ($map['key']) {
        $lat = $cemetery['latitude'];
        $long = $cemetery['longitude'];
        $zoom = $cemetery['zoom'] ? $cemetery['zoom'] : 10;
        if (!$zoom) {
          $zoom = 10;
        }
        //RM - set placeleve = 2 to provide this value to the map for all cemeteries
        $pinplacelevel = $pinplacelevel2;

        // if we have one, add it
        if ($lat && $long) {
          $cemeteryplace = "{$cemetery['city']}, {$cemetery['county']}, {$cemetery['state']}, {$cemetery['country']}";
          $localballooncemeteryname = htmlspecialchars($cemetery['cemname'], ENT_QUOTES, $session_charset);
          $localballooncemeteryplace = htmlspecialchars($cemeteryplace, ENT_QUOTES, $session_charset);
          $remoteballoontext = htmlspecialchars(str_replace($banish, $banreplace, "{$cemetery['cemname']}, $cemeteryplace"), ENT_QUOTES, $session_charset);
          $codednotes = $cemetery['notes'] ? "<br><br>" . tng_real_escape_string(uiTextSnippet('notes') . ": " . $cemetery['notes']) : "";
          $codednotes .= "<br><br><a href=\"https://maps.google.com/maps?f=q" . uiTextSnippet('glang') . "$mcharsetstr&amp;daddr=$lat,$long($remoteballoontext)\" target=\"_blank\">" .
                  uiTextSnippet('getdirections') . "</a>" . uiTextSnippet('directionsto') . " $localballooncemeteryname";
          $locations2map[$l2mCount] = [
            "zoom" => $zoom,
            "lat" => $lat,
            "long" => $long,
            "pinplacelevel" => $pinplacelevel,
            "htmlcontent" => "<div class=\"mapballoon\">$localballooncemeteryname<br>$localballooncemeteryplace$codednotes</div>"
          ];
          $cemcoords = true;
          $body .= "<div style=\"padding-bottom:15px\">\n";
          $body .= "<a href=\"https://maps.google.com/maps?f=q" . uiTextSnippet('glang') . "$mcharsetstr&amp;daddr=$lat,$long($remoteballoontext)&amp;z=$zoom&amp;om=1&amp;iwloc=addr\" target='_blank'>\n";
          $body .= "<img src=\"google_marker.php?image=$pinplacelevel2.png&amp;text=1\" alt=''>\n";
          $body .= "</a>";
          $map['pins']++;
          $body .= "<span><strong>" . uiTextSnippet('latitude') . ":</strong> $lat, <strong>" . uiTextSnippet('longitude') . ":</strong> $long</span></div>";
        }
      }
    }
    if ($infoblock) {
      $body .= "<div class=\"titlebox\">$infoblock</div>\n<br>\n";
    }
    $query = "SELECT mediaID, thumbpath, description, notes, usecollfolder, mediatypeID, path, form, abspath, newwindow from $media_table WHERE cemeteryID = \"$cemeteryID\" AND (mediatypeID != \"headstones\" OR linktocem = \"1\") ORDER BY description";
    $hsresult = tng_query($query);
    $gotImageJpeg = function_exists(imageJpeg);
    if (tng_num_rows($hsresult)) {
      $i = 1;
      $body .= "<div>\n";
      $body .= "<h4><b>" . uiTextSnippet('cemphotos') . "</b></h4>\n";

      $body .= "<table class='table'>\n";
      $body .= "<tr><th width='10'></th>\n";
      $body .= "<th width=\"$thumbmaxw\">" . uiTextSnippet('thumb') . "</th>\n";
      $body .= "<th>" . uiTextSnippet('description') . "</th>\n";

      while ($hs = tng_fetch_assoc($hsresult)) {
        $mediatypeID = $hs['mediatypeID'];
        $usefolder = $hs['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
        $description = $hs['description'];
        $notes = nl2br($hs['notes']);

        $hs['allow_living'] = $hs['allow_private'] = 1;

        $imgsrc = getSmallPhoto($hs);
        $href = getMediaHREF($hs, 3);

        $body .= "<tr><td>$i</td>";
        $body .= "<td width=\"$thumbmaxw\">";
        if ($imgsrc) {
          $body .= "<div class=\"media-img\"><div class=\"media-prev\" id=\"prev{$hs['mediaID']}\" style=\"display:none\"></div></div>\n";
          $body .= "<a href=\"$href\"";
          if ($gotImageJpeg && checkMediaFileSize("$rootpath$usefolder/{$hs['path']}")) {
            $body .= " class=\"media-preview\" id=\"img-{$hs['mediaID']}-0-" . urlencode("$usefolder/{$hs['path']}") . "\"";
          }
          $body .= ">$imgsrc</a>\n";
        } else {
          $body .= "&nbsp;";
        }

        $body .= "</td>\n";
        $body .= "<td>";
        $body .= "<a href=\"$href\">$description</a><br>$notes</td></tr>\n";
        $i++;
      }
      $body .= "</table>\n";
      $body .= "</div>\n<br>\n";
    }
    tng_free_result($hsresult);

    if ($tree) {
      $wherestr = " AND ($media_table.gedcom = \"$tree\" || $media_table.gedcom = \"\")";
      $wherestr2 = " AND $medialinks_table.gedcom = \"$tree\"";
    } else {
      $wherestr = $wherestr2 = "";
    }

    $query = "SELECT DISTINCT $media_table.mediaID, description, notes, path, thumbpath, status, plot, showmap, usecollfolder, mediatypeID, latitude, longitude, form, abspath, newwindow
      FROM $media_table LEFT JOIN $medialinks_table on $media_table.mediaID = $medialinks_table.mediaID
      WHERE cemeteryID = \"$cemeteryID\"$typeclause $wherestr AND mediatypeID = \"headstones\" AND linktocem != \"1\" ORDER BY description LIMIT $newoffset" . $maxsearchresults;
    $hsresult = tng_query($query);

    $numrows = tng_num_rows($hsresult);
    if ($numrows) {
      $body .= "<div>\n";
      $body .= "<h4>" . uiTextSnippet('headstone') . "</h4>\n";

      if ($numrows == $maxsearchresults || $offsetplus > 1) {
        $query = "SELECT count(DISTINCT $media_table.mediaID) as hscount FROM $media_table LEFT JOIN $medialinks_table on $media_table.mediaID = $medialinks_table.mediaID WHERE cemeteryID = \"$cemeteryID\"$typeclause $wherestr AND linktocem != \"1\"";
        $result2 = tng_query($query);
        $row = tng_fetch_assoc($result2);
        $totrows = $row['hscount'];
      } else {
        $totrows = $numrows;
      }

      $pagenav = buildSearchResultPagination($totrows, "cemeteriesShowCemetery.php?cemeteryID=$cemeteryID&amp;tree=$tree&amp;offset", $maxsearchresults, 5);
      if ($pagenav) {
        $body .= "<p>$pagenav</p>";
      }
      $body .= "<table class='table'>\n";
      $body .= "<tr>\n";
      $body .= "<th>" . uiTextSnippet('thumb') . "</th>";
      $body .= "<th>" . uiTextSnippet('description') . "</th>";
      $body .= "<th>" . uiTextSnippet('status') . "</th>";
      $body .= "<th>" . uiTextSnippet('location') . "</th>";
      $body .= "<th>" . uiTextSnippet('name') . " (" . uiTextSnippet('diedburied') . ")</th>\n";
      $body .= "</tr>\n";

      while ($hs = tng_fetch_assoc($hsresult)) {
        $mediatypeID = $hs['mediatypeID'];
        $hs['cemeteryID'] = $cemeteryID;
        $usefolder = $hs['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;

        $status = $hs['status'];
        if ($status && uiTextSnippet($status)) {
          $hs['status'] = uiTextSnippet($status);
        }

        $query = "SELECT medialinkID, $medialinks_table.personID as personID, people.personID as personID2, familyID, people.living as living, people.private as private, people.branch as branch,
          $families_table.branch as fbranch, $families_table.living as fliving, $families_table.private as fprivate, husband, wife, people.lastname as lastname, people.lnprefix as lnprefix, people.firstname as firstname,
          people.prefix as prefix, people.suffix as suffix, nameorder, $medialinks_table.gedcom as gedcom, treename, $sources_table.title, $sources_table.sourceID, $repositories_table.repoID,reponame, deathdate, burialdate, linktype
          FROM ($medialinks_table, $trees_table)
          LEFT JOIN $people_table AS people ON ($medialinks_table.personID = people.personID AND $medialinks_table.gedcom = people.gedcom)
          LEFT JOIN $families_table ON ($medialinks_table.personID = $families_table.familyID AND $medialinks_table.gedcom = $families_table.gedcom)
          LEFT JOIN $sources_table ON ($medialinks_table.personID = $sources_table.sourceID AND $medialinks_table.gedcom = $sources_table.gedcom)
          LEFT JOIN $repositories_table ON ($medialinks_table.personID = $repositories_table.repoID AND $medialinks_table.gedcom = $repositories_table.gedcom)
          WHERE mediaID = \"{$hs['mediaID']}\" AND $medialinks_table.gedcom = $trees_table.gedcom $wherestr2 ORDER BY lastname, lnprefix, firstname, $medialinks_table.personID";
        $presult = tng_query($query);
        $hslinktext = "";
        $noneliving = $noneprivate = 1;
        while ($prow = tng_fetch_assoc($presult)) {
          $hstext = "";
          if ($prow['personID2'] != null) {
            $prights = determineLivingPrivateRights($prow);
            $prow['allow_living'] = $prights['living'];
            $prow['allow_private'] = $prights['private'];

            if (!$prow['allow_living']) {
              $noneliving = 0;
            }
            if (!$prow['allow_private']) {
              $noneprivate = 0;
            }
            $hslinktext .= "<a href=\"peopleShowPerson.php?personID={$prow['personID2']}&amp;tree={$prow['gedcom']}\">";
            $hslinktext .= getName($prow);
            $deathdate = $prow['deathdate'] ? $prow['deathdate'] : $prow['burialdate'];
            if ($prow['deathdate']) {
              $abbrev = uiTextSnippet('deathabbr');
            } elseif ($prow['burialdate']) {
              $abbrev = uiTextSnippet('burialabbr');
            }
            $hstext = $deathdate ? " ($abbrev " . displayDate($deathdate) . ")" : "";
          } elseif ($prow['familyID'] != null) {
            $prow['living'] = $prow['fliving'];
            $prow['private'] = $prow['fprivate'];

            $prights = determineLivingPrivateRights($prow);
            $prow['allow_living'] = $prights['living'];
            $prow['allow_private'] = $prights['private'];

            if (!$prow['allow_living']) {
              $noneliving = 0;
            }
            if (!$prow['allow_private']) {
              $noneprivate = 0;
            }
            $hslinktext .= "<a href=\"familiesShowFamily.php?familyID={$prow['familyID']}&amp;tree={$prow['gedcom']}\">" . uiTextSnippet('family') . ": " . getFamilyName($prow);
          } elseif ($prow['sourceID'] != null) {
            $sourcetext = $prow['title'] ? uiTextSnippet('source') . ": {$prow['title']}" : uiTextSnippet('source') . ": {$prow['sourceID']}";
            $hslinktext .= "<a href=\"showsource.php?sourceID={$prow['sourceID']}&amp;tree={$prow['gedcom']}\">$sourcetext";
          } elseif ($prow['repoID'] != null) {
            $repotext = $prow['reponame'] ? uiTextSnippet('repository') . ": {$prow['reponame']}" : uiTextSnippet('repository') . ": {$prow['repoID']}";
            $hslinktext .= "<a href=\"repositoriesShowItem.php?repoID={$prow['repoID']}&amp;tree={$prow['gedcom']}\">$repotext";
          } else {
            $treestr = $tngconfig['places1tree'] ? "" : "&amp;tree={$prow['gedcom']}";
            $hslinktext .= "<a href=\"placesearch.php?psearch={$prow['personID']}$treestr\">{$prow['personID']}";
          }
          $hslinktext .= "</a>$hstext\n<br>\n";
        }
        tng_free_result($presult);

        $description = $hs['description'];
        $notes = nl2br($hs['notes']);

        $body .= "<tr>\n";
        $body .= "<td align='center' style=\"width:$thumbmaxw" . "px\">";
        $hs['allow_living'] = $noneliving;
        $hs['allow_private'] = $noneprivate;
        $imgsrc = getSmallPhoto($hs);
        $href = getMediaHREF($hs, 3);

        if ($imgsrc) {
          $body .= "<div class=\"media-img\"><div class=\"media-prev\" id=\"prev{$hs['mediaID']}\" style=\"display:none\"></div></div>\n";
          $body .= "<a href=\"$href\"";
          if ($gotImageJpeg && isPhoto($hs) && checkMediaFileSize("$rootpath$usefolder/{$hs['path']}")) {
            $body .= " class=\"media-preview\" id=\"img-{$hs['mediaID']}-0-" . urlencode("$usefolder/{$hs['path']}") . "\"";
          }
          $body .= ">$imgsrc</a>\n";
        } else {
          $body .= "&nbsp;";
        }

        $body .= "</td>\n";

        $body .= "<td><span><a href=\"$href\">{$hs['description']}</a><br>$notes&nbsp;</span></td>\n";
        $body .= "<td><span>{$hs['status']}&nbsp;</span></td>\n";
        $body .= "<td><span>" . nl2br($hs['plot']);
        if ($hs['latitude'] || $hs['longitude']) {
          if ($hs['plot']) {
            $body .= "<br>";
          }
          $body .= uiTextSnippet('latitude') . ": {$hs['latitude']}, " . uiTextSnippet('longitude') . ": {$hs['longitude']}";
        }
        $body .= "&nbsp;</span></td>\n";
        $body .= "<td><span>$hslinktext&nbsp;</span></td>\n";
        $body .= "</tr>\n";
      }
      $body .= "</table>\n";
      if ($pagenav) {
        $body .= "<p>$pagenav</p>";
      }
      $body .= "</div>\n";
    }
    tng_free_result($hsresult);

    if ($cemetery['place']) {
      $treestr = $tree ? "and $people_table.gedcom = \"$tree\"" : "";
      $query = "SELECT * FROM ($people_table, $trees_table) WHERE burialplace = \"" . addslashes($cemetery['place']) . "\" and $people_table.gedcom = $trees_table.gedcom $treestr ORDER BY lastname, firstname";
      $result = tng_query($query);
      if (tng_num_rows($result)) {
        $body .= "<br><div>\n";
        $body .= "<h4>" . uiTextSnippet('allburials') . "</h4>\n";

        $body .= "<table class='table table-sm table-striped'>\n";
        $body .= "<tr>\n";
        $body .= "<th></th>\n";
        $body .= "<th>" . uiTextSnippet('lastfirst') . "</th>\n";
        $body .= "<th colspan='2'>" . uiTextSnippet('buried') . "</th>\n";
        $body .= "<th>" . uiTextSnippet('personid') . "</th>\n";
        if ($numtrees > 1) {
          $body .= "<th>" . uiTextSnippet('tree') . "</th>\n";
        }
        $body .= "</tr>\n";

        $i = 1;
        while ($row = tng_fetch_assoc($result)) {
          $row['allow_living'] = 1;

          $rights = determineLivingPrivateRights($row);
          $row['allow_living'] = $rights['living'];
          $row['allow_private'] = $rights['private'];

          $name = getNameRev($row);
          $body .= "<tr><td>$i</td>\n";
            $body .= "<td>\n";
              $body .= "<a href=\"pedigree.php?personID={$row['personID']}&amp;tree={$row['gedcom']}\">$chartlink </a>\n"; // [ts] $chartlink undefined .. no chart icon displayed with link
              $body .= "<a href=\"peopleShowPerson.php?personID={$row['personID']}&amp;tree={$row['gedcom']}\">$name</a>\n";
          $body .= "</td>\n";

          $placetxt = $row['burialplace'];
          $placetxt .= "<a href=\"placesearch.php?tree=$tree&amp;psearch=" . urlencode($row['burialplace']) . "\" title=\"" . uiTextSnippet('findplaces') . "\">\n";
          $placetxt .= "<img class='icon-xs-inline' src='svg/magnifying-glass.svg' alt=\"" . uiTextSnippet('findplaces') . "\"></a>\n";

          $deathdate = $row['burialdate'] ? $row['burialdate'] : $row['deathdate'];
          if ($row['burialdate']) {
            $abbrev = uiTextSnippet('burialabbr', ['html' => 'strong']);
          } elseif ($row['deathdate']) {
            $abbrev = uiTextSnippet('deathabbr', ['html' => 'strong']);
          }
          $burialdate = $deathdate ? "$abbrev " . displayDate($deathdate) : "";

          $body .= "<td colspan='2'>$burialdate<br>$placetxt</td>\n";
          $body .= "<td>{$row['personID']}</td>\n";
          if ($numtrees > 1) {
            $body .= "<td><a href=\"showtree.php?tree={$row['gedcom']}\">{$row['treename']}</a>&nbsp;</td>\n";
          }
          $i++;
        }
        $body .= "</table>\n";
        $body .= "</div>\n";
      }
      tng_free_result($result);
    }
    if ($map['key'] && $map['pins']) {
      echo "<div id='map' style=\"width: {$map['hstw']}; height: {$map['hsth']};margin-bottom:20px;\" class='rounded10'></div>\n";
    }
    echo $body;
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <?php if ($map['key']) { ?>
    <script src='https://maps.googleapis.com/maps/api/js?language="<?php echo uiTextSnippet('glang'); ?>"'></script>
  <?php 
  }
  if ($map['key'] && $map['pins']) {
    tng_map_pins();
  }
  ?>
</body>
</html>