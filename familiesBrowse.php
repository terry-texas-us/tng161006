<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if ($newsearch) {
  $exptime = 0;
  setcookie('tng_search_families_post[search]', $searchstring, $exptime);
  setcookie('tng_search_families_post[living]', $living, $exptime);
  setcookie('tng_search_families_post[exactmatch]', $exactmatch, $exptime);
  setcookie('tng_search_families_post[spousename]', $spousename, $exptime);
  setcookie('tng_search_families_post[tngpage]', 1, $exptime);
  setcookie('tng_search_families_post[offset]', 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = stripslashes($_COOKIE['tng_search_families_post']['search']);
  }
  if (!$living) {
    $living = $_COOKIE['tng_search_families_post']['living'];
  }
  if (!$exactmatch) {
    $exactmatch = $_COOKIE['tng_search_families_post']['exactmatch'];
  }
  if (!$spousename) {
    $spousename = $_COOKIE['tng_search_families_post']['spousename'];
    if (!$spousename) {
      $spousename = 'husband';
    }
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_families_post']['tngpage'];
    $offset = $_COOKIE['tng_search_families_post']['offset'];
  } else {
    $exptime = 0;
    setcookie('tng_search_families_post[tngpage]', $tngpage, $exptime);
    setcookie('tng_search_families_post[offset]', $offset, $exptime);
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
$allwhere2 = '';

if ($searchstring) {
  $allwhere .= ' AND (1=0 ';
  if ($exactmatch == 'yes') {
    $frontmod = '=';
  } else {
    $frontmod = 'LIKE';
  }

  $allwhere .= addCriteria('familyID', $searchstring, $frontmod);
  $allwhere .= addCriteria('husband', $searchstring, $frontmod);
  $allwhere .= addCriteria('wife', $searchstring, $frontmod);

  if ($spousename == 'husband') {
    $allwhere .= addCriteria("CONCAT_WS(' ',TRIM(firstname)" . ($lnprefixes ? ',TRIM(lnprefix)' : '') . ',TRIM(lastname))', $searchstring, $frontmod);
  } elseif ($spousename == 'wife') {
    $allwhere .= addCriteria("CONCAT_WS(' ',TRIM(firstname)" . ($lnprefixes ? ',TRIM(lnprefix)' : '') . ',TRIM(lastname))', $searchstring, $frontmod);
  }
  $allwhere .= ')';
}
if ($spousename == 'husband') {
  $allwhere2 .= 'AND people.personID = husband ';
} elseif ($spousename == 'wife') {
  $allwhere2 .= 'AND people.personID = wife ';
}

if ($allwhere2) {
  $allwhere2 .= 'AND 1=1';
  $allwhere .= " $allwhere2";
  $allwhere .= ' ';

  if ($assignedbranch) {
    $allwhere .= " AND families.branch LIKE \"%$assignedbranch%\"";
  }
  $people_join = ', people';
  $otherfields = ', firstname, lnprefix, lastname, prefix, suffix, nameorder';
  $sortstr = 'lastname, lnprefix, firstname,';
} else {
  $people_join = '';
  $otherfields = '';
  $sortstr = '';
}
if ($living == 'yes') {
  $allwhere .= ' AND families.living = "1"';
}
$query = "SELECT families.ID AS ID, familyID, husband, wife, marrdate $otherfields FROM (families $people_join) WHERE $allwhere ORDER BY $sortstr familyID LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(families.ID) AS fcount FROM (families $people_join) WHERE $allwhere";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['fcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}

$revstar = checkReview('F');

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('families'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='families'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('families', $message);
    $navList = new navList('');
    //    $navList->appendItem([true, 'familiesBrowse.php', uiTextSnippet('browse'), 'findfamily']);
    $navList->appendItem([$allowAdd, 'familiesAdd.php', uiTextSnippet('add'), 'addfamily']);
    $navList->appendItem([$allowEdit, 'admin_findreview.php?type=F', uiTextSnippet('review') . $revstar, 'review']);
    echo $navList->build('findfamily');
    ?>
    <div>
      <?php require '_/components/php/findFamilyForm.php'; ?>
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
            <button class='btn btn-secondary' id='selectall-families' name='selectall' type='button'><?php echo uiTextSnippet('selectall'); ?></button>
            <button class='btn btn-secondary' id='clearall-families' name='clearall' type='button'><?php echo uiTextSnippet('clearall'); ?></button>
            <button class='btn btn-outline-danger' id='deleteselected-families' name='xfamaction' type='submit' value='true'><?php echo uiTextSnippet('deleteselected'); ?></button>
          </p>
        <?php }
        if ($numrows) {
        ?>
          <table class='table table-sm table-hover'>
            <thead class='thead-default'>
              <tr>
                <th><?php echo uiTextSnippet('action'); ?></th>
                <?php if ($allowDelete) { ?>
                  <th><?php echo uiTextSnippet('select'); ?></th>
                <?php } ?>
                <th><?php echo uiTextSnippet('id'); ?></th>
                <th><?php echo uiTextSnippet('husbid'); ?></th>
                <?php if ($spousename == 'husband') { ?>
                  <th><?php echo uiTextSnippet('husbname'); ?></th>
                <?php } ?>
                <th><?php echo uiTextSnippet('wifeid'); ?></th>
                <?php
                if ($spousename == 'wife') { ?>
                  <th><?php echo uiTextSnippet('wifename'); ?></th>
                <?php } ?>
                <th><?php echo uiTextSnippet('marrdate'); ?></th>
              </tr>
            </thead>
            <?php
            $actionstr = '';
            if ($allowEdit) {
              $actionstr .= "<a href=\"familiesEdit.php?familyID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
              $actionstr .= "</a>\n";
            }
            if ($allowDelete) {
              $actionstr .= "<a href='#' onClick=\"return confirmDelete('zzz');\" title='" . uiTextSnippet('delete') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
              $actionstr .= "</a>\n";
            }
            $actionstr .= "<a href=\"familiesShowFamily.php?familyID=xxx\" title='" . uiTextSnippet('preview') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/eye.svg'>\n";
            $actionstr .= "</a>\n";

            while ($row = tng_fetch_assoc($result)) {
              $newactionstr = preg_replace('/xxx/', $row['familyID'], $actionstr);
              $newactionstr = preg_replace('/zzz/', $row['ID'], $newactionstr);
              $rights = determineLivingPrivateRights($row);
              $row['allow_living'] = $rights['living'];
              $row['allow_private'] = $rights['private'];

              $editlink = "familiesEdit.php?familyID={$row['familyID']}";
              $id = $allowEdit ? "<a href=\"$editlink\" title='" . uiTextSnippet('edit') . "'>" . $row['familyID'] . '</a>' : $row['familyID'];

              echo "<tr id=\"row_{$row['ID']}\">\n";
              echo "<td>\n";
              echo    "<div class='action-btns'>\n$newactionstr</div>\n";
              echo "</td>\n";
              if ($allowDelete) {
                echo "<td><input class='selected' name='del" . $row['ID'] . "' type='checkbox' value='1'></td>\n";
              }
              echo "<td>$id</td>\n";
              echo "<td>{$row['husband']}</td>\n";
              if ($spousename == 'husband') {
                echo '<td>' . getName($row) . "</td>\n";
              }
              echo "<td>{$row['wife']}</td>\n";
              if ($spousename == 'wife') {
                echo '<td>' . getName($row) . "</td>\n";
              }
              echo "<td>{$row['marrdate']}</td>\n";
              echo "</tr>\n";
            }
            ?>
          </table>
          <?php
          echo buildSearchResultPagination($totrows, "familiesBrowse.php?searchstring=$searchstring&amp;spousename=$spousename&amp;living=$living&amp;exactmatch=$exactmatch&amp;offset", $maxsearchresults, 5);
        } else {
          echo "<div class='alert alert-warning'>" . uiTextSnippet('norecords') . "</div>\n";
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
    $('#selectall-families').on('click', function () {
        $('.selected').prop('checked', true);
    });

    $('#clearall-families').on('click', function () {
        $('.selected').prop('checked', false);
    });    
    
    $('#deleteselected-families').on('click', function () {
        return confirm(textSnippet('confdeleterecs'));
    });
    
    function confirmDelete(ID) {
      if (confirm(textSnippet('confdeletefam'))) {
        deleteIt('family', ID);
      }
      return false;
    }
    
    function resetFamiliesSearch() {
      document.form1.searchstring.value = '';
      document.form1.living.checked = false;
      document.form1.exactmatch.checked = false;    
      document.form1.spousename.selectedIndex = 0;
    }
  </script>
</body>
</html>
