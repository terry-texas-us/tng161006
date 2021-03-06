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
header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('addnewlanguage'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
<body id="languages-addnewlanguage">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('languages-addnewlanguage', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'languagesBrowse.php', uiTextSnippet('browse'), 'findlang']);
    //    $navList->appendItem([$allowAdd, 'languagesAdd.php', uiTextSnippet('add'), 'addlanguage']);
    echo $navList->build('addlanguage');
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <form action="languagesAddFormAction.php" method='post' name="form1" onSubmit="return validateForm();">
            <table>
              <tr>
                <td><?php echo uiTextSnippet('langfolder'); ?>:</td>
                <td>
                  <select name="folder">
                    <option value=''></option>
                    <?php
                    chdir($rootpath . $endrootpath . $languagesPath);
                    if ($handle = opendir('.')) {
                      $dirs = [];
                      while ($filename = readdir($handle)) {
                        if (is_dir($filename) && $filename != '..' && $filename != '.') {
                          array_push($dirs, $filename);
                        }
                      }
                      natcasesort($dirs);
                      foreach ($dirs as $dir) {
                        echo "<option value=\"$dir\">$dir</option>\n";
                      }
                      closedir($handle);
                    }
                    ?>
                  </select>
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('langdisplay'); ?>:</td>
                <td><input name='display' type='text' size='50'></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('charset'); ?>:</td>
                <td><input name='langcharset' type='text' size='30' value="<?php echo $sessionCharset; ?>">
                </td>
              </tr>
            </table>
            <br>
            <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
          </form>
        </td>
      </tr>

    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/admin.js"></script>
  <script>
    function validateForm() {
      var rval = true;
      if (document.form1.folder.value.length === 0) {
        alert(textSnippet('enterlangfolder'));
        rval = false;
      } else if (document.form1.display.value.length === 0) {
        alert(textSnippet('enterlangdisplay'));
        rval = false;
      }
      return rval;
    }
  </script>
</body>
</html>
