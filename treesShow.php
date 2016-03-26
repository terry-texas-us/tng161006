<?php
require 'tng_begin.php';

require 'functions.php';

if ($treesearch) {
  $wherestr = "WHERE treename LIKE \"%$treesearch%\" OR description LIKE \"%$treesearch%\"";
} else {
  $wherestr = "";
}
$query = "SELECT count(personID) as pcount, $trees_table.gedcom, treename, description FROM $trees_table LEFT JOIN $people_table on $trees_table.gedcom = $people_table.gedcom $wherestr GROUP BY $trees_table.gedcom ORDER BY treename LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);
$numrows = tng_num_rows($result);

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
    <table class='table table-sm table-striped'>
      <thead>
        <tr>
          <th></th>
          <th><?php echo uiTextSnippet('treename'); ?></th>
          <th><?php echo uiTextSnippet('description'); ?></th>
          <th><?php echo uiTextSnippet('individuals'); ?></th>
          <th><?php echo uiTextSnippet('families'); ?></th>
          <th><?php echo uiTextSnippet('sources'); ?></th>
        </tr>
      </thead>
      <?php
      $i = 1;
      while ($row = tng_fetch_assoc($result)) {
        $query = "SELECT count(familyID) as fcount FROM $families_table WHERE gedcom = \"{$row['gedcom']}\"";
        $famresult = tng_query($query);
        $famrow = tng_fetch_assoc($famresult);
        tng_free_result($famresult);

        $query = "SELECT count(sourceID) as scount FROM $sources_table WHERE gedcom = \"{$row['gedcom']}\"";
        $srcresult = tng_query($query);
        $srcrow = tng_fetch_assoc($srcresult);
        tng_free_result($srcresult);

        echo "<tr>\n";
          echo "<td>$i</td>\n";
          echo "<td><a href=\"showtree.php?tree=$row[gedcom]\">{$row['treename']}</a></td>";
          echo "<td>{$row['description']}</td>";
          echo "<td><a href=\"search.php?tree={$row['gedcom']}\">" . number_format($row['pcount']) . "</a></td>";
          echo "<td><a href=\"famsearch.php?tree={$row['gedcom']}\">" . number_format($famrow['fcount']) . "</a></td>";
          echo "<td><a href=\"sourcesShow.php?tree={$row['gedcom']}\">" . number_format($srcrow['scount']) . "</a></td>";
        echo "</tr>\n";
        $i++;
      }
      tng_free_result($result);
      ?>
    </table>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
