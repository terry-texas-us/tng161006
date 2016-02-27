<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if ($assignedtree) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

$query = "SELECT gedcom, treename FROM $trees_table ORDER BY treename";
$result = tng_query($query);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('backuprestore'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="backuprestore-renumber">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('backuprestore-renumber', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_utilities.php?sub=tables", uiTextSnippet('tables'), "tables"]);
    $navList->appendItem([true, "admin_utilities.php?sub=structure", uiTextSnippet('tablestruct'), "structure"]);
    $navList->appendItem([true, "admin_renumbermenu.php", uiTextSnippet('renumber'), "renumber"]);
    echo $navList->build("renumber");
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <p><?php echo uiTextSnippet('reseqwarn'); ?></p>

          <h4><?php echo uiTextSnippet('renumber'); ?></h4>
          <form action="admin_renumber.php" method='post' name='form1'>
            <table>
              <tr>
                <td><?php echo uiTextSnippet('tree'); ?>:</td>
                <td>
                  <select name='tree'>
                    <?php
                    while ($row = tng_fetch_assoc($result)) {
                      echo "	<option value=\"{$row['gedcom']}\">{$row['treename']}</option>\n";
                    }
                    ?>
                  </select>
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('idtype'); ?>:</td>
                <td>
                  <select name='type'>
                    <option value="person"><?php echo uiTextSnippet('people'); ?></option>
                    <option value="family"><?php echo uiTextSnippet('families'); ?></option>
                    <option value="source"><?php echo uiTextSnippet('sources'); ?></option>
                    <option value="repo"><?php echo uiTextSnippet('repositories'); ?></option>
                  </select>
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('mindigits'); ?>*:</td>
                <td>
                  <select name="digits">
                    <?php
                    for ($i = 1; $i <= 20; $i++) {
                      echo "<option value=\"$i\">$i</option>\n";
                    }
                    ?>
                  </select>
                </td>
              </tr>
              <!--<tr>
                <td><?php echo uiTextSnippet('useroffset'); ?>*:</td>
                <td><input name='start' type='text' value='1' /></td>
              </tr>-->
            </table>
            <br>
            <input name='start' type='hidden' value='1'/>
            <input type='submit'
                   value="<?php echo uiTextSnippet('renumber'); ?>"<?php if (!$tngconfig['maint']) {
              echo " disabled";
            } ?>>
            <?php
            if (!$tngconfig['maint']) {
              echo "<span>" . uiTextSnippet('needmaint') . "</span>";
            }
            ?>
            <br><br>
            <?php echo "<p>*" . uiTextSnippet('niprefix') . "</p>\n"; ?>
          </form>
        </td>
      </tr>
    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
</body>
</html>