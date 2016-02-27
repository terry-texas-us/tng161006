<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if (!$allow_edit || $assignedbranch) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$query = "SELECT action FROM $branches_table WHERE gedcom = \"$tree\" and branch = \"$branch\"";
$result = tng_query($query);
$brow = tng_fetch_assoc($result);
tng_free_result($result);

header("Content-type:text/html; charset=" . $session_charset);
?>
<div class='container'>
  <form id='form2' name='form2' action='#' method='post' onsubmit="return addLabels();">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('addlabels'); ?></h4>
    </header>
    <div class='modal-body'>
      <table class='table table-sm'>
        <tr>
          <td colspan='2'><strong><?php echo uiTextSnippet('action'); ?>:</strong></td>
        </tr>
        <tr>
          <td colspan='2'>
            <input name='branchaction' type='radio' value='add' checked
                   onClick="toggleAdd();"> <?php echo uiTextSnippet('addlabels'); ?><br>
            <input name='branchaction' type='radio' value='clear'
                   onClick="toggleClear(0);"> <?php echo uiTextSnippet('clearlabels'); ?><br>
            <input name='branchaction' type='radio' value='delete'
                   onClick="toggleClear(1);"> <?php echo uiTextSnippet('delpeople'); ?><br>
          </td>
        </tr>
        <tr>
          <td colspan='2'>
            <div id='allpart' style='display: none'>
              <input name='set' type='radio' value='all'> <?php echo uiTextSnippet('all'); ?>
              <input name='set' type='radio' value='partial' checked> <?php echo uiTextSnippet('partial'); ?>
            </div>
          </td>
        </tr>
        <tr id='overwrite1'>
          <td>
            <div><strong><?php echo uiTextSnippet('existlabels'); ?>:</strong></div>
          </td>
          <td>
            <div><br>
              <select id='overwrite' name='overwrite'>
                <?php
                $action = $brow['action'] ? $brow['action'] : 2;
                ?>
                <option value="2" <?php if ($action == 2) {echo " selected";} ?>><?php echo uiTextSnippet('append'); ?></option>
                <option value='1' <?php if ($action == 1) {echo " selected";} ?>><?php echo uiTextSnippet('overwrite'); ?></option>
                <option value='0' <?php if ($action == 0) {echo " selected";} ?>><?php echo uiTextSnippet('leave'); ?></option>
              </select>
            </div>
          </td>
        </tr>
      </table>
      <div id='branchresults'></div>
    </div>
    <footer class='modal-footer'>
      <input id='labelsub' type='submit' value="<?php echo uiTextSnippet('addlabels'); ?>">
      <img id='labelspinner' src='img/spinner.gif' style='display: none'>
    </footer>
  </form>
</div> <!-- .container -->