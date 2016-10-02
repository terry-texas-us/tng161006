<?php
require 'tng_begin.php';

require 'functions.php';

$max_browsesource_pages = 5;
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = '';
  $page = 1;
}
$sourcesearch = trim($sourcesearch);
if ($sourcesearch) {
  $wherestr = "WHERE title LIKE \"%$sourcesearch%\" OR shorttitle LIKE \"%$sourcesearch%\" OR author LIKE \"%$sourcesearch%\"";
} else {
  $wherestr = '';
}
$query = "SELECT sourceID, title, shorttitle, author FROM sources $wherestr ORDER BY title LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);

if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(sourceID) AS scount FROM sources $wherestr";

  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['scount'];
} else {
  $totrows = $numrows;
}
$numrowsplus = $numrows + $offset;

$logstring = "<a href=\"sourcesShow.php?offset=$offset&amp;sourcesearch=$sourcesearch\">" . xmlcharacters(uiTextSnippet('sources')) . '</a>';
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('sources'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $sessionCharset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/archive.svg'><?php echo uiTextSnippet('sources'); ?></h2>
    <br clear='left'>
    <?php
    $pagenav = buildSearchResultPagination($totrows, "sourcesShow.php?sourcesearch=$sourcesearch&amp;offset", $maxsearchresults, $max_browsesource_pages);
    if ($pagenav || $sourcesearch) {
    ?>      
      <div>
        <form class='form-inline' action='sourcesShow.php' method="get" name='SourceSearch1'>
          <input class='form-control' name='sourcesearch' type='text' value='<?php echo $sourcesearch; ?>'>
          <button class='btn btn-outline-primary' type='submit' value="<?php echo uiTextSnippet('search'); ?>"><img class='icon-sm' src='svg/magnifying-glass.svg'></button>
          <?php if ($sourcesearch) { ?>
            <button class='btn btn-outline-secondary'><a href='sourcesShow.php'><?php echo uiTextSnippet('showall'); ?></a></button>
          <?php } ?>
        </form>
      </div>
      <br>
    <?php 
    }
    if ($totrows) {
      echo '<p><span>' . uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows</span></p>";
    }
    ?>
    <table class='table table-sm table-hover'>
      <thead class='thead-default'>
        <tr>
          <th></th>
          <th><?php echo uiTextSnippet('sourceid'); ?></th>
          <th><?php echo uiTextSnippet('title') . ', ' . uiTextSnippet('author'); ?></th>
        </tr>
      </thead>
      <?php
      $i = $offsetplus;
      while ($row = tng_fetch_assoc($result)) {
        $sourcetitle = $row['title'] ? $row['title'] : $row['shorttitle'];
        echo "<tr>\n";
          echo "<td>$i</td>\n";
          echo "<td><a href='sourcesShowSource.php?sourceID={$row['sourceID']}'>{$row['sourceID']}</a></td>\n";
          echo "<td>$sourcetitle, {$row['author']}</td>\n";
        echo "</tr>\n";
        $i++;
      }
      tng_free_result($result);
      ?>
    </table>
    <br>
    <?php if ($pagenav || $sourcesearch) { ?>
      <form action='sourcesShow.php' method="get" name='SourceSearch2'>
        <?php echo $pagenav; ?>
      </form>
      <br>
    <?php } ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>