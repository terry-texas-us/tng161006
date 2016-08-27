<?php
require 'version.php';

require_once 'pwdlib.php';
require_once 'globallib.php';
require_once 'mediatypes.php';
require_once 'tngfiletypes.php';
checkMaintenanceMode(1);

if (isset($map['key']) && $map['key']) {
  include_once 'googlemaplib.php';
}
require_once 'classes/headElementSection.php';
require_once 'classes/adminNavElementSection.php';
require_once 'classes/adminHeaderElementSection.php';
require_once 'classes/footerElementSection.php';
require_once 'classes/scriptsManager.php';
require_once 'classes/navList.php';

$headSection = new HeadElementSection($sitename);

NavElementSection::maintenanceState(isset($tngconfig['maint']) && $tngconfig['maint'] != "", uiTextSnippet('mainton'));

//NavElementSection::allowAdmin(isset($allow_admin) && $allow_admin != "");
NavElementSection::currentUser($currentuser);
NavElementSection::homePage($homepage);
NavElementSection::helpPath(findhelp('index_help.php') . "/index_help.php");

$adminHeaderSection = new AdminHeaderElementSection();
$adminHeaderSection->setTitle($tng_title);
$adminHeaderSection->setVersion($tng_version);

$adminNavSection = new AdminNavElementSection('admin');

// [ts] magic vocabulary text

$adminNavSection->adminhome = uiTextSnippet('adminhome');
$adminNavSection->publichome = uiTextSnippet('publichome');
$adminNavSection->setup = uiTextSnippet('setup');
$adminNavSection->people = uiTextSnippet('people');
$adminNavSection->places = uiTextSnippet('places');
$adminNavSection->sources = uiTextSnippet('sources');
$adminNavSection->families = uiTextSnippet('families');
$adminNavSection->showlog = uiTextSnippet('showlog');
$adminNavSection->users = uiTextSnippet('users');
$adminNavSection->getstart = uiTextSnippet('getstart');
$adminNavSection->logout = uiTextSnippet('logout');

$adminFooterSection = new FooterElementSection('admin');
$scriptsManager = new scriptsManager('admin');

function getNewNumericID($type, $field, $table) {
  include 'prefixes.php';

  eval("\$prefix = \$$type" . "prefix;");
  eval("\$suffix = \$$type" . "suffix;");
  if ($prefix) {
    $prefixlen = strlen($prefix) + 1;
    $query = "SELECT MAX(0+SUBSTRING($field" . "ID,$prefixlen)) AS newID FROM $table WHERE $field" . "ID LIKE \"$prefix%\"";
  } else {
    $query = "SELECT MAX(0+SUBSTRING_INDEX($field" . "ID,'$suffix',1)) AS newID FROM $table";
  }
  $result = tng_query($query);
  $maxrow = tng_fetch_array($result);
  tng_free_result($result);

  $newID = $maxrow['newID'] + 1;

  return $newID;
}

function findhelp($helpfile) {
  global $mylanguage;
  global $language;

  $helplang = "languages/English-UTF8";

  if (file_exists("$mylanguage/$helpfile")) {
    $helplang = $mylanguage;
  } elseif (file_exists("languages/$language/$helpfile")) {
    $helplang = "languages/$language";
  }
  return $helplang;
}

function checkReview($type) {
  global $people_table;
  global $families_table;
  global $temp_events_table;
  global $assignedbranch;

  if ($type == 'I') {
    $revwhere = "$people_table.personID = $temp_events_table.personID AND (type = 'I' OR type = 'C')";
    $table = $people_table;
  } else {
    $revwhere = "$families_table.familyID = $temp_events_table.familyID AND type = 'F'";
    $table = $families_table;
  }
  if ($assignedbranch) {
    $revwhere .= " AND branch LIKE \"%$assignedbranch%\"";
  }
  $revquery = "SELECT count(tempID) AS tcount FROM ($table, $temp_events_table) WHERE $revwhere";
  $revresult = tng_query($revquery) or die(uiTextSnippet('cannotexecutequery') . ": $revquery");
  $revrow = tng_fetch_assoc($revresult);
  tng_free_result($revresult);

  return $revrow['tcount'] ? " *" : "";
}

function deleteNote($noteID, $flag) {
  global $notelinks_table;
  global $xnotes_table;

  $query = "SELECT xnoteID FROM $notelinks_table WHERE ID=\"$noteID\"";
  $result = tng_query($query);
  $nrow = tng_fetch_assoc($result);
  tng_free_result($result);

  $query = "SELECT count(ID) AS xcount FROM $xnotes_table WHERE ID=\"{$nrow['xnoteID']}\"";
  $result = tng_query($query);
  $xrow = tng_fetch_assoc($result);
  tng_free_result($result);

  if ($xrow['xcount'] == 1) {
    $query = "DELETE FROM $xnotes_table WHERE ID=\"{$nrow['xnoteID']}\"";
    tng_query($query);
  }
  if ($flag) {
    $query = "DELETE FROM $notelinks_table WHERE ID=\"$noteID\"";
    tng_query($query);
  }
}

function displayToggle($id, $state, $target, $headline, $subhead) {
  $rval = "<p class='togglehead'>\n";
  $rval .= "<a href='#' onclick=\"return toggleSection('$target','$id');\">\n";
  $rval .= "<img src=\"img/" . ($state ? "tng_collapse.gif" : "tng_expand.gif") . "\" title=\"" . uiTextSnippet('toggle') . "\" alt=\"" . uiTextSnippet('toggle') . "\" width='15' height='15' id=\"$id\" /></a>";
  $rval .= "<span class='th-indent'>$headline</span>\n";
  $rval .= "</p>\n";
  if ($subhead) {
    $rval .= "<span class='tsh-indent'><i>$subhead</i></span><br>\n";
  }
  return $rval;
}

function displayListLocation($start, $pagetotal, $grandtotal) {
  $rval = "<p>" . uiTextSnippet('matches') . ': ' . number_format($start) . " " . uiTextSnippet('to') . " <span class='pagetotal'>" . number_format($pagetotal) . "</span> " . uiTextSnippet('of') . " <span class='restotal'>" . number_format($grandtotal) . "</span></p>\n";
  return $rval;
}

function showEventRow($datefield, $placefield, $label, $persfamID) {
  global $gotmore;
  global $gotnotes;
  global $gotcites;
  global $row;
  global $noclass;

  $ldsarray = ["BAPL", "CONL", "INIT", "ENDL", "SLGS", "SLGC"];

  $short = " style='width: 100px'";
  $long = $noclass ? " style='width: 270px'" : " class='longfield'";

  $tr = "<tr>\n";
  $tr .= "<td>" . uiTextSnippet($label) . ":</td>\n";
  $tr .= "<td><input name=\"$datefield\" type='text' value=\"" . $row[$datefield] . "\" onblur=\"checkDate(this);\" maxlength=\"50\"$short /></td>\n";
  $tr .= "<td><input id=\"$placefield\"$long name=\"$placefield\" type='text' value=\"" . $row[$placefield] . "\" /></td>\n";
  if (in_array($label, $ldsarray)) {
    $tr .= "<td>\n";
    $tr .= "<a href='#' onclick=\"return openFindPlaceForm('$placefield', 1);\" title=\"" . uiTextSnippet('find') . "\">\n";
    $tr .= "<img class='icon-sm' src='svg/temple.svg'>\n";
    $tr .= "</a>\n";
    $tr .= "</td>\n";
  } else {
    $tr .= "<td>\n";
    $tr .= "<a href='#' onclick=\"return openFindPlaceForm('$placefield');\" title='" . uiTextSnippet('find') . "'>\n";
    $tr .= "<img class='icon-sm' src='svg/magnifying-glass.svg'>\n";
    $tr .= "</a></td>\n";
  }
  if (isset($gotmore)) {
    $tr .= "<td>\n";

    $iconColor = $gotmore[$label] ? "icon-info" : "icon-muted";
    $tr .= "<a class='event-more' href='#' title='" . uiTextSnippet('more') . "' data-event-id='$label' data-persfam-id='$persfamID'>\n";
    $tr .= "<img class='icon-sm icon-right icon-more $iconColor' data-event-id='$label' data-src='svg/plus.svg'>\n";
    $tr .= "</a>\n";

    $tr .= "</td>\n";
  }
  if (isset($gotnotes)) {
    $tr .= "<td>\n";

    $iconColor = $gotnotes[$label] ? "icon-info" : "icon-muted";
    $tr .= "<a class='event-notes' href='#' title='" . uiTextSnippet('notes') . "' data-event-id='$label' data-persfam-id='$persfamID'>\n";
    $tr .= "<img class='icon-sm icon-right icon-notes $iconColor' data-src='svg/documents.svg'>\n";
    $tr .= "</a>\n";

    $tr .= "</td>\n";
  }
  if (isset($gotcites)) {
    $tr .= "<td>\n";

    $iconColor = $gotcites[$label] ? "icon-info" : "icon-muted";
    $tr .= "<a class='event-citations' href='#' title='" . uiTextSnippet('citations') . "' data-event-id='$label' data-persfam-id='$persfamID'>\n";
    $tr .= "<img class='icon-sm icon-right icon-citations $iconColor' data-src='svg/archive.svg'>\n";
    $tr .= "</a>\n";

    $tr .= "</td>\n";
  }
  $tr .= "</tr>\n";
  return $tr;
}

function buildEventRow($datefield, $placefield, $label, $persfamID) {
  global $gotmore;
  global $gotnotes;
  global $gotcites;
  global $row;
  
  $ldsarray = ["BAPL", "CONL", "INIT", "ENDL", "SLGS", "SLGC"];

  $out = "<div class='row'>\n";
  $out .= "<div class='col-md-2'>" . uiTextSnippet($label) . ":</div>\n";
  $out .= "<div class='col-md-2'>\n";
  $out .= "<input class='form-control form-control-sm' name='$datefield' type='text' value=\"" . $row[$datefield] . "\" onblur=\"checkDate(this);\" maxlength='50' placeholder='" . uiTextSnippet('date') . "'>\n";
  $out .= "</div>\n";
  
  $out .= "<div class='col-md-5'>\n";
  $out .= "<input class='form-control form-control-sm' id='$placefield' name='$placefield' type='text' value='" . $row[$placefield] . "' placeholder='" . uiTextSnippet('place') . "'>\n";
  $out .= "</div>\n";
  
  $out .= "<div class='col-md-3'>";
  if (in_array($label, $ldsarray)) {
    $out .= "<a href='#' onclick=\"return openFindPlaceForm('$placefield', 1);\" title='" . uiTextSnippet('find') . "'>\n";
    $out .= "<img class='icon-sm' src='svg/temple.svg'>\n";
    $out .= "</a>\n";
  } else {
    $out .= "<a href='#' data-place-field='$placefield' onclick=\"return openFindPlaceForm('$placefield');\" title='" . uiTextSnippet('find') . "'>\n";
    $out .= "<img class='icon-sm' src='svg/magnifying-glass.svg'>\n";
    $out .= "</a>\n";
  }
  if (isset($gotmore)) {
    $iconColor = $gotmore[$label] ? "icon-info" : "icon-muted";
    $out .= "<a class='event-more' href='#' title='" . uiTextSnippet('more') . "' data-event-id='$label' data-persfam-id='$persfamID'>\n";
    $out .= "<img class='icon-sm icon-right icon-more $iconColor' data-event-id='$label' data-src='svg/plus.svg'>\n";
    $out .= "</a>\n";
  }
  if (isset($gotnotes)) {
    $iconColor = $gotnotes[$label] ? "icon-info" : "icon-muted";
    $out .= "<a class='event-notes' href='#' title='" . uiTextSnippet('notes') . "' data-event-id='$label' data-persfam-id='$persfamID'>\n";
    $out .= "<img class='icon-sm icon-right icon-notes $iconColor' data-event-id='$label' data-src='svg/documents.svg'>\n";
    $out .= "</a>\n";
  }
  if (isset($gotcites)) {
    $iconColor = $gotcites[$label] ? "icon-info" : "icon-muted";
    $out .= "<a class='event-citations' href='#' title='" . uiTextSnippet('citations') . "' data-event-id='$label' data-persfam-id='$persfamID'>\n";
    $out .= "<img class='icon-sm icon-right icon-citations $iconColor' data-event-id='$label' data-src='svg/archive.svg'>\n";
    $out .= "</a>\n";
  }
  $out .= "</div>\n";
  $out .= "</div>\n";
  return $out;
}

function cleanID($id) {
  return preg_replace('/[^a-z0-9_-]/', '', strtolower($id));
}

function determineConflict($row, $table) {
  global $currentuser;
  global $tngconfig;

  $editconflict = false;
  $currenttime = time();
  if ($row['edituser'] && $row['edituser'] != $currentuser) {
    if ($tngconfig['edit_timeout'] === "") {
      $tngconfig['edit_timeout'] = 15;
    }
    $expiretime = $row['edittime'] + (intval($tngconfig['edit_timeout']) * 60);

    if ($expiretime > $currenttime) {
      $editconflict = true;
    }
  }
  if (!$editconflict) {
    $query = "UPDATE $table SET edituser = \"$currentuser\", edittime = \"$currenttime\" WHERE ID = \"{$row['ID']}\"";
    tng_query($query);
  }
  return $editconflict;
}

function getHasKids($personID) {
  global $families_table;
  global $children_table;

  $haskids = 0;
  $query = "SELECT familyID FROM $families_table WHERE husband = '$personID' UNION
    SELECT familyID FROM $families_table WHERE wife = '$personID'";
  $fresult = tng_query($query);
  while ($famrow = tng_fetch_assoc($fresult)) {
    $query = "SELECT personID FROM $children_table WHERE familyID = \"{$famrow['familyID']}\"";
    $cresult = tng_query($query);
    $ccount = tng_num_rows($cresult);
    tng_free_result($cresult);
    if ($ccount) {
      $haskids = 1;
      break;
    }
  }
  tng_free_result($fresult);

  return $haskids;
}