<?php
include("begin.php");
include("adminlib.php");

include("checklogin.php");

if (!$allow_edit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$query = "SELECT passocID, relationship, reltype, gedcom FROM $assoc_table WHERE assocID = \"$assocID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['relationship'] = preg_replace('/\"/', '&#34;', $row['relationship']);

$helplang = findhelp("assoc_help.php");

header("Content-type:text/html; charset=" . $session_charset);
?>
<form action='' name='findassocform1' onSubmit="return updateAssociation(this);">
  <header class='modal-header'>
    <h4><?php echo uiTextSnippet('modifyassoc'); ?></h4>
    <a href="#" onclick="return openHelp('<?php echo $helplang; ?>/assoc_help.php#add', 'newwindow', 'height=500,width=700,resizable=yes,scrollbars=yes'); newwindow.focus();"><?php echo uiTextSnippet('help'); ?></a>
  </header>
  <div class='modal-body'>
    <table class='table table-sm'>
      <tr>
        <td colspan='2'>
          <input name='reltype' type='radio' value='I'<?php if ($row['reltype'] == 'I') {echo " checked";} ?>
                 onclick="activateAssocType('I');"/> <?php echo uiTextSnippet('person'); ?> &nbsp;&nbsp;
          <input name='reltype' type='radio' value='F'<?php if ($row['reltype'] == 'F') {echo " checked";} ?>
                 onclick="activateAssocType('F');"/> <?php echo uiTextSnippet('family'); ?>
        </td>
      </tr>
      <tr>
        <td>
          <span id="person_label"<?php if ($row['reltype'] == 'F') {
            echo " style=\"display:none\"";
          } ?>><?php echo uiTextSnippet('personid'); ?></span>
          <span id="family_label"<?php if ($row['reltype'] == 'I') {
            echo " style=\"display:none\"";
          } ?>><?php echo uiTextSnippet('familyid'); ?></span>:
        </td>
        <td>
          <input name='passocID' type='text' value="<?php echo $row['passocID']; ?>">&nbsp;<?php echo uiTextSnippet('or'); ?>&nbsp;
          <a href="#" onclick="return findItem(assocType, 'passocID', '<?php echo $tree; ?>', '<?php echo $assignedbranch; ?>');" title="<?php echo uiTextSnippet('find'); ?>">
            <img class='icon-sm-inline' src='svg/magnifying-glass.svg'>
          </a>
        </td>
      </tr>
      <tr>
        <td><span><?php echo uiTextSnippet('relationship'); ?>:</span></td>
        <td><input name='relationship' type='text' value="<?php echo $row['relationship']; ?>"></td>
      </tr>
    </table>
  </div> <!-- .modal-body -->
  <footer class='modal-footer'>
    <input name='assocID' type='hidden' value="<?php echo $assocID; ?>">
    <input name='tree' type='hidden' value="<?php echo $row['gedcom']; ?>">
    <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
  </footer>
</form>