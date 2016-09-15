<?php
require 'begin.php';
require $subroot . 'mapconfig.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

$exptime = 0;
if ($newsearch) {
  $searchstring = trim($searchstring);
  setcookie('tng_search_places_post[search]', $searchstring, $exptime);
  setcookie('tng_search_places_post[exactmatch]', $exactmatch, $exptime);
  setcookie('tng_search_places_post[nocoords]', $nocoords, $exptime);
  setcookie('tng_search_places_post[temples]', $temples, $exptime);
  setcookie('tng_search_places_post[tngpage]', 1, $exptime);
  setcookie('tng_search_places_post[offset]', 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = stripslashes($_COOKIE['tng_search_places_post']['search']);
  }
  if (!$exactmatch) {
    $exactmatch = $_COOKIE['tng_search_places_post']['exactmatch'];
  }
  if (!$nocoords) {
    $nocoords = $_COOKIE['tng_search_places_post']['nocoords'];
  }
  if (!$temples) {
    $temples = $_COOKIE['tng_search_places_post']['temples'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_places_post']['tngpage'];
    $offset = $_COOKIE['tng_search_places_post']['offset'];
  } else {
    setcookie('tng_search_places_post[tngpage]', $tngpage, $exptime);
    setcookie('tng_search_places_post[offset]', $offset, $exptime);
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
  $criteria = $operator == '=' ? " OR $field $operator \"$value\"" : " OR $field $operator \"%$value%\"";

  return $criteria;
}
$allwhere = '1 = 1';
if ($nocoords) {
  $allwhere .= ' AND (latitude IS NULL OR latitude = "" OR longitude IS NULL OR longitude = "")';
}
if ($temples) {
  $allwhere .= ' AND temple = 1';
}
if ($searchstring) {
  $allwhere .= ' AND (1=0';
  if ($exactmatch == 'yes') {
    $frontmod = '=';
  } else {
    $frontmod = 'LIKE';
  }
  $allwhere .= addCriteria('place', $searchstring, $frontmod);
  $allwhere .= addCriteria('notes', $searchstring, $frontmod);
  $allwhere .= ')';
}
$query = "SELECT ID, place, placelevel, longitude, latitude, zoom FROM $places_table WHERE $allwhere ORDER BY place, ID LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);
$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(ID) AS pcount FROM $places_table WHERE $allwhere";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['pcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('places'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='admin-places'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('places', $message);
    $navList = new navList('');
    //    $navList->appendItem([true, 'placesBrowse.php', uiTextSnippet('browse'), 'findplace']);
    $navList->appendItem([$allowAdd, 'placesAdd.php', uiTextSnippet('add'), 'addplace']);
    $navList->appendItem([$allowEdit && $allowDelete, 'placesMerge.php', uiTextSnippet('merge'), 'merge']);
    $navList->appendItem([$allowEdit, 'admin_geocodeform.php', uiTextSnippet('geocode'), 'geo']);
    echo $navList->build('findplace');
    ?>
    <br>
    <div class='row'>
      <form id='form1' name='form1' action='placesBrowse.php'>
        <label for='searchstring'><?php echo uiTextSnippet('searchfor'); ?></label>
        <input name='searchstring' type='text' value="<?php echo stripslashes($searchstring_noquotes); ?>">
        <input name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
        <input name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>" 
               onClick="resetPlacesSearch();">
        
        <label for='exactmatch'>
          <input name='exactmatch' type='checkbox' value='yes'<?php if ($exactmatch == 'yes') {echo ' checked';} ?>> 
          <?php echo uiTextSnippet('exactmatch'); ?>
        </label>
        <label for='nocoords'>
          <input name='nocoords' type='checkbox' value='yes'<?php if ($nocoords == 'yes') {echo ' checked';} ?>> 
          <?php echo uiTextSnippet('nocoords'); ?>
        </label>
        <?php
        if (determineLDSRights()) {
          echo "<label for='temples'>\n";
          echo "<input name='temples' type='checkbox' value='yes'";
          echo $temples == 'yes' ? " checked>\n" : ">\n";
          echo uiTextSnippet('findtemples');
          echo "</label>\n";
        }
        ?>
        <input name='findplace' type='hidden' value='1'>
        <input name='newsearch' type='hidden' value='1'>
      </form>
    </div> <!-- .card -->
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
          <input name='xplacaction' type='submit' value="<?php echo uiTextSnippet('deleteselected'); ?>" 
                 onClick="return confirm('<?php echo uiTextSnippet('confdeleterecs'); ?>');">
        </p>
        <?php
      }
      ?>
      <table class='table table-sm table-striped'>
        <tr>
          <th><?php echo uiTextSnippet('action'); ?></th>
          <?php if ($allowDelete) { ?>
            <th><?php echo uiTextSnippet('select'); ?></th>
          <?php } ?>
          <th><?php echo uiTextSnippet('place'); ?></th>
          <?php if ($map['key']) { ?>
            <th><?php echo uiTextSnippet('placelevel'); ?></th>
          <?php } ?>
          <th><?php echo uiTextSnippet('latitude'); ?></th>
          <th><?php echo uiTextSnippet('longitude'); ?></th>
          <?php if ($map['key']) { ?>
            <th><?php echo uiTextSnippet('zoom'); ?></th>
          <?php } ?>
        </tr>
        <?php
        if ($numrows) {
          $actionstr = '';
        if ($allowEdit) {
          $actionstr .= "<a href=\"placesEdit.php?ID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
          $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
          $actionstr .= '</a>';
        }
        if ($allowDelete) {
          $actionstr .= "<a href='#' onClick=\"return confirmDelete('xxx');\" title='" . uiTextSnippet('delete') . "'>\n";
          $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
          $actionstr .= '</a>';
        }
        $actionstr .= '<a href="placesearch.php?psearch=zzz';
        $actionstr .= "\" title='" . uiTextSnippet('preview') . "'>\n";
        $actionstr .= "<img class='icon-sm' src='svg/eye.svg'>\n";
        $actionstr .= "</a>\n";

        while ($row = tng_fetch_assoc($result)) {
          $newactionstr = preg_replace('/xxx/', $row['ID'], $actionstr);
          $newactionstr = preg_replace('/zzz/', urlencode($row['place']), $newactionstr);
          echo "<tr id=\"row_{$row['ID']}\"><td><div class=\"action-btns\">$newactionstr</div></td>\n";
          if ($allowDelete) {
            echo "<td><input name=\"del{$row['ID']}\" type='checkbox' value='1'></td>";
          }
          $display = $row['place'];
          $display = preg_replace('/</', '&lt;', $display);
          $display = preg_replace('/>/', '&gt;', $display);
          echo "<td>$display</td>\n";
          if ($map['key']) {
            echo "<td>{$row['placelevel']}</td>\n";
          }
          echo "<td>{$row['latitude']}</td>\n";
          echo "<td>{$row['longitude']}</td>\n";
          if ($map['key']) {
            echo "<td>{$row['zoom']}</td>\n";
          }
          echo "</tr>\n";
        }
        ?>
      </table>
      <?php
      echo buildSearchResultPagination($totrows, 'placesBrowse.php?searchstring=' . stripslashes($searchstring) . "&amp;exactmatch=$exactmatch&amp;noocords=$nocoords&amp;temples=$temples&amp;offset", $maxsearchresults, 5);
    } else {
      echo "</table>\n" . uiTextSnippet('norecords');
    }
    tng_free_result($result);
    ?>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/admin.js"></script>
  <script>
    function validateForm() {
      var rval = true;
      if (document.form1.searchstring.value.length === 0) {
        alert(textSnippet('entersearchvalue'));
        rval = false;
      }
      return rval;
    }

    function confirmDelete(ID) {
      if (confirm(textSnippet('confdeleteplace'))) {
        deleteIt('place', ID);
      }
      return false;
    }

    function resetPlacesSearch() {
      document.form1.searchstring.value = '';
      document.form1.exactmatch.checked = false;
      document.form1.nocoords.checked = false;
      document.form1.temples.checked = false;
    }
  </script>
</body>
</html>
