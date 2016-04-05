<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

$tng_search_album = $_SESSION['tng_search_album'] = 1;
if ($newsearch) {
  $exptime = 0;
  setcookie("tng_search_album_post[search]", $searchstring, $exptime);
  setcookie("tng_search_album_post[tree]", $tree, $exptime);
  setcookie("tng_search_album_post[tngpage]", 1, $exptime);
  setcookie("tng_search_album_post[offset]", 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = stripslashes($_COOKIE['tng_search_album_post']['search']);
  }
  if (!$tree) {
    $tree = $_COOKIE['tng_search_album_post']['tree'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_album_post']['tngpage'];
    $offset = $_COOKIE['tng_search_album_post']['offset'];
  } else {
    $exptime = 05;
    setcookie("tng_search_album_post[tngpage]", $tngpage, $exptime);
    setcookie("tng_search_album_post[offset]", $offset, $exptime);
  }
}
$searchstring_noquotes = preg_replace("/\"/", "&#34;", $searchstring);
$searchstring = addslashes($searchstring);

if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $tngpage = 1;
}

if ($assignedtree) {
  $tree = $assignedtree;
}

$wherestr = $searchstring ? "WHERE albumname LIKE \"%$searchstring%\" OR description LIKE \"%$searchstring%\" OR keywords LIKE \"%$searchstring%\"" : "";

if ($assignedtree) {
  $wherestr2 = " AND $album2entities_table.gedcom = \"$assignedtree\"";
} elseif ($tree) {
  $wherestr2 = " AND $album2entities_table.gedcom = \"$tree\"";
} else {
  $wherestr2 = "";
}

$query = "SELECT * FROM $albums_table $wherestr ORDER BY albumname LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(albumID) as acount FROM $albums_table $wherestr";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['acount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('albums'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="admin-albums">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('albums', $message);
    $navList = new navList('');
    //    $navList->appendItem([true, "albumsBrowse.php", uiTextSnippet('browse'), "findalbum"]);
    $navList->appendItem([$allowMediaAdd, "albumsAdd.php", uiTextSnippet('add'), "addalbum"]);
    $navList->appendItem([$allowMediaEdit, "albumsSort.php", uiTextSnippet('text_sort'), "sortalbums"]);
    echo $navList->build("findalbum");
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <div>
            <?php require '_/components/php/findAlbumForm.php'; ?>
            <?php
            $numrowsplus = $numrows + $offset;
            if (!$numrowsplus) {
              $offsetplus = 0;
            }
            echo displayListLocation($offsetplus, $numrowsplus, $totrows);
            if ($numrows) {
            ?>
              <table class="table table-sm table-striped">
                <tr>
                  <th><?php echo uiTextSnippet('action'); ?></th>
                  <th><?php echo uiTextSnippet('thumb'); ?></th>
                  <th><?php echo uiTextSnippet('albumname') . ", " . uiTextSnippet('description'); ?></th>
                  <th><?php echo uiTextSnippet('albmedia'); ?></th>
                  <th><?php echo uiTextSnippet('active'); ?>?</th>
                  <th><?php echo uiTextSnippet('linkedto'); ?></th>
                </tr>
                <?php
                $actionstr = "";
                if ($allowMediaEdit) {
                  $actionstr .= "<a href='albumsEdit.php?albumID=xxx' title='" . uiTextSnippet('edit') . "'>\n";
                  $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
                  $actionstr .= "</a>\n";
                }
                if ($allowMediaDelete) {
                  $actionstr .= "<a href='#' onclick=\"return confirmDeleteAlbum('xxx');\" title='" . uiTextSnippet('delete') . "'>\n";
                  $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
                  $actionstr .= "</a>\n";
                }
                $actionstr .= "<a href=\"" . "albumsShowAlbum.php?albumID=xxx\" title='" . uiTextSnippet('preview') . "'>\n";
                $actionstr .= "<img class='icon-sm' src='svg/eye.svg'>\n";
                $actionstr .= "</a>\n";

                while ($row = tng_fetch_assoc($result)) {
                  $newactionstr = preg_replace("/xxx/", $row['albumID'], $actionstr);
                  echo "<tr id=\"row_{$row['albumID']}\">\n";
                  echo   "<td>\n";
                  echo     "<div class=\"action-btns\">$newactionstr</div>\n";
                  echo   "</td>\n";
                  echo   "<td style=\"width: " . ($thumbmaxw + 6) . "px; text-align: center\">";

                  $query2 = "SELECT thumbpath, usecollfolder, mediatypeID FROM ($media_table, $albumlinks_table) WHERE albumID = \"{$row['albumID']}\" AND $media_table.mediaID = $albumlinks_table.mediaID AND defphoto=\"1\"";
                  $result2 = tng_query($query2) or die(uiTextSnippet('cannotexecutequery') . ": $query2");
                  $trow = tng_fetch_assoc($result2);
                  $tmediatypeID = $trow['mediatypeID'];
                  $tusefolder = $trow['usecollfolder'] ? $mediatypes_assoc[$tmediatypeID] : $mediapath;

                  tng_free_result($result2);

                  if ($trow['thumbpath'] && file_exists("$rootpath$tusefolder/" . $trow['thumbpath'])) {
                    $size = getimagesize("$rootpath$tusefolder/" . $trow['thumbpath']);
                    echo "<a href=\"albumsEdit.php?albumID={$row['albumID']}\"><img src=\"$tusefolder/" . str_replace("%2F", "/", rawurlencode($trow['thumbpath'])) . "\" $size[3] alt=\"{$row['albumname']}\"></a>\n";
                  } else {
                    echo "&nbsp;";
                  }
                  echo "</td>\n";

                  $query = "SELECT count(albumlinkID) as acount FROM $albumlinks_table WHERE albumID = \"{$row['albumID']}\"";
                  $cresult = tng_query($query);
                  $crow = tng_fetch_assoc($cresult);
                  $acount = $crow['acount'];
                  tng_free_result($cresult);

                  $editlink = "albumsEdit.php?albumID={$row['albumID']}";
                  $albumname = $allowEdit ? "<a href=\"$editlink\" title='" . uiTextSnippet('edit') . "'>" . $row['albumname'] . "</a>" : "<u>" . $row['albumname'] . "</u>";

                  echo "<td>$albumname<br>" . strip_tags($row['description']) . "&nbsp;</td>\n";
                  echo "<td>$acount&nbsp;</td>\n";
                  $active = $row['active'] ? uiTextSnippet('yes') : uiTextSnippet('no');
                  echo "<td>$active</td>\n";

                  $query = "SELECT people.personID as personID2, familyID, husband, wife, people.lastname as lastname, people.lnprefix as lnprefix, people.firstname as firstname, people.prefix as prefix, people.suffix as suffix, nameorder, $album2entities_table.entityID as personID, $sources_table.title, $sources_table.sourceID, $repositories_table.repoID, reponame
                      FROM $album2entities_table
                      LEFT JOIN $people_table AS people ON $album2entities_table.entityID = people.personID AND $album2entities_table.gedcom = people.gedcom
                      LEFT JOIN $families_table ON $album2entities_table.entityID = $families_table.familyID AND $album2entities_table.gedcom = $families_table.gedcom
                      LEFT JOIN $sources_table ON $album2entities_table.entityID = $sources_table.sourceID AND $album2entities_table.gedcom = $sources_table.gedcom
                      LEFT JOIN $repositories_table ON ($album2entities_table.entityID = $repositories_table.repoID AND $album2entities_table.gedcom = $repositories_table.gedcom)
                      WHERE albumID = \"{$row['albumID']}\"$wherestr2 ORDER BY lastname, lnprefix, firstname, personID LIMIT 10";
                  $presult = tng_query($query);
                  $alinktext = "";
                  while ($prow = tng_fetch_assoc($presult)) {
                    $prow['allow_living'] = 1;
                    if ($prow['personID2'] != null) {
                      $alinktext .= "<li>" . getName($prow) . " ({$prow['personID2']})</li>\n";
                    } elseif ($prow['sourceID'] != null) {
                      $sourcetext = $prow['title'] ? uiTextSnippet('source') . ": {$prow['title']}" : uiTextSnippet('source') . ": {$prow['sourceID']}";
                      $alinktext .= "<li>$sourcetext ({$prow['sourceID']})</li>\n";
                    } elseif ($prow['repoID'] != null) {
                      $repotext = $prow['reponame'] ? uiTextSnippet('repository') . ": {$prow['reponame']}" : uiTextSnippet('repository') . ": {$prow['repoID']}";
                      $alinktext .= "<li>$repotext ({$prow['repoID']})</li>\n";
                    } elseif ($prow['familyID'] != null) {
                      $alinktext .= "<li>" . uiTextSnippet('family') . ": " . getFamilyName($prow) . "</li>\n";
                    } else {
                      $alinktext .= "<li>{$prow['personID']}</li>";
                    }
                  }
                  $alinktext = $alinktext ? "<ul>\n$alinktext\n</ul>\n" : "&nbsp;";
                  echo "<td>$alinktext</td>\n</tr>\n";
                }
                ?>
              </table>
              <?php
              echo buildSearchResultPagination($totrows, "albumsBrowse.php?searchstring=$searchstring&amp;exactmatch=$exactmatch&amp;offset", $maxsearchresults, 5);
            } else {
              echo "<div class='alert alert-warning'>" . uiTextSnippet('norecords') . "</div>\n";
            }
            tng_free_result($result);
            ?>
          </div>
        </td>
      </tr>
    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src='js/admin.js'></script>
<script>
  function confirmDeleteAlbum(ID) {
    if (confirm(textSnippet('confdeletealbum'))) {
      deleteIt('album', ID);
    }
    return false;
  }

  function resetFamilies() {
    document.form1.searchstring.value = '';
  }
</script>
</body>
</html>
