<?php

function buildPlaceMenu($currpage, $entityID) {
  global $allowEdit;
  global $rightbranch;
  global $emailaddr;

  $menu = '';
  if ($allowEdit && $rightbranch) {
    $menu .= "<a id='a0' href='placesEdit.php?ID=" . urlencode($entityID) . "&amp;cw=1' title='" . uiTextSnippet('edit') . "'>\n";
    $menu .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
    $menu .= "</a>\n";
  } elseif ($emailaddr && $currpage != 'suggest') {
    $menu .= "<a id='a0' href='placeSuggest.php?&amp;ID=" . urlencode($entityID) . "' title='" . uiTextSnippet('suggest') . "'>\n";
    $menu .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
    $menu .= "</a>\n";
  }
  return $menu;
}

function processPlaceEvents($prefix, $stdevents, $displaymsgs) {
  global $eventtypes_table;
  global $people_table;
  global $families_table;
  global $offset;
  global $page;
  global $psearch;
  global $maxsearchresults;
  global $psearchns;
  global $urlstring;
  global $events_table;
  global $order;
  global $namesort;
  global $datesort;

  $successcount = 0;
  if ($prefix == 'I') {
    $table = $people_table;
    $peoplejoin1 = $peoplejoin2 = '';
    $idfield = 'personID';
    $idtext = 'personid';
    $namefield = 'lastfirst';
  } elseif ($prefix == 'F') {
    $table = $families_table;
    $peoplejoin1 = " LEFT JOIN $people_table as p1 ON p1.personID = $families_table.husband";
    $peoplejoin2 = " LEFT JOIN $people_table as p2 ON p2.personID = $families_table.wife";
    $idfield = 'familyID';
    $idtext = 'familyid';
    $namefield = 'family';
  }
  $livingPrivateCondition = getLivingPrivateRestrictions($table, false, false);
  $livingPrivateCondition .= ($livingPrivateCondition ? ' AND ' : '');
  $max_browsesearch_pages = 5;
  if ($offset) {
    $offsetplus = $offset + 1;
    $newoffset = "$offset, ";
  } else {
    $offsetplus = 1;
    $newoffset = '';
    $page = 1;
  }
  $tngevents = $stdevents;
  $custevents = [];
  $query = "SELECT tag, eventtypeID, display FROM $eventtypes_table WHERE keep = '1' AND type = '$prefix' ORDER BY display";
  $result = tng_query($query);
  while ($row = tng_fetch_assoc($result)) {
    $eventtypeID = $row['eventtypeID'];
    array_push($tngevents, $eventtypeID);
    array_push($custevents, $eventtypeID);
    $displaymsgs[$eventtypeID] = getEventDisplay($row['display']);
  }
  tng_free_result($result);

  foreach ($tngevents as $tngevent) {
    $eventsjoin = '';
    $allwhere2 = '';
    $placetxt = $displaymsgs[$tngevent];

    if (in_array($tngevent, $custevents)) {
      $eventsjoin = ", $events_table";
      $allwhere2 .= "$table.$idfield = $events_table.persfamID AND eventtypeID = '$tngevent' AND parenttag = '' AND ";
      $tngevent = 'event';
    }
    $datefield = $tngevent . 'date';
    $datefieldtr = $tngevent . 'datetr';
    $place = $tngevent . 'place';
    $allwhere2 .= "$place = '$psearch'";

    if ($prefix == 'F') {
      if ($order == 'name') {
        $orderstr = "p1lastname, p2lastname, $datefieldtr";
      } elseif ($order == 'nameup') {
        $orderstr = "p1lastname DESC, p2lastname DESC, $datefieldtr DESC";
      } elseif ($order == 'date') {
        $orderstr = "$datefieldtr, p1lastname, p2lastname";
      } else {
        $orderstr = "$datefieldtr DESC, p1lastname DESC, p2lastname DESC";
      }
      $query = "SELECT $families_table.ID, familyID, $families_table.living, $families_table.private, $families_table.branch, p1.lastname AS p1lastname, p2.lastname AS p2lastname, $place, $datefield "
          . "FROM ($families_table $eventsjoin) $peoplejoin1 $peoplejoin2 WHERE $livingPrivateCondition $allwhere2 ORDER BY $orderstr LIMIT $newoffset" . $maxsearchresults;
    } elseif ($prefix == 'I') {
      if ($order == 'name') {
        $orderstr = "lastname, firstname, $datefieldtr";
      } elseif ($order == 'nameup') {
        $orderstr = "lastname DESC, firstname DESC, $datefieldtr DESC";
      } elseif ($order == 'date') {
        $orderstr = "$datefieldtr, lastname, firstname";
      } else {
        $orderstr = "$datefieldtr DESC, lastname DESC, firstname DESC";
      }
      $query = "SELECT $people_table.ID, personID, lastname, lnprefix, firstname, living, private, branch, prefix, suffix, nameorder, $place, $datefield "
          . "FROM ($people_table $eventsjoin) WHERE $livingPrivateCondition $allwhere2 ORDER BY $orderstr LIMIT $newoffset" . $maxsearchresults;
    }
    $result = tng_query($query);
    $numrows = tng_num_rows($result);

    //if results, do again w/o pagination to get total
    if ($numrows == $maxsearchresults || $offsetplus > 1) {
      $query = "SELECT count($idfield) AS rcount FROM ($table $eventsjoin) WHERE $livingPrivateCondition $allwhere2";
      $result2 = tng_query($query);
      $countrow = tng_fetch_assoc($result2);
      $totrows = $countrow['rcount'];
    } else {
      $totrows = $numrows;
    }
    if ($numrows) {
      echo "<br>\n";
      echo "<div class='card'>\n";
      echo "<h4 class='card-header'>" . $placetxt . "</h4>\n";
      echo "<br>\n";
      $numrowsplus = $numrows + $offset;
      $successcount++;

      echo '<p>' . uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows</p>";

      $namestr = preg_replace('/xxx/', uiTextSnippet($namefield), $namesort);
      $datestr = preg_replace('/yyy/', $placetxt, $datesort);
      ?>
      <table class="table table-sm table-striped">
        <tr>
          <th></th>
          <th><?php echo $namestr; ?></th>
          <th><?php echo $datestr; ?></th>
        </tr>
        <?php
        $i = $offsetplus;
        while ($row = tng_fetch_assoc($result)) {
          $rights = determineLivingPrivateRights($row);
          $row['allow_living'] = $rights['living'];
          $row['allow_private'] = $rights['private'];
          if ($rights['both']) {
            $placetxt = $row[$place] ? $row[$place] : '';
            $dateval = $row[$datefield];
          } else {
            $dateval = $placetxt = '';
          }
          echo "<tr>\n";

          echo "<td>$i</td>\n";
          $i++;
          echo "<td>\n";
          if ($prefix == 'F') {
            echo "<a href=\"familiesShowFamily.php?familyID={$row['familyID']}\">{$row['p1lastname']} / {$row['p2lastname']}</a>\n";
          } elseif ($prefix == 'I') {
            $name = getNameRev($row);
            echo "<a tabindex='0' class='btn btn-sm btn-outline-primary person-popover' role='button' data-toggle='popover' data-placement='bottom' data-person-id='{$row['personID']}'>$name</a>\n";
          }
          echo '</td>';
          echo '<td>' . displayDate($dateval) . "<br>$placetxt</td>\n";
          echo "</tr>\n";
        }
        tng_free_result($result);
        ?>
      </table>
      <?php
      echo buildSearchResultPagination($totrows, "placesearch.php?$urlstring&amp;psearch=" . urlencode($psearchns) . "&amp;order=$order&amp;offset", $maxsearchresults, $max_browsesearch_pages);
      echo "</div>\n";
    }
  }
  return $successcount;
}

