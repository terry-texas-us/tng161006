<?php
$needMap = true;
include("tng_begin.php");

include($subroot . "mapconfig.php");

if (!$psearch) {
  exit;
}
include("personlib.php");

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

$treequery = "SELECT count(gedcom) as treecount FROM $trees_table";
$treeresult = tng_query($treequery);
$treerow = tng_fetch_assoc($treeresult);
$numtrees = $treerow['treecount'];
tng_free_result($treeresult);

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

function processEvents($prefix, $stdevents, $displaymsgs) {
  global $eventtypes_table;
  global $tree;
  global $people_table;
  global $families_table;
  global $trees_table;
  global $offset;
  global $page;
  global $psearch;
  global $maxsearchresults;
  global $psearchns;
  global $urlstring;
  global $events_table;
  global $order;
  global $namesort;
  global $datesort;

  $successcount = 0;
  $allwhere = "";
  if ($prefix == 'I') {
    $table = $people_table;
    $peoplejoin1 = $peoplejoin2 = "";
    $idfield = "personID";
    $idtext = "personid";
    $namefield = "lastfirst";
  } elseif ($prefix == 'F') {
    $table = $families_table;
    $peoplejoin1 = " LEFT JOIN $people_table as p1 ON $families_table.gedcom = p1.gedcom AND p1.personID = $families_table.husband";
    $peoplejoin2 = " LEFT JOIN $people_table as p2 ON $families_table.gedcom = p2.gedcom AND p2.personID = $families_table.wife";
    $idfield = "familyID";
    $idtext = "familyid";
    $namefield = "family";
  }
  $allwhere .= "$table.gedcom = $trees_table.gedcom";
  if ($tree) {
    $allwhere .= " AND $table.gedcom=\"$tree\"";
  }
  $more = getLivingPrivateRestrictions($table, false, false);
  if ($more) {
    if ($allwhere) {
      $allwhere .= " AND ";
    }
    $allwhere .= $more;
  }
  $max_browsesearch_pages = 5;
  if ($offset) {
    $offsetplus = $offset + 1;
    $newoffset = "$offset, ";
  } else {
    $offsetplus = 1;
    $newoffset = "";
    $page = 1;
  }
  $tngevents = $stdevents;
  $custevents = array();
  $query = "SELECT tag, eventtypeID, display FROM $eventtypes_table
    WHERE keep=\"1\" AND type=\"$prefix\" ORDER BY display";
  $result = tng_query($query);
  while ($row = tng_fetch_assoc($result)) {
    $eventtypeID = $row['eventtypeID'];
    array_push($tngevents, $eventtypeID);
    array_push($custevents, $eventtypeID);
    $displaymsgs[$eventtypeID] = getEventDisplay($row['display']);
  }
  tng_free_result($result);

  foreach ($tngevents as $tngevent) {
    $eventsjoin = "";
    $allwhere2 = "";
    $placetxt = $displaymsgs[$tngevent];

    if (in_array($tngevent, $custevents)) {
      $eventsjoin = ", $events_table";
      $allwhere2 .= " AND $table.$idfield = $events_table.persfamID AND $table.gedcom = $events_table.gedcom AND eventtypeID = \"$tngevent\" AND parenttag = \"\"";
      $tngevent = "event";
    }
    $datefield = $tngevent . "date";
    $datefieldtr = $tngevent . "datetr";
    $place = $tngevent . "place";
    $allwhere2 .= " AND $place = '$psearch'";

    if ($prefix == 'F') {
      if ($order == "name") {
        $orderstr = "p1lastname, p2lastname, $datefieldtr";
      } elseif ($order == "nameup") {
        $orderstr = "p1lastname DESC, p2lastname DESC, $datefieldtr DESC";
      } elseif ($order == "date") {
        $orderstr = "$datefieldtr, p1lastname, p2lastname";
      } else {
        $orderstr = "$datefieldtr DESC, p1lastname DESC, p2lastname DESC";
      }
      $query = "SELECT $families_table.ID, $families_table.familyID, $families_table.living, $families_table.private, $families_table.branch, p1.lastname as p1lastname, p2.lastname as p2lastname, $place, $datefield, $families_table.gedcom, treename
        FROM ($families_table, $trees_table $eventsjoin) $peoplejoin1 $peoplejoin2
        WHERE $allwhere $allwhere2
        ORDER BY $orderstr LIMIT $newoffset" . $maxsearchresults;
    } elseif ($prefix == 'I') {
      if ($order == "name") {
        $orderstr = "lastname, firstname, $datefieldtr";
      } elseif ($order == "nameup") {
        $orderstr = "lastname DESC, firstname DESC, $datefieldtr DESC";
      } elseif ($order == "date") {
        $orderstr = "$datefieldtr, lastname, firstname";
      } else {
        $orderstr = "$datefieldtr DESC, lastname DESC, firstname DESC";
      }
      $query = "SELECT $people_table.ID, $people_table.personID, lastname, lnprefix, firstname, $people_table.living, $people_table.private, $people_table.branch, prefix, suffix, nameorder, $place, $datefield, $people_table.gedcom, treename
        FROM ($people_table, $trees_table $eventsjoin)
        WHERE $allwhere $allwhere2
        ORDER BY $orderstr LIMIT $newoffset" . $maxsearchresults;
    }
    $result = tng_query($query);
    $numrows = tng_num_rows($result);

    //if results, do again w/o pagination to get total
    if ($numrows == $maxsearchresults || $offsetplus > 1) {
      $query = "SELECT count($idfield) as rcount
        FROM ($table, $trees_table $eventsjoin)
        WHERE $allwhere $allwhere2";
      $result2 = tng_query($query);
      $countrow = tng_fetch_assoc($result2);
      $totrows = $countrow['rcount'];
    } else {
      $totrows = $numrows;
    }
    if ($numrows) {
      echo "<br>\n";
      echo "<div class='card'>\n";
      echo "<h4 class='card-header'>" . $placetxt . "</h4>\n";
      echo "<br>\n";
      $numrowsplus = $numrows + $offset;
      $successcount++;

      echo "<p>" . uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows</p>";

      $namestr = preg_replace("/xxx/", uiTextSnippet($namefield), $namesort);
      $datestr = preg_replace("/yyy/", $placetxt, $datesort);
      ?>
      <table class="table table-sm table-striped">
        <tr>
          <th></th>
          <th><?php echo $namestr; ?></th>
          <th colspan='2'><?php echo $datestr; ?></th>
          <th><?php echo uiTextSnippet($idtext); ?></th>
          <?php if ($numtrees > 1) { ?>
            <th><?php echo uiTextSnippet('tree'); ?></th>
          <?php } ?>
        </tr>
        <?php
        $i = $offsetplus;
        $chartlinkimg = getimagesize("img/Chart.gif");
        $chartlink = "<img src='img/Chart.gif' $chartlinkimg[3] alt=''>";
        while ($row = tng_fetch_assoc($result)) {
          $rights = determineLivingPrivateRights($row);
          $row['allow_living'] = $rights['living'];
          $row['allow_private'] = $rights['private'];
          if ($rights['both']) {
            $placetxt = $row[$place] ? $row[$place] : "";
            $dateval = $row[$datefield];
          } else {
            $dateval = $placetxt = "";
          }
          echo "<tr>\n";

          echo "<td>$i</td>\n";
          $i++;
          echo "<td>\n";
          if ($prefix == 'F') {
            echo "<a href=\"familygroup.php?familyID={$row['familyID']}&amp;tree={$row['gedcom']}\">{$row['p1lastname']} / {$row['p2lastname']}</a>\n";
          } elseif ($prefix == 'I') {
            $name = getNameRev($row);
            echo "<a href=\"pedigree.php?personID={$row['personID']}&amp;tree={$row['gedcom']}\">$chartlink </a>\n";
            echo "<a href=\"getperson.php?personID={$row['personID']}&amp;tree={$row['gedcom']}\">$name</a>\n";
          }
          echo "</td>";
          echo "<td colspan='2'>" . displayDate($dateval) . "<br>$placetxt</td>\n";
          echo "<td>{$row[$idfield]} </td>\n";
          if ($numtrees > 1) {
            echo "<td><a href=\"showtree.php?tree={$row['gedcom']}\">{$row['treename']}</a></td>";
          }
          echo "</tr>\n";
        }
        tng_free_result($result);
        ?>
      </table>
      <?php
      echo buildSearchResultPagination($totrows, "placesearch.php?$urlstring&amp;psearch=" . urlencode($psearchns) . "&amp;order=$order&amp;offset", $maxsearchresults, $max_browsesearch_pages);
      echo "</div>\n";
    }
  }
  return $successcount;
}
//don't allow default tree here
$tree = $orgtree;
$tngconfig['istart'] = 0;

$ldsOK = determineLDSRights();

if ($tree && !$tngconfig['places1tree']) {
  $urlstring = "&amp;tree=$tree";
  $wherestr2 = " AND $places_table.gedcom = \"$tree\" ";

  $query = "SELECT treename FROM $trees_table WHERE gedcom = \"$tree\"";
  $treeresult = tng_query($query);
  $treerow = tng_fetch_assoc($treeresult);
  tng_free_result($treeresult);
} else {
  $urlstring = $wherestr2 = "";
}

if (!$tngconfig['places1tree']) {
  $querystring .= " " . uiTextSnippet('and') . " tree " . uiTextSnippet('equals') . " {$treerow['treename']} ";
  $treejoin = " LEFT JOIN $trees_table on $places_table.gedcom = $trees_table.gedcom";
  $treename = ", treename";
} else {
  $treejoin = $treename = "";
}
$logstring = "<a href=\"placesearch.php?psearch=$psearchns$urlstring\">" . uiTextSnippet('searchresults') . " $querystring</a>";
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

    //show the notes and media for each tree (if none specified)
    //first do media
    $pquery = "SELECT placelevel,latitude,longitude,zoom,notes,$places_table.gedcom$treename FROM $places_table$treejoin WHERE place = \"$psearch\"$wherestr2";
    $presult = tng_query($pquery) or die(uiTextSnippet('cannotexecutequery') . ": $pquery");

    $rightbranch = 1;
    $innermenu = "&nbsp;\n";
    echo tng_menu('L', "place", $psearch, $innermenu);

    $altstr = ", altdescription, altnotes";
    $mapdrawn = false;
    $foundtree = "";
    while ($prow = tng_fetch_assoc($presult)) {
      $foundtree = $prow['gedcom'];
      if ($prow['notes'] || $prow['latitude'] || $prow['longitude']) {
        if (($prow['latitude'] || $prow['longitude']) && $map['key'] && !$mapdrawn) {
          echo "<br><div id='map' style=\"width: {$map['hstw']}; height: {$map['hsth']}; margin-bottom:20px;\" class=\"rounded10\"></div>\n";
          $usedplaces = array();
          $mapdrawn = true;
        }
        if (!$tngconfig['places1tree'] && $numtrees > 1) {
          echo "<br><span><strong>" . uiTextSnippet('tree') . ":</strong> {$prow['treename']}</span><br>\n";
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
              $locations2map[$l2mCount] = array("pinplacelevel" => $pinplacelevel, "lat" => $lat, "long" => $long, "zoom" => $zoom, "htmlcontent" => "<div class=\"mapballoon\">$placeleveltext<br>$codedplace$codednotes</div>");
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
    if (!$tree && tng_num_rows($presult) == 1) {
      $tree = $foundtree;
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
      $cemdata .= "<tr><td>$i.</td><td><a href=\"showmap.php?cemeteryID={$prow['cemeteryID']}\">{$prow['cemname']}</a></td><td>$location</td></tr>\n";
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
    $stdevents = array("birth", "altbirth", "death", "burial");
    $displaymsgs = array("birth" => uiTextSnippet('birth'), "altbirth" => uiTextSnippet('christened'), "death" => uiTextSnippet('died'), "burial" => uiTextSnippet('buried'));
    //$dontdo = array("ADDR","BIRT","CHR","DEAT","BURI","NAME","NICK","TITL","NSFX");
    if ($ldsOK) {
      array_push($stdevents, "endl", "init", "conf", "bapt");
      $displaymsgs['endl'] = uiTextSnippet('endowedlds');
      $displaymsgs['init'] = uiTextSnippet('initlds');
      $displaymsgs['conf'] = uiTextSnippet('conflds');
      $displaymsgs['bapt'] = uiTextSnippet('baptizedlds');
    }
    $successcount += processEvents('I', $stdevents, $displaymsgs);

    $stdevents = array("marr", "div");
    $displaymsgs = array("marr" => uiTextSnippet('married'), "div" => uiTextSnippet('divorced'));
    if ($ldsOK) {
      array_push($stdevents, "seal");
      $displaymsgs['seal'] = uiTextSnippet('sealedslds');
    }
    $successcount += processEvents('F', $stdevents, $displaymsgs);

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