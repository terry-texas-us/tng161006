<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowMediaAdd) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('addnewalbum'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="albums-addnewalbum">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('albums-addnewalbum', $message);
    $navList = new navList('');
    $navList->appendItem([true, "albumsBrowse.php", uiTextSnippet('browse'), "findalbum"]);
    $navList->appendItem([$allowAdd, "albumsAdd.php", uiTextSnippet('add'), "addalbum"]);
    $navList->appendItem([$allowEdit, "albumsSort.php", uiTextSnippet('text_sort'), "sortalbums"]);
    echo $navList->build("addalbum");
    ?>
    <form name='form1' action='albumsAddFormAction.php' method='post' onSubmit="return validateForm();">
      <table class='table table-sm'>
        <tr>
          <td>
            <?php echo displayToggle("plus0", 1, "details", uiTextSnippet('existingalbuminfo'), uiTextSnippet('infosubt')); ?>

            <div id='details'>
              <table>
                <tr>
                  <td><?php echo uiTextSnippet('albumname'); ?>:</td>
                  <td>
                    <input name='albumname' type='text' size='50'>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('description'); ?>:</td>
                  <td>
                    <textarea cols="60" rows="3" name='description'></textarea>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('keywords'); ?>:</td>
                  <td>
                    <textarea cols="60" rows="3" name="keywords"></textarea>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('active'); ?>:</td>
                  <td>
                    <input name='active' type='radio' value='1' checked> <?php echo uiTextSnippet('yes'); ?> &nbsp; 
                    <input name='active' type='radio' value='0'> <?php echo uiTextSnippet('no'); ?>
                  </td>
                </tr>
                <tr>
                  <td colspan='2'>
                    <input name='alwayson' type='checkbox' value='1'> <?php echo uiTextSnippet('alwayson'); ?>
                  </td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <p><strong><?php echo uiTextSnippet('alblater'); ?></strong></p>
            <input name='saveit' type='submit' value="<?php echo uiTextSnippet('savecont'); ?>">
          </td>
        </tr>
      </table>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/admin.js"></script>
  <script>
    function validateForm() {
      var rval = true;
      if (document.form1.albumname.value.length === 0) {
        alert(textSnippet('enteralbumname'));
        rval = false;
      }
      return rval;
    }
  </script>
</body>
</html>
