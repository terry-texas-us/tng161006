<?php
include("begin.php");
include("adminlib.php");

$admin_login = true;
include("checklogin.php");
include("version.php");

if ($type == 'I') {
  $tng_search_preview = $_SESSION['tng_search_preview'] = 1;
  if ($newsearch) {
    $_SESSION['tng_search_preview_post']['tree'] = $tree;
    $_SESSION['tng_search_preview_post']['user'] = $reviewuser;
    $_SESSION['tng_search_preview_post']['page'] = 1;
    $_SESSION['tng_search_preview_post']['offset'] = 0;
  } else {
    if (!$tree) {
      $tree = $_SESSION['tng_search_preview_post']['tree'];
    }
    if (!$reviewuser) {
      $reviewuser = $_SESSION['tng_search_preview_post']['user'];
    }
    if (!isset($offset)) {
      $page = $_SESSION['tng_search_preview_post']['page'];
      $offset = $_SESSION['tng_search_preview_post']['offset'];
    } else {
      $_SESSION['tng_search_preview_post']['page'] = $page;
      $_SESSION['tng_search_preview_post']['offset'] = $offset;
    }
  }
} else { //$type == F
  $tng_search_preview = $_SESSION['tng_search_preview'] = 1;
  if ($newsearch) {
    $_SESSION['tng_search_freview_post']['tree'] = $tree;
    $_SESSION['tng_search_freview_post']['user'] = $reviewuser;
    $_SESSION['tng_search_freview_post']['page'] = 1;
    $_SESSION['tng_search_freview_post']['offset'] = 0;
  } else {
    if (!$tree) {
      $tree = $_SESSION['tng_search_freview_post']['tree'];
    }
    if (!$reviewuser) {
      $reviewuser = $_SESSION['tng_search_freview_post']['user'];
    }
    if (!isset($offset)) {
      $page = $_SESSION['tng_search_freview_post']['page'];
      $offset = $_SESSION['tng_search_freview_post']['offset'];
    } else {
      $_SESSION['tng_search_freview_post']['page'] = $page;
      $_SESSION['tng_search_freview_post']['offset'] = $offset;
    }
  }
}
$orgtree = $tree;


if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $page = 1;
}

if ($assignedtree) {
  $wherestr = "WHERE gedcom = \"$assignedtree\"";
  $tree = $assignedtree;
} else {
  $wherestr = "";
}
$treequery = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";

$allwhere = "$temp_events_table.gedcom = $trees_table.gedcom";
if ($tree) {
  $allwhere .= " AND $temp_events_table.gedcom = \"$tree\"";
}

if ($assignedbranch) {
  $allwhere .= " AND branch LIKE \"%$assignedbranch%\"";
}
if ($reviewuser != "") {
  $allwhere .= " AND user = \"$reviewuser\"";
}

if ($type == 'I') {
  $allwhere .= " AND $people_table.personID = $temp_events_table.personID AND $people_table.gedcom = $temp_events_table.gedcom AND (type = \"I\" OR type = \"C\")";
  $query = "SELECT tempID, $temp_events_table.personID as personID, lastname, firstname, lnprefix, prefix, suffix, nameorder, treename, eventID, DATE_FORMAT(postdate,\"%d %b %Y %H:%i:%s\") as postdate, living, private, $people_table.gedcom, branch
    FROM $people_table, $trees_table, $temp_events_table WHERE $allwhere ORDER BY postdate DESC";
  $returnpage = "people.php";
  $totquery = "SELECT count(tempID) as tcount FROM $people_table, $trees_table, $temp_events_table WHERE $allwhere";
} elseif ($type == 'F') {
  $allwhere .= " AND $families_table.familyID = $temp_events_table.familyID AND $families_table.gedcom = $temp_events_table.gedcom AND type = \"F\"";
  $query = "SELECT tempID, $temp_events_table.familyID as familyID, $families_table.gedcom as gedcom, husband, wife, treename, eventID, DATE_FORMAT(postdate,\"%d %b %Y %H:%i:%s\") as postdate
    FROM $families_table, $trees_table, $temp_events_table WHERE $allwhere ORDER BY postdate DESC";
  $returnpage = "families.php";
  $totquery = "SELECT count(tempID) as tcount FROM $people_table, $trees_table, $temp_events_table WHERE $allwhere";
}
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $result2 = tng_query($totquery) or die(uiTextSnippet('cannotexecutequery') . ": $totquery");
  $row = tng_fetch_assoc($result2);
  $totrows = $row['pcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('review'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build($hmsg . '-review', $message);
    $navList = new navList('');
    if ($type == 'I') {
      $hmsg = 'people';
      $navList->appendItem([true, "admin_people.php", uiTextSnippet('search'), "findperson"]);
      $navList->appendItem([$allow_add, "admin_newperson.php", uiTextSnippet('addnew'), "addperson"]);
      $navList->appendItem([$allow_edit, "admin_findreview.php?type=I", uiTextSnippet('review'), "review"]);
      $navList->appendItem([$allow_edit && $allow_delete, "admin_merge.php", uiTextSnippet('merge'), "merge"]);
    } else {
      $hmsg = 'families';
      
      $navList->appendItem([true, "admin_families.php", uiTextSnippet('search'), "findperson"]);
      $navList->appendItem([$allow_add, "admin_newfamily.php", uiTextSnippet('addnew'), "addfamily"]);
      $navList->appendItem([$allow_edit, "admin_findreview.php?type=F", uiTextSnippet('review'), "review"]);
    }
    echo $navList->build("review");
    ?>

    <table class='table table-sm'>
      <tr>
        <td>
          <h4><?php echo uiTextSnippet('selectevaction'); ?></h4>

          <div>
            <form action="admin_findreview.php" name='form1'>
              <table>
                <tr>
                  <td><?php echo uiTextSnippet('user'); ?>:</td>
                  <td>
                    <select name="reviewuser">
                      <?php
                      echo "  <option value=''>" . uiTextSnippet('allusers') . "</option>\n";
                      $query = "SELECT username, description FROM $users_table ORDER BY description";
                      $userresult = tng_query($query);
                      while ($userrow = tng_fetch_assoc($userresult)) {
                        echo "  <option value=\"{$userrow['username']}\"";
                        if ($userrow['username'] == $reviewuser) {
                          echo " selected";
                        }
                        echo ">{$userrow['description']}</option>\n";
                      }
                      tng_free_result($userresult);
                      ?>
                    </select>
                  </td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('tree'); ?>:</td>
                  <td>
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
                  </td>
                  <td>
                    <input name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
                    <input name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>"
                           onClick="document.form1.reviewuser.value = ''; document.form1.tree.selectedIndex = 0; document.form1.living.checked = false; document.form1.exactmatch.checked = false;">
                  </td>
                </tr>
              </table>
              <input name='type' type='hidden' value="<?php echo $type; ?>">
              <input name='newsearch' type='hidden' value='1'>
            </form>
            <br>

            <?php
            $numrowsplus = $numrows + $offset;
            if (!$numrowsplus) {
              $offsetplus = 0;
            }
            echo "<p>" . uiTextSnippet('matches') . ": $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows";
            ?>
            <table class="table table-sm table-striped">
              <tr>
                <th><?php echo uiTextSnippet('action'); ?></th>
                <th><?php echo uiTextSnippet('id'); ?></th>
                <th><?php echo uiTextSnippet('name'); ?></th>
                <th><?php echo uiTextSnippet('event'); ?></th>
                <th><?php echo uiTextSnippet('postdate'); ?></th>
                <th><?php echo uiTextSnippet('tree'); ?></th>
              </tr>

              <?php
              $actionstr = "<a href=\"admin_review.php?tempID=xxx\" title='" . uiTextSnippet('review') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
              $actionstr .= "</a>";
              if ($allow_delete) {
                $actionstr .= "<a href='#' onclick=\"return confirmDelete('xxx');\" title='" . uiTextSnippet('delete') . "'>\n";
                $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
                $actionstr .= "</a>";
              }

              while ($row = tng_fetch_assoc($result)) {
                if (is_numeric($row['eventID'])) {
                  $query = "SELECT display, $eventtypes_table.eventtypeID as eventtypeID, tag FROM $eventtypes_table, $events_table WHERE eventID = {$row['eventID']} AND $eventtypes_table.eventtypeID = $events_table.eventtypeID";
                  $evresult = tng_query($query);
                  $evrow = tng_fetch_assoc($evresult);

                  if ($evrow['display']) {
                    $dispvalues = explode("|", $evrow['display']);
                    $numvalues = count($dispvalues);
                    if ($numvalues > 1) {
                      $displayval = "";
                      for ($i = 0; $i < $numvalues; $i += 2) {
                        $lang = $dispvalues[$i];
                        if ($mylanguage == $languages_path . $lang) {
                          $displayval = $dispvalues[$i + 1];
                          break;
                        }
                      }
                    } else {
                      $displayval = $evrow['display'];
                    }
                  } elseif ($evrow['tag']) {
                    $displayval = $eventtype['tag'];
                  } else {
                    $displayval = uiTextSnippet($eventID);
                  }
                } else {
                  $eventID = $row['eventID'];
                  $displayval = uiTextSnippet($eventID);
                }
                if ($type == 'I') {
                  $rights = determineLivingPrivateRights($row);
                  $row['allow_living'] = $rights['living'];
                  $row['allow_private'] = $rights['private'];
                  $name = getName($row);
                  $persfamID = $row['personID'];
                } elseif ($type == 'F') {
                  $hname = $wname = "";
                  if ($row['husband']) {
                    $query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix, living, private, gedcom, branch FROM $people_table WHERE personID = \"{$row['husband']}\" AND gedcom = \"{$row['gedcom']}\"";
                    $hresult = tng_query($query);
                    $prow = tng_fetch_assoc($hresult);
                    tng_free_result($hresult);
                    $prights = determineLivingPrivateRights($prow);
                    $prow['allow_living'] = $prights['living'];
                    $prow['allow_private'] = $prights['private'];
                    $hname = getName($prow);
                  }
                  if ($row['wife']) {
                    $query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix, living, private, gedcom, branch FROM $people_table WHERE personID = \"{$row['wife']}\" AND gedcom = \"{$row['gedcom']}\"";
                    $wresult = tng_query($query);
                    $prow = tng_fetch_assoc($wresult);
                    tng_free_result($wresult);
                    $prights = determineLivingPrivateRights($prow);
                    $prow['allow_living'] = $prights['living'];
                    $prow['allow_private'] = $prights['private'];
                    $wname = getName($prow);
                  }
                  $plus = $hname && $wname ? " + " : "";
                  $name = "$hname$plus$wname";
                  $persfamID = $row['familyID'];
                }
                $newactionstr = str_replace("xxx", $row['tempID'], $actionstr);
                echo "<tr id=\"row_{$row['tempID']}\"><td><span>$newactionstr</span></td>\n";
                echo "<td><span>$persfamID</span></td>\n";
                echo "<td><span>$name</span></td>\n";
                echo "<td><span>$displayval</span></td>\n";
                echo "<td><span>{$row['postdate']}</span></td>\n";
                echo "<td><span>{$row['treename']}</span></td></tr>\n";
              }
              tng_free_result($result);
              ?>
            </table>
            <?php
            echo buildSearchResultPagination($totrows, "admin_findreview.php?type=$type&amp;reviewuser=$reviewuser&amp;offset", $maxsearchresults, 5);
            ?>
          </div>
        </td>
      </tr>

    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/admin.js"></script>
<script>
function confirmDelete(ID) {
  if (confirm(textSnippet('confdeleteevent'))) {
    deleteIt('tevent', ID);
  }
  return false;
}
</script>
</body>
</html>
