<?php
include("begin.php");
include("adminlib.php");

include("checklogin.php");

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

$applyfilter = "applyFilter({form:'findrepoform1',fieldId:'mytitle',type:'R',tree:'$tree',destdiv:'reporesults'$mediaoption});";

header("Content-type:text/html; charset=" . $session_charset);
?>
<div id='findrepodiv'>
  <form id='findrepoform1' name='findrepoform1' action='' method='post' onsubmit="return <?php echo $applyfilter; ?>">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('findrepoid'); ?></h4>
      <span>(<?php echo uiTextSnippet('enterrepopart'); ?>)</span>
    </header>
    <div class='modal-body'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('title'); ?>:</td>
          <td>
            <input id='mytitle' name='mytitle' type='text' onkeyup="filterChanged(event, {
              form: 'findrepoform1',
              fieldId: 'mytitle',
              type: 'R',
              tree: '<?php echo $tree; ?>',
              destdiv: 'reporesults'<?php echo $mediaoption; ?>
            });">
          </td>
          <td>
            <input type='submit' value="<?php echo uiTextSnippet('search'); ?>"> 
            <input type='button' value="<?php echo $bailtext; ?>" onclick="gotoSection(seclitbox, null);">
          </td>
        </tr>
        <tr>
          <td colspan='3'>
            <input name='filter' type='radio' value='s' onclick="<?php echo $applyfilter; ?>"/> <?php echo uiTextSnippet('startswith'); ?>
            <input name='filter' type='radio' value='c' checked onclick="<?php echo $applyfilter; ?>"/> <?php echo uiTextSnippet('contains'); ?>
          </td>
        </tr>
      </table>
    </div> <!-- .modal-footer -->
    <footer class='modal-footer'></footer>
  </form>
  <span><strong><?php echo uiTextSnippet('searchresults'); ?></strong> (<?php echo uiTextSnippet('clicktoselect'); ?>)</span>
  <div id='reporesults' style='width: 605px; height: 385px; overflow: auto'></div>
</div>