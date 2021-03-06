<?php
/**
 * Name history: findplaceform.php
 */

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

if ($sessionCharset != 'UTF-8') {
  $place = tng_utf8_decode($place);
}
if ($mediaID) {
  $mediaoption = ", mediaID: '$mediaID'";
} else {
  if ($albumID) {
    $mediaoption = ", albumID: '$albumID'";
  } else {
    $mediaoption = '';
  }
}
$bailtext = $mediaoption ? uiTextSnippet('finish') : uiTextSnippet('cancel');

$applyfilter = "applyFilter({form:'findform1',fieldId:'myplace', type:'L', destdiv:'placeresults', temple:getTempleCheck()$mediaoption});";

header('Content-type:text/html; charset=' . $sessionCharset);
?>
<div id='finddiv'>
  <form id='findform1' name='findform1' action='' method='post' onsubmit="return <?php echo $applyfilter; ?>">
    <header class='modal-header'>
    <h4><?php echo uiTextSnippet('findplace'); ?>
      <span>(<?php echo uiTextSnippet('enterplacepart'); ?>)</span>
    </h4>
    </header>
    <div class='modal-body'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('place'); ?>:</td>
          <td>
              <input id='myplace' name='myplace' type='text' onkeyup="filterChanged(event, {
                form:'findform1',
                fieldId:'myplace',
                type:'L',
                destdiv:'placeresults',
                temple:getTempleCheck()<?php echo $mediaoption; ?>
              });">
          </td>
          <td>
            <input type='submit' value="<?php echo uiTextSnippet('search'); ?>"> 
            <input type='button' value="<?php echo $bailtext; ?>" onclick="gotoSection(seclitbox, null);">
          </td>
        </tr>
        <tr>
          <td colspan='3'>
            <input name='filter' type='radio' value='s'
                   onclick="<?php echo $applyfilter; ?>"> <?php echo uiTextSnippet('startswith'); ?> &nbsp;&nbsp;
            <input name='filter' type='radio' value='c' checked
                   onclick="<?php echo $applyfilter; ?>"> <?php echo uiTextSnippet('contains'); ?>
          </td>
        </tr>
        <?php if ($temple) { ?>
          <tr>
            <td>&nbsp;</td>
            <td colspan='2'>
              <input id='temple' name='temple' type='checkbox' value='1' checked onclick="lastFilter = ''; applyFilter({
                  form:'findform1',
                  fieldId:'myplace',
                  destdiv:'placeresults',
                  temple:getTempleCheck()<?php echo $mediaoption; ?>
                });"> <?php echo uiTextSnippet('findtemples'); ?>
            </td>
          </tr>
        <?php } ?>
      </table>
      <span><strong><?php echo uiTextSnippet('searchresults'); ?></strong> (<?php echo uiTextSnippet('clicktoselect'); ?>)</span>
      <div id='placeresults' style='width: 605px; height: 385px; overflow: auto'></div>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
    </footer>
  </form>
</div>
