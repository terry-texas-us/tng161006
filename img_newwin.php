<?php
require 'begin.php';
require 'genlib.php';
require 'getlang.php';

header('Content-type:text/html;charset=' . $session_charset);
?>
<!DOCTYPE html>
<html>
<head>
  <?php
  if ($session_charset) {
    echo "<meta http-equiv=\"Content-type\" content=\"text/html; charset=$session_charset\" />\n";
  }
  $title = $_GET['title'];
  $siteprefix = $sitename ? htmlspecialchars($title ? ': ' . $sitename : $sitename, ENT_QUOTES, $session_charset) : '';
  $title = htmlspecialchars($title, ENT_QUOTES, $session_charset);
  ?>
  <link href="css/img_viewer.css" rel="stylesheet" type="text/css">
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
  <iframe name="iframe1" id="iframe1" src="<?php echo $srcUrl; ?>" width="400" height="1" onLoad="calcHeight(1);" style="border: medium"></iframe>
</body>
</html>