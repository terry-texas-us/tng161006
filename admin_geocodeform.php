<?php
require 'begin.php';
include($subroot . "mapconfig.php");
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
include("version.php");

$orgtree = $tree;

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('places'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('places-geocode', $message);
    $navList = new navList('');
    $navList->appendItem([true, "placesBrowse.php", uiTextSnippet('browse'), "findplace"]);
    $navList->appendItem([$allowAdd, "placesAdd.php", uiTextSnippet('add'), "addplace"]);
    $navList->appendItem([$allowEdit && $allowDelete, "placesMerge.php", uiTextSnippet('merge'), "merge"]);
    $navList->appendItem([$allowEdit, "admin_geocodeform.php", uiTextSnippet('geocode'), "geo"]);
    echo $navList->build("geo");
    ?>

    <table class='table table-sm'>
      <tr>
        <td>
          <h4><?php echo uiTextSnippet('geoexpl'); ?></h4>

          <form action="admin_geocode.php" method='post' name='form1'>
            <?php
            if ($tngconfig['places1tree']) {
              echo "<input name='tree1' type='hidden' value='' />\n";
            }
            ?>
            <table class='table tabel-sm'>
              <?php if (!$tngconfig['places1tree']) { ?>
                <tr>
                  <td><?php echo uiTextSnippet('tree'); ?>:</td>
                  <td>
                    <select name="tree1">
                      <?php
                      if ($assignedtree) {
                        $wherestr = "WHERE gedcom = \"$assignedtree\"";
                      } else {
                        $wherestr = "";
                      }
                      $treequery = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";
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
              <?php } ?>
              <tr>
                <td><?php echo uiTextSnippet('limit'); ?></td>
                <td>
                  <select name="limit">
                    <option value="10">10</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="250">250</option>
                    <option value="500">500</option>
                    <option value="1000">1000</option>
                    <option value="2500">2500</option>
                    <option value="5000">5000</option>
                    <option value="10000">10000</option>
                    <option value=''><?php echo uiTextSnippet('nolimit'); ?></option>
                  </select>
                </td>
              </tr>
            </table>
            <div>
              <p><?php echo uiTextSnippet('multchoice'); ?></p>
              <p>
                <input name='multiples' type='radio' value='0' checked /> <?php echo uiTextSnippet('ignoreall'); ?>
                <input name='multiples' type='radio' value='1' /> <?php echo uiTextSnippet('usefirst'); ?>
              </p>
              <input type='submit' value="<?php echo uiTextSnippet('geocode'); ?>"/>
            </div>
          </form>
        </td>
      </tr>
    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/admin.js"></script>
</body>
</html>
