<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if ($newsearch) {
  $exptime = 0;
  setcookie('tng_search_sources_post[search]', $searchstring, $exptime);
  setcookie('tng_search_sources_post[exactmatch]', $exactmatch, $exptime);
  setcookie('tng_search_sources_post[tngpage]', 1, $exptime);
  setcookie('tng_search_sources_post[offset]', 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = stripslashes($_COOKIE['tng_search_sources_post']['search']);
  }
  if (!$exactmatch) {
    $exactmatch = $_COOKIE['tng_search_sources_post']['exactmatch'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_sources_post']['tngpage'];
    $offset = $_COOKIE['tng_search_sources_post']['offset'];
  } else {
    $exptime = 0;
    setcookie('tng_search_sources_post[tngpage]', $tngpage, $exptime);
    setcookie('tng_search_sources_post[offset]', $offset, $exptime);
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

function addCriteria($field, $value, $operator) {
  $criteria = '';

  if ($operator == '=') {
    $criteria = " OR $field $operator \"$value\"";
  } else {
    $innercriteria = '';
    $terms = explode(' ', $value);
    foreach ($terms as $term) {
      if ($innercriteria) {
        $innercriteria .= ' AND ';
      }
      $innercriteria .= "$field $operator \"%$term%\"";
    }
    if ($innercriteria) {
      $criteria = " OR ($innercriteria)";
    }
  }

  return $criteria;
}

$allwhere = '1=1';

if ($searchstring) {
  $allwhere .= ' AND (1=0 ';
  if ($exactmatch == 'yes') {
    $frontmod = '=';
  } else {
    $frontmod = 'LIKE';
  }

  $allwhere .= addCriteria('sourceID', $searchstring, $frontmod);
  $allwhere .= addCriteria('shorttitle', $searchstring, $frontmod);
  $allwhere .= addCriteria('title', $searchstring, $frontmod);
  $allwhere .= addCriteria('author', $searchstring, $frontmod);
  $allwhere .= addCriteria('callnum', $searchstring, $frontmod);
  $allwhere .= addCriteria('publisher', $searchstring, $frontmod);
  $allwhere .= addCriteria('actualtext', $searchstring, $frontmod);
  $allwhere .= ')';
}

$query = "SELECT sourceID, shorttitle, title, ID FROM sources WHERE $allwhere ORDER BY shorttitle, title LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(sourceID) AS scount FROM sources WHERE $allwhere";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['scount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('sources'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
<body id='admin-sources'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('sources', $message);
    $navList = new navList('');
    // $navList->appendItem([true, 'sourcesBrowse.php', uiTextSnippet('browse'), 'findsource']);
    $navList->appendItem([$allowAdd, 'sourcesAdd.php', uiTextSnippet('add'), 'addsource']);
    $navList->appendItem([$allowEdit && $allowDelete, 'sourcesMerge.php', uiTextSnippet('merge'), 'merge']);
    echo $navList->build('findsource');
    require '_/components/php/findSourcesForm.php';
    ?>
    <div class="row">
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
          <button class='btn btn-secondary btn-sm' id='selectall-sources' name='selectall' type='button'><?php echo uiTextSnippet('selectall'); ?></button>
          <button class='btn btn-secondary btn-sm' id='clearall-sources' name='clearall' type='button'><?php echo uiTextSnippet('clearall'); ?></button>
          <button class='btn btn-outline-danger btn-sm' id='deleteselected-sources' name='xsrcaction' type='submit' value='true'><?php echo uiTextSnippet('deleteselected'); ?></button>
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
          </tr>
          <?php
          if ($numrows) {
            $actionstr = '';
            if ($allowEdit) {
              $actionstr .= "<a href=\"sourcesEdit.php?sourceID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
              $actionstr .= "</a>\n";
            }
            if ($allowDelete) {
              $actionstr .= "<a href='#' onClick=\"return confirmDelete('zzz');\" title='" . uiTextSnippet('delete') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
              $actionstr .= "</a>\n";
            }
            $actionstr .= "<a href=\"sourcesShowSource.php?sourceID=xxx\" title='" . uiTextSnippet('preview') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/eye.svg'>\n";
            $actionstr .= "</a>\n";

            while ($row = tng_fetch_assoc($result)) {
              $newactionstr = preg_replace('/xxx/', $row['sourceID'], $actionstr);
              $newactionstr = preg_replace('/zzz/', $row['ID'], $newactionstr);
              $title = $row['shorttitle'] ? $row['shorttitle'] : $row['title'];
              $editlink = "sourcesEdit.php?sourceID={$row['sourceID']}";
              $id = $allowEdit ? "<a href=\"$editlink\" title='" . uiTextSnippet('edit') . "'>" . $row['sourceID'] . '</a>' : $row['sourceID'];

              echo "<tr id=\"row_{$row['ID']}\">\n";
                echo "<td><div class=\"action-btns\">$newactionstr</div></td>\n";
                if ($allowDelete) {
                  echo "<td><input name=\"del{$row['ID']}\" type='checkbox' value='1'></td>";
                }
                echo "<td>$id</td>\n";
                echo "<td>$title</td>\n";
              echo "</tr>\n";
            }
          } else {
            echo '<tr><td>' . uiTextSnippet('norecords') . "</td></tr>\n";
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
    $('#selectall-sources').on('click', function () {
        toggleAll(1);
    });

    $('#clearall-sources').on('click', function () {
        toggleAll(0);
    });

    $('#deleteselected-sources').on('click', function () {
        return confirm(textSnippet('confdeleterecs'));
    });    
    
    function resetSourcesSearch() {
      document.form1.searchstring.value = '';
      document.form1.exactmatch.checked = false;
    }
  </script>
</body>
</html>
