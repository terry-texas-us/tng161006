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

$helplang = findhelp("collections_help.php");

initMediaTypes();

header("Content-type:text/html; charset=" . $session_charset);
?>
<div id='newcollection'>
  <form id='collform' name='collform' action='admin_addcollection.php' method='post' onsubmit="return addCollection(this);">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('addnewcoll'); ?></h4>
      <p> 
        <a href='#' onclick="return openHelp('<?php echo $helplang; ?>/collections_help.php');"><?php echo uiTextSnippet('help'); ?></a>
      </p>
    </header>
    <div class='modal-body'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('collid'); ?>:</td>
          <td><input name='collid' type='text' 
                     onblur="if(!$('exportas').value) $('exportas').value = this.value.toUpperCase();"/></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('collexpas'); ?>:</td>
          <td><input id='exportas' name='exportas' type='text' value="PHOTO"/></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('colldisplay'); ?>:</td>
          <td><input name='display' type='text' size='30'/></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('collpath'); ?>:</td>
          <td><input name='path' type='text' size='50'/></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td><input type='button' value="<?php echo uiTextSnippet('makefolder'); ?>"
                     onclick="if(document.collform.path.value){makeFolder('newcoll',document.collform.path.value);}"/>
            <span id="msg_newcoll"></span></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('collicon'); ?>:</td>
          <td><input name='icon' type='text' size='30' value="img/"/></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('collthumb'); ?>:</td>
          <td><input name='thumb' type='text' size='30' value="img/"/></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('displayorder'); ?>:</td>
          <td><input name='ordernum' type='text' size='5' /></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('colllike'); ?>:</td>
          <td>
        <span>
        <select name="liketype">
          <?php
          foreach ($mediatypes as $mediatype) {
            if (!$mediatype['type']) {
              $msgID = $mediatype['ID'];
              echo "	<option value=\"$msgID\">" . $mediatype['display'] . "</option>\n";
            }
          }
          ?>
        </select>
        </span>
          </td>
        </tr>
      </table>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <input name='field' type='hidden' value="<?php echo "$field"; ?>">
      <input type='submit' value="<?php echo uiTextSnippet('save'); ?>">
      <input name='cancel' type='button' value="<?php echo uiTextSnippet('cancel'); ?>"
             onclick="tnglitbox.remove();"> 
      <span id='cerrormsg' style="color: #CC0000; display: none"><?php echo uiTextSnippet('cidexists'); ?></span>
    </footer>
  </form>
</div>
