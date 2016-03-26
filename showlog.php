<?php
require 'tng_begin.php';

require $subroot . 'logconfig.php';

if ($maxloglines) {
  $loglines = $maxloglines;
} else {
  $loglines = "";
}
$owner = $sitename ? $sitename : $dbowner;

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle("$loglines " . uiTextSnippet('mostrecentactions'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='showlog'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2 class='header'><?php echo "$loglines " . uiTextSnippet('mostrecentactions'); ?></h2>
    <br clear='all'>
    <?php
    if ($autorefresh) {
      echo "<p><a href=\"showlog.php?autorefresh=0\">" . uiTextSnippet('refreshoff') . "</a></p>\n";
    } else {
      echo "<p><a href=\"showlog.php?autorefresh=1\">" . uiTextSnippet('autorefresh') . "</a></p>\n";
    }
    ?>
    <div id="content">
      <?php
      if (!$autorefresh) {
        $lines = file($logfile);
        foreach ($lines as $line) {
          echo "$line<br>\n";
        }
      }
      ?>
    </div>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php 
  echo scriptsManager::buildScriptElements($flags, 'public');
  if ($autorefresh) { 
  ?>
    <script>
      function refreshPage() {
        var loader1 = new net.ContentLoader('ajx_logxml.php', FillPage, null, "POST", '');
        var timer = setTimeout("refreshPage()", 30000);
      }

      function FillPage() {
        var content = document.getElementById("content");
        content.innerHTML = this.req.responseText;
      }
      refreshPage();
    </script>
  <?php } ?>
</body>
</html>