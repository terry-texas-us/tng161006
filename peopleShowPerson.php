<?php
$needMap = true;
include("tng_begin.php");

if (!$personID) {
    header("Location: thispagedoesnotexist.html");
    exit;
}
if ($tngprint) {
    $tngconfig['istart'] = "";
    $tngconfig['hidemedia'] = "";
}
$defermap = $map['pstartoff'] || $tngconfig['istart'] ? 1 : 0;
require 'personlib.php';

$citations = array();
$citedisplay = array();
$citestring = array();
$citationctr = 0;
$citedispctr = 0;
$firstsection = 1;
$firstsectionsave = "";
$tableid = "";
$cellnumber = 0;

$indnotes = getNotes($personID, 'I');
getCitations($personID);
$stdex = getStdExtras($personID);

$result = getPersonFullPlusDates($tree, $personID);
if (!tng_num_rows($result)) {
    tng_free_result($result);
    header("Location: thispagedoesnotexist.html");
    exit;
}
$flags['imgprev'] = true;

$row = tng_fetch_assoc($result);
$righttree = checktree($tree);
$rightbranch = $righttree ? checkbranch($row['branch']) : false;
$rights = determineLivingPrivateRights($row, $righttree, $rightbranch);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];
if (!$rightbranch) {
    $tentative_edit = "";
}
$org_rightbranch = $rightbranch;
$namestr = getName($row);
$nameformap = $namestr;

$treeResult = getTreeSimple($tree);
$treerow = tng_fetch_assoc($treeResult);
$disallowgedcreate = $treerow['disallowgedcreate'];
$allowpdf = !$treerow['disallowpdf'] || ($allow_pdf && $rightbranch);
tng_free_result($treeResult);

$logname = $tngconfig['nnpriv'] && $row['private'] ? uiTextSnippet('private') : ($nonames && $row['living'] ? uiTextSnippet('living') : $namestr);
$treestr = "<a href='showtree.php?tree=$tree'>{$treerow['treename']}</a>";
if ($row['branch']) {
    //explode on commas
    $branches = explode(",", $row['branch']);
    $count = 0;
    $branchstr = "";
    foreach ($branches as $branch) {
    $count++;
    $brresult = getBranchesSimple($tree, $branch);
    $brrow = tng_fetch_assoc($brresult);
    $branchstr .= $brrow['description'] ? $brrow['description'] : $branch;
    if ($count < count($branches)) {
      $branchstr .= ", ";
    }
    tng_free_result($brresult);
    }
    if ($branchstr) {
    $treestr = $treestr . " | $branchstr";
    }
}
tng_free_result($result);

writelog("<a href='peopleShowPerson.php?personID=$personID&amp;tree=$tree'>" . uiTextSnippet('indinfofor') . " $logname ($personID)</a>");
preparebookmark("<a href='peopleShowPerson.php?personID=$personID&amp;tree=$tree'>" . uiTextSnippet('indinfofor') . " $namestr ($personID)</a>");

$headTitle = $namestr;
if ($rights['both']) {
    if ($row['birthdate']) {
    $headTitle .= " " . uiTextSnippet('birthabbr') . " " . displayDate($row['birthdate']);
    }
    if ($row['birthplace']) {
    $headTitle .= " " . $row['birthplace'];
    }
    if ($row['deathdate']) {
    $headTitle .= " " . uiTextSnippet('deathabbr') . " " . displayDate($row['deathdate']);
    }
    if ($row['deathplace']) {
    $headTitle .= " " . $row['deathplace'];
    }
}
scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle($headTitle);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php

    echo $publicHeaderSection->build();

    $indmedia = getMedia($row, 'I');
    $indalbums = getAlbums($row, 'I');

    $photostr = showSmallPhoto($personID, $namestr, $rights['both'], 0, 'I', $row['sex']);
    if ($rights['both']) {
      $citekey = $personID . "_";
      $cite = reorderCitation($citekey);
      
      if ($cite) {
        $namestr .= "<sup> $cite</sup>";
      }
    }
    echo "<div class='vcard'>\n";
    echo tng_DrawHeading($photostr, $namestr, getYears($row));

    $persontext = "";
    $persontext .= "<ul class='nopad'>\n";

    if ($tng_extras) {
      $media = doMediaSection($personID, $indmedia, $indalbums);
      if ($media) {
        $persontext .= beginListItem('media');
        $persontext .= $media . "<br>\n";
        $persontext .= endListItem('media');
      }
    }

    $persontext .= beginListItem('info');
    $persontext .= "<table class='table table-sm'>\n";
    resetEvents();
    if ($rights['both']) {
      $persontext .= showEvent(array("text" => uiTextSnippet('name'), "fact" => getName($row, true), "event" => "NAME", "entity" => $personID, "type" => 'I'));
      if ($row['title']) {
        $persontext .= showEvent(array("text" => uiTextSnippet('title'), "fact" => $row['title'], "event" => "TITL", "entity" => $personID, "type" => 'I'));
      }
      if ($row['prefix']) {
        $persontext .= showEvent(array("text" => uiTextSnippet('prefix'), "fact" => $row['prefix'], "event" => "NPFX", "entity" => $personID, "type" => 'I'));
      }
      if ($row['suffix']) {
        $persontext .= showEvent(array("text" => uiTextSnippet('suffix'), "fact" => $row['suffix'], "event" => "NSFX", "entity" => $personID, "type" => 'I'));
      }
      if ($row['nickname']) {
        $persontext .= showEvent(array("text" => uiTextSnippet('nickname'), "fact" => $row['nickname'], "event" => "NICK", "entity" => $personID, "type" => 'I'));
      }
      if ($row['private'] && $allow_edit && $allow_add && $allow_delete && !$assignedtree) {
        $persontext .= showEvent(array("text" => uiTextSnippet('private'), "fact" => uiTextSnippet('yes')));
      }
      setEvent(array("text" => uiTextSnippet('born'), "fact" => $stdex['BIRT'], "date" => $row['birthdate'], "place" => $row['birthplace'], "event" => "BIRT", "entity" => $personID, "type" => 'I'), $row['birthdatetr']);
      setEvent(array("text" => uiTextSnippet('christened'), "fact" => $stdex['CHR'], "date" => $row['altbirthdate'], "place" => $row['altbirthplace'], "event" => "CHR", "entity" => $personID, "type" => 'I'), $row['altbirthdatetr']);
    }
    if ($row['sex'] == 'M') {
      $sex = uiTextSnippet('male');
      $spouse = 'wife';
      $self = 'husband';
      $spouseorder = 'husborder';
    }
    else {if ($row['sex'] == 'F') {
      $sex = uiTextSnippet('female');
      $spouse = 'husband';
      $self = 'wife';
      $spouseorder = 'wifeorder';
    }
    else {
      $sex = uiTextSnippet('unknown');
      $spouseorder = "";
    }}
    setEvent(array("text" => uiTextSnippet('gender'), "fact" => $sex), $nodate);

    if ($rights['both']) {
      if ($rights['lds']) {
        setEvent(array("text" => uiTextSnippet('baptizedlds'), "fact" => $stdex['BAPL'], "date" => $row['baptdate'], "place" => $row['baptplace'], "event" => "BAPL", "entity" => $personID, "type" => 'I'), $row['baptdatetr']);
        setEvent(array("text" => uiTextSnippet('conflds'), "fact" => $stdex['CONL'], "date" => $row['confdate'], "place" => $row['confplace'], "event" => "CONL", "entity" => $personID, "type" => 'I'), $row['confdatetr']);
        setEvent(array("text" => uiTextSnippet('initlds'), "fact" => $stdex['INIT'], "date" => $row['initdate'], "place" => $row['initplace'], "event" => "INIT", "entity" => $personID, "type" => 'I'), $row['initdatetr']);
        setEvent(array("text" => uiTextSnippet('endowedlds'), "fact" => $stdex['ENDL'], "date" => $row['endldate'], "place" => $row['endlplace'], "event" => "ENDL", "entity" => $personID, "type" => 'I'), $row['endldatetr']);
      }
      doCustomEvents($personID, 'I');

      setEvent(array("text" => uiTextSnippet('died'), "fact" => $stdex['DEAT'], "date" => $row['deathdate'], "place" => $row['deathplace'], "event" => "DEAT", "entity" => $personID, "type" => 'I'), $row['deathdatetr']);
      $burialmsg = $row['burialtype'] ? uiTextSnippet('cremated') : uiTextSnippet('buried');
      setEvent(array("text" => $burialmsg, "fact" => $stdex['BURI'], "date" => $row['burialdate'], "place" => $row['burialplace'], "event" => "BURI", "entity" => $personID, "type" => 'I'), $row['burialdatetr']);
    }
    ksort($events);
    foreach ($events as $event) {
      $persontext .= showEvent($event);
    }
    if ($rights['both']) {
      $assocresult = getAssociations($tree, $personID);
      while ($assoc = tng_fetch_assoc($assocresult)) {
        $persontext .= showEvent(array("text" => uiTextSnippet('association'), "fact" => formatAssoc($assoc)));
      }
      tng_free_result($assocresult);
    }
    $notes = "";
    if ($notestogether == 1) {
      if ($rights['both']) {
        $notes = buildGenNotes($indnotes, $personID, "--x-general-x--");
      }
      elseif ($row['living']) {
        $notes = uiTextSnippet('livingnote');
      }
      if ($notes) {
        $persontext .= "<tr>\n";
        $persontext .= "<td id='notes1'>" . uiTextSnippet('notes') . "</td>\n";
        $persontext .= "<td colspan='2'><div class='notearea'>$notes</div></td>\n";
        $persontext .= "</tr>\n";
        $notes = ""; //wipe it out so we don't get a link at the top
      }
    }
    // [ts] stuffing $personID into `date` array element for pass to showEvent requires special text snippet processing
    //      determine if this is neccessary. 
    $persontext .= showEvent(array("text" => uiTextSnippet('personid'), "date" => $personID, "place" => $treestr, "np" => 1));
    if ($row['changedate'] || ( $allow_edit && $rightbranch )) {
      $row['changedate'] = displayDate($row['changedate'], false);
      if ($allow_edit && $rightbranch) {
        if ($row['changedate']) {
          $row['changedate'] .= " | ";
        }
        $row['changedate'] .= "<a href='peopleEdit.php?personID=$personID&amp;tree=$tree&amp;cw=1' target='_blank'>" . uiTextSnippet('edit') . "</a>";
      }
      $persontext .= showEvent(array("text" => uiTextSnippet('lastmodified'), "fact" => $row['changedate']));
    }
    $persontext .= "</table>\n";
    $persontext .= "<br>\n";

    // do parents
    $parents = getChildParentsFamily($tree, $personID);

    if ($parents && tng_num_rows($parents)) {
      while ($parent = tng_fetch_assoc($parents)) {
        $persontext .= "<table class='table table-sm'>\n";
        $tableid = "fam" . $parent['familyID'] . "_";
        $cellnumber = 0;
        resetEvents();
        getCitations($personID . "::" . $parent['familyID']);
        $gotfather = getParentData($tree, $parent['familyID'], 'husband');

        if ($gotfather) {
          $fathrow = tng_fetch_assoc($gotfather);
          $birthinfo = getBirthInfo($fathrow);
          $frights = determineLivingPrivateRights($fathrow, $righttree);
          $fathrow['allow_living'] = $frights['living'];
          $fathrow['allow_private'] = $frights['private'];
          if ($fathrow['firstname'] || $fathrow['lastname']) {
            $fathname = getName($fathrow);
            $fatherlink = "<a href='peopleShowPerson.php?personID={$fathrow['personID']}&amp;tree=$tree'>$fathname</a>";
          }
          else {
            $fatherlink = "";
          }
          if ($frights['both']) {
            $fatherlink .= $birthinfo;
            if ($fatherlink) {
              $age = age($fathrow);
              if ($age) {
                $fatherlink .= " &nbsp;(" . uiTextSnippet('age') . " $age)";
              }
            }
          }
          $label = $fathrow['sex'] == 'F' ? uiTextSnippet('mother') : uiTextSnippet('father');
          $persontext .= showEvent(array("text" => $label, "fact" => $fatherlink));
          if ($rights['both'] && $parent['frel']) {
            $rel = $parent['frel'];
            $relstr = uiTextSnippet($rel) ? uiTextSnippet($rel) : $rel;
            $persontext .= showEvent(array("text" => uiTextSnippet('relationship2'), "fact" => $relstr));
          }
          tng_free_result($gotfather);
        }
        $gotmother = getParentData($tree, $parent['familyID'], 'wife');

        if ($gotmother) {
          $mothrow = tng_fetch_assoc($gotmother);
          $birthinfo = getBirthInfo($mothrow);
          $mrights = determineLivingPrivateRights($mothrow, $righttree);
          $mothrow['allow_living'] = $mrights['living'];
          $mothrow['allow_private'] = $mrights['private'];
          if ($mothrow['firstname'] || $mothrow['lastname']) {
            $mothname = getName($mothrow);
            $motherlink = "<a href=\"peopleShowPerson.php?personID={$mothrow['personID']}&amp;tree=$tree\">$mothname</a>";
          }
          else {
            $motherlink = "";
          }
          if ($mrights['both']) {
            $motherlink .= $birthinfo;
            if ($motherlink) {
              $age = age($mothrow);
              if ($age) {
                $motherlink .= " &nbsp;(" . uiTextSnippet('age') . " $age)";
              }
            }
          }
          $label = $mothrow['sex'] == 'M' ? uiTextSnippet('father') : uiTextSnippet('mother');
          $persontext .= showEvent(array("text" => uiTextSnippet('mother'), "fact" => $motherlink));
          if ($rights['both'] && $parent['mrel']) {
            $rel = $parent['mrel'];
            $relstr = uiTextSnippet($rel) ? uiTextSnippet($rel) : $rel;
            $persontext .= showEvent(array("text" => uiTextSnippet('relationship2'), "fact" => $relstr));
          }
          tng_free_result($gotmother);
        }
        if ($rights['both'] && $rights['lds'] && $tngconfig['pardata'] < 2) {
          setEvent(array("text" => uiTextSnippet('sealedplds'), "date" => $parent['sealdate'], "place" => $parent['sealplace'], "event" => "SLGC", "entity" => "$personID::{$parent['familyID']}", "type" => "C", "nomap" => 1), $parent['sealdatetr']);
        }
        $gotparents = getFamilyData($tree, $parent['familyID']);
        $parentrow = tng_fetch_assoc($gotparents);
        tng_free_result($gotparents);
        $parent['personID'] = "";

        if ($tngconfig['pardata'] < 2) {
          $prights = determineLivingPrivateRights($parentrow, $righttree);
          $parentrow['allow_living'] = $prights['living'];
          $parentrow['allow_private'] = $prights['private'];

          if ($prights['both'] && (!$gotfather || $frights['both']) && (!$gotmother || $mrights['both'])) {
            if (!$tngconfig['pardata']) {
              $famnotes = getNotes($parent['familyID'], 'F');
              getCitations($parent['familyID']);
              $stdexf = getStdExtras($parent['familyID']);
              if ($parent['marrtype']) {
                if (!is_array($stdexf['MARR'])) {
                  $stdexf['MARR'] = array();
                }
                array_unshift($stdexf['MARR'], uiTextSnippet('type') . ": " . $parent['marrtype']);
              }
            }
            setEvent(array("text" => uiTextSnippet('married'), "fact" => $stdexf['MARR'], "date" => $parentrow['marrdate'], "place" => $parentrow['marrplace'], "event" => "MARR", "entity" => $parentrow['familyID'], "type" => 'F', "nomap" => 1), $parentrow['marrdatetr']);
            setEvent(array("text" => uiTextSnippet('divorced'), "fact" => $stdexf['DIV'], "date" => $parentrow['divdate'], "place" => $parentrow['divplace'], "event" => "DIV", "entity" => $parentrow['familyID'], "type" => 'F', "nomap" => 1), $parentrow['divdatetr']);

            if (!$tngconfig['pardata']) {
              doCustomEvents($parent['familyID'], 'F', 1);
            }
            if (!$tngconfig['pardata']) {
              $fammedia = getMedia($parentrow, 'F');
              $famalbums = getAlbums($parentrow, 'F');
            }
            ksort($events);
            foreach ($events as $event) {
              $persontext .= showEvent($event);
            }
            $assocresult = getAssociations($tree, $parent['familyID']);
            while ($assoc = tng_fetch_assoc($assocresult)) {
              $persontext .= showEvent(array("text" => uiTextSnippet('association'), "fact" => formatAssoc($assoc)));
            }
            tng_free_result($assocresult);

            if (!$tngconfig['pardata']) {
              $famnotes2 = "";
              if (!$notestogether) {
                $famnotes2 = buildNotes($famnotes, $parent['familyID']);
              }
              else {
                $famnotes2 = buildGenNotes($famnotes, $parent['familyID'], "--x-general-x--");
              }
              if ($famnotes2) {
                $persontext .= "<tr>\n";
                $persontext .= "<td>" . uiTextSnippet('notes') . "</td>\n";
                $persontext .= "<td colspan='2'><span><div class='notearea'>$famnotes2</div></span></td>\n";
                $persontext .= "</tr>\n";
              }
              foreach ($mediatypes as $mediatype) {
                $mediatypeID = $mediatype['ID'];
                $persontext .= writeMedia($fammedia, $mediatypeID, "p");
              }
              $persontext .= writeAlbums($famalbums);
            }
          }
        }
        $persontext .= showEvent(array("text" => uiTextSnippet('familyid'), "date" => $parent['familyID'], "place" => "<a href=\"familiesShowFamily.php?familyID={$parent['familyID']}&amp;tree=$tree\">" . uiTextSnippet('groupsheet') . "</a>", "np" => 1));
        $persontext .= "</table>\n";
        $persontext .= "<br>\n";
      }
      tng_free_result($parents);
    }
    // marriages
    if ($spouseorder) {
      $marriages = getSpouseFamilyFull($tree, $self, $personID, $spouseorder);
    }
    else {
      $marriages = getSpouseFamilyFullUnion($tree, $personID);
    }
    $marrtot = tng_num_rows($marriages);
    if (!$marrtot) {
      if ($spouseorder) {
        $marriages = getSpouseFamilyFullUnion($tree, $personID);
        $marrtot = tng_num_rows($marriages);
      }
      $spouseorder = 0;
    }
    $marrcount = 1;

    while ($marriagerow = tng_fetch_assoc($marriages)) {
      $persontext .= "<table class='table table-sm'>\n";
      $tableid = "fam" . $marriagerow['familyID'] . "_";
      $cellnumber = 0;
      $famnotes = getNotes($marriagerow['familyID'], 'F');
      getCitations($marriagerow['familyID']);
      $stdexf = getStdExtras($marriagerow['familyID']);
      if ($marriagerow['marrtype']) {
        if (!is_array($stdexf['MARR'])) {
          $stdexf['MARR'] = array();
        }
        array_unshift($stdexf['MARR'], uiTextSnippet('type') . ": " . $marriagerow['marrtype']);
      }

      if (!$spouseorder) {
        $spouse = $marriagerow['husband'] == $personID ? wife : husband;
      }
      unset($spouserow);
      unset($birthinfo);
      if ($marriagerow[$spouse]) {
        $spouseresult = getPersonData($tree, $marriagerow[$spouse]);
        $spouserow = tng_fetch_assoc($spouseresult);
        $birthinfo = getBirthInfo($spouserow);
        $srights = determineLivingPrivateRights($spouserow, $righttree);
        $spouserow['allow_living'] = $srights['living'];
        $spouserow['allow_private'] = $srights['private'];
        if ($spouserow['firstname'] || $spouserow['lastname']) {
          $spousename = getName($spouserow);
          $spouselink = "<a href=\"peopleShowPerson.php?personID={$spouserow['personID']}&amp;tree=$tree\">$spousename</a>";
        }
        if ($srights['both']) {
          $spouselink .= $birthinfo;
          if ($spouselink) {
            $age = age($spouserow);
            if ($age) {
              $spouselink .= " &nbsp;(" . uiTextSnippet('age') . " $age)";
            }
          }
        }
        tng_free_result($spouseresult);
      }
      else {
        $spouselink = "";
        $srights['both'] = true;
      }
      $marrstr = $marrtot > 1 ? " $marrcount" : "";
      if ($srights['both']) {
        $persontext .= showEvent(array("text" => uiTextSnippet('family') . "$marrstr", "fact" => $spouselink, "entity" => $marriagerow['familyID'], "type" => 'F'));
      }
      else {
        $persontext .= showEvent(array("text" => uiTextSnippet('family') . "$marrstr", "fact" => $spouselink));
      }
      $rightfbranch = checkbranch($marriagerow['branch']) ? 1 : 0;
      $marrights = determineLivingPrivateRights($marriagerow, $righttree);
      $marriagerow['allow_living'] = $marrights['living'];
      $marriagerow['allow_private'] = $marrights['private'];
      $fammedia = getMedia($marriagerow, 'F');
      $famalbums = getAlbums($marriagerow, 'F');
      if ($marrights['both'] && $srights['both']) {
        resetEvents();

        setEvent(array("text" => uiTextSnippet('married'), "fact" => $stdexf['MARR'], "date" => $marriagerow['marrdate'], "place" => $marriagerow['marrplace'], "event" => "MARR", "entity" => $marriagerow['familyID'], "type" => 'F'), $marriagerow['marrdatetr']);
        setEvent(array("text" => uiTextSnippet('divorced'), "fact" => $stdexf['DIV'], "date" => $marriagerow['divdate'], "place" => $marriagerow['divplace'], "event" => "DIV", "entity" => $marriagerow['familyID'], "type" => 'F'), $marriagerow['divdatetr']);

        if ($marrights['lds']) {
          setEvent(array("text" => uiTextSnippet('sealedslds'), "fact" => $stdexf['SLGS'], "date" => $marriagerow['sealdate'], "place" => $marriagerow['sealplace'], "event" => "SLGS", "entity" => $marriagerow['familyID'], "type" => 'F'), $marriagerow['sealdatetr']);
        }
        doCustomEvents($marriagerow['familyID'], 'F');
        ksort($events);
        foreach ($events as $event) {
          $persontext .= showEvent($event);
        }
        $assocresult = getAssociations($tree, $marriagerow['familyID']);
        while ($assoc = tng_fetch_assoc($assocresult)) {
          $persontext .= showEvent(array("text" => uiTextSnippet('association'), "fact" => formatAssoc($assoc)));
        }
        tng_free_result($assocresult);

        $famnotes2 = "";
        if (!$notestogether) {
          $famnotes2 = buildNotes($famnotes, $marriagerow['familyID']);
        }
        else {
          $famnotes2 = buildGenNotes($famnotes, $marriagerow['familyID'], "--x-general-x--");
        }
        if ($famnotes2) {
          $persontext .= "<tr>\n";
          $persontext .= "<td>" . uiTextSnippet('notes') . "</td>\n";
          $persontext .= "<td colspan='2'><span><div class=\"notearea\">$famnotes2</div></span></td>\n";
          $persontext .= "</tr>\n";
        }
      }
      $marrcount++;

      // children
      $children = getChildrenData($tree, $marriagerow['familyID']);

      if ($children && tng_num_rows($children)) {
        $persontext .= "<tr>\n";
        $persontext .= "<td>" . uiTextSnippet('children') . "</td>\n";
        $persontext .= "<td colspan='2'>\n";

        $kidcount = 1;
        $persontext .= "<table class='table table-sm table-striped'>\n";
        while ($child = tng_fetch_assoc($children)) {
          $childID = $child['personID'];
          $child['gedcom'] = $tree;
          $ifkids = $child['haskids'] ? "<a href=\"descend.php?personID=$childID&amp;tree=$tree\" title=\"" . uiTextSnippet('descendants') . "\" class=\"descindicator\"><strong>+</strong></a>" : "&nbsp;";
          $birthinfo = getBirthInfo($child);
          $crights = determineLivingPrivateRights($child, $righttree);
          $child['allow_living'] = $crights['living'];
          $child['allow_private'] = $crights['private'];
          if ($child['firstname'] || $child['lastname']) {
            $childname = getName($child);
            $persontext .= "<tr>\n";
            $persontext .= "<td width='10'>$ifkids</td>\n";
            $persontext .= "<td id=\"child$childID\"><span>$kidcount. <a href=\"peopleShowPerson.php?personID=$childID&amp;tree=$tree\">$childname</a>";
            if ($crights['both']) {
              $persontext .= $birthinfo;
              $age = age($child);
              if ($age) {
                $persontext .= " &nbsp;(" . uiTextSnippet('age') . " $age)";
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
        $persontext .= writeMedia($fammedia, $mediatypeID, "s");
      }
      $persontext .= writeAlbums($famalbums);

      if ($marriagerow['changedate'] || ( $allow_edit && $rightfbranch )) {
        $marriagerow['changedate'] = displayDate($marriagerow['changedate']);
        if ($allow_edit && $rightfbranch) {
          if ($marriagerow['changedate']) {
            $marriagerow['changedate'] .= " | ";
          }
          $marriagerow['changedate'] .= "<a href=\"familiesEdit.php?familyID={$marriagerow['familyID']}&amp;tree=$tree&amp;cw=1\" target='_blank'>" . uiTextSnippet('edit') . "</a>";
        }
        $persontext .= showEvent(array("text" => uiTextSnippet('lastmodified'), "fact" => $marriagerow['changedate']));
      }
      $persontext .= showEvent(array("text" => uiTextSnippet('familyid'), "date" => $marriagerow['familyID'], "place" => "<a href=\"familiesShowFamily.php?familyID={$marriagerow['familyID']}&amp;tree=$tree\">" . uiTextSnippet('groupsheet') . "</a>", "np" => 1));
      $persontext .= "</table>\n";
      $persontext .= "<br>\n";
    }
    tng_free_result($marriages);

    $persontext .= endListItem('info');

    // [ts] map section
    if ($map['key'] && $locations2map) {
      
      $persontext .= beginListItem('eventmap');
      $persontext .= "<table class='table table-sm'>\n";
      $persontext .= "<tr>\n";
      $persontext .= "<td colspan='3' class='indleftcol' id='eventmap1'><span>" . uiTextSnippet('gmapevent') . "</span></td>\n";
      $persontext .= "</tr>\n";
      $persontext .= "<tr>\n";
      $persontext .= "<td class='mapcol' colspan='2'>\n";
        $persontext .= "<div id='map' class='rounded10' style='width: {$map['indw']}; height: {$map['indh']};'>";
          if ($map['pstartoff']) {
            $persontext .= "<a href='#' onclick='ShowTheMap(); return false;'>\n";
              $persontext .= "<div class='loadmap'>" . uiTextSnippet('loadmap') . "<br>\n";
                $persontext .= "<img src='img/loadmap.gif' width='150' height='150'>\n";
              $persontext .= "</div>\n";
            $persontext .= "</a>";
          }
        $persontext .= "</div>\n";
      $persontext .= "</td>\n";
      $mapheight = (intval($map['indh']) - 40) . "px";
      $persontext .= "<td>\n";
        $persontext .= "<div style='height:{$mapheight};' id='mapevents'>\n";
          $persontext .= "<table class='table table-sm'>\n";
            asort($locations2map);
            reset($locations2map);
            $markerIcon = 0;
            $nonzeroplaces = 0;
            $usedplaces = array();
            $savedplaces = array();
            while (list($key, $val) = each($locations2map)) {
              // RM these next lines are about getting different coloured pins for different levels of place
              $placelevel = $val['placelevel'];
              $pinplacelevel = $val['pinplacelevel'];
              if (!$placelevel) {
                $placelevel = 0;
              }
              else {
                $nonzeroplaces++;
              }
              if (!$pinplacelevel) {
                $pinplacelevel = $pinplacelevel0;
              }
              $lat = $val['lat'];
              $long = $val['long'];
              $zoom = $val['zoom'] ? $val['zoom'] : 10;
              $event = $val['event'];
              $place = $val['place'];
              $dateforremoteballoon = $dateforeventtable = displayDate($val['eventdate']);
              $dateforlocalballoon = htmlspecialchars(tng_real_escape_string($dateforremoteballoon), ENT_QUOTES, $session_charset);
              $description = $val['description'];
              $balloondesc = str_replace("\n", " ", $description);
              if ($place) {
                $persontext .= "<tr>\n";
                $persontext .= "<td>\n";
                if ($lat && $long) {
                  $directionplace = htmlspecialchars(stri_replace($banish, $banreplace, $place), ENT_QUOTES, $session_charset);
                  $directionballoontext = htmlspecialchars(stri_replace($banish, $banreplace, $place), ENT_QUOTES, $session_charset);
                  if ($map['showallpins'] || !in_array($place, $usedplaces)) {
                    $markerIcon++;
                    $usedplaces[] = $place;
                    $savedplaces[] = array("place" => $place, "key" => $key);
                    $locations2map[$key]['htmlcontent'] = "<div class=\"mapballoon\"><strong>{$val['fixedplace']}</strong><br><br>$event: $dateforlocalballoon";
                    $locations2map[$key]['htmlcontent'] .= "<br><br><a href=\"https://maps.google.com/maps?f=q" .
                        uiTextSnippet('glang') . "$mcharsetstr&amp;daddr=$lat,$long($directionballoontext)&amp;z=$zoom&amp;om=1&amp;iwloc=addr\" target=\"_blank\">" .
                        uiTextSnippet('getdirections') . "</a>" . uiTextSnippet('directionsto') . " $directionplace</div>";
                    $thismarker = $markerIcon;
                  }
                  else {
                    $total = count($usedplaces);
                    for ($i = 0; $i < $total; $i++) {
                      if ($savedplaces[$i]['place'] == $place) {
                        $thismarker = $i + 1;
                        $thiskey = $savedplaces[$i]['key'];
                        $locations2map[$thiskey]['htmlcontent'] = str_replace("</div>", "<br>$event: $dateforlocalballoon</div>", $locations2map[$thiskey]['htmlcontent']);
                        break;
                      }
                    }
                  }
                  $persontext .= "<a href=\"https://maps.google.com/maps?f=q" . uiTextSnippet('glang') . "$mcharsetstr&amp;daddr=$lat,$long($directionballoontext)&amp;z=$zoom&amp;om=1&amp;iwloc=addr\" target= \"_blank\">\n";
                    $persontext .= "<img src='google_marker.php?image=$pinplacelevel.png&amp;text=$thismarker' alt='" . uiTextSnippet('googlemaplink') . "' width= '20' height='34'>\n";
                  $persontext .= "</a>\n";
                  $map['pins'] ++;
                }
                else {
                  $persontext .= "&nbsp;";
                }
                $persontext .= "</td>\n";
                $persontext .= "<td><span class='small'><strong>$event</strong>";
                if ($description) {
                  $persontext .= " - $description";
                }
                $persontext .= " - $dateforeventtable - $place</span></td>\n";
                $persontext .= "<td>\n";
                  $persontext .= "<a href='googleearthbylatlong.php?m=world&amp;n=$directionplace&amp;lon=$long&amp;lat=$lat&amp;z=$zoom'>\n";
                    $persontext .= "<img class='icon-sm icon-primary icon-globe' data-src='svg/globe.svg' alt='" . uiTextSnippet('googleearthlink') . "'>\n";
                  $persontext .= "</a>\n";
                $persontext .= "</td>\n";
                $persontext .= "</tr>\n";
                if ($val['notes']) {
                  $locations2map[$key]['htmlcontent'] = str_replace("</div>", "<br><br>" . tng_real_escape_string($val['notes']) . "</div>", $locations2map[$key]['htmlcontent']);
                }
              }
            }
          $persontext .= "</table>\n";
        $persontext .= "</div>\n";
      $persontext .= "<table class='table table-sm'>";
        $persontext .= "<tr>\n";
          $persontext .= "<td><span class='small'><img src='img/white.gif' alt='' height='15' width='9'>&nbsp;= " . uiTextSnippet('googlemaplink') . "&nbsp;</span></td>\n";
        $persontext .= "</tr>\n";
        $persontext .= "<tr>\n";
          $persontext .= "<td class='small'>\n";
            $persontext .= "<img class='icon-sm icon-muted icon-globe' data-src='svg/globe.svg' alt=''>&nbsp;= <a href='http://earth.google.com/download-earth.html' target='_blank'>" . uiTextSnippet('googleearthlink') . "</a>\n";
          $persontext .= "&nbsp;\n";
          $persontext .= "</td>\n";
        $persontext .= "</tr>\n";
      $persontext .= "</table>\n";
      $persontext .= "</td>\n</tr>\n";
      if ($nonzeroplaces) {
        $persontext .= "<tr><td>" . uiTextSnippet('gmaplegend') . "</td>\n";
        $persontext .= "<td colspan='2'><span class=\"small\">";
        for ($i = 1; $i < 7; $i++) {
          $persontext .= "<img src=\"img/" . ${"pinplacelevel" . $i} . ".png\" alt='' height=\"17\" width='10'>&nbsp;: " . uiTextSnippet("level$i") . " &nbsp;&nbsp;&nbsp;&nbsp;\n";
        }
        $persontext .= "<img src=\"img/$pinplacelevel0.png\" alt='' height='17' width='10'>&nbsp;: " . uiTextSnippet('level0') . "</span></td>\n";
        $persontext .= "</tr>\n";
      }
      $persontext .= "</table>\n";
      $persontext .= "<br>\n";
      $persontext .= endListItem('eventmap');
      
    }
    if (!$tng_extras) {
      $media = doMediaSection($personID, $indmedia, $indalbums);
      if ($media) {
        $persontext .= beginListItem('media');
        $persontext .= $media . "<br>\n";
        $persontext .= endListItem('media');
      }
    }
    if ($notestogether != 1) {
      if ($rights['both']) {
        $notes = $notestogether ? buildGenNotes($indnotes, $personID, "--x-general-x--") : buildNotes($indnotes, $personID);
      }
      else {
        $notes = uiTextSnippet('livingnote');
      }

      if ($notes) {
        $persontext .= beginListItem('notes');
        $persontext .= "<table class='table table-sm'>\n";
        $persontext .= "<tr>\n";
        $persontext .= "<td class=\"indleftcol\" id=\"notes1\"><span>" . uiTextSnippet('notes') . "&nbsp;</span></td>\n";
        $persontext .= "<td>$notes</td>\n";
        $persontext .= "</tr>\n";
        $persontext .= "</table>\n";
        $persontext .= "<br>\n";
        $persontext .= endListItem('notes');
      }
    }
    if ($citedispctr) {
      $persontext .= beginListItem('sources');
      $persontext .= "<table class='table table-sm'>\n";
      $persontext .= "<tr>\n";
      $persontext .= "<td colspan='2' class='indleftcol' id='citations1'>\n";
      $persontext .= "<a name='sources'>" . uiTextSnippet('sources') . "</a>\n";
      $persontext .= "</td>\n";
      $persontext .= "</tr>\n";
      $persontext .= "<tr>\n";
      $persontext .= "<td colspan='2'>";
      if ($tngconfig['scrollcite']) {
        $persontext .= "<div class='notearea'>";
      }
      $persontext .= "<ol class='citeblock'>";
      $citectr = 0;
      $count = count($citestring);
      foreach ($citestring as $cite) {
        $persontext .= "<li><a name='cite" . ++$citectr . "'></a>$cite<br>";
        if ($citectr < $count) {
          $persontext .= "<br>";
        }
        $persontext .= "</li>\n";
      }
      $persontext .= "</ol>";
      
      if ($tngconfig['scrollcite']) {
        $persontext .= "</div>";
      }
      $persontext .= "</td>\n";
      $persontext .= "</tr>\n";
      $persontext .= "</table>\n";
      $persontext .= "<br>\n";
      $persontext .= endListItem('citations');
    }
    $persontext .= "</ul>\n";

    if ($media || $notes || $citedispctr || $map['key']) {
      $innermenu = "<a href='#' onclick=\"return infoToggle('info');\">" . uiTextSnippet('persinfo') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
      if ($media) {
        $innermenu .= "<a href='#' onclick=\"return infoToggle('media');\">" . uiTextSnippet('media') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
      }
      if ($notes) {
        $innermenu .= "<a href='#' onclick=\"return infoToggle('notes');\">" . uiTextSnippet('notes') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
      }
      if ($citedispctr) {
        $innermenu .= "<a href='#' onclick=\"return infoToggle('sources');\">" . uiTextSnippet('sources') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
      }
      if ($map['key'] && $locations2map) {
        $innermenu .= "<a href='#' onclick=\"return infoToggle('eventmap');\">" . uiTextSnippet('gmapevent') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
      }
      $innermenu .= "<a href='#' onclick=\"return infoToggle('all');\" id=\"tng_alink\">" . uiTextSnippet('all') . "</a>\n";
    }
    else {
      $innermenu = "<span>" . uiTextSnippet('persinfo') . "</span>\n";
    }
    if ($allowpdf) {
      $innermenu .= " &nbsp;&nbsp; | &nbsp;&nbsp; <a href='#' onclick=\"tnglitbox = new ModalDialog('rpt_pdfform.php?pdftype=ind&amp;personID=$personID&amp;tree=$tree');return false;\">PDF</a>\n";
    }
    $rightbranch = $org_rightbranch;

    echo buildPersonMenu("person", $personID);
    echo "<br>\n";
    echo "<div class='pub-innermenu small'>\n";
      echo $innermenu;
    echo "</div><br>\n";
    
    echo $persontext;
    echo "</div>\n"; // .vcard
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->

  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
<script src="js/peopleShowPerson.js"></script>
<?php if ($map['key']) { ?>
  <script src='https://maps.googleapis.com/maps/api/js?language="<?php echo uiTextSnippet('glang'); ?>"'></script>
<?php } ?>
<script>
  'use strict';
  var media = '<?php echo $media; ?>';
  var citedispctr = '<?php echo $citedispctr; ?>';
  var notes = '<?php echo $notes; ?>';
  var mapKey = '<?php echo $map['key']; ?>';
  var locations2Map = '<?php echo $locations2map; ?>';
  var istart = '<?php echo $tngconfig['istart']; ?>';

  if (mapKey && locations2Map && istart) {
    window.onload = function() {
      $('#eventmap').hide();
    };
  }

  function infoToggle(part) {
    if (part === "all") {
      $('#info').show();
      if (media) {
        $('#media').show();
      }
      if (notes) {
        $('#notes').show();
      }
      if (citedispctr) {
        $('#sources').show();
      }
      if (mapKey && locations2Map) {
        $('#eventmap').show();
      }
    } else {
      innerToggle(part, "info");
      if (media) {
        innerToggle(part,"media");
      }
      if (notes) {
        innerToggle(part, "notes");
      }
      if (citedispctr) {
        innerToggle(part, "sources");
      }
      if (mapKey && locations2Map) {
        innerToggle(part, "eventmap");
      }
    }
    if (mapKey && locations2Map && istart) {
      if ((part === "eventmap" || part === "all") && !maploaded) {
        ShowTheMap();
      }
    }
    return false;
  }
</script>
<script src='js/rpt_utils.js'></script>

<?php if ($tentative_edit) { ?>
  <script>
    var preferEuro = <?php echo($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : "false"); ?>;
    var preferDateFormat = '<?php echo $preferDateFormat; ?>';
  </script>
  <script src='js/tentedit.js'></script>
  <script src='js/datevalidation.js'></script>
<?php } ?>
  
  <?php
  if ($map['key'] && $map['pins']) {
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