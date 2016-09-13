<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

$query = "SELECT eventID, age, agency, cause, $events_table.addressID, address1, address2, city, state, zip, country, info, phone, email, www FROM $events_table "
    . "LEFT JOIN $address_table ON $events_table.addressID = $address_table.addressID "
    . "WHERE parenttag = '$eventID' AND $events_table.persfamID = '$persfamID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

$row['age'] = preg_replace('/\"/', '&#34;', $row['age']);
$row['agency'] = preg_replace('/\"/', '&#34;', $row['agency']);
$row['cause'] = preg_replace('/\"/', '&#34;', $row['cause']);
$row['address1'] = preg_replace('/\"/', '&#34;', $row['address1']);
$row['address2'] = preg_replace('/\"/', '&#34;', $row['address2']);
$row['city'] = preg_replace('/\"/', '&#34;', $row['city']);
$row['state'] = preg_replace('/\"/', '&#34;', $row['state']);
$row['zip'] = preg_replace('/\"/', '&#34;', $row['zip']);
$row['country'] = preg_replace('/\"/', '&#34;', $row['country']);

$helplang = findhelp('more_help.php');

header('Content-type:text/html; charset=' . $session_charset);
?>
<div id='more'>
  <form name='editmoreform' action='' onsubmit="return updateMore(this);">
    <header class='modal-header'>
      <button id='modalHelpControl' class='help' onclick="return openHelp('<?php echo $helplang; ?>/more_help.php');" title='<?php echo uiTextSnippet('help'); ?>'>
        <span aria-hidden='true'>?</span>
      </button>
      <h4><?php echo uiTextSnippet('moreinfo') . ': ' . uiTextSnippet($eventID); ?></h4>
    </header>
    <div class='container'>
    <div class='modal-body'>
      <div class='row'>
        <label class='col-xs-3' for='age'><?php echo uiTextSnippet('age'); ?>:</label>
        <div class='col-xs-9'><input class='form-control' name='age' type='text' maxlength='12' value="<?php echo $row['age']; ?>"></div>
      </div>
      <div class='row'>
        <label class='col-xs-3' for='agency'><?php echo uiTextSnippet('agency'); ?>:</label>
        <div class='col-xs-9'><input class='form-control' name='agency' type='text' value="<?php echo $row['agency']; ?>"></div>
      </div>
      <div class='row'>
        <label class='col-xs-3' for='cause'><?php echo uiTextSnippet('cause'); ?>:</label>
        <div class='col-xs-9'><input class='form-control' name='cause' type='text' value="<?php echo $row['cause']; ?>"></div>
      </div>
      <div class='row'>
        <label class='col-xs-3' for='address1'><?php echo uiTextSnippet('address1'); ?>:</label>
        <div class='col-xs-9'><input class='form-control' name='address1' type='text' value="<?php echo $row['address1']; ?>"></div>
      </div>
      <div class='row'>
        <label class='col-xs-3' for='address2'><?php echo uiTextSnippet('address2'); ?>:</label>
        <div class='col-xs-9'><input class='form-control' name='address2' type='text' value="<?php echo $row['address2']; ?>"></div>
      </div>
      <div class='row'>
        <label class='col-xs-3' for='city'><?php echo uiTextSnippet('city'); ?>:</label>
        <div class='col-xs-9'><input class='form-control' name='city' type='text' value="<?php echo $row['city']; ?>"></div>
      </div>
      <div class='row'>
        <label class='col-xs-3' for='stateprov'><?php echo uiTextSnippet('stateprov'); ?>:</label>
        <div class='col-xs-9'><input class='form-control' name='state' type='text' value="<?php echo $row['state']; ?>"></div>
      </div>
      <div class='row'>
        <label class='col-xs-3' for='zip'><?php echo uiTextSnippet('zip'); ?>:</label>
        <div class='col-xs-9'><input class='form-control' name='zip' type='text' value="<?php echo $row['zip']; ?>"></div>
      </div>
      <div class='row'>
        <label class='col-xs-3' for='countryaddr'><?php echo uiTextSnippet('countryaddr'); ?>:</label>
        <div class='col-xs-9'><input class='form-control' name='country' type='text' value="<?php echo $row['country']; ?>"></div>
      </div>
      <div class='row'>
        <label class='col-xs-3' for='phone'><?php echo uiTextSnippet('phone'); ?>:</label>
        <div class='col-xs-9'><input class='form-control' name='phone' type='tel' value="<?php echo $row['phone']; ?>"></div>
      </div>
      <div class='row'>
        <label class='col-xs-3' for='email'><?php echo uiTextSnippet('email'); ?>:</label>
        <div class='col-xs-9'><input class='form-control' name='email' type='email' value="<?php echo $row['email']; ?>"></div>
      </div>
      <div class='row'>
        <label class='col-xs-3' for='website'><?php echo uiTextSnippet('website'); ?>:</label>
        <div class='col-xs-9'><input class='form-control' name='www' type='url' value="<?php echo $row['www']; ?>"></div>
      </div>
    </div> <!-- .modal-body -->
    </div>
    <footer class='modal-footer'>
      <div style='float: right'>
        <button class='btn btn-primary' name='submit' type='submit'><?php echo uiTextSnippet('save'); ?></button>
      </div>
      <input name='eventtypeID' type='hidden' value="<?php echo $eventID; ?>">
      <input name='addressID' type='hidden' value="<?php echo $row['addressID']; ?>">
      <input name='eventID' type='hidden' value="<?php echo $row['eventID']; ?>">
      <input name='persfamID' type='hidden' value="<?php echo $persfamID; ?>">
    </footer>
  </form>
</div>