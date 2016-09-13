<?php
require 'tng_begin.php';

require 'functions.php';

$query = "SELECT gedcom, treename, description FROM $treesTable";
$result = tng_query($query);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
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
        $presult = tng_query("SELECT count(personID) AS pcount FROM $people_table");
        $prow = tng_fetch_assoc($presult);
        tng_free_result($presult);
                
        $fresult = tng_query("SELECT count(familyID) AS fcount FROM $families_table");
        $frow = tng_fetch_assoc($fresult);
        tng_free_result($fresult);

        $sresult = tng_query("SELECT count(sourceID) AS scount FROM $sources_table");
        $srow = tng_fetch_assoc($sresult);
        tng_free_result($sresult);

        echo "<tr>\n";
          echo "<td>$i</td>\n";
          echo "<td><a href=\"showtree.php?tree=$row[gedcom]\">{$row['treename']}</a></td>";
          echo "<td>{$row['description']}</td>";
          echo "<td><a href='search.php'>" . number_format($prow['pcount']) . '</a></td>';
          echo "<td><a href='famsearch.php'>" . number_format($frow['fcount']) . '</a></td>';
          echo "<td><a href='sourcesShow.php'>" . number_format($srow['scount']) . '</a></td>';
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
