<?php
require 'begin.php';
require 'genlib.php';
require 'getlang.php';

require 'functions.php';
require 'checklogin.php';
require 'showmedialib.php';

header('Content-type:text/html; charset=' . $session_charset);
?>
<div id='slideshell'>
  <header class='modal-header'></header>
  <div class='modal-body'>
    <div id='slideshow'>
      <div id='loadingdiv' style='display: none'><?php echo uiTextSnippet('loading'); ?></div>
      <div id='div1' class='slide'>
        <?php
        initMediaTypes();

        require 'showmediaxmllib.php';

        echo "<p class='topmargin'>$pagenav</p>";
        echo '<h4>' . truncateIt($description, 100) . "</h4>\n";

        if ($noneliving || $imgrow['alwayson']) {
          showMediaSource($imgrow, true);
        } else {
          ?>
          <div style='width: 400px; height: 300px; border: 1px solid black'><?php echo uiTextSnippet('living'); ?></div>
          <?php
        }
        ?>
      </div>
      <div id='div0' class='slide' style='display: none'></div>
    </div>
  </div> <!-- .modal-body -->
  <footer class='modal-footer'></footer>
</div>