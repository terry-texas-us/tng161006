<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('secondary'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
<body>
  <section class='container'>
    <?php
    $allow_export = 1;
    echo $adminHeaderSection->build('datamaint-secondary', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'dataImportGedcom.php', uiTextSnippet('import'), 'import']);
    $navList->appendItem([$allow_export, 'dataExportGedcom.php', uiTextSnippet('export'), 'export']);
    //    $navList->appendItem([true, 'dataSecondaryProcesses.php', uiTextSnippet('secondarymaint'), 'second']);
    echo $navList->build('second');
    ?>
    <form action="dataSecondaryProcessesFormAction.php" method='post' name='form1'>
      <input class='btn btn-outline-secondary' name='secaction' type='submit' value="<?php echo uiTextSnippet('tracklines'); ?>">
      <input class='btn btn-outline-secondary' name='secaction' type='submit' value="<?php echo uiTextSnippet('sortchildren'); ?>">
      <input class='btn btn-outline-secondary' name='secaction' type='submit' value="<?php echo uiTextSnippet('sortspouses'); ?>">
      <input class='btn btn-outline-secondary' name='secaction' type='submit' value="<?php echo uiTextSnippet('relabelbranches'); ?>">

      <input class='btn btn-outline-secondary' name='secaction' type='submit' value="<?php echo uiTextSnippet('creategendex'); ?>">
      <input class='btn btn-outline-secondary' name='secaction' type='submit' value="<?php echo uiTextSnippet('evalmedia'); ?>">
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
</body>
</html>

