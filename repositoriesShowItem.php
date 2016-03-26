<?php
require 'tng_begin.php';

if (!$repoID) {
  header("Location: thispagedoesnotexist.html");
  exit;
}
require 'personlib.php';
require 'repositories.php';

$flags['imgprev'] = true;

$firstsection = 1;
$firstsectionsave = "";
$tableid = "";
$cellnumber = 0;

$query = "SELECT * FROM $repositories_table WHERE repoID = \"$repoID\" AND gedcom = \"$tree\"";
$result = tng_query($query);
$reporow = tng_fetch_assoc($result);
if (!tng_num_rows($result)) {
  tng_free_result($result);
  header("Location: thispagedoesnotexist.html");
  exit;
}
tng_free_result($result);

$reporow['living'] = 0;
$reporow['allow_living'] = 1;

$reponotes = getNotes($repoID, 'R');

$logstring = "<a href=\"repositoriesShowItem.php?repoID=$repoID&amp;tree=$tree\">" . uiTextSnippet('repo') . " {$reporow['reponame']} ($repoID)</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle($reporow['reponame']);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<?php
echo "<body id='public'>\n";
echo $publicHeaderSection->build();

$repomedia = getMedia($reporow, 'R');
$repoalbums = getAlbums($reporow, 'R');
$photostr = showSmallPhoto($repoID, $reporow['reponame'], $reporow['allow_living'], 0);
echo tng_DrawHeading($photostr, $reporow['reponame'], "");

$repotext = "";
$repotext .= "<ul class='nopad'>\n";
$repotext .= beginListItem('info');
$repotext .= "<table class=\"table tfixed\">\n";
$repotext .= "<col class=\"labelcol\"/><col style=\"width:{$datewidth}px\"/><col/>\n";
if ($reporow['reponame']) {
  $repotext .= showEvent(array("text" => uiTextSnippet('name'), "fact" => $reporow['reponame']));
}
if ($reporow['addressID']) {
  $reporow['isrepo'] = true;
  $extras = getFact($reporow);
  $repotext .= showEvent(array("text" => uiTextSnippet('address'), "fact" => $extras));
}

//do custom events
resetEvents();
doCustomEvents($repoID, 'R');

ksort($events);
foreach ($events as $event) {
  $repotext .= showEvent($event);
}
if ($allow_admin && $allowEdit) {
  $repotext .= showEvent(array("text" => uiTextSnippet('repoid'), "date" => $repoID, "place" => "<a href=\"repositoriesEdit.php?repoID=$repoID&amp;tree=$tree&amp;cw=1\" target='_blank'>" . uiTextSnippet('edit') . "</a>", "np" => 1));
} else {
  $repotext .= showEvent(array("text" => uiTextSnippet('repoid'), "date" => $repoID));
}

if ($soffset) {
  $soffsetstr = "$soffset, ";
  $newsoffset = $soffset + 1;
} else {
  $soffsetstr = "";
  $newsoffset = "";
}

$query = "SELECT sourceID, title, shorttitle FROM $sources_table WHERE gedcom = \"$tree\" AND repoID = '$repoID' ORDER BY title LIMIT $soffsetstr" . ($maxsearchresults + 1);
$sresult = tng_query($query);
$numrows = tng_num_rows($sresult);
$repolinktext = "";
while ($srow = tng_fetch_assoc($sresult)) {
  if ($repolinktext) {
    $repolinktext .= "\n";
  }
  $title = $srow['shorttitle'] ? $srow['shorttitle'] : $srow['title'];
  $repolinktext .= "<a href=\"showsource.php?sourceID={$srow['sourceID']}&amp;tree=$tree\">$title</a>";
}
if ($numrows >= $maxsearchresults) {
  $repolinktext .= "\n[<a href=\"repositoriesShowItem.php?repoID=$repoID&amp;tree=$tree&amp;foffset=$foffset&amp;soffset=" . ($newsoffset + $maxsearchresults) . "\">" . uiTextSnippet('moresrc') . "</a>]";
}
tng_free_result($sresult);

if ($repolinktext) {
  $repotext .= showEvent(array("text" => uiTextSnippet('indlinked'), "fact" => $repolinktext));
}

$repotext .= "</table>\n";
$repotext .= "<br>\n";
$repotext .= endListItem('info');

$media = doMediaSection($repoID, $repomedia, $repoalbums);
if ($media) {
  $repotext .= beginListItem('media');
  $repotext .= $media;
  $repotext .= endListItem('media');
}

$notes = buildNotes($reponotes, "");
if ($notes) {
  $repotext .= beginListItem('notes');
  $repotext .= "<table class=\"table tfixed\">\n";
  $repotext .= "<col class=\"labelcol\"/><col/>\n";
  $repotext .= "<tr>\n";
  $repotext .= "<td class=\"indleftcol\" id=\"notes1\"><span>" . uiTextSnippet('notes') . "</span></td>\n";
  $repotext .= "<td>$notes</td>\n";
  $repotext .= "</tr>\n";
  $repotext .= "</table>\n";
  $repotext .= "<br>\n";
  $repotext .= endListItem('notes');
}
$repotext .= "</ul>\n";

if ($media || $notes) {
  $innermenu = "<a href='#' onclick=\"return infoToggle('info');\" id='tng_plink'>" . uiTextSnippet('repoinfo') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
  if ($media) {
    $innermenu .= "<a href='#' onclick=\"return infoToggle('media');\" id=\"tng_mlink\">" . uiTextSnippet('media') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
  }
  if ($notes) {
    $innermenu .= "<a href='#' onclick=\"return infoToggle('notes');\" id=\"tng_nlink\">" . uiTextSnippet('notes') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
  }
  $innermenu .= "<a href='#' onclick=\"return infoToggle('all');\" id=\"tng_alink\">" . uiTextSnippet('all') . "</a>\n";
} else {
  $innermenu = "<span id='tng_plink'>" . uiTextSnippet('repoinfo') . "</span>\n";
}

$rightbranch = 1;
echo buildRepositoryMenu('repository', $repoID);
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

<?php
echo $repotext;
?>
  <br>

<?php
echo $publicFooterSection->build();
echo scriptsManager::buildScriptElements($flags, 'public');
?>
</body>
</html>
