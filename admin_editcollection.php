<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if (!$allow_edit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

$query = "SELECT * FROM $mediatypes_table WHERE mediatypeID = \"$mediatypeID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

$exportas = $row['exportas'];
if (!$exportas) {
  $exportas = strtoupper($mediatypeID);
  if (substr($exportas, -1) == 'S') {
    $exportas = substr($exportas, 0, -1);
  }
  if ($exportas == "HISTORIE") {
    $exportas = "HISTORY";
  }
}

$helplang = findhelp("collections_help.php");

initMediaTypes();

header("Content-type:text/html; charset=" . $session_charset);
?>

<div style="margin:10px;border:0" id="editcollection">
  <form action="admin_updatecollection.php" method='post' name="collform" id="collform" onsubmit="return updateCollection(this);">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('editcoll'); ?> |
        <a href='#' onclick="return openHelp('<?php echo $helplang; ?>/collections_help.php');"><?php echo uiTextSnippet('help'); ?></a>
      </h4>
    </header>
    <div class='modal-body'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('collid'); ?>:</td>
          <td><?php echo $mediatypeID; ?></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('collexpas'); ?>:</td>
          <td>
            <input id='exportas' name='exportas' type='text' value="<?php echo $exportas; ?>">
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('colldisplay'); ?>:</td>
          <td><input name='display' type='text' size='30' value="<?php echo $row['display']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('collpath'); ?>:</td>
          <td><input name='path' type='text' size='50' value="<?php echo $row['path']; ?>"></td>
        </tr>
        <tr>
          <td></td>
          <td><input type='button' value="<?php echo uiTextSnippet('makefolder'); ?>" onclick="if (document.collform.path.value) {
              makeFolder('newcoll', document.collform.path.value);
            }"> <span id="msg_newcoll"></span></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('collicon'); ?>:</td>
          <td><input name='icon' type='text' size='30' value="<?php echo $row['icon']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('collthumb'); ?>:</td>
          <td><input name='thumb' type='text' size='30' value="<?php echo $row['thumb']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('displayorder'); ?>:</td>
          <td><input name='ordernum' type='text' size='5' value="<?php echo $row['ordernum']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('colllike'); ?>:</td>
          <td>
            <span>
              <select name="liketype">
                <?php
                foreach ($mediatypes as $mediatype) {
                  if (!$mediatype['type']) {
                    $msgID = $mediatype['ID'];
                    echo "  <option value=\"$msgID\"";
                    if ($msgID == $row['liketype']) {
                      echo " selected";
                    }
                    echo ">" . $mediatype['display'] . "</option>\n";
                  }
                }
                ?>
              </select>
            </span>
          </td>
        </tr>
      </table>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <input name='collid' type='hidden' value="<?php echo $mediatypeID; ?>">
      <input name='field' type='hidden' value="<?php echo $field; ?>">
      <input name='selidx' type='hidden' value="<?php echo $selidx; ?>">
      <input type='submit' value="<?php echo uiTextSnippet('save'); ?>">
      <input name='cancel' type='button' value="<?php echo uiTextSnippet('cancel'); ?>" onclick="tnglitbox.remove();">
    </footer>
  </form>
</div>