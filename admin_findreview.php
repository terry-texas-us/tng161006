<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if ($type == 'I') {
  $tng_search_preview = $_SESSION['tng_search_preview'] = 1;
  if ($newsearch) {
    $_SESSION['tng_search_preview_post']['user'] = $reviewuser;
    $_SESSION['tng_search_preview_post']['page'] = 1;
    $_SESSION['tng_search_preview_post']['offset'] = 0;
  } else {
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
    $_SESSION['tng_search_freview_post']['user'] = $reviewuser;
    $_SESSION['tng_search_freview_post']['page'] = 1;
    $_SESSION['tng_search_freview_post']['offset'] = 0;
  } else {
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
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = '';
  $page = 1;
}
$allwhere = '1=1';

if ($assignedbranch) {
  $allwhere .= " AND branch LIKE \"%$assignedbranch%\"";
}
if ($reviewuser != '') {
  $allwhere .= " AND user = \"$reviewuser\"";
}
if ($type == 'I') {
  $allwhere .= " AND people.personID = temp_events.personID AND (type = 'I' OR type = 'C')";
  $query = "SELECT tempID, temp_events.personID AS personID, lastname, firstname, lnprefix, prefix, suffix, nameorder, eventID, DATE_FORMAT(postdate,\"%d %b %Y %H:%i:%s\") AS postdate, living, private, branch FROM people, trees, temp_events WHERE $allwhere ORDER BY postdate DESC";
  $returnpage = 'people.php';
  $totquery = "SELECT count(tempID) AS tcount FROM people, trees, temp_events WHERE $allwhere";
} elseif ($type == 'F') {
  $allwhere .= " AND families.familyID = temp_events.familyID AND type = 'F'";
  $query = "SELECT tempID, temp_events.familyID AS familyID, husband, wife, eventID, DATE_FORMAT(postdate,\"%d %b %Y %H:%i:%s\") AS postdate FROM families, trees, temp_events WHERE $allwhere ORDER BY postdate DESC";
  $returnpage = 'families.php';
  $totquery = "SELECT count(tempID) AS tcount FROM people, trees, temp_events WHERE $allwhere";
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

header('Content-type: text/html; charset=' . $session_charset);
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
      $navList->appendItem([true, 'peopleBrowse.php', uiTextSnippet('browse'), 'findperson']);
      $navList->appendItem([$allowAdd, 'peopleAdd.php', uiTextSnippet('add'), 'addperson']);
      $navList->appendItem([$allowEdit, 'admin_findreview.php?type=I', uiTextSnippet('review'), 'review']);
      $navList->appendItem([$allowEdit && $allowDelete, 'peopleMerge.php', uiTextSnippet('merge'), 'merge']);
    } else {
      $hmsg = 'families';
      
      $navList->appendItem([true, 'familiesBrowse.php', uiTextSnippet('browse'), 'findperson']);
      $navList->appendItem([$allowAdd, 'familiesAdd.php', uiTextSnippet('add'), 'addfamily']);
      $navList->appendItem([$allowEdit, 'admin_findreview.php?type=F', uiTextSnippet('review'), 'review']);
    }
    echo $navList->build('review');
    ?>
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
                $query = 'SELECT username, description FROM users ORDER BY description';
                $userresult = tng_query($query);
                while ($userrow = tng_fetch_assoc($userresult)) {
                  echo "  <option value=\"{$userrow['username']}\"";
                  if ($userrow['username'] == $reviewuser) {
                    echo ' selected';
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
            <td></td>
            <td></td>
            <td>
              <input name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
              <input name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>"
                     onClick="document.form1.reviewuser.value = ''; document.form1.living.checked = false; document.form1.exactmatch.checked = false;">
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
      echo '<p>' . uiTextSnippet('matches') . ": $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows";
      ?>
      <table class="table table-sm table-striped">
        <tr>
          <th><?php echo uiTextSnippet('action'); ?></th>
          <th><?php echo uiTextSnippet('id'); ?></th>
          <th><?php echo uiTextSnippet('name'); ?></th>
          <th><?php echo uiTextSnippet('event'); ?></th>
          <th><?php echo uiTextSnippet('postdate'); ?></th>
        </tr>

        <?php
        $actionstr = "<a href=\"admin_review.php?tempID=xxx\" title='" . uiTextSnippet('review') . "'>\n";
        $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
        $actionstr .= '</a>';
        if ($allowDelete) {
          $actionstr .= "<a href='#' onclick=\"return confirmDelete('xxx');\" title='" . uiTextSnippet('delete') . "'>\n";
          $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
          $actionstr .= '</a>';
        }

        while ($row = tng_fetch_assoc($result)) {
          if (is_numeric($row['eventID'])) {
            $query = "SELECT display, eventtypes.eventtypeID AS eventtypeID, tag FROM eventtypes, events WHERE eventID = {$row['eventID']} AND eventtypes.eventtypeID = events.eventtypeID";
            $evresult = tng_query($query);
            $evrow = tng_fetch_assoc($evresult);

            if ($evrow['display']) {
              $dispvalues = explode('|', $evrow['display']);
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
            $hname = $wname = '';
            if ($row['husband']) {
              $query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix, living, private, branch FROM people WHERE personID = '{$row['husband']}'";
              $hresult = tng_query($query);
              $prow = tng_fetch_assoc($hresult);
              tng_free_result($hresult);
              $prights = determineLivingPrivateRights($prow);
              $prow['allow_living'] = $prights['living'];
              $prow['allow_private'] = $prights['private'];
              $hname = getName($prow);
            }
            if ($row['wife']) {
              $query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix, living, private, branch FROM people WHERE personID = '{$row['wife']}'";
              $wresult = tng_query($query);
              $prow = tng_fetch_assoc($wresult);
              tng_free_result($wresult);
              $prights = determineLivingPrivateRights($prow);
              $prow['allow_living'] = $prights['living'];
              $prow['allow_private'] = $prights['private'];
              $wname = getName($prow);
            }
            $plus = $hname && $wname ? ' + ' : '';
            $name = "$hname$plus$wname";
            $persfamID = $row['familyID'];
          }
          $newactionstr = str_replace('xxx', $row['tempID'], $actionstr);
          echo "<tr id=\"row_{$row['tempID']}\"><td><span>$newactionstr</span></td>\n";
          echo "<td><span>$persfamID</span></td>\n";
          echo "<td><span>$name</span></td>\n";
          echo "<td><span>$displayval</span></td>\n";
          echo "<td><span>{$row['postdate']}</span></td>\n";
          echo "</tr>\n";
        }
        tng_free_result($result);
        ?>
      </table>
      <?php
      echo buildSearchResultPagination($totrows, "admin_findreview.php?type=$type&amp;reviewuser=$reviewuser&amp;offset", $maxsearchresults, 5);
      ?>
    </div>
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
