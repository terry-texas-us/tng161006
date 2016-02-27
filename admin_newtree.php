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
<body id="tree-addnewtree">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('trees-addnewtree', $message);
    $navList = new navList('');
    $allow_add_tree = $assignedtree ? 0 : $allow_add;
    $navList->appendItem([true, "admin_trees.php", uiTextSnippet('search'), "findtree"]);
    $navList->appendItem([$allow_add_tree, "admin_newtree.php", uiTextSnippet('addnew'), "addtree"]);
    echo $navList->build("addtree");
    ?>
    <?php include '_/components/php/newTreeForm.php'; ?>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script>
function alphaNumericCheck(string) {
  'use strict';
  var regex = /^[0-9A-Za-z_-]+$/; //^['a-zA-z']+$/
  return regex.test(string);
}
function validateTreeForm(form) {
  'use strict';

  var rval = true;
  if (form.gedcom.value.length === 0) {
    alert(textSnippet('entertreeid'));
    rval = false;
  } else if (!alphaNumericCheck(form.gedcom.value)) {
    alert(textSnippet('alphanum'));
    rval = false;
  } else if (form.treename.value.length === 0) {
    alert(textSnippet('entertreename'));
    rval = false;
  }
  return rval;
}
</script>
</body>
</html>