<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

$tng_search_eventtypes = $_SESSION['tng_search_eventtypes'] = 1;
if ($newsearch) {
  $exptime = 05;
  $searchstring = stripslashes(trim($searchstring));
  setcookie('tng_search_eventtypes_post[search]', $searchstring, $exptime);
  setcookie('tng_search_eventtypes_post[etype]', $etype, $exptime);
  setcookie('tng_search_eventtypes_post[onimport]', $onimport, $exptime);
  setcookie('tng_search_eventtypes_post[tngpage]', 1, $exptime);
  setcookie('tng_search_eventtypes_post[offset]', 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = stripslashes($_COOKIE['tng_search_eventtypes_post']['search']);
  }
  if (!$etype) {
    $etype = $_COOKIE['tng_search_eventtypes_post']['etype'];
  }
  if (!$onimport) {
    $onimport = $_COOKIE['tng_search_eventtypes_post']['onimport'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_eventtypes_post']['tngpage'];
    $offset = $_COOKIE['tng_search_eventtypes_post']['offset'];
  } else {
    $exptime = 0;
    setcookie('tng_search_eventtypes_post[tngpage]', $tngpage, $exptime);
    setcookie('tng_search_eventtypes_post[offset]', $offset, $exptime);
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
$wherestr = $searchstring ? "(tag LIKE \"%$searchstring%\" OR description LIKE \"%$searchstring%\" OR display LIKE \"%$searchstring%\")" : '';
if ($etype) {
  $wherestr .= $wherestr ? " AND type = \"$etype\"" : "type = \"$etype\"";
}
if ($onimport || $onimport === '0') {
  $wherestr .= $wherestr ? " AND keep = \"$onimport\"" : "keep = \"$onimport\"";
}
if ($wherestr) {
  $wherestr = "WHERE $wherestr";
}
$query = "SELECT eventtypeID, tag, description, display, type, keep, collapse, ordernum FROM eventtypes $wherestr ORDER BY tag, description LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(eventtypeID) AS ecount FROM eventtypes $wherestr";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['ecount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('eventtypes'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='customeventtypes'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('customeventtypes', $message);
    $navList = new navList('');
    //    $navList->appendItem([true, 'eventtypesBrowse.php', uiTextSnippet('browse'), 'findevent']);
    $navList->appendItem([$allowAdd, 'eventtypesAdd.php', uiTextSnippet('add'), 'addevent']);
    echo $navList->build('findevent');
    ?>
    <hr>
    <form name='form1' action='eventtypesBrowse.php'>
      <div class='row'>
        <div class='col-md-2'><?php echo uiTextSnippet('searchfor'); ?></div>
        <div class='col-md-4'>
          <input class='form-control' name='searchstring' type='text' value="<?php echo $searchstring; ?>">
        </div>
        <div class='col-md-6'>
          <button class='btn' name='submit' type='submit'><?php echo uiTextSnippet('search'); ?></button>
          <button class='btn' name='reset' type='submit'><?php echo uiTextSnippet('reset'); ?></button>
        </div>
      </div>
      <br>
      <div class='row'>
        <div class='col-md-2'>
          <?php echo uiTextSnippet('assocwith'); ?>
        </div>
        <div class='col-md-4'>
          <select class='form-control' name='etype'>
            <option value=''><?php echo uiTextSnippet('all'); ?></option>
            <option value='I'<?php if ($etype == 'I') {echo ' selected';} ?>><?php echo uiTextSnippet('individual'); ?></option>
            <option value='F'<?php if ($etype == 'F') {echo ' selected';} ?>><?php echo uiTextSnippet('family'); ?></option>
            <option value='S'<?php if ($etype == 'S') {echo ' selected';} ?>><?php echo uiTextSnippet('source'); ?></option>
            <option value='R'<?php if ($etype == 'R') {echo ' selected';} ?>><?php echo uiTextSnippet('repository'); ?></option>
          </select>
        </div>
        <div class='col-md-6'>
          <input name='onimport' type='radio' value='1'<?php if ($onimport) {echo ' checked';} ?>> <?php echo uiTextSnippet('accept'); ?>
          <input name='onimport' type='radio' value='0'<?php if ($onimport === '0') {echo ' checked';} ?>> <?php echo uiTextSnippet('ignore'); ?>
          <input name='onimport' type='radio' value=''<?php if ($onimport === null || $onimport === '') {echo ' checked';} ?>> <?php echo uiTextSnippet('all'); ?>
        </div>
      </div>
      <input name='findeventtype' type='hidden' value='1'>
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
    <form action="eventtypesBrowseFormAction.php" method='post' name="form2">
      <p>
        <?php if ($allowDelete) { ?>
          <input class='btn btn-sm btn-outline-warning' id='deleteselected-eventtypes' name='cetaction' type='submit' value="<?php echo uiTextSnippet('deleteselected'); ?>">
        <?php } ?>
        <?php if ($allowEdit) { ?>
          <input class='btn btn-sm btn-outline-secondary' name='cetaction' type='submit' value="<?php echo uiTextSnippet('acceptselected'); ?>">
          <input class='btn btn-sm btn-outline-secondary' name='cetaction' type='submit' value="<?php echo uiTextSnippet('ignoreselected'); ?>">
          <input class='btn btn-sm btn-outline-secondary' name='cetaction' type='submit' value ="<?php echo uiTextSnippet('collapseselected'); ?>">
          <input class='btn btn-sm btn-outline-secondary' name='cetaction' type='submit' value ="<?php echo uiTextSnippet('expandselected'); ?>">
        <?php } ?>
      </p>
      <p>
        <button class='btn btn-sm btn-outline-secondary' id='selectall-eventtypes' name='selectall' type='button'><?php echo uiTextSnippet('selectall'); ?></button>
        <button class='btn btn-sm btn-outline-secondary' id='clearall-eventtypes' name='clearall' type='button'><?php echo uiTextSnippet('clearall'); ?></button>
      </p>
      <?php if ($numrows) { ?>
        <table class="table table-sm table-striped">
          <thead>
            <tr>
              <th><?php echo uiTextSnippet('action'); ?></th>
              <?php if ($allowDelete || $allowEdit) { ?>
                <th><?php echo uiTextSnippet('select'); ?></th>
              <?php } ?>
              <th><?php echo uiTextSnippet('tag'); ?></th>
              <th><?php echo uiTextSnippet('typedescription'); ?></th>
              <th><?php echo uiTextSnippet('display'); ?></th>
              <th><?php echo uiTextSnippet('orderpound'); ?></th>
              <th><?php echo uiTextSnippet('indfam'); ?></th>
              <th><?php echo uiTextSnippet('onimport'); ?></th>
              <th><?php echo uiTextSnippet('collapse'); ?></th>
            </tr>
          </thead>
          <?php
          $actionstr = '';
          if ($allowEdit) {
            $actionstr .= "<a href='eventtypesEdit.php?eventtypeID=xxx' title='" . uiTextSnippet('edit') . "'>";
            $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>";
            $actionstr .= "</a>\n";
          }
          if ($allowDelete) {
            $actionstr .= "<a id='delete' href='#' title='" . uiTextSnippet('delete') . "' data-row-id='xxx'>";
            $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>";
            $actionstr .= "</a>\n";
          }
          while ($row = tng_fetch_assoc($result)) {
            $keep = $row['keep'] ? uiTextSnippet('accept') : uiTextSnippet('ignore');
            $collapse = $row['collapse'] ? uiTextSnippet('yes') : uiTextSnippet('no');
            switch ($row['type']) {
              case 'I':
                $type = uiTextSnippet('individual');
                break;
              case 'F':
                $type = uiTextSnippet('family');
                break;
              case 'S':
                $type = uiTextSnippet('source');
                break;
              case 'R':
                $type = uiTextSnippet('repository');
                break;
            }
            $dispvalues = explode('|', $row['display']);
            $numvalues = count($dispvalues);
            if ($numvalues > 1) {
              $displayval = '';
              for ($i = 0; $i < $numvalues; $i += 2) {
                $lang = $dispvalues[$i];
                if ($mylanguage == $languagesPath . $lang) {
                  $displayval = $dispvalues[$i + 1];
                  break;
                }
              }
            } else {
              $displayval = $row['display'];
            }
            $newactionstr = preg_replace('/xxx/', $row['eventtypeID'], $actionstr);
            echo "<tr id='row_" . $row['eventtypeID'] . "'>\n";
            echo "<td><div class='action-btns2'>$newactionstr</div></td>\n";
            if ($allowDelete || $allowEdit) {
              echo "<td><input class='selected' name='et" . $row['eventtypeID'] . "' type='checkbox' value='1'></td>\n";
            }
            echo "<td>{$row['tag']}</td>\n";
            echo "<td>{$row['description']}</td><td>$displayval</td>";
            echo "<td>{$row['ordernum']}</td><td>$type</td><td>$keep</td><td>$collapse</td></tr>\n";
          }
          ?>
        </table>
        <?php 
        echo buildSearchResultPagination($totrows, "eventtypesBrowse.php?searchstring=$searchstring&amp;etype=$etype&amp;onimport=$onimport&amp;offset", $maxsearchresults, 5);
      } else {
        echo "<div class='alert alert-warning'>" . uiTextSnippet('norecords') . "</div>\n";
      }
      tng_free_result($result);
      ?>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/admin.js"></script>
<script>
  
  $('#selectall-eventtypes').on('click', function () {
      $('.selected').prop('checked', true);
  });

  $('#clearall-eventtypes').on('click', function () {
      $('.selected').prop('checked', false);
  });

  $('#deleteselected-eventtypes').on('click', function () {
      return confirm(textSnippet('confdeleterecs'));
  });
  
  $('button[name="reset"]').on('click', function () {
      document.form1.searchstring.value = '';
      document.form1.etype.selectedIndex = 0;
      document.form1.onimport['2'].checked = true;
  });
  
  $('#customeventtypes #delete').on('click', function () {
        var rowId = $(this).data('rowId');
        if (confirm(textSnippet('confdeleteevtype'))) {
            deleteIt('eventtype', rowId);
      }
      return false;
  });
</script>
</body>
</html>
