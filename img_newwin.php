<?php
require 'begin.php';
require 'genlib.php';
require 'getlang.php';

header('Content-type:text/html;charset=' . $sessionCharset);
?>
<!DOCTYPE html>
<html>
<head>
  <?php
  if ($sessionCharset) {
    echo "<meta http-equiv=\"Content-type\" content=\"text/html; charset=$sessionCharset\" />\n";
  }
  $title = $_GET['title'];
  $siteprefix = $sitename ? htmlspecialchars($title ? ': ' . $sitename : $sitename, ENT_QUOTES, $sessionCharset) : '';
  $title = htmlspecialchars($title, ENT_QUOTES, $sessionCharset);
  ?>
  <script src="js/img_viewer.js"></script>
  <title><?php echo $title; ?></title>
</head>

<body onload="calcHeight(window.innerHeight);">
  <script>
    <?php require 'js/img_utils.js'; ?>
  </script>
  <div id="loadingdiv2" style="position:static;">
    <?php echo uiTextSnippet('loading') ?> 
  </div>

  <?php
  $srcUrl = 'img_viewer.php?sa=1&mediaID=' . $_GET['mediaID'] . '&medialinkID=' . $_GET['medialinkID'];
  // [ts] width attribute was 100%. changed to 400. html5 did not like percentage. do not know what the actual width should be.
  ?>
  <iframe id='iframe1' name='iframe1' src="<?php echo $srcUrl; ?>" width='400' height='1' onLoad="calcHeight(1);" style='border: medium'></iframe>
</body>
</html>