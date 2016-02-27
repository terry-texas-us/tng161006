<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if ($assignedtree) {
  $wherestr = "WHERE gedcom = \"$assignedtree\"";
  $tree = $assignedtree;
} else {
  $wherestr = "";
}
$treequery = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";
initMediaTypes();
header("Content-type:text/html; charset=" . $session_charset);
?>
<div id='finddiv'>
  <form name='find2' onsubmit="getNewMwMedia(this, 1); return false;">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('addmedia'); ?></h4>
    </header>
    <div class='modal-body'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('searchfor'); ?>:</td>
          <td><input id='searchstring' name='searchstring' type='text' value="<?php echo $searchstring; ?>"></td>
          <td>
            <input name='searchbutton' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
            <span id='spinner1' style='display: none'><img src='img/spinner.gif'></span>
          </td>
        </tr>
      </table>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <input name='mediatypeID' type='hidden' value="<?php echo $mediatypeID; ?>">
      <input name='tree' type='hidden' value="<?php echo $tree; ?>">
    </footer>
  </form>
  <div id='newmedia' style='width: 620px; height: 430px; overflow: auto'></div>
</div>