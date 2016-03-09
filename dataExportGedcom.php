<?php
include("begin.php");
include($subroot . "importconfig.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if (!$allow_ged && $assignedtree) {
  $query = "SELECT disallowgedcreate FROM $trees_table WHERE gedcom = \"$assignedtree\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $disallowgedcreate = $row['disallowgedcreate'];
  tng_free_result($result);

  if ($disallowgedcreate) {
    $message = uiTextSnippet('norights');
    header("Location: admin_login.php?message=" . urlencode($message));
    exit;
  }
}
if ($assignedtree) {
  $wherestr = "WHERE gedcom = \"$assignedtree\"";
} else {
  $wherestr = "";
}
$treequery = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";

$query = "SELECT branch, gedcom, description FROM $branches_table WHERE gedcom = \"{$row['gedcom']}\" ORDER BY description";
$branchresult = tng_query($query);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('gedexport'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="datamaint-gedexport">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('datamaint-gedexport', $message);
    $navList = new navList('');
    $navList->appendItem([true, "dataImportGedcom.php", uiTextSnippet('import'), "import"]);
//    $navList->appendItem([$allow_ged,dataExportGedcomrt.php", uiTextSnippet('export'), "export"]);
    $navList->appendItem([true, "dataSecondaryProcesses.php", uiTextSnippet('secondarymaint'), "second"]);
    echo $navList->build("export");
    ?>
    <form name='form1' action='dataExportGedcomFormAction.php' method='post'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('tree'); ?>:</td>
          <td>
            <select id='gedcom' name='tree' onchange="swapBranches();">
              <?php
              $treeresult = tng_query($treequery) or die(uiTextSnippet('cannotexecutequery') . ": $treequery");
              $firsttree = "";
              while ($treerow = tng_fetch_assoc($treeresult)) {
                if (!$firsttree) {
                  $firsttree = $treerow['gedcom'];
                }
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
        <tr>
          <td><?php echo uiTextSnippet('branch'); ?>:</td>
          <td>
            <?php
            $query = "SELECT branch, gedcom, description FROM $branches_table WHERE gedcom = \"$firsttree\" ORDER BY description";
            $branchresult = tng_query($query);

            echo "<select id='branch' name=\"branch\" size=\"$selectnum\">\n";
            echo "  <option value=''>" . uiTextSnippet('allbranches') . "</option>\n";
            while ($branch = tng_fetch_assoc($branchresult)) {
              echo "  <option value=\"{$branch['branch']}\"";
              if ($row['branch'] == $branch['branch']) {
                echo " selected";
              }
              echo ">{$branch['description']}</option>\n";
            }
            echo "</select>\n";
            ?>
          </td>
        </tr>
      </table>
      <br>
      <input id='exliving' name='exliving' type='checkbox' value='1'> 
      <label for='exliving'><?php echo uiTextSnippet('exliving'); ?></label>
      <input id='exprivate' name='exprivate' type='checkbox' value='1'> 
      <label for='exprivate'><?php echo uiTextSnippet('exprivate'); ?></label> 
      <br><br>
      <input id='exportmedia' name='exportmedia' type='checkbox' value='1' onClick="toggleStuff();">
      <label for='exportmedia'><?php echo uiTextSnippet('exportmedia'); ?></label>
      <br>
      <div id='exprows' style="display: none">
        <table class='table table-sm'>
          <tr>
            <td><?php echo uiTextSnippet('select'); ?></td>
            <td><?php echo uiTextSnippet('mediatypes'); ?></td>
            <td><?php echo uiTextSnippet('exppaths'); ?>:</td>
          </tr>
          <?php
          foreach ($mediatypes as $mediatype) {
            $msgID = $mediatype['ID'];
            switch ($msgID) {
              case "photos":
                $value = strtok($locimppath['photos'], ",");
                break;
              case "histories":
                $value = strtok($locimppath['histories'], ",");
                break;
              case "documents":
                $value = strtok($locimppath['documents'], ",");
                break;
              case "headstones":
                $value = strtok($locimppath['headstones'], ",");
                break;
              default:
                $value = strtok($locimppath['other'], ",");
                break;
            }
            echo "<tr><td>\n";
            echo "<input name=\"incl_$msgID\" type='checkbox' value='1' checked /></td>\n<td>" . $mediatype['display'] . ":</td>\n<td>\n";
            echo "<input class='verylongfield' name=\"exp_path_$msgID\" type='text' value=\"$value\"></td></tr>\n";
          }
          ?>
        </table>
      </div>
      <br>
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('export'); ?>">
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script>
    <?php include("branchlibjs.php"); ?>

    function toggleStuff() {
      if (document.form1.exportmedia.checked === true)
        $('#exprows').slideDown(400);
      else
        $('#exprows').slideUp(400);
    }
  </script>
</body>
</html>

