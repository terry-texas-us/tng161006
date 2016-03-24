<?php
require 'begin.php';
require 'adminlib.php';

if ($link) {
  $adminLogin = 1;
  require 'checklogin.php';
  include("version.php");
}

require("adminlog.php");

if ($assignedtree) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

$badtables = "";
$collation = "";
include("tabledefs.php");

if (!$badtables) {
  adminwritelog(uiTextSnippet('createtables'));
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('tablecreation'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <?php
  echo $adminHeaderSection->build('setup-tablecreation', $message);
  $navList = new navList('');
  $navList->appendItem([true, "admin_setup.php", uiTextSnippet('configuration'), "configuration"]);
  $navList->appendItem([true, "admin_setup.php?sub=diagnostics", uiTextSnippet('diagnostics'), "diagnostics"]);
  $navList->appendItem([true, "admin_setup.php?sub=tablecreation", uiTextSnippet('tablecreation'), "tablecreation"]);
  echo $navList->build("tablecreation");
  ?>
  <table class='table table-sm'>
    <tr>
      <td>
        <p>
          <?php
          if ($badtables) {
            echo "Tables not created: $badtables";
          } else {
            echo uiTextSnippet('tablesuccess');
          }
          ?>
        </p>
        <p>
          <a href="admin_setup.php"><?php echo uiTextSnippet('backtosetup'); ?></a>.
        </p>
      </td>
    </tr>
  </table>
  <?php
  echo $adminFooterSection->build();
  echo scriptsManager::buildScriptElements($flags, 'admin');
  ?>
</body>
</html>
