<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$treequery = "SELECT gedcom, treename FROM $treesTable ORDER BY treename";

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('sortmedia'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="albums-text_sort">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('albums-text_sort', $message);
    $navList = new navList('');
    $navList->appendItem([true, "albumsBrowse.php", uiTextSnippet('browse'), "findalbum"]);
    $navList->appendItem([$allowAdd, "albumsAdd.php", uiTextSnippet('add'), "addalbum"]);
    $navList->appendItem([$allowEdit, "albumsSort.php", uiTextSnippet('text_sort'), "sortalbums"]);
    echo $navList->build("sortalbums");
    ?>
    <form name='find' action='albumsSortFormAction.php' method='post' onsubmit="return validateSortForm();">
      <h4><?php echo uiTextSnippet('sortalbumind'); ?></h4>
      <table class='table table-sm'>
        <thead>
          <tr>
            <th><?php echo uiTextSnippet('linktype'); ?></th>
            <th colspan='3'><?php echo uiTextSnippet('id'); ?></th>
          </tr>
        </thead>
        <tr>
          <td>
            <select name="linktype1">
              <option value='I'><?php echo uiTextSnippet('person'); ?></option>
              <option value='F'><?php echo uiTextSnippet('family'); ?></option>
              <option value='S'><?php echo uiTextSnippet('source'); ?></option>
              <option value='R'><?php echo uiTextSnippet('repository'); ?></option>
              <option value='L'><?php echo uiTextSnippet('place'); ?></option>
            </select>
          </td>
          <td><input id='newlink1' name='newlink1' type='text' value="<?php echo $personID; ?>"></td>
          <td><input type='submit' value="<?php echo uiTextSnippet('text_continue'); ?>">
            &nbsp;<?php echo uiTextSnippet('or'); ?>&nbsp; </td>
          <td>
            <a href="#" onclick="return findItem(document.find.linktype1.options[document.find.linktype1.selectedIndex].value, 'newlink1', null, '<?php echo $assignedbranch; ?>');"
               title="<?php echo uiTextSnippet('find'); ?>">
              <img class='icon-sm' src='svg/magnifying-glass.svg'>
            </a>
          </td>
        </tr>
      </table>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/mediafind.js"></script>
  <script src="js/selectutils.js"></script>
  <script>
    var tnglitbox;
    var findopen;
    var album = '';
    var type = "album";
    var formname = "find";
    var resheremsg = '<span>' + textSnippet('reshere') + '</span>';

    function validateSortForm() {
      var rval = true;

      if (document.find.newlink1.value.length === 0) {
        alert(textSnippet('enterid'));
        rval = false;
      }
      return rval;
    }

    function getTree(treeobj) {
      if (treeobj.options.length)
        return treeobj.options['treeobj.selectedIndex'].value;
      else {
        alert(textSnippet('selecttree'));
        return false;
      }
    }

    var gsControlName = "";
  </script>
</body>
</html>
