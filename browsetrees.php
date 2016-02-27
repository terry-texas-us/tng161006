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
  if ($treesearch) {
    $str .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='browsetrees.php'>" . uiTextSnippet('browsealltrees') . "</a>";
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
  $wherestr = "WHERE treename LIKE \"%$treesearch%\" OR description LIKE \"%$treesearch%\"";
} else {
  $wherestr = "";
}

$query = "SELECT count(personID) as pcount, $trees_table.gedcom, treename, description FROM $trees_table LEFT JOIN $people_table on $trees_table.gedcom = $people_table.gedcom $wherestr GROUP BY $trees_table.gedcom ORDER BY treename LIMIT $newoffset" . $maxsearchresults;
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
$headSection->setTitle(uiTextSnippet('trees'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/tree.svg'><?php echo uiTextSnippet('trees'); ?></h2>
    <br clear='left'>
    <?php
    if ($totrows) {
      echo "<p><span>" . uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows</span></p>";
    }
    $pagenav = buildSearchResultPagination($totrows, "browsetrees.php?treesearch=$treesearch&amp;offset", $maxsearchresults, $max_browsetree_pages);
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
          <th><?php echo uiTextSnippet('families'); ?></th>
          <th><?php echo uiTextSnippet('sources'); ?></th>
        </tr>
        <?php
        $i = $offsetplus;
        while ($row = tng_fetch_assoc($result)) {
          $query = "SELECT count(familyID) as fcount FROM $families_table WHERE gedcom = \"{$row['gedcom']}\"";
          $famresult = tng_query($query);
          $famrow = tng_fetch_assoc($famresult);
          tng_free_result($famresult);

          $query = "SELECT count(sourceID) as scount FROM $sources_table WHERE gedcom = \"{$row['gedcom']}\"";
          $srcresult = tng_query($query);
          $srcrow = tng_fetch_assoc($srcresult);
          tng_free_result($srcresult);

          echo "<tr><td>$i</td>\n";
          echo "<td><a href=\"showtree.php?tree=$row[gedcom]\">{$row['treename']}</a></td>";
          echo "<td>{$row['description']}</td>";
          echo "<td align=\"right\"><a href=\"search.php?tree={$row['gedcom']}\">" . number_format($row['pcount']) . "</a></td>";
          echo "<td align=\"right\"><a href=\"famsearch.php?tree={$row['gedcom']}\">" . number_format($famrow['fcount']) . "</a></td>";
          echo "<td align=\"right\"><a href=\"browsesources.php?tree={$row['gedcom']}\">" . number_format($srcrow['scount']) . "</a></td>";
          echo "</tr>\n";
          $i++;
        }
        tng_free_result($result);
        ?>
      </table>

    <?php
    if ($pagenav || $treesearch) {
      echo doTreeSearch(2, $pagenav);
    }
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
