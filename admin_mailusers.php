<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

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
    $navList->appendItem([true, "admin_users.php", uiTextSnippet('search'), "finduser"]);
    $navList->appendItem([$allow_add, "admin_newuser.php", uiTextSnippet('addnew'), "adduser"]);
    $navList->appendItem([$allow_edit, "admin_reviewusers.php", uiTextSnippet('review') . $revstar, "review"]);
    $navList->appendItem([true, "admin_mailusers.php", uiTextSnippet('email'), "mail"]);
    echo $navList->build("mail");
    ?>
    <form name='form1' action='admin_sendmailusers.php' method='post' onSubmit="return validateForm();">
      <table class='table table-sm'>
        <tr>
          <td><span><?php echo uiTextSnippet('subject'); ?>:</span></td>
          <td><input name='subject' type='text' size='50' maxlength='50'></td>
        </tr>
        <tr>
          <td><span><?php echo uiTextSnippet('messagetext'); ?>:</span></td>
          <td><textarea cols="50" rows="15" name="messagetext"></textarea></td>
        </tr>
        <tr>
          <td colspan='2'>
            <span><br><strong><?php echo uiTextSnippet('selectgroup'); ?></strong></span></td>
        </tr>
        <tr>
          <td>
            <?php echo uiTextSnippet('tree'); ?>*:
          </td>
          <td>
            <select id='gedcom' name='gedcom'>
              <option value=''></option>
              <?php
              $query = "SELECT gedcom, treename FROM $trees_table ORDER BY treename";
              $treeresult = tng_query($query);

              while ($treerow = tng_fetch_assoc($treeresult)) {
                echo "	<option value=\"{$treerow['gedcom']}\">{$treerow['treename']}</option>\n";
              }
              ?>
            </select>
          </td>
        </tr>
        <tr>
          <td>
            <?php echo uiTextSnippet('branch'); ?>**:
          </td>
          <td>
            <select name="branch" id="branch">
              <option value=''></option>
              <option value='' selected><?php echo uiTextSnippet('nobranch'); ?></option>
            </select>
          </td>
        </tr>
      </table>
      <br>
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('send'); ?>">
    </form>
    <br>
    <p>
      <?php
      echo "*" . uiTextSnippet('treemailmsg') . "<br>\n";
      echo "**" . uiTextSnippet('branchmailmsg') . "<br>\n";
      ?>
    </p>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/selectutils.js"></script>
  <script>
    <?php include("branchlibjs.php"); ?>

    $('#gedcom').on('change', function () {
      var tree = getTree(this);
      if (!tree) {
        tree = 'none';
      }
      <?php echo $swapbranches; ?>
    });
    
    function validateForm() {
      var rval = true;
      if (document.form1.subject.value.length === 0) {
        alert(textSnippet('entersubject'));
        rval = false;
      } else if (document.form1.messagetext.value.length === 0) {
        alert(textSnippet('entermsgtext'));
        rval = false;
      }
      return rval;
    }
  </script>
</body>
</html>