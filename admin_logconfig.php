<?php
require 'begin.php';
require $subroot . 'logconfig.php';
require 'adminlib.php';

if ($link) {
  $adminLogin = 1;
  include 'checklogin.php';
  include 'version.php';

  if (!$allowEdit) {
    $message = uiTextSnippet('norights');
    header("Location: admin_login.php?message=" . urlencode($message));
    exit;
  }
  $query = "SELECT gedcom, treename FROM $treesTable ORDER BY treename";
  $result = tng_query($query);
} else {
  $result = false;
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('modifylogsettings'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('setup-configuration-logconfigsettings', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_setup.php", uiTextSnippet('configuration'), "configuration"]);
    $navList->appendItem([true, "admin_diagnostics.php", uiTextSnippet('diagnostics'), "diagnostics"]);
    $navList->appendItem([true, "admin_setup.php?sub=tablecreation", uiTextSnippet('tablecreation'), "tablecreation"]);
    $navList->appendItem([true, "#", uiTextSnippet('logconfigsettings'), "log"]);
    echo $navList->build("log");
    ?>
    <form action="admin_updatelogconfig.php" method='post' name='form1'>
      <table class='table table-sm'>
        <tr>
          <td>
            <span><?php echo uiTextSnippet('logfilename') . " " . uiTextSnippet('text_public'); ?>:</span>
          </td>
          <td><input name='logname' type='text' value="<?php echo $logname; ?>"></td>
        </tr>
        <tr>
          <td>
            <span><?php echo uiTextSnippet('maxloglines') . " " . uiTextSnippet('text_public'); ?>:</span>
          </td>
          <td><input name='maxloglines' type='text' value="<?php echo $maxloglines; ?>" size='5'></td>
        </tr>
        <tr>
          <td><span><?php echo uiTextSnippet('badhosts'); ?>*:</span></td>
          <td><input name='badhosts' type='text' value="<?php echo $badhosts; ?>" size='80'></td>
        </tr>
        <tr>
          <td><span><?php echo uiTextSnippet('exusers'); ?>*:</span></td>
          <td><input name='exusers' type='text' value="<?php echo $exusers; ?>" size='80'></td>
        </tr>
        <tr>
          <td><span><?php echo uiTextSnippet('logfilename') . " " . uiTextSnippet('admin'); ?>
              :</span></td>
          <td><input name='adminlogfile' type='text' value="<?php echo $adminlogfile; ?>">
          </td>
        </tr>
        <tr>
          <td><span><?php echo uiTextSnippet('maxloglines') . " " . uiTextSnippet('admin'); ?>
              :</span></td>
          <td><input name='adminmaxloglines' type='text' value="<?php echo $adminmaxloglines; ?>" size='5'></td>
        </tr>
        <tr>
          <td colspan='2'><span><br><?php echo uiTextSnippet('blockemail'); ?>
              <br><br></span></td>
        </tr>
        <tr>
          <td><span><?php echo uiTextSnippet('addrcontains'); ?>*:</span></td>
          <td><input name='addr_exclude' type='text' value="<?php echo $addr_exclude; ?>" size='80'>
          </td>
        </tr>
        <tr>
          <td><span><?php echo uiTextSnippet('msgcontains'); ?>*:</span></td>
          <td><input name='msg_exclude' type='text' value="<?php echo $msg_exclude; ?>" size='80'>
          </td>
        </tr>
      </table>
      <br>&nbsp;
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    </form>
    <p>*<?php echo uiTextSnippet('commas'); ?></p>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
</body>
</html>

