<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

if ($session_charset != "UTF-8") {
  $mytitle = tng_utf8_decode($mytitle);
}
$query = "SELECT sourceID, title FROM $sources_table WHERE gedcom = \"$tree\" AND title LIKE \"%$mytitle%\" ORDER BY title LIMIT 250";
$result = tng_query($query);

header("Content-type:text/html; charset=" . $session_charset);
?>
<div id='findrepodiv'>
  <table class='table table-sm'>
    <tr>
      <td>
        <h4><?php echo uiTextSnippet('searchresults'); ?></h4>
        <span>(<?php echo uiTextSnippet('clicktoselect'); ?>)</span>
      </td>
      <td></td>
      <td>
        <form action=''>
          <input type='button' value="<?php echo uiTextSnippet('find'); ?>" onClick="reopenFindSourceForm();">
        </form>
      </td>
    </tr>
  </table>
  <table class='table table-sm'>
    <tr>
      <th><?php echo uiTextSnippet('sourceid'); ?></th>
      <th><?php echo uiTextSnippet('name'); ?></th>
    </tr>
    <?php
    while ($row = tng_fetch_assoc($result)) {
      $fixedtitle = addslashes($row['title']);
      echo "<tr><td><span><a href=\"findsource2.php\" onClick=\"return returnTitle('{$row['sourceID']}');\">{$row['sourceID']}</a></span></td><td><a href=\"findsource2.php\" onClick=\"return returnTitle('{$row['sourceID']}');\">" . truncateIt($row['title'], 75) . "</a>&nbsp;</td></tr>\n";
    }
    tng_free_result($result);
    ?>
  </table>
</div>
