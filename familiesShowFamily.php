<?php
require 'tng_begin.php';

require 'personlib.php';
require 'families.php';

$firstsection = 0;
$tableid = '';
$cellnumber = 0;
$notestogether = 0; //so they always show at the bottom
$allow_lds_this = '';

$flags['imgprev'] = true;

$citations = [];
$citedisplay = [];
$citestring = [];
$citedispctr = 0;

$ldsOK = determineLDSRights();

$totcols = $ldsOK ? 6 : 3;
$factcols = $totcols - 1;

function showFact($text, $fact) {
  global $factcols;
  $facttext = "<tr>\n";
  $facttext .= '<td>' . $text . "</td>\n";
  $facttext .= "<td colspan=\"$factcols\"><span>$fact&nbsp;</span></td>\n";
  $facttext .= "</tr>\n";

  return $facttext;
}

function showDatePlace($event) {
  global $allow_lds_this;
  global $cellnumber;
  global $tentative_edit;
  global $familyID;

  $dptext = '';
  if (!$cellnumber) {
    $cellid = " id='info1'";
    $cellnumber++;
  } else {
    $cellid = '';
  }

  $dcitestr = $pcitestr = '';
  if ($event['date'] || $event['place']) {
    $citekey = $familyID . '_' . $event['event'];
    $cite = reorderCitation($citekey);
    if ($cite) {
      $dcitestr = $event['date'] ? "<sup>$cite</sup>" : '';
      $pcitestr = $event['place'] ? "<sup>$cite</sup>" : '';
    }
  }
  $dptext .= "<tr>\n";
  $editicon = $tentative_edit ? "<img class='icon-sm' src='svg/new-message.svg' alt=\"" . uiTextSnippet('editevent') . "\" onclick=\"tnglitbox = new ModalDialog('tentEdit.modal.php?persfamID={$event['ID']}&amp;type={$event['type']}&amp;event={$event['event']}&amp;title={$event['text']}');\" class=\"fakelink\">" : '';
  $dptext .= "<td $cellid><span>" . $event['text'] . "$editicon</span></td>\n";
  $dptext .= "<td colspan='2'>" . displayDate($event['date']) . " $dcitestr<br>\n";
  if ($allow_lds_this && $event['ldstext']) {
    if ($event['eventlds'] == 'div') {
      $dptext .= " colspan='4'";
    }
  }
  if ($event['place']) {
    $dptext .= buildSilentPlaceLink($event['place']);
  }
  $dptext .= " $pcitestr";
  $dptext .= "</td>\n";
  if ($allow_lds_this && $event['ldstext']) {
    if ($event['type2']) {
      $event['type'] = $event['type2'];
      $event['ID'] = $event['ID2'];
    }
    $editicon = $tentative_edit && $event['eventlds'] ? "<img class='icon-sm' src='svg/new-message.svg' alt=\"" . uiTextSnippet('editevent') . "\" onclick=\"tnglitbox = new ModalDialog('tentEdit.modal.php?persfamID={$event['ID']}&amp;type={$event['type']}&amp;event={$event['eventlds']}&amp;title={$event['ldstext']}');\" class=\"fakelink\">" : '';
    $dptext .= '<td>' . $event['ldstext'] . "$editicon</td>\n";
    $dptext .= '<td><span>' . displayDate($event['ldsdate']) . "&nbsp;</span></td>\n";
    $dptext .= '<td><span>';
    if ($event['ldsplace'] && $event['ldsplace'] != uiTextSnippet('place')) {
      $dptext .= buildSilentPlaceLink($event['ldsplace']);
    }
    $dptext .= "</span>\n";
    $dptext .= "</td>\n";
  }
  $dptext .= "</tr>\n";

  return $dptext;
}

function displayIndividual($ind, $label, $familyID, $showmarriage) {
  global $datewidth;
  global $allow_lds_this;
  global $allowEdit;
  global $personID;

  $indtext = '';

  $rightbranch = checkbranch($ind['branch']);
  $rights = determineLivingPrivateRights($ind, $rightbranch);
  $ind['allow_living'] = $rights['living'];
  $ind['allow_private'] = $rights['private'];

  $allow_lds_this = $rights['lds'];
  $haskids = $ind['haskids'] ? 'X' : '&nbsp;';
  $restriction = $familyID ? "AND familyID != \"$familyID\"" : '';
  if ($ind['sex'] == 'M') {
    $sex = uiTextSnippet('male');
  } else {
    if ($ind['sex'] == 'F') {
      $sex = uiTextSnippet('female');
    } else {
      $sex = uiTextSnippet('unknown');
    }
  }
  $namestr = getName($ind);
  $personID = $ind['personID'];

  //adjust for same-sex relationships
  if ($ind['sex'] == 'M' && $label == uiTextSnippet('wife')) {
    $label = uiTextSnippet('husband');
  } elseif ($ind['sex'] == 'F' && $label == uiTextSnippet('husband')) {
    $label = uiTextSnippet('wife');
  }
  $indtext .= "<div class='card'>\n";
  $indtext .= "<div class='card-header'>\n";
  //show photo & name
  $indtext .= showSmallPhoto($ind['personID'], $namestr, $rights['both'], 0, false, $ind['sex']);
  $indtext .= "<span>$label | $sex</span>\n";
  $indtext .= '<h4>';
  if ($ind['haskids']) {
    $indtext .= '+ ';
  }
  $indtext .= "<a href='peopleShowPerson.php?personID={$ind['personID']}'>$namestr</a>";

  if ($allowEdit && $rightbranch) {
    $indtext .= " | <a href='peopleEdit.php?personID={$ind['personID']}&amp;cw=1' target='_blank'>" . uiTextSnippet('edit') . '</a>';
  }
  $indtext .= "</h4>\n";
  $indtext .= "</div>\n"; // .card-header

  $event = '';

  $indtext .= "<table class='table table-sm'>\n";
  $indtext .= "<colgroup>\n";
  $indtext .= "<col width='10%' class='labelcol'>\n";
  $indtext .= "<col width='85%'>\n";
  //  $indtext .= "<col style='width: {$datewidth}px'>\n";
  $indtext .= "<col>\n";
  if ($allow_lds_this) {
    $indtext .= "<col style='width: 125px'>\n";
    $indtext .= "<col style='width: {$datewidth}px'>\n";
    $indtext .= "<col class='labelcol'>\n";
  }
  $indtext .= "</colgroup>\n";
  $event['text'] = uiTextSnippet('born');
  $event['event'] = 'BIRT';
  $event['type'] = 'I';
  $event['ID'] = $personID;
  $event['ldstext'] = uiTextSnippet('ldsords');
  if ($rights['both']) {
    $event['date'] = $ind['birthdate'];
    $event['place'] = $ind['birthplace'];
    if ($allow_lds_this) {
      $event['ldsdate'] = uiTextSnippet('date');
      $event['ldsplace'] = uiTextSnippet('place');
    }
  }
  $indtext .= showDatePlace($event);

  $event = '';
  $event['event'] = 'CHR';
  $event['type'] = 'I';
  $event['ID'] = $personID;
  $event['eventlds'] = 'BAPL';
  $event['ldstext'] = uiTextSnippet('baptizedlds');
  if ($rights['both']) {
    $event['date'] = $ind['altbirthdate'];
    $event['place'] = $ind['altbirthplace'];
    if ($allow_lds_this) {
      $event['ldsdate'] = $ind['baptdate'];
      $event['ldsplace'] = $ind['baptplace'];
    }
  }
  if ((isset($event['date']) && $event['date']) || (isset($event['place']) && $event['place']) || isset($event['ldsdate']) || isset($event['ldsplace'])) {
    $event['text'] = uiTextSnippet('christened');
    $indtext .= showDatePlace($event);
  }

  $event = '';
  $event['text'] = uiTextSnippet('died');
  $event['event'] = 'DEAT';
  $event['type'] = 'I';
  $event['ID'] = $personID;
  $event['eventlds'] = 'ENDL';
  $event['ldstext'] = uiTextSnippet('endowedlds');
  if ($rights['both']) {
    $event['date'] = $ind['deathdate'];
    $event['place'] = $ind['deathplace'];
    if ($allow_lds_this) {
      $event['ldsdate'] = $ind['endldate'];
      $event['ldsplace'] = $ind['endlplace'];
    }
  }
  $indtext .= showDatePlace($event);

  $event = '';
  $event['text'] = $ind['burialtype'] ? uiTextSnippet('cremated') : uiTextSnippet('buried');
  $event['event'] = 'BURI';
  $event['type'] = 'I';
  $event['ID'] = $personID;
  $event['eventlds'] = 'SLGC';
  $event['ldstext'] = uiTextSnippet('sealedplds');
  if ($rights['both']) {
    $event['date'] = $ind['burialdate'];
    $event['place'] = $ind['burialplace'];
    if ($allow_lds_this) {
      if ($familyID) {
        $query = "SELECT sealdate, sealplace FROM children WHERE familyID = \"{$ind['famc']}\" AND personID = \"{$ind['personID']}\"";
        $cresult = tng_query($query);
        $sealinfo = tng_fetch_assoc($cresult);
        $ind['sealdate'] = $sealinfo['sealdate'];
        $ind['sealplace'] = $sealinfo['sealplace'];
        tng_free_result($cresult);
      }
      $event['type2'] = 'C';
      $event['ID2'] = "$personID::{$ind['famc']}";
      $event['ldsdate'] = $ind['sealdate'];
      $event['ldsplace'] = $ind['sealplace'];
    }
  }
  $indtext .= showDatePlace($event);

  //show marriage & sealing if $showmarriage
  $query = "SELECT marrdate, marrplace, divdate, divplace, sealdate, sealplace, living, private, branch, marrtype FROM families WHERE familyID = '$familyID'";
  $result = tng_query($query);
  $fam = tng_fetch_assoc($result);
  if ($familyID || $fam['marrtype']) {
    if ($showmarriage) {
      $famrights = determineLivingPrivateRights($fam);
      $fam['allow_living'] = $famrights['living'];
      $fam['allow_private'] = $famrights['private'];

      tng_free_result($result);

      $event = '';
      $eventd = '';
      $event['text'] = uiTextSnippet('married');
      $event['event'] = 'MARR';
      $event['type'] = 'F';
      $event['ID'] = $familyID;
      $event['eventlds'] = 'SLGS';
      $event['ldstext'] = uiTextSnippet('sealedslds');
      if ($famrights['both'] && $rights['both']) {
        $event['date'] = $fam['marrdate'];
        $event['place'] = $fam['marrplace'];
        if ($allow_lds_this) {
          $event['ldsdate'] = $fam['sealdate'];
          $event['ldsplace'] = $fam['sealplace'];
        }
        $eventd['event'] = 'DIV';
        $eventd['text'] = uiTextSnippet('divorced');
        $eventd['date'] = $fam['divdate'];
        $eventd['place'] = $fam['divplace'];
      }
      $indtext .= showDatePlace($event);
      $eventd['ldstext'] = '';
      $eventd['eventlds'] = 'div';
      if ($eventd['date'] || $eventd['place']) {
        $indtext .= showDatePlace($eventd);
      }

      if ($fam['marrtype'] && $famrights['both'] && $rights['both']) {
        $indtext .= showFact(uiTextSnippet('type'), $fam['marrtype']);
      }
    }
    $spousetext = uiTextSnippet('otherspouse');
  } else {
    $spousetext = uiTextSnippet('spouse');
  }

  //show other spouses
  $query = 'SELECT familyID, personID, firstname, lnprefix, lastname, prefix, suffix, nameorder, families.living AS fliving, families.private AS fprivate, families.branch AS branch, people.living AS living, people.private AS private, marrdate, marrplace, sealdate, sealplace, marrtype FROM families ';
  if ($ind['sex'] == 'M') {
    $query .= "LEFT JOIN people ON families.wife = people.personID WHERE husband = '{$ind['personID']}' $restriction ORDER BY husborder";
  } else {
    if ($ind['sex'] == 'F') {
      $query .= "LEFT JOIN people ON families.husband = people.personID WHERE wife = '{$ind['personID']}' $restriction ORDER BY wifeorder";
    } else {
      $query .= "LEFT JOIN people ON (families.husband = people.personID OR families.wife = people.personID) WHERE (wife = '{$ind['personID']}' && husband = '{$ind['personID']}')";
    }
  }
  $spresult = tng_query($query);

  while ($fam = tng_fetch_assoc($spresult)) {
    $famrights = determineLivingPrivateRights($fam);
    $fam['allow_living'] = $famrights['living'];
    $fam['allow_private'] = $famrights['private'];

    $spousename = getName($fam);
    $spouselink = $spousename ? "<a href=\"peopleShowPerson.php?personID={$fam['personID']}\">$spousename</a> | " : '';
    $spouselink .= "<a href=\"familiesShowFamily.php?familyID={$fam['familyID']}\">{$fam['familyID']}</a>";

    $fam['living'] = $fam['fliving'];
    $fam['private'] = $fam['fprivate'];
    $famrights = determineLivingPrivateRights($fam);
    $fam['allow_living'] = $famrights['living'];
    $fam['allow_private'] = $famrights['private'];

    if ($famrights['both'] && $rights['both'] && $fam['marrtype']) {
      $spouselink .= " ({$fam['marrtype']})";
    }
    $indtext .= showFact($spousetext, $spouselink);

    $event = '';
    $event['text'] = uiTextSnippet('married');
    $event['event'] = 'MARR';
    $event['type'] = 'F';
    $event['ID'] = $fam['familyID'];
    $event['eventlds'] = 'SLGS';
    $event['ldstext'] = uiTextSnippet('sealedslds');
    if ($famrights['both'] && $rights['both']) {
      $event['date'] = $fam['marrdate'];
      $event['place'] = $fam['marrplace'];
      if ($allow_lds_this) {
        $event['ldsdate'] = $fam['sealdate'];
        $event['ldsplace'] = $fam['sealplace'];
      }
    }
    $indtext .= showDatePlace($event);
  }

  //show parents (for hus&wif)
  if ($familyID) {
    $query = "SELECT familyID, personID, firstname, lnprefix, lastname, prefix, suffix, nameorder, people.living, people.private, people.branch FROM (families, people) WHERE families.familyID = \"{$ind['famc']}\" AND people.personID = families.husband";
    $presult = tng_query($query);
    $parent = tng_fetch_assoc($presult);

    $prights = determineLivingPrivateRights($parent);
    $parent['allow_living'] = $prights['living'];
    $parent['allow_private'] = $prights['private'];

    $fathername = getName($parent);
    tng_free_result($presult);
    $fatherlink = $fathername ? "<a href=\"peopleShowPerson.php?personID={$parent['personID']}\">$fathername</a> | " : '';
    $fatherlink .= $fathername ? "<a href=\"familiesShowFamily.php?familyID={$parent['familyID']}\">{$parent['familyID']} " . uiTextSnippet('groupsheet') . '</a>' : '';
    $indtext .= showFact(uiTextSnippet('father'), $fatherlink);

    $query = "SELECT familyID, personID, firstname, lnprefix, lastname, prefix, suffix, nameorder, people.living, people.private, people.branch FROM (families, people) WHERE families.familyID = \"{$ind['famc']}\" AND people.personID = families.wife";
    $presult = tng_query($query);
    $parent = tng_fetch_assoc($presult);

    $prights = determineLivingPrivateRights($parent);
    $parent['allow_living'] = $prights['living'];
    $parent['allow_private'] = $prights['private'];

    $mothername = getName($parent);
    tng_free_result($presult);
    $motherlink = $mothername ? "<a href=\"peopleShowPerson.php?personID={$parent['personID']}\">$mothername</a> | " : '';
    $motherlink .= $mothername ? "<a href=\"familiesShowFamily.php?familyID={$parent['familyID']}\">{$parent['familyID']} " . uiTextSnippet('groupsheet') . '</a>' : '';
    $indtext .= showFact(uiTextSnippet('mother'), $motherlink);
  }
  $indtext .= "</table>\n";
  $indtext .= "</div>\n";
  $indtext .= "<br>\n";

  return $indtext;
}

$query = "SELECT familyID, husband, wife, living, private, marrdate, branch FROM families WHERE familyID = '$familyID'";
$result = tng_query($query);
$famrow = tng_fetch_assoc($result);
if (!tng_num_rows($result)) {
  tng_free_result($result);
  header('Location: thispagedoesnotexist.html');
  exit;
} else {
  tng_free_result($result);
}
$rightbranch = checkbranch($famrow['branch']);
$rights = determineLivingPrivateRights($famrow, $rightbranch);
$famrow['allow_living'] = $rights['living'];
$famrow['allow_private'] = $rights['private'];

$famname = getFamilyName($famrow);
$namestr = uiTextSnippet('family') . ': ' . $famname;
if (!$rightbranch) {
  $tentative_edit = '';
}

$logstring = "<a href=\"familiesShowFamily.php?familyID=$familyID\">" . uiTextSnippet('familygroupfor') . " $famname</a>";
writelog($logstring);
preparebookmark($logstring);

$famnotes = getNotes($familyID, 'F');

$years = $famrow['marrdate'] && $rights['both'] ? uiTextSnippet('marrabbr') . ' ' . displayDate($famrow['marrdate']) : '';
if ($rights['both']) {
  getCitations($familyID);
  $citekey = $familyID . '_';
  $cite = reorderCitation($citekey);
  if ($cite) {
    $namestr .= "<sup>&nbsp; [$cite]&nbsp;</sup>";
  }
}
$headTitle = uiTextSnippet('familygroupfor') . " $famname";
if ($rights['both']) {
  $headTitle .= " $years ";
}
scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle($headTitle);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();
    $photostr = showSmallPhoto($familyID, $famname, $rights['both'], 0);
    echo tng_DrawHeading($photostr, $namestr, $years);

    $famtext = '';
    $personID = $famrow['husband'] ? $famrow['husband'] : $famrow['wife'];
    $fammedia = getMedia($famrow, 'F');
    $famalbums = getAlbums($famrow, 'F');

    //    $famtext .= "<ul>\n";
    //    $famtext .= beginListItem('info');

    //get husband & spouses
    if ($famrow['husband']) {
      $query = "SELECT * FROM people WHERE personID = \"{$famrow['husband']}\"";
      $result = tng_query($query);
      $husbrow = tng_fetch_assoc($result);
      $label = $husbrow['sex'] != 'F' ? uiTextSnippet('husband') : uiTextSnippet('wife');
      $famtext .= displayIndividual($husbrow, $label, $familyID, 1);
      tng_free_result($result);
    }

    //get wife & spouses
    if ($famrow['wife']) {
      $query = "SELECT * FROM people WHERE personID = \"{$famrow['wife']}\"";
      $result = tng_query($query);
      $wiferow = tng_fetch_assoc($result);
      $label = $husbrow['sex'] != 'M' ? uiTextSnippet('wife') : uiTextSnippet('husband');
      $famtext .= displayIndividual($wiferow, $label, $familyID, 0);
      tng_free_result($result);
    }

    //for each child
    $query = "SELECT people.personID AS personID, branch, firstname, lnprefix, lastname, prefix, suffix, nameorder, living, private, famc, sex, birthdate, birthplace, altbirthdate, altbirthplace, haskids, deathdate, deathplace, burialdate, burialplace, burialtype, baptdate, baptplace, endldate, endlplace, sealdate, sealplace FROM people, children WHERE people.personID = children.personID AND children.familyID = \"{$famrow['familyID']}\" ORDER BY ordernum";
    $children = tng_query($query);

    if ($children && tng_num_rows($children)) {
      $childcount = 0;
      while ($childrow = tng_fetch_assoc($children)) {
        $childcount++;
        $famtext .= displayIndividual($childrow, uiTextSnippet('child') . " $childcount", '', 1);
      }
    }
    tng_free_result($children);

    //    $famtext .= endListItem('info');

    $firstsection = 1;
    $firstsectionsave = '';

    $assoctext = '';
    if ($rights['both']) {
      $query = "SELECT passocID, relationship, reltype FROM associations WHERE personID = '$familyID'";
      $assocresult = tng_query($query);
      while ($assoc = tng_fetch_assoc($assocresult)) {
        $assoctext .= showEvent(['text' => uiTextSnippet('association'), 'fact' => formatAssoc($assoc)]);
      }
      tng_free_result($assocresult);
      if ($assoctext) {
        $famtext .= beginListItem('assoc');
          $famtext .= "<div class='container'>\n";
            $famtext .= "<table class='table table-sm'>\n";
              $famtext .= "$assoctext\n";
            $famtext .= "</table>\n";
          $famtext .= "</div>\n<br>\n";
        $famtext .= endListItem('assoc');
      }
    }
    $media = doMediaSection($familyID, $fammedia, $famalbums);
    if ($media) {
      $famtext .= beginListItem('media');
        $famtext .= "<div class='titlebox'>\n$media\n</div>\n<br>\n";
      $famtext .= endListItem('media');
    }
    if ($rights['both']) {
      $notes = buildNotes($famnotes, $familyID);

      if ($notes) {
        $famtext .= beginListItem('notes');
          $famtext .= "<div class='container'>\n";
          $famtext .= "<table class='table table-sm'>\n";
            $famtext .= "<tr>\n";
              $famtext .= "<td class='indleftcol' id='notes1' style='width: 100px'>" . uiTextSnippet('notes') . "</td>\n";
              $famtext .= "<td colspan='2'>$notes</td>\n";
            $famtext .= "</tr>\n";
          $famtext .= "</table>\n</div>\n<br>\n";
        $famtext .= endListItem('notes');
      }
      if ($citedispctr) {
        $famtext .= beginListItem('citations');
        $famtext .= "<table class='table table-sm'>\n";
        $famtext .= "<tr>\n";
        $famtext .= "<td colspan='2' class='indleftcol' id='citations1'>\n";
        $famtext .= "<a name='sources'>" . uiTextSnippet('sources') . "</a>\n";
        $famtext .= "</td>\n";
        $famtext .= "</tr>\n";
        $famtext .= "<tr>\n";
        $famtext .= "<td colspan='2'>\n";

        $famtext .= "<ol class='citeblock'>";
        $citectr = 0;
        $count = count($citestring);
        foreach ($citestring as $cite) {
          $famtext .= "<li><a name='cite" . ++$citectr . "'></a>$cite<br>";
          if ($citectr < $count) {
            $famtext .= '<br>';
          }
          $famtext .= "</li>\n";
        }
        $famtext .= "</ol>\n";

        $famtext .= "</td>\n";
        $famtext .= "</tr>\n";
        $famtext .= "</table>\n";
        $famtext .= "<br>\n";
        $famtext .= endListItem('citations');
      }
    } elseif ($rights['both']) {
      $famtext .= beginListItem('notes');
      $famtext .= "<div class='container'>\n";
      $famtext .= "<table class='table table-sm'>\n";
      $famtext .= "<tr>\n";
      $famtext .= "<td class='indleftcol' id='notes1' style='width: 100px'>" . uiTextSnippet('notes') . "</td>\n";
      $famtext .= "<td colspan='2'>" . uiTextSnippet('livingnote') . "</td>\n";
      $famtext .= "</tr>\n";
      $famtext .= "</table>\n</div>\n<br>\n";
      $famtext .= endListItem('notes');
      $notes = true;
    }
    //    $famtext .= "</ul>\n";

    if ($media || $notes || $citedispctr || $assoctext) {
      if ($tngconfig['istart']) {
      } else {
      }
      $innermenu = "<a class='navigation-item' href='#' onclick=\"return infoToggle('info');\">" . uiTextSnippet('faminfo') . "</a>\n";
      if ($media) {
        $innermenu .= "<a class='navigation-item' href='#' onclick=\"return infoToggle('media');\">" . uiTextSnippet('media') . "</a>\n";
      }
      if ($assoctext) {
        $innermenu .= "<a class='navigation-item' href='#' onclick=\"return infoToggle('assoc');\">" . uiTextSnippet('association') . "</a>\n";
      }
      if ($notes) {
        $innermenu .= "<a class='navigation-item' href='#' onclick=\"return infoToggle('notes');\">" . uiTextSnippet('notes') . "</a>\n";
      }
      if ($citedispctr) {
        $innermenu .= "<a class='navigation-item' href='#' onclick=\"return infoToggle('citations');\">" . uiTextSnippet('citations') . "</a>\n";
      }
      $innermenu .= "<a class='navigation-item' href='#' onclick=\"return infoToggle('all');\">" . uiTextSnippet('all') . "</a>\n";
    } else {
      $innermenu = '<span>' . uiTextSnippet('faminfo') . "</span>\n";
    }
    if ($allowPdf && $rightbranch) {
      $innermenu .= "<a class='navigation-item' href='#' onclick=\"tnglitbox = new ModalDialog('pdfReportOptions.modal.php?type=fam&amp;familyID=$familyID');return false;\">PDF</a>\n";
    }
    echo buildFamilyMenu('family', $familyID);
    echo "<div class='pub-innermenu small'>\n";
      echo $innermenu;
    echo "</div>\n";
    echo "<br>\n";
    echo $famtext;
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script src="js/rpt_utils.js"></script>
  <?php if ($tentative_edit) { ?>
    <script>
      var preferEuro = <?php echo ($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : 'false'); ?>;
      var preferDateFormat = '<?php echo $preferDateFormat; ?>';
    </script>
    <script src="js/tentedit.js"></script>
    <script src="js/datevalidation.js"></script>
  <?php } ?>
  <script>
    var media = '<?php echo $media; ?>';
    var citedispctr = '<?php echo $citedispctr; ?>';
    var notes = '<?php echo $notes; ?>';
    var assoctext = '<?php echo $assoctext; ?>';

    function innerToggle(part, subpart) {
      if (part === subpart)
        turnOn(subpart);
      else
        turnOff(subpart);
    }

    function turnOn(subpart) {
      $('#' + subpart).show();
    }

    function turnOff(subpart) {
      $('#' + subpart).hide();
    }

    function infoToggle(part) {
      if (part === "all") {
        $('#info').show();
        if (media) {
          $('#media').show();
        }
        if (assoctext) {
          $('#assoc').show();
        }
        if (notes) {
          $('#notes').show();
        }
        if (citedispctr) {
          $('#citations').show();
        }
      } else {
        innerToggle(part, "info");
        if (media) {
          innerToggle(part, 'media');
        }
        if (assoctext) {
          innerToggle(part, 'assoc');
        }
        if (notes) {
          innerToggle(part, 'notes');
        }
        if (citedispctr) {
          innerToggle(part, 'citations');
        }
      }
      return false;
    }
  </script>
</body>
</html>
