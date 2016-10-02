<?php
require 'tng_begin.php';

require 'functions.php';

function doNoteSearch($notesearch) {
  $html .= "<div>\n";
  $html .= "<form class='form-inline' name='notesearch1' action='notesShow.php' method='get'>\n";
  $html .= "<input class='form-control' name='notesearch' type='text' value='$notesearch'>\n";
  $html .= "<button class='btn btn-outline-primary' type='submit' value='" . uiTextSnippet('search') . "'><img class='icon-sm' src='svg/magnifying-glass.svg'></button>\n";
  if ($notesearch) {
    $html .= "<button class='btn btn-outline-secondary'><a href='notesShow.php'>" . uiTextSnippet('showall') . '</a></button>';
  }
  $html .= "</form>\n";
  $html .= "</div>\n";

  return $html;
}

$max_browsenote_pages = 5;
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = '';
  $page = 1;
}
$whereClause = 'WHERE xnotes.ID = notelinks.xnoteID';

if (!$allowPrivate) {
  $whereClause .= ' AND notelinks.secret != "1"';
}
if ($notesearch) {
  $notesearch2 = addslashes($notesearch);
  $notesearch = cleanIt($notesearch);

  $whereClause .= $whereClause ? ' AND' : 'WHERE';
  $whereClause .= " MATCH(xnotes.note) AGAINST('$notesearch2' IN BOOLEAN MODE)";
}

$query = "SELECT xnotes.ID AS ID, xnotes.note AS note, notelinks.persfamID AS personID FROM (xnotes, notelinks) $whereClause ORDER BY note LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);

if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(xnotes.ID) AS scount FROM (xnotes, notelinks) $whereClause";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['scount'];
} else {
  $totrows = $numrows;
}
$numrowsplus = $numrows + $offset;

$logstring = "<a href=\"notesShow.php?offset=$offset&amp;notesearch=" . htmlentities(stripslashes($notesearch), ENT_QUOTES) . '">' . xmlcharacters(uiTextSnippet('notes')) . '</a>';
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('notes'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $sessionCharset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/new-message.svg'><?php echo uiTextSnippet('notes'); ?></h2>
    <br clear='left'>
    <?php
    echo doNoteSearch($notesearch);
    echo "<br>\n";
    if ($totrows) {
      echo '<p>' . uiTextSnippet('matches') . ' ' . number_format($offsetplus) . ' ' . uiTextSnippet('to') . ' ' . number_format($numrowsplus) . ' ' . uiTextSnippet('of') . ' ' . number_format($totrows) . '</p>';
    }
    ?>
      <table class='table table-sm table-hover'>
        <thead class='thead-default'>
          <tr>
            <th></th>
            <th><?php echo uiTextSnippet('notes'); ?></th>
            <th width='20%'><?php echo uiTextSnippet('indlinked'); ?></th>
          </tr>
        </thead>
        <?php
        $i = $offsetplus;
        while ($nrow = tng_fetch_assoc($result)) {
          $linkedTo = '';
          $noneliving = 1;
          $noneprivate = 1;
          $query2 = $query;

          if ($nrow['secret']) {
            $nrow['private'] = 1;
          }
          if (!$linkedTo) {
            $query = "SELECT * FROM people WHERE personID = '{$nrow['personID']}'";
            $result2 = tng_query($query);
            if (tng_num_rows($result2) == 1) {
              $row2 = tng_fetch_assoc($result2);

              if (!$row2['living'] || !$row2['private']) {
                $query = "SELECT count(personID) AS ccount FROM citations, people
                    WHERE citations.sourceID = '{$nrow['personID']}' AND citations.persfamID = people.personID AND (living = '1' OR private = '1')";
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

              $linkedTo .= "<a href=\"peopleShowPerson.php?personID={$row2['personID']}\">" . getNameRev($row2) . "</a>\n<br>\n";
              tng_free_result($result2);
            }
          }

          if (!$linkedTo) {
            $query = "SELECT * FROM families WHERE familyID = '{$nrow['personID']}'";
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
              $linkedTo .= "<a href=\"familiesShowFamily.php?familyID={$row2['familyID']}\" target='_blank'>" . uiTextSnippet('family') . " ({$row2['familyID']})</a>\n<br>\n";
              tng_free_result($result2);
            }
          }
          if (!$linkedTo) {
            $query = "SELECT * FROM sources WHERE sourceID = '{$nrow['personID']}'";
            $result2 = tng_query($query);
            if (tng_num_rows($result2) == 1) {
              $row2 = tng_fetch_assoc($result2);
              $linkedTo .= "<a href=\"sourcesShowSource.php?sourceID={$row2['sourceID']}\" target='_blank'>" . uiTextSnippet('source') . " $sourcetext ({$row2['sourceID']})</a>\n<br>\n";
              tng_free_result($result2);
            }
          }
          if (!$linkedTo) {
            $query = "SELECT * FROM repositories WHERE repoID = '{$nrow['personID']}'";
            $result2 = tng_query($query);
            if (tng_num_rows($result2) == 1) {
              $row2 = tng_fetch_assoc($result2);
              $linkedTo .= "<a href=\"repositoriesShowItem.php?repoID={$row2['repoID']}\" target='_blank'>" . uiTextSnippet('repository') . " $sourcetext ({$row2['repoID']})</a>\n<br>\n";
              tng_free_result($result2);
            }
          }
          echo "<tr><td>$i</td>\n";
          echo '<td>';
          if ($noneliving && $noneprivate) {
            echo nl2br($nrow['note']);
          } else {
            echo uiTextSnippet('livingnote');
          }
          echo '</td>';
          echo "<td>$linkedTo&nbsp;</td></tr>\n";
          $i++;
        }
        tng_free_result($result);
        ?>
      </table>
    <?php
    $pagenav = buildSearchResultPagination($totrows, "notesShow.php?notesearch=$notesearch&amp;offset", $maxsearchresults, $max_browsenote_pages);
    if ($pagenav || $notesearch) {
      echo $pagenav;
      echo '<br>';
    }
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
