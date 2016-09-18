<?php
require 'tng_begin.php';
if ($requirelogin && !$_SESSION['currentuser']) {
  header("Location:$homepage");
  exit;
}

$langstr = isset($_GET['lang']) ? "&amp;lang=$languagesPath" . $_GET['lang'] : '';

ini_set('session.bug_compat_warn', '0');

require 'version.php';

$date = date('r');
$timezone = date('T');

function doMedia($mediatypeID) {
  global $tngdomain;
  global $langstr;
  global $mediatypes_display;
  global $timezone;
  global $session_charset;
  global $change_limit;
  global $cutoffstr;
  global $families_table;
  global $nonames;
  global $people_table;
  global $livedefault;
  global $wherestr2;

  if ($mediatypeID == 'headstones') {
    $hsfields = ", media.cemeteryID, cemname";
    $hsjoin = "LEFT JOIN cemeteries ON media.cemeteryID = cemeteries.cemeteryID";
  } else {
    $hsfields = $hsjoin = '';
  }
  $query = "SELECT distinct media.mediaID AS mediaID, description, media.notes, thumbpath, path, form, mediatypeID, alwayson, usecollfolder, DATE_FORMAT(changedate,'%a, %d %b %Y %T') AS changedatef, status, abspath, newwindow $hsfields FROM media $hsjoin WHERE $cutoffstr $wherestr AND mediatypeID = \"$mediatypeID\" ORDER BY changedate DESC, description LIMIT $change_limit";
  $mediaresult = tng_query($query);

  while ($row = tng_fetch_assoc($mediaresult)) {
    $query = "SELECT medialinkID, medialinks.personID AS personID, medialinks.eventID, people.personID AS personID2, familyID, people.living AS living, people.private AS private, people.branch AS branch, $families_table.branch AS fbranch, $families_table.living AS fliving, $families_table.private AS fprivate, husband, wife, people.lastname AS lastname, people.lnprefix AS lnprefix, people.firstname AS firstname, people.suffix AS suffix, nameorder, sources.title, sources.sourceID, repositories.repoID,reponame, deathdate, burialdate, linktype FROM (medialinks, trees) LEFT JOIN $people_table AS people ON (medialinks.personID = people.personID) LEFT JOIN $families_table ON (medialinks.personID = $families_table.familyID) LEFT JOIN sources ON (medialinks.personID = sources.sourceID) LEFT JOIN repositories ON (medialinks.personID = repositories.repoID) WHERE mediaID = '{$row['mediaID']}' $wherestr2 ORDER BY lastname, lnprefix, firstname, medialinks.personID";
    $presult = tng_query($query);
    $foundliving = 0;
    $foundprivate = 0;
    $hstext = '';
    while ($prow = tng_fetch_assoc($presult)) {
      if ($prow['fbranch'] != null) {
        $prow['branch'] = $prow['fbranch'];
      }
      if ($prow['fliving'] != null) {
        $prow['living'] = $prow['fliving'];
      }
      if ($prow['fprivate'] != null) {
        $prow['private'] = $prow['fprivate'];
      }
      if ($prow['living'] == null && $prow['private'] == null && $prow['linktype'] == 'I') {
        $query = "SELECT count(personID) AS ccount FROM citations, $people_table WHERE citations.sourceID = '{$prow['personID']}' AND citations.persfamID = $people_table.personID AND (living = '1' OR private = '1')";
        $presult2 = tng_query($query);
        $prow2 = tng_fetch_assoc($presult2);
        if ($prow2['ccount']) {
          $prow['living'] = 1;
        }
        tng_free_result($presult2);
      }

      $prow['allow_living'] = !$prow['living'] || $livedefault == 2;
      $prow['allow_private'] = !$prow['private'];

      if ($prow['living'] && $livedefault != 2) {
        $foundliving = 1;
      }
      if ($prow['private']) {
        $foundprivate = 1;
      }

      if ($prow['personID2'] != null) {
        $medialink = "peopleShowPerson.php?personID={$prow['personID2']}";
        $mediatext = getName($prow);
        if ($mediatypeID == 'headstones') {
          $deathdate = $prow['deathdate'] ? $prow['deathdate'] : $prow['burialdate'];
          if ($prow['deathdate']) {
            $abbrev = uiTextSnippet('deathabbr');
          } elseif ($prow['burialdate']) {
            $abbrev = uiTextSnippet('burialabbr');
          }
          $hstext = $deathdate ? " ($abbrev " . displayDate($deathdate) . ')' : '';
        }
      } elseif ($prow['familyID'] != null) {
        $medialink = "familiesShowFamily.php?familyID={$prow['familyID']}";
        $mediatext = uiTextSnippet('family') . ': ' . getFamilyName($prow);
      } elseif ($prow['sourceID'] != null) {
        $mediatext = $prow['title'] ? uiTextSnippet('source') . ": {$prow['title']}" : uiTextSnippet('source') . ": {$prow['sourceID']}";
        $medialink = "sourcesShowSource.php?sourceID={$prow['sourceID']}";
      } elseif ($prow['repoID'] != null) {
        $mediatext = $prow['reponame'] ? uiTextSnippet('repository') . ': ' . $prow['reponame'] : uiTextSnippet('repository') . ': ' . $prow['repoID'];
        $medialink = "repositoriesShowItem.php?repoID={$prow['repoID']}";
      } else {
        $medialink = "placesearch.php?psearch={$prow['personID']}";
        $mediatext = $prow['personID'];
      }
      if ($prow['eventID']) {
        $query = "SELECT description FROM events, eventtypes WHERE eventID = \"$prow[eventID]\" AND events.eventtypeID = eventtypes.eventtypeID";
        $eresult = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . " : $query");
        $erow = tng_fetch_assoc($eresult);
        $event = $erow['description'] ? $erow['description'] : $prow['eventID'];
        tng_free_result($eresult);
        $mediatext .= " ($event)";
      }
    }
    tng_free_result($presult);

    $href = getMediaHREF($row, 0);
    $href = str_replace('" target="_blank', '', $href);  // fix the string in case someone might have used the "open in a new window" option on the media
    if ((!$foundliving && !$foundprivate) || !$nonames || $row['alwayson']) {
      $description = strip_tags($row['description']);
      $notes = nl2br(strip_tags(getXrefNotes($row['notes'])));
      if (($foundliving || $foundprivate) && !$row['alwayson']) {
        $notes .= ' (' . uiTextSnippet('livingphoto') . ')';
      }
    } else {
      $description = uiTextSnippet('living');
      $notes = '(' . uiTextSnippet('livingphoto') . ')';
    }

    if ($row['status']) {
      $notes = uiTextSnippet('status') . ": $row[status]. $notes";
    }
    $item = "\n<item>\n"; // build the $item string so that you can apply string functions more globally instead of piece meal, as required

    $typestr = uiTextSnippet($mediatypeID) ? uiTextSnippet($mediatypeID) : $mediatypes_display[$mediatypeID];
    $item .= '<title>' . xmlcharacters($typestr) . ': ' . xmlcharacters($description) . "</title>\n";
    $item .= '<link>' . ($row['abspath'] ? '' : "$tngdomain/") . "$href$langstr" . "</link>\n";

    if ($mediatypeID == 'headstones') {
      $deathdate = $row['deathdate'] ? $row['deathdate'] : $row['burialdate'];
      $item .= '<description>' . xmlcharacters($hstext . ' ' . htmlspecialchars($notes, ENT_NOQUOTES, $session_charset)) . "</description>\n";
      $item .= '<category>' . uiTextSnippet('tree') . ": master</category>\n";
    } else {
      $item .= '<description>' . xmlcharacters(htmlspecialchars($notes, ENT_NOQUOTES, $session_charset)) . "</description>\n";
    }
    $changedate = date_format(date_create($row['changedatef']), 'D, d M Y H:i:s');
    $item .= "<pubDate>$changedate $timezone</pubDate>\n";

    $item .= "<guid isPermaLink=\"false\">$tngdomain/{$row['mediaID']}-$changedate $timezone</guid>\n"; // using a guid improves the granularity of changes one ca monitor (ie: it allows for a changes minitus appart to be captures by the RSS feed)
    $item .= "</item>\n";
    echo $item;
  }
  tng_free_result($mediaresult);
}

header("Content-type: application/rss+xml; charset=\"$charset\"");

$item .= "<rss version=\"2.0\" xmlns:atom=\"{$http}://www.w3.org/2005/Atom\">\n";
$item .= "<channel>\n";
$item .= '<atom:link href="' . $tngdomain . "/tngrss.php\" rel=\"self\" type=\"application/rss+xml\" />\n";

$tngscript = basename($_SERVER['SCRIPT_NAME'], '.php');

$item .= "<copyright>$tng_title, v.$tng_version ($tng_date), $tng_copyright</copyright>\n";
$item .= "<lastBuildDate>$date</lastBuildDate>\n";
$item .= '<description>' . xmlcharacters($site_desc) . "</description>\n";

if ($personID) {
  $item .= '<title>' . trim($sitename . ' ' . uiTextSnippet('indinfo')) . ": $personID</title>\n";
} elseif ($familyID) {
  $item .= '<title>' . trim($sitename . ' ' . uiTextSnippet('family')) . ": $familyID</title>\n";
} else {
  $item .= "<title>$sitename</title>\n";
}
$item .= "<link>$tngdomain</link>\n";
$item .= "<managingEditor>$emailaddr ($dbowner)</managingEditor>\n";
$item .= "<webMaster>$emailaddr ($dbowner)</webMaster>\n";
// [ts] define $rssimage to use this. (allows a logo on your feed once you have subscribed)
//if ($rssimage) {
//  $item .= "<image>\n";
//  $item .= "<url>" . $tngdomain . $rssimage . "</url>\n";     // path for the logo
//  if ($personID) {
//    $item .= "<title>" . trim($sitename . ' ' . uiTextSnippet('indinfo')) . ": $personID</title>\n";  // images require a title (match it with either the personID)
//  } elseif ($familyID) {
//    $item .= "<title>" . trim($sitename . ' ' . uiTextSnippet('family')) . ": $familyID</title>\n";    // the familyID
//  } else {
//    $item .= "<title>$sitename</title>\n";                      // or just the site name
//  }
//  $item .= "<link>" . $tngdomain . "</link>\n";                  // images also require the site link so that if you click on the image you go to the site
//  $item .= "</image>\n";
//}
echo $item;

// [ts] define $rsslang to use this
//echo "<language>$rsslang</language>\n";

$text['pastxdays'] = preg_replace('/xx/', "$change_cutoff", $text['pastxdays']);
if (!$change_cutoff) {
  $change_cutoff = 0;
}
if (!$change_limit) {
  $change_limit = 10;
}
$cutoffstr = $change_cutoff ? "TO_DAYS(NOW()) - TO_DAYS(changedate) <= $change_cutoff" : '1=1';

if (!$personID && !$familyID) {             // only feed the changes when not monitoring an person or a family
  initMediaTypes();
  foreach ($mediatypes as $mediatype) {
    $mediatypeID = $mediatype[ID];
    echo doMedia($mediatypeID);
  }
}
$allwhere = '';

$more = getLivingPrivateRestrictions('p', false, false);
if ($more) {
  $allwhere .= ' AND ' . $more;
}

if (!$familyID) {    // if a family is NOT specified (ie: we are looking for a personID or the What's New
  $query = "SELECT p.personID, lastname, lnprefix, firstname, birthdate, prefix, suffix, nameorder, living, private, branch, DATE_FORMAT(changedate,'%e %b %Y') AS changedatef, changedby, LPAD(SUBSTRING_INDEX(birthdate, ' ', -1),4,'0') AS birthyear, birthplace, altbirthdate, LPAD(SUBSTRING_INDEX(altbirthdate, ' ', -1),4,'0') AS altbirthyear, altbirthplace FROM $people_table as p, trees WHERE $cutoffstr $allwhere ORDER BY changedate DESC, lastname, firstname, birthyear, altbirthyear LIMIT $change_limit";
  $result = tng_query($query);
  $numrows = tng_num_rows($result);
  if ($numrows) {
    while ($row = tng_fetch_assoc($result)) {
      $rights = determineLivingPrivateRights($row);
      $row['allow_living'] = $rights['living'];
      $row['allow_private'] = $rights['private'];
      $namestr = getNameRev($row);
      $birthplacestr = '';
      if ($rights['both']) {
        if ($row['birthdate']) {
          $birthdate = uiTextSnippet('birthabbr') . ' ' . displayDate($row['birthdate']);
          $birthplace = $row['birthplace'];
        } else {
          if ($row['altbirthdate']) {
            $birthdate = uiTextSnippet('chrabbr') . ' ' . displayDate($row['altbirthdate']);
            $birthplace = $row['altbirthplace'];
          } else {
            $birthdate = '';
            $birthplace = '';
          }
        }
      } else {
        $birthdate = $birthplace = '';
      }
      $item = "\n<item>\n";
      $item .= '<title>';
      $item .= xmlcharacters(uiTextSnippet('indinfo') . ': ' . $namestr . ' (' . $row['personID'] . ')');
      $item .= "</title>\n";
      $item .= '<link>' . "$tngdomain/peopleShowPerson.php?personID=" . $row['personID'] . $langstr . "</link>\n";
      $item .= '<description>';
      if ($birthdate || $birthplace) {
        $item .= xmlcharacters("$birthdate, $birthplace") . "</description>\n";
      } else {
        $item .= xmlcharacters(uiTextSnippet('birthabbr')) . "</description>\n";
      }
      $item .= '<category>' . uiTextSnippet('tree') . ": master</category>\n";
      $changedate = date_format(date_create($row['changedatef']), 'D, d M Y H:i:s');
      $item .= "<pubDate>$changedate $timezone </pubDate>\n";

      $item .= "</item>\n";
      echo $item;
    }
    tng_free_result($result);
  }
}

if ($familyID) {
  $whereclause = "WHERE $families_table.familyID = \"$familyID\"$privacystr ORDER BY changedate LIMIT $change_limit";
} else {
  $whereclause = $change_cutoff ? "WHERE TO_DAYS(NOW()) - TO_DAYS($families_table.changedate) <= $change_cutoff$privacystr" : "WHERE 1=1$privacystr";
  $whereclause .= " ORDER BY changedate DESC LIMIT $change_limit";
}

if (!$personID) {
  $query = "SELECT familyID, husband, wife, marrdate, marrplace, branch, living, private, DATE_FORMAT(changedate,'%a, %d %b %Y %T') AS changedatef FROM $families_table $whereclause";
  $famresult = tng_query($query);
  $numrows = tng_num_rows($famresult);
  if ($numrows) {
    while ($row = tng_fetch_assoc($famresult)) {
      $row['allow_living'] = $nonames == 2 && $row['living'] ? 0 : 1;
      $row['allow_private'] = $tngconfig['nnpriv'] == 2 && $row['private'] ? 0 : 1;

      $item = "\n<item>\n";
      $item .= '<title>' . xmlcharacters(uiTextSnippet('family') . ': ' . getFamilyName($row)) . "</title>\n";
      $item .= '<link>' . "$tngdomain/familiesShowFamily.php?familyID={$row['familyID']}$langstr" . "</link>\n";
      $item .= '<description>';

      $item .= displayDate($row['marrdate']);
      if ($row['marrdate'] && $row['marrplace']) {
        $item .= ', ';
      }
      $item .= xmlcharacters($row['marrplace']);

      $item .= "</description>\n";
      $item .= '<category>' . uiTextSnippet('tree') . ": master</category>\n";
      $item .= '<pubDate>' . displayDate($row['changedatef']) . " $timezone </pubDate>\n";

      $item .= "</item>\n";
      echo $item;
    }
    tng_free_result($famresult);
  }
}

echo "</channel>\n";
echo "</rss>\n";
