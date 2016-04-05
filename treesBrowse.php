<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

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
$query = "SELECT gedcom, treename, description, owner, DATE_FORMAT(lastimportdate,\"%d %b %Y %H:%i:%s\") as lastimportdate, importfilename FROM $treesTable $wherestr ORDER BY treename LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(gedcom) as tcount FROM $treesTable $wherestr";
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
    $allow_add_tree = $assignedtree ? 0 : $allowAdd;
    echo $adminHeaderSection->build('trees', $message);
    $navList = new navList('');
    //    $navList->appendItem([true, 'treesBrowse.php', uiTextSnippet('search'), 'findtree']);
    $navList->appendItem([$allow_add_tree, "treesAdd.php", uiTextSnippet('add'), "addtree"]);
    echo $navList->build("findtree");
    ?>
    <form action='treesBrowse.php' name='form1'>
      <label for='searchstring'><?php echo uiTextSnippet('searchfor'); ?>: </label>
      <div class='row form-group'>
        <div class='col-sm-6'>
          <div class='input-group'>
            <input class='form-control' name='searchstring' type='text' value="<?php echo $searchstring; ?>">
            <span class='input-group-btn'>
              <button class='btn btn-primary-outline' name='submit' type='submit'><?php echo uiTextSnippet('search'); ?></button>
            </span>
          </div>
        </div>
        <div class='col-sm-2'>
          <button class='btn btn-secondary-outline' id='trees-search-reset' name='submit' type='submit'><?php echo uiTextSnippet('reset'); ?></button>
        </div>
      </div>
      <input name='findtree' type='hidden' value='1'>
      <input name='newsearch' type='hidden' value='1'>
    </form>
    <hr>
    <?php
    $numrowsplus = $numrows + $offset;
    if (!$numrowsplus) {
      $offsetplus = 0;
    }
    echo displayListLocation($offsetplus, $numrowsplus, $totrows);
    if ($numrows) {
    ?>
      <table class="table table-sm table-striped">
        <thead>
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
        </thead>
        <?php
        $actionstr = "";
        if ($allowEdit && !$assignedbranch) {
          $actionstr .= "<a href='treesEdit.php?tree=xxx' title='" . uiTextSnippet('edit') . "'>\n";
          $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
          $actionstr .= "</a>\n";
        }
        if ($allowDelete && !$assignedbranch) {
          if (!$assignedtree) {
            $actionstr .= "<a href='#' onClick=\"if(confirm('" . uiTextSnippet('conftreedelete') . "' )){deleteIt('tree','xxx');} return false;\" title='" . uiTextSnippet('delete') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
            $actionstr .= "</a>\n";
          }
          $actionstr .= "<a href=\"treesClear.php?tree=xxx\" onClick=\"return confirm('" . uiTextSnippet('conftreeclear') . "' );\" title=\"" . uiTextSnippet('clear') . "\">\n";
          $actionstr .= "<img class='icon-sm' src='svg/axe.svg'>\n";
          $actionstr .= "</a>";
        }
        while ($row = tng_fetch_assoc($result)) {
          $newactionstr = preg_replace("/xxx/", $row['gedcom'], $actionstr);
          $editlink = "treesEdit.php?tree={$row['gedcom']}";
          $gedcom = $allowEdit ? "<a href=\"$editlink\" title='" . uiTextSnippet('edit') . "'>" . $row['gedcom'] . "</a>" : $row['gedcom'];

          $query = "SELECT count(personID) as pcount FROM $people_table WHERE gedcom = \"{$row['gedcom']}\"";
          $result2 = tng_query($query);
          $prow = tng_fetch_assoc($result2);
          $pcount = number_format($prow['pcount']);
          tng_free_result($result2);

          echo "<tr id=\"row_{$row['gedcom']}\">\n";
            echo "<td><div class='action-btns'>$newactionstr</div></td>\n";
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
      echo buildSearchResultPagination($totrows, "treesBrowse.php?searchstring=$searchstring&amp;offset", $maxsearchresults, 5);
    } else {
      echo "<div class='alert alert-warning'>" . uiTextSnippet('notrees') . "</div>";
    }
    ?>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/admin.js"></script>
<script src='js/trees.js'></script>
</body>
</html>
