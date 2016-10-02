<?php
require 'begin.php';
require 'genlib.php';
if (!$sourceID) {
  header('Location: thispagedoesnotexist.html');
  exit;
}
require 'getlang.php';

require 'checklogin.php';
require 'log.php';
require 'personlib.php';
require 'sources.php';

$flags['imgprev'] = true;

$firstsection = 1;
$firstsectionsave = '';
$tableid = '';
$cellnumber = 0;

$query = "SELECT sourceID, title, shorttitle, author, publisher, actualtext, reponame, sources.repoID AS repoID, callnum, other FROM sources LEFT JOIN repositories ON sources.repoID = repositories.repoID WHERE sources.sourceID = '$sourceID'";
$result = tng_query($query);
$srcrow = tng_fetch_assoc($result);
if (!tng_num_rows($result)) {
  tng_free_result($result);
  header('Location: thispagedoesnotexist.html');
  exit;
}
tng_free_result($result);

$query = "SELECT count(personID) AS ccount FROM citations, people WHERE citations.sourceID = '$sourceID' AND citations.persfamID = people.personID AND (living = '1' OR private = '1')";
$sresult = tng_query($query);
$srow = tng_fetch_assoc($sresult);
$srcrow['living'] = $srcrow['private'] = $srow['ccount'] ? 1 : 0;

$rightbranch = true;
$rights = determineLivingPrivateRights($srcrow, $rightbranch);
$srcrow['allow_living'] = $rights['living'];
$srcrow['allow_private'] = $rights['private'];

tng_free_result($sresult);

$srcnotes = getNotes($sourceID, 'S');
getCitations($sourceID);

$logstring = "<a href=\"sourcesShowSource.php?sourceID=$sourceID\">" . xmlcharacters(uiTextSnippet('source') . " {$srcrow['title']} ($sourceID)") . '</a>';
writelog($logstring);
preparebookmark($logstring);

$headtext = $srcrow['title'] ? $srcrow['title'] : $srcrow['shorttitle'];
scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle($headtext);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $sessionCharset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build();

    $srcmedia = getMedia($srcrow, 'S');
    $srcalbums = getAlbums($srcrow, 'S');
    $photostr = showSmallPhoto($sourceID, $headtext, $rights['both'], 0);
    echo tng_DrawHeading($photostr, $srcrow['title'], '');

    $sourcetext = '';
    $sourcetext .= "<ul>\n";
    $sourcetext .= beginListItem('info');
    $sourcetext .= "<table class='table'>\n";
    $sourcetext .= "<col class='labelcol'>\n";
    $sourcetext .= "<col style='width: {$datewidth}px'>\n";
    $sourcetext .= "<col>\n";
    if ($srcrow['title']) {
      $sourcetext .= showEvent(['text' => uiTextSnippet('title'), 'fact' => $srcrow['title']]);
    }
    if ($srcrow['shorttitle']) {
      $sourcetext .= showEvent(['text' => uiTextSnippet('shorttitle'), 'fact' => $srcrow['shorttitle']]);
    }
    if ($srcrow['author']) {
      $sourcetext .= showEvent(['text' => uiTextSnippet('author'), 'fact' => $srcrow['author']]);
    }
    if ($srcrow['publisher']) {
      $sourcetext .= showEvent(['text' => uiTextSnippet('publisher'), 'fact' => $srcrow['publisher']]);
    }
    if ($srcrow['callnum']) {
      $sourcetext .= showEvent(['text' => uiTextSnippet('callnum'), 'fact' => $srcrow['callnum']]);
    }
    if ($srcrow['reponame']) {
      $sourcetext .= showEvent(['text' => uiTextSnippet('repository'), 'fact' => "<a href=\"repositoriesShowItem.php?repoID={$srcrow['repoID']}\">{$srcrow['reponame']}</a>"]);
    }
    if ($srcrow['other']) {
      $sourcetext .= showEvent(['text' => uiTextSnippet('other'), 'fact' => $srcrow['other']]);
    }

    //do custom events
    resetEvents();
    doCustomEvents($sourceID, 'S');

    ksort($events);
    foreach ($events as $event) {
      $sourcetext .= showEvent($event);
    }
    if ($allow_admin && $allowEdit) {
      $sourcetext .= showEvent(['text' => uiTextSnippet('sourceid'), 'date' => $sourceID, 'place' => "<a href=\"sourcesEdit.php?sourceID=$sourceID&amp;cw=1\" target='_blank'>" . uiTextSnippet('edit') . '</a>', 'np' => 1]);
    } else {
      $sourcetext .= showEvent(['text' => uiTextSnippet('sourceid'), 'date' => $sourceID]);
    }
    if ($ioffset) {
      $ioffsetstr = "$ioffset, ";
      $newioffset = $ioffset + 1;
    } else {
      $ioffsetstr = '';
      $newioffset = '';
    }
    if ($foffset) {
      $foffsetstr = "$foffset, ";
      $newfoffset = $foffset + 1;
    } else {
      $foffsetstr = '';
      $newfoffset = '';
    }
    $query = "SELECT DISTINCT citations.persfamID, firstname, lnprefix, lastname, prefix, suffix, nameorder, living, private, branch FROM citations, people WHERE citations.persfamID = people.personID AND citations.sourceID = '$sourceID' ORDER BY lastname, firstname LIMIT $ioffsetstr" . ($maxsearchresults + 1);
    $sresult = tng_query($query);
    $numrows = tng_num_rows($sresult);
    $sourcelinktext = '';
    $noneliving = $noneprivate = 1;
    while ($srow = tng_fetch_assoc($sresult)) {
      if ($sourcelinktext) {
        $sourcelinktext .= "\n";
      }
      $srights = determineLivingPrivateRights($srow);
      $srow['allow_living'] = $srights['living'];
      $srow['allow_private'] = $srights['private'];

      if (!$srow['allow_living']) {
        $noneliving = 0;
      }
      if (!$srow['allow_private']) {
        $noneprivate = 0;
      }
      $sourcelinktext .= "<a href=\"peopleShowPerson.php?personID={$srow['persfamID']}\">";
      $sourcelinktext .= getName($srow);
      $sourcelinktext .= '</a>';
    }
    if ($srcrow['actualtext']) {
      if ((!$noneliving && !$srcrow['allow_living']) || (!$noneprivate && !$srcrow['allow_private'])) {
        $srcrow['actualtext'] = uiTextSnippet('livingphoto');
      }
      $sourcetext .= showEvent(['text' => uiTextSnippet('text'), 'fact' => $srcrow['actualtext']]);
    }
    if ($numrows > $maxsearchresults) {
      $sourcelinktext .= "\n[<a href=\"sourcesShowSource.php?sourceID=$sourceID&amp;foffset=$foffset&amp;ioffset=" . ($newioffset + $maxsearchresults) . '">' . uiTextSnippet('moreind') . '</a>]';
    }
    tng_free_result($sresult);

    $query = "SELECT DISTINCT citations.persfamID, familyID, husband, wife, living, private, branch FROM citations, families WHERE citations.persfamID = families.familyID AND citations.sourceID = '$sourceID' ORDER BY familyID LIMIT $foffsetstr" . ($maxsearchresults + 1);
    $sresult = tng_query($query);
    $numrows = tng_num_rows($sresult);
    $noneliving = $noneprivate = 1;
    while ($srow = tng_fetch_assoc($sresult)) {
      if ($sourcelinktext) {
        $sourcelinktext .= "\n";
      }
      $srights = determineLivingPrivateRights($srow);
      $srow['allow_living'] = $srights['living'];
      $srow['allow_private'] = $srights['private'];

      if (!$srow['allow_living']) {
        $noneliving = 0;
      }
      if (!$srow['allow_private']) {
        $noneprivate = 0;
      }
      $sourcelinktext .= "<a href=\"familiesShowFamily.php?familyID=$srow[familyID]\">" . uiTextSnippet('family') . ': ' . getFamilyName($srow) . '</a>';
    }
    if ($numrows >= $maxsearchresults) {
      $sourcelinktext .= "\n[<a href=\"sourcesShowSource.php?sourceID=$sourceID&amp;ioffset=$ioffset&amp;foffset=" . ($newfoffset + $maxsearchresults) . '">' . uiTextSnippet('morefam') . '</a>]';
    }
    tng_free_result($sresult);

    if ($sourcelinktext) {
      $sourcetext .= showEvent(['text' => uiTextSnippet('indlinked'), 'fact' => $sourcelinktext]);
    }
    $sourcetext .= "</table>\n";
    $sourcetext .= "<br>\n";
    $sourcetext .= endListItem('info');

    $media = doMediaSection($sourceID, $srcmedia, $srcalbums);
    if ($media) {
      $sourcetext .= beginListItem('media');
      $sourcetext .= $media . "<br>\n";
      $sourcetext .= endListItem('media');
    }
    $notes = buildNotes($srcnotes, '');
    if ($notes) {
      $sourcetext .= beginListItem('notes');
      $sourcetext .= "<table class='table table-sm'>\n";
      $sourcetext .= "<col class='labelcol'>\n";
      $sourcetext .= "<col>\n";
      $sourcetext .= "<tr>\n";
      $sourcetext .= '<td class="indleftcol" id="notes1"><span>' . uiTextSnippet('notes') . "</span></td>\n";
      $sourcetext .= "<td>$notes</td>\n";
      $sourcetext .= "</tr>\n";
      $sourcetext .= "</table>\n";
      $sourcetext .= "<br>\n";
      $sourcetext .= endListItem('notes');
    }
    $sourcetext .= "</ul>\n";

    if ($notes || $media) {
      $innermenu = "<a href='#' onclick=\"return infoToggle('info');\" id='tng_plink'>" . uiTextSnippet('srcinfo') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
      if ($media) {
        $innermenu .= "<a href='#' onclick=\"return infoToggle('media');\" id=\"tng_mlink\">" . uiTextSnippet('media') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
      }
      if ($notes) {
        $innermenu .= "<a href='#' onclick=\"return infoToggle('notes');\" id=\"tng_nlink\">" . uiTextSnippet('notes') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
      }
      $innermenu .= "<a href='#' onclick=\"return infoToggle('all');\" id=\"tng_alink\">" . uiTextSnippet('all') . "</a>\n";
    } else {
      $innermenu = "<span id='tng_plink'>" . uiTextSnippet('srcinfo') . "</span>\n";
    }
    echo buildSourceMenu('source', $sourceID);
    echo "<div class='pub-innermenu small'>\n";
      echo $innermenu;
    echo "</div>\n";
    echo "<br>\n";
    ?>
    <script>
      function innerToggle(part, subpart, subpartlink) {
        if (part === subpart)
          turnOn(subpart, subpartlink);
        else
          turnOff(subpart, subpartlink);
      }

      function turnOn(subpart, subpartlink) {
        $('#' + subpart).show();
      }

      function turnOff(subpart, subpartlink) {
        $('#' + subpart).hide();
      }

      function infoToggle(part) {
        if (part === "all") {
          $('#info').show();
          <?php
          if ($media) {
            echo "\$('#media').show();\n";
          }
          if ($notes) {
            echo "\$('#notes').show();\n";
          }
          ?>
        }
        else {
          innerToggle(part, "info", "tng_plink");
          <?php
          if ($media) {
            echo "innerToggle(part,\"media\",\"tng_mlink\");\n";
          }
          if ($notes) {
            echo "innerToggle(part,\"notes\",\"tng_nlink\");\n";
          }
          ?>
        }
        return false;
      }
    </script>
    <?php echo $sourcetext; ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
