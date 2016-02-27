<?php
include("tng_begin.php");

function showFact($text, $fact, $numflag = 0) {
  echo "<tr>\n";
  echo "<td>" . $text . "</td>\n";
  echo "<td colspan='2' ";
  echo $numflag ? " align=\"right\"" : "";
  echo ">";
  echo $numflag ? number_format($fact) : $fact;
  echo "&nbsp;</td>\n";
  echo "</tr>\n";
}

$query = "SELECT count(personID) as pcount, $trees_table.gedcom, treename, description, owner, secret, address, email, city, state, zip, country, phone FROM $trees_table LEFT JOIN $people_table on $trees_table.gedcom = $people_table.gedcom WHERE $trees_table.gedcom = \"$tree\" GROUP BY $trees_table.gedcom";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

writelog("<a href='showtree.php?tree=$tree'>" . uiTextSnippet('tree') . ": {$row['treename']}</a>");
preparebookmark("<a href='showtree.php?tree=$tree'>" . uiTextSnippet('tree') . ": {$row['treename']}</a>");

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('tree') . ": " . $row['treename']);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><?php echo uiTextSnippet('tree') . ": " . $row['treename']; ?></h2>
    <br clear='all'>

      <table class="table table-sm">
        <?php
        if ($row['treename']) {
          showFact(uiTextSnippet('treename'), $row['treename']);
        }
        if ($row['description']) {
          showFact(uiTextSnippet('description'), $row['description']);
        }

        showFact(uiTextSnippet('individuals'), $row['pcount'], true);

        $query = "SELECT count(familyID) as fcount FROM $families_table WHERE gedcom = \"{$row['gedcom']}\"";
        $famresult = tng_query($query);
        $famrow = tng_fetch_assoc($famresult);
        tng_free_result($famresult);
        showFact(uiTextSnippet('families'), $famrow['fcount'], true);

        $query = "SELECT count(sourceID) as scount FROM $sources_table WHERE gedcom = \"{$row['gedcom']}\"";
        $srcresult = tng_query($query);
        $srcrow = tng_fetch_assoc($srcresult);
        tng_free_result($srcresult);
        showFact(uiTextSnippet('sources'), $srcrow['scount'], true);

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
            showFact(uiTextSnippet('state'), $row['state']);
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