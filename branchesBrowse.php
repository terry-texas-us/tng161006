<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'prefixes.php';
require 'version.php';

if ($assignedbranch) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}

function getBranchCount($branch, $table) {
  $query = "SELECT count(ID) AS count FROM $table WHERE branch LIKE \"%$branch%\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $count = $row['count'];
  if (!$count) {
    $count = '0';
  }
  tng_free_result($result);

  return $count;
}

$tng_search_branches = $_SESSION['tng_search_branches'] = 1;
if ($newsearch) {
  $exptime = 05;
  $searchstring = stripslashes(trim($searchstring));
  setcookie('tng_search_branches_post[search]', $searchstring, $exptime);
  setcookie('tng_search_branches_post[tngpage]', 1, $exptime);
  setcookie('tng_search_branches_post[offset]', 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = $_COOKIE['tng_search_branches_post']['search'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_branches_post']['tngpage'];
    $offset = $_COOKIE['tng_search_branches_post']['offset'];
  } else {
    $exptime = 0;
    setcookie('tng_search_branches_post[tngpage]', $tngpage, $exptime);
    setcookie('tng_search_branches_post[offset]', $offset, $exptime);
  }
}
$searchstring_noquotes = preg_replace('/\"/', '&#34;', $searchstring);
$searchstring = addslashes($searchstring);

if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = '';
  $tngpage = 1;
}
$whereClause = $searchstring ? "WHERE (branch LIKE '%$searchstring%' OR branches.description LIKE '%$searchstring%')" : '';
$query = "SELECT branch, branches.description AS description, personID FROM branches $whereClause ORDER BY branches.description LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(branch) AS bcount FROM branches $whereClause";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['bcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('branches'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
<body id='admin-branches'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('branches', $message);
    $navList = new navList('');
    //    $navList->appendItem([true, 'branchesBrowse.php', uiTextSnippet('browse'), 'findbranch']);
    $navList->appendItem([$allowAdd, 'branchesAdd.php', uiTextSnippet('add'), 'addbranch']);
    echo $navList->build('findbranch');
    ?>
    <form id='form1' name='form1' action='branchesBrowse.php'>
      <input name='searchstring' type='text' value="<?php echo $searchstring_noquotes; ?>">

      <input name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>"
                   onClick="document.form1.searchstring.value = '';">
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
            <th><?php echo uiTextSnippet('startingind'); ?></th>
            <th><?php echo uiTextSnippet('people'); ?></th>
            <th><?php echo uiTextSnippet('families'); ?></th>
          </tr>
        </thead>
        <?php
        if ($numrows) {
          $actionstr = '';
          if ($allowEdit) {
            $actionstr .= "<a href=\"branchesEdit.php?branch=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
            $actionstr .= "</a>\n";
          }
          if ($allowDelete) {
            $actionstr .= "<a id='delete' data-branch='xxx' href='#' title='" . uiTextSnippet('delete') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
            $actionstr .= "</a>\n";
          }
          while ($row = tng_fetch_assoc($result)) {
            $newactionstr = str_replace('xxx', $row['branch'], $actionstr);
            echo "<tr id=\"row_{$row['branch']}\">\n";
            echo "<td>\n";
            echo "<div>\n$newactionstr</div>\n";
            echo "</td>\n";
            if ($allowDelete) {
              echo "<td><input name=\"del{$row['branch']}\" type='checkbox' value='1'></td>";
            }
            $editlink = "branchesEdit.php?branch={$row['branch']}";
            $id = $allowEdit ? "<a href=\"$editlink\" title='" . uiTextSnippet('edit') . "'>" . $row['branch'] . '</a>' : $row['branch'];

            echo "<td>$id</td>\n";
            echo "<td>&nbsp;{$row['description']}</td>\n";

            $pcount = getBranchCount($row['branch'], 'people');
            $fcount = getBranchCount($row['branch'], 'families');

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
    if (confirm(textSnippet('confbranchdelete'))) {
        deleteIt('branch', branch);
    }
    return false;
});
</script>
</body>
</html>