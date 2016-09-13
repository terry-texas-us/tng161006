<?php
require 'tng_begin.php';

$query = "SELECT reportname, reportdesc, reportID FROM $reports_table WHERE active = 1 ORDER BY rank, reportname";
$result = tng_query($query);
$numrows = tng_num_rows($result);

$logstring = "<a href='reportsShow.php'>" . xmlcharacters(uiTextSnippet('reports')) . '</a>';
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('reports'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/print.svg'><?php echo uiTextSnippet('reports'); ?></h2>
    <br clear='left'>
    <?php
    if (!$numrows) {
      echo uiTextSnippet('noreports');
    } else {
      ?>
      <table class="table table-sm table-striped">
        <thead>
          <tr>
            <th></th>
            <th><?php echo uiTextSnippet('reportname'); ?></th>
            <th><?php echo uiTextSnippet('description'); ?></th>
          </tr>
        </thead>
        <?php
        $count = 1;
        while ($row = tng_fetch_assoc($result)) {
          echo "<tr><td>$count.</td><td><a href=\"reportsShowReport.php?reportID={$row['reportID']}\">{$row['reportname']}</a></td><td>{$row['reportdesc']}</td></tr>\n";
          $count++;
        }
        tng_free_result($result);
        ?>
      </table>
    <?php } ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>