<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if ($assignedtree) {
  $wherestr = "WHERE gedcom = \"$assignedtree\"";
} else {
  $wherestr = "";
}
$query = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";
$result = tng_query($query);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('secondary'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    $allow_export = 1;
    if (!$allow_ged && $assignedtree) {
      $query = "SELECT disallowgedcreate FROM $trees_table WHERE gedcom = \"$assignedtree\"";
      $disresult = tng_query($query);
      $row = tng_fetch_assoc($disresult);
      if ($row['disallowgedcreate']) {
        $allow_export = 0;
      }
      tng_free_result($disresult);
    }
    echo $adminHeaderSection->build('datamaint-secondary', $message);
    $navList = new navList('');
    $navList->appendItem([true, "dataImportGedcom.php", uiTextSnippet('import'), "import"]);
    $navList->appendItem([$allow_export, "dataExportGedcom.php", uiTextSnippet('export'), "export"]);
//    $navList->appendItem([true, "dataSecondaryProcesses.php", uiTextSnippet('secondarymaint'), "second"]);
    echo $navList->build("second");
    ?>
    <form action="dataSecondaryProcessesFormAction.php" method='post' name='form1'>
      <label for='tree'><?php echo uiTextSnippet('tree'); ?></label>
      <select class='form-control' name='tree'>
        <?php
        if (!$assignedtree) {
          echo "  <option value=\"--all--\">" . uiTextSnippet('alltrees') . "</option>\n";
        }
        while ($row = tng_fetch_assoc($result)) {
          echo "  <option value=\"{$row['gedcom']}\">{$row['treename']}</option>\n";
        }
        ?>
      </select>
      <br>
      <hr>
      <input class='btn btn-secondary-outline' name='secaction' type='submit' value="<?php echo uiTextSnippet('tracklines'); ?>">
      <input class='btn btn-secondary-outline' name='secaction' type='submit' value="<?php echo uiTextSnippet('sortchildren'); ?>">
      <input class='btn btn-secondary-outline' name='secaction' type='submit' value="<?php echo uiTextSnippet('sortspouses'); ?>">
      <input class='btn btn-secondary-outline' name='secaction' type='submit' value="<?php echo uiTextSnippet('relabelbranches'); ?>">

      <input class='btn btn-secondary-outline' name='secaction' type='submit' value="<?php echo uiTextSnippet('creategendex'); ?>">
      <input class='btn btn-secondary-outline' name='secaction' type='submit' value="<?php echo uiTextSnippet('evalmedia'); ?>">
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
</body>
</html>

