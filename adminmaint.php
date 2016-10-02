<?php
require 'begin.php';
$tngconfig['maint'] = '';
require 'adminlib.php';

$maintenance_mode = true;
require 'checklogin.php';
require 'version.php';

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('maintmode'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
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
