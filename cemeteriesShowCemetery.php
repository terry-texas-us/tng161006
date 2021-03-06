<?php
$needMap = true;
require 'tng_begin.php';
require $subroot . 'mapconfig.php';

if (!$cemeteryID || !is_numeric($cemeteryID)) {
  header('Location: thispagedoesnotexist.html');
  exit;
}
require 'functions.php';

$flags['imgprev'] = true;

if (!$thumbmaxw) {
  $thumbmaxw = 80;
}
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = '';
  $page = 1;
}
if ($cemeteryID) {
  $query = "SELECT cemname, city, county, state, country, maplink, notes, latitude, longitude, zoom, place FROM cemeteries WHERE cemeteryID = '$cemeteryID'";
  $cemresult = tng_query($query);

  if (!tng_num_rows($cemresult)) {
    header('Location: thispagedoesnotexist.html');
    exit;
  }

  $cemetery = tng_fetch_assoc($cemresult);
  tng_free_result($cemresult);

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
} else {
  $location = uiTextSnippet('nocemetery');
}

$logstring = "<a href=\"cemeteriesShowCemetery.php?cemeteryID=$cemeteryID\">$location</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle($location);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $sessionCharset); ?>
<body id='cemeteries'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/headstone.svg'><?php echo $location; ?></h2>
    <?php
    $hiddenfields[] = ['name' => 'cemeteryID', 'value' => $cemeteryID];

    $body = '';
    if ($cemeteryID) {
      $infoblock = "<div class='row'>\n";
      $infoblock .= "<div class='col-md-6'>\n";
      if ($cemetery['maplink'] && file_exists("$rootpath$headstonepath/" . $cemetery['maplink'])) {
        $imageSize = getimagesize("$rootpath$headstonepath/" . $cemetery['maplink']);
        $infoblock .= "<img class='information' src=\"$headstonepath/{$cemetery['maplink']}\" alt=\"{$cemetery['cemname']}\">\n";
      }
      $infoblock .= "</div>\n";
      $infoblock .= "<div class='col-md-6'>\n";
      if ($allow_admin && $allowEdit) {
        $infoblock .= "<p><a href=\"cemeteriesEdit.php?cemeteryID=$cemeteryID&amp;cw=1\" target='_blank'>" . uiTextSnippet('editcem') . "</a></p>\n";
      }
      if ($cemetery['notes']) {
        $infoblock .= '<p><strong>' . uiTextSnippet('notes') . ":</strong><br>\n" . nl2br(insertLinks($cemetery['notes'])) . '</p>';
      }
      if ($map['key'] === false && ($cemetery['latitude'] || $cemetery['longitude'])) {
        $infoblock .= '<p><strong>' . uiTextSnippet('latitude') . ":</strong> {$cemetery['latitude']}, <strong>" . uiTextSnippet('longitude') . ":</strong> {$cemetery['longitude']}</p>";
      }
      $infoblock .= "</div>\n";
      $infoblock .= "</div>\n";

      $cemcoords = false;
      if ($map['key'] === true) {
        $lat = $cemetery['latitude'];
        $long = $cemetery['longitude'];
        $zoom = $cemetery['zoom'] ? $cemetery['zoom'] : 10;
        if (!$zoom) {
          $zoom = 10;
        }

        if ($lat && $long) {
          $cemeteryplace = "{$cemetery['city']}, {$cemetery['county']}, {$cemetery['state']}, {$cemetery['country']}";
          $localballooncemeteryname = htmlspecialchars($cemetery['cemname'], ENT_QUOTES, $sessionCharset);
          $localballooncemeteryplace = htmlspecialchars($cemeteryplace, ENT_QUOTES, $sessionCharset);
          $remoteballoontext = htmlspecialchars(str_replace($banish, $banreplace, "{$cemetery['cemname']}, $cemeteryplace"), ENT_QUOTES, $sessionCharset);
          $codednotes = $cemetery['notes'] ? '<br><br>' . tng_real_escape_string(uiTextSnippet('notes') . ': ' . $cemetery['notes']) : '';
          $codednotes .= '<br><br><a href="https://maps.google.com/maps?f=q&amp;' . uiTextSnippet('localize') . "&amp;oe=$sessionCharset&amp;daddr=$lat,$long($remoteballoontext)\" target=\"_blank\">" .
                  uiTextSnippet('getdirections') . '</a>' . uiTextSnippet('directionsto') . " $localballooncemeteryname";
          $locations2map[$l2mCount] = [
            'zoom' => $zoom,
            'lat' => $lat,
            'long' => $long,
            'placelevel' => 2,
            'htmlcontent' => "<div class=\"mapballoon\">$localballooncemeteryname<br>$localballooncemeteryplace$codednotes</div>"
          ];
          $cemcoords = true;
          $googleMap = "<div class='card card-block'\n";
          $googleMap .= "<div class='row'>\n";
          $googleMap .= "<div class='col-md-8'>\n";
          $googleMap .= "<div class='map-cemetery' id='map' style='width: {$map['hstw']}; height: {$map['hsth']};'></div>\n";
          $googleMap .= "</div>\n";
          $googleMap .= "<div class='col-md-4'>\n";
          
          $googleMap .= "<div style='padding-bottom: 15px'>\n";
          $googleMap .= '<a href="https://maps.google.com/maps?f=q&amp;' . uiTextSnippet('localize') . "&amp;oe=$sessionCharset&amp;daddr=$lat,$long($remoteballoontext)&amp;z=$zoom&amp;om=1&amp;iwloc=addr\" target='_blank'>\n";
//          $googleMap .= "<img src=\"google_marker.php?image=$pins[2]&amp;text=1\" alt=''>\n";
          $googleMap .= '</a>';
          $map['pins']++;
          $googleMap .= '<span><strong>' . uiTextSnippet('latitude') . ":</strong> $lat<br><strong>" . uiTextSnippet('longitude') . ":</strong> $long</span>\n";
          $googleMap .= "</div>\n";
          $googleMap .= "</div>\n";
          $googleMap .= "</div>\n"; // .row
          $googleMap .= "</div>\n"; // .card.card-block

          $body .= $googleMap;
        }
      }
      $body .= "<div class='card card-block'>$infoblock</div>\n<br>\n";
    }
    // headstones media
    $query = "SELECT mediaID, thumbpath, description, notes, usecollfolder, mediatypeID, path, form, abspath, newwindow FROM media WHERE cemeteryID = '$cemeteryID' AND (mediatypeID != 'headstones' OR linktocem = '1') ORDER BY description";
    $hsresult = tng_query($query);
    $gotImageJpeg = function_exists('imageJpeg');
    if (tng_num_rows($hsresult)) {
      $i = 1;
      $body .= "<div>\n";
      $body .= '<h4><b>' . uiTextSnippet('cemphotos') . "</b></h4>\n";

      $body .= "<table class='table'>\n";
      $body .= "<thead class='thead-default'>\n";
      $body .= "<tr>\n";
      $body .= "<th width='10'></th>\n";
      $body .= "<th width=\"$thumbmaxw\">" . uiTextSnippet('thumb') . "</th>\n";
      $body .= '<th>' . uiTextSnippet('description') . "</th>\n";
      $body .= "</tr>\n";
      $body .= "</thead>\n";
      
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
          $body .= "<div class=\"media-img\"><div class=\"media-prev\" id=\"prev{$hs['mediaID']}\" style='display: none'></div></div>\n";
          $body .= "<a href=\"$href\"";
          if ($gotImageJpeg && checkMediaFileSize("$rootpath$usefolder/{$hs['path']}")) {
            $body .= " class=\"media-preview\" id=\"img-{$hs['mediaID']}-0-" . urlencode("$usefolder/{$hs['path']}") . '"';
          }
          $body .= ">$imgsrc</a>\n";
        } else {
          $body .= '&nbsp;';
        }

        $body .= "</td>\n";
        $body .= '<td>';
        $body .= "<a href=\"$href\">$description</a><br>$notes</td></tr>\n";
        $i++;
      }
      $body .= "</table>\n";
      $body .= "</div>\n<br>\n";
    }
    tng_free_result($hsresult);

    $query = "SELECT DISTINCT media.mediaID, description, notes, path, thumbpath, status, plot, showmap, usecollfolder, mediatypeID, latitude, longitude, form, abspath, newwindow FROM media LEFT JOIN medialinks ON media.mediaID = medialinks.mediaID WHERE cemeteryID = \"$cemeteryID\"$typeclause AND mediatypeID = 'headstones' AND linktocem != '1' ORDER BY description LIMIT $newoffset" . $maxsearchresults;
    $hsresult = tng_query($query);

    $numrows = tng_num_rows($hsresult);
    if ($numrows) {
      $body .= "<div>\n";
      $body .= '<h4>' . uiTextSnippet('headstone') . "</h4>\n";

      if ($numrows == $maxsearchresults || $offsetplus > 1) {
        $query = "SELECT count(DISTINCT media.mediaID) AS hscount FROM media LEFT JOIN medialinks ON media.mediaID = medialinks.mediaID WHERE cemeteryID = \"$cemeteryID\"$typeclause AND linktocem != '1'";
        $result2 = tng_query($query);
        $row = tng_fetch_assoc($result2);
        $totrows = $row['hscount'];
      } else {
        $totrows = $numrows;
      }

      $pagenav = buildSearchResultPagination($totrows, "cemeteriesShowCemetery.php?cemeteryID=$cemeteryID&amp;offset", $maxsearchresults, 5);
      if ($pagenav) {
        $body .= "<p>$pagenav</p>";
      }
      $body .= "<table class='table table-sm table-hover'>\n";
      $body .= "<thead class='thead-default'>\n";
      $body .= "<tr>\n";
      $body .= '<th>' . uiTextSnippet('thumb') . '</th>';
      $body .= '<th>' . uiTextSnippet('description') . '</th>';
      $body .= '<th>' . uiTextSnippet('status') . '</th>';
      $body .= '<th>' . uiTextSnippet('location') . '</th>';
      $body .= '<th>' . uiTextSnippet('name') . ' (' . uiTextSnippet('diedburied') . ")</th>\n";
      $body .= "</tr>\n";
      $body .= "</thead>\n";

      while ($hs = tng_fetch_assoc($hsresult)) {
        $mediatypeID = $hs['mediatypeID'];
        $hs['cemeteryID'] = $cemeteryID;
        $usefolder = $hs['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;

        $status = $hs['status'];
        if ($status && uiTextSnippet($status)) {
          $hs['status'] = uiTextSnippet($status);
        }

        $query = "SELECT medialinkID, medialinks.personID AS personID, people.personID AS personID2, familyID, people.living AS living, people.private AS private, people.branch AS branch, families.branch as fbranch, families.living as fliving, families.private as fprivate, husband, wife, people.lastname as lastname, people.lnprefix as lnprefix, people.firstname as firstname, people.prefix as prefix, people.suffix as suffix, nameorder, sources.title, sources.sourceID, repositories.repoID,reponame, deathdate, burialdate, linktype FROM (medialinks) LEFT JOIN people AS people ON (medialinks.personID = people.personID) LEFT JOIN families ON (medialinks.personID = families.familyID) LEFT JOIN sources ON (medialinks.personID = sources.sourceID) LEFT JOIN repositories ON (medialinks.personID = repositories.repoID) WHERE mediaID = \"{$hs['mediaID']}\" ORDER BY lastname, lnprefix, firstname, medialinks.personID";
        $presult = tng_query($query);
        $hslinktext = '';
        $noneliving = $noneprivate = 1;
        while ($prow = tng_fetch_assoc($presult)) {
          $hstext = '';
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
            $hslinktext .= "<a href=\"peopleShowPerson.php?personID={$prow['personID2']}\">";
            $hslinktext .= getName($prow);
            $deathdate = $prow['deathdate'] ? $prow['deathdate'] : $prow['burialdate'];
            if ($prow['deathdate']) {
              $abbrev = uiTextSnippet('deathabbr');
            } elseif ($prow['burialdate']) {
              $abbrev = uiTextSnippet('burialabbr');
            }
            $hstext = $deathdate ? " ($abbrev " . displayDate($deathdate) . ')' : '';
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
            $hslinktext .= "<a href=\"familiesShowFamily.php?familyID={$prow['familyID']}\">" . uiTextSnippet('family') . ': ' . getFamilyName($prow);
          } elseif ($prow['sourceID'] != null) {
            $sourcetext = $prow['title'] ? uiTextSnippet('source') . ": {$prow['title']}" : uiTextSnippet('source') . ": {$prow['sourceID']}";
            $hslinktext .= "<a href=\"sourcesShowSource.php?sourceID={$prow['sourceID']}\">$sourcetext";
          } elseif ($prow['repoID'] != null) {
            $repotext = $prow['reponame'] ? uiTextSnippet('repository') . ": {$prow['reponame']}" : uiTextSnippet('repository') . ": {$prow['repoID']}";
            $hslinktext .= "<a href=\"repositoriesShowItem.php?repoID={$prow['repoID']}\">$repotext";
          } else {
            $hslinktext .= "<a href=\"placesearch.php?psearch={$prow['personID']}\">{$prow['personID']}";
          }
          $hslinktext .= "</a>$hstext\n<br>\n";
        }
        tng_free_result($presult);

        $description = $hs['description'];
        $notes = nl2br($hs['notes']);

        $body .= "<tr>\n";
        $body .= "<td style='width: {$thumbmaxw}px'>";
        $hs['allow_living'] = $noneliving;
        $hs['allow_private'] = $noneprivate;
        $imgsrc = getSmallPhoto($hs);
        $href = getMediaHREF($hs, 3);

        if ($imgsrc) {
          $body .= "<div class=\"media-img\"><div class=\"media-prev\" id=\"prev{$hs['mediaID']}\" style='display: none'></div></div>\n";
          $body .= "<a href=\"$href\"";
          if ($gotImageJpeg && isPhoto($hs) && checkMediaFileSize("$rootpath$usefolder/{$hs['path']}")) {
            $body .= " class=\"media-preview\" id=\"img-{$hs['mediaID']}-0-" . urlencode("$usefolder/{$hs['path']}") . '"';
          }
          $body .= ">$imgsrc</a>\n";
        } else {
          $body .= '&nbsp;';
        }

        $body .= "</td>\n";

        $body .= "<td><span><a href=\"$href\">{$hs['description']}</a><br>$notes&nbsp;</span></td>\n";
        $body .= "<td><span>{$hs['status']}&nbsp;</span></td>\n";
        $body .= '<td><span>' . nl2br($hs['plot']);
        if ($hs['latitude'] || $hs['longitude']) {
          if ($hs['plot']) {
            $body .= '<br>';
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
      $query = 'SELECT * FROM people WHERE burialplace = "' . addslashes($cemetery['place']) . '" ORDER BY lastname, firstname';
      $result = tng_query($query);
      if (tng_num_rows($result)) {
        $body .= "<br><div>\n";
        $body .= '<h4>' . uiTextSnippet('allburials') . "</h4>\n";

        $body .= "<table class='table table-sm table-hover'>\n";
        $body .= "<thead class='thead-default'>\n";
        $body .= "<tr>\n";
        $body .= "<th></th>\n";
        $body .= '<th>' . uiTextSnippet('lastfirst') . "</th>\n";
        $body .= '<th>' . uiTextSnippet('buried') . "</th>\n";
        $body .= "</tr>\n";
        $body .= "</thead>\n";

        $i = 1;
        while ($row = tng_fetch_assoc($result)) {
          $row['allow_living'] = 1;

          $rights = determineLivingPrivateRights($row);
          $row['allow_living'] = $rights['living'];
          $row['allow_private'] = $rights['private'];

          $name = getNameRev($row);
          $body .= "<tr>\n";
          $body .= "<td>$i</td>\n";
          $body .= "<td>\n";
          $body .= "<a tabindex='0' class='btn btn-sm btn-outline-primary person-popover' role='button' data-toggle='popover' data-placement='bottom' data-person-id='{$row['personID']}'>$name</a>\n";
          $body .= "</td>\n";

          $burialPlace = $row['burialplace'] ? buildSilentPlaceLink($row['burialplace']) : '';
          
          $deathdate = $row['burialdate'] ? $row['burialdate'] : $row['deathdate'];
          if ($row['burialdate']) {
            $abbrev = uiTextSnippet('burialabbr', ['html' => 'strong']);
          } elseif ($row['deathdate']) {
            $abbrev = uiTextSnippet('deathabbr', ['html' => 'strong']);
          }
          $burialdate = $deathdate ? "$abbrev " . displayDate($deathdate) : '';

          $body .= "<td>$burialdate<br>$burialPlace</td>\n";
          $i++;
        }
        $body .= "</table>\n";
        $body .= "</div>\n";
      }
      tng_free_result($result);
    }
    echo $body;
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <?php if ($map['key'] === true) { ?>
    <script src='https://maps.googleapis.com/maps/api/js?key=AIzaSyAlWTL2QZDQv9BWXBvCwdAuhq1Lak8jSwU&amp;<?php echo uiTextSnippet('localize'); ?>'></script>
  <?php 
  }
  if ($map['key'] === true && $map['pins']) {
    tng_map_pins();
  }
  ?>
  <script src="js/search.js"></script>
  <script>
   $(function () {
        $('[data-toggle="popover"]').popover();
    });
  </script>
</body>
</html>