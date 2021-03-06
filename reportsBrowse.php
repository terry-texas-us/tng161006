<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

$tng_search_tlevents = $_SESSION['tng_search_reports'] = 1;
if ($newsearch) {
  $exptime = 0;
  $searchstring = stripslashes(trim($searchstring));
  setcookie('tng_search_reports_post[search]', $searchstring, $exptime);
  setcookie('tng_search_reports_post[tngpage]', 1, $exptime);
  setcookie('tng_search_reports_post[offset]', 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = $_COOKIE['tng_search_reports_post']['search'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_reports_post']['tngpage'];
    $offset = $_COOKIE['tng_search_reports_post']['offset'];
  } else {
    $exptime = 0;
    setcookie('tng_search_reports_post[tngpage]', $tngpage, $exptime);
    setcookie('tng_search_reports_post[offset]', $offset, $exptime);
  }
}

if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = '';
  $tngpage = 1;
}

$wherestr = $searchstring ? "WHERE reportname LIKE \"%$searchstring%\" OR reportdesc LIKE \"%$searchstring%\"" : '';
$query = "SELECT reportID, reportname, reportdesc, ranking, active FROM reports $wherestr ORDER BY ranking, reportname, reportID LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(reportID) AS rcount FROM reports $wherestr";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['rcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('reports'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('reports', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'reportsBrowse.php', uiTextSnippet('browse'), 'findreport']);
    $navList->appendItem([$allowAdd, 'reportsAdd.php', uiTextSnippet('add'), 'addreport']);
    echo $navList->build('findreport');
    ?>
    <div>
      <form action="reportsBrowse.php" name='form1'>
        <?php echo uiTextSnippet('searchfor'); ?>: <input class='longfield' name='searchstring' type='text' value="<?php echo $searchstring; ?>">
        <input name='findreport' type='hidden' value='1'>
        <input name='newsearch' type='hidden' value='1'>
        <input name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
        <input name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>"
               onClick="document.form1.searchstring.value = '';">
      </form>
      <?php
      $numrowsplus = $numrows + $offset;
      if (!$numrowsplus) {
        $offsetplus = 0;
      }
      echo displayListLocation($offsetplus, $numrowsplus, $totrows);
      if ($numrows) {
      ?>
        <table class="table table-sm table-striped">
          <tr>
            <th><?php echo uiTextSnippet('action'); ?></th>
            <th><?php echo uiTextSnippet('ranking'); ?></th>
            <th><?php echo uiTextSnippet('id'); ?></th>
            <th><?php echo uiTextSnippet('name') . ', ' . uiTextSnippet('description'); ?></th>
            <th><?php echo uiTextSnippet('active'); ?>?</th>
          </tr>

          <?php
          $actionstr = '';
          if ($allowEdit) {
            $actionstr .= "<a href=\"reportsEdit.php?reportID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
            $actionstr .= '</a>';
          }
          if ($allowDelete) {
            $actionstr .= "<a href='#' onClick=\"return confirmDelete('xxx');\" title='" . uiTextSnippet('delete') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
            $actionstr .= '</a>';
          }
          $actionstr .= "<a href=\"reportsShowReport.php?reportID=xxx&amp;test=1\" title='" . uiTextSnippet('preview') . "'>\n";
          $actionstr .= "<img class='icon-sm' src='svg/eye.svg'>\n";
          $actionstr .= "</a>\n";

          while ($row = tng_fetch_assoc($result)) {
            $active = $row['active'] ? uiTextSnippet('yes') : uiTextSnippet('no');
            $newactionstr = preg_replace('/xxx/', $row['reportID'], $actionstr);
            $editlink = "reportsEdit.php?reportID={$row['reportID']}";
            $id = $allowEdit ? "<a href=\"$editlink\" title='" . uiTextSnippet('edit') . "'>" . $row['reportID'] . '</a>' : $row['reportID'];
            $name = $allowEdit ? "<a href=\"$editlink\" title='" . uiTextSnippet('edit') . "'>" . $row['reportname'] . '</a>' : $row['reportname'];

            echo "<tr id=\"row_{$row['reportID']}\"><td><div class=\"action-btns\">$newactionstr</div></td>\n";
            echo "<td>{$row['ranking']}</td>\n";
            echo "<td>$id</td>\n";
            echo "<td>$name<br>{$row['reportdesc']}</td>\n";
            echo "<td>$active</td></tr>\n";
          }
          ?>
        </table>
        <?php
        echo buildSearchResultPagination($totrows, "reportsBrowse.php?searchstring=$searchstring&amp;offset", $maxsearchresults, 5);
      } else {
        echo "<div class='alert alert-warning'>" . uiTextSnippet('norecords') . "</div>\n";
      }
      tng_free_result($result);
      ?>
    </div>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script>
  function confirmDelete(ID) {
    if (confirm(textSnippet('confreportdelete')))
      deleteIt('report', ID);
    return false;
  }
</script>
<script src="js/admin.js"></script>
</body>
</html>
