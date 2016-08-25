<?php
$needMap = true;
require 'tng_begin.php';

require $subroot . 'mapconfig.php';
require 'functions.php';

$flags['imgprev'] = true;

if (!$thumbmaxw) {
  $thumbmaxw = 80;
}
$max_pages = 5;
if (!isset($max_cemeteries)) {
  $max_cemeteries = 5;
}
$city = preg_replace("/[<>{};!=]/", '', $city);
$county = preg_replace("/[<>{};!=]/", '', $county);
$state = preg_replace("/[<>{};!=]/", '', $state);
$country = preg_replace("/[<>{};!=]/", '', $country);

$city = addslashes($city);
$county = addslashes($county);
$state = addslashes($state);
$country = addslashes($country);

if ($cemeteryID) {
  $subquery = "WHERE cemeteryID = '$cemeteryID'";
} else {
  if ($cemoffset) {
    $cemoffsetplus = $cemoffset + 1;
    $cemnewoffset = "$cemoffset, ";
  } else {
    $cemoffsetplus = 1;
    $cemnewoffset = "";
    $page = 1;
  }
  if ($country) {
    $subquery = "WHERE country = '$country' ";
  } else {
    $subquery = "";
  }
  if ($state) {
    $subquery .= "AND state = '$state' ";
  }
  if ($county) {
    $subquery .= "AND county = '$county'";
  }
  if ($city) {
    $subquery .= "AND city = '$city'";
  }
}
if ($subquery) {
  $query = "SELECT * FROM $cemeteries_table $subquery ORDER BY country, state, county, city, cemname LIMIT $cemnewoffset" . $max_cemeteries;
  $cemresult = tng_query($query);

  $numrows = tng_num_rows($cemresult);

  if ($numrows == $max_cemeteries || $cemoffsetplus > 1) {
    $query = "SELECT count(cemeteryID) as ccount FROM $cemeteries_table $subquery";
    $result2 = tng_query($query);
    $row = tng_fetch_assoc($result2);
    tng_free_result($result2);
    $totrows = $row['ccount'];
  } else {
    $totrows = $numrows;
  }
} else {
  $cemresult = "";
}
if (!$cemeteryID) {
  $toppagenav = buildSearchResultPagination($totrows, "headstones.php?country=$country&amp;state=$state&amp;county=$county&amp;city=$city&amp;cemoffset", $max_cemeteries, $max_pages);
  $tngpage = 1;
}
if (!$tngpage && !$cemeteryID && $cemresult && tng_num_rows($cemresult) == 1) {
  $cemetery = tng_fetch_assoc($cemresult);
  tng_free_result($cemresult);
  header("Location: cemeteriesShowCemetery.php?cemeteryID={$cemetery['cemeteryID']}");
  exit;
}
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $page = 1;
}
$country = stripslashes($country);
$state = stripslashes($state);
$county = stripslashes($county);
$city = stripslashes($city);
$location = $city;

if ($location && $county) {
  $location .= ", $county";
} else {
  $location = $county;
}
if ($location && $state) {
  $location .= ", $state";
} else {
  $location = $state;
}
if ($location && $country) {
  $location .= ", $country";
} else {
  $location = $country;
}
$titlestr = uiTextSnippet('cemeteriesheadstones');
if ($location) {
  $titlestr .= " " . uiTextSnippet('in') . " $location";
}
$logstring = "<a href=\"headstones.php?country=$country&amp;state=$state&amp;county=$county&amp;city=$city&amp;cemeteryID=$cemeteryID\">$titlestr</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('cemeteriesheadstones'));
$headerLabel = uiTextSnippet('cemeteriesheadstones');
if ($location) {
  $headerLabel .= " " . uiTextSnippet('in') . " $location";
}
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/headstone.svg'><?php echo $headerLabel; ?></h2>
    <br clear='all'>
    <?php
    $hiddenfields[] = ['name' => 'country', 'value' => $country];
    $hiddenfields[] = ['name' => 'state', 'value' => $state];
    $hiddenfields[] = ['name' => 'county', 'value' => $county];

    $body = "";
    $cemcount = 0;
    $gotImageJpeg = function_exists(imageJpeg);
    while (!$subquery || $cemetery = tng_fetch_assoc($cemresult)) {
      if ($cemcount) {
        $body .= "<br>\n";
      }
      $body .= "<div class=\"titlebox\">\n";
      $thiscem = $subquery ? $cemetery['cemeteryID'] : "";
      $query = "SELECT DISTINCT $media_table.mediaID, description, notes, path, thumbpath, status, plot, showmap, usecollfolder, form, mediatypeID, abspath, newwindow
        FROM $media_table LEFT JOIN $medialinks_table on $media_table.mediaID = $medialinks_table.mediaID
        WHERE mediatypeID = 'headstones' AND cemeteryID = '$thiscem' ORDER BY description LIMIT $newoffset" . $maxsearchresults;
      if (!$subquery) {
        $cemetery = [];
        $cemetery['cemname'] = uiTextSnippet('nocemetery');
        $subquery = "done";
      }
      $hsresult = tng_query($query);

      $numrows = tng_num_rows($hsresult);
      if ($numrows == $maxsearchresults || $offsetplus > 1) {
        $query = "SELECT count(DISTINCT $media_table.mediaID) as hscount FROM $media_table LEFT JOIN $medialinks_table on $media_table.mediaID = $medialinks_table.mediaID WHERE mediatypeID = 'headstones' AND cemeteryID = '$thiscem'";
        $result2 = tng_query($query);
        $row = tng_fetch_assoc($result2);
        $totrows = $row['hscount'];
      } else {
        $totrows = $numrows;
      }

      $body .= "<div><h4>\n";
      if ($cemetery['cemname'] == uiTextSnippet('nocemetery')) {
        $location = $cemetery['cemname'];
      } else {
        $location = "<a href=\"cemeteriesShowCemetery.php?cemeteryID={$cemetery['cemeteryID']}\">" . $cemetery['cemname'];
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
        $location .= "</a>";
      }

      if ($map['key']) {
        $lat = $cemetery['latitude'];
        $long = $cemetery['longitude'];
        $zoom = $cemetery['zoom'] ? $cemetery['zoom'] : 10;
        $pinplacelevel = $pinplacelevel2;

        // if we have one, add it
        if ($lat && $long) {
          $cemeteryplace = "{$cemetery['city']}, {$cemetery['county']}, {$cemetery['state']}, {$cemetery['country']}";
          $localballooncemeteryname = htmlspecialchars($cemetery['cemname'], ENT_QUOTES, $session_charset);
          $localballooncemeteryplace = htmlspecialchars($cemeteryplace, ENT_QUOTES, $session_charset);
          $remoteballoontext = htmlspecialchars(str_replace($banish, $banreplace, "{$cemetery['cemname']}, $cemeteryplace"), ENT_QUOTES, $session_charset);
          $codednotes = $cemetery['notes'] ? "<br><br>" . tng_real_escape_string(uiTextSnippet('notes') . ": " . $cemetery['notes']) : "";
          $codednotes .= "<br><br><a href=\"{$http}://maps.google.com/maps?f=q" . uiTextSnippet('glang') . "$mcharsetstr&amp;daddr=$lat,$long($remoteballoontext)\" target=\"_blank\">" .
                  uiTextSnippet('getdirections') . "</a>" . uiTextSnippet('directionsto') . " $localballooncemeteryname";
          $locations2map[$l2mCount] = [
            "zoom" => $zoom,
            "lat" => $lat,
            "long" => $long,
            "pinplacelevel" => $pinplacelevel,
            "place" => $cemeteryplace,
            "htmlcontent" => "<div class=\"mapballoon\"><a href=\"cemeteriesShowCemetery.php?cemeteryID={$cemetery['cemeteryID']}\">$localballooncemeteryname</a><br>$localballooncemeteryplace$codednotes</div>"
          ];
          $l2mCount++;
          $body .= "<a href=\"https://maps.google.com/maps?f=q" . uiTextSnippet('glang') . "$mcharsetstr&amp;daddr=$lat,$long($remoteballoontext)&amp;z=$zoom&amp;om=1&amp;iwloc=addr\" target=\"_blank\">\n";
          $body .= "<img src=\"google_marker.php?image=$pinplacelevel2.png&amp;text=$l2mCount\" alt='' style=\"padding-right:5px\">\n";
          $body .= "</a>";
          $map['pins']++;
        }
      }

      $body .= $location;
      $body .= "</h4>";
      $pagenav = buildSearchResultPagination($totrows, "headstones.php?cemeteryID={$cemetery['cemeteryID']}&amp;offset", $maxsearchresults, 5);
      $body .= "<p>$pagenav</p>";
      $body .= "</div>\n";

      $body .= "<table class=\"table table-sm table-striped\">\n";
      $body .= "<tr><th>" . uiTextSnippet('thumb') . "</th>";
      $body .= "<th>" . uiTextSnippet('description') . "</th>";
      $body .= "<th>" . uiTextSnippet('status') . "</th>";
      $body .= "<th>" . uiTextSnippet('location') . "</th>";
      $body .= "<th>" . uiTextSnippet('name') . " (" . uiTextSnippet('diedburied') . ")</th></tr>";

      while ($hs = tng_fetch_assoc($hsresult)) {
        $mediatypeID = $hs['mediatypeID'];
        $usefolder = $hs['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;

        $status = $hs['status'];
        $hs['cemeteryID'] = $cemetery['cemeteryID'];
        if ($status && uiTextSnippet($status)) {
          $hs['status'] = uiTextSnippet($status);
        }

        $query = "SELECT medialinkID, $medialinks_table.personID as personID, people.personID as personID2, familyID, people.living as living, people.private as private, people.branch as branch, husband, wife, people.lastname as lastname, people.lnprefix as lnprefix, people.firstname as firstname, people.prefix as prefix, people.suffix as suffix, nameorder, $sources_table.title, $sources_table.sourceID, $repositories_table.repoID,reponame, deathdate, burialdate, linktype
          FROM ($medialinks_table)
          LEFT JOIN $people_table AS people ON ($medialinks_table.personID = people.personID)
          LEFT JOIN $families_table ON ($medialinks_table.personID = $families_table.familyID)
          LEFT JOIN $sources_table ON ($medialinks_table.personID = $sources_table.sourceID)
          LEFT JOIN $repositories_table ON ($medialinks_table.personID = $repositories_table.repoID)
          WHERE mediaID = \"{$hs['mediaID']}\" ORDER BY lastname, lnprefix, firstname, $medialinks_table.personID";

        $presult = tng_query($query);
        $hslinktext = "";
        while ($prow = tng_fetch_assoc($presult)) {
          $prights = determineLivingPrivateRights($prow);
          $prow['allow_living'] = $prights['living'];
          $prow['allow_private'] = $prights['private'];

          $hstext = "";
          if ($prow['personID2'] != null) {
            $hslinktext .= "<a href=\"peopleShowPerson.php?personID={$prow['personID2']}\">";
            $hslinktext .= getName($prow);
            $deathdate = $prow['deathdate'] ? $prow['deathdate'] : $prow['burialdate'];
            if ($prow['deathdate']) {
              $abbrev = uiTextSnippet('deathabbr');
            } elseif ($prow['burialdate']) {
              $abbrev = uiTextSnippet('burialabbr');
            }
            $hstext = $deathdate ? " ($abbrev " . displayDate($deathdate) . ")" : "";
          } elseif ($prow['familyID'] != null) {
            $hslinktext .= "<a href=\"familiesShowFamily.php?familyID={$prow['familyID']}<br>}\">" . uiTextSnippet('family') . ": " . getFamilyName($prow);
          } elseif ($prow['sourceID'] != null) {
            $sourcetext = $prow['title'] ? uiTextSnippet('source') . ": {$prow['title']}" : uiTextSnippet('source') . ": {$prow['sourceID']}";
            $hslinktext .= "<a href=\"showsource.php?sourceID={$prow['sourceID']}\">$sourcetext";
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
        $notes = $hs['notes'];

        $body .= "<tr><td style=\"width:$thumbmaxw" . "px\">";
        $hs['mediatypeID'] = "headstones";
        $hs['allow_living'] = 1;
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

        $body .= "<td><span><a href=\"$href\">{$hs['description']}</a><br>{$hs['notes']}&nbsp;</span></td>\n";
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
      $cemcount++;
      $body .= "</table>\n";
      if ($pagenav) {
        $body .= "<br>$pagenav";
      }
      $body .= "</div>\n<br>\n";

      if ($subquery == "done") {
        break;
      }
    }
    if ($map['key'] && $map['pins']) {
      echo "<div id='map' style='width: {$map['hstw']}; height: {$map['hsth']}; margin-bottom: 20px;' class='rounded10'></div>\n";
    }
    if ($toppagenav) {
      echo "<p>$toppagenav</p>\n$body\n<p>$toppagenav</p>";
    } else {
      echo $body;
    }
    if ($cemresult) {
      tng_free_result($cemresult);
    }
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
