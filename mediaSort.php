<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowMediaEdit) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('sortmedia'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
<body id="media-text_sort">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('media-text_sort', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'mediaBrowse.php', uiTextSnippet('browse'), 'findmedia']);
    $navList->appendItem([$allowMediaAdd, 'mediaAdd.php', uiTextSnippet('add'), 'addmedia']);
    //    $navList->appendItem([$allowMediaEdit, 'mediaSort.php', uiTextSnippet('text_sort'), 'sortmedia']);
    $navList->appendItem([$allowMediaEdit, 'mediaThumbnails.php', uiTextSnippet('thumbnails'), 'thumbs']);
    $navList->appendItem([$allowMediaAdd, 'mediaImport.php', uiTextSnippet('import'), 'import']);
    $navList->appendItem([$allowMediaAdd, 'mediaUpload.php', uiTextSnippet('upload'), 'upload']);
    echo $navList->build('sortmedia');
    ?>
    <form name='find' action='mediaSortFormAction.php' method='get' onsubmit="return validateForm();">
      <h4><?php echo uiTextSnippet('sortmediaind'); ?></h4>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('linktype'); ?></td>
          <td><?php echo uiTextSnippet('mediatype'); ?></td>
          <td colspan='3'><?php echo uiTextSnippet('id'); ?></td>
        </tr>
        <tr>
          <td>
            <select name='linktype1' onchange="toggleEventLink(this.selectedIndex);">
              <option value='I'><?php echo uiTextSnippet('person'); ?></option>
              <option value='F'><?php echo uiTextSnippet('family'); ?></option>
              <option value='S'><?php echo uiTextSnippet('source'); ?></option>
              <option value='R'><?php echo uiTextSnippet('repository'); ?></option>
              <option value='L'><?php echo uiTextSnippet('place'); ?></option>
            </select>
          </td>
          <td>
            <select name='mediatypeID'>
              <?php
              foreach ($mediatypes as $mediatype) {
                $msgID = $mediatype['ID'];
                echo " <option value=\"$msgID\"";
                if ($msgID == $mediatypeID) {
                  echo ' selected';
                }
                echo '>' . $mediatype['display'] . "</option>\n";
              }
              ?>
            </select>
          </td>
          <td>
            <input id='newlink1' name='newlink1' type='text' value="<?php echo $personID; ?>"
                     onblur="toggleEventRow(document.find.eventlink1.checked);">
          </td>
          <td>
            <a href="#" title="<?php echo uiTextSnippet('find'); ?>"
                 onclick="return findItem(document.find.linktype1.options[document.find.linktype1.selectedIndex].value, 'newlink1', null, '<?php echo $assignedbranch; ?>');">
              <img class='icon-sm' src='svg/magnifying-glass.svg'>
            </a>
          </td>
          <td><input type='submit' value="<?php echo uiTextSnippet('text_continue'); ?>"></td>
        </tr>
        <tr>
          <td colspan='3'>&nbsp;</td>
          <td colspan='2'>
            <span id='eventlink1'>
              <input name='eventlink1' type='checkbox' value='1'
                     onclick="return toggleEventRow(this.checked);"/> <?php echo uiTextSnippet('eventlink'); ?>
            </span><br>
            <select id='eventrow1' name='event1' style='display: none'>
              <option value=''></option>
            </select>
          </td>
          <td></td>
        </tr>
      </table>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/mediafind.js"></script>
<script src="js/selectutils.js"></script>
<script>
  var findopen;
  var album = '';
  var media = '';
  var type = "media";
  var findform = "find";
  var resheremsg = '<span>' + textSnippet('reshere') + '</span>';

  function validateForm() {
    var rval = true;

    if (document.find.newlink1.value.length === 0) {
      alert(textSnippet('enterid'));
      rval = false;
    }
    return rval;
  }
</script>
</body>
</html>

