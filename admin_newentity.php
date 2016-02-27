<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if (!$allow_add) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
header("Content-type:text/html; charset=" . $session_charset);
?>
<div class='container'>
  <form id='entityform' name='entityform' action='admin_addentity.php' method='post' onsubmit="return addEntity(this);">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('enternew') . " " . ucfirst(uiTextSnippet($entity)); ?></h4>
    </header>
    <div id='modal-body'>
      <fieldset class='form-group'>
        <label for='entityInput'><?php echo ucfirst(uiTextSnippet($entity)); ?></label>
        <input class='form-control' id='newitem' name='newitem'  type='text' placeholder=''>
        <div id='entitymsg' style='color: green'></div>
      </fieldset>
    </div>
    <footer class='modal-footer'>
      <input name='entity' type='hidden' value="<?php echo "$entity"; ?>">
      <button type='submit' class='btn btn-primary'><?php echo uiTextSnippet('add'); ?></button>
    </footer>
  </form>
</div> <!-- .container -->
