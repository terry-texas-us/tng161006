<?php
include("begin.php");
include($subroot . "mapconfig.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if (!$allow_add) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

if (!$tngconfig['places1tree']) {
  if ($assignedtree) {
    $wherestr = "WHERE gedcom = \"$assignedtree\"";
  } else {
    $wherestr = "";
  }
  $query = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";
  $result = tng_query($query);
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('addnewplace'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body<?php if ($map['key']) {
    if (!$map['startoff']) {
      echo " onload=\"divbox('mapcontainer');\"";
    }
  } ?>>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('places-addnewplace', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_places.php", uiTextSnippet('search'), "findplace"]);
    $navList->appendItem([$allow_add, "admin_newplace.php", uiTextSnippet('addnew'), "addplace"]);
    $navList->appendItem([$allow_edit && $allow_delete, "admin_mergeplaces.php", uiTextSnippet('merge'), "merge"]);
    $navList->appendItem([$allow_edit, "admin_geocodeform.php", uiTextSnippet('geocode'), "geo"]);
    echo $navList->build("addplace");
    ?>
    <form action="admin_addplace.php" method='post' name="form1" onSubmit="return validateForm();">
      <table class='table table-sm'>
        <?php
        if (!$tngconfig['places1tree']) {
          ?>
          <tr>
            <td><?php echo uiTextSnippet('tree'); ?>:</td>
            <td width="90%">
              <select name='tree'>
                <?php
                while ($row = tng_fetch_assoc($result)) {
                  echo "		<option value=\"{$row['gedcom']}\">{$row['treename']}</option>\n";
                }
                tng_free_result($result);
                ?>
              </select>
            </td>
          </tr>
          <?php
        }
        ?>
        <tr>
          <td><?php echo uiTextSnippet('place'); ?>:</td>
          <td><input id='place' name='place' type='text' size='50'></td>
        </tr>
        <?php
        if (determineLDSRights()) {
          ?>
          <tr>
            <td>&nbsp;</td>
            <td><input name='temple' type='checkbox' value='1'> <?php echo uiTextSnippet('istemple'); ?>
            </td>
          </tr>
          <?php
        }
        if ($map['key']) {
          ?>
          <tr>
            <td colspan='2'>
              <div style="padding:10px">
                <?php
                // draw the map here
                include "googlemapdrawthemap.php";
                ?>
              </div>
            </td>
          </tr>
          <?php
        }
        ?>
        <tr>
          <td><?php echo uiTextSnippet('latitude'); ?>:</td>
          <td><input id='latbox' name='latitude' type='text'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('longitude'); ?>:</td>
          <td><input id='lonbox' name='longitude' type='text'></td>
        </tr>
        <?php
        if ($map['key']) {
          ?>
          <tr>
            <td><?php echo uiTextSnippet('zoom'); ?>:</td>
            <td>
              <input id='zoombox' name='zoom' type='text'>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('placelevel'); ?>:</td>
            <td>
              <select name="placelevel">
                <option value=''></option>
                <?php
                for ($i = 1; $i < 7; $i++) {
                  echo "<option value=\"$i\">" . uiTextSnippet('level' . $i) . "</option>\n";
                }
                ?>
              </select>
            </td>
          </tr>
          <?php
        }
        ?>
        <tr>
          <td><?php echo uiTextSnippet('notes'); ?>:</td>
          <td>
            <textarea wrap='soft' cols="50" rows="5" name="notes"></textarea>
          </td>
        </tr>
      </table>
      <br>&nbsp;
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <?php if ($map['key']) { ?>
    <script src='https://maps.googleapis.com/maps/api/js?language="<?php echo uiTextSnippet('glang'); ?>"'></script>
  <?php } ?>
  <script>
    function validateForm() {
      'use strict';
      var rval = true;
      if (document.form1.place.value.length === 0) {
        alert(textSnippet('enterplace'));
        rval = false;
      }
      return rval;
    }
  </script>
  <?php
  if ($map['key']) {
    include "googlemaplib2.php";
  }
  ?>
</body>
</html>
