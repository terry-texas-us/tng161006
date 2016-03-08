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
    $navList->appendItem([true, "admin_secondmenu.php", uiTextSnippet('secondarymaint'), "second"]);
    echo $navList->build("second");
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <form action="admin_secondary.php" method='post' name='form1'>
            <span><?php echo uiTextSnippet('tree'); ?>: <select name='tree'>
                <?php
                if (!$assignedtree) {
                  echo "  <option value=\"--all--\">" . uiTextSnippet('alltrees') . "</option>\n";
                }
                while ($row = tng_fetch_assoc($result)) {
                  echo "  <option value=\"{$row['gedcom']}\">{$row['treename']}</option>\n";
                }
                ?>
              </select><br><br></span>
            <input name='secaction' type='submit' value="<?php echo uiTextSnippet('tracklines'); ?>">
            <input name='secaction' type='submit' value="<?php echo uiTextSnippet('sortchildren'); ?>">
            <input name='secaction' type='submit' value="<?php echo uiTextSnippet('sortspouses'); ?>">
            <input name='secaction' type='submit' value="<?php echo uiTextSnippet('relabelbranches'); ?>">
            
            <input name='secaction' type='submit' value="<?php echo uiTextSnippet('creategendex'); ?>">
            <input name='secaction' type='submit' value="<?php echo uiTextSnippet('evalmedia'); ?>">
          </form>
          <p><?php echo uiTextSnippet('postgdx'); ?>:<br>
            &raquo; <a href="http://gendexnetwork.org" target="_blank">GenDex Network</a><br>
            &raquo; <a href="http://www.familytreeseeker.com" target="_blank">FamilyTreeSeeker.com</a>
          </p>
        </td>
      </tr>
    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
</body>
</html>

