<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowMediaAdd) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('mediaimport'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    $standardtypes = [];
    $moptions = '';
    $likearray = "var like = new Array();\n";
    foreach ($mediatypes as $mediatype) {
      if (!$mediatype['type']) {
        $standardtypes[] = '"' . $mediatype['ID'] . '"';
      }
      $msgID = $mediatype['ID'];
      $moptions .= "  <option value=\"$msgID\"";
      if ($msgID == $mediatypeID) {
        $moptions .= ' selected';
      }
      $moptions .= '>' . $mediatype['display'] . "</option>\n";
      $likearray .= "like['$msgID'] = '{$mediatype['liketype']}';\n";
    }
    $sttypestr = implode(',', $standardtypes);
    ?>

    <?php
    echo $adminHeaderSection->build('media-import', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'mediaBrowse.php', uiTextSnippet('search'), 'findmedia']);
    $navList->appendItem([$allowMediaAdd, 'admin_newmedia.php', uiTextSnippet('addnew'), 'addmedia']);
    $navList->appendItem([$allowMediaEdit, 'admin_ordermediaform.php', uiTextSnippet('text_sort'), 'sortmedia']);
    $navList->appendItem([$allowMediaEdit, 'mediaThumbnails.php', uiTextSnippet('thumbnails'), 'thumbs']);
    //    $navList->appendItem([$allowMediaAdd, 'mediaImport.php', uiTextSnippet('import'), 'import']);
    $navList->appendItem([$allowMediaAdd, 'mediaUpload.php', uiTextSnippet('upload'), 'upload']);
    echo $navList->build('import');
    ?>

    <form action="mediaImportFormAction.php" method='post' name='form1'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('mediatype'); ?>:</td>
          <td>
            <select name="mediatypeID"
                    onChange="switchOnType(this.options[this.selectedIndex].value)">
              <?php
              foreach ($mediatypes as $mediatype) {
                $msgID = $mediatype['ID'];
                echo "  <option value=\"$msgID\">" . $mediatype['display'] . "</option>\n";
              }
              ?>
            </select>
            <?php if ($allowAdd && $allowEdit && $allowDelete) { ?>
              <input name='addnewmediatype' type='button' value="<?php echo uiTextSnippet('addnewcoll'); ?>"
                     onclick="tnglitbox = new ModalDialog('admin_newcollection.php?field=mediatypeID');">
              <input id='editmediatype' name='editmediatype' type='button' value="<?php echo uiTextSnippet('edit'); ?>"
                     style='display: none'
                     onclick="editMediatype(document.form1.mediatypeID);">
              <input id='delmediatype' name='delmediatype' type='button' value="<?php echo uiTextSnippet('delete'); ?>"
                     style='display: none'
                     onclick="confirmDeleteMediatype(document.form1.mediatypeID);">
            <?php } ?>
          </td>
        </tr>
      </table>
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('import'); ?>">
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src='js/admin.js'></script>
<script src='js/mediautils.js'></script>
<script>
  var tnglitbox;
  var stmediatypes = new Array(<?php echo $sttypestr; ?>);
  var allow_edit = <?php echo($allowEdit ? '1' : '0'); ?>;
  var allow_delete = <?php echo($allowDelete ? '1' : '0'); ?>;
  var manage = 1;
  <?php echo $likearray; ?>
</script>
</body>
</html>
