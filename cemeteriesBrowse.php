<?php
require 'begin.php';
require $subroot . 'mapconfig.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

$tng_search_cemeteries = $_SESSION['tng_search_cemeteries'] = 1;
if ($newsearch) {
  $exptime = 0;
  setcookie("tng_search_cemeteries_post[search]", $searchstring, $exptime);
  setcookie("tng_search_cemeteries_post[offset]", 0, $exptime);
  setcookie("tng_search_cemeteries_post[tngpage]", 1, $exptime);
  setcookie("tng_search_cemeteries_post[offset]", 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = stripslashes($_COOKIE['tng_search_cemeteries_post']['search']);
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_cemeteries_post']['tngpage'];
    $offset = $_COOKIE['tng_search_cemeteries_post']['offset'];
  } else {
    $exptime = 0;
    setcookie("tng_search_cemeteries_post[tngpage]", $tngpage, $exptime);
    setcookie("tng_search_cemeteries_post[offset]", $offset, $exptime);
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
$frontmod = "LIKE";
$allwhere = "WHERE 1=0";

$allwhere .= addCriteria("$cemeteries_table.cemeteryID", $searchstring, $frontmod);
$allwhere .= addCriteria("maplink", $searchstring, $frontmod);
$allwhere .= addCriteria("cemname", $searchstring, $frontmod);
$allwhere .= addCriteria("city", $searchstring, $frontmod);
$allwhere .= addCriteria("state", $searchstring, $frontmod);
$allwhere .= addCriteria("county", $searchstring, $frontmod);
$allwhere .= addCriteria("country", $searchstring, $frontmod);

$query = "SELECT cemeteryID,cemname,city,county,state,country,latitude,longitude,zoom FROM $cemeteries_table $allwhere ORDER BY cemname, city, county, state, country LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(cemeteryID) as ccount FROM $cemeteries_table $allwhere";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['ccount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('cemeteries'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="admin-cemeteries">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('cemeteries', $message);
    $navList = new navList('');
//    $navList->appendItem([true, "cemeteriesBrowse.php", uiTextSnippet('browse'), "findcem"]);
    $navList->appendItem([$allowAdd, "cemeteriesAdd.php", uiTextSnippet('add'), "addcemetery"]);
    echo $navList->build("findcem");
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <div>
            <form action="cemeteriesBrowse.php" name='form1'>
              <table>
                <tr>
                  <td><?php echo uiTextSnippet('searchfor'); ?>:</td>
                  <td>
                    <input class='longfield' name='searchstring' type='text' value="<?php echo $searchstring_noquotes; ?>">
                  </td>
                  <td>
                    <input name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
                    <input name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>" onClick="resetCemeteriesSearch();">

                  </td>
                </tr>
              </table>
              <input name='findcemetery' type='hidden' value='1'>
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
              <?php
              if ($allowDelete) {
                ?>
                <p>
                  <input name='selectall' type='button' value="<?php echo uiTextSnippet('selectall'); ?>" onClick="toggleAll(1);">
                  <input name='clearall' type='button' value="<?php echo uiTextSnippet('clearall'); ?>" onClick="toggleAll(0);">
                  <input name='xcemaction' type='submit' value="<?php echo uiTextSnippet('deleteselected'); ?>" onClick="return confirm('<?php echo uiTextSnippet('confdeleterecs'); ?>');">
                </p>
                <?php
              }
              ?>
              <table class="table table-sm table-striped">
                <tr>
                  <th><?php echo uiTextSnippet('action'); ?></th>
                  <?php if ($allowDelete) { ?>
                    <th><?php echo uiTextSnippet('select'); ?></th>
                  <?php } ?>
                  <th><?php echo uiTextSnippet('cemetery'); ?></th>
                  <th><?php echo uiTextSnippet('location'); ?></th>
                  <?php if ($map['key']) { ?>
                    <th><?php echo uiTextSnippet('googleplace'); ?></th>
                  <?php } else { ?>
                    <th><?php echo uiTextSnippet('latitude'); ?></th>
                    <th><?php echo uiTextSnippet('longitude'); ?></th>
                  <?php } ?>
                </tr>

                <?php
                if ($numrows) {
                $actionstr = "";
                if ($allowEdit) {
                  $actionstr .= "<a href=\"cemeteriesEdit.php?cemeteryID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
                  $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
                  $actionstr .= "</a>";
                }
                if ($allowDelete) {
                  $actionstr .= "<a href='#' onClick=\"return confirmDelete('xxx');\" title=\"" . uiTextSnippet('delete') . "\">\n";
                  $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
                  $actionstr .= "</a>";
                }
                $actionstr .= "<a href=\"cemeteriesShowCemetery.php?cemeteryID=xxx&amp;\" title='" . uiTextSnippet('preview') . "'>\n";
                $actionstr .= "<img class='icon-sm' src='svg/eye.svg'>\n";
                $actionstr .= "</a>\n";

                while ($row = tng_fetch_assoc($result)) {
                  $location = $row['city'];
                  if ($row['county']) {
                    if ($location) {
                      $location .= ", ";
                    }
                    $location .= $row['county'];
                  }
                  if ($row['state']) {
                    if ($location) {
                      $location .= ", ";
                    }
                    $location .= $row['state'];
                  }
                  if ($row['country']) {
                    if ($location) {
                      $location .= ", ";
                    }
                    $location .= $row['country'];
                  }
                  $newactionstr = preg_replace("/xxx/", $row['cemeteryID'], $actionstr);
                  echo "<tr id=\"row_{$row['cemeteryID']}\"><td><div class=\"action-btns\">$newactionstr</div></td>\n";
                  if ($allowDelete) {
                    echo "<td>"
                    . "<input name=\"del{$row['cemeteryID']}\" type='checkbox' value='1'></td>";
                  }
                  $editlink = "cemeteriesEdit.php?cemeteryID={$row['cemeteryID']}";
                  $cemname = $allowEdit ? "<a href=\"$editlink\" title='" . uiTextSnippet('edit') . "'>" . $row['cemname'] . "</a>" : $row['cemname'];

                  echo "<td>$cemname</td>\n";
                  echo "<td>$location</td>\n";
                  if ($map['key']) {
                    echo "<td>";
                    $geo = "";
                    if ($row['latitude']) {
                      $geo .= uiTextSnippet('latitude') . ": " . number_format($row['latitude'], 3);
                    }
                    if ($row['longitude']) {
                      if ($geo) {
                        $geo .= "<br>";
                      }
                      $geo .= uiTextSnippet('longitude') . ": " . number_format($row['longitude'], 3);
                    }
                    if ($row['zoom']) {
                      if ($geo) {
                        $geo .= "<br>";
                      }
                      $geo .= uiTextSnippet('zoom') . ": " . $row['zoom'];
                    }
                    echo "$geo</td>\n";
                  } else {
                    echo "<td>{$row['latitude']}</td>\n";
                    echo "<td>{$row['longitude']}</td></tr>\n";
                  }
                }
                ?>
              </table>
            <?php
            echo buildSearchResultPagination($totrows, "cemeteriesBrowse.php?searchstring=$searchstring&amp;exactmatch=$exactmatch&amp;offset", $maxsearchresults, 5);
            }
            else {
              echo "<div class='alert alert-warning'>" . uiTextSnippet('norecords') . "</div>\n";
            }
            tng_free_result($result);
            ?>
            </form>
          </div>
        </td>
      </tr>
    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src='js/admin.js'></script>
  <script>
    function confirmDelete(ID) {
      if (confirm(textSnippet('confdeletecem')))
        deleteIt('cemetery', ID);
      return false;
    }
    function resetCemeteriesSearch() {
      document.form1.searchstring.value = '';
    }
  </script>
</body>
</html>
