<?php
include("begin.php");
$tngconfig['maint'] = "";
include("adminlib.php");

$maintenance_mode = true;
include("checklogin.php");
include("version.php");

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('maintmode'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <div class='table table-sm'>
    <div style="padding:10px">
      <h4><?php echo uiTextSnippet('maintmode'); ?></h4>

      <p><?php echo uiTextSnippet('maintexp'); ?>
      </p><br><br>
    </div>
  </div>
  <?php
  echo $adminFooterSection->build();
  echo scriptsManager::buildScriptElements($flags, 'admin');
  ?>
  </body>
</html>
