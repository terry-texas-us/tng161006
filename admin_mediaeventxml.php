<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

function doEvent($eventID, $displayval, $info) {
  echo "<event>\n";
  echo "<eventID>$eventID</eventID>\n";
  echo '<display>' . xmlcharacters($displayval) . "</display>\n";
  if ($info) {
    echo '<info>' . xmlcharacters("($info)") . "</info>\n";
  } else {
    echo "<info>-1</info>\n";
  }
  echo "</event>\n";
}

header('Content-Type: application/xml');
echo '<?xml version="1.0"';
if ($session_charset) {
  echo " encoding=\"$session_charset\"";
}
echo "?>\n";
echo "<eventlist>\n";

//write out the number for the list to be filled
echo "<targetlist>\n";
echo "<target>$count</target>\n";
echo "</targetlist>\n";

if ($linktype == 'I') {
  //standard people events
  $list = ['NAME', 'BIRT', 'CHR', 'DEAT', 'BURI'];
  foreach ($list as $eventtype) {
    doEvent($eventtype, uiTextSnippet($eventtype), '');
  }
  if ($allow_lds) {
    $ldslist = ['BAPL', 'CONL', 'INIT', 'ENDL', 'SLGC'];
    foreach ($ldslist as $eventtype) {
      doEvent($eventtype, uiTextSnippet($eventtype), '');
    }
  }
} elseif ($linktype == 'F') {
  //standard family events
  $list = ['MARR', 'DIV'];
  foreach ($list as $eventtype) {
    doEvent($eventtype, uiTextSnippet($eventtype), '');
  }
  if ($allow_lds) {
    $ldslist = ['SLGS'];
    foreach ($ldslist as $eventtype) {
      doEvent($eventtype, uiTextSnippet($eventtype), '');
    }
  }
}

//now call up custom events linked to passed in entity
$query = "SELECT display, eventdate, eventplace, info, eventID FROM $events_table, $eventtypes_table WHERE persfamID = \"$persfamID\" AND $events_table.eventtypeID = $eventtypes_table.eventtypeID AND keep = \"1\" AND parenttag = \"\" ORDER BY ordernum, tag, description, eventdatetr, info, eventID";
$custevents = tng_query($query);
while ($custevent = tng_fetch_assoc($custevents)) {
  $displayval = getEventDisplay($custevent['display']);
  if ($custevent['eventdate']) {
    $info = displayDate($custevent['eventdate']);
  } elseif ($custevent['eventplace']) {
    $info = $custevent['eventplace'];
  } elseif ($custevent['info']) {
    $info = substr($custevent['info'], 0, 20) . '...';
  }
  doEvent($custevent['eventID'], $displayval, $info);
}
tng_free_result($custevents);

echo '</eventlist>';
