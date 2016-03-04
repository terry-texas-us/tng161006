<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if (!$allow_add) {
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
    $allow_add_tree = $assignedtree ? 0 : $allow_add;
    $navList->appendItem([true, 'treesBrowse.php', uiTextSnippet('search'), 'findtree']);
//    $navList->appendItem([$allow_add_tree, "treesAdd.php", uiTextSnippet('add'), "addtree"]);
    echo $navList->build("addtree");
    ?>
    <?php include '_/components/php/newTreeForm.php'; ?>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src='js/trees.js'></script>
</body>
</html>