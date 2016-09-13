<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowAdd) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('addnewsource'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='newsource'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('sources-addnewsource', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'sourcesBrowse.php', uiTextSnippet('browse'), 'findsource']);
    $navList->appendItem([$allowAdd, 'sourcesAdd.php', uiTextSnippet('add'), 'addsource']);
    $navList->appendItem([$allowEdit && $allowDelete, 'sourcesMerge.php', uiTextSnippet('merge'), 'merge']);
    echo $navList->build('addsource');
    ?>
    <form name='form1' action='sourcesAddFormAction.php' method='post' onSubmit="return validateForm();">
      <strong><?php echo uiTextSnippet('prefixsourceid'); ?></strong>
      <?php echo uiTextSnippet('sourceid'); ?>:
      <div class='row'>
        <div class='col-md-6'>
          <div class='input-group'>
            <span class='input-group-btn'>
              <button class='btn btn-secondary' id='generate' type='button' onClick="generateID('source', document.form1.sourceID);"><?php echo uiTextSnippet('generate'); ?></button>
            </span>
            <input class='form-control' id='source-id' name='sourceID' type='text' onBlur="this.value = this.value.toUpperCase()" data-check-result=''>
            <span class='input-group-btn'>
              <button class='btn btn-secondary' id='check' type='button' onClick="checkID(document.form1.sourceID.value, 'source', 'checkmsg');"><?php echo uiTextSnippet('check'); ?></button>
            </span>
          </div>
        </div>
        <div id="checkmsg"></div>
        <div class='offset-md-1 col-md-2'>
          <button class='btn btn-outline-primary' name='submit' type='submit' value="<?php echo uiTextSnippet('lockid'); ?>" onClick="document.form1.newscreen[0].checked = true;"><?php echo uiTextSnippet('lockid'); ?></button>
        </div>          
      </div>
      <br>
      <?php require 'micro_newsource.php'; ?>
      <p><strong><?php echo uiTextSnippet('sevslater'); ?></strong></p>
        <input name='save' type='submit' value="<?php echo uiTextSnippet('savecont'); ?>">
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/selectutils.js"></script>
<script>
  $(document).ready(function() {
      generateID('source', document.form1.sourceID);
  });
  
  function validateForm() {
      var rval = true;

      document.form1.sourceID.value = TrimString(document.form1.sourceID.value);
      if (document.form1.sourceID.value.length === 0) {
          alert(textSnippet('entersourceid'));
          return false;
      }
      return rval;
  }
</script>
</body>
</html>
