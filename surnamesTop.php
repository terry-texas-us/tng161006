<?php
/**
 * Name history: surnames100.php
 */

require 'tng_begin.php';

$topnum = preg_replace('/[^0-9]/', '', $topnum);

$pageName = uiTextSnippet('surnames') . ': ' . uiTextSnippet('top') . " $topnum";

$logstring = "<a href='surnamesTop.php?topnum=$topnum'>" . xmlcharacters($pageName) . '</a>';
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle($pageName);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='surnames-top'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/person.svg'><?php echo $pageName; ?></h2>
    <br class='clearleft'>
    <nav class='breadcrumb'>
      <a class='breadcrumb-item' href='surnames.php'><?php echo uiTextSnippet('mainsurnamepage'); ?></a>
      <span class='breadcrumb-item'><?php echo $pageName; ?></span>      
    </nav>
    <hr>
    <form class='form-inline' action='surnamesTop.php' method='get'>
      <div class='form-group'>
        <label for='topnum'><?php echo uiTextSnippet('showtop'); ?></label>
        <input class='form-control' name='topnum' type='text' value="<?php echo $topnum; ?>" size='4' maxlength='4'><span class='verbose-md'><?php echo uiTextSnippet('byoccurrence'); ?></span>
        <input class='btn btn-outline-secondary' type='submit' value="<?php echo uiTextSnippet('go'); ?>">
      </div>
    </form>
    <br>
    <div>
      <h4><?php echo $pageName . ' (' . uiTextSnippet('totalnames') . '):'; ?></h4>
      <br>
      <?php require 'surnamestable.php'; ?>
    </div>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
  