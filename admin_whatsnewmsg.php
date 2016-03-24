<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
include("version.php");

if ($assignedtree) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$file = "$rootpath/whatsnew.txt";

$contents = file($file);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('whatsnew'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('misc-whatsnewmsg', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_misc.php", uiTextSnippet('menu'), "misc"]);
    $navList->appendItem([true, "admin_notelist.php", uiTextSnippet('notes'), "notes"]);
    $navList->appendItem([true, "admin_whatsnewmsg.php", uiTextSnippet('whatsnew'), "whatsnew"]);
    $navList->appendItem([true, "admin_mostwanted.php", uiTextSnippet('mostwanted'), "mostwanted"]);
    echo $navList->build("whatsnew");
    $messageClass = "class='";
    if (isset($color) && $color != '') {
      $messageClass .= "$color'";
    } else {
      $messageClass .= "msgnone'";
    }
    ?>
    <a href="whatsnew.php" title='<?php echo uiTextSnippet('preview') ?>'>
      <img class='icon-sm' src='svg/eye.svg'>
    </a>
    <br>
    <hr>
    <form action='admin_savewhatsnewmsg.php' method='post' name='form1'>
      <label><?php echo uiTextSnippet('wnmsg'); ?></label>
        <?php if (isset($message) && $message != '') { ?>
          <p <?php echo $messageClass; ?> id='savedmsg'><i><?php echo $message; ?></i></p>
        <?php } ?>
        <br>
        <textarea id='whatsnewmsg' name='whatsnewmsg' style='width: 100%'>
          <?php if (is_array($contents)) {
            foreach ($contents as $line) {
              echo $line;
            }
          } ?>
        </textarea>
      <br>
      <button class='btn btn-primary-outline pull-right' name='submit' type='submit'><?php echo uiTextSnippet('save'); ?></button>
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

