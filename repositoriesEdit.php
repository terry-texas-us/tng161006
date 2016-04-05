<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if ((!$allowEdit && (!$allowAdd || !$added)) || ($assignedtree && $assignedtree != $tree)) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$repoID = ucfirst($repoID);

$query = "SELECT treename FROM $treesTable WHERE gedcom = \"$tree\"";
$result = tng_query($query);
$treerow = tng_fetch_assoc($result);
tng_free_result($result);

$query = "SELECT reponame, changedby, $repositories_table.addressID, address1, address2, city, state, zip, country, phone, email, www, DATE_FORMAT(changedate,\"%d %b %Y %H:%i:%s\") as changedate FROM $repositories_table LEFT JOIN $address_table on $repositories_table.addressID = $address_table.addressID WHERE repoID = \"$repoID\" AND $repositories_table.gedcom = \"$tree\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['reponame'] = preg_replace("/\"/", "&#34;", $row['reponame']);

$row['allow_living'] = 1;

$query = "SELECT DISTINCT eventID as eventID FROM $notelinks_table WHERE persfamID=\"$repoID\" AND gedcom =\"$tree\"";
$notelinks = tng_query($query);
$gotnotes = array();
while ($note = tng_fetch_assoc($notelinks)) {
  if (!$note['eventID']) {
    $note['eventID'] = "general";
  }
  $gotnotes[$note['eventID']] = "*";
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('modifyrepo'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    $photo = showSmallPhoto($repoID, $row['reponame'], 1, 0, 'R');
    require_once 'eventlib.php';
    ?>
    <?php
    echo $adminHeaderSection->build('repositories-modifyrepo', $message);
    $navList = new navList('');
    $navList->appendItem([true, "repositoriesBrowse.php", uiTextSnippet('search'), "findrepo"]);
    $navList->appendItem([$allowAdd, "repositoriesAdd.php", uiTextSnippet('add'), "addrepo"]);
    $navList->appendItem([$allowEdit && $allowDelete, "repositoriesMerge.php", uiTextSnippet('merge'), "merge"]);
    //    $navList->appendItem([$allowEdit, "repositoriesEdit.php?repoID=$repoID&tree=$tree", uiTextSnippet('edit'), "edit"]);
    echo $navList->build("edit");
    ?>
    <br>
    <a href="repositoriesShowItem.php?repoID=<?php echo $repoID; ?>&amp;tree=<?php echo $tree; ?>" title='<?php echo uiTextSnippet('preview') ?>'>
      <img class='icon-sm' src='svg/eye.svg'>
    </a>
    <?php if ($allowAdd && (!$assignedtree || $assignedtree == $tree)) { ?>
      <a href="admin_newmedia.php?personID=<?php echo $repoID; ?>&amp;tree=<?php echo $tree; ?>&amp;linktype=R"><?php echo uiTextSnippet('addmedia'); ?></a>
    <?php } ?>
    <form id='repositories-edit' name='form1' action='repositoriesEditFormAction.php' method='post'>
      <div id="thumbholder" style="margin-right:5px; <?php if (!$photo) {echo "display: none";} ?>">
        <?php echo $photo; ?>
      </div>
      <h4><?php echo $row['reponame'] . " ($repoID)"; ?></h4>
      <div class='smallest'>
        <?php
        $iconColor = $gotnotes['general'] ? "icon-info" : "icon-muted";
        echo "<a id='repository-notes' href='#' title='" . uiTextSnippet('notes') . "' data-repository-id='$repoID'>\n";
        echo "<img class='icon-sm icon-right icon-notes $iconColor' data-src='svg/documents.svg'>\n";
        echo "</a>\n";
        ?>
        <br clear='all'>
      </div>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('tree'); ?>:</td>
          <td>
            <?php echo $treerow['treename']; ?>
            &nbsp;(<a href="#" onclick="return openChangeTree('source', '<?php echo $tree; ?>', '<?php echo $sourceID; ?>');">
              <img src="img/ArrowDown.gif"><?php echo uiTextSnippet('edit'); ?>
            </a> )
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('name'); ?>:</td>
          <td><input name='reponame' type='text' size='40' value="<?php echo $row['reponame']; ?>">
            (<?php echo uiTextSnippet('required'); ?>)
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('address1'); ?>:</td>
          <td><input name='address1' type='text' size='50' value="<?php echo $row['address1']; ?>">
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('address2'); ?>:</td>
          <td><input name='address2' type='text' size='50' value="<?php echo $row['address2']; ?>">
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('city'); ?>:</td>
          <td><input name='city' type='text' size='50' value="<?php echo $row['city']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('stateprov'); ?>:</td>
          <td><input name='state' type='text' size='50' value="<?php echo $row['state']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('zip'); ?>:</td>
          <td><input name='zip' type='text' value="<?php echo $row['zip']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('countryaddr'); ?>:</td>
          <td><input name='country' type='text' size='50' value="<?php echo $row['country']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('phone'); ?>:</td>
          <td><input name='phone' type='text' size='30' value="<?php echo $row['phone']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('email'); ?>:</td>
          <td><input name='email' type='text' size='50' value="<?php echo $row['email']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('website'); ?>:</td>
          <td><input name='www' type='text' size='50' value="<?php echo $row['www']; ?>"></td>
        </tr>
        <tr>
          <td colspan='2'>&nbsp;</td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('otherevents'); ?>:</td>
          <td>
            <?php
            echo "<input type='button' value=\"  " . uiTextSnippet('addnew') . "  \" onClick=\"newEvent('R','$repoID','$tree');\">&nbsp;\n";
            ?>
          </td>
        </tr>
      </table>
      <?php
      showCustEvents($repoID);
      ?>
      <p>
        <?php
        echo uiTextSnippet('onsave') . ":<br>";
        echo "<input name='newscreen' type='radio' value='return'> " . uiTextSnippet('savereturn') . "<br>\n";
        if ($cw) {
          echo "<input name='newscreen' type='radio' value='close' checked> " . uiTextSnippet('closewindow') . "\n";
        } else {
          echo "<input name='newscreen' type='radio' value='none' checked> " . uiTextSnippet('saveback') . "\n";
        }
        ?>
      </p>
      <input name='tree' type='hidden' value="<?php echo $tree; ?>">
      <input name='addressID' type='hidden' value="<?php echo $row['addressID']; ?>">
      <input name='repoID' type='hidden' value="<?php echo "$repoID"; ?>">
      <input name='cw' type='hidden' value="<?php echo "$cw"; ?>">
      <input name='submit2' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    </form>
    <span class="smallest"><?php echo uiTextSnippet('lastmodified') . ": {$row['changedate']} ({$row['changedby']})"; ?></span>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script>
  var tnglitbox;
  var preferEuro = <?php echo($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : "false"); ?>;
  var preferDateFormat = '<?php echo $preferDateFormat; ?>';

  var tree = '<?php echo $tree; ?>';
</script>
<script src="js/selectutils.js"></script>
<script src="js/datevalidation.js"></script>  
<script src="js/admin.js"></script>
<script src="js/notes.js"></script>
<script>
  var persfamID = "<?php echo $repoID; ?>";
  var allow_cites = false;
  var allow_notes = true;
</script>
</body>
</html>
