<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

if ($del) {
  $query = "DELETE FROM $places_table WHERE ID=\"$del\"";
  $result = tng_query($query);
}

if ($session_charset != 'UTF-8') {
  $myplace = tng_utf8_decode($myplace);
}

$allwhere = '1=1';
if ($myplace) {
  $allwhere .= " AND place LIKE \"%$myplace%\"";
}
if ($temple) {
  $allwhere .= ' AND temple = 1';
}
$query = "SELECT ID, place, temple, notes FROM $places_table WHERE $allwhere ORDER BY place LIMIT 250";
$result = tng_query($query);

header('Content-type:text/html; charset=' . $session_charset);
?>
<div id='findplaceresdiv'>
  <table class='table table-sm'>
    <tr>
      <td>
        <h4><?php echo uiTextSnippet('searchresults'); ?></h4>
        <span>(<?php echo uiTextSnippet('clicktoselect'); ?>)</span>
      </td>
      <td></td>
      <td>
        <form action=''>
          <input type='button' value="<?php echo uiTextSnippet('find'); ?>" onclick="reopenFindForm();">
        </form>
      </td>
    </tr>
  </table>
  <table class='table table-sm'>
    <?php
    while ($row = tng_fetch_assoc($result)) {
      $row['place'] = str_replace("'", '&#39;', $row['place']);
      $notes = $row['temple'] && $row['notes'] ? ' (' . truncateIt($row['notes'], 75) . ')' : '';
      echo "<tr>\n";
      echo "<td>\n";
      echo '<span>';
      echo "<a href='findplace.php' onClick='return returnValue(\"" . addslashes($row['place']) . "\");'>{$row['place']}</a>$notes\n";
      echo "</span>\n";
      echo "</td>\n";
      echo "</tr>\n";
    }
    tng_free_result($result);
    ?>
  </table>
</div>
