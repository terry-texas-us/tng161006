<?php
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
    $navList = new navList('');
    $navList->appendItem([true, 'admin_misc.php', uiTextSnippet('menu'), 'misc']);
    $navList->appendItem([true, 'admin_notelist.php', uiTextSnippet('notes'), 'notes']);
    $navList->appendItem([true, 'admin_whatsnewmsg.php', uiTextSnippet('whatsnew'), 'whatsnew']);
    $navList->appendItem([true, 'admin_mostwanted.php', uiTextSnippet('mostwanted'), 'mostwanted']);
    $navList->appendItem([$allowEdit, '#', uiTextSnippet('edit'), 'edit']);
    echo $navList->build('edit');
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <form action="admin_updatenote2.php" name="form2" method='post' onSubmit="return validateForm(this);">
            <table>
              <tr>
                <td><?php echo uiTextSnippet('note'); ?>:</td>
                <td>
                  <textarea wrap='soft' cols="80" rows="30" name="note"><?php echo $row['note']; ?></textarea>
                </td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>
                  <?php
                  echo "<input name='private' type='checkbox' value='1'";
                  if ($row['secret']) {
                    echo ' checked';
                  }
                  echo '> ' . uiTextSnippet('private');
                  ?>
                </td>
              </tr>
            </table>
            <input name='ID' type='hidden' value="<?php echo $row['nID']; ?>">
            <input name='xID' type='hidden' value="<?php echo $ID; ?>">
            <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
            <input name='cancel' type='button' value="<?php echo uiTextSnippet('cancel'); ?>"
                onClick="window.location.href = 'admin_notelist.php';">
          </form>
        </td>
      </tr>
    </table>
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
</script>
</body>
</html>
