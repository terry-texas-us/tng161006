<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if (!$allow_edit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$query = "SELECT * FROM $languages_table WHERE languageID = \"$languageID\"";
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
    $navList->appendItem([true, "admin_languages.php", uiTextSnippet('search'), "findlang"]);
    $navList->appendItem([$allow_add, "admin_newlanguage.php", uiTextSnippet('addnew'), "addlanguage"]);
    $navList->appendItem([$allow_edit, "#", uiTextSnippet('edit'), "edit"]);
    echo $navList->build("edit");
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <form action="admin_updatelanguage.php" method='post' name="form1" onSubmit="return validateForm();">
            <table>
              <tr>
                <td><?php echo uiTextSnippet('langfolder'); ?>:</td>
                <td>
                  <select name="folder">
                    <?php
                    @chdir($rootpath . $endrootpath . $languages_path);
                    if ($handle = @opendir('.')) {
                      $dirs = array();
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