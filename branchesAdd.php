<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if (!$allow_add) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
if ($assignedtree) {
  $wherestr = "WHERE gedcom = \"$assignedtree\"";
  $tree = $assignedtree;
} else {
  $wherestr = "";
}
$orgtree = $tree;
$treequery = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('addnewbranch'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="branches-addnewbranch">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('branches-addnewbranch', $message);
    $navList = new navList('');
    $navList->appendItem([true, "branchesBrowse.php", uiTextSnippet('browse'), "findbranch"]);
//    $navList->appendItem([$allow_add, "branchesAdd.php", uiTextSnippet('add'), "addbranch"]);
    echo $navList->build("addbranch");
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <form action="branchesAddFormAction.php" method='post' name="form1" onsubmit="return validateForm();">
            <table>
              <tr>
                <td><?php echo uiTextSnippet('tree'); ?>:</td>
                <td>
                  <select name='tree' id="tree1">
                    <?php
                    $treeresult = tng_query($treequery) or die(uiTextSnippet('cannotexecutequery') . ": $treequery");
                    while ($treerow = tng_fetch_assoc($treeresult)) {
                      echo "  <option value=\"{$treerow['gedcom']}\">{$treerow['treename']}</option>\n";
                    }
                    tng_free_result($treeresult);
                    ?>
                  </select>
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('branchid'); ?>:</td>
                <td><input name='branch' type='text' maxlength="20"/></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('description'); ?>:</td>
                <td><input name='description' type='text'></td>
              </tr>

              <tr>
                <td colspan='2'>
                  <div id="startind1"><br><strong><?php echo uiTextSnippet('startingind'); ?>:</strong></div>
                </td>
              </tr>
              <tr>
                <td>
                  <div id="startind2"><?php echo uiTextSnippet('personid'); ?>:</div>
                </td>
                <td>
                  <table id="startind3">
                    <tr>
                      <td>
                        <input id='personID' name='personID' type='text' size='10'><?php echo uiTextSnippet('or'); ?>
                      </td>
                      <td>
                        <a href="#" title="<?php echo uiTextSnippet('find'); ?>"
                           onclick="return findItem('I', 'personID', '', getTree(document.getElementById('tree1')), '<?php echo $assignedbranch; ?>');">
                          <img class='icon-sm' src='svg/magnifying-glass.svg'>
                        </a>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td colspan='2'>
                  <div id="numgens1"><br><strong><?php echo uiTextSnippet('numgenerations'); ?>
                      :</strong></div>
                </td>
              </tr>
              <tr>
                <td>
                  <div id="numgens2"><?php echo uiTextSnippet('ancestors'); ?>:</div>
                </td>
                <td>
                  <div id="numgens3">
                    <input name='agens' type='text' size='3' maxlength='3' value='0'>
                    <?php echo uiTextSnippet('descofanc'); ?>:
                    <select name='dagens'>
                      <option value='0'>0</option>
                      <option value='1' selected>1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                      <option value="4">4</option>
                      <option value="5">5</option>
                    </select>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div id="numgens4"><?php echo uiTextSnippet('descendants'); ?>:</div>
                </td>
                <td>
                  <div id="numgens5">
                    <input name='dgens' type='text' size='3' maxlength='3' value='0'>
                    <input name='dospouses' type='checkbox' value='1' checked> <?php echo uiTextSnippet('inclspouses'); ?>
                  </div>
                </td>
              </tr>
            </table>
            <br>
            <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
            <input name='submitx' type='submit' value="<?php echo uiTextSnippet('saveexit'); ?>">
          </form>
        </td>
      </tr>
    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/selectutils.js"></script>
<script>
  var tree = '';

  function validateForm() {
    var rval = true;

    document.form1.branch.value = document.form1.branch.value.replace(/[^a-zA-Z0-9-_]+/g, "");
    if (document.form1.branch.value.length === 0) {
      alert(textSnippet('enterbranchid'));
      rval = false;
    } else if (document.form1.description.value.length === 0) {
      alert(textSnippet('enterbranchdesc'));
      rval = false;
    }
    return rval;
  }
</script>
</body>
</html>