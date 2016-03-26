<?php
require 'tng_begin.php';

$newroot = preg_replace('/\//', '', $rootpath);
$newroot = preg_replace('/ /', '', $newroot);
$newroot = preg_replace('/\./', '', $newroot);
$ref = "tngbookmarks_$newroot";

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('bookmarks'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/bookmarks.svg'><?php echo uiTextSnippet('bookmarks'); ?></h2>
    <br clear='left'>
    <p><?php echo uiTextSnippet('bkmkvis'); ?></p>
    <ul>
    <?php
    if (isset($_COOKIE[$ref])) {
      $bcount = 0;
      $bookmarks = explode("|", $_COOKIE[$ref]);
      foreach ($bookmarks as $bookmark) {
        if (trim($bookmark)) {
          echo "<li>" . stripslashes($bookmark) . " | <a href=\"ajx_deletebookmark.php?idx=$bcount\">" . uiTextSnippet('remove') . "</a></li>\n";
          $bcount++;
        }
      }
    } else {
      echo "<li>0 " . uiTextSnippet('bookmarks') . "</li>";
    }
    ?>
    </ul><br>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
