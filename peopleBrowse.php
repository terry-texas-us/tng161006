<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = true;
require 'checklogin.php';
require 'version.php';

$exptime = 0;
if ($newsearch) {
  setcookie("tng_search_people_post[search]", $searchstring, $exptime);
  setcookie("tng_search_people_post[living]", $living, $exptime);
  setcookie("tng_search_people_post[exactmatch]", $exactmatch, $exptime);
  setcookie("tng_search_people_post[nokids]", $nokids, $exptime);
  setcookie("tng_search_people_post[noparents]", $noparents, $exptime);
  setcookie("tng_search_people_post[nospouse]", $nospouse, $exptime);
  setcookie("tng_search_people_post[tngpage]", 1, $exptime);
  setcookie("tng_search_people_post[offset]", 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = stripslashes($_COOKIE['tng_search_people_post']['search']);
  }
  if (!$living) {
    $living = $_COOKIE['tng_search_people_post']['living'];
  }
  if (!$exactmatch) {
    $exactmatch = $_COOKIE['tng_search_people_post']['exactmatch'];
  }
  if (!$nokids) {
    $nokids = $_COOKIE['tng_search_people_post']['nokids'];
  }
  if (!$noparents) {
    $noparents = $_COOKIE['tng_search_people_post']['noparents'];
  }
  if (!$nospouse) {
    $nospouse = $_COOKIE['tng_search_people_post']['nospouse'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_people_post']['tngpage'];
    $offset = $_COOKIE['tng_search_people_post']['offset'];
  } else {
    setcookie("tng_search_people_post[tngpage]", $tngpage, $exptime);
    setcookie("tng_search_people_post[offset]", $offset, $exptime);
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

function addCriteria($field, $value, $operator)
{
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

$allwhere = "1=1 ";

if ($assignedbranch) {
  $allwhere .= " AND $people_table.branch LIKE \"%$assignedbranch%\"";
}
if ($searchstring) {
  $allwhere .= " AND (1=0";
  if ($exactmatch == "yes") {
    $frontmod = "=";
  } else {
    $frontmod = "LIKE";
  }
  $allwhere .= addCriteria("$people_table.personID", $searchstring, $frontmod);
  $allwhere .= addCriteria("CONCAT_WS(' ',TRIM(firstname)" . ($lnprefixes ? ",IF(TRIM(lnprefix),TRIM(lnprefix),NULL)" : "") . ",TRIM(lastname))", $searchstring, $frontmod);
  $allwhere .= ")";
}
if ($living == "yes") {
  $allwhere .= " AND $people_table.living = \"1\"";
}
if ($noparents) {
  $noparentjoin = "LEFT JOIN $children_table as noparents ON $people_table.personID = noparents.personID";
  $allwhere .= " AND noparents.familyID is NULL";
} else {
  $noparentjoin = "";
}
if ($nospouse) {
  $nospousejoin = "LEFT JOIN $families_table as nospousef ON $people_table.personID = nospousef.husband "
          . "LEFT JOIN $families_table as nospousem ON $people_table.personID = nospousem.wife";
  $allwhere .= " AND nospousef.familyID is NULL AND nospousem.familyID is NULL";
} else {
  $nospousejoin = "";
}
if ($nokids) {
  $nokidjoin = "LEFT OUTER JOIN $families_table AS familiesH ON $people_table.personID=familiesH.husband "
          . "LEFT OUTER JOIN $families_table AS familiesW ON $people_table.personID=familiesW.wife "
          . "LEFT OUTER JOIN $children_table AS childrenH ON familiesH.familyID=childrenH.familyID "
          . "LEFT OUTER JOIN $children_table AS childrenW ON familiesW.familyID=childrenW.familyID ";
  $nokidhaving = "HAVING ChildrenCount = 0 ";
  $nokidgroup = "GROUP BY $people_table.personID, $people_table.lastname, $people_table.firstname, $people_table.firstname, $people_table.lnprefix, "
          . "$people_table.prefix, $people_table.suffix, $people_table.nameorder, $people_table.birthdate, birthyear, $people_table.birthplace, $people_table.altbirthdate, altbirthyear, "
          . "$people_table.altbirthplace ";
  $nokidselect = ", SUM((childrenH.familyID is not NULL) + (childrenW.familyID is not NULL)) AS ChildrenCount ";
  $nokidgroup2 = "GROUP BY $people_table.personID, $people_table.lastname, $people_table.firstname, $people_table.firstname, $people_table.lnprefix ";
} else {
  $nokidjoin = "";
  $nokidhaving = "";
  $nokidgroup = "";
  $nokidselect = "";
}
$query = "SELECT $people_table.ID, $people_table.personID, lastname, firstname, lnprefix, prefix, suffix, nameorder, "
  . "birthdate, LPAD(SUBSTRING_INDEX(birthdate, ' ', -1),4,'0') as birthyear, birthplace, "
  . "altbirthdate, LPAD(SUBSTRING_INDEX(altbirthdate, ' ', -1),4,'0') as altbirthyear, altbirthplace, "
  . "deathdate, LPAD(SUBSTRING_INDEX(deathdate, ' ', -1),4,'0') as deathyear, deathplace $nokidselect "
  . "FROM ($people_table) $nokidjoin $noparentjoin $nospousejoin "
  . "WHERE $allwhere $nokidgroup $nokidhaving "
  . "ORDER BY lastname, lnprefix, firstname, birthyear, altbirthyear LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  if ($nokids) {
    $query = "SELECT $people_table.ID, $people_table.personID, lastname, firstname, lnprefix $nokidselect "
            . "FROM ($people_table) $nokidjoin $noparentjoin $nospousejoin "
            . "WHERE $allwhere $nokidgroup2 $nokidhaving";
    $result2 = tng_query($query);
    $totrows = tng_num_rows($result2);
  } else {
    $query = "SELECT count($people_table.personID) as pcount "
            . "FROM ($people_table) $noparentjoin $nospousejoin "
            . "WHERE $allwhere";
    $result2 = tng_query($query);
    $row = tng_fetch_assoc($result2);
    $totrows = $row['pcount'];
  }
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
$revstar = checkReview('I');

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('people'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="admin-people">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('people', $message);
    $navList = new navList('people');
    //    $navList->appendItem([true, "peopleBrowse.php", uiTextSnippet('browse'), "findperson"]);
    $navList->appendItem([$allowAdd, "peopleAdd.php", uiTextSnippet('add'), "addperson"]);
    $navList->appendItem([$allowEdit, "admin_findreview.php?type=I", uiTextSnippet('review') . $revstar, "review"]);
    $navList->appendItem([$allowEdit && $allowDelete, "peopleMerge.php", uiTextSnippet('merge'), "merge"]);
    echo $navList->build('findperson');
    require '_/components/php/findPeopleForm.php';
    
    $numrowsplus = $numrows + $offset;
    if (!$numrowsplus) {
      $offsetplus = 0;
    }
    echo displayListLocation($offsetplus, $numrowsplus, $totrows);
    ?>
    <form action="admin_deleteselected.php" method='post' name="form2">
      <?php if ($allowDelete) { ?>
        <p>
          <button class='btn btn-secondary' id='selectall-people' name='selectall' type='button'><?php echo uiTextSnippet('selectall'); ?></button>
          <button class='btn btn-secondary' id='clearall-people' name='clearall' type='button'><?php echo uiTextSnippet('clearall'); ?></button>
          <button class='btn btn-outline-danger' id='deleteselected-people' name='xperaction' type='submit'><?php echo uiTextSnippet('deleteselected'); ?></button>
        </p>
      <?php } ?>
      <?php if ($numrows) { ?>            
        <table class='table table-sm table-striped'>
          <thead>
            <tr>
              <th><?php echo uiTextSnippet('action'); ?></th>
              <?php if ($allowDelete) { ?>
                <th><?php echo uiTextSnippet('select'); ?></th>
              <?php } ?>
              <th><?php echo uiTextSnippet('name'); ?></th>
              <th><?php echo uiTextSnippet('events'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php
            $actionstr = "";
            if ($allowEdit) {
              $actionstr .= "<a href=\"peopleEdit.php?personID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
              $actionstr .= "</a>\n";
            }
            if ($allowDelete) {
              $actionstr .= "<a id='delete' href='#' title='" . uiTextSnippet('delete') . "' data-row-id='zzz'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
              $actionstr .= "</a>\n";
            }
            $actionstr .= "<a href=\"peopleShowPerson.php?personID=xxx\" title='" . uiTextSnippet('preview') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/eye.svg'>\n";
            $actionstr .= "</a>\n";

            while ($row = tng_fetch_assoc($result)) {
              $rights = determineLivingPrivateRights($row);
              $row['allow_living'] = $rights['living'];
              $row['allow_private'] = $rights['private'];
              if ($row['birthdate']) {
                $birthdate = uiTextSnippet('birthabbr') . " " . $row['birthdate'];
                $birthplace = $row['birthplace'];
              } else {
                if ($row['altbirthdate']) {
                  $birthdate = uiTextSnippet('chrabbr') . " " . $row['altbirthdate'];
                  $birthplace = $row['altbirthplace'];
                } else {
                  $birthdate = "";
                  $birthplace = $row['birthplace'] ? $row['birthplace'] : $row['altbirthplace'];
                }
              }
              if ($row['deathdate']) {
                $deathdate = uiTextSnippet('deathabbr') . " " . $row['deathdate'];
                $deathplace = $row['deathplace'];
              }
              $newactionstr = preg_replace("/xxx/", $row['personID'], $actionstr);
              $newactionstr = preg_replace("/zzz/", $row['ID'], $newactionstr);

              echo "<tr id=\"row_{$row['ID']}\">\n";
              echo "<td><div class=\"action-btns\">$newactionstr</div></td>\n";

              if ($allowDelete) {
                echo "<td><input name=\"del{$row['ID']}\" type='checkbox' value='1'></td>";
              }
              echo "<td>\n";
                $editlink = "peopleEdit.php?personID={$row['personID']}";
                echo $allowEdit ? "<a href='$editlink' title='" . uiTextSnippet('edit') . "'>" . getname($row) . "</a>" : getname($row);
                echo "<br>";
                echo "{$row['personID']}\n";
              echo "</td>\n";
              echo "<td>$birthdate, $birthplace<br>$deathdate, $deathplace</td>\n";
              echo "</tr>\n";
            }
            ?>
          </tbody>
        </table>
      <?php 
      } else {
        echo "<div class='alert alert-warning'>" . uiTextSnippet('norecords') . "</div>\n";
      }
      echo buildSearchResultPagination($totrows, "peopleBrowse.php?searchstring=$searchstring&amp;living=$living&amp;exactmatch=$exactmatch&amp;offset", $maxsearchresults, 5);
      tng_free_result($result);
      ?>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin');?>
  <script src="js/admin.js"></script>
  <script>
    $('#selectall-people').on('click', function () {
        toggleAll(1);
    });

    $('#clearall-people').on('click', function () {
        toggleAll(0);
    });
    
    $('#deleteselected-people').on('click', function () {
        return confirm(textSnippet('confdeleterecs'));
    });
    
    $('#admin-people #delete').on('click', function () {
        var rowId = $(this).data('rowId');
        if (confirm(textSnippet('confdeletepers'))) {
            deleteIt('person', rowId);
      }
      return false;
    });

    function resetPeople() {
      document.form1.searchstring.value = '';
      document.form1.tree.selectedIndex = 0;
      document.form1.living.checked = false;
      document.form1.exactmatch.checked = false;
      document.form1.nokids.checked = false;
      document.form1.noparents.checked = false;
      document.form1.nospouse.checked = false;
    }
  </script>
</body>
</html>
