<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowAdd) {
    $message = uiTextSnippet('norights');
    header("Location: admin_login.php?message=" . urlencode($message));
    exit;
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('addnewtree'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('trees-addnewtree', $message);
    $navList = new navList('');
    $allow_add_tree = $allowAdd;
    $navList->appendItem([true, 'treesBrowse.php', uiTextSnippet('search'), 'findtree']);
    //    $navList->appendItem([$allow_add_tree, "treesAdd.php", uiTextSnippet('add'), "addtree"]);
    echo $navList->build("addtree");
    ?>
    <?php require '_/components/php/newTreeForm.php'; ?>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src='js/trees.js'></script>
</body>
</html>