<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
include("prefixes.php");
include("version.php");

if ($assignedbranch) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

function getBranchCount($tree, $branch, $table) {
  $query = "SELECT count(ID) as count FROM $table WHERE gedcom = \"$tree\" and branch LIKE \"%$branch%\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $count = $row['count'];
  if (!$count) {
    $count = "0";
  }
  tng_free_result($result);

  return $count;
}

$tng_search_branches = $_SESSION['tng_search_branches'] = 1;
if ($newsearch) {
  $exptime = 05;
  $searchstring = stripslashes(trim($searchstring));
  setcookie("tng_search_branches_post[search]", $searchstring, $exptime);
  setcookie("tng_search_branches_post[tree]", $tree, $exptime);
  setcookie("tng_search_branches_post[tngpage]", 1, $exptime);
  setcookie("tng_search_branches_post[offset]", 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = $_COOKIE['tng_search_branches_post']['search'];
  }
  if (!$tree) {
    $tree = $_COOKIE['tng_search_branches_post']['tree'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_branches_post']['tngpage'];
    $offset = $_COOKIE['tng_search_branches_post']['offset'];
  } else {
    $exptime = 0;
    setcookie("tng_search_branches_post[tngpage]", $tngpage, $exptime);
    setcookie("tng_search_branches_post[offset]", $offset, $exptime);
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
$treequery = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";

$wherestr = $searchstring ? "WHERE (branch LIKE \"%$searchstring%\" OR $branches_table.description LIKE \"%$searchstring%\")" : "";
if ($tree) {
  $wherestr .= $wherestr ? " AND $branches_table.gedcom = \"$tree\"" : "WHERE $branches_table.gedcom = \"$tree\"";
}
$query = "SELECT $branches_table.gedcom as gedcom, branch, $branches_table.description as description, personID, treename FROM $branches_table LEFT JOIN $trees_table ON $trees_table.gedcom = $branches_table.gedcom $wherestr ORDER BY $branches_table.description LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(branch) as bcount FROM $branches_table LEFT JOIN $trees_table ON $trees_table.gedcom = $branches_table.gedcom $wherestr";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['bcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('branches'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='admin-branches'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('branches', $message);
    $navList = new navList('');
//    $navList->appendItem([true, "branchesBrowse.php", uiTextSnippet('browse'), "findbranch"]);
    $navList->appendItem([$allowAdd, "branchesAdd.php", uiTextSnippet('add'), "addbranch"]);
    echo $navList->build("findbranch");
    ?>
    <form id='form1' name='form1' action='branchesBrowse.php'>
      <label class='form-control-label' for='searchstring'><?php echo uiTextSnippet('searchfor'); ?>:</label>
      <select name='tree'>
        <?php
        if (!$assignedtree) {
          echo "  <option value=''>" . uiTextSnippet('alltrees') . "</option>\n";
        }
        $treeresult = tng_query($treequery) or die(uiTextSnippet('cannotexecutequery') . ": $treequery");
        while ($treerow = tng_fetch_assoc($treeresult)) {
          echo "  <option value=\"{$treerow['gedcom']}\"";
          if ($treerow['gedcom'] == $tree) {
            echo " selected";
          }
          echo ">{$treerow['treename']}</option>\n";
        }
        tng_free_result($treeresult);
        ?>
      </select>
      <input name='searchstring' type='text' value="<?php echo $searchstring_noquotes; ?>">

      <input name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>"
                   onClick="document.form1.searchstring.value = ''; document.form1.tree.selectedIndex = 0;">
      <input name='findbranch' type='hidden' value='1'>
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
      <?php if ($allowDelete) { ?>
        <p>
          <input name='selectall' type='button' value="<?php echo uiTextSnippet('selectall'); ?>" 
                 onClick="toggleAll(1);">
          <input name='clearall' type='button' value="<?php echo uiTextSnippet('clearall'); ?>"
                 onClick="toggleAll(0);">
          <input name='xbranchaction' type='submit' value="<?php echo uiTextSnippet('deleteselected'); ?>"
                 onClick="return confirm('<?php echo uiTextSnippet('confdeleterecs'); ?>');">
        </p>
      <?php } ?>
      <table class="table table-sm table-striped">
        <thead>
          <tr>
            <th><?php echo uiTextSnippet('action'); ?></th>
            <?php if ($allowDelete) { ?>
              <th><?php echo uiTextSnippet('select'); ?></th>
            <?php } ?>
            <th><?php echo uiTextSnippet('id'); ?></th>
            <th><?php echo uiTextSnippet('description'); ?></th>
            <th><?php echo uiTextSnippet('tree'); ?></th>
            <th><?php echo uiTextSnippet('startingind'); ?></th>
            <th><?php echo uiTextSnippet('people'); ?></th>
            <th><?php echo uiTextSnippet('families'); ?></th>
          </tr>
        </thead>
        <?php
        if ($numrows) {
          $actionstr = "";
          if ($allowEdit) {
            $actionstr .= "<a href=\"branchesEdit.php?branch=xxx&amp;tree=yyy\" title='" . uiTextSnippet('edit') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
            $actionstr .= "</a>\n";
          }
          if ($allowDelete) {
            if (!$assignedtree) {
              $actionstr .= "<a id='delete' data-branch='xxx' data-tree='yyy' href='#' title='" . uiTextSnippet('delete') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
              $actionstr .= "</a>\n";
            }
          }
          while ($row = tng_fetch_assoc($result)) {
            $newactionstr = str_replace("xxx", $row['branch'], $actionstr);
            $newactionstr = str_replace("yyy", $row['gedcom'], $newactionstr);
            echo "<tr id=\"row_{$row['branch']}\">\n";
            echo "<td>\n";
            echo "<div>\n$newactionstr</div>\n";
            echo "</td>\n";
            if ($allowDelete) {
              echo "<td><input name=\"del{$row['branch']}&amp;{$row['gedcom']}\" type='checkbox' value='1'></td>";
            }
            $editlink = "branchesEdit.php?branch={$row['branch']}&tree={$row['gedcom']}";
            $id = $allowEdit ? "<a href=\"$editlink\" title='" . uiTextSnippet('edit') . "'>" . $row['branch'] . "</a>" : $row['branch'];

            echo "<td>$id</td>\n";
            echo "<td>&nbsp;{$row['description']}</td>\n";
            echo "<td>{$row['treename']}</td>\n";

            $pcount = getBranchCount($row['gedcom'], $row['branch'], $people_table);
            $fcount = getBranchCount($row['gedcom'], $row['branch'], $families_table);

            echo "<td>{$row['personID']}&nbsp;</td>\n";
            echo "<td>$pcount&nbsp;</td>\n";
            echo "<td>$fcount&nbsp;</td>\n";
            echo "</tr>\n";
          }
          tng_free_result($result);
          ?>
        </table>
        <?php
        echo buildSearchResultPagination($totrows, "branchesBrowse.php?searchstring=$searchstring&amp;offset", $maxsearchresults, 5);
      } else {
        echo uiTextSnippet('notrees');
      }
      ?>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src='js/admin.js'></script>
<script>
$('#admin-branches #delete').on('click', function () {
    'use strict';
    var branch = $(this).data('branch');
    var tree = $(this).data('tree');
    if (confirm(textSnippet('confbranchdelete'))) {
        deleteIt('branch', branch, tree);
    }
    return false;
});
</script>
</body>
</html>