<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';
require 'prefixes.php';

if ($sessionCharset != 'UTF-8') {
  $criteria = tng_utf8_decode($criteria);
  $myffirstname = tng_utf8_decode($myffirstname);
  $myflastname = tng_utf8_decode($myflastname);
  $myhusbname = tng_utf8_decode($myhusbname);
  $mywifename = tng_utf8_decode($mywifename);
}

$criteria = trim($criteria);
$f = $filter == 'c' ? '%' : '';

header('Content-type:text/html; charset=' . $sessionCharset);

$mediaquery = '';
if ($albumID) {
  $mediaquery = "SELECT entityID FROM albumplinks WHERE albumID = '$albumID' AND linktype = '$type'";
} else {
  if ($mediaID) {
    $mediaquery = "SELECT personID AS entityID FROM medialinks WHERE mediaID = '$mediaID' AND linktype = '$type'";
  }
}

if ($mediaquery) {
  $result2 = tng_query($mediaquery) or die(uiTextSnippet('cannotexecutequery') . ": $mediaquery");
  $alreadygot = [];
  while ($row2 = tng_fetch_assoc($result2)) {
    $alreadygot[] = $row2['entityID'];
  }
  tng_free_result($result2);
}

function showAction($entityID, $num = null) {
  global $alreadygot;
  global $albumID;
  global $mediaID;

  $id = $num ? $num : $entityID;
  $lines = '<td>';
  $lines .= "<div id=\"link_$id\" style=\"text-align: center; width:50px;";
  if ($albumID || $mediaID) {
    $gotit = in_array($entityID, $alreadygot);
    if ($gotit) {
      $lines .= 'display: none';
    }
    $lines .= "\"><a href='#' onclick=\"return addMedia2EntityLink(findform, '" . str_replace('&#39;', "\\'", $entityID) . "', '$num');\">" . uiTextSnippet('add') . '</a></div>';
    $lines .= "<div id=\"linked_$id\" style=\"text-align: center; width:50px;";
    if (!$gotit) {
      $lines .= 'display: none';
    }
    $lines .= "\"><img class='icon-sm' src='svg/eye.svg' alt=''>\n";
    $lines .= '<div id="sdef_' . urlencode($entityID) . '"></div>';
  } else {
    $lines .= "\"><a href='#' onclick=\"selectEntity(document.find.newlink1, '$id');\">" . uiTextSnippet('select') . '</a>';
  }
  $lines .= '</div>';
  $lines .= "</td>\n";

  return $lines;
}

$selectline = $mediaID || $albumID ? '<td width="50">' . uiTextSnippet('select') . "</td>\n" : '';

switch ($type) {
  case 'I':
    $myffirstname = trim($myffirstname);
    $myflastname = trim($myflastname);
    $myfpersonID = trim($myfpersonID);
    $allwhere = '1';
    if ($branch) {
      $allwhere .= " AND branch LIKE \"%$branch%\"";
    }
    if ($myfpersonID) {
      $myfpersonID = strtoupper($myfpersonID);
      if ($f != '%' && substr($myfpersonID, 0, 1) != $personprefix) {
        $myfpersonID = $personprefix . $myfpersonID;
      }
      $allwhere .= " AND personID LIKE \"$f$myfpersonID%\"";
    }
    if ($myffirstname) {
      $allwhere .= " AND firstname LIKE \"$f" . trim($myffirstname) . '%"';
    }
    if ($myflastname) {
      if ($lnprefixes) {
        $allwhere .= " AND TRIM(CONCAT_WS(' ',lnprefix,lastname)) LIKE \"$f" . trim($myflastname) . '%"';
      } else {
        $allwhere .= " AND lastname LIKE \"$f" . trim($myflastname) . '%"';
      }
    }

    $livingPrivateCondition = getLivingPrivateRestrictions('', $myffirstname, $false);

    if ($livingPrivateCondition) {
      if ($allwhere) {
        $allwhere = "($allwhere) AND ";
      }
      $allwhere .= $livingPrivateCondition;
    }

    $query = "SELECT personID, lastname, firstname, lnprefix, birthdate, altbirthdate, deathdate, burialdate, prefix, suffix, nameorder, living, private, branch FROM people WHERE $allwhere ORDER BY lastname, lnprefix, firstname LIMIT 250";
    $result = tng_query($query);

    if (tng_num_rows($result)) {
      $lines .= "<tr>\n";
      $lines .= $selectline;
      $lines .= '<td>' . uiTextSnippet('personid') . "</td>\n";
      $lines .= '<td>' . uiTextSnippet('name') . "</td>\n";
      $lines .= '<td>' . uiTextSnippet('birthdate') . "</td>\n";
      $lines .= '<td>' . uiTextSnippet('deathdate') . "</td>\n";
      $lines .= "</tr>\n";

      while ($row = tng_fetch_assoc($result)) {
        $birthdate = $deathdate = '';
        $rights = determineLivingPrivateRights($row);
        $row['allow_living'] = $rights['living'];
        $row['allow_private'] = $rights['private'];

        if ($rights['both']) {
          if ($row['birthdate']) {
            $birthdate = uiTextSnippet('birthabbr') . ' ' . displayDate($row['birthdate']);
          } else {
            if ($row['altbirthdate']) {
              $birthdate = uiTextSnippet('chrabbr') . ' ' . displayDate($row['altbirthdate']);
            }
          }
          if ($row['deathdate']) {
            $deathdate = uiTextSnippet('deathabbr') . ' ' . displayDate($row['deathdate']);
          } else {
            if ($row['burialdate']) {
              $deathdate = uiTextSnippet('burialabbr') . ' ' . displayDate($row['burialdate']);
            }
          }
          if (!$birthdate && $deathdate) {
            $birthdate = uiTextSnippet('nobirthinfo');
          }
        }
        $namestr = getName($row);

        $lines .= "<tr id=\"linkrow_{$row['personID']}\">\n";
        if ($mediaquery) {
          $lines .= showAction($row['personID']);
        }
        $lines .= "<td>{$row['personID']}&nbsp;</td>\n";
        $lines .= "<td><a href='#' onclick=\"return retItem('{$row['personID']}');\" id=\"item_{$row['personID']}\">$namestr</a>&nbsp;</td>\n";
        $lines .= "<td><span id=\"birth_{$row['personID']}\">$birthdate</span>&nbsp;</td>\n";
        $lines .= "<td>$deathdate&nbsp;</td>\n</tr>\n";
      }
    }
    break;
  case 'F':
    $myhusbname = trim($myhusbname);
    $mywifename = trim($mywifename);
    $myfamilyID = trim($myfamilyID);
    $allwhere = '1';
    if ($branch) {
      $allwhere .= " AND families.branch LIKE \"%$branch%\"";
    }
    if ($myfamilyID) {
      $myfamilyID = strtoupper($myfamilyID);
      if ($f != '%' && substr($myfamilyID, 0, 1) != $familyprefix) {
        $myfamilyID = $familyprefix . $myfamilyID;
      }
      $allwhere .= " AND familyID LIKE \"%$myfamilyID%\"";
    }
    $joinon = '';
    if ($assignedbranch) {
      $allwhere .= " AND families.branch LIKE \"%$assignedbranch%\"";
    }

    $allwhere2 = '';

    if ($mywifename) {
      $terms = explode(' ', $mywifename);
      foreach ($terms as $term) {
        if ($allwhere2) {
          $allwhere2 .= ' AND ';
        }
        $allwhere2 .= "CONCAT_WS(' ',wifepeople.firstname,TRIM(CONCAT_WS(' ',wifepeople.lnprefix,wifepeople.lastname))) LIKE \"$f$term%\"";
      }
    }

    if ($myhusbname) {
      $terms = explode(' ', $myhusbname);
      foreach ($terms as $term) {
        if ($allwhere2) {
          $allwhere2 .= ' AND ';
        }
        $allwhere2 .= "CONCAT_WS(' ',husbpeople.firstname,TRIM(CONCAT_WS(' ',husbpeople.lnprefix,husbpeople.lastname))) LIKE \"$f$term%\"";
      }
    } else {
      $joinonhusb = '';
    }

    if ($allwhere2) {
      $allwhere2 = "AND $allwhere2";
    }

    $joinonwife = 'LEFT JOIN people AS wifepeople ON families.wife = wifepeople.personID';
    $joinonhusb = 'LEFT JOIN people AS husbpeople ON families.husband = husbpeople.personID';
    $query = "SELECT familyID, wifepeople.personID AS wpersonID, wifepeople.firstname AS wfirstname, wifepeople.lnprefix AS wlnprefix, wifepeople.lastname AS wlastname, wifepeople.suffix AS wsuffix, wifepeople.nameorder AS wnameorder, wifepeople.living AS wliving, wifepeople.private AS wprivate, wifepeople.branch AS wbranch, husbpeople.personID AS hpersonID, husbpeople.firstname AS hfirstname, husbpeople.lnprefix AS hlnprefix, husbpeople.lastname AS hlastname, husbpeople.suffix AS hsuffix, husbpeople.nameorder AS hnameorder, husbpeople.living AS hliving, husbpeople.private AS hprivate, husbpeople.branch AS hbranch FROM families $joinonwife $joinonhusb WHERE $allwhere $allwhere2 ORDER BY hlastname, hlnprefix, hfirstname LIMIT 250";
    $result = tng_query($query);

    if (tng_num_rows($result)) {
      $lines = "<tr>\n";
      $lines .= $selectline;
      $lines .= '<td>' . uiTextSnippet('familyid') . "</td>\n";
      $lines .= '<td>' . uiTextSnippet('husbname') . "</td>\n";
      $lines .= '<td>' . uiTextSnippet('wifename') . "</td>\n";
      $lines .= "</tr>\n";

      while ($row = tng_fetch_assoc($result)) {
        $thishusb = $thiswife = '';
        if ($row['hpersonID']) {
          $person['firstname'] = $row['hfirstname'];
          $person['lnprefix'] = $row['hlnprefix'];
          $person['lastname'] = $row['hlastname'];
          $person['suffix'] = $row['hsuffix'];
          $person['nameorder'] = $row['hnameorder'];
          $person['living'] = $row['hliving'];
          $person['private'] = $row['hprivate'];
          $person['branch'] = $row['hbranch'];
          $rights = determineLivingPrivateRights($person);
          $person['allow_living'] = $rights['living'];
          $person['allow_private'] = $rights['private'];
          $thishusb = getName($person);
        }
        if ($row['wpersonID']) {
          if ($thisfamily) {
            $thisfamily .= '<br>';
          }
          $person['firstname'] = $row['wfirstname'];
          $person['lnprefix'] = $row['wlnprefix'];
          $person['lastname'] = $row['wlastname'];
          $person['suffix'] = $row['wsuffix'];
          $person['nameorder'] = $row['wnameorder'];
          $person['living'] = $row['wliving'];
          $person['private'] = $row['wprivate'];
          $person['branch'] = $row['wbranch'];
          $rights = determineLivingPrivateRights($person);
          $person['allow_living'] = $rights['living'];
          $person['allow_private'] = $rights['private'];
          $thiswife = getName($person);
        }
        $lines .= "<tr id=\"linkrow_{$row['familyID']}\">\n";
        if ($mediaquery) {
          $lines .= showAction($row['familyID']);
        }
        $lines .= '<td>' . $row['familyID'] . "&nbsp;</td>\n";
        $lines .= "<td><a href='#' onclick=\"return retItem('{$row['familyID']}');\" id=\"item_{$row['familyID']}\">$thishusb</a>&nbsp;</td>\n";
        $lines .= "<td>$thiswife&nbsp;</td></tr>\n";
      }
    }
    break;
  case 'S':
    $query = "SELECT sourceID, title, shorttitle FROM sources WHERE (title LIKE \"$f$criteria%\" OR shorttitle LIKE \"$f$criteria%\") ORDER BY title LIMIT 250";
    $result = tng_query($query);

    if (tng_num_rows($result)) {
      $lines = "<tr>\n";
      $lines .= $selectline;
      $lines .= '<td style="width: 100px">' . uiTextSnippet('sourceid') . "</td>\n";
      $lines .= '<td>' . uiTextSnippet('title') . "</td>\n";
      $lines .= "</tr>\n";

      while ($row = tng_fetch_assoc($result)) {
        $lines .= "<tr id=\"linkrow_{$row['sourceID']}\">\n";
        if ($mediaquery) {
          $lines .= showAction($row['sourceID']);
        }
        $lines .= '<td>' . $row['sourceID'] . "&nbsp;</td>\n";
        $title = $row['title'] ? $row['title'] : $row['shorttitle'];
        $lines .= "<td><a href='#' onclick=\"return retItem('{$row['sourceID']}');\" id=\"item_{$row['sourceID']}\">" . truncateIt($title, 100) . "</a>&nbsp;</td></tr>\n";
      }
    }
    break;
  case 'R':
    $query = "SELECT repoID, reponame FROM repositories WHERE reponame LIKE \"$f$criteria%\" ORDER BY reponame LIMIT 250";
    $result = tng_query($query);

    if (tng_num_rows($result)) {
      $lines = "<tr>\n";
      $lines .= $selectline;
      $lines .= '<td style="width: 100px">' . uiTextSnippet('repoid') . "</td>\n";
      $lines .= '<td>' . uiTextSnippet('title') . "</td>\n";
      $lines .= "</tr>\n";

      while ($row = tng_fetch_assoc($result)) {
        $lines .= "<tr id=\"linkrow_{$row['repoID']}\">\n";
        if ($mediaquery) {
          $lines .= showAction($row['repoID']);
        }
        $lines .= '<td>' . $row['repoID'] . "&nbsp;</td>\n";
        $lines .= "<td><a href='#' onclick=\"return retItem('{$row['repoID']}');\" id=\"item_{$row['repoID']}\">" . truncateIt($row['reponame'], 75) . "</a>&nbsp;</td></tr>\n";
      }
    }
    break;
  case 'L':
    $allwhere = '1=1';
    if ($criteria) {
      $allwhere .= " AND place LIKE \"$f$criteria%\"";
    }
    if ($temple) {
      $allwhere .= ' AND temple = 1';
    }
    $query = "SELECT ID, place, temple, notes FROM places WHERE $allwhere ORDER BY place LIMIT 250";
    $result = tng_query($query);

    if (tng_num_rows($result)) {
      $lines = "<tr>\n";
      $lines .= $selectline;
      $lines .= '<td>' . uiTextSnippet('place') . "</td>\n";
      $lines .= "</tr>\n";

      $num = 1;
      while ($row = tng_fetch_assoc($result)) {
        $row['place'] = preg_replace("/'/", '&#39;', $row['place']);
        $notes = $row['temple'] && $row['notes'] ? ' (' . truncateIt($row['notes'], 75) . ')' : '';
        $place_slashed = addslashes(preg_replace('/[^A-Za-z0-9]/', '_', $row['place']));
        $lines .= "<tr id=\"linkrow_{$row['ID']}\">\n";
        if ($mediaquery) {
          $lines .= showAction($row['place'], $num);
        }
        $lines .= "<td><a href='#' onclick='return retItem(\"{$row['ID']}\",true);' class=\"rplace\" id=\"item_{$row['ID']}\">{$row['place']}</a>$notes&nbsp;</td></tr>\n";
        $num++;
      }
    }
    break;
}

if (tng_num_rows($result)) {
  echo "<table width='585'>\n$lines\n</table>\n";
} else {
  echo uiTextSnippet('noresults');
}

tng_free_result($result);
