<?php
require 'begin.php';
require 'adminlib.php';
require 'getlang.php';

require 'checklogin.php';

if ($type == 'map') {
  $subtitle = uiTextSnippet('enternamepart2');
} else {
  $subtitle = uiTextSnippet('enternamepart');
}
if ($mediaID) {
  $mediaoption = ",mediaID:'$mediaID'";
} else {
  if ($albumID) {
    $mediaoption = ",albumID:'$albumID'";
  } else {
    $mediaoption = "";
  }
}
$bailtext = $mediaoption ? uiTextSnippet('finish') : uiTextSnippet('cancel');

$applyfilter = "applyFilter({"
        . "form: 'findform1', fieldId: 'myflastname', myflastname: $('#myflastname').val(), myffirstname: $('#myffirstname').val(), myfpersonID: $('#myfpersonID').val(),"
        . "type: 'I', branch: '$branch', destdiv: 'findresults'$mediaoption});";

header("Content-type:text/html; charset=" . $session_charset);
?>
<div id='finddiv'>
  <form id='findform1' name='findform1' action='' method='post' onsubmit="return <?php echo $applyfilter; ?>">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('findpersonid'); ?></h4>
      <span>(<?php echo $subtitle; ?>)</span>
    </header>
    <div class='modal-body'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('lastname'); ?></td>
          <td><?php echo uiTextSnippet('firstname'); ?></td>
          <td><?php echo uiTextSnippet('personid'); ?></td>
        </tr>
        <tr>
          <td>
            <input id='myflastname' name='myflastname' type='text' tabindex='1'
                   onkeyup="filterChanged(event, {
                    form: 'findform1', fieldId: 'myflastname', myflastname: $('#myflastname').val(), myffirstname: $('#myffirstname').val(), myfpersonID: $('#myfpersonID').val(),
                    type: 'I', branch: '<?php echo $branch; ?>', destdiv: 'findresults'<?php echo $mediaoption; ?>
                  });">
          </td>
          <td>
            <input id='myffirstname' name='myffirstname' type='text' tabindex='2'
                   onkeyup="filterChanged(event, {
                           form: 'findform1', fieldId: 'myffirstname', myflastname: $('#myflastname').val(), myffirstname: $('#myffirstname').val(), myfpersonID: $('#myfpersonID').val(),
                           type: 'I', branch: '<?php echo $branch; ?>', destdiv: 'findresults'<?php echo $mediaoption; ?>
                         });">
          </td>
          <td>
            <input id='myfpersonID' name='myfpersonID' type='text' tabindex="3"
                   onkeyup="filterChanged(event, {
                           form: 'findform1', fieldId: 'myfpersonID', myflastname: $('#myflastname').val(), myffirstname: $('#myffirstname').val(), myfpersonID: $('#myfpersonID').val(),
                           type: 'I', branch: '<?php echo $branch; ?>', destdiv: 'findresults'<?php echo $mediaoption; ?>
                         });">
            <!--<input type='submit' value="<?php echo uiTextSnippet('search'); ?>" />-->
            <input type='button' value="<?php echo $bailtext; ?>" onclick="gotoSection(seclitbox, null);">
          </td>
        </tr>
        <tr>
          <td colspan='3'>
            <label>
              <input name='filter' type='radio' value='s' 
                     onclick="<?php echo $applyfilter; ?>" checked='checked'> <?php echo uiTextSnippet('startswith'); ?>
            </label>
            <label>
              <input name='filter' type='radio' value='c'
                     onclick="<?php echo $applyfilter; ?>"> <?php echo uiTextSnippet('contains'); ?>
            </label>
          </td>
        </tr>
      </table>
      <span><strong><?php echo uiTextSnippet('searchresults'); ?></strong> (<?php echo uiTextSnippet('clicktoselect'); ?>)</span>
      <div id='findresults'></div>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
    </footer>
  </form>
</div>
