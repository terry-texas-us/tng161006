<?php
require 'processvars.php';
require 'subroot.php';
// [ts] require_once 'tngconnect.php';

if (!file_exists($subroot . "config.php")) {
  $subroot = $_GET['sr'];
}
require $subroot . 'config.php';
require $subroot . 'templateconfig.php';

require 'begin.php';
require 'adminlib.php';

if ($subroot != $_GET['sr']) {
  $subroot = $_GET['sr'];
}
session_start();
$session_language = $_SESSION['session_language'];
$session_charset = $_SESSION['session_charset'];

$languagesPath = "languages/";
require 'getlang.php';

$link = tng_db_connect($database_host, $database_name, $database_username, $database_password);
if ($link) {
  $adminLogin = 1;
  include 'checklogin.php';
}
require 'version.php';

$error_reporting = ((int)ini_get('error_reporting')) & E_NOTICE;

if (!$sub) {
  $sub = "configuration";
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('setup'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('setup-' . $sub, $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_setup.php", uiTextSnippet('configuration'), "configuration"]);
    $navList->appendItem([true, "admin_diagnostics.php", uiTextSnippet('diagnostics'), "diagnostics"]);
    $navList->appendItem([true, "admin_setup.php?sub=tablecreation", uiTextSnippet('tablecreation'), "tablecreation"]);
    $internallink = $sub == "configuration" ? "config" : "tables";
    echo $navList->build($sub);
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <?php
          if ($sub == "configuration") {
            ?>
            <span><i><?php echo uiTextSnippet('entersysvars'); ?></i></span><br><br>

            <table>
              <tr>
                <td>
                  <h4><img src="img/tng_expand.gif" width="15" height="15"> <a
                            href="admin_genconfig.php"><b><?php echo uiTextSnippet('configsettings'); ?></b></a>
                  </h4>
                  <h4><img src="img/tng_expand.gif" width="15" height="15"> <a
                            href="admin_pedconfig.php"><b><?php echo uiTextSnippet('pedconfigsettings'); ?></b></a>
                  </h4>
                </td>
                <td style="width:50px">&nbsp;</td>
                <td>
                  <h4><img src="img/tng_expand.gif" width="15" height="15"> <a
                            href="admin_logconfig.php"><b><?php echo uiTextSnippet('logconfigsettings'); ?></b></a>
                  </h4>
                  <h4><img src="img/tng_expand.gif" width="15" height="15"> <a
                            href="admin_importconfig.php"><b><?php echo uiTextSnippet('importconfigsettings'); ?></b></a>
                  </h4>
                </td>
                <td style="width:50px">&nbsp;</td>
                <td>
                  <h4><img src="img/tng_expand.gif" width="15" height="15"> <a
                            href="admin_mapconfig.php"><b><?php echo uiTextSnippet('mapconfigsettings'); ?></b></a>
                  </h4>
                  <h4><img src="img/tng_expand.gif" width="15" height="15"> <a
                            href="admin_templateconfig.php"><b><?php echo uiTextSnippet('templateconfigsettings'); ?></b></a>
                  </h4>
                </td>
              </tr>
            </table>
            <br>
            <p><em><?php echo uiTextSnippet('custvars'); ?></em></p>
            <?php
          } elseif ($sub == "tablecreation") {
            ?>
            <span><i><?php echo uiTextSnippet('createdbtables'); ?></i></span><br>

            <p><em><?php echo uiTextSnippet('tcwarning'); ?></em></p>
            <form action="">
              <?php echo uiTextSnippet('collation'); ?>: <input type='text'
                                                                name="collation"/> <?php echo uiTextSnippet('collationexpl'); ?>
              <br><br>
              <input type='button' value="<?php echo uiTextSnippet('createtables'); ?>"
                     onClick="if (confirm('<?php echo uiTextSnippet('conftabledelete'); ?>'))
                             window.location.href = 'admin_tablecreate.php';">
            </form>
            <?php
          }
          ?>
        </td>
      </tr>
    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
</body>
</html>
