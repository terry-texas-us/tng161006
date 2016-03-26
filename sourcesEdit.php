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

initMediaTypes();

$sourceID = ucfirst($sourceID);

$query = "SELECT treename FROM $trees_table WHERE gedcom = \"$tree\"";
$result = tng_query($query);
$treerow = tng_fetch_assoc($result);
tng_free_result($result);

$query = "SELECT *, DATE_FORMAT(changedate,\"%d %b %Y %H:%i:%s\") as changedate FROM $sources_table WHERE sourceID = \"$sourceID\" AND gedcom = \"$tree\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['shorttitle'] = preg_replace("/\"/", "&#34;", $row['shorttitle']);
$row['title'] = preg_replace("/\"/", "&#34;", $row['title']);
$row['author'] = preg_replace("/\"/", "&#34;", $row['author']);
$row['callnum'] = preg_replace("/\"/", "&#34;", $row['callnum']);
$row['publisher'] = preg_replace("/\"/", "&#34;", $row['publisher']);
$row['actualtext'] = preg_replace("/\"/", "&#34;", $row['actualtext']);

$sourcename = $row['title'] ? $row['title'] : $row['shorttitle'];
$row['allow_living'] = 1;

$query = "SELECT DISTINCT eventID as eventID FROM $notelinks_table WHERE persfamID=\"$sourceID\" AND gedcom =\"$tree\"";
$notelinks = tng_query($query);
$gotnotes = array();
while ($note = tng_fetch_assoc($notelinks)) {
  if (!$note['eventID']) {
    $note['eventID'] = "general";
  }
  $gotnotes[$note['eventID']] = "*";
}

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('modifysource'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='sources-modifysource'>
  <section class='container'>
    <?php
    $photo = showSmallPhoto($sourceID, $sourcename, 1, 0, 'S');
    require_once 'eventlib.php';
    ?>
    <script>
      var tnglitbox;
      var preferEuro = <?php echo($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : "false"); ?>;
      var preferDateFormat = '<?php echo $preferDateFormat; ?>';

      var tree = '<?php echo $tree; ?>';
    </script>
    <script src="js/selectutils.js"></script>
    <script src="js/datevalidation.js"></script>
    <?php
    echo $adminHeaderSection->build('sources-modifysource', $message);
    $navList = new navList('');
    $navList->appendItem([true, "sourcesBrowse.php", uiTextSnippet('browse'), "findsource"]);
    $navList->appendItem([$allowAdd, "sourcesAdd.php", uiTextSnippet('add'), "addsource"]);
    $navList->appendItem([$allowEdit && $allowDelete, "sourcesMerge.php", uiTextSnippet('merge'), "merge"]);
    $navList->appendItem([$allowEdit, "sourcesEdit.php?sourceID=$sourceID&tree=$tree", uiTextSnippet('edit'), "edit"]);
    echo $navList->build("edit");
    ?>
    <br>
    <a href="sourcesShowSource.php?sourceID=<?php echo $sourceID; ?>&amp;tree=<?php echo $tree; ?>" title='<?php echo uiTextSnippet('preview') ?>'>
      <img class='icon-sm' src='svg/eye.svg'>
    </a>
    <?php if ($allowAdd && (!$assignedtree || $assignedtree == $tree)) { ?>
      <a href="admin_newmedia.php?personID=<?php echo $sourceID; ?>&amp;tree=<?php echo $tree; ?>&amp;linktype=S"><?php echo uiTextSnippet('addmedia'); ?></a>
    <?php } ?>
    <form name='form1' action='sourcesEditFormAction.php' method='post'>
      <header id='source-header'>
        <div class='row'>
          <div class='col-sm-12' id="thumbholder" style="margin-right: 5px; <?php if (!$photo) {} ?>">
            <?php echo $photo; ?>
            <h4><?php echo "$sourcename ($sourceID)"; ?></h4>
            <div class='smallest'>
              <?php
              $iconColor = $gotnotes['general'] ? "icon-info" : "icon-muted";
              echo "<a id='sources-notes' href='#' title='" . uiTextSnippet('notes') . "' data-repository-id='$sourceID'>\n";
              echo "<img class='icon-sm icon-right icon-notes $iconColor' data-src='svg/documents.svg'>\n";
              echo "</a>\n";
              ?>
              <br clear='all'>
            </div>
            <span class='smallest'><?php echo uiTextSnippet('lastmodified') . ": {$row['changedate']} ({$row['changedby']})"; ?></span>
          </div>
        </div>
      </header>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('tree'); ?>:</td>
          <td>
            <?php echo $treerow['treename']; ?>
            &nbsp;(<a href="#" onclick="return openChangeTree('source', '<?php echo $tree; ?>', '<?php echo $sourceID; ?>');">
              <img src='img/ArrowDown.gif'>
                <?php echo uiTextSnippet('edit'); ?></a>)
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('shorttitle'); ?>:</td>
          <td>
            <input name='shorttitle' type='text' size='40' value="<?php echo $row['shorttitle']; ?>">
            (<?php echo uiTextSnippet('required'); ?>)
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('longtitle'); ?>:</td>
          <td><input name='title' type='text' size='50' value="<?php echo $row['title']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('author'); ?>:</td>
          <td><input name='author' type='text' size='40' value="<?php echo $row['author']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('callnumber'); ?>:</td>
          <td><input name='callnum' type='text' value="<?php echo $row['callnum']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('publisher'); ?>:</td>
          <td><input name='publisher' type='text' size='40' value="<?php echo $row['publisher']; ?>">
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('repository'); ?>:</td>
          <td>
            <select name="repoID">
              <option value=''></option>
              <?php
              $query = "SELECT repoID, reponame, gedcom FROM $repositories_table WHERE gedcom = \"$tree\" ORDER BY reponame";
              $reporesult = tng_query($query);
              while ($reporow = tng_fetch_assoc($reporesult)) {
                echo "    <option value=\"{$reporow['repoID']}\"";
                if ($reporow['repoID'] == $row['repoID']) {
                  echo " selected";
                }
                if (!$assignedtree && $numtrees > 1) {
                  echo ">{$reporow['reponame']} (" . uiTextSnippet('tree') . ": {$reporow['gedcom']})</option>\n";
                } else {
                  echo ">{$reporow['reponame']}</option>\n";
                }
              }
              tng_free_result($reporesult);
              ?>
            </select>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('actualtext'); ?>:</td>
          <td>
            <textarea rows="5" name="actualtext"><?php echo $row['actualtext']; ?></textarea>
          </td>
        </tr>
        <tr>
          <td colspan='2'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('otherevents'); ?>:</td>
          <td>
            <?php
            echo "<input type='button' value=\"  " . uiTextSnippet('addnew') . "  \" onClick=\"newEvent('S','$sourceID','$tree');\">&nbsp;\n";
            ?>
          </td>
        </tr>
      </table>
      <?php showCustEvents($sourceID); ?>
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
      <input name='sourceID' type='hidden' value="<?php echo "$sourceID"; ?>">
      <input name='cw' type='hidden' value="<?php echo "$cw"; ?>">
      <input name='submit2' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/admin.js"></script>
<script src="js/selectutils.js"></script>
<script src="js/notes.js"></script>
<script>
  var persfamID = "<?php echo $sourceID; ?>";
  var allow_cites = false;
  var allow_notes = true;
</script>
</body>
</html>