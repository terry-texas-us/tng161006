<?php
/*
 * name history: admin_editnote.php
 */

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

$query = "SELECT xnotes.note AS note, xnotes.ID AS xID, secret, persfamID, eventID FROM notelinks, xnotes
    WHERE notelinks.xnoteID = xnotes.ID AND notelinks.ID = '$noteID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

$row['note'] = str_replace('&', '&amp;', $row['note']);
$row['note'] = str_replace('"', '&quot;', $row['note']);

$helplang = findhelp('notes_help.php');
header('Content-type:text/html; charset=' . $session_charset);
?>
<form name='form3' action='' onSubmit="return updateNote(this);">
  <header class='modal-header'>
    <h5><?php echo uiTextSnippet('modifynote'); ?></h5>
    <span><a href="#" onclick="return openHelp('<?php echo $helplang; ?>/notes_help.php');"><?php echo uiTextSnippet('help'); ?></a></span>
  </header>
  <div class='modal-body'>
    <?php echo uiTextSnippet('note'); ?>:
    <textarea class='form-control' name='note' rows='8'><?php echo $row['note']; ?></textarea>
    <label class='form-check-inline'>
      <?php
      echo "<input class='form-check-input' name='private' type='checkbox' value='1'";
      echo ($row['secret'] !== 0) ? ' checked>' : '>';
      echo uiTextSnippet('private');
      ?>
    </label>
  </div>
  <footer class='modal-footer'>
    <input name='xID' type='hidden' value="<?php echo $row['xID']; ?>">
    <input name='ID' type='hidden' value="<?php echo $noteID; ?>">
    <input name='persfamID' type='hidden' value="<?php echo $row['persfamID']; ?>">
    <input name='eventID' type='hidden' value="<?php echo $row['eventID']; ?>">

    <button class='btn btn-outline-primary' name='submit' type='submit'><?php echo uiTextSnippet('save'); ?></button>
    <button class='btn' name='cancel' type='button' onclick="gotoSection('editnote', 'notelist');"><?php echo uiTextSnippet('cancel'); ?></button>
  </footer>
</form>
