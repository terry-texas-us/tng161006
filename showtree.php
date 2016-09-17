<?php
require 'tng_begin.php';

function showFact($text, $fact, $numflag = 0) {
  echo "<tr>\n";
  echo '<td>' . $text . "</td>\n";
  echo "<td colspan='2' ";
  echo $numflag ? " align='right'" : '';
  echo '>';
  echo $numflag ? number_format($fact) : $fact;
  echo "&nbsp;</td>\n";
  echo "</tr>\n";
}

$result = tng_query('SELECT gedcom, treename, description, owner, secret, address, email, city, state, zip, country, phone FROM trees');
$row = tng_fetch_assoc($result);
tng_free_result($result);

writelog("<a href='showtree.php'>" . uiTextSnippet('tree') . ": {$row['treename']}</a>");
preparebookmark("<a href='showtree.php'>" . uiTextSnippet('tree') . ": {$row['treename']}</a>");

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('tree') . ': ' . $row['treename']);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><?php echo uiTextSnippet('tree') . ': ' . $row['treename']; ?></h2>
    <br clear='all'>

      <table class="table table-sm">
        <?php
        if ($row['treename']) {
          showFact(uiTextSnippet('treename'), $row['treename']);
        }
        if ($row['description']) {
          showFact(uiTextSnippet('description'), $row['description']);
        }
        $presult = tng_query("SELECT count(personID) AS pcount FROM $people_table");
        $prow = tng_fetch_assoc($presult);
        tng_free_result($presult);
        showFact(uiTextSnippet('individuals'), "<a href='search.php'>" . number_format($prow['pcount']) . '</a>');
          
        $fresult = tng_query("SELECT count(familyID) AS fcount FROM $families_table");
        $frow = tng_fetch_assoc($fresult);
        tng_free_result($fresult);
        showFact(uiTextSnippet('families'), "<a href='famsearch.php'>" . number_format($frow['fcount']) . '</a>');

        $sresult = tng_query('SELECT count(sourceID) AS scount FROM sources');
        $srow = tng_fetch_assoc($sresult);
        tng_free_result($sresult);
        showFact(uiTextSnippet('sources'), "<a href='sourcesShow.php'>" . number_format($srow['scount']) . '</a>');

        if (!$row['secret']) {
          if ($row['owner']) {
            showFact(uiTextSnippet('owner'), $row['owner']);
          }
          if ($row['address']) {
            showFact(uiTextSnippet('address'), $row['address']);
          }
          if ($row['city']) {
            showFact(uiTextSnippet('city'), $row['city']);
          }
          if ($row['state']) {
            showFact(uiTextSnippet('stateprov'), $row['state']);
          }
          if ($row['zip']) {
            showFact(uiTextSnippet('zip'), $row['zip']);
          }
          if ($row['country']) {
            showFact(uiTextSnippet('country'), $row['country']);
          }
          if ($row['email']) {
            showFact(uiTextSnippet('email'), "<a href=\"mailto:{$row['email']}\">{$row['email']}</a>");
          }
          if ($row['phone']) {
            showFact(uiTextSnippet('phone'), $row['phone']);
          }
        }
        ?>
      </table>
      <br>
    <?php
    echo "<a href='statistics.php'>" . uiTextSnippet('morestats') . "</a>\n";
    ?>
    <br><br>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>