<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

$tng_search_trees = $_SESSION['tng_search_trees'] = 1;
if ($newsearch) {
  $exptime = 0;
  setcookie("tng_search_trees_post[search]", $searchstring, $exptime);
  setcookie("tng_search_trees_post[tngpage]", 1, $exptime);
  setcookie("tng_search_trees_post[offset]", 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = $_COOKIE['tng_search_trees_post']['search'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_trees_post']['tngpage'];
    $offset = $_COOKIE['tng_search_trees_post']['offset'];
  } else {
    $exptime = 0;
    setcookie("tng_search_trees_post[tngpage]", $tngpage, $exptime);
    setcookie("tng_search_trees_post[offset]", $offset, $exptime);
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

$wherestr = $searchstring ? "WHERE (gedcom LIKE \"%$searchstring%\" OR treename LIKE \"%$searchstring%\" OR description LIKE \"%$searchstring%\" OR owner LIKE \"%$searchstring%\")" : "";
if ($assignedtree) {
  $wherestr .= $wherestr ? " AND gedcom = \"$assignedtree\"" : "WHERE gedcom = \"$assignedtree\"";
}

$query = "SELECT gedcom, treename, description, owner, DATE_FORMAT(lastimportdate,\"%d %b %Y %H:%i:%s\") as lastimportdate, importfilename FROM $trees_table $wherestr ORDER BY treename LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(gedcom) as tcount FROM $trees_table $wherestr";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['tcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('trees'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='admin-trees'>
  <section class='container'>
    <?php
    $allow_add_tree = $assignedtree ? 0 : $allow_add;
    echo $adminHeaderSection->build('trees', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_trees.php", uiTextSnippet('search'), "findtree"]);
    $navList->appendItem([$allow_add_tree, "admin_newtree.php", uiTextSnippet('addnew'), "addtree"]);
    echo $navList->build("findtree");
    ?>
    <div>
      <form action="admin_trees.php" name='form1'>
        <div class='form-group'>
          <label for='searchstring'><?php echo uiTextSnippet('searchfor'); ?>: </label>
          <input class='form-control' name='searchstring' type='text' value="<?php echo $searchstring; ?>">
        </div> <!-- .form-group -->
        <input name='findtree' type='hidden' value='1'>
        <input name='newsearch' type='hidden' value='1'>
        <input class='btn btn-secondary' name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
        <input class='btn btn-warning' name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>" onClick="resetTreesSearch();">
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
            <th><?php echo uiTextSnippet('id'); ?></th>
            <th><?php echo uiTextSnippet('treename'); ?></th>
            <th><?php echo uiTextSnippet('description'); ?></th>
            <th><?php echo uiTextSnippet('people'); ?></th>
            <th><?php echo uiTextSnippet('owner'); ?></th>
            <th><?php echo uiTextSnippet('lastimport'); ?></th>
            <th><?php echo uiTextSnippet('importfilename'); ?></th>
          </tr>

          <?php
          $actionstr = "";
          if ($allow_edit && !$assignedbranch) {
            $actionstr .= "<a href=\"admin_edittree.php?tree=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
            $actionstr .= "</a>\n";
          }
          if ($allow_delete && !$assignedbranch) {
            if (!$assignedtree) {
              $actionstr .= "<a href='#' onClick=\"if(confirm('" . uiTextSnippet('conftreedelete') . "' )){deleteIt('tree','xxx');} return false;\" title='" . uiTextSnippet('delete') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
              $actionstr .= "</a>\n";
            }
            $actionstr .= "<a href=\"admin_cleartree.php?tree=xxx\" onClick=\"return confirm('" . uiTextSnippet('conftreeclear') . "' );\" title=\"" . uiTextSnippet('clear') . "\">\n";
            $actionstr .= "<img class='icon-sm' src='svg/axe.svg'>\n";
            $actionstr .= "</a>";
          }
          while ($row = tng_fetch_assoc($result)) {
            $newactionstr = preg_replace("/xxx/", $row['gedcom'], $actionstr);
            $editlink = "admin_edittree.php?tree={$row['gedcom']}";
            $gedcom = $allow_edit ? "<a href=\"$editlink\" title='" . uiTextSnippet('edit') . "'>" . $row['gedcom'] . "</a>" : $row['gedcom'];

            $query = "SELECT count(personID) as pcount FROM $people_table WHERE gedcom = \"{$row['gedcom']}\"";
            $result2 = tng_query($query);
            $prow = tng_fetch_assoc($result2);
            $pcount = number_format($prow['pcount']);
            tng_free_result($result2);

            echo "<tr id=\"row_{$row['gedcom']}\"><td><div class=\"action-btns\">$newactionstr</div></td>\n";
            echo "<td>$gedcom</td>\n";
            echo "<td>{$row['treename']}</td>\n";
            echo "<td>{$row['description']}</td>\n";
            echo "<td align=\"right\">$pcount</td>\n";
            echo "<td>{$row['owner']}</td>\n";
            echo "<td>{$row['lastimportdate']}</td>\n";
            echo "<td>{$row['importfilename']}</td>\n";
            echo "</tr>\n";
          }
          tng_free_result($result);
          ?>
        </table>
        <?php
        echo buildSearchResultPagination($totrows, "admin_trees.php?searchstring=$searchstring&amp;offset", $maxsearchresults, 5);
      } else {
        echo uiTextSnippet('notrees');
      }
      ?>
    </div>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script>
    function resetTreesSearch() {
      document.form1.searchstring.value = '';
    }
  </script>
  <script src="js/admin.js"></script>
</body>
</html>
