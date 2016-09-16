<?php

function showCustEvents($id) {
  global $events_table;
  global $eventtypes_table;
  global $allowEdit;
  global $allowDelete;
  global $gotnotes;
  global $gotcites;
  global $mylanguage;
  global $languagesPath;

  echo "<div id='custevents' style='margin-bottom: 5px'>\n";

  $query = "SELECT display, eventdate, eventplace, info, $events_table.eventID AS eventID FROM $events_table, $eventtypes_table WHERE parenttag = \"\" AND persfamID = '$id' AND $events_table.eventtypeID = $eventtypes_table.eventtypeID ORDER BY eventdatetr, ordernum";
  $evresult = tng_query($query);
  $eventcount = tng_num_rows($evresult);

  echo "<table id='custeventstbl' class='table table-sm'";
  if (!$eventcount) {
    echo " style='display: none'";
  }
  echo '>';
  echo "<tr>\n";
  echo '<th>' . uiTextSnippet('action') . "</th>\n";
  echo '<th>' . uiTextSnippet('event') . "</th>\n";
  echo '<th>' . uiTextSnippet('eventdate') . "</th>\n";
  echo '<th>' . uiTextSnippet('eventplace') . "</th>\n";
  echo '<th>' . uiTextSnippet('detail') . "</th>\n";
  echo "</tr>\n";
  echo "<tbody id='custeventstblbody'>\n";

  if ($evresult && $eventcount) {
    while ($event = tng_fetch_assoc($evresult)) {
      $dispvalues = explode('|', $event['display']);
      $numvalues = count($dispvalues);
      if ($numvalues > 1) {
        $displayval = '';
        for ($i = 0; $i < $numvalues; $i += 2) {
          $lang = $dispvalues[$i];
          if ($mylanguage == $languagesPath . $lang) {
            $displayval = $dispvalues[$i + 1];
            break;
          }
        }
      } else {
        $displayval = $event['display'];
      }
      $info = cleanIt($event['info']);
      $truncated = substr($info, 0, 90);
      $info = strlen($info) > 90 ? substr($truncated, 0, strrpos($truncated, ' ')) . '&hellip;' : $info;

      $actionstr = '';
      if ($allowEdit) {
        $actionstr .= "<a href='#' onclick=\"return editEvent({$event['eventID']});\" title='" . uiTextSnippet('edit') . "'>\n";
        $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
        $actionstr .= "</a>\n";
      }
      if ($allowDelete) {
        $actionstr .= "<a href='#' onclick=\"return deleteEvent('{$event['eventID']}');\" title='" . uiTextSnippet('delete') . "'>\n";
        $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
        $actionstr .= "</a>\n";
      }
      if (isset($gotnotes)) {
        $eventId = $event['eventID'];
        $iconColor = $gotnotes[$eventId] ? 'icon-info' : 'icon-muted';
        $actionstr .= "<a class='event-notes' href='#' title='" . uiTextSnippet('notes') . "' data-event-id='$eventId' data-persfam-id='$id'>\n";
        $actionstr .= "<img class='icon-sm icon-right icon-notes $iconColor' data-event-id='$eventId' data-src='svg/documents.svg'>\n";
        $actionstr .= "</a>\n";
      }
      if (isset($gotcites)) {
        $eventId = $event['eventID'];
        $iconColor = $gotcites[$eventId] ? 'icon-info' : 'icon-muted';
        $actionstr .= "<a class='event-citations' href='#' title='" . uiTextSnippet('citations') . "' data-event-id='$eventId' data-persfam-id='$id'>\n";
        $actionstr .= "<img class='icon-sm icon-right icon-citations $iconColor' data-event-id='$eventId' data-src='svg/archive.svg'>\n";
        $actionstr .= "</a>\n";
      }
      echo "<tr id=\"row_{$event['eventID']}\">\n";
      echo "<td>$actionstr</td>\n";
      echo "<td>$displayval</td>\n";
      echo "<td>{$event['eventdate']}</td>\n";
      echo "<td>{$event['eventplace']}</td>\n";
      echo "<td>$info</td>\n";
      echo "</tr>\n";
    }
    tng_free_result($evresult);
  }
  echo "</tbody>\n";
  echo "</table>\n";
  echo "</div>\n";
}