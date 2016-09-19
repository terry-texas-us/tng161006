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
    $navList->appendItem([true, 'albumsBrowse.php', uiTextSnippet('browse'), 'findalbum']);
    // $navList->appendItem([$allowAdd, 'albumsAdd.php', uiTextSnippet('add'), 'addalbum']);
    $navList->appendItem([$allowEdit, 'albumsSort.php', uiTextSnippet('text_sort'), 'sortalbums']);
    echo $navList->build('addalbum');
    ?>
    <form name='form1' action='albumsAddFormAction.php' method='post' onSubmit="return validateForm();">
      <?php echo displayToggle('plus0', 1, 'details', uiTextSnippet('existingalbuminfo'), uiTextSnippet('infosubt')); ?>

      <div id='details'>
        <?php echo uiTextSnippet('albumname'); ?>:
        <input class='form-control' name='albumname' type='text' size='50'>
        <?php echo uiTextSnippet('description'); ?>:
        <textarea class='form-control' name='description' rows='3'></textarea>
        <?php echo uiTextSnippet('keywords'); ?>:
        <textarea class='form-control' name='keywords' rows='3'></textarea>
        <?php echo uiTextSnippet('active'); ?>:
        <div class='row'>
          <div class='col-md-4'>
            <input name='active' type='radio' value='1' checked> <?php echo uiTextSnippet('yes'); ?> &nbsp; 
            <input name='active' type='radio' value='0'> <?php echo uiTextSnippet('no'); ?>
          </div>
          <div class='col-md-4'>
            <input name='alwayson' type='checkbox' value='1'> <?php echo uiTextSnippet('alwayson'); ?>
          </div>
        </div>
      </div>
      <p><strong><?php echo uiTextSnippet('alblater'); ?></strong></p>
      <input name='saveit' type='submit' value="<?php echo uiTextSnippet('savecont'); ?>">
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
