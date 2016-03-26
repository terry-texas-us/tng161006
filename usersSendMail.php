<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('emailusers'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='users-emailmessage'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('users-emailmessage', $message);
    $navList = new navList('');
    $navList->appendItem([true, "usersBrowse.php", uiTextSnippet('search'), "finduser"]);
    $navList->appendItem([$allowAdd, "usersAdd.php", uiTextSnippet('add'), "adduser"]);
    $navList->appendItem([$allowEdit, "usersReview.php", uiTextSnippet('review') . $revstar, "review"]);
//    $navList->appendItem([true, "usersSendMail.php", uiTextSnippet('email'), "mail"]);
    echo $navList->build("mail");
    ?>
    <form id='users-send-mail' name='form1' action='usersSendMailFormAction.php' method='post'>
      <span><?php echo uiTextSnippet('subject'); ?>:</span>
      <input class='form-control' name='subject' type='text' maxlength='50' required>
      <span><?php echo uiTextSnippet('messagetext'); ?>:</span>
      <textarea class='form-control' rows='8' name='messagetext' required></textarea>
      <span><br><strong><?php echo uiTextSnippet('selectgroup'); ?></strong></span>
      <div class='row'>
        <div class='col-sm-2'>
          <span><?php echo uiTextSnippet('tree'); ?>*:</span>
        </div>
        <div class='col-sm-4'>
          <select id='gedcom' name='gedcom'>
            <option value=''></option>
            <?php
            $query = "SELECT gedcom, treename FROM $trees_table ORDER BY treename";
            $treeresult = tng_query($query);

            while ($treerow = tng_fetch_assoc($treeresult)) {
              echo "  <option value=\"{$treerow['gedcom']}\">{$treerow['treename']}</option>\n";
            }
            ?>
          </select>
        </div>
          <div class='col-sm-2'>
            <span><?php echo uiTextSnippet('branch'); ?>**:</span>
          </div>
          <div class='col-sm-4'>
            <select name="branch" id="branch">
              <option value=''></option>
              <option value='' selected><?php echo uiTextSnippet('nobranch'); ?></option>
            </select>
          </div>
      </div>
      <br>
      <button class='btn btn-primary' name='submit' type='submit'><?php echo uiTextSnippet('send'); ?></button>
    </form>
    <br>
    <p>
      <span>*<?php echo uiTextSnippet('treemailmsg'); ?></span>
      <br>
      <span>**<?php echo uiTextSnippet('branchmailmsg'); ?></span>
      <br>
    </p>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/selectutils.js"></script>
<script src='js/users.js'></script>
<script>
    <?php require 'branchlibjs.php'; ?>

    $('#gedcom').on('change', function () {
      var tree = getTree(this);
      if (!tree) {
        tree = 'none';
      }
      <?php echo $swapbranches; ?>
    });
</script>
</body>
</html>