<?php
require 'tng_begin.php';

require 'functions.php';
require 'personlib.php';

function doMediaSearch($instance, $pagenav) {
  global $mediasearch;

  $str = buildFormElement('albumsShow', 'get', "MediaSearch$instance");
  $str .= "<input name='mediasearch' type='text' value=\"$mediasearch\"> \n";
  $str .= "<input type='submit' value=\"" . uiTextSnippet('search') . "\" /> \n";
  $str .= "<input type='button' value=\"" . uiTextSnippet('tng_reset') . "\" onclick=\"window.location.href='albumsShow.php';\" />&nbsp;&nbsp;&nbsp;";
  $str .= $pagenav;
  $str .= "</form>\n";

  return $str;
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
$wherestr = "WHERE active = \"1\"";
if ($mediasearch) {
  $wherestr .= " AND ($albums_table.albumname LIKE \"%$mediasearch%\" OR $albums_table.description LIKE \"%$mediasearch%\" OR $albums_table.keywords LIKE \"%$mediasearch%\")";
}

$query = "SELECT albumID, albumname, description, alwayson FROM $albums_table $wherestr ORDER BY albumname LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);
$numrows = tng_num_rows($result);

if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count($albums_table.albumID) AS acount FROM $albums_table";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  tng_free_result($result2);
  $totrows = $row['acount'];
} else {
  $totrows = $numrows;
}
$numrowsplus = $numrows + $offset;

$treestr = '';
$treestr = trim("$mediasearch $treestr");
$treestr = $treestr ? " ($treestr)" : '';

$logstring = "<a href=\"albumsShow.php?" . "offset=$offset&amp;mediasearch=$mediasearch\">" . uiTextSnippet('allalbums') . "$treestr</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('albums'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/album.svg'><?php echo uiTextSnippet('albums'); ?></h2>
    <br clear='all'>
    <?php
    if ($totrows) {
      echo '<p>' . uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows</p>";
    }
    $pagenav = buildSearchResultPagination($totrows, 'albumsShow.php?' . "mediasearch=$mediasearch&amp;offset", $maxsearchresults, $max_browsemedia_pages);
    echo doMediaSearch(1, $pagenav);
    echo "<br>\n";
    ?>
      <table class='table table-sm'>
        <?php
        $albumtext = $header = '';
        $header .= "<tr><td></td>\n";
        $header .= '<th>' . uiTextSnippet('thumb') . "</th>\n";
        $header .= '<th>' . uiTextSnippet('description') . "</th>\n";
        $header .= '<th>' . uiTextSnippet('numitems') . "</th>\n";
        $header .= '<th>' . uiTextSnippet('indlinked') . "</th>\n";
        $header .= "</tr>\n";

        $i = $offsetplus;
        $maxplus = $maxsearchresults + 1;
        $thumbcount = 0;
        while ($row = tng_fetch_assoc($result)) {
          $query2 = "SELECT count($albumlinks_table.albumlinkID) AS acount "
                . "FROM $albumlinks_table WHERE albumID = \"{$row['albumID']}\"";
          $result2 = tng_query($query2) or die(uiTextSnippet('cannotexecutequery') . ": $query2");
          $arow = tng_fetch_assoc($result2);
          tng_free_result($result2);

          $query = "SELECT $album2entities_table.entityID AS personID, people.personID AS personID2, people.living AS living, people.private AS private, people.branch AS branch, $families_table.branch AS fbranch, $families_table.living AS fliving, $families_table.private AS fprivate, familyID, husband, wife, people.lastname AS lastname, people.lnprefix AS lnprefix, people.firstname AS firstname, people.prefix AS prefix, people.suffix AS suffix, nameorder, $sources_table.title, $sources_table.sourceID, $repositories_table.repoID, reponame, deathdate, burialdate, linktype FROM $album2entities_table "
              . "LEFT JOIN $people_table AS people ON $album2entities_table.entityID = people.personID "
              . "LEFT JOIN $families_table ON $album2entities_table.entityID = $families_table.familyID "
              . "LEFT JOIN $sources_table ON $album2entities_table.entityID = $sources_table.sourceID "
              . "LEFT JOIN $repositories_table ON ($album2entities_table.entityID = $repositories_table.repoID) "
              . "WHERE albumID = '{$row['albumID']}' ORDER BY lastname, lnprefix, firstname, personID LIMIT $maxplus";
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
              $query = "SELECT count(personID) AS ccount FROM $citations_table, $people_table "
                  . "WHERE $citations_table.sourceID = '{$prow['personID']}' AND $citations_table.persfamID = $people_table.personID AND (living = '1' OR private = '1')";
              $presult2 = tng_query($query);
              $prow2 = tng_fetch_assoc($presult2);
              if ($prow2['ccount']) {
                $prow['living'] = 1;
              }
              tng_free_result($presult2);
            }
            if ($prow['living'] == null && $prow['private'] == null && $prow[linktype] == 'F') {
              $query = "SELECT count(familyID) AS ccount FROM $citations_table, $families_table "
                  . "WHERE $citations_table.sourceID = '{$prow['personID']}' AND $citations_table.persfamID = $families_table.familyID AND living = '1'";
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

            //echo "al={$prow['allow_living']}, ap={$prow['allow_private']}<br>";

            if (!$rights['living']) {
              $foundliving = 1;
            }
            if (!$rights['private']) {
              $foundprivate = 1;
            }

            if ($prow['personID2'] != null) {
              $medialinktext .= "<li><a href=\"peopleShowPerson.php?personID={$prow['personID2']}\">";
              $medialinktext .= getName($prow) . "</a></li>\n";
            } elseif ($prow['sourceID'] != null) {
              $sourcetext = $prow['title'] ? uiTextSnippet('source') . ": {$prow['title']}" : uiTextSnippet('source') . ": {$prow['sourceID']}";
              $medialinktext .= "<li><a href=\"sourcesShowSource.php?sourceID={$prow['personID']}\">$sourcetext</a></li>\n";
            } elseif ($prow['repoID'] != null) {
              $repotext = $prow['reponame'] ? uiTextSnippet('repository') . ": {$prow['reponame']}" : uiTextSnippet('repository') . ": {$prow['repoID']}";
              $medialinktext .= "<li><a href=\"repositoriesShowItem.php?repoID={$prow['personID']}\">$repotext</a></li>\n";
            } elseif ($prow['familyID'] != null) {
              $medialinktext .= "<li><a href=\"familiesShowFamily.php?familyID={$prow['personID']}\">" . uiTextSnippet('family') . ': ' . getFamilyName($prow) . "</a></li>\n";
            } else {
              $medialinktext .= "<li><a href=\"placesearch.php?psearch={$prow['personID']}\">{$prow['personID']}</a></li>\n";
            }
            $count++;
          }
          if ($medialinktext) {
            $medialinktext = "<ul>$medialinktext</ul>\n";
          }
          tng_free_result($presult);

          $showAlbumInfo = $row['allow_living'] = $row['alwayson'] || (!$foundprivate && !$foundliving);

          $albumtext .= "<tr><td><span>$i</span></td>";

          $description = $row['description'];
          if ($showAlbumInfo) {
            $imgsrc = getAlbumPhoto($row['albumID'], $row['albumname']);
            $alblink = "<a href=\"albumsShowAlbum.php?albumID={$row['albumID']}\">{$row['albumname']}</a>";
          } else {
            $imgsrc = '';
            $alblink = uiTextSnippet('living');
            $nonamesloc = $foundprivate ? $tngconfig['nnpriv'] : $nonames;
            if ($nonamesloc) {
              $description = uiTextSnippet('livingphoto');
            } else {
              $description .= '(' . uiTextSnippet('livingphoto') . ')';
            }
          }

          if ($imgsrc) {
            $albumtext .= "<td style=\"width:{$thumbmaxw}px\">$imgsrc</td>";
            $thumbcount++;
          } else {
            $albumtext .= '<td>&nbsp;</td>';
          }

          $albumtext .= "<td><span>$alblink<br>$description&nbsp;</span></td>\n";
          $albumtext .= "<td><span>$arow[acount]&nbsp;</span></td>\n";
          $albumtext .= "<td width=\"200\"><span>\n$medialinktext&nbsp;</span></td>\n";
          $albumtext .= "</tr>\n";
          $i++;
        }
        tng_free_result($result);

        if (!$thumbcount) {
          $header = str_replace('<td>' . uiTextSnippet('thumb') . '</td>', '', $header);
          $albumtext = str_replace('<td>&nbsp;</td><td>', '<td>', $albumtext);
        }
        echo $header . $albumtext;
        ?>
      </table>
    <?php
    if ($totrows && ($pagenav || $mediasearch)) {
      echo doMediaSearch(2, $pagenav);
      echo '<br>';
    }
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
?>
</body>
</html>
