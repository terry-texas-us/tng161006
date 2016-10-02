<?php
/**
 * Name history: admin_editevent.php
 */
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

$query = "SELECT display, events.eventtypeID AS eventtypeID, eventdate, eventplace, age, agency, cause, events.addressID, address1, address2, city, state, zip, country, info, phone, email, www FROM (events, eventtypes) LEFT JOIN addresses ON events.addressID = addresses.addressID WHERE eventID = '$eventID' AND events.eventtypeID = eventtypes.eventtypeID";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['eventplace'] = preg_replace('/\"/', '&#34;', $row['eventplace']);
$row['info'] = preg_replace('/\"/', '&#34;', $row['info']);
$row['age'] = preg_replace('/\"/', '&#34;', $row['age']);
$row['agency'] = preg_replace('/\"/', '&#34;', $row['agency']);
$row['cause'] = preg_replace('/\"/', '&#34;', $row['cause']);
$row['address1'] = preg_replace('/\"/', '&#34;', $row['address1']);
$row['address1'] = preg_replace('/\"/', '&#34;', $row['address1']);
$row['city'] = preg_replace('/\"/', '&#34;', $row['city']);
$row['state'] = preg_replace('/\"/', '&#34;', $row['state']);
$row['zip'] = preg_replace('/\"/', '&#34;', $row['zip']);
$row['country'] = preg_replace('/\"/', '&#34;', $row['country']);

$display = getEventDisplay($row['display']);

$helplang = findhelp('events_help.php');

header('Content-type:text/html; charset=' . $sessionCharset);
?>
<form id='form1' name='form1' action='' method='post' onSubmit="return updateEvent(this);">
  <header class='modal-header'>
    <h5><?php echo uiTextSnippet('modifyevent'); ?></h5>
    <a href='#' onclick="return openHelp('<?php echo $helplang; ?>/events_help.php');"><?php echo uiTextSnippet('help'); ?></a>
  </header>
  <div class='modal-body'>
    <table class='table table-sm'>
      <tr>
        <td><?php echo uiTextSnippet('eventtype'); ?>:</td>
        <td><?php echo "{$row['tag']} $display"; ?></td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('eventdate'); ?>:</td>
        <td>
          <input name='eventdate' type='text' value="<?php echo $row['eventdate']; ?>"
                 onBlur="checkDate(this);" /> <span><?php echo uiTextSnippet('dateformat'); ?>:</span>
        </td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('eventplace'); ?>:</td>
        <td>
          <input id='eventplace' name='eventplace' type='text' size='40' value="<?php echo $row['eventplace']; ?>">
          &nbsp;<?php echo uiTextSnippet('or'); ?>&nbsp;
          <a href="#" onclick="return openFindPlaceForm('eventplace');" title="<?php echo uiTextSnippet('find'); ?>">
            <img class='icon-sm-inline' src='svg/magnifying-glass.svg' alt="<?php echo uiTextSnippet('find'); ?>">
          </a>
        </td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('detail'); ?>:</td>
        <td><textarea class='form-control' name='info' rows='4'><?php echo $row['info']; ?></textarea>
        </td>
      </tr>
    </table>
    <?php echo displayToggle('plus9', 0, 'more', uiTextSnippet('more'), ''); ?>
    <div id='more' style='display: none'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('age'); ?>:</td>
          <td><input name='age' type='text' size='12' maxlength='12' value="<?php echo $row['age']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('agency'); ?>:</td>
          <td><input name='agency' type='text' size='40' value="<?php echo $row['agency']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('cause'); ?>:</td>
          <td><input name='cause' type='text' size='40' value="<?php echo $row['cause']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('address1'); ?>:</td>
          <td><input name='address1' type='text' size='40' value="<?php echo $row['address1']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('address2'); ?>:</td>
          <td><input name='address2' type='text' size='40' value="<?php echo $row['address2']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('city'); ?>:</td>
          <td><input name='city' type='text' size='40' value="<?php echo $row['city']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('stateprov'); ?>:</td>
          <td><input name='state' type='text' size='40' value="<?php echo $row['state']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('zip'); ?>:</td>
          <td><input name='zip' type='text' value="<?php echo $row['zip']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('countryaddr'); ?>:</td>
          <td><input name='country' type='text' size='40' value="<?php echo $row['country']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('phone'); ?>:</td>
          <td><input name='phone' type='text' size='30' value="<?php echo $row['phone']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('email'); ?>:</td>
          <td><input name='email' type='text' size='50' value="<?php echo $row['email']; ?>"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('website'); ?>:</td>
          <td><input name='www' type='text' size='50' value="<?php echo $row['www']; ?>"></td>
        </tr>
      </table>
      <br>
    </div>
  </div>
  <footer class='modal-footer'>
    <input name='addressID' type='hidden' value="<?php echo $row['addressID']; ?>">
    <input name='eventID' type='hidden' value="<?php echo $eventID; ?>">
    <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    <input name='cancel' type='button' value="<?php echo uiTextSnippet('cancel'); ?>" onclick="tnglitbox.remove();">
  </footer>
</form>
