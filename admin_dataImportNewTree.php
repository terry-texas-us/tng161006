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
?>
<header class='modal-header'>
  <h4><?php echo uiTextSnippet('addnewtree'); ?></h4>
  <a href='#' onclick="return openHelp(helpLang + '/trees_help.php#add', 'newwindow', 'height=500,width=700, resizable=no, scrollbars=no'); newwindow.focus();">
    <span><?php echo uiTextSnippet('help'); ?></span>
  </a>
</header>
<div id='modal-body'>
  <?php include '_/components/php/newTreeForm.php'; ?>
</div>
<footer class='modal-footer'></footer>
