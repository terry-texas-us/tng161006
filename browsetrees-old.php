<?php
include("tng_begin.php");

include("functions.php");

function doTreeSearch($instance, $pagenav) {
  global $treesearch;

  $str = "<span>\n";
  $str .= buildFormElement("browsetrees", "GET", "TreeSearch$instance");
  $str .= "<input name='treesearch' type='text' value=\"$treesearch\"> \n";
  $str .= "<input type='submit' value=\"" . uiTextSnippet('search') . "\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  $str .= $pagenav;
  if ($docsearch) {
    $str .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='browsetrees-old.php'>" . uiTextSnippet('browsealltrees') . "</a>";
  }
  $str .= "</form></span>\n";

  return $str;
}

$max_browsetree_pages = 5;
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $page = 1;
}

if ($treesearch) {
  $wherestr = "WHERE treename LIKE \"%$treesearch%\" OR description LIKE \"$treesearch%\"";
} else {
  $wherestr = "";
}

$query = "SELECT count(personID) as pcount, $trees_table.gedcom, treename, description FROM $trees_table LEFT JOIN $people_table on $trees_table.gedcom = $people_table.gedcom GROUP BY $trees_table.gedcom ORDER BY treename LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);

if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(gedcom) as treecount FROM $trees_table";
  $result2 = tng_query($query);
  $countrow = tng_fetch_assoc($result2);
  $totrows = $countrow['treecount'];
} else {
  $totrows = $numrows;
}

$numrowsplus = $numrows + $offset;

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('browsealltrees'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
<?php echo $publicHeaderSection->build(); ?>
<h2><?php echo uiTextSnippet('browsealltrees'); ?></h2>
<br clear='all'>
<?php
if ($totrows) {
  echo "<p><span>" . uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows</span></p>";
}

$pagenav = buildSearchResultPagination($totrows, "browsetrees-old.php?treesearch=$treesearch&amp;offset", $maxsearchresults, $max_browsetree_pages);
if ($pagenav || $treesearch) {
  echo doTreeSearch(1, $pagenav);
}
?>
  <table class="table table-sm table-striped">
    <tr>
      <th></th>
      <th><?php echo uiTextSnippet('treename'); ?></th>
      <th><?php echo uiTextSnippet('description'); ?></th>
      <th><?php echo uiTextSnippet('individuals'); ?></th>
    </tr>
    <?php
    $i = $offsetplus;
    while ($row = tng_fetch_assoc($result)) {
      echo "<tr><td>$i</td>\n";
      echo "<td><a href=\"showtree.php?tree=$row[gedcom]\">{$row['treename']}</a></td>";
      echo "<td>{$row['description']}</td>";
      if ($row['pcount']) {
        echo "<td align=\"right\"><span><a href=\"search.php?tree={$row['gedcom']}\">{$row['pcount']}</a></span></td>";
      } else {
        echo "<td align=\"right\"><span>{$row['pcount']}</span></td>";
      }
      echo "</tr>\n";
      $i++;
    }
    tng_free_result($result);
    ?>
  </table>
  <br>
<?php
if ($pagenav || $treesearch) {
  echo doTreeSearch(2, $pagenav) . "<br>\n";
}
echo $publicFooterSection->build();
echo scriptsManager::buildScriptElements($flags, 'public');
?>
</body>
</html>
