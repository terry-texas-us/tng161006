<?php
/**
 * Name history: treesChange.php
 */

require 'begin.php'; // [ts] args expected entity, oldtree, entityID
require 'adminlib.php';

require 'checklogin.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
switch ($entity) {
  case 'person':
    $IDlabel = uiTextSnippet('personid');
    break;
  case 'source':
    $IDlabel = uiTextSnippet('sourceid');
    break;
  case 'repo':
    $IDlabel = uiTextSnippet('repoid');
    break;
}
//use passed in type, gedcom & id to get name
//get list of trees, omit current tree from list in dropdown
$treelist = "  <option value=''></option>\n";
$currenttree = '';

$query = 'SELECT gedcom, treename FROM trees ORDER BY treename';
$result = tng_query($query);

while ($row = tng_fetch_assoc($result)) {
  if ($row['gedcom'] != $oldtree) {
    $treelist .= "  <option value=\"{$row['gedcom']}\">{$row['treename']}</option>\n";
  } else {
    $currenttree = $row['treename'];
  }
}
header('Content-type:text/html; charset=' . $sessionCharset);
?>
<form id='treeschange' name='treeschange' action='treesChangeFormAction.php'>
  <header class='modal-header'>
    <h4><?php echo uiTextSnippet('changetree'); ?></h4>
    <p><?php echo uiTextSnippet('currtree'); ?>: <?php echo $currenttree; ?></p>
  </header>
  <div class='modal-body'>
    <div class='row'>
      <div class='col-sm-6'>
        <label for='newtree'><?php echo uiTextSnippet('newtree'); ?>:</label>
        <select class='form-control' id='newtree' name='newtree' data-entity='<?php echo $entity; ?>'>
          <?php echo $treelist; ?>
        </select>
      </div>
      <div class='col-sm-6'>
        <label for='newID'><?php echo $IDlabel; ?>:</label>
        <div class='input-group'>
          <span class='input-group-btn'>
            <button class='btn' id='generate' type='button'><?php echo uiTextSnippet('generate'); ?></button>
          </span>
          <input class='form-control' id='newID' name='newID' type='text'>
          <span class='input-group-btn'>
            <button class='btn' id='check' type='button'><?php echo uiTextSnippet('check'); ?></button>
          </span>
        </div>
      </div>
    </div>
    <br>
    <div class='alert alert-warning' id='checkmsg'><?php echo uiTextSnippet('chwarn'); ?></div>
  </div>
  <footer class='modal-footer'>
    <input name='entity' type='hidden' value="<?php echo $entity; ?>">
    <input name='oldtree' type='hidden' value="<?php echo $oldtree; ?>">
    <input name='entityID' type='hidden' value="<?php echo $entityID; ?>">

    <button class='btn btn-primary' name='submit' type='submit'><?php echo uiTextSnippet('save'); ?></button>
    <button class='btn' name='cancel' type='button' data-dismiss='modal'><?php echo uiTextSnippet('cancel'); ?></button>
  </footer>
</form>
<script src='js/trees.js'></script>