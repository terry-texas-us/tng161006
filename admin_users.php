<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if ($assignedtree) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

$tng_search_users = $_SESSION['tng_search_users'] = 1;
if ($newsearch) {
  $exptime = 0;
  $searchstring = stripslashes(trim($searchstring));
  setcookie("tng_search_users_post[search]", $searchstring, $exptime);
  setcookie("tng_search_users_post[adminonly]", $adminonly, $exptime);
  setcookie("tng_search_users_post[tngpage]", 1, $exptime);
  setcookie("tng_search_users_post[offset]", 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = stripslashes($_COOKIE['tng_search_users_post']['search']);
  }
  if (!$adminonly) {
    $adminonly = $_COOKIE['tng_search_users_post']['adminonly'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_users_post']['tngpage'];
    $offset = $_COOKIE['tng_search_users_post']['offset'];
  } else {
    $exptime = 0;
    setcookie("tng_search_users_post[tngpage]", $tngpage, $exptime);
    setcookie("tng_search_users_post[offset]", $offset, $exptime);
  }
}

if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $tngpage = 1;
}

$wherestr = $searchstring ? " AND (username LIKE \"%$searchstring%\" OR description LIKE \"%$searchstring%\" OR realname LIKE \"%$searchstring%\" OR email LIKE \"%$searchstring%\")" : "";
$wherestr .= $adminonly ? " AND allow_add = \"1\" AND allow_edit = \"1\" AND allow_delete = \"1\" AND gedcom = \"\"" : "";
$query = "SELECT *, DATE_FORMAT(lastlogin,\"%d %b %Y %H:%i:%s\") as lastlogin FROM $users_table WHERE allow_living != \"-1\" $wherestr ORDER BY description LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(userID) as ucount FROM $users_table WHERE allow_living != \"-1\" $wherestr";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['ucount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}

$revquery = "SELECT count(userID) as ucount FROM $users_table WHERE allow_living = \"-1\"";
$revresult = tng_query($revquery) or die(uiTextSnippet('cannotexecutequery') . ": $revquery");
$revrow = tng_fetch_assoc($revresult);
$revstar = $revrow['ucount'] ? " *" : "";
tng_free_result($revresult);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('users'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="users">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('users', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_users.php", uiTextSnippet('search'), "finduser"]);
    $navList->appendItem([$allow_add, "admin_newuser.php", uiTextSnippet('addnew'), "adduser"]);
    $navList->appendItem([$allow_edit, "admin_reviewusers.php", uiTextSnippet('review') . $revstar, "review"]);
    $navList->appendItem([true, "admin_mailusers.php", uiTextSnippet('email'), "mail"]);
    echo $navList->build("finduser");
    ?>
    <div>
      <form action="admin_users.php" name='form1'>
        <table>
          <tr>
            <td><?php echo uiTextSnippet('searchfor'); ?>:</td>
            <td><input class='longfield' name='searchstring' type='text' value="<?php echo $searchstring; ?>"></td>
            <td>
              <input name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
              <input name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>"
                     onClick="document.form1.searchstring.value = ''; document.form1.adminonly.checked = false;">
            </td>
          </tr>
          <tr>
            <td></td>
            <td colspan='2'>
              <input name='adminonly' type='checkbox' value='yes'<?php if ($adminonly == "yes") {echo " checked";} ?>>
              <?php echo uiTextSnippet('adminonly'); ?>
            </td>
          </tr>
        </table>
        <input name='finduser' type='hidden' value='1'>
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
        <?php
        if ($allow_delete) {
          ?>
          <p>
            <input name='selectall' type='button' value="<?php echo uiTextSnippet('selectall'); ?>" 
                   onClick="toggleAll(1);">
            <input name='clearall' type='button' value="<?php echo uiTextSnippet('clearall'); ?>" 
                   onClick="toggleAll(0);">
            <input name='xuseraction' type='submit' value="<?php echo uiTextSnippet('deleteselected'); ?>"
                   onClick="return confirm('<?php echo uiTextSnippet('confdeleterecs'); ?>');">
          </p>
          <?php
        }
        if ($numrows) {
        ?>
          <table class="table table-sm table-striped">
            <tr>
              <th><?php echo uiTextSnippet('action'); ?></th>
              <?php if ($allow_delete) { ?>
                <th><?php echo uiTextSnippet('select'); ?></th>
              <?php } ?>
              <th><?php echo uiTextSnippet('username'); ?></th>
              <th><?php echo uiTextSnippet('description'); ?></th>
              <th><?php echo uiTextSnippet('realname') . " / " . uiTextSnippet('email'); ?></th>
              <!--<th><?php echo uiTextSnippet('admin'); ?></th>-->
              <th><?php echo uiTextSnippet('tree'); ?></th>
              <th><?php echo uiTextSnippet('branch'); ?></th>
              <th><?php echo uiTextSnippet('role'); ?></th>
              <th><?php echo uiTextSnippet('living'); ?></th>
              <th><?php echo uiTextSnippet('private'); ?></th>
              <th>GED</th>
              <th>PDF</th>
              <th><?php echo uiTextSnippet('lds'); ?></th>
              <th><?php echo uiTextSnippet('lastlogin'); ?></th>
              <th><?php echo uiTextSnippet('disabled'); ?></th>
            </tr>

            <?php
            $actionstr = "";
            if ($allow_edit) {
              $actionstr .= "<a href=\"admin_edituser.php?userID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
              $actionstr .= "</a>";
            }
            if ($allow_delete) {
              $actionstr .= "<a href='#' onClick=\"return confirmDelete('xxx');\" title='" . uiTextSnippet('delete') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
              $actionstr .= "</a>";
            }
            while ($row = tng_fetch_assoc($result)) {
              $form_allow_admin = $row['gedcom'] || (!$row['allow_edit'] && !$row['allow_add'] && !$row['allow_delete']) ? "" : uiTextSnippet('yes');
              $form_allow_lds = $row['allow_lds'] ? uiTextSnippet('yes') : "";
              $form_allow_living = $row['allow_living'] > 0 ? uiTextSnippet('yes') : "";
              $form_allow_private = $row['allow_private'] > 0 ? uiTextSnippet('yes') : "";
              $form_allow_ged = $row['allow_ged'] ? uiTextSnippet('yes') : "";
              $form_allow_pdf = $row['allow_pdf'] ? uiTextSnippet('yes') : "";
              $form_disabled = $row['disabled'] ? uiTextSnippet('yes') : "";
              $newactionstr = preg_replace("/xxx/", $row['userID'], $actionstr);
              echo "<tr id=\"row_{$row['userID']}\"><td><div class=\"action-btns2\">$newactionstr</div></td>\n";
              if ($allow_delete) {
                echo "<td><input name=\"del{$row['userID']}\" type='checkbox' value='1'></td>";
              }
              $editlink = "admin_edituser.php?userID={$row['userID']}";
              $username = $allow_edit ? "<a href=\"$editlink\" title='" . uiTextSnippet('edit') . "'>" . $row['username'] . "</a>" : $row['username'];

              echo "<td>$username</td>\n";
              echo "<td>{$row['description']}</td>\n";
              echo "<td>" . $row['realname'];
              if ($row['realname'] && $row['email']) {
                echo "<br>";
              }
              $rolestr = 'usr' . ($row['role'] ? $row['role'] : 'custom');
              echo "<a href=\"mailto:" . $row['email'] . "\">" . $row['email'] . "</a></td>\n";
              
              echo "<td>{$row['gedcom']}</td>\n";
              echo "<td>{$row['branch']}</td>\n";
              echo "<td>" . uiTextSnippet($rolestr) . "</td>\n";
              echo "<td>$form_allow_living</td>\n";
              echo "<td>$form_allow_private</td>\n";
              echo "<td>$form_allow_ged</td>\n";
              echo "<td>$form_allow_pdf</td>\n";
              echo "<td>$form_allow_lds</td>\n";
              echo "<td>{$row['lastlogin']}</td>\n";
              echo "<td>$form_disabled</td>\n";
              echo "</tr>\n";
            }
            ?>
          </table>
          <?php
          echo buildSearchResultPagination($totrows, "admin_users.php?searchstring=$searchstring&amp;offset", $maxsearchresults, 5);
        } else {
          echo uiTextSnippet('norecords');
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
    function confirmDelete(ID) {
      if (confirm(textSnippet('confuserdelete')))
        deleteIt('user', ID);
      return false;
    }
  </script>
</body>
</html>
