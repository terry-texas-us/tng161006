<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$query = "SELECT * FROM $languagesTable WHERE languageID = \"$languageID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('modifylanguage'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="languages-modifylanguage">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('languages-modifylanguage', $message);
    $navList = new navList('');
    $navList->appendItem([true, "languagesBrowse.php", uiTextSnippet('browse'), "findlang"]);
    $navList->appendItem([$allowAdd, "languagesAdd.php", uiTextSnippet('add'), "addlanguage"]);
    $navList->appendItem([$allowEdit, "#", uiTextSnippet('edit'), "edit"]);
    echo $navList->build("edit");
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <form name='form1' action='languagesEditFormAction.php' method='post' onSubmit="return validateForm();">
            <table>
              <tr>
                <td><?php echo uiTextSnippet('langfolder'); ?>:</td>
                <td>
                  <select name="folder">
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
                      $found_current = 0;
                      foreach ($dirs as $dir) {
                        echo "<option value=\"$dir\"";
                        if ($dir == $row['folder']) {
                          echo " selected";
                          $found_current = 1;
                        }
                        echo ">$dir</option>\n";
                      }
                      if (!$found_current) {
                        echo "<option value=\"{$row['folder']}\" selected>{$row['folder']}</option>\n";
                      }
                      closedir($handle);
                    }
                    ?>
                  </select>
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('langdisplay'); ?>:</td>
                <td><input name='display' type='text' size='50' value="<?php echo $row['display']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('charset'); ?>:</td>
                <td><input name='langcharset' type='text' size='30' value="<?php echo $row['charset']; ?>">
                </td>
              </tr>
            </table>
            <br>
            <input name='languageID' type='hidden' value="<?php echo "$languageID"; ?>">
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