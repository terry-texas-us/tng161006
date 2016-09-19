<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

$file = "$rootpath/whatsnew.txt";

$contents = file($file);

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('whatsnew'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('misc-whatsnewmsg', $message);
    $messageClass = "class='";
    if (isset($color) && $color != '') {
      $messageClass .= "$color'";
    } else {
      $messageClass .= "msgnone'";
    }
    ?>
    <br>
    <a href="whatsnew.php" title='<?php echo uiTextSnippet('preview'); ?>'>
      <img class='icon-sm' src='svg/eye.svg'>
    </a>
    <br>
    <hr>
    <form action='admin_savewhatsnewmsg.php' method='post' name='form1'>
      <label><?php echo uiTextSnippet('wnmsg'); ?></label>
        <?php
        if (isset($message) && $message != '') {
          echo '<p ' . $messageClass . "id='savedmsg'><i>" . $message . '</i></p>';
        }
        ?>
        <br>
        <textarea class='form-control' id='whatsnewmsg' name='whatsnewmsg'>
          <?php
          if (is_array($contents)) {
            foreach ($contents as $line) {
              echo $line;
            }
          }
          ?>
        </textarea>
      <br>
      <button class='btn btn-outline-primary pull-right' name='submit' type='submit'><?php echo uiTextSnippet('save'); ?></button>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/nicedit.js"></script>
<script>
  bkLib.onDomLoaded(function () {
    new nicEditor(/* {fullPanel : true} */).panelInstance('whatsnewmsg');
  });
</script>
</body>
</html>

