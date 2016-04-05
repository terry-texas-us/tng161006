<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowMediaEdit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('sortmedia'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('media-thumbnails', $message);
    $navList = new navList('');
    $navList->appendItem([true, "mediaBrowse.php", uiTextSnippet('search'), "findmedia"]);
    $navList->appendItem([$allowAdd, "admin_newmedia.php", uiTextSnippet('addnew'), "addmedia"]);
    $navList->appendItem([$allowEdit, "admin_ordermediaform.php", uiTextSnippet('text_sort'), "sortmedia"]);
    //    $navList->appendItem([$allowEdit && !$assignedtree, "mediaThumbnails.php", uiTextSnippet('thumbnails'), "thumbs"]);
    $navList->appendItem([$allowMediaAdd, "mediaImport.php", uiTextSnippet('import'), "import"]);
    $navList->appendItem([$allowMediaAdd && !$assignedtree, "mediaUpload.php", uiTextSnippet('upload'), "upload"]);
    echo $navList->build("thumbs");
    ?>
    <div class='small'>
      <a href='#' onClick="toggleAll('on');"><?php echo uiTextSnippet('expandall'); ?></a>
      <a href='#' onClick="toggleAll('off');"><?php echo uiTextSnippet('collapseall'); ?></a>
    </div>
    <table class='table table-sm'>
      <?php if (!$assignedtree) { ?>
        <?php if (function_exists(imageJpeg)) { ?>
          <tr>
            <td>
              <?php echo displayToggle("plus1", 1, "thumbs", uiTextSnippet('genthumbs'), uiTextSnippet('genthumbsdesc')); ?>
              <div id="thumbs">
                <br>
                <form action="admin_generatethumbs.php" method='post' onsubmit="return generateThumbs(this);">
                  <input name='regenerate' type='checkbox' value='1'> <?php echo uiTextSnippet('regenerate'); ?><br>
                  <input name='repath' type='checkbox' value='1'> <?php echo uiTextSnippet('repath'); ?>
                  <br><br>
                  <input name='submit' type='submit' value="<?php echo uiTextSnippet('generate'); ?>">
                  <img src="img/spinner.gif" id="thumbspin" style="display: none">
                </form>
                <div id="thumbresults" style="display:none">
                </div>
              </div>
            </td>
          </tr>
        <?php } ?>
        <tr>
          <td>
            <?php echo displayToggle("plus2", 1, "defaults", uiTextSnippet('assigndefs'), uiTextSnippet('assigndefsdesc')); ?>
            <div id="defaults">
              <br>
              <form action="defphotos.php" method='post' onsubmit="return assignDefaults(this);">
                <input name='overwritedefs' type='checkbox' value='1'> <?php echo uiTextSnippet('overwritedefs'); ?>
                <br><br>
                <span><?php echo uiTextSnippet('tree') . ': '; ?></span>
                <select name='tree'>
                  <?php
                  $query = "SELECT gedcom, treename FROM $treesTable ORDER BY treename";
                  $result = tng_query($query);
                  while ($row = tng_fetch_assoc($result)) {
                    echo "<option value=\"{$row['gedcom']}\">{$row['treename']}</option>\n";
                  }
                  ?>
                </select>
                <br><br>
                <input name='submit' type='submit' value="<?php echo uiTextSnippet('assign'); ?>">
                <img src="img/spinner.gif" id="defspin" style="display: none">
              </form>
              <div id="defresults" style="display: none"></div>
            </div>
          </td>
        </tr>
      <?php } ?>
    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src='js/admin.js'></script>
<script src='js/mediautils.js'></script>
<script>
  function toggleAll(display) {
    toggleSection('thumbs', 'plus1', display);
    toggleSection('defaults', 'plus2', display);
    return false;
  }
</script>
</body>
</html>

