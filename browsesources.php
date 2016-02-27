<?php
include("tng_begin.php");

include("functions.php");

function doSourceSearch($instance, $pagenav) {
  global $sourcesearch;
  global $tree;

  $str = "<div>\n";
  $str .= buildFormElement("browsesources", "get", "SourceSearch$instance");
  $str .= "<input name='sourcesearch' type='text' value=\"$sourcesearch\"> \n";
  $str .= "<input type='submit' value=\"" . uiTextSnippet('search') . "\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  $str .= $pagenav;
  $str .= "<input name='tree' type='hidden' value=\"$tree\" />\n";
  if ($sourcesearch) {
    $str .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='browsesources.php'>" . uiTextSnippet('browseallsources') . "</a>";
  }
  $str .= "</form></div>\n";

  return $str;
}

$max_browsesource_pages = 5;
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $page = 1;
}
$sourcesearch = trim($sourcesearch);
if ($tree) {
  $wherestr = "WHERE $sources_table.gedcom = \"$tree\"";
  if ($sourcesearch) {
    $wherestr .= " AND (title LIKE \"%$sourcesearch%\" OR shorttitle LIKE \"%$sourcesearch%\" OR author LIKE \"%$sourcesearch%\")";
  }
  $join = "INNER JOIN";
} else {
  if ($sourcesearch) {
    $wherestr = "WHERE title LIKE \"%$sourcesearch%\" OR shorttitle LIKE \"%$sourcesearch%\" OR author LIKE \"%$sourcesearch%\"";
  } else {
    $wherestr = "";
  }
  $join = "LEFT JOIN";
}
$query = "SELECT sourceID, title, shorttitle, author, $sources_table.gedcom as gedcom, treename FROM $sources_table $join $trees_table on $sources_table.gedcom = $trees_table.gedcom $wherestr ORDER BY title LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);

if ($numrows == $maxsearchresults || $offsetplus > 1) {
  if ($tree) {
    $query = "SELECT count(sourceID) as scount FROM $sources_table LEFT JOIN $trees_table on $sources_table.gedcom = $trees_table.gedcom $wherestr";
  } else {
    $query = "SELECT count(sourceID) as scount FROM $sources_table $wherestr";
  }
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['scount'];
} else {
  $totrows = $numrows;
}
$numrowsplus = $numrows + $offset;

$treestr = $tree ? " (" . uiTextSnippet('tree') . ": $tree)" : "";
$logstring = "<a href=\"browsesources.php?tree=$tree&amp;offset=$offset&amp;sourcesearch=$sourcesearch\">" . xmlcharacters(uiTextSnippet('sources') . $treestr) . "</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('sources'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/archive.svg'><?php echo uiTextSnippet('sources'); ?></h2>
    <br clear='left'>
    <?php
    echo treeDropdown(array('startform' => true, 'endform' => true, 'action' => 'browsesources', 'method' => 'get', 'name' => 'form1', 'id' => 'form1'));

    if ($totrows) {
      echo "<p><span>" . uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows</span></p>";
    }
    $pagenav = buildSearchResultPagination($totrows, "browsesources.php?sourcesearch=$sourcesearch&amp;offset", $maxsearchresults, $max_browsesource_pages);
    if ($pagenav || $sourcesearch) {
      echo doSourceSearch(1, $pagenav);
      echo "<br>\n";
    }
    ?>
    <table class="table table-sm table-striped">
      <tr>
        <th></th>
        <th><?php echo uiTextSnippet('sourceid'); ?></th>
        <th><?php echo uiTextSnippet('title') . ", " . uiTextSnippet('author'); ?></th>
        <?php if ($numtrees > 1) { ?>
          <th><?php echo uiTextSnippet('tree'); ?></th>
        <?php } ?>
      </tr>
      <?php
      $i = $offsetplus;
      while ($row = tng_fetch_assoc($result)) {
        $sourcetitle = $row['title'] ? $row['title'] : $row['shorttitle'];
        echo "<tr>\n";
          echo "<td>$i</td>\n";
          echo "<td><a href='showsource.php?sourceID={$row['sourceID']}&amp;tree={$row['gedcom']}'>{$row['sourceID']}</a></td>\n";
          echo "<td>$sourcetitle<br>{$row['author']}</td>\n";
          if ($numtrees > 1) {
            echo "<td><a href='showtree.php?tree={$row['gedcom']}'>{$row['treename']}</a></td>\n";
          }
        echo "</tr>\n";
        $i++;
      }
      tng_free_result($result);
      ?>
    </table>
    <br>
    <?php
    if ($pagenav || $sourcesearch) {
      echo doSourceSearch(2, $pagenav) . "<br>\n";
    }
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>