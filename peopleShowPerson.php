<?php
/**
 * Name history: getperson.php
 */

$needMap = true;
require 'tng_begin.php';

if (!$personID) {
    header('Location: thispagedoesnotexist.html');
    exit;
}
if ($tngprint) {
    $tngconfig['istart'] = '';
    $tngconfig['hidemedia'] = '';
}
$defermap = ($map['pstartoff'] === true) || $tngconfig['istart'] ? 1 : 0;
require 'personlib.php';

$citations = [];
$citedisplay = [];
$citestring = [];
$citationctr = 0;
$citedispctr = 0;
$firstsection = 1;
$firstsectionsave = '';
$tableid = '';
$cellnumber = 0;

$indnotes = getNotes($personID, 'I');
getCitations($personID);
$stdex = getStdExtras($personID);

$result = getPersonFullPlusDates($personID);
if (!tng_num_rows($result)) {
    tng_free_result($result);
    header('Location: thispagedoesnotexist.html');
    exit;
}
$flags['imgprev'] = true;

$row = tng_fetch_assoc($result);
$rightbranch = checkbranch($row['branch']);
$rights = determineLivingPrivateRights($row, $rightbranch);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];
if (!$rightbranch) {
    $tentative_edit = '';
}
$org_rightbranch = $rightbranch;
$namestr = getName($row);
$nameformap = $namestr;

$logname = $tngconfig['nnpriv'] && $row['private'] ? uiTextSnippet('private') : ($nonames && $row['living'] ? uiTextSnippet('living') : $namestr);
$treestr = "<a href='showtree.php'>{$treerow['treename']}</a>";
if ($row['branch']) {
  $branches = explode(',', $row['branch']);
  $count = 0;
  $branchstr = '';
  foreach ($branches as $branch) {
    $count++;
    $brresult = getBranchesSimple($branch);
    $brrow = tng_fetch_assoc($brresult);
    $branchstr .= $brrow['description'] ? $brrow['description'] : $branch;
    if ($count < count($branches)) {
      $branchstr .= ', ';
    }
    tng_free_result($brresult);
  }
  if ($branchstr) {
    $treestr = $treestr . " | $branchstr";
  }
}
tng_free_result($result);

writelog("<a href='peopleShowPerson.php?personID=$personID'>" . uiTextSnippet('indinfofor') . " $logname ($personID)</a>");
preparebookmark("<a href='peopleShowPerson.php?personID=$personID'>" . uiTextSnippet('indinfofor') . " $namestr ($personID)</a>");

$headTitle = $namestr;
if ($rights['both']) {
  if ($row['birthdate']) {
    $headTitle .= ' ' . uiTextSnippet('birthabbr') . ' ' . displayDate($row['birthdate']);
  }
  if ($row['birthplace']) {
    $headTitle .= ' ' . $row['birthplace'];
  }
  if ($row['deathdate']) {
    $headTitle .= ' ' . uiTextSnippet('deathabbr') . ' ' . displayDate($row['deathdate']);
  }
  if ($row['deathplace']) {
    $headTitle .= ' ' . $row['deathplace'];
  }
}

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

$snippets['persinfo'] = uiTextSnippet('persinfo');
$snippets['family'] = uiTextSnippet('family');
$snippets['notes'] = uiTextSnippet('notes');
$snippets['media'] = uiTextSnippet('media');

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle($headTitle);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $sessionCharset); ?>
<body class='people' id='showperson'>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();

    $indmedia = getMedia($row, 'I');
    $indalbums = getAlbums($row, 'I');

    $photostr = showSmallPhoto($personID, $namestr, $rights['both'], 0, 'I', $row['sex']);
    if ($rights['both']) {
      $citekey = $personID . '_';
      $cite = reorderCitation($citekey);
      
      if ($cite) {
        $namestr .= "<sup> $cite</sup>";
      }
    }
    echo "<div>\n";
    echo tng_DrawHeading($photostr, $namestr, getYears($row));

    $persontext = '';
    $persontext .= "<div id='accordion' role='tablist' aria-multiselectable='true'>\n";

    if ($tng_extras) {
      $media = doMediaSection($personID, $indmedia, $indalbums);
      if ($media) {
        $persontext .= "<div class='card'>\n";
        $persontext .= "<div class='card-header' id='headingMedia' role='tab'>\n";
        $persontext .= "<h5>\n";
        $persontext .= "<a data-toggle='collapse' data-parent='#accordion' href='#collapseMedia' aria-expanded='true' aria-controls='collapseMedia'>Media</a>\n";
        $persontext .= "</h5>\n";
        $persontext .= "</div>\n";
        
        $persontext .= "<div class='card-block collapse' id='collapseMedia' role='tabpanel' aria-labelledby='headingMedia'>\n";

        $persontext .= $media . "\n";
        $persontext .= "</div>\n";
        $persontext .= "</div>\n";
      }
    }
    $persontext .= "<div class='card'>\n";
    $persontext .= "<div class='card-header' role='tab' id='headingInfo'>\n";
    $persontext .= "<h5>\n";
    $persontext .= "<a data-toggle='collapse' data-parent='#accordion' href='#collapseInfo' aria-expanded='true' aria-controls='collapseInfo'>Personal Information</a>\n";
    $persontext .= "</h5>\n";
    $persontext .= "</div>\n";
    $persontext .= "<div class='card-block collapse in' id='collapseInfo' role='tabpanel' aria-labelledby='headingInfo'>\n";
    
    $persontext .= "<table class='table table-sm'>\n";
    resetEvents();
    if ($rights['both']) {
      $persontext .= showEvent(['text' => uiTextSnippet('name'), 'fact' => getName($row, true), 'event' => 'NAME', 'entity' => $personID, 'type' => 'I']);
      if ($row['title']) {
        $persontext .= showEvent(['text' => uiTextSnippet('title'), 'fact' => $row['title'], 'event' => 'TITL', 'entity' => $personID, 'type' => 'I']);
      }
      if ($row['prefix']) {
        $persontext .= showEvent(['text' => uiTextSnippet('prefix'), 'fact' => $row['prefix'], 'event' => 'NPFX', 'entity' => $personID, 'type' => 'I']);
      }
      if ($row['suffix']) {
        $persontext .= showEvent(['text' => uiTextSnippet('suffix'), 'fact' => $row['suffix'], 'event' => 'NSFX', 'entity' => $personID, 'type' => 'I']);
      }
      if ($row['nickname']) {
        $persontext .= showEvent(['text' => uiTextSnippet('nickname'), 'fact' => $row['nickname'], 'event' => 'NICK', 'entity' => $personID, 'type' => 'I']);
      }
      if ($row['private'] && $allowEdit && $allowAdd && $allowDelete) {
        $persontext .= showEvent(['text' => uiTextSnippet('private'), 'fact' => uiTextSnippet('yes')]);
      }
      setEvent(['text' => uiTextSnippet('born'), 'fact' => $stdex['BIRT'], 'date' => $row['birthdate'], 'place' => $row['birthplace'], 'event' => 'BIRT', 'entity' => $personID, 'type' => 'I'], $row['birthdatetr']);
      setEvent(['text' => uiTextSnippet('christened'), 'fact' => $stdex['CHR'], 'date' => $row['altbirthdate'], 'place' => $row['altbirthplace'], 'event' => 'CHR', 'entity' => $personID, 'type' => 'I'], $row['altbirthdatetr']);
    }
    if ($row['sex'] == 'M') {
      $sex = uiTextSnippet('male');
      $spouse = 'wife';
      $self = 'husband';
      $spouseorder = 'husborder';
    } else if ($row['sex'] == 'F') {
        $sex = uiTextSnippet('female');
        $spouse = 'husband';
        $self = 'wife';
        $spouseorder = 'wifeorder';
    } else {
        $sex = uiTextSnippet('unknown');
        $spouseorder = '';
    }
    setEvent(['text' => uiTextSnippet('gender'), 'fact' => $sex], $nodate);

    if ($rights['both']) {
      if ($rights['lds']) {
        setEvent(['text' => uiTextSnippet('baptizedlds'), 'fact' => $stdex['BAPL'], 'date' => $row['baptdate'], 'place' => $row['baptplace'], 'event' => 'BAPL', 'entity' => $personID, 'type' => 'I'], $row['baptdatetr']);
        setEvent(['text' => uiTextSnippet('conflds'), 'fact' => $stdex['CONL'], 'date' => $row['confdate'], 'place' => $row['confplace'], 'event' => 'CONL', 'entity' => $personID, 'type' => 'I'], $row['confdatetr']);
        setEvent(['text' => uiTextSnippet('initlds'), 'fact' => $stdex['INIT'], 'date' => $row['initdate'], 'place' => $row['initplace'], 'event' => 'INIT', 'entity' => $personID, 'type' => 'I'], $row['initdatetr']);
        setEvent(['text' => uiTextSnippet('endowedlds'), 'fact' => $stdex['ENDL'], 'date' => $row['endldate'], 'place' => $row['endlplace'], 'event' => 'ENDL', 'entity' => $personID, 'type' => 'I'], $row['endldatetr']);
      }
      doCustomEvents($personID, 'I');

      setEvent(['text' => uiTextSnippet('died'), 'fact' => $stdex['DEAT'], 'date' => $row['deathdate'], 'place' => $row['deathplace'], 'event' => 'DEAT', 'entity' => $personID, 'type' => 'I'], $row['deathdatetr']);
      $burialmsg = $row['burialtype'] ? uiTextSnippet('cremated') : uiTextSnippet('buried');
      setEvent(['text' => $burialmsg, 'fact' => $stdex['BURI'], 'date' => $row['burialdate'], 'place' => $row['burialplace'], 'event' => 'BURI', 'entity' => $personID, 'type' => 'I'], $row['burialdatetr']);
    }
    ksort($events);
    foreach ($events as $event) {
      $persontext .= showEvent($event);
    }
    if ($rights['both']) {
      $assocresult = getAssociations($personID);
      while ($assoc = tng_fetch_assoc($assocresult)) {
        $persontext .= showEvent(['text' => uiTextSnippet('association'), 'fact' => formatAssoc($assoc)]);
      }
      tng_free_result($assocresult);
    }
    $notes = '';
    if ($notestogether == 1) {
      if ($rights['both']) {
        $notes = buildGenNotes($indnotes, $personID, '--x-general-x--');
      }
      elseif ($row['living']) {
        $notes = uiTextSnippet('livingnote');
      }
      if ($notes) {
        $persontext .= "<tr>\n";
        $persontext .= "<td id='notes1'>{$snippets['notes']}</td>\n";
        $persontext .= "<td colspan='2'><div class='notearea'>$notes</div></td>\n";
        $persontext .= "</tr>\n";
        $notes = ''; //wipe it out so we don't get a link at the top
      }
    }
    // [ts] stuffing $personID into `date` array element for pass to showEvent requires special text snippet processing
    //      determine if this is neccessary. 
    $persontext .= showEvent(['text' => uiTextSnippet('personid'), 'date' => $personID, 'place' => $treestr, 'np' => 1]);
    if ($row['changedate'] || ( $allowEdit && $rightbranch )) {
      $row['changedate'] = displayDate($row['changedate'], false);
      if ($allowEdit && $rightbranch) {
        if ($row['changedate']) {
          $row['changedate'] .= ' | ';
        }
        $row['changedate'] .= "<a href='peopleEdit.php?personID=$personID&amp;cw=1' target='_blank'>" . uiTextSnippet('edit') . '</a>';
      }
      $persontext .= showEvent(['text' => uiTextSnippet('lastmodified'), 'fact' => $row['changedate']]);
    }
    $persontext .= "</table>\n";
    $persontext .= "<br>\n";

    $persontext .= "</div>\n";
    $persontext .= "</div> <!-- .card -->\n";

    $persontext .= "<div class='card'>\n";
    $persontext .= "<div class='card-header' role='tab' id='headingFamily'>\n";
    $persontext .= "<h5>\n";
    $persontext .= "<a data-toggle='collapse' data-parent='#accordion' href='#collapseFamily' aria-expanded='true' aria-controls='collapseFamily'>{$snippets['family']}</a>\n";
    $persontext .= "</h5>\n";
    $persontext .= "</div>\n";
    $persontext .= "<div class='collapse' id='collapseFamily' role='tabpanel' aria-labelledby='headingFamily'>\n";
    
    // do parents
    $parents = getChildParentsFamily($personID);

    if ($parents && tng_num_rows($parents)) {
      while ($parent = tng_fetch_assoc($parents)) {
        $persontext .= "<table class='table table-sm'>\n";
        $tableid = 'fam' . $parent['familyID'] . '_';
        $cellnumber = 0;
        resetEvents();
        getCitations($personID . '::' . $parent['familyID']);

        $fatherHtml = '';
        $gotfather = getParentData($parent['familyID'], 'husband');
        if ($gotfather) {
          $fathrow = tng_fetch_assoc($gotfather);
          $birthinfo = getBirthInfo($fathrow);
          $frights = determineLivingPrivateRights($fathrow);
          $fathrow['allow_living'] = $frights['living'];
          $fathrow['allow_private'] = $frights['private'];
          if ($fathrow['firstname'] || $fathrow['lastname']) {
            $fathname = getName($fathrow);
            $fatherlink = "<a href='peopleShowPerson.php?personID={$fathrow['personID']}'>$fathname</a>";
          }
          else {
            $fatherlink = '';
          }
          if ($frights['both']) {
            $fatherlink .= $birthinfo;
            if ($fatherlink) {
              $age = age($fathrow);
              if ($age) {
                $fatherlink .= ' &nbsp;(' . uiTextSnippet('age') . " $age)";
              }
            }
          }
          $label = $fathrow['sex'] == 'F' ? uiTextSnippet('mother') : uiTextSnippet('father');
          $fatherHtml .= showEvent(['text' => $label, 'fact' => $fatherlink]);
          if ($rights['both'] && $parent['frel']) {
            $rel = $parent['frel'];
            $relstr = uiTextSnippet($rel) ? uiTextSnippet($rel) : $rel;
            $fatherHtml .= showEvent(['text' => uiTextSnippet('relationship2'), 'fact' => $relstr]);
          }
          tng_free_result($gotfather);
        }

        $motherHtml = '';
        $gotmother = getParentData($parent['familyID'], 'wife');
        if ($gotmother) {
          $mothrow = tng_fetch_assoc($gotmother);
          $birthinfo = getBirthInfo($mothrow);
          $mrights = determineLivingPrivateRights($mothrow);
          $mothrow['allow_living'] = $mrights['living'];
          $mothrow['allow_private'] = $mrights['private'];
          if ($mothrow['firstname'] || $mothrow['lastname']) {
            $mothname = getName($mothrow);
            $motherlink = "<a href=\"peopleShowPerson.php?personID={$mothrow['personID']}\">$mothname</a>";
          }
          else {
            $motherlink = '';
          }
          if ($mrights['both']) {
            $motherlink .= $birthinfo;
            if ($motherlink) {
              $age = age($mothrow);
              if ($age) {
                $motherlink .= ' &nbsp;(' . uiTextSnippet('age') . " $age)";
              }
            }
          }
          $label = $mothrow['sex'] == 'M' ? uiTextSnippet('father') : uiTextSnippet('mother');
          $motherHtml .= showEvent(['text' => uiTextSnippet('mother'), 'fact' => $motherlink]);
          if ($rights['both'] && $parent['mrel']) {
            $rel = $parent['mrel'];
            $relstr = uiTextSnippet($rel) ? uiTextSnippet($rel) : $rel;
            $motherHtml .= showEvent(['text' => uiTextSnippet('relationship2'), 'fact' => $relstr]);
          }
          tng_free_result($gotmother);
        }

        // parents events
        
        $parentsEventsHtml = '';
        if ($rights['both'] && $rights['lds'] && $tngconfig['pardata'] < 2) {
          setEvent(['text' => uiTextSnippet('sealedplds'), 'date' => $parent['sealdate'], 'place' => $parent['sealplace'], 'event' => 'SLGC', 'entity' => "$personID::{$parent['familyID']}", 'type' => 'C', 'nomap' => 1], $parent['sealdatetr']);
        }
        $gotparents = getFamilyData($parent['familyID']);
        $parentrow = tng_fetch_assoc($gotparents);
        tng_free_result($gotparents);
        $parent['personID'] = '';

        if ($tngconfig['pardata'] < 2) { // [ts] 0 all events or 1 standard events only
          $prights = determineLivingPrivateRights($parentrow);
          $parentrow['allow_living'] = $prights['living'];
          $parentrow['allow_private'] = $prights['private'];

          if ($prights['both'] && (!$gotfather || $frights['both']) && (!$gotmother || $mrights['both'])) {
            if (!$tngconfig['pardata']) { // [ts] 0 non-standard events
              $famnotes = getNotes($parent['familyID'], 'F');
              getCitations($parent['familyID']);
              $stdexf = getStdExtras($parent['familyID']);
              if ($parent['marrtype']) {
                if (!is_array($stdexf['MARR'])) {
                  $stdexf['MARR'] = [];
                }
                array_unshift($stdexf['MARR'], uiTextSnippet('type') . ': ' . $parent['marrtype']);
              }
            }
            setEvent(['text' => uiTextSnippet('married'), 'fact' => $stdexf['MARR'], 'date' => $parentrow['marrdate'], 'place' => $parentrow['marrplace'], 'event' => 'MARR', 'entity' => $parentrow['familyID'], 'type' => 'F', 'nomap' => 1], $parentrow['marrdatetr']);
            setEvent(['text' => uiTextSnippet('divorced'), 'fact' => $stdexf['DIV'], 'date' => $parentrow['divdate'], 'place' => $parentrow['divplace'], 'event' => 'DIV', 'entity' => $parentrow['familyID'], 'type' => 'F', 'nomap' => 1], $parentrow['divdatetr']);

            if (!$tngconfig['pardata']) {
              doCustomEvents($parent['familyID'], 'F', 1);
            }
            if (!$tngconfig['pardata']) {
              $fammedia = getMedia($parentrow, 'F');
              $famalbums = getAlbums($parentrow, 'F');
            }
            ksort($events);
            foreach ($events as $event) {
              $parentsEventsHtml .= showEvent($event);
            }
            $assocresult = getAssociations($parent['familyID']);
            while ($assoc = tng_fetch_assoc($assocresult)) {
              $parentsEventsHtml .= showEvent(['text' => uiTextSnippet('association'), 'fact' => formatAssoc($assoc)]);
            }
            tng_free_result($assocresult);

            if (!$tngconfig['pardata']) {
              $famnotes2 = '';
              if (!$notestogether) {
                $famnotes2 = buildNotes($famnotes, $parent['familyID']);
              }
              else {
                $famnotes2 = buildGenNotes($famnotes, $parent['familyID'], '--x-general-x--');
              }
              if ($famnotes2) {
                $parentsEventsHtml .= "<tr>\n";
              $parentsEventsHtml .= "<td>{$snippets['notes']}</td>\n";
                $parentsEventsHtml .= "<td colspan='2'><span><div class='notearea'>$famnotes2</div></span></td>\n";
                $parentsEventsHtml .= "</tr>\n";
              }
              foreach ($mediatypes as $mediatype) {
                $mediatypeID = $mediatype['ID'];
                $parentsEventsHtml .= writeMedia($fammedia, $mediatypeID, 'p');
              }
              $parentsEventsHtml .= writeAlbums($famalbums);
            }
          }
        }
        $persontext .= $fatherHtml;
        $persontext .= $motherHtml;

        $persontext .= $parentsEventsHtml;
        $persontext .= showEvent(['text' => uiTextSnippet('familyid'), 'date' => $parent['familyID'], 'place' => "<a href=\"familiesShowFamily.php?familyID={$parent['familyID']}\">" . uiTextSnippet('groupsheet') . '</a>', 'np' => 1]);
        $persontext .= "</table>\n";
        $persontext .= "<br>\n";
      }
      tng_free_result($parents);
    }

    // spouses and children
    if ($spouseorder) {
      $marriages = getSpouseFamilyFull($self, $personID, $spouseorder);
    }
    else {
      $marriages = getSpouseFamilyFullUnion($personID);
    }
    $marrtot = tng_num_rows($marriages);
    if (!$marrtot) {
      if ($spouseorder) {
        $marriages = getSpouseFamilyFullUnion($personID);
        $marrtot = tng_num_rows($marriages);
      }
      $spouseorder = 0;
    }
    $marrcount = 1;

    while ($marriagerow = tng_fetch_assoc($marriages)) {
      $persontext .= "<table class='table table-sm'>\n";
      $tableid = 'fam' . $marriagerow['familyID'] . '_';
      $cellnumber = 0;
      $famnotes = getNotes($marriagerow['familyID'], 'F');
      getCitations($marriagerow['familyID']);
      $stdexf = getStdExtras($marriagerow['familyID']);
      if ($marriagerow['marrtype']) {
        if (!is_array($stdexf['MARR'])) {
          $stdexf['MARR'] = [];
        }
        array_unshift($stdexf['MARR'], uiTextSnippet('type') . ': ' . $marriagerow['marrtype']);
      }

      if (!$spouseorder) {
        $spouse = $marriagerow['husband'] == $personID ? wife : husband;
      }
      unset($spouserow);
      unset($birthinfo);
      if ($marriagerow[$spouse]) {
        $spouseresult = getPersonData($marriagerow[$spouse]);
        $spouserow = tng_fetch_assoc($spouseresult);
        $birthinfo = getBirthInfo($spouserow);
        $srights = determineLivingPrivateRights($spouserow);
        $spouserow['allow_living'] = $srights['living'];
        $spouserow['allow_private'] = $srights['private'];
        if ($spouserow['firstname'] || $spouserow['lastname']) {
          $spousename = getName($spouserow);
          $spouselink = "<a href=\"peopleShowPerson.php?personID={$spouserow['personID']}\">$spousename</a>";
        }
        if ($srights['both']) {
          $spouselink .= $birthinfo;
          if ($spouselink) {
            $age = age($spouserow);
            if ($age) {
              $spouselink .= ' &nbsp;(' . uiTextSnippet('age') . " $age)";
            }
          }
        }
        tng_free_result($spouseresult);
      }
      else {
        $spouselink = '';
        $srights['both'] = true;
      }
      $marrstr = $marrtot > 1 ? " $marrcount" : '';
      if ($srights['both']) {
        $persontext .= showEvent(['text' => uiTextSnippet('family') . "$marrstr", 'fact' => $spouselink, 'entity' => $marriagerow['familyID'], 'type' => 'F']);
      }
      else {
        $persontext .= showEvent(['text' => uiTextSnippet('family') . "$marrstr", 'fact' => $spouselink]);
      }
      $rightfbranch = checkbranch($marriagerow['branch']) ? 1 : 0;
      $marrights = determineLivingPrivateRights($marriagerow);
      $marriagerow['allow_living'] = $marrights['living'];
      $marriagerow['allow_private'] = $marrights['private'];
      $fammedia = getMedia($marriagerow, 'F');
      $famalbums = getAlbums($marriagerow, 'F');
      if ($marrights['both'] && $srights['both']) {
        resetEvents();

        setEvent(['text' => uiTextSnippet('married'), 'fact' => $stdexf['MARR'], 'date' => $marriagerow['marrdate'], 'place' => $marriagerow['marrplace'], 'event' => 'MARR', 'entity' => $marriagerow['familyID'], 'type' => 'F'], $marriagerow['marrdatetr']);
        setEvent(['text' => uiTextSnippet('divorced'), 'fact' => $stdexf['DIV'], 'date' => $marriagerow['divdate'], 'place' => $marriagerow['divplace'], 'event' => 'DIV', 'entity' => $marriagerow['familyID'], 'type' => 'F'], $marriagerow['divdatetr']);

        if ($marrights['lds']) {
          setEvent(['text' => uiTextSnippet('sealedslds'), 'fact' => $stdexf['SLGS'], 'date' => $marriagerow['sealdate'], 'place' => $marriagerow['sealplace'], 'event' => 'SLGS', 'entity' => $marriagerow['familyID'], 'type' => 'F'], $marriagerow['sealdatetr']);
        }
        doCustomEvents($marriagerow['familyID'], 'F');
        ksort($events);
        foreach ($events as $event) {
          $persontext .= showEvent($event);
        }
        $assocresult = getAssociations($marriagerow['familyID']);
        while ($assoc = tng_fetch_assoc($assocresult)) {
          $persontext .= showEvent(['text' => uiTextSnippet('association'), 'fact' => formatAssoc($assoc)]);
        }
        tng_free_result($assocresult);

        $famnotes2 = '';
        if (!$notestogether) {
          $famnotes2 = buildNotes($famnotes, $marriagerow['familyID']);
        }
        else {
          $famnotes2 = buildGenNotes($famnotes, $marriagerow['familyID'], '--x-general-x--');
        }
        if ($famnotes2) {
          $persontext .= "<tr>\n";
          $persontext .= "<td>{$snippets['notes']}</td>\n";
          $persontext .= "<td colspan='2'><span><div class=\"notearea\">$famnotes2</div></span></td>\n";
          $persontext .= "</tr>\n";
        }
      }
      $marrcount++;

      // children
      $children = getChildrenData($marriagerow['familyID']);

      if ($children && tng_num_rows($children)) {
        $persontext .= "<tr>\n";
        $persontext .= '<td>' . uiTextSnippet('children') . "</td>\n";
        $persontext .= "<td colspan='2'>\n";

        $kidcount = 1;
        $persontext .= "<table class='table table-sm table-striped'>\n";
        while ($child = tng_fetch_assoc($children)) {
          $childID = $child['personID'];
          $child['gedcom'] = '';
          $ifkids = $child['haskids'] ? "<a href=\"descend.php?personID=$childID\" title=\"" . uiTextSnippet('descendants') . '" class="descindicator"><strong>+</strong></a>' : '&nbsp;';
          $birthinfo = getBirthInfo($child);
          $crights = determineLivingPrivateRights($child);
          $child['allow_living'] = $crights['living'];
          $child['allow_private'] = $crights['private'];
          if ($child['firstname'] || $child['lastname']) {
            $childname = getName($child);
            $persontext .= "<tr>\n";
            $persontext .= "<td width='10'>$ifkids</td>\n";
            $persontext .= "<td id=\"child$childID\"><span>$kidcount. <a href=\"peopleShowPerson.php?personID=$childID\">$childname</a>";
            if ($crights['both']) {
              $persontext .= $birthinfo;
              $age = age($child);
              if ($age) {
                $persontext .= ' &nbsp;(' . uiTextSnippet('age') . " $age)";
              }
            }
            $persontext .= "</span></td></tr>\n";
            $kidcount++;
          }
        }
        $persontext .= "</table>\n";
        $persontext .= "</td>\n";
        $persontext .= "</tr>\n";

        tng_free_result($children);
      }
      foreach ($mediatypes as $mediatype) {
        $mediatypeID = $mediatype['ID'];
        $persontext .= writeMedia($fammedia, $mediatypeID, 's');
      }
      $persontext .= writeAlbums($famalbums);

      if ($marriagerow['changedate'] || ( $allowEdit && $rightfbranch )) {
        $marriagerow['changedate'] = displayDate($marriagerow['changedate']);
        if ($allowEdit && $rightfbranch) {
          if ($marriagerow['changedate']) {
            $marriagerow['changedate'] .= ' | ';
          }
          $marriagerow['changedate'] .= "<a href=\"familiesEdit.php?familyID={$marriagerow['familyID']}&amp;cw=1\" target='_blank'>" . uiTextSnippet('edit') . '</a>';
        }
        $persontext .= showEvent(['text' => uiTextSnippet('lastmodified'), 'fact' => $marriagerow['changedate']]);
      }
      $persontext .= showEvent(['text' => uiTextSnippet('familyid'), 'date' => $marriagerow['familyID'], 'place' => "<a href=\"familiesShowFamily.php?familyID={$marriagerow['familyID']}\">" . uiTextSnippet('groupsheet') . '</a>', 'np' => 1]);
      $persontext .= "</table>\n";
      $persontext .= "<br>\n";
    }
    tng_free_result($marriages);

    $persontext .= "</div>\n";
    $persontext .= "</div>\n";


    // [ts] map section
    if ($map['key'] === true && $locations2map) {
      $persontext .= "<div class='card'>\n";
      $persontext .= "<div class='card-header' role='tab' id='headingEventMap'>\n";
      $persontext .= "<h5>\n";
      $persontext .= "<a data-toggle='collapse' data-parent='#accordion' href='#collapseEventMap' aria-expanded='true' aria-controls='collapseEventMap'>Event Map</a>\n";
      $persontext .= "</h5>\n";
      $persontext .= "</div>\n";
      $persontext .= "<div class='collapse in' id='collapseEventMap' role='tabpanel' aria-labelledby='headingEventMap'>\n";

      $persontext .= buildEventMapHtml($map, $locations2map);
      $persontext .= "</div>\n";
      $persontext .= "</div>\n";
    }
    if (!$tng_extras) {
      $media = doMediaSection($personID, $indmedia, $indalbums);
      if ($media) {
        $persontext .= "<div class='card'>\n";
        $persontext .= "<div class='card-header' role='tab' id='headingMedia'>\n";
        $persontext .= "<h5>\n";
        $persontext .= "<a data-toggle='collapse' data-parent='#accordion' href='#collapseMedia' aria-expanded='true' aria-controls='collapseMedia'>Media</a>\n";
        $persontext .= "</h5>\n";
        $persontext .= "</div>\n";
        $persontext .= "<div class='collapse' id='collapseMedia' role='tabpanel' aria-labelledby='headingMedia'>\n";
                
        $persontext .= $media . "\n";
        $persontext .= "</div>\n";
        $persontext .= "</div>\n";

      }
    }
    if ($notestogether != 1) {
      if ($rights['both']) {
        $notes = $notestogether ? buildGenNotes($indnotes, $personID, '--x-general-x--') : buildNotes($indnotes, $personID);
      }
      else {
        $notes = uiTextSnippet('livingnote');
      }

      if ($notes) {
        $persontext .= "<div class='card'>\n";
        $persontext .= "<div class='card-header' role='tab' id='headingNotes'>\n";
        $persontext .= "<h5>\n";
        $persontext .= "<a data-toggle='collapse' data-parent='#accordion' href='#collapseNotes' aria-expanded='true' aria-controls='collapseNotes'>Notes</a>\n";
        $persontext .= "</h5>\n";
        $persontext .= "</div>\n";
        $persontext .= "<div id='collapseNotes' class='collapse' role='tabpanel' aria-labelledby='headingNotes'>\n";
        
        $persontext .= "<table class='table table-sm'>\n";
        $persontext .= "<tr>\n";
        $persontext .= "<td id='notes1'><span>{$snippets['notes']}&nbsp;</span></td>\n";
        $persontext .= "<td>$notes</td>\n";
        $persontext .= "</tr>\n";
        $persontext .= "</table>\n";
        $persontext .= "</div>\n";
        $persontext .= "</div>\n";

      }
    }
    if ($citedispctr) {
      $persontext .= "<div class='card'>\n";
      $persontext .= "<div class='card-header' role='tab' id='headingSources'>\n";
      $persontext .= "<h5>\n";
      $persontext .= "<a data-toggle='collapse' data-parent='#accordion' href='#collapseSources' aria-expanded='true' aria-controls='collapseSources'>Sources</a>\n";
      $persontext .= "</h5>\n";
      $persontext .= "</div>\n";
      $persontext .= "<div class='card-block collapse in' id='collapseSources' role='tabpanel' aria-labelledby='headingSources'>\n";
      
      $persontext .= buildSourcesListHtml($citestring, $tngconfig);
      $persontext .= "</div>\n";
      $persontext .= "</div>\n";
    }
    $persontext .= "</div> <!-- #accordion -->\n";
    
    if ($media || $notes || $citedispctr || $map['key'] === true) {
      $innermenu = "<a class='navigation-item' href='#collapseInfo' data-toggle='collapse' aria-expanded='false' aria-controls='collapseInfo'>{$snippets['persinfo']}</a>\n";
      $innermenu .= "<a class='navigation-item' href='#collapseFamily' data-toggle='collapse' aria-expanded='false' aria-controls='collapseFamily'>{$snippets['family']}</a>\n";
      if ($media) {
        $innermenu .= "<a class='navigation-item' href='#collapseMedia' data-toggle='collapse' aria-expanded='false' aria-controls='collapseMedia'>{$snippets['media']}</a>\n";
      }
      if ($notes) {
        $innermenu .= "<a class='navigation-item' href='#collapseNotes' data-toggle='collapse' aria-expanded='false' aria-controls='collapseNotes'>{$snippets['notes']}</a>\n";
      }
      if ($citedispctr) {
        $innermenu .= "<a class='navigation-item' href='#collapseSources' data-toggle='collapse' aria-expanded='false' aria-controls='collapseSources'>" . uiTextSnippet('sources') . "</a>\n";
      }
      if ($map['key'] === true && $locations2map) {
        $innermenu .= "<a class='navigation-item' href='#collapseEventMap' data-toggle='collapse' aria-expanded='false' aria-controls='collapseEventMap'>" . uiTextSnippet('gmapevent') . "</a>\n";
      }
    }
    else {
      $innermenu = "<span>{$snippets['persinfo']}</span>\n";
    }
    if ($allowPdf && $rightbranch) {
      $innermenu .= "<a  class='navigation-item' href='#' onclick=\"tnglitbox = new ModalDialog('pdfReportOptions.modal.php?type=ind&amp;personID=$personID');return false;\">PDF</a>\n";
    }
    $rightbranch = $org_rightbranch;

    echo buildPersonMenu('person', $personID);
    echo "<br>\n";
    echo "<div class='pub-innermenu small'>\n";
    echo $innermenu;
    echo "</div><br>\n";
    
    echo $persontext;
    echo "</div>\n";
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->

  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
<script src="js/peopleShowPerson.js"></script>
<?php if ($map['key'] === true) { ?>
  <script src='https://maps.googleapis.com/maps/api/js?key=AIzaSyAlWTL2QZDQv9BWXBvCwdAuhq1Lak8jSwU&amp;<?php echo uiTextSnippet('localize'); ?>'></script>
<?php } ?>
<script>
  var media = '<?php echo $media; ?>';
  var citedispctr = '<?php echo $citedispctr; ?>';
  var notes = '<?php echo $notes; ?>';
  var mapKey = <?php echo $map['key']; ?>;
  var locations2Map = '<?php echo $locations2map; ?>';
  var istart = '<?php echo $tngconfig['istart']; ?>';

  if (mapKey && locations2Map && istart) {
    window.onload = function() {
      $('#eventmap').hide();
    };
  }
</script>
<script src='js/rpt_utils.js'></script>

<?php if ($tentative_edit) { ?>
  <script>
    var preferEuro = <?php echo($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : 'false'); ?>;
    var preferDateFormat = '<?php echo $preferDateFormat; ?>';
  </script>
  <script src='js/tentedit.js'></script>
  <script src='js/datevalidation.js'></script>
<?php
}  
if ($map['key'] === true && $map['pins']) {
  tng_map_pins();
}
?>
<script>
  var tnglitbox;
</script>
<script>
    var globeIcon = $('img.icon-globe');
    SVGInjector(globeIcon);
</script>

</body>
</html>
