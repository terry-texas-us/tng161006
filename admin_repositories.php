<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if ($newsearch) {
  $exptime = 0;
  setcookie("tng_search_repos_post[search]", $searchstring, $exptime);
  setcookie("tng_search_repos_post[tree]", $tree, $exptime);
  setcookie("tng_search_repos_post[exactmatch]", $exactmatch, $exptime);
  setcookie("tng_search_repos_post[tngpage]", 1, $exptime);
  setcookie("tng_search_repos_post[offset]", 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = stripslashes($_COOKIE['tng_search_repos_post']['search']);
  }
  if (!$tree) {
    $tree = $_COOKIE['tng_search_repos_post']['tree'];
  }
  if (!$exactmatch) {
    $exactmatch = $_COOKIE['tng_search_repos_post']['exactmatch'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_repos_post']['tngpage'];
    $offset = $_COOKIE['tng_search_repos_post']['offset'];
  } else {
    $exptime = 0;
    setcookie("tng_search_repos_post[tngpage]", $tngpage, $exptime);
    setcookie("tng_search_repos_post[offset]", $offset, $exptime);
  }
}
$searchstring_noquotes = preg_replace("/\"/", "&#34;", $searchstring);
$searchstring = addslashes($searchstring);

if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $tngpage = 1;
}

if ($assignedtree) {
  $wherestr = "WHERE gedcom = \"$assignedtree\"";
  $tree = $assignedtree;
} else {
  $wherestr = "";
}
$orgtree = $tree;

function addCriteria($field, $value, $operator) {
  $criteria = "";

  if ($operator == "=") {
    $criteria = " OR $field $operator \"$value\"";
  } else {
    $innercriteria = "";
    $terms = explode(' ', $value);
    foreach ($terms as $term) {
      if ($innercriteria) {
        $innercriteria .= " AND ";
      }
      $innercriteria .= "$field $operator \"%$term%\"";
    }
    if ($innercriteria) {
      $criteria = " OR ($innercriteria)";
    }
  }
  return $criteria;
}

if ($tree) {
  $allwhere = "$repositories_table.gedcom = \"$tree\" AND $repositories_table.gedcom = $trees_table.gedcom";
} else {
  $allwhere = "$repositories_table.gedcom = $trees_table.gedcom";
}
if ($searchstring) {
  $allwhere .= " AND (1=0 ";
  if ($exactmatch == "yes") {
    $frontmod = "=";
  } else {
    $frontmod = "LIKE";
  }

  $allwhere .= addCriteria("repoID", $searchstring, $frontmod);
  $allwhere .= addCriteria("reponame", $searchstring, $frontmod);
  $allwhere .= ")";
}
$query = "SELECT ID, repoID, reponame, $repositories_table.gedcom as gedcom, treename FROM ($repositories_table, $trees_table) WHERE $allwhere ORDER BY reponame, repoID LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(repoID) as rcount FROM ($repositories_table, $trees_table) WHERE $allwhere";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['rcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('repositories'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='admin-repositories'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('repositories', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_repositories.php", uiTextSnippet('search'), "findrepo"]);
    $navList->appendItem([$allow_add, "admin_newrepo.php", uiTextSnippet('addnew'), "addrepo"]);
    $navList->appendItem([$allow_edit && $allow_delete, "admin_mergerepos.php", uiTextSnippet('merge'), "merge"]);
    echo $navList->build("findrepo");
    ?>
    <div>
      <form id='form1' name='form1' action='admin_repositories.php'>
        <?php include '_/components/php/treeSelectControl.php'; ?>
        <label for='searchstring'><?php echo uiTextSnippet('searchfor'); ?></label>
        <input name='searchstring' type='text' value="<?php echo $searchstring_noquotes; ?>">
        <input name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
        <input name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>"
            onClick="resetRepositioriesSearch();">
        <span>
          <input name='exactmatch' type='checkbox' value='yes'<?php if ($exactmatch == "yes") {echo " checked";} ?>>
          <?php echo uiTextSnippet('exactmatch'); ?>
        </span>
        <input name='findrepo' type='hidden' value='1'>
        <input name='newsearch' type='hidden' value='1'>
      </form>
      <br>
      <?php
      $numrowsplus = $numrows + $offset;
      if (!$numrowsplus) {
        $offsetplus = 0;
      }
      echo displayListLocation($offsetplus, $numrowsplus, $totrows);
      ?>
      <form action="admin_deleteselected.php" method='post' name="form2">
        <?php if ($allow_delete) { ?>
          <p>
            <input name='selectall' type='button' value="<?php echo uiTextSnippet('selectall'); ?>" 
                onClick="toggleAll(1);">
            <input name='clearall' type='button' value="<?php echo uiTextSnippet('clearall'); ?>"
                onClick="toggleAll(0);">
            <input name='xrepoaction' type='submit' value="<?php echo uiTextSnippet('deleteselected'); ?>"
                onClick="return confirm('<?php echo uiTextSnippet('confdeleterecs'); ?>');">
          </p>
        <?php
        }
        if ($numrows) {
        ?>
          <table class="table table-sm table-striped">
            <tr>
              <th><?php echo uiTextSnippet('action'); ?></th>
              <?php if ($allow_delete) { ?>
                <th><?php echo uiTextSnippet('select'); ?></th>
              <?php } ?>
              <th><?php echo uiTextSnippet('repoid'); ?></th>
              <th><?php echo uiTextSnippet('name'); ?></th>
              <?php if ($numtrees > 1) { ?>
                <th><?php echo uiTextSnippet('tree'); ?></th>
              <?php } ?>
            </tr>
            <?php
            $actionstr = "";
            if ($allow_edit) {
              $actionstr .= "<a href=\"admin_editrepo.php?repoID=xxx&amp;tree=yyy\" title='" . uiTextSnippet('edit') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
              $actionstr .= "</a>";
            }
            if ($allow_delete) {
              $actionstr .= "<a href='#' onclick=\"return confirmDelete('zzz');\" title='" . uiTextSnippet('delete') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
              $actionstr .= "</a>";
            }
            $actionstr .= "<a href=\"showrepo.php?repoID=xxx&amp;tree=yyy\" title='" . uiTextSnippet('preview') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/eye.svg'>\n";
            $actionstr .= "</a>\n";

            while ($row = tng_fetch_assoc($result)) {
              $newactionstr = preg_replace("/xxx/", $row['repoID'], $actionstr);
              $newactionstr = preg_replace("/yyy/", $row['gedcom'], $newactionstr);
              $newactionstr = preg_replace("/zzz/", $row['ID'], $newactionstr);
              $editlink = "admin_editrepo.php?repoID={$row['repoID']}&amp;tree={$row['gedcom']}";
              $id = $allow_edit ? "<a href=\"$editlink\" title='" . uiTextSnippet('edit') . "'>" . $row['repoID'] . "</a>" : $row['repoID'];

              echo "<tr id=\"row_{$row['ID']}\"><td><div class=\"action-btns\">$newactionstr</div></td>\n";
              if ($allow_delete) {
                echo "<td><input name=\"del{$row['ID']}\" type='checkbox' value='1'></td>";
              }
              echo "<td>$id</td>\n";
              echo "<td>{$row['reponame']}</td>\n";
              if ($numtrees > 1) {
                echo "<td>{$row['treename']}</td>\n";
              }
              echo "</tr>\n";
            }
            ?>
          </table>
          <?php
          echo buildSearchResultPagination($totrows, "admin_repositories.php?searchstring=$searchstring&amp;exactmatch=$exactmatch&amp;offset", $maxsearchresults, 5);
        } else {
          echo uiTextSnippet('norecords');
        }
        tng_free_result($result);
        ?>
      </form>
    </div>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/admin.js"></script>
  <script>
    function confirmDelete(ID) {
      if (confirm(textSnippet('confdeleterepo')))
        deleteIt('repository', ID);
      return false;
    }

    function resetRepositioriesSearch() {
      document.form1.searchstring.value = '';
      document.form1.tree.selectedIndex = 0;
      document.form1.exactmatch.checked = false;
    }
  </script>
</body>
</html>
