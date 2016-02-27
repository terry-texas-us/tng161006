<?php
include("begin.php");
include("adminlib.php");

include("checklogin.php");

$query = "SELECT $xnotes_table.note as note, $xnotes_table.ID as xID, secret, $notelinks_table.gedcom as gedcom, persfamID, eventID FROM $notelinks_table,  $xnotes_table
    WHERE $notelinks_table.xnoteID = $xnotes_table.ID AND $notelinks_table.gedcom = $xnotes_table.gedcom AND $notelinks_table.ID = \"$noteID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

$row['note'] = str_replace("&", "&amp;", $row['note']);
$row['note'] = preg_replace("/\"/", "&#34;", $row['note']);

$helplang = findhelp("notes_help.php");
header("Content-type:text/html; charset=" . $session_charset);
?>
<form name='form3' action='' onSubmit="return updateNote(this);">
  <div style='float: right; text-align: center'>
    <input class='bigsave' name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    <p><a href="#" onclick="gotoSection('editnote', 'notelist');"><?php echo uiTextSnippet('cancel'); ?></a></p>
  </div>
  <h4><?php echo uiTextSnippet('modifynote'); ?> |
    <a href="#"
       onclick="return openHelp('<?php echo $helplang; ?>/notes_help.php');"><?php echo uiTextSnippet('help'); ?></a>
  </h4>

  <table class='table table-sm'>
    <tr>
      <td><?php echo uiTextSnippet('note'); ?>:
      </td>
      <td>
        <textarea wrap='soft' cols='60' rows='25' name='note'><?php echo $row['note']; ?></textarea>
      </td>
    </tr>
    <tr>
      <td>&nbsp;
      </td>
      <td>
        <?php
        echo "<input name='private' type='checkbox' value='1'";
        if ($row['secret']) {
          echo " checked";
        }
        echo "> " . uiTextSnippet('private');
        ?>
      </td>
    </tr>
  </table>
  <br>
  <input name='xID' type='hidden' value="<?php echo $row['xID']; ?>">
  <input name='ID' type='hidden' value="<?php echo $noteID; ?>">
  <input name='tree' type='hidden' value="<?php echo $row['gedcom']; ?>">
  <input name='persfamID' type='hidden' value="<?php echo $row['persfamID']; ?>">
  <input name='eventID' type='hidden' value="<?php echo $row['eventID']; ?>">
</form>
