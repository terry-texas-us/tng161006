<?php
/**
 * Name history: admin_editnote2.php
 */

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

$query = "SELECT xnotes.note AS note, secret, notelinks.ID AS nID FROM (notelinks, xnotes)
    WHERE notelinks.xnoteID = xnotes.ID AND xnotes.ID = '$ID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
$row['note'] = str_replace('&', '&amp;', $row['note']);
$row['note'] = preg_replace('/\"/', '&#34;', $row['note']);

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('modifynote'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="misc-modifynote">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('misc-modifynote', $message);
    ?>
    <br>
    <form action="admin_updatenote2.php" name="form2" method='post' onSubmit="return validateForm(this);">
      <div class='row'>
        <div class='col-sm-12'>
          <?php echo uiTextSnippet('note'); ?>:
          <textarea class='form-control' name='note' rows='8'><?php echo $row['note']; ?></textarea>
        </div>
      </div>
      <br>
      <div class='row'>
        <div class='col-md-6'>
          <label class='form-check-inline'>
          <?php
            echo "<input class='form-check-input' name='private' type='checkbox' value='1'";
            echo ($row['secret'] !== 0) ? ' checked>' : '>';
            echo uiTextSnippet('private');
            ?>
          </label>            
        </div>
        <input name='ID' type='hidden' value="<?php echo $row['nID']; ?>">
        <input name='xID' type='hidden' value="<?php echo $ID; ?>">
        <div class='col-md-2'>
          <input class='btn btn-outline-primary btn-block' name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
        </div>
        <div class='col-md-2'>
          <input class='btn btn-block cancel-note' name='cancel' type='button' value="<?php echo uiTextSnippet('cancel'); ?>">
        </div>
      </div>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script>
function validateForm(form) {
  var rval = true;
  if (form.note.value.length === 0) {
    alert(textSnippet('enternote'));
    rval = false;
  }
  return rval;
}
$('.cancel-note').on('click', function() {
  window.location.href = 'notesBrowse.php';
});
</script>
</body>
</html>
