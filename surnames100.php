<?php
require 'tng_begin.php';

$topnum = preg_replace("/[^0-9]/", '', $topnum);

$text['top30'] = preg_replace("/xxx/", $topnum, $text['top30']);

$logstring = "<a href='surnames100.php?topnum=$topnum'>" . xmlcharacters(uiTextSnippet('surnamelist') . ": " . uiTextSnippet('top') . " $topnum") . "</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('surnamelist') . " &mdash; " . uiTextSnippet('top') . " $topnum");
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/person.svg'><?php echo uiTextSnippet('surnamelist') . ": " . uiTextSnippet('top30'); ?></h2>
    <br class='clearleft'>
    <?php

    beginFormElement("surnames100", "get");
    ?>
      <div>
        <?php echo uiTextSnippet('showtop'); ?>&nbsp;
        <input name='topnum' type='text' value="<?php echo $topnum; ?>" size='4' maxlength='4'/> <?php echo uiTextSnippet('byoccurrence'); ?>&nbsp;
        <input type='submit' value="<?php echo uiTextSnippet('go'); ?>"/>
      </div>
    <?php endFormElement(); ?>
      <br>

      <div>
        <h4><?php echo uiTextSnippet('top30') . " (" . uiTextSnippet('totalnames') . "):"; ?></h4>
        <p class="small"><?php echo uiTextSnippet('showmatchingsurnames') . "&nbsp;&nbsp;&nbsp;<a href='surnames.php'>" . uiTextSnippet('mainsurnamepage') . "</a> &nbsp;|&nbsp; <a href = 'surnames-all.php'>" . uiTextSnippet('showallsurnames') . "</a>"; ?></p>
        <?php require 'surnamestable.php'; ?>
      </div>
      <br>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
