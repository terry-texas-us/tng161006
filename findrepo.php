<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

if ($session_charset != "UTF-8") {
  $mytitle = tng_utf8_decode($mytitle);
}
$query = "SELECT repoID, reponame FROM $repositories_table WHERE gedcom = \"$tree\" AND reponame LIKE \"%$mytitle%\" ORDER BY reponame LIMIT 250";
$result = tng_query($query);

header("Content-type:text/html; charset=" . $session_charset);
?>
<div id='findreporesdiv'>
  <table class='table table-sm'>
    <tr>
      <td>
        <h4><?php echo uiTextSnippet('searchresults'); ?></h4>
        <span>(<?php echo uiTextSnippet('clicktoselect'); ?>)</span>
      </td>
      <td></td>
      <td>
        <form action=''>
          <input type='button' value="<?php echo uiTextSnippet('find'); ?>" onclick="reopenFindRepoForm();">
        </form>
      </td>
    </tr>
  </table>
  <table class='table table-sm'>
    <tr>
      <th><span><?php echo uiTextSnippet('repoid'); ?></span></th>
      <th><span><?php echo uiTextSnippet('name'); ?></span></th>
    </tr>
    <?php
    while ($row = tng_fetch_assoc($result)) {
      $fixedtitle = addslashes($row['reponame']);
      echo "<tr>\n";
        echo "<td><span><a href=\"findrepo.php\" onClick=\"return returnTitle('{$row['repoID']}');\">{$row['repoID']}</a></span></td>";
        echo "<td><a href='findrepo.php' onClick=\"return returnTitle('{$row['repoID']}');\">" . truncateIt($row['reponame'], 75) . "</a></td>\n";
      echo "</tr>\n";
    }
    tng_free_result($result);
    ?>
  </table>
</div>
