<?php
require 'begin.php';
require $subroot . 'mapconfig.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowAdd) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}

$query = 'SELECT state FROM states';
$stateresult = tng_query($query);

$query = 'SELECT country FROM countries';
$countryresult = tng_query($query);

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('addnewcemetery'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body<?php echo ((bool) $map['key'] === true && (bool) $map['startoff'] === false) ? " onload=\"divbox('mapcontainer');\"" : ""; ?>>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('cemeteries-addnewcemetery', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'cemeteriesBrowse.php', uiTextSnippet('browse'), 'findcem']);
    //    $navList->appendItem([$allowAdd, 'cemeteriesAdd.php', uiTextSnippet('add'), 'addcemetery']);
    echo $navList->build('addcemetery');
    ?>
    <form action="cemeteriesAddFormAction.php" method='post' name='form1' id='form1' ENCTYPE="multipart/form-data" onSubmit="return validateForm();">
      <div class='form-group row'>
        <label class='col-form-label col-sm-3' for='cemname'><?php echo uiTextSnippet('cemname'); ?></label>
        <div class='col-sm-9'>
          <input class='form-control' id='cemname' name='cemname' type='text'>
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
            <input class='form-control' id='maplink' name='maplink' type='text'>
            <span class='input-group-btn'>
              <input class='form-control' type='button' value="<?php echo uiTextSnippet('select') . '...'; ?>" onclick="FilePicker('maplink', 'headstones');">
            </span>
          </div>
        </div>
        <input id='maplink_org' type='hidden'>
        <input id='maplink_last' type='hidden'> 
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
        <div class='col-sm-3'>
          <select class='form-control' name='state' id='state'>
            <option></option>
            <?php
            while ($staterow = tng_fetch_assoc($stateresult)) {
              echo "<option value='{$staterow['state']}'>{$staterow['state']}</option>\n";
            }
            ?>
          </select>
        </div>
        <div class='col-sm-6'>
          <button class='btn btn-outline-secondary' id='addnewstate' type='button'><?php echo uiTextSnippet('addnew'); ?></button>
          <button class='btn btn-outline-warning' id='deletestate' type='button'><?php echo uiTextSnippet('deleteselected'); ?></button>
        </div>
      </div>
      <div class='form-group row'>
        <label class='col-form-label col-sm-3' for='country'><?php echo uiTextSnippet('cap_country'); ?></label>
        <div class='col-sm-3'>
          <select class='form-control' name='country' id='country'>
            <option></option>
            <?php
            while ($countryrow = tng_fetch_assoc($countryresult)) {
              echo "  <option value='{$countryrow['country']}'>{$countryrow['country']}</option>\n";
            }
            ?>
          </select>
        </div>
        <div class='col-sm-6'>
          <button class='btn btn-outline-secondary' id='addnewcountry' type='button'><?php echo uiTextSnippet('addnew'); ?></button>
          <button class='btn btn-outline-warning' id='deletecountry' type='button'><?php echo uiTextSnippet('deleteselected'); ?></button>
        </div>
      </div>
      <hr>
      <div class='form-group row'>
        <label class='col-form-label col-sm-3 btn' for='place'><?php echo uiTextSnippet('linkplace'); ?></label>
        <div class='col-sm-6'>
          <input class='form-control' id='place' name='place' type='text' onblur="fillCemetery(this.value);">
        </div>
        <div class='col-sm-3'>
          <a href="#" onclick="return openFindPlaceForm('place');" title="<?php echo uiTextSnippet('find'); ?>">
            <img class='icon-sm-inline' src='svg/magnifying-glass.svg' alt="<?php echo uiTextSnippet('find'); ?>">
          </a>
          <input class='btn btn-outline-primary' id='fillplace' type='button' value="<?php echo uiTextSnippet('fillplace'); ?>">
        </div>
      </div>
      <input name='usecoords' type='checkbox' value='1' checked> <?php echo uiTextSnippet('usecemcoords'); ?>
      <hr>
      <?php if ($map['key']) { ?>
        <div class='form-group row'>
          <label class='col-form-label col-md-2'><?php echo uiTextSnippet('mapof'); ?></label>
          <div class='col-md-6'>
            <?php echo buildGoogleMapCardHtml($map); ?>
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
            <input class='form-control' id='zoombox' name='zoom' type='text'>
          </div>
        </div>
      <?php } ?>
      <div class='form-group row'>
        <label class='col-form-label col-sm-2' for='notes'><?php echo uiTextSnippet('notes'); ?></label>
          <div class='col-sm-10'>
            <textarea class='form-control' name='notes' rows='4'></textarea>
          </div>
      </div>
      <hr>

      <input class='btn btn-primary' name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
      
      <p>*<?php echo uiTextSnippet('ifmapuploaded'); ?><br>
        **<?php echo uiTextSnippet('requiredmap'); ?></p>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<?php if ($map['key']) { ?>
  <script src='https://maps.googleapis.com/maps/api/js?key=AIzaSyAlWTL2QZDQv9BWXBvCwdAuhq1Lak8jSwU&amp;<?php echo uiTextSnippet('localize'); ?>'></script>
<?php } ?>
<script src='js/selectutils.js'></script>
<script src='js/mediautils.js'></script>
<script src='js/admin.js'></script>
<script src='js/cemeteries.js'></script>
<?php
if ($map['key']) {
  include 'googlemaplib2.php';
}
?>
</body>
</html>
