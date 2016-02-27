<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

$helplang = findhelp("events_help.php");

header("Content-type:text/html; charset=" . $session_charset);
?>
<form id='form1' name='form1' action='' method='post' onSubmit="return addEvent(this);">
  <header class='modal-header'>
    <h4><?php echo uiTextSnippet('addnewevent'); ?></h4>
    <p>
      <a href='#' onclick="return openHelp('<?php echo $helplang; ?>/events_help.php');"><?php echo uiTextSnippet('help'); ?></a>
    </p>
    <?php if ($message) { ?>
      <span class='red'><em><?php echo urldecode($message); ?></em></span>
    <?php } ?>
  </header>
  <div class='modal-body'>
    <table class='table table-sm'>
      <tr>
        <td><span><?php echo uiTextSnippet('eventtype'); ?>:</span></td>
        <td>
          <span>
            <select name="eventtypeID">
              <option value=''></option>
              <?php
              $query = "SELECT * FROM $eventtypes_table WHERE type = \"$prefix\" ORDER BY tag";
              $evresult = tng_query($query);

              $events = array();
              while ($eventtype = tng_fetch_assoc($evresult)) {
                $display = getEventDisplay($eventtype['display']);
                $option = $display . ($eventtype['tag'] != "EVEN" ? " ({$eventtype['tag']})" : "");
                $optionlen = strlen($option);
                $option = substr($option, 0, 40);
                if ($optionlen > strlen($option)) {
                  $option .= "&hellip;";
                }
                $events[$display] = "<option value=\"{$eventtype['eventtypeID']}\">$option</option>\n";
              }
              tng_free_result($evresult);

              ksort($events);
              foreach ($events as $event) {
                echo $event;
              }
              ?>
            </select>
          </span>
        </td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('eventdate'); ?>:</td>
        <td><input name='eventdate' type='text' onBlur="checkDate(this);">
          <span><?php echo uiTextSnippet('dateformat'); ?>:</span></td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('eventplace'); ?>:</td>
        <td><input id='eventplace' name='eventplace' type='text' size='40'>
          &nbsp;<?php echo uiTextSnippet('text_or'); ?>&nbsp;
          <a href='#' onclick="return openFindPlaceForm('eventplace');" title="<?php echo uiTextSnippet('find'); ?>">
            <img class='icon-sm-inline' src='svg/magnifying-glass.svg' alt="<?php echo uiTextSnippet('find'); ?>">
          </a>
        </td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('detail'); ?>:</td>
        <td><textarea name="info" rows="4" cols="40"></textarea></td>
      </tr>
    </table>
    <?php echo displayToggle("plus9", 0, "more", uiTextSnippet('more'), ""); ?>
    <div id='more' style='display: none'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('age'); ?>:</td>
          <td><input name='age' type='text' size='12' maxlength='12'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('agency'); ?>:</td>
          <td><input name='agency' type='text' size='40'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('cause'); ?>:</td>
          <td><input name='cause' type='text' size='40'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('address1'); ?>:</td>
          <td><input name='address1' type='text' size='40'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('address2'); ?>:</td>
          <td><input name='address2' type='text' size='40'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('city'); ?>:</td>
          <td><input name='city' type='text' size='40'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('stateprov'); ?>:</td>
          <td><input name='state' type='text' size='40'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('zip'); ?>:</td>
          <td><input name='zip' type='text'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('countryaddr'); ?>:</td>
          <td><input name='country' type='text' size='40'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('phone'); ?>:</td>
          <td><input name='phone' type='text' size='30'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('email'); ?>:</td>
          <td><input name='email' type='text' size='50'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('website'); ?>:</td>
          <td><input name='www' type='text' size='50'></td>
        </tr>
      </table>
    </div>
  </div> <!-- .modal-body -->
  <footer class='modal-footer'>
    <input name='persfamID' type='hidden' value="<?php echo $persfamID; ?>">
    <input name='tree' type='hidden' value="<?php echo $tree; ?>">
    <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    <input name='cancel' type='button' value="<?php echo uiTextSnippet('cancel'); ?>" onclick="tnglitbox.remove();">
  </footer>
</form>
