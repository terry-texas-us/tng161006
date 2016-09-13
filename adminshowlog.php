<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

require $subroot . 'logconfig.php';

$loglines = $adminmaxloglines ? $adminmaxloglines : '';

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('adminlogfile'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container-fluid'>
    <?php echo $adminHeaderSection->build('mostrecentactions'); ?>
    <div>
      <h4><?php echo "$loglines " . uiTextSnippet('mostrecentactions'); ?></h4>
      <div class='small'>
        <?php
        $lines = file($adminlogfile);
        foreach ($lines as $line) {
          echo "$line<br>";
        }
        ?>
      </div>
    </div>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
</body>
</html>