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
header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('addnewplace'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
<body<?php echo ((bool) $map['key'] === true && (bool) $map['startoff'] === false) ? " onload=\"divbox('mapcontainer');\"" : ""; ?>>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('places-addnewplace', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'placesBrowse.php', uiTextSnippet('browse'), 'findplace']);
    $navList->appendItem([$allowAdd, 'placesAdd.php', uiTextSnippet('add'), 'addplace']);
    $navList->appendItem([$allowEdit && $allowDelete, 'placesMerge.php', uiTextSnippet('merge'), 'merge']);
    $navList->appendItem([$allowEdit, 'admin_geocodeform.php', uiTextSnippet('geocode'), 'geo']);
    echo $navList->build('addplace');
    ?>
    <form name='form1' action='placesAddFormAction.php' method='post' onSubmit="return validateForm();">
      <table class='table table-sm'>
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
              <div>
                <?php echo buildGoogleMapCardHtml($map); ?>
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
            <textarea class='form-control' name='notes' rows='5'></textarea>
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
    <script src='https://maps.googleapis.com/maps/api/js?key=AIzaSyAlWTL2QZDQv9BWXBvCwdAuhq1Lak8jSwU&amp;<?php echo uiTextSnippet('localize'); ?>'></script>
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
    include 'googlemaplib2.php';
  }
  ?>
</body>
</html>
