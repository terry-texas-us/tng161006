<?php
/**
 * Name history: admin_editcemetery.php
 */

require 'begin.php';
require $subroot . 'mapconfig.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}

$tng_search_cemeteries = $_SESSION['tng_search_cemeteries'];

$query = "SELECT * FROM cemeteries WHERE cemeteryID = '$cemeteryID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['cemname'] = preg_replace('/\"/', '&#34;', $row['cemname']);

$query = 'SELECT state FROM states';
$stateresult = tng_query($query);

$query = 'SELECT country FROM countries';
$countryresult = tng_query($query);

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('modifycemetery'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
  
<body<?php echo ((bool) $map['key'] === true && (bool) $map['startoff'] === false) ? " onload=\"divbox('mapcontainer');\"" : ""; ?>>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('cemeteries-modifycemetery', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'cemeteriesBrowse.php', uiTextSnippet('browse'), 'findcem']);
    $navList->appendItem([$allowAdd, 'cemeteriesAdd.php', uiTextSnippet('add'), 'addcemetery']);
    // $navList->appendItem([$allowAdd, '#', uiTextSnippet('edit'), 'edit']);
    echo $navList->build('edit');
    ?>
    <br>
    <a href="cemeteriesShowCemetery.php?cemeteryID=<?php echo $cemeteryID; ?>" title='<?php echo uiTextSnippet('preview') ?>'>
      <img class='icon-sm' src='svg/eye.svg'>
    </a>
    <br>
    <form action="cemeteriesEditFormAction.php" method='post' name='form1' id='form1' ENCTYPE="multipart/form-data" onSubmit="return validateForm();">
      <div class='form-group row'>
        <label class='col-form-label col-sm-3' for='cemname'><?php echo uiTextSnippet('cemname'); ?></label>
        <div class='col-sm-9'>
        <input class='form-control' id='cemname' name='cemname' type='text' value="<?php echo $row['cemname']; ?>">
        </div>
      </div>
      <div class='form-group row'>
        <label class='col-form-label col-sm-3' for='newfile'><?php echo uiTextSnippet('maptoupload'); ?>*</label>
        <div class='col-sm-9'>
          <input class='form-control' name='newfile' type='file' onChange="populatePath(document.form1.newfile, document.form1.maplink);">
        </div>
      </div>
      <div class='form-group row'>
        <label class='col-form-label col-sm-3' for='maplink'><?php echo uiTextSnippet('mapfilenamefolder'); ?>**</label>
        <div class='col-sm-9'>
          <div class='input-group'>
            <input class='form-control' id='maplink' name='maplink' type='text' value="<?php echo $row['maplink']; ?>">
            <span class='input-group-btn'>
              <input class='form-control' type='button' value="<?php echo uiTextSnippet('select') . '...'; ?>" onClick="FilePicker('maplink', 'headstones');">
            </span>
          </div>
        </div>
        <input id='maplink_org' type='hidden' value="<?php echo $row['maplink']; ?>">
        <input id='maplink_last' type="hidden">
      </div>
      <div class='form-group row'>
        <label class='col-form-label col-sm-3' for='city'><?php echo uiTextSnippet('city'); ?></label>
        <div class='col-sm-9'>
          <input class='form-control' id='city' name='city' type='text' value="<?php echo $row['city']; ?>">
        </div>
      </div>
      <div class='form-group row'>
        <label class='col-form-label col-sm-3' for='county'><?php echo uiTextSnippet('county'); ?></label>
        <div class='col-sm-9'>
          <input class='form-control' id='county' name='county' type='text' value="<?php echo $row['county']; ?>">
        </div>
      </div>
      <div class='form-group row'>
        <label class='col-form-label col-sm-3' for='state'><?php echo uiTextSnippet('stateprovince'); ?></label>
        <div class='col-sm-4'>
          <select class='form-control' name='state' id='state'>
            <option></option>
            <?php
            while ($staterow = tng_fetch_assoc($stateresult)) {
              echo "<option value=\"{$staterow['state']}\"";
              if ($staterow['state'] === $row['state']) {
                echo ' selected';
              }
              echo ">{$staterow['state']}</option>\n";
            }
            ?>
          </select>
        </div>
        <div class='col-sm-5'>
          <button class='btn btn-outline-secondary' id='addnewstate' type='button'><?php echo uiTextSnippet('addnew'); ?></button>
          <button class='btn btn-outline-warning' id='deletestate' type='button'><?php echo uiTextSnippet('deleteselected'); ?></button>
        </div>
      </div>

      <div class='form-group row'>
        <label class='col-form-label col-sm-3' for='country'><?php echo uiTextSnippet('cap_country'); ?></label>
        <div class='col-sm-4'>
          <select class='form-control' name='country' id='country'>
            <option></option>
            <?php
            while ($countryrow = tng_fetch_assoc($countryresult)) {
              echo "<option value=\"{$countryrow['country']}\"";
              if ($countryrow['country'] == $row['country']) {
                echo ' selected';
              }
              echo ">{$countryrow['country']}</option>\n";
            }
            ?>
          </select>
        </div>
        <div class='col-sm-5'>
          <button class='btn btn-outline-secondary' id='addnewcountry' type='button'><?php echo uiTextSnippet('addnew'); ?></button>
          <button class='btn btn-outline-warning' id='deletecountry' type='button'><?php echo uiTextSnippet('deleteselected'); ?></button>
        </div>
      </div>
      <hr>
      <div class='form-group row'>
        <label class='col-form-label col-sm-3' for='place'><?php echo uiTextSnippet('linkplace'); ?></label>
        <div class='col-sm-6'>
          <input class='form-control' id='place' name='place' type='text' value="<?php echo $row['place']; ?>" onblur="fillCemetery(this.value);">
        </div>
        <div class='col-sm-3'>
          <a href="#" onclick="return openFindPlaceForm('place');" title="<?php echo uiTextSnippet('find'); ?>">
            <img class='icon-sm-inline' src='svg/magnifying-glass.svg' alt="<?php echo uiTextSnippet('find'); ?>">
          </a>
          <input class='btn btn-outline-primary' id='fillplace' type='button' value="<?php echo uiTextSnippet('fillplace'); ?>">
        </div>
      </div>
       <input class='form-check-inline' name='usecoords' type='checkbox' value='1'> <?php echo uiTextSnippet('usecemcoords'); ?>
      <hr>
      <?php if ($map['key']) { ?>
        <div class='form-group row'>
          <label class='col-form-label col-sm-2'><?php echo uiTextSnippet('mapof'); ?></label>
          <div class='col-sm-10'>
            <?php echo buildGoogleMapCardHtml($map, $row['place']); ?>
          </div>
        </div>
      <?php } ?>
      <div class='form-group row'>
        <label class='col-form-label col-sm-2' for='latitude'><?php echo uiTextSnippet('latitude'); ?></label>
        <div class='col-sm-4'>
          <input class='form-control' id='latbox' name='latitude' type='text' value="<?php echo $row['latitude']; ?>">
        </div>
        <label class='col-form-label col-sm-2' for='longitude'><?php echo uiTextSnippet('longitude'); ?></label>
        <div class='col-sm-4'>
          <input class='form-control' id='lonbox' name='longitude' type='text' value="<?php echo $row['longitude']; ?>">
        </div>
      </div>

      <?php if ($map['key']) { ?>
        <div class='form-group row'>
          <label class='col-form-label col-sm-2' for='zoom'><?php echo uiTextSnippet('zoom'); ?></label>
          <div class='col-sm-4'>
            <input class='form-control' id='zoombox' name='zoom' type='text' value="<?php echo $row['zoom']; ?>">
          </div>
        </div>
      <?php } ?>
      <div class='form-group row'>
        <label class='col-form-label col-sm-2' for='notes'><?php echo uiTextSnippet('notes'); ?></label>
        <div class='col-sm-10'>
          <textarea class='form-control' name='notes' rows='4'><?php echo $row['notes']; ?></textarea>
        </div>
      </div>
      <hr>
      <span>
        <?php
        echo uiTextSnippet('onsave') . ':<br>';
        echo "<input name='newscreen' type='radio' value='return'> " . uiTextSnippet('savereturn') . "<br>\n";
        if ($cw) {
          echo "<input name='newscreen' type='radio' value='close' checked> " . uiTextSnippet('closewindow') . "\n";
        } else {
          echo "<input name='newscreen' type='radio' value='none' checked> " . uiTextSnippet('saveback') . "\n";
        }
        ?>
      </span>
      <input name='cemeteryID' type='hidden' value="<?php echo "$cemeteryID"; ?>">
      <input name='cw' type='hidden' value="<?php echo "$cw"; /* stands for "close window" */ ?>">
      <br>
      <input class='btn btn-primary' name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">

      <p>*<?php echo uiTextSnippet('ifmapuploaded'); ?><br>
        **<?php echo uiTextSnippet('requiredmap'); ?></p>
      <?php
      if ($row['maplink']) {
        $size = GetImageSize("$rootpath$headstonepath/" . $row['maplink']);
        echo "<br><br><img src=\"$headstonepath/{$row['maplink']}\" $size[3] alt=\"{$row['cemname']}\">\n";
      }
      ?>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<?php if ($map['key']) { ?>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAlWTL2QZDQv9BWXBvCwdAuhq1Lak8jSwU&amp;<?php echo uiTextSnippet('localize'); ?>"></script>
<?php } ?>
<script src='js/selectutils.js'></script>
<script src='js/mediautils.js'></script>
<script src='js/admin.js'></script>
<script src='js/cemeteries.js'></script>
<script>
  var loaded = false;
</script>
<?php
if ($map['key']) {
  include 'googlemaplib2.php';
}
?>
</body>
</html>
