<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
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
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('users-review', $message);
    $navList = new navList('');
    $navList->appendItem([true, "usersBrowse.php", uiTextSnippet('search'), "finduser"]);
    $navList->appendItem([$allowAdd, "usersAdd.php", uiTextSnippet('add'), "adduser"]);
//    $navList->appendItem([$allowEdit, "usersReview.php", uiTextSnippet('review') . $revstar, "review"]);
    $navList->appendItem([true, "usersSendMail.php", uiTextSnippet('email'), "mail"]);
    echo $navList->build("review");
    ?>
    <em><?php echo uiTextSnippet('editnewusers'); ?></em><br><br>
    <?php
    echo "<p>" . uiTextSnippet('matches') . ": <span class=\"restotal\">$numrows</span></p>";
    ?>
    <form id='users-review' name='form2' action='userDeleteSelectedFormAction.php' method='post'>
      <?php if ($allowDelete) { ?>
        <button class='btn btn-secondary' name='selectall' type='button'><?php echo uiTextSnippet('selectall'); ?></button> 
        <button class='btn btn-secondary' name='clearall' type='button'><?php echo uiTextSnippet('clearall'); ?></button>
        <button class='btn btn-danger-outline' name='xruseraction' type='submit' value='true'><?php echo uiTextSnippet('deleteselected'); ?></button>
        <br>
      <?php } ?>
      <?php if ($numrows) { ?>
        <table class="table table-sm table-striped">
          <thead>
            <tr>
              <th><?php echo uiTextSnippet('action'); ?></th>
              <?php if ($allowDelete) { ?>
                <th><?php echo uiTextSnippet('select'); ?></th>
              <?php } ?>
              <th><?php echo uiTextSnippet('username'); ?></th>
              <th><?php echo uiTextSnippet('description'); ?></th>
              <th><?php echo uiTextSnippet('realname') . " / " . uiTextSnippet('email'); ?></th>
              <th><?php echo uiTextSnippet('dtregistered'); ?></th>
            </tr>
          </thead>
          <?php
          $actionstr = "";
          if ($allowEdit) {
            $actionstr .= "<a href=\"usersEdit.php?newuser=1&amp;userID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
            $actionstr .= "</a>\n";
          }
          if ($allowDelete) {
            $actionstr .= "<a id='delete' href='#' title='" . uiTextSnippet('delete') . "' data-user-id='xxx'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
            $actionstr .= "</a>";
          }
          while ($row = tng_fetch_assoc($result)) {
            $newactionstr = preg_replace("/xxx/", $row['userID'], $actionstr);
            echo "<tr id=\"row_{$row['userID']}\">\n";
              echo "<td><span>$newactionstr</span></td>\n";
              if ($allowDelete) {
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
            echo "</tr>\n";
          }
          ?>
        </table>
        <?php
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
<script src='js/users.js'></script>
</body>
</html>

