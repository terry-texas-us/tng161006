<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if (!$allow_edit || ($assignedtree && $assignedtree != $tree)) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

$query = "SELECT * FROM $trees_table WHERE gedcom = \"$tree\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

$query = "SELECT count(personID) as pcount FROM $people_table WHERE gedcom = \"$tree\"";
$result = tng_query($query);
$prow = tng_fetch_assoc($result);
$pcount = number_format($prow['pcount']);
tng_free_result($result);

$query = "SELECT count(familyID) as fcount FROM $families_table WHERE gedcom = \"{$row['gedcom']}\"";
$famresult = tng_query($query);
$famrow = tng_fetch_assoc($famresult);
$fcount = number_format($famrow['fcount']);
tng_free_result($famresult);

$query = "SELECT count(sourceID) as scount FROM $sources_table WHERE gedcom = \"{$row['gedcom']}\"";
$srcresult = tng_query($query);
$srcrow = tng_fetch_assoc($srcresult);
$scount = number_format($srcrow['scount']);
tng_free_result($srcresult);

$query = "SELECT count(repoID) as rcount FROM $repositories_table WHERE gedcom = \"{$row['gedcom']}\"";
$reporesult = tng_query($query);
$reporow = tng_fetch_assoc($reporesult);
$rcount = number_format($reporow['rcount']);
tng_free_result($reporesult);

$query = "SELECT count(noteID) as ncount FROM $xnotes_table WHERE gedcom = \"{$row['gedcom']}\"";
$nresult = tng_query($query);
$nrow = tng_fetch_assoc($nresult);
$ncount = number_format($nrow['ncount']);
tng_free_result($nresult);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('modifytree'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="trees-modifytree">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('trees-modifytree', $message);
    $navList = new navList('');
    $allow_add_tree = $assignedtree ? 0 : $allow_add;
    $navList->appendItem([true, "admin_trees.php", uiTextSnippet('search'), "findtree"]);
    $navList->appendItem([$allow_add_tree, "admin_newtree.php", uiTextSnippet('addnew'), "addtree"]);
    $navList->appendItem([$allow_edit, "#", uiTextSnippet('edit'), "edit"]);
    echo $navList->build("edit");
    ?>

    <table class="table table-sm">
      <tr>
        <td>
          <form action="admin_updatetree.php" method='post' name="form1" onSubmit="return validateForm();">
            <table>
              <tr>
                <td><?php echo uiTextSnippet('treeid'); ?>:</td>
                <td><?php echo "$tree"; ?></td>
                <td width="30" rowspan="11"></td>
                <td rowspan="11">
                  <table class='table table-sm'>
                    <?php
                    echo "<tr><td>" . uiTextSnippet('people') . ": </td><td align=\"right\">$pcount</td></tr>\n";
                    echo "<tr><td>" . uiTextSnippet('families') . ": </td><td align=\"right\">$fcount</td></tr>\n";
                    echo "<tr><td>" . uiTextSnippet('sources') . ": </td><td align=\"right\">$scount</td></tr>\n";
                    echo "<tr><td>" . uiTextSnippet('repositories') . ": </td><td align=\"right\">$rcount</td></tr>\n";
                    echo "<tr><td>" . uiTextSnippet('notes') . ": </td><td align=\"right\">$ncount</td></tr>\n";
                    ?>
                  </table>
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('treename'); ?>:</td>
                <td><input name='treename' type='text' size='50' value="<?php echo $row['treename']; ?>">
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('description'); ?>:</td>
                <td><textarea cols="40" rows="3"
                              name='description'><?php echo $row['description']; ?></textarea></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('owner'); ?>:</td>
                <td><input name='owner' type='text' size='50' value="<?php echo $row['owner']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('email'); ?>:</td>
                <td><input name='email' type='text' size='50' value="<?php echo $row['email']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('address'); ?>:</td>
                <td><input name='address' type='text' size='50' value="<?php echo $row['address']; ?>"></td>
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
                <td><input name='zip' type='text' size='50' value="<?php echo $row['zip']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('cap_country'); ?>:</td>
                <td><input name='country' type='text' size='50' value="<?php echo $row['country']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('phone'); ?>:</td>
                <td><input name='phone' type='text' size='50' value="<?php echo $row['phone']; ?>"></td>
              </tr>
            </table>
            <span>
              <input name='private' type='checkbox' value='1'<?php if ($row['secret']) {echo " checked";} ?>> <?php echo uiTextSnippet('keepprivate'); ?>
              <br>
              <input name='disallowgedcreate' type='checkbox' value='1'<?php if ($row['disallowgedcreate']) {echo " checked";} ?>> <?php echo uiTextSnippet('gedcomextraction'); ?>
              <br>
              <input name='disallowpdf' type='checkbox' value='1'<?php if ($row['disallowpdf']) {echo " checked";} ?>> <?php echo uiTextSnippet('nopdf'); ?>
              <br><br>
            </span>
            <input name='tree' type='hidden' value="<?php echo "$tree"; ?>">
            <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
          </form>
        </td>
      </tr>

    </table>
<?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script>
    function validateForm() {
      var rval = true;
      if (document.form1.treename.value.length === 0) {
        alert(textSnippet('entertreename'));
        rval = false;
      }
      return rval;
    }
  </script>
</body>
</html>