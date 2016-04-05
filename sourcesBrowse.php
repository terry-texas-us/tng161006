<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if ($newsearch) {
  $exptime = 0;
  setcookie("tng_search_sources_post[search]", $searchstring, $exptime);
  setcookie("tng_search_sources_post[tree]", $tree, $exptime);
  setcookie("tng_search_sources_post[exactmatch]", $exactmatch, $exptime);
  setcookie("tng_search_sources_post[tngpage]", 1, $exptime);
  setcookie("tng_search_sources_post[offset]", 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = stripslashes($_COOKIE['tng_search_sources_post']['search']);
  }
  if (!$tree) {
    $tree = $_COOKIE['tng_search_sources_post']['tree'];
  }
  if (!$exactmatch) {
    $exactmatch = $_COOKIE['tng_search_sources_post']['exactmatch'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_sources_post']['tngpage'];
    $offset = $_COOKIE['tng_search_sources_post']['offset'];
  } else {
    $exptime = 0;
    setcookie("tng_search_sources_post[tngpage]", $tngpage, $exptime);
    setcookie("tng_search_sources_post[offset]", $offset, $exptime);
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
  $allwhere = "$sources_table.gedcom = \"$tree\" AND $sources_table.gedcom = $treesTable.gedcom";
} else {
  $allwhere = "$sources_table.gedcom = $treesTable.gedcom";
}

if ($searchstring) {
  $allwhere .= " AND (1=0 ";
  if ($exactmatch == "yes") {
    $frontmod = "=";
  } else {
    $frontmod = "LIKE";
  }

  $allwhere .= addCriteria("sourceID", $searchstring, $frontmod);
  $allwhere .= addCriteria("shorttitle", $searchstring, $frontmod);
  $allwhere .= addCriteria("title", $searchstring, $frontmod);
  $allwhere .= addCriteria("author", $searchstring, $frontmod);
  $allwhere .= addCriteria("callnum", $searchstring, $frontmod);
  $allwhere .= addCriteria("publisher", $searchstring, $frontmod);
  $allwhere .= addCriteria("actualtext", $searchstring, $frontmod);
  $allwhere .= ")";
}

$query = "SELECT sourceID, shorttitle, title, $sources_table.gedcom as gedcom, treename, ID FROM ($sources_table, $treesTable) WHERE $allwhere ORDER BY shorttitle, title LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(sourceID) as scount FROM ($sources_table, $treesTable) WHERE $allwhere";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['scount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('sources'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='admin-sources'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('sources', $message);
    $navList = new navList('');
    $navList->appendItem([true, "sourcesBrowse.php", uiTextSnippet('browse'), "findsource"]);
    $navList->appendItem([$allowAdd, "sourcesAdd.php", uiTextSnippet('add'), "addsource"]);
    $navList->appendItem([$allowEdit && $allowDelete, "sourcesMerge.php", uiTextSnippet('merge'), "merge"]);
    echo $navList->build("findsource");
    ?>
    <div class="row">
      <form action="sourcesBrowse.php" name='form1' id='form1'>
        <?php require '_/components/php/treeSelectControl.php'; ?>
        <label for='searchstring'>
          <?php echo uiTextSnippet('searchfor'); ?>
          <input name='searchstring' type='text' value="<?php echo $searchstring_noquotes; ?>">
        </label>
        <input name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
        <input name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>"
            onClick="resetSourcesSearch()">
        <span>
          <input name='exactmatch' type='checkbox' value='yes'<?php if ($exactmatch == "yes") {echo " checked";} ?>>
          <?php echo uiTextSnippet('exactmatch'); ?>
        </span>
        <input name='findsource' type='hidden' value='1'>
        <input name='newsearch' type='hidden' value='1'>
      </form>
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
            <input name='xsrcaction' type='submit' value="<?php echo uiTextSnippet('deleteselected'); ?>"
                onClick="return confirm('<?php echo uiTextSnippet('confdeleterecs'); ?>');">
          </p>
        <?php } ?>

        <table class="table table-sm table-striped">
          <tr>
            <th><span><?php echo uiTextSnippet('action'); ?></span></th>
            <?php if ($allowDelete) { ?>
              <th><span><?php echo uiTextSnippet('select'); ?></span></th>
            <?php } ?>
            <th><span><?php echo uiTextSnippet('sourceid'); ?></span></th>
            <th><span><?php echo uiTextSnippet('title'); ?></span></th>
            <?php if ($numtrees > 1) { ?>
              <th><span><?php echo uiTextSnippet('tree'); ?></span></th>
            <?php } ?>
          </tr>
          <?php
          if ($numrows) {
            $actionstr = "";
            if ($allowEdit) {
              $actionstr .= "<a href=\"sourcesEdit.php?sourceID=xxx&amp;tree=yyy\" title='" . uiTextSnippet('edit') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
              $actionstr .= "</a>\n";
            }
            if ($allowDelete) {
              $actionstr .= "<a href='#' onClick=\"return confirmDelete('zzz');\" title='" . uiTextSnippet('delete') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
              $actionstr .= "</a>\n";
            }
            $actionstr .= "<a href=\"sourcesShowSource.php?sourceID=xxx&amp;tree=yyy\" title='" . uiTextSnippet('preview') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/eye.svg'>\n";
            $actionstr .= "</a>\n";

            while ($row = tng_fetch_assoc($result)) {
              $newactionstr = preg_replace("/xxx/", $row['sourceID'], $actionstr);
              $newactionstr = preg_replace("/yyy/", $row['gedcom'], $newactionstr);
              $newactionstr = preg_replace("/zzz/", $row['ID'], $newactionstr);
              $title = $row['shorttitle'] ? $row['shorttitle'] : $row['title'];
              $editlink = "sourcesEdit.php?sourceID={$row['sourceID']}&amp;tree={$row['gedcom']}";
              $id = $allowEdit ? "<a href=\"$editlink\" title='" . uiTextSnippet('edit') . "'>" . $row['sourceID'] . "</a>" : $row['sourceID'];

              echo "<tr id=\"row_{$row['ID']}\">\n";
                echo "<td><div class=\"action-btns\">$newactionstr</div></td>\n";
                if ($allowDelete) {
                  echo "<td><input name=\"del{$row['ID']}\" type='checkbox' value='1'></td>";
                }
                echo "<td>$id</td>\n";
                echo "<td>$title</td>\n";
                if ($numtrees > 1) {
                  echo "<td>{$row['treename']}</td>\n";
                }
              echo "</tr>\n";
            }
          } else {
            echo "<tr><td>" . uiTextSnippet('norecords') . "</td></tr>\n";
          }
        ?> </table> <?php
        echo buildSearchResultPagination($totrows, "sourcesBrowse.php?searchstring=$searchstring&amp;exactmatch=$exactmatch&amp;offset", $maxsearchresults, 5);
        tng_free_result($result);
        ?>
      </form>
    </div> <!-- .row -->
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/admin.js"></script>
  <script>
    function confirmDelete(ID) {
      if (confirm(textSnippet('confdeletesrc'))) {
        deleteIt('source', ID);
      }
      return false;
    }
    
    function resetSourcesSearch() {
      document.form1.searchstring.value = '';
      document.form1.tree.selectedIndex = 0;
      document.form1.exactmatch.checked = false;
    }
  </script>
</body>
</html>
