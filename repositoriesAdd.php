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
$headSection->setTitle(uiTextSnippet('addnewrepo'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='newrepo'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('repositories-addnewrepo', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'repositoriesBrowse.php', uiTextSnippet('search'), 'findrepo']);
    //    $navList->appendItem([$allowAdd, 'repositoriesAdd.php', uiTextSnippet('add'), 'addrepo']);
    $navList->appendItem([$allowEdit && $allowDelete, 'repositoriesMerge.php', uiTextSnippet('merge'), 'merge']);
    echo $navList->build('addrepo');
    ?>
    <form name='form1' action='repositoriesAddFormAction.php' method='post' onSubmit="return validateForm();">
      <header id='repository-header'>
        <span><strong><?php echo uiTextSnippet('prefixrepoid'); ?></strong></span>
        <?php echo uiTextSnippet('repoid'); ?>:
        <div class='row'>
          <div class='col-sm-6'>
            <div class='input-group'>
              <span class='input-group-btn'>
                <button class='btn' id='generate' type='button' onClick="generateID('repo', document.form1.repoID);"><?php echo uiTextSnippet('generate'); ?></button>
              </span>
              <input class='form-control' name='repoID' type='text' onBlur="this.value = this.value.toUpperCase()" required>
              <span class='input-group-btn'>
                <button class='btn' id='check' type='button' onClick="checkID(document.form1.repoID.value, 'repo', 'checkmsg');"><?php echo uiTextSnippet('check'); ?></button>
              </span>
            </div>
          </div>
          <div class='col-sm-2'>
            <button class='btn btn-outline-primary' name='submit' type='submit' onClick="document.form1.newscreen[0].checked = true;"><?php echo uiTextSnippet('lockid'); ?></button>
          </div>
          <span id="checkmsg"></span>
        </div>
      </header>
      <div>        
        <br>
        <?php echo uiTextSnippet('name'); ?>:
        <input class='form-control' name='reponame' type='text' required>
        <?php // echo '(' . uiTextSnippet('required') . ')'; ?>
        <div class='row'>
          <div class='col-sm-6'>
            <?php echo uiTextSnippet('address1'); ?>:
            <br>
            <input class='form-control' name='address1' type='text'>
          </div>
          <div class='col-sm-6'>
            <?php echo uiTextSnippet('address2'); ?>:
            <br>
            <input class='form-control' name='address2' type='text'>
          </div>
        </div>
        <div class='row'>
          <div class='col-sm-6'>
            <?php echo uiTextSnippet('city'); ?>:
            <br>
            <input class='form-control' name='city' type='text'>
          </div>
          <div class='col-sm-6'>
            <?php echo uiTextSnippet('stateprov'); ?>:
            <br>
            <input class='form-control' name='state' type='text'>
          </div>
        </div>
        <?php echo uiTextSnippet('zip'); ?>:
        <input class='form-control' name='zip' type='text'>
        <?php echo uiTextSnippet('countryaddr'); ?>:
        <input class='form-control' name='country' type='text'>
        <?php echo uiTextSnippet('phone'); ?>:
        <input class='form-control' name='phone' type='text' value=''>
        <?php echo uiTextSnippet('email'); ?>:
        <input class='form-control' name='email' type='text' value=''>
        <?php echo uiTextSnippet('website'); ?>:
        <input class='form-control' name='www' type='text' value=''>
      </div>
      <footer id='repository-footer'>
        <p><strong><?php echo uiTextSnippet('revslater'); ?></strong></p>
        <button class='btn btn-primary' name='save' type='submit'><?php echo uiTextSnippet('savecont'); ?></button>
      </footer>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/selectutils.js"></script>
<script>
  $(document).ready(function() {
      generateID('repo', document.form1.repoID);
  });

  function validateForm() {
    var rval = true;

    document.form1.repoID.value = TrimString(document.form1.repoID.value);
    if (document.form1.repoID.value.length === 0) {
      alert(textSnippet('enterrepoid'));
      return false;
    } else if (document.form1.reponame.value.length === 0) {
      alert(textSnippet('enterreponame'));
      return false;
    }
    return rval;
  }
</script>
</body>
</html>
