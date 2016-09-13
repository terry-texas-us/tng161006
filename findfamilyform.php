<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

if ($mediaID) {
  $mediaoption = ",mediaID:'$mediaID'";
} else {
  if ($albumID) {
    $mediaoption = ",albumID:'$albumID'";
  } else {
    $mediaoption = '';
  }
}
$bailtext = $mediaoption ? uiTextSnippet('finish') : uiTextSnippet('cancel');

$applyfilter = "applyFilter({form:'findform1', fieldId:'myhusbname', myhusbname:$('#myhusbname').val(), mywifename:$('#mywifename').val(), myfamilyID:$('#myfamilyID').val(), type:'F', branch:'branch', destdiv:'findresults'$mediaoption});";

header('Content-type:text/html; charset=' . $session_charset);
?>
<div id='finddiv'>
  <form id='findform1' name='findform1' action='' method='post' onsubmit="return <?php echo $applyfilter; ?>">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('findfamilyid'); ?></h4>
      <span>(<?php echo uiTextSnippet('enternamepart'); ?>)</span>
    </header>
    <div class='modal-body'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('husbname'); ?></td>
          <td><?php echo uiTextSnippet('wifename'); ?></td>
          <td><?php echo uiTextSnippet('familyid'); ?></td>
        </tr>
        <tr>
          <td>
            <input id='myhusbname' name='myhusbname' type='text' onkeyup="filterChanged(event, {
               form:'findform1',
               fieldId:'myhusbname',
               myhusbname:$('#myhusbname').val(),
               mywifename:$('#mywifename').val(),
               myfamilyID:$('#myfamilyID').val(),
               type:'F',
               branch:'<?php echo $branch; ?>',
               destdiv:'findresults'<?php echo $mediaoption; ?>
             });">
          </td>
          <td>
            <input id='mywifename' name='mywifename' type='text' onkeyup="filterChanged(event, {
              form:'findform1',
              fieldId:'mywifename',
              myhusbname:$('#myhusbname').val(),
              mywifename:$('#mywifename').val(),
              myfamilyID:$('#myfamilyID').val(),
              type:'F',
              branch:'<?php echo $branch; ?>',
              destdiv:'findresults'<?php echo $mediaoption; ?>
            });">
          </td>
          <td>
            <input id='myfamilyID' name='myfamilyID' type='text' onkeyup="filterChanged(event, {
              form:'findform1',
              fieldId:'myfamilyID',
              myhusbname:$('#myhusbname').val(),
              mywifename:$('#mywifename').val(),
              myfamilyID:$('#myfamilyID').val(),
              type:'F',
              branch:'<?php echo $branch; ?>',
              destdiv:'findresults'<?php echo $mediaoption; ?>
            });">
            <input type='submit' value="<?php echo uiTextSnippet('search'); ?>"> 
            <input type='button' value="<?php echo $bailtext; ?>" onclick="gotoSection(seclitbox, null);">
          </td>
        </tr>
        <tr>
          <td colspan='3'>
            <input name='filter' type='radio' value='s' onclick="<?php echo $applyfilter; ?>"/> <?php echo uiTextSnippet('startswith'); ?> &nbsp;&nbsp;
            <input name='filter' type='radio' value='c' checked onclick="<?php echo $applyfilter; ?>"/> <?php echo uiTextSnippet('contains'); ?>
          </td>
        </tr>
      </table>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'></footer>
  </form>
  <span><strong><?php echo uiTextSnippet('searchresults'); ?></strong> (<?php echo uiTextSnippet('clicktoselect'); ?>)</span>
  <div id='findresults' style='width: 605px; height: 365px; overflow: auto'></div>
</div>