<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if ($albumID) {
  $query2 = "SELECT entityID FROM $album2entities_table WHERE albumID = '$albumID' AND linktype = '$linktype'";
} else {
  $query2 = "SELECT personID as entityID FROM $medialinks_table WHERE mediaID = '$mediaID' AND linktype = '$linktype'";
}
$result2 = tng_query($query2) or die(uiTextSnippet('cannotexecutequery') . ": $query2");
$alreadygot = [];
while ($row2 = tng_fetch_assoc($result2)) {
  $alreadygot[] = $row2['entityID'];
}
tng_free_result($result2);

function showAction($entityID, $num = null) {
  global $alreadygot;
  global $albumID;
  global $mediaID;

  $id = $num ? $num : $entityID;
  $lines = "<tr id=\"linkrow_$id\"><td>";
  $lines .= "<div id=\"link_$id\" style=\"text-align:center;width:50px;";
  if ($albumID || $mediaID) {
    $gotit = in_array($entityID, $alreadygot);
    if ($gotit) {
      $lines .= "display:none";
    }
    $lines .= "\"><a href='#' onclick=\"return addMedia2EntityLink(findform, '" . urlencode($entityID) . "', '$num');\">" . uiTextSnippet('add') . "</a></div>";
    $lines .= "<div id=\"linked_$id\" style=\"text-align:center;width:50px;";
    if (!$gotit) {
      $lines .= "display:none";
    }
    $lines .= "\"><img class='icon-sm' src='svg/eye.svg' alt=''>\n";
    $lines .= "<div id=\"sdef_" . urlencode($entityID) . "\"></div>";
  } else {
    $lines .= "\"><a href='#' onclick=\"selectEntity(document.find.newlink1, '$id');\">" . uiTextSnippet('select') . "</a>";
  }
  $lines .= "</div>";
  $lines .= "</td>";

  return $lines;
}

function doPeople($firstname, $lastname) {
  global $assignedbranch;
  global $lnprefixes;
  global $maxsearchresults;
  global $people_table;

  $lines = "<tr>\n";
  $lines .= "<td width='50'>" . uiTextSnippet('select') . "</td>\n";
  $lines .= "<td>" . uiTextSnippet('personid') . "</td>\n";
  $lines .= "<td>" . uiTextSnippet('name') . "</td>\n";
  $lines .= "<td>" . uiTextSnippet('birthdate') . "</td>\n";
  $lines .= "<td>" . uiTextSnippet('deathdate') . "</td>\n";
  $lines .= "</tr>\n";

  $allwhere = "1";
  if ($assignedbranch) {
    $allwhere .= " AND branch LIKE \"%$assignedbranch%\"";
  }
  if ($firstname) {
    $allwhere .= " AND firstname LIKE \"%$firstname%\"";
  }
  if ($lastname) {
    if ($lnprefixes) {
      $allwhere .= " AND CONCAT_WS(' ',lnprefix,lastname) LIKE \"%$lastname%\"";
    } else {
      $allwhere .= " AND lastname LIKE \"%$lastname%\"";
    }
  }

  $query = "SELECT personID, lastname, firstname, lnprefix, birthdate, altbirthdate, deathdate, burialdate, prefix, suffix, nameorder FROM $people_table WHERE $allwhere ORDER BY lastname, lnprefix, firstname LIMIT $maxsearchresults";
  $result = tng_query($query);

  while ($row = tng_fetch_assoc($result)) {
    if ($row['birthdate']) {
      $birthdate = uiTextSnippet('birthabbr') . " " . $row['birthdate'];
    } elseif ($row['altbirthdate']) {
      $birthdate = uiTextSnippet('chrabbr') . " " . $row['altbirthdate'];
    } else {
      $birthdate = "";
    }

    if ($row['deathdate']) {
      $deathdate = uiTextSnippet('deathabbr') . " " . $row['deathdate'];
    } elseif ($row['burialdate']) {
      $deathdate = uiTextSnippet('burialabbr') . " " . $row['burial'];
    } else {
      $deathdate = "";
    }

    if (!$birthdate && $deathdate) {
      $birthdate = uiTextSnippet('nobirthinfo');
    }
    $row['allow_living'] = 1;
    $name = getName($row);

    $lines .= showAction($row['personID']);
    $lines .= "<td>" . $row['personID'] . "&nbsp;</td>\n";
    $lines .= "<td>$name&nbsp;</td>\n";
    $lines .= "<td>$birthdate&nbsp;</td>\n";
    $lines .= "<td>$deathdate&nbsp;</td></tr>\n";
  }
  tng_free_result($result);

  return $lines;
}

function doFamilies($husbname, $wifename) {
  global $assignedbranch;
  global $maxsearchresults;
  global $families_table;
  global $people_table;

  $lines = "<tr>\n";
  $lines .= "<td width='50'>" . uiTextSnippet('select') . "</td>\n";
  $lines .= "<td>" . uiTextSnippet('familyid') . "</td>\n";
  $lines .= "<td>" . uiTextSnippet('husbname') . "</td>\n";
  $lines .= "<td>" . uiTextSnippet('wifename') . "</td>\n";
  $lines .= "</tr>\n";

  $allwhere = "1";

  if ($assignedbranch) {
    $allwhere .= " AND $families_table.branch LIKE \"%$assignedbranch%\"";
  }
  $allwhere2 = "";

  if ($wifename) {
    $terms = explode(' ', $wifename);
    foreach ($terms as $term) {
      if ($allwhere2) {
        $allwhere2 .= " AND ";
      }
      $allwhere2 .= "CONCAT_WS(' ',wifepeople.firstname,TRIM(CONCAT_WS(' ',wifepeople.lnprefix,wifepeople.lastname))) LIKE \"%$term%\"";
    }
  }
  if ($husbname) {
    $terms = explode(' ', $husbname);
    foreach ($terms as $term) {
      if ($allwhere2) {
        $allwhere2 .= " AND ";
      }
      $allwhere2 .= "CONCAT_WS(' ',husbpeople.firstname,TRIM(CONCAT_WS(' ',husbpeople.lnprefix,husbpeople.lastname))) LIKE \"%$term%\"";
    }
  }

  if ($allwhere2) {
    $allwhere2 = "AND $allwhere2";
  }

  $joinonwife = "LEFT JOIN $people_table AS wifepeople ON $families_table.wife = wifepeople.personID";
  $joinonhusb = "LEFT JOIN $people_table AS husbpeople ON $families_table.husband = husbpeople.personID";
  $query = "SELECT familyID, wifepeople.personID as wpersonID, wifepeople.firstname as wfirstname, wifepeople.lnprefix as wlnprefix, wifepeople.lastname as wlastname, wifepeople.prefix as wprefix, wifepeople.suffix as wsuffix, wifepeople.nameorder as wnameorder, husbpeople.personID as hpersonID, husbpeople.firstname as hfirstname, husbpeople.lnprefix as hlnprefix, husbpeople.lastname as hlastname, husbpeople.prefix as hprefix, husbpeople.suffix as hsuffix, husbpeople.nameorder as hnameorder FROM $families_table $joinonwife $joinonhusb WHERE $allwhere $allwhere2 ORDER BY hlastname, hlnprefix, hfirstname LIMIT $maxsearchresults";
  $result = tng_query($query);

  while ($row = tng_fetch_assoc($result)) {
    $thishusb = $thiswife = "";
    $person['allow_living'] = 1;
    if ($row['hpersonID']) {
      $person['firstname'] = $row['hfirstname'];
      $person['lnprefix'] = $row['hlnprefix'];
      $person['lastname'] = $row['hlastname'];
      $person['prefix'] = $row['hprefix'];
      $person['suffix'] = $row['hsuffix'];
      $person['nameorder'] = $row['hnameorder'];
      $thishusb .= getName($person);
    }
    if ($row['wpersonID']) {
      if ($thisfamily) {
        $thisfamily .= "<br>";
      }
      $person['firstname'] = $row['wfirstname'];
      $person['lnprefix'] = $row['wlnprefix'];
      $person['lastname'] = $row['wlastname'];
      $person['prefix'] = $row['wprefix'];
      $person['suffix'] = $row['wsuffix'];
      $person['nameorder'] = $row['wnameorder'];
      $thiswife = getName($person);
    }
    $lines .= showAction($row['familyID']);
    $lines .= "<td>" . $row['familyID'] . "&nbsp;</td>\n";
    $lines .= "<td>$thishusb&nbsp;</td>\n";
    $lines .= "<td>$thiswife&nbsp;</td></tr>\n";
  }
  tng_free_result($result);

  return $lines;
}

function doSources($title) {
  global $sources_table;
  global $maxsearchresults;

  $lines = "<tr>\n";
  $lines .= "<td width='50'>" . uiTextSnippet('select') . "</td>\n";
  $lines .= "<td>" . uiTextSnippet('sourceid') . "</td>\n";
  $lines .= "<td>" . uiTextSnippet('title') . "</td>\n";
  $lines .= "</tr>\n";

  $query = "SELECT sourceID, title FROM $sources_table WHERE title LIKE \"%$title%\" ORDER BY title LIMIT $maxsearchresults";
  $result = tng_query($query);

  while ($row = tng_fetch_assoc($result)) {
    $lines .= showAction($row['sourceID']);
    $lines .= "<td>" . $row['sourceID'] . "&nbsp;</td>\n";
    $lines .= "<td>" . $row['title'] . "&nbsp;</td></tr>\n";
  }
  tng_free_result($result);

  return $lines;
}

function doRepos($title) {
  global $repositories_table;
  global $maxsearchresults;

  $lines = "<tr>\n";
  $lines .= "<td width='50'>" . uiTextSnippet('select') . "</td>\n";
  $lines .= "<td>" . uiTextSnippet('repoid') . "</td>\n";
  $lines .= "<td>" . uiTextSnippet('title') . "</td>\n";
  $lines .= "</tr>\n";

  $query = "SELECT repoID, reponame FROM $repositories_table WHERE reponame LIKE \"%$title%\" ORDER BY reponame LIMIT $maxsearchresults";
  $result = tng_query($query);

  while ($row = tng_fetch_assoc($result)) {
    $lines .= showAction($row['repoID']);
    $lines .= "<td>" . $row['repoID'] . "&nbsp;</td>\n";
    $lines .= "<td>" . $row['reponame'] . "&nbsp;</td></tr>\n";
  }
  tng_free_result($result);

  return $lines;
}

function doPlaces($place) {
  global $maxsearchresults;
  global $places_table;

  $lines = "<tr>\n";
  $lines .= "<td width='50'>" . uiTextSnippet('select') . "</td>\n";
  $lines .= "<td>" . uiTextSnippet('place') . "</td>\n";
  $lines .= "</tr>\n";

  $allwhere = "1";
  if ($place) {
    $allwhere .= " AND place LIKE \"%$place%\"";
  }
  $query = "SELECT ID, place FROM $places_table WHERE $allwhere ORDER BY place LIMIT $maxsearchresults";
  $result = tng_query($query);

  $num = 1;
  while ($row = tng_fetch_assoc($result)) {
    $lines .= showAction($row['place'], $num);
    $lines .= "<td>" . $row['place'] . "&nbsp;</td></tr>\n";
    $num++;
  }
  tng_free_result($result);

  return $lines;
}

$lines = "";
switch ($linktype) {
  case 'I':
    if ($session_charset != "UTF-8") {
      $firstname = tng_utf8_decode($firstname);
      $lastname = tng_utf8_decode($lastname);
    }
    $lines = doPeople($firstname, $lastname);
    break;
  case 'F':
    if ($session_charset != "UTF-8") {
      $husbname = tng_utf8_decode($husbname);
      $wifename = tng_utf8_decode($wifename);
    }
    $lines = doFamilies($husbname, $wifename);
    break;
  case 'S':
    if ($session_charset != "UTF-8") {
      $title = tng_utf8_decode($title);
    }
    $lines = doSources($title);
    break;
  case 'R':
    if ($session_charset != "UTF-8") {
      $title = tng_utf8_decode($title);
    }
    $lines = doRepos($title);
    break;
  case 'L':
    if ($session_charset != "UTF-8") {
      $place = tng_utf8_decode($place);
    }
    $lines = doPlaces($place);
    break;
}

header("Content-type:text/html; charset=" . $session_charset);
echo "<table cellpadding=\"3\" cellspacing=\"1\" width=\"585\">\n$lines\n</table>\n";
