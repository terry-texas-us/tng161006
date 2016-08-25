<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if ($type == "album") {
  $query = "SELECT eventID, linktype, entityID FROM $album2entities_table WHERE alinkID = \"$linkID\"";
} else {
  $query = "SELECT eventID, altdescription, altnotes, defphoto, linktype, personID, dontshow FROM $medialinks_table WHERE medialinkID = \"$linkID\"";
}
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$meventID = $row['eventID'];
$entityID = $type == "album" ? $row['entityID'] : $row['personID'];

$ldsOK = determineLDSRights();

function doEvent($eventID, $displayval, $info) {
  global $meventID;
  return "<option value=\"$eventID\"" . ($eventID == $meventID ? " selected" : "") . ">$displayval" . ($info ? ": $info" : "") . "</option>\n";
}

$options = "<option value=''>" . uiTextSnippet('none') . "</option>";
if ($row['linktype'] == 'I') {
  //standard people events
  $list = ["NAME", "BIRT", "CHR", "DEAT", "BURI"];
  foreach ($list as $eventtype) {
    $options .= doEvent($eventtype, uiTextSnippet($eventtype), '');
  }
  if ($ldsOK) {
    $ldslist = ["BAPL", "CONL", "INIT", "ENDL", "SLGC"];
    foreach ($ldslist as $eventtype) {
      $options .= doEvent($eventtype, uiTextSnippet($eventtype), '');
    }
  }
} elseif ($row['linktype'] == 'F') {
  //standard family events
  $list = ["MARR", "DIV"];
  foreach ($list as $eventtype) {
    $options .= doEvent($eventtype, uiTextSnippet($eventtype), '');
  }
  if ($ldsOK) {
    $ldslist = ["SLGS"];
    foreach ($ldslist as $eventtype) {
      $options .= doEvent($eventtype, uiTextSnippet($eventtype), '');
    }
  }
}

//now call up custom events linked to passed in entity
$query = "SELECT display, eventdate, eventplace, info, eventID FROM $events_table, $eventtypes_table WHERE persfamID = \"$entityID\" AND $events_table.eventtypeID = $eventtypes_table.eventtypeID AND keep = \"1\" AND parenttag = \"\" ORDER BY ordernum, tag, description, eventdatetr, info, eventID";
$custevents = tng_query($query);
while ($custevent = tng_fetch_assoc($custevents)) {
  $displayval = getEventDisplay($custevent['display']);
  $info = "";
  if ($custevent['eventdate']) {
    $info = displayDate($custevent['eventdate']);
  } elseif ($custevent['eventplace']) {
    $info = truncateIt($custevent['eventplace'], 20);
  } elseif ($custevent['info']) {
    $info = truncateIt($custevent['info'], 20);
  }
  $options .= doEvent($custevent['eventID'], $displayval, $info);
}
tng_free_result($custevents);

header("Content-type:text/html; charset=" . $session_charset);
?>
<form id='editlinkform' name='editlinkform' action='' method='post' onsubmit="return updateMedia2EntityLink(this);">
  <header class='modal-header'>
    <h4><?php echo $headline; ?></h4>
  </header>
  <div class='modal-body'>
    <table class='table table-sm'>
      <tr>
        <td><?php echo uiTextSnippet('event'); ?>:</td>
        <td>
          <select id='eventID' name='eventID'>
            <?php echo $options; ?>
          </select>
        </td>
      </tr>
      <?php if ($type != "album") { ?>
        <tr>
          <td><?php echo uiTextSnippet('alttitle'); ?>:</td>
          <td>
            <textarea name='altdescription' rows='3' cols='40'><?php echo $row['altdescription']; ?></textarea>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('altdesc'); ?>:</td>
          <td>
            <textarea name='altnotes' rows='4' cols='40'><?php echo $row['altnotes']; ?></textarea>
          </td>
        </tr>
        <tr>
          <td colspan='2'>
            <input name='defphoto' type='checkbox' value='1'<?php if ($row['defphoto']) {echo " checked";} ?>> <?php echo uiTextSnippet('makedefault'); ?>
            *
            <input name='show' type='checkbox' value='1'<?php if (!$row['dontshow']) {echo " checked";} ?>> <?php echo uiTextSnippet('show'); ?>
          </td>
        </tr>
      <?php } ?>
    </table>
    <br>
    <?php if ($type != 'album') { ?>
      <input name='personID' type='hidden' value="<?php echo $entityID; ?>">
    <?php } ?>
    <p>
      <?php
      if ($type != 'album') {
        echo "*" . uiTextSnippet('defphotonote') . "\n";
      }
      ?>
    </p>
  </div> <!-- .modal-body -->
  <footer class='modal-footer'>
    <input name='linkID' type='hidden' value="<?php echo $linkID; ?>">
    <input name='type' type='hidden' value="<?php echo $type; ?>">
    <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    <input name='cancel' type='button' value="<?php echo uiTextSnippet('cancel'); ?>" onclick="tnglitbox.remove();">
  </footer>
</form>