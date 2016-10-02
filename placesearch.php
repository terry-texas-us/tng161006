<?php
$needMap = true;
require 'tng_begin.php';

require $subroot . 'mapconfig.php';
require 'places.inc.php';

if (!$psearch) {
  exit;
}
require 'personlib.php';

set_time_limit(0);
$psearch = preg_replace('/[<>{};!=]/', '', $psearch);

$psearchns = $psearch;
$psearch = addslashes($psearch);

$querystring = $psearchns;
$cutoffstr = "personID = \"$psearch\"";
$whatsnew = 0;

if ($order) {
  $_SESSION['tng_psearch_order'] = $order;
} else {
  $order = isset($_SESSION['tng_psearch_order']) ? $_SESSION['tng_psearch_order'] : 'name';
}
if ($order != 'name' && $order != 'nameup' && $order != 'date' && $order != 'dateup') {
  $order = '';
}
$datesort = 'dateup';
$namesort = 'name';
$orderloc = strpos($_SERVER['QUERY_STRING'], '&amp;order=');
$currargs = $orderloc > 0 ? substr($_SERVER['QUERY_STRING'], 0, $orderloc) : $_SERVER['QUERY_STRING'];

if ($order == 'name') {
  $namesort = "<a href='placesearch.php?$currargs&amp;order=nameup'>xxx <img src=\"img/tng_sort_desc.gif\" width=\"15\" height=\"8\" alt=''></a>";
} else {
  $namesort = "<a href='placesearch.php?$currargs&amp;order=name'>xxx <img src=\"img/tng_sort_asc.gif\" width=\"15\" height=\"8\" alt=''></a>";
}
if ($order == 'date') {
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

$snippets = ['latitude' => uiTextSnippet('latitude'), 'longitude' => uiTextSnippet('longitude')];

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle($psearchns);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $sessionCharset); ?>
<body id='public'>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();

    $photostr = showSmallPhoto($psearch, $psearch, 1, 0);

    echo tng_DrawHeading($photostr, $psearchns, '');

    $pquery = "SELECT placelevel, latitude, longitude, zoom, notes FROM places WHERE place = '$psearch'";
    $presult = tng_query($pquery) or die(uiTextSnippet('cannotexecutequery') . ": $pquery");

    $rightbranch = 1;
    echo buildPlaceMenu('place', $psearch);
    echo "<br>\n";

    $altstr = ', altdescription, altnotes';
    $mapdrawn = false;
    while ($prow = tng_fetch_assoc($presult)) {
      if ($prow['latitude'] || $prow['longitude']) {
        echo "<div class='card card-block'>";
        echo "<div class='row'>\n";
        echo "<div class='col-md-8'>\n";
        if (($prow['latitude'] || $prow['longitude']) && $map['key'] && !$mapdrawn) {
          echo "<div class='map-place-search' id='map' style='width: {$map['hstw']}; height: {$map['hsth']};'></div>\n";
          $usedplaces = [];
          $mapdrawn = true;
        }
        echo "</div>\n";
        echo "<div class='col-md-4'>\n";
        if ($map['key']) {
          $lat = $prow['latitude'];
          $long = $prow['longitude'];
          $zoom = $prow['zoom'] ? $prow['zoom'] : 10;
          $placelevel = $prow['placelevel'] ? $prow['placelevel'] : '0';
          $placeleveltext = $placelevel != '0' ? uiTextSnippet('level' . $placelevel) : '';
          $codedplace = htmlspecialchars(str_replace($banish, $banreplace, $psearchns), ENT_QUOTES, $sessionCharset);
          $codednotes = $prow['notes'] ? '<br>' . tng_real_escape_string(uiTextSnippet('notes') . ': ' . $prow['notes']) : '';

          $codednotes .= "<br><br><a href=\"https://maps.google.com/maps?f=q&amp;" . uiTextSnippet('localize') . "&amp;oe=$sessionCharset&amp;daddr=$lat,$long($codedplace)&amp;z=$zoom&amp;om=1&amp;iwloc=addr\" target=\"_blank\">" . uiTextSnippet('getdirections') . '</a>' . uiTextSnippet('directionsto') . " $codedplace";

          if ($lat && $long) {
            $uniqueplace = $psearch . $lat . $long;
            if ($map['showallpins'] || !in_array($uniqueplace, $usedplaces)) {
              $usedplaces[] = $uniqueplace;
              $locations2map[$l2mCount] = ['placelevel' => $placelevel, 'lat' => $lat, 'long' => $long, 'zoom' => $zoom, 'htmlcontent' => "<div class=\"mapballoon\">$placeleveltext<br>$codedplace$codednotes</div>"];
              $l2mCount++;
            }
          }
          echo '<a href="https://maps.google.com/maps?f=q&amp;' . uiTextSnippet('localize') . "&amp;oe=$sessionCharset&amp;daddr=$lat,$long($codedplace)&amp;z=12&amp;om=1&amp;iwloc=addr\" target='_blank'>\n";
//          echo "<img src=\"google_marker.php?image=$pins[$placelevel]&amp;text=$l2mCount\" alt=''>\n";
          echo "</a>\n";
          
          $map['pins']++;
          echo "<strong>" . uiTextSnippet('placelevel') . ":</strong> $placeleveltext<br>\n";
          echo "<strong>{$snippets['latitude']}:</strong> {$prow['latitude']}<br>\n";
          echo "<strong>{$snippets['longitude']}:</strong> {$prow['longitude']}\n";
        } elseif ($prow['latitude'] || $prow['longitude']) {
          echo "<strong>{$snippets['latitude']}:</strong> {$prow['latitude']}<br>\n";
          echo "<strong>{$snippets['longitude']}:</strong> {$prow['longitude']}\n";
        }
        echo "</div>\n";
        echo "</div>\n"; // .row
        echo "</div>\n"; // .card.card-block
      }
      if ($prow['notes']) {
        echo '<span><strong>' . uiTextSnippet('notes') . ':</strong> ' . nl2br($prow['notes']) . '</span><br>';
      }
    }
    
    tng_free_result($presult);

    $placemedia = getMedia($psearch, 'L');
    $placealbums = getAlbums($psearch, 'L');
    $media = doMediaSection($psearch, $placemedia, $placealbums);
    if ($media) {
      echo "<br>\n<div class='card'>\n";
      echo '<h4>' . uiTextSnippet('media') . '</h4>';
      echo "$media\n";
      echo "</div>\n";
    }
    $pquery = "SELECT cemname, city, county, state, country, cemeteryID FROM cemeteries WHERE place = \"$psearch\"";
    $presult = tng_query($pquery) or die(uiTextSnippet('cannotexecutequery') . ": $pquery");
    $cemdata = '';
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
      echo '<h4>' . uiTextSnippet('cemeteries') . '</h4>';
      echo "<table class='table'>\n";
      echo "<tr>\n";
      echo "<td></td>\n";
      echo '<td>' . uiTextSnippet('name') . "</td>\n";
      echo '<td>' . uiTextSnippet('location') . "</td>\n";
      echo "</tr>\n";
      echo "$cemdata</table>\n";
      echo "</div>\n";
    }
    $successcount = 0;

    //then loop over events like anniversaries
    $stdevents = ['birth', 'altbirth', 'death', 'burial'];
    $displaymsgs = ['birth' => uiTextSnippet('birth'), 'altbirth' => uiTextSnippet('christened'), 'death' => uiTextSnippet('died'), 'burial' => uiTextSnippet('buried')];
    //$dontdo = ['ADDR', 'BIRT', 'CHR', 'DEAT', 'BURI', 'NAME', 'NICK', 'TITL', 'NSFX'];
    if ($ldsOK) {
      array_push($stdevents, 'endl', 'init', 'conf', 'bapt');
      $displaymsgs['endl'] = uiTextSnippet('endowedlds');
      $displaymsgs['init'] = uiTextSnippet('initlds');
      $displaymsgs['conf'] = uiTextSnippet('conflds');
      $displaymsgs['bapt'] = uiTextSnippet('baptizedlds');
    }
    $successcount += processPlaceEvents('I', $stdevents, $displaymsgs);

    $stdevents = ['marr', 'div'];
    $displaymsgs = ['marr' => uiTextSnippet('married'), 'div' => uiTextSnippet('divorced')];
    if ($ldsOK) {
      array_push($stdevents, 'seal');
      $displaymsgs['seal'] = uiTextSnippet('sealedslds');
    }
    $successcount += processPlaceEvents('F', $stdevents, $displaymsgs);

    if (!$successcount) {
      echo '<p>' . uiTextSnippet('noresults') . '.</p>';
    }
    echo $publicFooterSection->build();
    ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <?php if ($map['key']) { ?>
    <script src='https://maps.googleapis.com/maps/api/js?key=AIzaSyAlWTL2QZDQv9BWXBvCwdAuhq1Lak8jSwU&amp;<?php echo uiTextSnippet('localize'); ?>'></script>
  <?php
  }
  if ($map['key'] && $map['pins']) {
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