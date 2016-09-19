<?php
require 'processvars.php';
require 'subroot.php';
// [ts] require_once 'tngconnect.php';

if (!file_exists($subroot . 'config.php')) {
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

$languagesPath = 'languages/';
require 'getlang.php';

$link = tng_db_connect($database_host, $database_name, $database_username, $database_password);
if ($link) {
  $adminLogin = 1;
  include 'checklogin.php';
}
require 'version.php';

$error_reporting = ((int) ini_get('error_reporting')) & E_NOTICE;

if (!$sub) {
  $sub = 'configuration';
}
header('Content-type: text/html; charset=' . $session_charset);
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
    // $navList->appendItem([true, 'admin_setup.php', uiTextSnippet('configuration'), 'configuration']);
    $navList->appendItem([true, 'admin_diagnostics.php', uiTextSnippet('diagnostics'), 'diagnostics']);
    $navList->appendItem([true, 'admin_setup.php?sub=tablecreation', uiTextSnippet('tablecreation'), 'tablecreation']);
    $internallink = $sub == 'configuration' ? 'config' : 'tables';
    echo $navList->build($sub);
    if ($sub == 'configuration') {
    ?>
      <span><i><?php echo uiTextSnippet('entersysvars'); ?></i></span><br><br>

      <div class='row'>
        <div class='col-md-4 h4'>
          <img src="img/tng_expand.gif" width="15" height="15"> <a href="admin_genconfig.php"><?php echo uiTextSnippet('configsettings'); ?></a>
        </div>
        <div class='col-md-4 h4'>
          <img src="img/tng_expand.gif" width="15" height="15"> <a href="admin_logconfig.php"><?php echo uiTextSnippet('logconfigsettings'); ?></a>
        </div>
        <div class='col-md-4 h4'>
          <img src="img/tng_expand.gif" width="15" height="15"> <a href="admin_mapconfig.php"><?php echo uiTextSnippet('mapconfigsettings'); ?></a>
        </div>
      </div>
      <hr>
      <div class='row'>
        <div class='col-md-4 h4'>
          <img src="img/tng_expand.gif" width="15" height="15"> <a href="admin_pedconfig.php"><?php echo uiTextSnippet('pedconfigsettings'); ?></a>
        </div>
        <div class='col-md-4 h4'>
          <img src="img/tng_expand.gif" width="15" height="15"> <a href="admin_importconfig.php"><?php echo uiTextSnippet('importconfigsettings'); ?></a>
        </div>
      </div>
      <hr>
      <div class='row'>
        <div class='col-md-4'>
          <h4><img src="img/tng_expand.gif" width="15" height="15"> <a href="admin_templateconfig.php"><?php echo uiTextSnippet('templateconfigsettings'); ?></a></h4>
        </div>
        <div class='col-md-4'>
          <h4><img src="img/tng_expand.gif" width="15" height="15"> <a href="admin_whatsnewmsg.php"><?php echo uiTextSnippet('whatsnew'); ?></a></h4>
          <blockquote><?php echo uiTextSnippet('whatsnewblurb'); ?></blockquote>
        </div>
        <div class='col-md-4'>
          <h4><img src="img/tng_expand.gif" width="15" height="15"> <a href="admin_mostwanted.php"><?php echo uiTextSnippet('mostwanted'); ?></a></h4>
          <blockquote><?php echo uiTextSnippet('mwblurb'); ?></blockquote>
        </div>
      </div>
    <?php
    } elseif ($sub === 'tablecreation') {
    ?>
      <span><i><?php echo uiTextSnippet('createdbtables'); ?></i></span><br>

      <p><em><?php echo uiTextSnippet('tcwarning'); ?></em></p>
      <form action=''>
        <?php echo uiTextSnippet('collation'); ?>: <input type='text' name='collation'> <?php echo uiTextSnippet('collationexpl'); ?>
        <br>
        <input id='create-tables' type='button' value='<?php echo uiTextSnippet('createtables'); ?>'>
      </form>
    <?php
    }
    ?>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script>
    $('#create-tables').on('click', function () {
        if (confirm('<?php echo uiTextSnippet('conftabledelete'); ?>')) {
            window.location.href = 'admin_tablecreate.php';
        }
    });
  </script>
</body>
</html>
