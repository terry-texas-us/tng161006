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

$query = "SELECT userID, description, username, gedcom, branch, allow_edit, allow_add, allow_delete, allow_living, allow_lds, allow_ged, realname, email, DATE_FORMAT(dt_registered,\"%d %b %Y %H:%i:%s\") as dt_registered_fmt FROM $users_table WHERE allow_living = \"-1\"ORDER BY dt_registered DESC";
$result = tng_query($query);

$numrows = tng_num_rows($result);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('users'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="users-review">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('users-review', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_users.php", uiTextSnippet('search'), "finduser"]);
    $navList->appendItem([$allow_add, "admin_newuser.php", uiTextSnippet('addnew'), "adduser"]);
    $navList->appendItem([$allow_edit, "admin_reviewusers.php", uiTextSnippet('review') . $revstar, "review"]);
    $navList->appendItem([true, "admin_mailusers.php", uiTextSnippet('email'), "mail"]);
    echo $navList->build("review");
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <div>
            <em><?php echo uiTextSnippet('editnewusers'); ?></em><br><br>
            <?php
            echo "<p>" . uiTextSnippet('matches') . ": <span class=\"restotal\">$numrows</span></p>";
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
                  <input name='xruseraction' type='submit' value="<?php echo uiTextSnippet('deleteselected'); ?>"
                         onClick="return confirm('<?php echo uiTextSnippet('confdeleterecs'); ?>');">
                </p>
                <?php
              }
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
                  <th><?php echo uiTextSnippet('dtregistered'); ?></th>
                </tr>

                <?php
                if ($numrows) {
                $actionstr = "";
                if ($allow_edit) {
                  $actionstr .= "<a href=\"admin_edituser.php?newuser=1&amp;userID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
                  $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
                  $actionstr .= "</a>\n";
                }
                if ($allow_delete) {
                  $actionstr .= "<a href='#' onClick=\"return confirmDelete('xxx');\" title='" . uiTextSnippet('delete') . "'>\n";
                  $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
                  $actionstr .= "</a>";
                }
                while ($row = tng_fetch_assoc($result)) {
                  $newactionstr = preg_replace("/xxx/", $row['userID'], $actionstr);
                  echo "<tr id=\"row_{$row['userID']}\">\n";
                  echo "<td><span>$newactionstr</span></td>\n";
                  if ($allow_delete) {
                    echo "<td><input name=\"del{$row['userID']}\" type='checkbox' value='1'></td>";
                  }
                  echo "<td><span>{$row['username']}</span></td>\n";
                  echo "<td><span>{$row['description']}</span></td>\n";
                  echo "<td><span>{$row['realname']}";
                  if ($row['realname'] && $row['email']) {
                    echo "<br>";
                  }
                  echo "<a href=\"mailto:" . $row['email'] . "\">" . $row['email'] . "</a></span></td>\n";
                  echo "<td><span>{$row['dt_registered_fmt']}</span></td>\n";
                }
                ?>
              </table>
            <?php
            echo "<p>" . uiTextSnippet('matches') . ": <span class=\"restotal\">$numrows</span></p>";
            }
            else {
              echo uiTextSnippet('norecords');
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
  <script>
    function confirmDelete(ID) {
      if (confirm(textSnippet('confuserdelete')))
        deleteIt('user', ID);
      return false;
    }
  </script>
  <script src="js/admin.js"></script>
</body>
</html>

