<?php
include("tng_begin.php");

include("functions.php");

function doNoteSearch($instance, $pagenav) {
  global $notesearch;
  global $tree;

  $str = "<div>\n";
  $str .= buildFormElement("browsenotes", "get", "notesearch$instance");
  $str .= "<input name='notesearch' type='text' value=\"$notesearch\" /> \n";
  $str .= "<input type='submit' value=\"" . uiTextSnippet('search') . "\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  $str .= $pagenav;
  if ($notesearch) {
    $str .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='browsenotes.php'>" . uiTextSnippet('browseallnotes') . "</a>";
  }
  $str .= "<input name='tree' type='hidden' value=\"$tree\" />\n";
  $str .= "</form></div>\n";

  return $str;
}

$max_browsenote_pages = 5;
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $page = 1;
}
$wherestr = "WHERE $xnotes_table.ID = $notelinks_table.xnoteID";
if ($tree) {
  $wherestr .= " AND $xnotes_table.gedcom = \"$tree\"";
}
if (!$allow_private) {
  $wherestr .= " AND $notelinks_table.secret != \"1\"";
}
if ($notesearch) {
  $notesearch2 = addslashes($notesearch);
  $notesearch = cleanIt($notesearch);

  $wherestr .= $wherestr ? " AND" : "WHERE";
  $wherestr .= " match($xnotes_table.note) against( \"$notesearch2\" in boolean mode)";
}

$query = "SELECT $xnotes_table.ID as ID, $xnotes_table.note as note, $notelinks_table.persfamID as personID, $xnotes_table.gedcom as gedcom
    FROM ($xnotes_table, $notelinks_table)
    $wherestr ORDER BY note LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);

if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count($xnotes_table.ID) as scount FROM ($xnotes_table, $notelinks_table)
    LEFT JOIN $trees_table on $xnotes_table.gedcom = $trees_table.gedcom
    $wherestr";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['scount'];
} else {
  $totrows = $numrows;
}

$numrowsplus = $numrows + $offset;

$treestr = $tree ? " (" . uiTextSnippet('tree') . ": $tree)" : "";
$logstring = "<a href=\"browsenotes.php?tree=$tree&amp;offset=$offset&amp;notesearch=" . htmlentities(stripslashes($notesearch), ENT_QUOTES) . "\">" . xmlcharacters(uiTextSnippet('notes') . $treestr) . "</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('notes'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/new-message.svg'><?php echo uiTextSnippet('notes'); ?></h2>
    <br clear='left'>
    <?php
    echo treeDropdown(array('startform' => true, 'endform' => true, 'action' => 'browsenotes', 'method' => 'get', 'name' => 'form1', 'id' => 'form1'));

    if ($totrows) {
      echo "<p>" . uiTextSnippet('matches') . " " . number_format($offsetplus) . " " . uiTextSnippet('to') . " " . number_format($numrowsplus) . " " . uiTextSnippet('of') . " " . number_format($totrows) . "</p>";
    }

    $pagenav = buildSearchResultPagination($totrows, "browsenotes.php?notesearch=$notesearch&amp;offset", $maxsearchresults, $max_browsenote_pages);
    echo doNoteSearch(1, $pagenav);
    echo "<br>\n";
    ?>
      <table class='table table-sm'>
        <tr>
          <th></th>
          <th><?php echo uiTextSnippet('notes'); ?></th>
          <th><?php echo uiTextSnippet('indlinked'); ?></th>
        </tr>
        <?php
        $i = $offsetplus;
        while ($nrow = tng_fetch_assoc($result)) {
          $notelinktext = "";
          $noneliving = 1;
          $noneprivate = 1;
          $query2 = $query;

          if ($nrow['secret']) {
            $nrow['private'] = 1;
          }
          if (!$notelinktext) {
            $query = "SELECT * FROM $people_table WHERE personID = \"{$nrow['personID']}\" AND gedcom = \"{$nrow['gedcom']}\"";
            $result2 = tng_query($query);
            if (tng_num_rows($result2) == 1) {
              $row2 = tng_fetch_assoc($result2);

              if (!$row2['living'] || !$row2['private']) {
                $query = "SELECT count(personID) as ccount FROM $citations_table, $people_table
              WHERE $citations_table.sourceID = '{$nrow['personID']}' AND $citations_table.persfamID = $people_table.personID AND $citations_table.gedcom = $people_table.gedcom
              AND (living = '1' OR private = '1')";
                $nresult2 = tng_query($query);
                $nrow2 = tng_fetch_assoc($nresult2);
                if ($nrow2['ccount']) {
                  $row2['living'] = 1;
                  $row2['private'] = 1;
                }
                tng_free_result($nresult2);
              }

              $nrights = determineLivingPrivateRights($row2);
              $row2['allow_living'] = $nrights['living'];
              $row2['allow_private'] = $nrights['private'];

              if (!$row2['allow_private']) {
                $noneprivate = 0;
              }
              if (!$row2['allow_living']) {
                $noneliving = 0;
              }

              $notelinktext .= "<a href=\"peopleShowPerson.php?personID={$row2['personID']}&tree={$row2['gedcom']}\">" . getNameRev($row2) . " ({$row2['personID']})</a>\n<br>\n";
              tng_free_result($result2);
            }
          }

          if (!$notelinktext) {
            $query = "SELECT * FROM $families_table WHERE familyID = \"{$nrow['personID']}\" AND gedcom = \"{$nrow['gedcom']}\"";
            $result2 = tng_query($query);
            if (tng_num_rows($result2) == 1) {
              $row2 = tng_fetch_assoc($result2);
              $nrights = determineLivingPrivateRights($row2);
              $row2['allow_living'] = $nrights['living'];
              $row2['allow_private'] = $nrights['private'];

              if (!$row2['allow_private']) {
                $noneprivate = 0;
              }
              if (!$row2['allow_living']) {
                $noneliving = 0;
              }
              $notelinktext .= "<a href=\"familygroup.php?familyID={$row2['familyID']}&tree={$row2['gedcom']}\" target='_blank'>" . uiTextSnippet('family') . " {$row2['familyID']}</a>\n<br>\n";
              tng_free_result($result2);
            }
          }
          if (!$notelinktext) {
            $query = "SELECT * FROM $sources_table WHERE sourceID = \"{$nrow['personID']}\" AND gedcom = \"{$nrow['gedcom']}\"";
            $result2 = tng_query($query);
            if (tng_num_rows($result2) == 1) {
              $row2 = tng_fetch_assoc($result2);
              $notelinktext .= "<a href=\"showsource.php?sourceID={$row2['sourceID']}&tree={$row2['gedcom']}\" target='_blank'>" . uiTextSnippet('source') . " $sourcetext ({$row2['sourceID']})</a>\n<br>\n";
              tng_free_result($result2);
            }
          }
          if (!$notelinktext) {
            $query = "SELECT * FROM $repositories_table WHERE repoID = \"{$nrow['personID']}\" AND gedcom = \"{$nrow['gedcom']}\"";
            $result2 = tng_query($query);
            if (tng_num_rows($result2) == 1) {
              $row2 = tng_fetch_assoc($result2);
              $notelinktext .= "<a href=\"repositoriesShowItem.php?repoID={$row2['repoID']}&tree={$row2['gedcom']}\" target='_blank'>" . uiTextSnippet('repository') . " $sourcetext ({$row2['repoID']})</a>\n<br>\n";
              tng_free_result($result2);
            }
          }
          echo "<tr><td>$i</td>\n";
          echo "<td>";
          if ($noneliving && $noneprivate) {
            echo nl2br($nrow['note']);
          } else {
            echo uiTextSnippet('livingnote');
          }
          echo "&nbsp;</td>";
          echo "<td width=\"175\">$notelinktext&nbsp;</td></tr>\n";
          $i++;
        }
        tng_free_result($result);
        ?>
      </table><br>
    <?php
    if ($pagenav || $notesearch) {
      echo doNoteSearch(2, $pagenav);
      echo "<br>";
    }
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
