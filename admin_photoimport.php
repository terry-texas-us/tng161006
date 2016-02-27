<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if (!$allow_media_add || $assignedtree) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

if ($assignedtree) {
  $wherestr = "WHERE gedcom = \"$assignedtree\"";
} else {
  $wherestr = "";
}
$treequery = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('mediaimport'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    $standardtypes = array();
    $moptions = "";
    $likearray = "var like = new Array();\n";
    foreach ($mediatypes as $mediatype) {
      if (!$mediatype['type']) {
        $standardtypes[] = "\"" . $mediatype['ID'] . "\"";
      }
      $msgID = $mediatype['ID'];
      $moptions .= "  <option value=\"$msgID\"";
      if ($msgID == $mediatypeID) {
        $moptions .= " selected";
      }
      $moptions .= ">" . $mediatype['display'] . "</option>\n";
      $likearray .= "like['$msgID'] = '{$mediatype['liketype']}';\n";
    }
    $sttypestr = implode(",", $standardtypes);
    ?>

    <?php
    echo $adminHeaderSection->build('media-import', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_media.php", uiTextSnippet('search'), "findmedia"]);
    $navList->appendItem([$allow_media_add, "admin_newmedia.php", uiTextSnippet('addnew'), "addmedia"]);
    $navList->appendItem([$allow_media_edit, "admin_ordermediaform.php", uiTextSnippet('text_sort'), "sortmedia"]);
    $navList->appendItem([$allow_media_edit && !$assignedtree, "admin_thumbnails.php", uiTextSnippet('thumbnails'), "thumbs"]);
    $navList->appendItem([$allow_media_add && !$assignedtree, "admin_photoimport.php", uiTextSnippet('import'), "import"]);
    $navList->appendItem([$allow_media_add && !$assignedtree, "admin_mediaupload.php", uiTextSnippet('upload'), "upload"]);
    echo $navList->build("import");
    ?>

    <form action="admin_photoimporter.php" method='post' name='form1'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('mediatype'); ?>:</td>
          <td>
            <select name="mediatypeID"
                    onChange="switchOnType(this.options[this.selectedIndex].value)">
              <?php
              foreach ($mediatypes as $mediatype) {
                $msgID = $mediatype['ID'];
                echo "  <option value=\"$msgID\">" . $mediatype['display'] . "</option>\n";
              }
              ?>
            </select>
            <?php
            if (!$assignedtree && $allow_add && $allow_edit && $allow_delete) {
              ?>
              <input name='addnewmediatype' type='button' value="<?php echo uiTextSnippet('addnewcoll'); ?>"
                     onclick="tnglitbox = new ModalDialog('admin_newcollection.php?field=mediatypeID');">
              <input id='editmediatype' name='editmediatype' type='button' value="<?php echo uiTextSnippet('edit'); ?>"
                     style="display: none"
                     onclick="editMediatype(document.form1.mediatypeID);">
              <input id='delmediatype' name='delmediatype' type='button' value="<?php echo uiTextSnippet('delete'); ?>"
                     style="display: none"
                     onclick="confirmDeleteMediatype(document.form1.mediatypeID);">
              <?php
            }
            ?>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('tree'); ?>*:</td>
          <td>
            <select name='tree'>
              <option value=''></option>
              <?php
              $treeresult = tng_query($treequery) or die(uiTextSnippet('cannotexecutequery') . ": $treequery");
              while ($treerow = tng_fetch_assoc($treeresult)) {
                echo "  <option value=\"{$treerow['gedcom']}\"";
                if ($treerow['gedcom'] == $tree) {
                  echo " selected";
                }
                echo ">{$treerow['treename']}</option>\n";
              }
              tng_free_result($treeresult);
              ?>
            </select>
          </td>
        </tr>
      </table>
      <p>*<?php echo uiTextSnippet('phalltrees'); ?></p>
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('import'); ?>">
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src='js/admin.js'></script>
<script src='js/mediautils.js'></script>
<script>
  var tree = "<?php echo $tree; ?>";
  var tnglitbox;
  var stmediatypes = new Array(<?php echo $sttypestr; ?>);
  var allow_edit = <?php echo($allow_edit ? "1" : "0"); ?>;
  var allow_delete = <?php echo($allow_delete ? "1" : "0"); ?>;
  var manage = 1;
  <?php echo $likearray; ?>
</script>
</body>
</html>