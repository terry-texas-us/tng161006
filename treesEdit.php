<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
include("version.php");

if (!$allowEdit || ($assignedtree && $assignedtree != $tree)) {
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
    $allow_add_tree = $assignedtree ? 0 : $allowAdd;
    $navList->appendItem([true, 'treesBrowse.php', uiTextSnippet('search'), "findtree"]);
    $navList->appendItem([$allow_add_tree, 'treesAdd.php', uiTextSnippet('add'), "addtree"]);
//    $navList->appendItem([$allowEdit, "#", uiTextSnippet('edit'), "edit"]);
    echo $navList->build("edit");
    ?>
    <form name='form1' action="treesEditFormAction.php" method='post' onSubmit="return validateForm();">
      <section class='card'>
        <div class='card-header'>
          <span><?php echo uiTextSnippet('existingtreeinfo'); ?></span>
        </div>
        <div class='card-block'>
          <div class='row form-group'>
            <div class='col col-md-2'>
              <label class='form-control-label' for='gedcom'><?php echo uiTextSnippet('treeid'); ?>:</label>
            </div>
            <div class='col col-md-4'>
              <input class='form-control' name='gedcom' type='text'  placeholder='<?php echo "$tree"; ?>' disabled>
            </div>
            <div class='col col-md-6'>
              <label for='treename' class='sr-only'><?php echo uiTextSnippet('treename'); ?></label>
              <input class='form-control' name='treename' type='text' value="<?php echo $row['treename']; ?>" placeholder="<?php echo uiTextSnippet('treename'); ?>" required>
            </div>
          </div>         
          <div class='row form-group'>
            <div class='col col-sm-12'>
              <label for='description' class='sr-only'><?php echo uiTextSnippet('description'); ?></label>
              <input class='form-control' name='description' type='text' value='<?php echo $row['description']; ?>' placeholder="<?php echo uiTextSnippet('description'); ?>">
            </div>
          </div>
          <div class='footer'>
            <div class='row form-group'>
              <div class='col col-sm-6'>
                <label>
                  <input name='disallowgedcreate' type='checkbox' value='1'<?php if ($row['disallowgedcreate']) {echo " checked";} ?>> <?php echo uiTextSnippet('gedcomextraction'); ?>
                </label>
                <br>
                <label>
                  <input name='disallowpdf' type='checkbox' value='1'<?php if ($row['disallowpdf']) {echo " checked";} ?>> <?php echo uiTextSnippet('nopdf'); ?>
                </label>
              </div>
              <div class='col col-sm-3'>
                <?php echo uiTextSnippet('people') . ": $pcount\n"; ?>
                <br>
                <?php echo uiTextSnippet('families') . ": $fcount\n"; ?>
              </div>
              <div class='col col-sm-3'>
                <?php echo uiTextSnippet('sources') . ": $scount\n"; ?>
                <br>
                <?php echo uiTextSnippet('repositories') . ": $rcount\n"; ?>
                <br>
                <?php echo uiTextSnippet('notes') . ": $ncount\n"; ?>
              </div>
            </div>
          </div>
        </div>
      </section>  
      
      
      <section class='card'>
        <div class='card-header'>
          <span><?php echo uiTextSnippet('ownerofthistree'); ?></span>
        </div>
        <div class='card-block'>
          <div class='row'>
            <div class='col col-sm-6'>
              <label for='owner' class='sr-only'><?php echo uiTextSnippet('owner'); ?></label>
              <input class='form-control' name='owner' type='text' value="<?php echo $row['owner']; ?>" placeholder="<?php echo uiTextSnippet('owner'); ?>">
              <br>
              <label for='phone' class='sr-only'><?php echo uiTextSnippet('phone'); ?></label>
              <input class='form-control' name='phone' type='text' value="<?php echo $row['phone']; ?>" placeholder="<?php echo uiTextSnippet('phone'); ?>">
              <label for='email' class='sr-only'><?php echo uiTextSnippet('email'); ?></label>
              <input class='form-control' name='email' type='text' value="<?php echo $row['email']; ?>" placeholder="<?php echo uiTextSnippet('email'); ?>">
            </div>
            <div class='col col-sm-6'>
              <label for='address' class='sr-only'><?php echo uiTextSnippet('address'); ?></label>
              <input class='form-control' name='address' type='text' value="<?php echo $row['address']; ?>" placeholder="<?php echo uiTextSnippet('address'); ?>">
              <label for='city' class='sr-only'><?php echo uiTextSnippet('city'); ?></label>
              <input class='form-control' name='city' type='text' value="<?php echo $row['city']; ?>" placeholder="<?php echo uiTextSnippet('city'); ?>">
              <label for='stateprov' class='sr-only'><?php echo uiTextSnippet('stateprov'); ?></label>
              <input class='form-control' name='state' type='text' value="<?php echo $row['state']; ?>" placeholder="<?php echo uiTextSnippet('stateprov'); ?>">
              <label for='zip' class='sr-only'><?php echo uiTextSnippet('zip'); ?></label>
              <input class='form-control' name='zip' type='text' value="<?php echo $row['zip']; ?>" placeholder="<?php echo uiTextSnippet('zip'); ?>">
              <label for='cap_country' class='sr-only'><?php echo uiTextSnippet('cap_country'); ?></label>
              <input class='form-control' name='country' type='text' value="<?php echo $row['country']; ?>" placeholder="<?php echo uiTextSnippet('cap_country'); ?>">
            </div>
          </div>
          <div class='footer'>
            <label>
              <input name='private' type='checkbox' value='1'<?php if ($row['secret']) {echo " checked";} ?>> <?php echo uiTextSnippet('keepprivate'); ?>
            </label>
          </div>
        </div>
      </section>
      <div class='footer'>
        <input name='tree' type='hidden' value="<?php echo "$tree"; ?>">
        <button class='btn btn-primary btn-block' name='submit' type='submit'><?php echo uiTextSnippet('save'); ?></button>
      </div>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src='js/trees.js'></script>
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