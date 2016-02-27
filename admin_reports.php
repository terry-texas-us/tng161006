<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if ($assignedtree) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

$tng_search_tlevents = $_SESSION['tng_search_reports'] = 1;
if ($newsearch) {
  $exptime = 0;
  $searchstring = stripslashes(trim($searchstring));
  setcookie("tng_search_reports_post[search]", $searchstring, $exptime);
  setcookie("tng_search_reports_post[tngpage]", 1, $exptime);
  setcookie("tng_search_reports_post[offset]", 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = $_COOKIE['tng_search_reports_post']['search'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_reports_post']['tngpage'];
    $offset = $_COOKIE['tng_search_reports_post']['offset'];
  } else {
    $exptime = 0;
    setcookie("tng_search_reports_post[tngpage]", $tngpage, $exptime);
    setcookie("tng_search_reports_post[offset]", $offset, $exptime);
  }
}

if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $tngpage = 1;
}

$wherestr = $searchstring ? "WHERE reportname LIKE \"%$searchstring%\" OR reportdesc LIKE \"%$searchstring%\"" : "";
$query = "SELECT reportID, reportname, reportdesc, rank, active FROM $reports_table $wherestr ORDER BY rank, reportname, reportID LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(reportID) as rcount FROM $reports_table $wherestr";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['rcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('reports'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('reports', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_reports.php", uiTextSnippet('search'), "findreport"]);
    $navList->appendItem([$allow_add, "admin_newreport.php", uiTextSnippet('addnew'), "addreport"]);
    echo $navList->build("findreport");
    ?>
    <div>
      <form action="admin_reports.php" name='form1'>
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
            <th><?php echo uiTextSnippet('rank'); ?></th>
            <th><?php echo uiTextSnippet('id'); ?></th>
            <th><?php echo uiTextSnippet('name') . ", " . uiTextSnippet('description'); ?></th>
            <th><?php echo uiTextSnippet('active'); ?>?</th>
          </tr>

          <?php
          $actionstr = "";
          if ($allow_edit) {
            $actionstr .= "<a href=\"admin_editreport.php?reportID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
            $actionstr .= "</a>";
          }
          if ($allow_delete) {
            $actionstr .= "<a href='#' onClick=\"return confirmDelete('xxx');\" title='" . uiTextSnippet('delete') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
            $actionstr .= "</a>";
          }
          $actionstr .= "<a href=\"showreport.php?reportID=xxx&amp;test=1\" title='" . uiTextSnippet('preview') . "'>\n";
          $actionstr .= "<img class='icon-sm' src='svg/eye.svg'>\n";
          $actionstr .= "</a>\n";

          while ($row = tng_fetch_assoc($result)) {
            $active = $row['active'] ? uiTextSnippet('yes') : uiTextSnippet('no');
            $newactionstr = preg_replace("/xxx/", $row['reportID'], $actionstr);
            $editlink = "admin_editreport.php?reportID={$row['reportID']}";
            $id = $allow_edit ? "<a href=\"$editlink\" title='" . uiTextSnippet('edit') . "'>" . $row['reportID'] . "</a>" : $row['reportID'];
            $name = $allow_edit ? "<a href=\"$editlink\" title='" . uiTextSnippet('edit') . "'>" . $row['reportname'] . "</a>" : $row['reportname'];

            echo "<tr id=\"row_{$row['reportID']}\"><td><div class=\"action-btns\">$newactionstr</div></td>\n";
            echo "<td>{$row['rank']}</td>\n";
            echo "<td>$id</td>\n";
            echo "<td>$name<br>{$row['reportdesc']}</td>\n";
            echo "<td>$active</td></tr>\n";
          }
          ?>
        </table>
        <?php
        echo buildSearchResultPagination($totrows, "admin_reports.php?searchstring=$searchstring&amp;offset", $maxsearchresults, 5);
      } else {
        echo uiTextSnippet('norecords');
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
