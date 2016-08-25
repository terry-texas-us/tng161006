<?php
$needMap = true;
require 'tng_begin.php';

require $subroot . 'mapconfig.php';
require 'places.php';

if (!$psearch) {
  exit;
}
require 'personlib.php';

set_time_limit(0);
$psearch = preg_replace("/[<>{};!=]/", '', $psearch);

$psearchns = $psearch;
$psearch = addslashes($psearch);

$querystring = $psearchns;
$cutoffstr = "personID = \"$psearch\"";
$whatsnew = 0;

if ($order) {
  $_SESSION['tng_psearch_order'] = $order;
} else {
  $order = isset($_SESSION['tng_psearch_order']) ? $_SESSION['tng_psearch_order'] : "name";
}
if ($order != "name" && $order != "nameup" && $order != "date" && $order != "dateup") {
  $order = "";
}
$datesort = "dateup";
$namesort = "name";
$orderloc = strpos($_SERVER['QUERY_STRING'], "&amp;order=");
$currargs = $orderloc > 0 ? substr($_SERVER['QUERY_STRING'], 0, $orderloc) : $_SERVER['QUERY_STRING'];

if ($order == "name") {
  $namesort = "<a href='placesearch.php?$currargs&amp;order=nameup'>xxx <img src=\"img/tng_sort_desc.gif\" width=\"15\" height=\"8\" alt=''></a>";
} else {
  $namesort = "<a href='placesearch.php?$currargs&amp;order=name'>xxx <img src=\"img/tng_sort_asc.gif\" width=\"15\" height=\"8\" alt=''></a>";
}
if ($order == "date") {
  $datesort = "<a href='placesearch.php?$currargs&amp;order=dateup'>yyy <img src=\"img/tng_sort_desc.gif\" width=\"15\" height=\"8\" alt=''></a>";
} else {
  $datesort = "<a href='placesearch.php?$currargs&amp;order=date'>yyy <img src=\"img/tng_sort_asc.gif\" width=\"15\" height=\"8\" alt=''></a>";
}
$tngconfig['istart'] = 0;

$ldsOK = determineLDSRights();

$logstring = "<a href=\"placesearch.php?psearch=$psearchns\">" . uiTextSnippet('searchresults') . " $querystring</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle($psearchns);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();

    $photostr = showSmallPhoto($psearch, $psearch, 1, 0);

    echo tng_DrawHeading($photostr, $psearchns, "");

    $pquery = "SELECT placelevel, latitude, longitude, zoom, notes FROM $places_table WHERE place = '$psearch'";
    $presult = tng_query($pquery) or die(uiTextSnippet('cannotexecutequery') . ": $pquery");

    $rightbranch = 1;
    echo buildPlaceMenu('place', $psearch);
    echo "<br>\n";

    $altstr = ", altdescription, altnotes";
    $mapdrawn = false;
    while ($prow = tng_fetch_assoc($presult)) {
      if ($prow['notes'] || $prow['latitude'] || $prow['longitude']) {
        if (($prow['latitude'] || $prow['longitude']) && $map['key'] && !$mapdrawn) {
          echo "<br><div id='map' style=\"width: {$map['hstw']}; height: {$map['hsth']}; margin-bottom:20px;\" class=\"rounded10\"></div>\n";
          $usedplaces = [];
          $mapdrawn = true;
        }
        if ($prow['notes']) {
          echo "<span><strong>" . uiTextSnippet('notes') . ":</strong> " . nl2br($prow['notes']) . "</span><br>";
        }
        if ($map['key']) {
          $lat = $prow['latitude'];
          $long = $prow['longitude'];
          $zoom = $prow['zoom'] ? $prow['zoom'] : 10;
          $placelevel = $prow['placelevel'] ? $prow['placelevel'] : "0";
          $pinplacelevel = ${"pinplacelevel" . $placelevel};
          $placeleveltext = $placelevel != "0" ? uiTextSnippet('level' . $placelevel) . "&nbsp;:&nbsp;" : "";
          $codedplace = htmlspecialchars(str_replace($banish, $banreplace, $psearchns), ENT_QUOTES, $session_charset);
          $codednotes = $prow['notes'] ? "<br><br>" . tng_real_escape_string(uiTextSnippet('notes') . ": " . $prow['notes']) : "";
          // add external link to Google Maps for Directions in the balloon
          $codednotes .= "<br><br><a href=\"{$http}://maps.google.com/maps?f=q" . uiTextSnippet('glang') .
                  "$mcharsetstr&amp;daddr=$lat,$long($codedplace)&amp;z=$zoom&amp;om=1&amp;iwloc=addr\" target=\"_blank\">" . uiTextSnippet('getdirections') . "</a>" . uiTextSnippet('directionsto') . " $codedplace";
          if ($lat && $long) {
            $uniqueplace = $psearch . $lat . $long;
            if ($map['showallpins'] || !in_array($uniqueplace, $usedplaces)) {
              $usedplaces[] = $uniqueplace;
              $locations2map[$l2mCount] = ["pinplacelevel" => $pinplacelevel, "lat" => $lat, "long" => $long, "zoom" => $zoom, "htmlcontent" => "<div class=\"mapballoon\">$placeleveltext<br>$codedplace$codednotes</div>"];
              $l2mCount++;
            }
          }
          echo "<a href=\"{$http}://maps.google.com/maps?f=q" . uiTextSnippet('glang') . "$mcharsetstr&amp;daddr=$lat,$long($codedplace)&amp;z=12&amp;om=1&amp;iwloc=addr\" target=\"_blank\">\n";
          echo "<img src=\"google_marker.php?image=$pinplacelevel.png&amp;text=$l2mCount\" alt=''></a><strong>$placeleveltext</strong><span><strong>" .
                  uiTextSnippet('latitude') . ":</strong> {$prow['latitude']}, <strong>" . uiTextSnippet('longitude') . ":</strong> {$prow['longitude']}</span><br><br>";
          $map['pins']++;
        } elseif ($prow['latitude'] || $prow['longitude']) {
          echo "<span><strong>" . uiTextSnippet('latitude') . ":</strong> {$prow['latitude']}, <strong>" . uiTextSnippet('longitude') . ":</strong> {$prow['longitude']}</span><br><br>";
        }
      }
    }
    
    tng_free_result($presult);

    $placemedia = getMedia($psearch, 'L');
    $placealbums = getAlbums($psearch, 'L');
    $media = doMediaSection($psearch, $placemedia, $placealbums);
    if ($media) {
      echo "<br>\n<div class=\"titlebox\">\n";
      echo "<h4>" . uiTextSnippet('media') . "</h4>";
      echo "$media\n";
      echo "</div>\n";
    }
    $pquery = "SELECT cemname, city, county, state, country, cemeteryID FROM $cemeteries_table WHERE place = \"$psearch\"";
    $presult = tng_query($pquery) or die(uiTextSnippet('cannotexecutequery') . ": $pquery");
    $cemdata = "";
    $i = 1;
    while ($prow = tng_fetch_assoc($presult)) {
      $country = stripslashes($prow['country']);
      $state = stripslashes($prow['state']);
      $county = stripslashes($prow['county']);
      $city = stripslashes($prow['city']);
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
      $cemdata .= "<tr><td>$i.</td><td><a href=\"cemeteriesShowCemetery.php?cemeteryID={$prow['cemeteryID']}\">{$prow['cemname']}</a></td><td>$location</td></tr>\n";
      $i++;
    }
    if ($cemdata) {
      echo "<br>\n";
      echo "<div>\n";
      echo "<h4>" . uiTextSnippet('cemeteries') . "</h4>";
      echo "<table class='table'>\n";
      echo "<tr>\n";
      echo "<td></td>\n";
      echo "<td>" . uiTextSnippet('name') . "</td>\n";
      echo "<td>" . uiTextSnippet('location') . "</td>\n";
      echo "</tr>\n";
      echo "$cemdata</table>\n";
      echo "</div>\n";
    }
    $successcount = 0;

    //then loop over events like anniversaries
    $stdevents = ["birth", "altbirth", "death", "burial"];
    $displaymsgs = ["birth" => uiTextSnippet('birth'), "altbirth" => uiTextSnippet('christened'), "death" => uiTextSnippet('died'), "burial" => uiTextSnippet('buried')];
    //$dontdo = array("ADDR","BIRT","CHR","DEAT","BURI","NAME","NICK","TITL","NSFX");
    if ($ldsOK) {
      array_push($stdevents, "endl", "init", "conf", "bapt");
      $displaymsgs['endl'] = uiTextSnippet('endowedlds');
      $displaymsgs['init'] = uiTextSnippet('initlds');
      $displaymsgs['conf'] = uiTextSnippet('conflds');
      $displaymsgs['bapt'] = uiTextSnippet('baptizedlds');
    }
    $successcount += processPlaceEvents('I', $stdevents, $displaymsgs);

    $stdevents = ["marr", "div"];
    $displaymsgs = ["marr" => uiTextSnippet('married'), "div" => uiTextSnippet('divorced')];
    if ($ldsOK) {
      array_push($stdevents, "seal");
      $displaymsgs['seal'] = uiTextSnippet('sealedslds');
    }
    $successcount += processPlaceEvents('F', $stdevents, $displaymsgs);

    if (!$successcount) {
      echo "<p>" . uiTextSnippet('noresults') . ".</p>";
    }
    echo $publicFooterSection->build();
    ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <?php
  if ($map['key']) {
  ?>
    <script src='https://maps.googleapis.com/maps/api/js?language="<?php echo uiTextSnippet('glang'); ?>"'></script>
  <?php
  }
  if ($map['key'] && $map['pins']) {
    tng_map_pins();
  }
  ?>
</body>
</html>