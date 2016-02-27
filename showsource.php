<?php
include("begin.php");
include("genlib.php");
if (!$sourceID) {
  header("Location: thispagedoesnotexist.html");
  exit;
}
include("getlang.php");

include("checklogin.php");
include("log.php");
include("personlib.php");

$flags['imgprev'] = true;

$firstsection = 1;
$firstsectionsave = "";
$tableid = "";
$cellnumber = 0;

$query = "SELECT sourceID, title, shorttitle, author, publisher, actualtext, reponame, $sources_table.repoID as repoID, callnum, other FROM $sources_table LEFT JOIN $repositories_table on $sources_table.repoID = $repositories_table.repoID AND $sources_table.gedcom = $repositories_table.gedcom WHERE $sources_table.sourceID = \"$sourceID\" AND $sources_table.gedcom = \"$tree\"";
$result = tng_query($query);
$srcrow = tng_fetch_assoc($result);
if (!tng_num_rows($result)) {
  tng_free_result($result);
  header("Location: thispagedoesnotexist.html");
  exit;
}
tng_free_result($result);

$query = "SELECT count(personID) as ccount FROM $citations_table, $people_table
    WHERE $citations_table.sourceID = '$sourceID' AND $citations_table.gedcom = \"$tree\" AND $citations_table.persfamID = $people_table.personID AND $citations_table.gedcom = $people_table.gedcom
    AND (living = '1' OR private = '1')";
$sresult = tng_query($query);
$srow = tng_fetch_assoc($sresult);
$srcrow['living'] = $srcrow['private'] = $srow['ccount'] ? 1 : 0;

$righttree = checktree($tree);
$rightbranch = $righttree ? true : false;
$rights = determineLivingPrivateRights($srcrow, $righttree, $rightbranch);
$srcrow['allow_living'] = $rights['living'];
$srcrow['allow_private'] = $rights['private'];

tng_free_result($sresult);

$srcnotes = getNotes($sourceID, 'S');
getCitations($sourceID);

$logstring = "<a href=\"showsource.php?sourceID=$sourceID&amp;tree=$tree\">" . xmlcharacters(uiTextSnippet('source') . " {$srcrow['title']} ($sourceID)") . "</a>";
writelog($logstring);
preparebookmark($logstring);

$headtext = $srcrow['title'] ? $srcrow['title'] : $srcrow['shorttitle'];
scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle($headtext);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build();

    $srcmedia = getMedia($srcrow, 'S');
    $srcalbums = getAlbums($srcrow, 'S');
    $photostr = showSmallPhoto($sourceID, $headtext, $rights['both'], 0);
    echo tng_DrawHeading($photostr, $srcrow['shorttitle'], "");

    $sourcetext = "";
    $sourcetext .= "<ul class='nopad'>\n";
    $sourcetext .= beginListItem('info');
    $sourcetext .= "<table class='table'>\n";
    $sourcetext .= "<col class='labelcol'>\n";
    $sourcetext .= "<col style='width: {$datewidth}px'>\n";
    $sourcetext .= "<col>\n";
    if ($srcrow['title']) {
      $sourcetext .= showEvent(array("text" => uiTextSnippet('title'), "fact" => $srcrow['title']));
    }
    if ($srcrow['shorttitle']) {
      $sourcetext .= showEvent(array("text" => uiTextSnippet('shorttitle'), "fact" => $srcrow['shorttitle']));
    }
    if ($srcrow['author']) {
      $sourcetext .= showEvent(array("text" => uiTextSnippet('author'), "fact" => $srcrow['author']));
    }
    if ($srcrow['publisher']) {
      $sourcetext .= showEvent(array("text" => uiTextSnippet('publisher'), "fact" => $srcrow['publisher']));
    }
    if ($srcrow['callnum']) {
      $sourcetext .= showEvent(array("text" => uiTextSnippet('callnum'), "fact" => $srcrow['callnum']));
    }
    if ($srcrow['reponame']) {
      $sourcetext .= showEvent(array("text" => uiTextSnippet('repository'), "fact" => "<a href=\"showrepo.php?repoID={$srcrow['repoID']}&amp;tree=$tree\">{$srcrow['reponame']}</a>"));
    }
    if ($srcrow['other']) {
      $sourcetext .= showEvent(array("text" => uiTextSnippet('other'), "fact" => $srcrow['other']));
    }

    //do custom events
    resetEvents();
    doCustomEvents($sourceID, 'S');

    ksort($events);
    foreach ($events as $event) {
      $sourcetext .= showEvent($event);
    }
    if ($allow_admin && $allow_edit) {
      $sourcetext .= showEvent(array("text" => uiTextSnippet('sourceid'), "date" => $sourceID, "place" => "<a href=\"admin_editsource.php?sourceID=$sourceID&amp;tree=$tree&amp;cw=1\" target='_blank'>" . uiTextSnippet('edit') . "</a>", "np" => 1));
    } else {
      $sourcetext .= showEvent(array("text" => uiTextSnippet('sourceid'), "date" => $sourceID));
    }
    if ($ioffset) {
      $ioffsetstr = "$ioffset, ";
      $newioffset = $ioffset + 1;
    } else {
      $ioffsetstr = "";
      $newioffset = "";
    }
    if ($foffset) {
      $foffsetstr = "$foffset, ";
      $newfoffset = $foffset + 1;
    } else {
      $foffsetstr = "";
      $newfoffset = "";
    }
    $query = "SELECT DISTINCT $citations_table.persfamID, $citations_table.gedcom, firstname, lnprefix, lastname, prefix, suffix, nameorder, living, private, branch FROM $citations_table, $people_table WHERE $citations_table.persfamID = $people_table.personID AND $citations_table.gedcom = $people_table.gedcom AND $citations_table.gedcom = \"$tree\" AND $citations_table.sourceID = '$sourceID' ORDER BY lastname, firstname LIMIT $ioffsetstr" . ($maxsearchresults + 1);
    $sresult = tng_query($query);
    $numrows = tng_num_rows($sresult);
    $sourcelinktext = "";
    $noneliving = $noneprivate = 1;
    while ($srow = tng_fetch_assoc($sresult)) {
      if ($sourcelinktext) {
        $sourcelinktext .= "\n";
      }
      $srights = determineLivingPrivateRights($srow, $righttree);
      $srow['allow_living'] = $srights['living'];
      $srow['allow_private'] = $srights['private'];

      if (!$srow['allow_living']) {
        $noneliving = 0;
      }
      if (!$srow['allow_private']) {
        $noneprivate = 0;
      }
      $sourcelinktext .= "<a href=\"getperson.php?personID={$srow['persfamID']}&amp;tree={$srow['gedcom']}\">";
      $sourcelinktext .= getName($srow);
      $sourcelinktext .= "</a>";
    }
    if ($srcrow['actualtext']) {
      if ((!$noneliving && !$srcrow['allow_living']) || (!$noneprivate && !$srcrow['allow_private'])) {
        $srcrow['actualtext'] = uiTextSnippet('livingphoto');
      }
      $sourcetext .= showEvent(array("text" => uiTextSnippet('text'), "fact" => $srcrow['actualtext']));
    }
    if ($numrows > $maxsearchresults) {
      $sourcelinktext .= "\n[<a href=\"showsource.php?sourceID=$sourceID&amp;tree=$tree&amp;foffset=$foffset&amp;ioffset=" . ($newioffset + $maxsearchresults) . "\">" . uiTextSnippet('moreind') . "</a>]";
    }
    tng_free_result($sresult);

    $query = "SELECT DISTINCT $citations_table.persfamID, $citations_table.gedcom, familyID, husband, wife, living, private, branch FROM $citations_table, $families_table WHERE $citations_table.persfamID = $families_table.familyID AND $citations_table.gedcom = $families_table.gedcom AND $citations_table.gedcom = \"$tree\" AND $citations_table.sourceID = '$sourceID' ORDER BY familyID LIMIT $foffsetstr" . ($maxsearchresults + 1);
    $sresult = tng_query($query);
    $numrows = tng_num_rows($sresult);
    $noneliving = $noneprivate = 1;
    while ($srow = tng_fetch_assoc($sresult)) {
      if ($sourcelinktext) {
        $sourcelinktext .= "\n";
      }
      $srights = determineLivingPrivateRights($srow, $righttree);
      $srow['allow_living'] = $srights['living'];
      $srow['allow_private'] = $srights['private'];

      if (!$srow['allow_living']) {
        $noneliving = 0;
      }
      if (!$srow['allow_private']) {
        $noneprivate = 0;
      }
      $sourcelinktext .= "<a href=\"familygroup.php?familyID=$srow[familyID]&amp;tree={$srow['gedcom']}\">" . uiTextSnippet('family') . ": " . getFamilyName($srow) . "</a>";
    }
    if ($numrows >= $maxsearchresults) {
      $sourcelinktext .= "\n[<a href=\"showsource.php?sourceID=$sourceID&amp;tree=$tree&amp;ioffset=$ioffset&amp;foffset=" . ($newfoffset + $maxsearchresults) . "\">" . uiTextSnippet('morefam') . "</a>]";
    }
    tng_free_result($sresult);

    if ($sourcelinktext) {
      $sourcetext .= showEvent(array("text" => uiTextSnippet('indlinked'), "fact" => $sourcelinktext));
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
    $notes = buildNotes($srcnotes, "");
    if ($notes) {
      $sourcetext .= beginListItem('notes');
      $sourcetext .= "<table class='table table-sm'>\n";
      $sourcetext .= "<col class='labelcol'>\n";
      $sourcetext .= "<col>\n";
      $sourcetext .= "<tr>\n";
      $sourcetext .= "<td class=\"indleftcol\" id=\"notes1\"><span>" . uiTextSnippet('notes') . "</span></td>\n";
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
    echo tng_menu('S', "source", $sourceID, $innermenu);
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
    echo $sourcetext;
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>