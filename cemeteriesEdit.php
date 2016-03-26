<?php
require 'begin.php';
require $subroot . 'mapconfig.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

$tng_search_cemeteries = $_SESSION['tng_search_cemeteries'];

$query = "SELECT * FROM $cemeteries_table WHERE cemeteryID = \"$cemeteryID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['cemname'] = preg_replace("/\"/", "&#34;", $row['cemname']);

$query = "SELECT state FROM $states_table";
$stateresult = tng_query($query);

$query = "SELECT country FROM $countries_table";
$countryresult = tng_query($query);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('modifycemetery'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body
  <?php
  if ($map['key']) {
    if (!$map['startoff']) {
      echo " onload=\"divbox('mapcontainer');\"";
    }
  }
  ?>>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('cemeteries-modifycemetery', $message);
    $navList = new navList('');
    $navList->appendItem([true, "cemeteriesBrowse.php", uiTextSnippet('browse'), "findcem"]);
    $navList->appendItem([$allowAdd, "cemeteriesAdd.php", uiTextSnippet('add'), "addcemetery"]);
    $navList->appendItem([$allowAdd, "#", uiTextSnippet('edit'), "edit"]);
    echo $navList->build("edit");
    ?>
    <br>
    <a href="cemeteriesShowCemetery.php?cemeteryID=<?php echo $cemeteryID; ?>&amp;tree=<?php echo $tree; ?>" title='<?php echo uiTextSnippet('preview') ?>'>
      <img class='icon-sm' src='svg/eye.svg'>
    </a>
    <form action="cemeteriesEditFormAction.php" method='post' name='form1' id='form1' ENCTYPE="multipart/form-data"
          onSubmit="return validateForm();">
      <table class='table table-sm'>
        <tr>
          <td>
            <table>
              <tr>
                <td><?php echo uiTextSnippet('cemeteryname'); ?>:</td>
                <td width="80%">
                  <input id='cemname' name='cemname' type='text' value="<?php echo $row['cemname']; ?>" size='40'>
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('maptoupload'); ?>*:</td>
                <td>
                  <input name='newfile' type='file' onChange="populatePath(document.form1.newfile, document.form1.maplink);">
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('mapfilenamefolder'); ?>**:</td>
                <td>
                  <input id='maplink' name='maplink' type='text' value="<?php echo $row['maplink']; ?>" size='60'>
                  <input id='maplink_org' type='hidden' value="<?php echo $row['maplink']; ?>">
                  <input id='maplink_last' type="hidden">
                  <input type='button' value="<?php echo uiTextSnippet('select') . "..."; ?>"
                         onClick="FilePicker('maplink', 'headstones');">
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('city'); ?>:</td>
                <td><input id='city' name='city' type='text' value="<?php echo $row['city']; ?>">
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('countyparish'); ?>:</td>
                <td>
                  <input id='county' name='county' type='text' value="<?php echo $row['county']; ?>">
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('stateprovince'); ?>:</td>
                <td>
                  <select name='state' id="state">
                    <option></option>
                    <?php
                    while ($staterow = tng_fetch_assoc($stateresult)) {
                      echo "  <option value=\"{$staterow['state']}\"";
                      if ($staterow['state'] == $row['state']) {
                        echo " selected";
                      }
                      echo ">{$staterow['state']}</option>\n";
                    }
                    ?>
                  </select>
                  <input id='addnewstate' type='button' value="<?php echo uiTextSnippet('addnew'); ?>">
                  <input id='deletestate' type='button' value="<?php echo uiTextSnippet('deleteselected'); ?>">
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('cap_country'); ?>:</td>
                <td>
                  <select name='country' id="country">
                    <option></option>
                    <?php
                    while ($countryrow = tng_fetch_assoc($countryresult)) {
                      echo "  <option value=\"{$countryrow['country']}\"";
                      if ($countryrow['country'] == $row['country']) {
                        echo " selected";
                      }
                      echo ">{$countryrow['country']}</option>\n";
                    }
                    ?>
                  </select>
                  <input id='addnewcountry' type='button' value="<?php echo uiTextSnippet('addnew'); ?>">
                  <input id='deletecountry' type='button' value="<?php echo uiTextSnippet('deleteselected'); ?>">
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('linkplace'); ?>:</td>
                <td>
                  <input class='longfield' id='place' name='place' type='text' value="<?php echo $row['place']; ?>" onblur="fillCemetery(this.value);">
                  <a href="#" onclick="return openFindPlaceForm('place');" title="<?php echo uiTextSnippet('find'); ?>">
                    <img class='icon-sm-inline' src='svg/magnifying-glass.svg' alt="<?php echo uiTextSnippet('find'); ?>">
                  </a>
                  <input id='fillplace' type='button' value="<?php echo uiTextSnippet('fillplace'); ?>">
                </td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>
                  <input name='usecoords' type='checkbox' value='1'/> <?php echo uiTextSnippet('usecemcoords'); ?>
                </td>
              </tr>
              <?php
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
                <td><input id='latbox' name='latitude' type='text' value="<?php echo $row['latitude']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('longitude'); ?>:</td>
                <td><input id='lonbox' name='longitude' type='text' value="<?php echo $row['longitude']; ?>"></td>
              </tr>
              <?php
              if ($map['key']) {
                ?>
                <tr>
                  <td><?php echo uiTextSnippet('zoom'); ?>:</td>
                  <td><input id='zoombox' name='zoom' type='text' value="<?php echo $row['zoom']; ?>"></td>
                </tr>
                <?php
              }
              ?>
              <tr>
                <td><?php echo uiTextSnippet('notes'); ?>:</td>
                <td>
                  <textarea wrap='soft' cols="60" rows="8" name="notes"><?php echo $row['notes']; ?></textarea>
                </td>
              </tr>
              <tr>
                <td colspan='2'>
                  <span>
                    <?php
                    echo uiTextSnippet('onsave') . ":<br>";
                    echo "<input name='newscreen' type='radio' value='return'> " . uiTextSnippet('savereturn') . "<br>\n";
                    if ($cw) {
                      echo "<input name='newscreen' type='radio' value='close' checked> " . uiTextSnippet('closewindow') . "\n";
                    } else {
                      echo "<input name='newscreen' type='radio' value='none' checked> " . uiTextSnippet('saveback') . "\n";
                    }
                    ?>
                  </span>
                </td>
              </tr>
            </table>
            &nbsp;
            <input name='cemeteryID' type='hidden' value="<?php echo "$cemeteryID"; ?>">
            <input name='cw' type='hidden' value="<?php echo "$cw"; /* stands for "close window" */ ?>">
            <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">

            <p>*<?php echo uiTextSnippet('ifmapuploaded'); ?><br>
              **<?php echo uiTextSnippet('requiredmap'); ?></p>
            <?php
            if ($row['maplink']) {
              $size = GetImageSize("$rootpath$headstonepath/" . $row['maplink']);
              echo "<br><br><img src=\"$headstonepath/{$row['maplink']}\" $size[3] alt=\"{$row['cemname']}\">\n";
            }
            ?>
          </td>
        </tr>
      </table>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<?php if ($map['key']) { ?>
  <script src='https://maps.googleapis.com/maps/api/js?language="<?php echo uiTextSnippet('glang'); ?>"'></script>
<?php } ?>
<script src='js/selectutils.js'></script>
<script src='js/mediautils.js'></script>
<script src='js/admin.js'></script>
<script src='js/cemeteries.js'></script>
<script>
  var tree = '';

  var loaded = false;
</script>
<?php
if ($map['key']) {
  include "googlemaplib2.php";
}
?>
</body>
</html>
