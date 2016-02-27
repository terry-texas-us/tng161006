<?php
include("begin.php"); // [ts] args expected entity, oldtree, entityID
include("adminlib.php");

include("checklogin.php");

if ($assignedtree || !$allow_edit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

switch ($entity) {
  case "person":
    $IDlabel = uiTextSnippet('personid');
    break;
  case "source":
    $IDlabel = uiTextSnippet('sourceid');
    break;
  case "repo":
    $IDlabel = uiTextSnippet('repoid');
    break;
}
//use passed in type, gedcom & id to get name
//get list of trees, omit current tree from list in dropdown
$treelist = "  <option value=''></option>\n";
$currenttree = "";

$query = "SELECT gedcom, treename FROM $trees_table ORDER BY treename";
$result = tng_query($query);

while ($row = tng_fetch_assoc($result)) {
  if ($row['gedcom'] != $oldtree) {
    $treelist .= "  <option value=\"{$row['gedcom']}\">{$row['treename']}</option>\n";
  } else {
    $currenttree = $row['treename'];
  }
}
header("Content-type:text/html; charset=" . $session_charset);
?>
<form id='changetree' name='changetree' action='admin_changetree.php' onsubmit="return onChangeTree(this);">
  <header class='modal-header'>
    <h4><?php echo uiTextSnippet('changetree'); ?></h4>
    <p><?php echo uiTextSnippet('currtree'); ?>: <?php echo $currenttree; ?></p>
  </header>
  <div class='modal-body' id='changetree'>
    <table class='table table-sm'>
      <tr>
        <td><?php echo uiTextSnippet('newtree'); ?>:</td>
        <td>
          <select id='newtree' name='newtree'>
            <?php
            echo $treelist;
            ?>
          </select>
        </td>
      </tr>
      <tr>
        <td><?php echo $IDlabel; ?>:</td>
        <td>
          <input id='newID' name='newID' type='text' size='10'>
          <input id='generate' type='button' value="<?php echo uiTextSnippet('generate'); ?>">
          <input id='check' type='button' value="<?php echo uiTextSnippet('check'); ?>">
        </td>
      </tr>
    </table>

    <div id='checkmsg'><em><?php echo uiTextSnippet('chwarn'); ?></em></div>
  </div>
  <footer class='modal-footer'>
    <input name='entity' type='hidden' value="<?php echo $entity; ?>"/>
    <input name='oldtree' type='hidden' value="<?php echo $oldtree; ?>"/>
    <input name='entityID' type='hidden' value="<?php echo $entityID; ?>"/>

    <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    <input name='cancel' type='button' data-dismiss='modal' value="<?php echo uiTextSnippet('cancel'); ?>">
  </footer>
</form>
<script>
    var entity = '<?php echo $entity; ?>';
    
    $('.close').on('click', function () {
        tnglitbox.remove();  
    });
    
    function onChangeTree(form) {
        'use strict';
        tnglitbox.remove();
        return form.newtree.selectedIndex >= 1;
    }
    
    $('#newtree').on('change', function () {
        if (document.changetree.newtree.selectedIndex > 0) {
            generateID(entity, document.changetree.newID, document.changetree.newtree);
        }
    });
    
    $('#generate').on('click', function () {
        if (document.changetree.newtree.selectedIndex > 0) {
            generateID('person', document.changetree.newID, document.changetree.newtree);
        }
    });
    
    $('#check').on('click', function () {
        if (document.changetree.newtree.selectedIndex > 0) {
            checkID(document.changetree.newID.value, 'person', 'checkmsg', document.changetree.newtree);
        }
    });
    
    $('#newID').on('blur', function () {
      this.value = this.value.toUpperCase();
    });
</script>