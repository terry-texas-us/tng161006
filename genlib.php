<?php
require_once("version.php");

include_once("pwdlib.php");
include_once("globallib.php");
include_once("mediatypes.php");
include_once("tngfiletypes.php");

checkMaintenanceMode(0);
if ($needMap) {
  include($subroot . "mapconfig.php");
  if ($map['key']) {
    include_once("googlemaplib.php");
  }
}
include("tngrobots.php");

$gotlastpage = false;
$flags['error'] = $error;

if ($requirelogin && $treerestrict && $_SESSION['assignedtree']) {
  if (!$tree) {
    $tree = $_SESSION['assignedtree'];
  } elseif ($tree != $_SESSION['assignedtree']) {
    header("Location:$homepage");
    exit;
  }
}
$orgtree = $tree;
if (!$tree && $defaulttree) {
  $tree = $defaulttree;
} elseif ($tree == "-x--all--x-") {
  $tree = "";
}
require_once './classes/headElementSection.php';
require_once './classes/publicNavElementSection.php';
require_once './classes/publicHeaderElementSection.php';
require_once './classes/footerElementSection.php';
require_once './classes/scriptsManager.php';

$headSection = new headElementSection($sitename);

navElementSection::maintenanceState(isset($tngconfig['maint']) && $tngconfig['maint'] != "", uiTextSnippet('mainton'));

$publicHeaderSection = new publicHeaderElementSection();
$publicHeaderSection->setTitle(getTemplateMessage('headtitle'));
$publicHeaderSection->setSubtitle(getTemplateMessage('headsubtitle'));
$publicHeaderSection->setImageUrl($tmp['headimage']);

$publicNavSection = new publicNavElementSection('public');

$publicFooterSection = new footerElementSection('public');
$publicFooterSection->poweredBy = uiTextSnippet('pwrdby');
$publicFooterSection->writtenBy = uiTextSnippet('writby');

function getSmallPhoto($medialink) {
  global $rootpath;
  global $mediapath;
  global $mediatypes_assoc;
  global $thumbmaxw;
  global $thumbmaxh;
  global $mediatypes_thumbs;

  $mediatypeID = $medialink['mediatypeID'];
  $usefolder = $medialink['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
  //determine $usefolder based on mediatypeID and usecollfolder

  if ($medialink['allow_living'] && $medialink['thumbpath'] && file_exists("$rootpath$usefolder/" . $medialink['thumbpath'])) {
    $thumb = "$usefolder/" . str_replace("%2F", "/", rawurlencode($medialink['thumbpath']));
    $photoinfo = getimagesize("$rootpath$usefolder/" . $medialink['thumbpath']);
    if ($photoinfo[0] <= $thumbmaxw && $photoinfo[1] <= $thumbmaxh) {
      $photohtouse = $photoinfo[1];
      $photowtouse = $photoinfo[0];
    } else {
      if ($photoinfo[0] > $photoinfo[1]) {
        $photowtouse = $thumbmaxw;
        $photohtouse = intval($thumbmaxw * $photoinfo[1] / $photoinfo[0]);
      } else {
        $photohtouse = $thumbmaxh;
        $photowtouse = intval($thumbmaxh * $photoinfo[0] / $photoinfo[1]);
      }
    }
    $dimensions = " width=\"$photowtouse\" height=\"$photohtouse\"";
    $class = " class=\"thumb\"";
  } else {
    $thumb = "img/" . $mediatypes_thumbs[$mediatypeID];
    $dimensions = $class = "";
  }

  $cleantitle = $medialink['allow_living'] ? str_replace("\"", "'", $medialink['description']) : "";
  $imgsrc = "<img src=\"$thumb\" $dimensions alt=\"$cleantitle\" title=\"$cleantitle\"$class>";

  return $imgsrc;
}

function tng_DrawHeading($photostr, $namestr, $years) {
  if ($photostr) {
    $outputstr = "<div style='float: left; padding-right: 5px'>$photostr</div>\n";
    $outputstr .= "<h2>$namestr</h2><span>$years</span>\n";
  } else {
    $outputstr = "<h2 class='header fn' id='nameheader'>$namestr</h2>";
    if ($years) {
      $outputstr .= "<span>$years</span><br>\n";
    }
  }
  $outputstr .= "<br clear='all'><br>\n";
  return $outputstr;
}

function getSurnameOnly($row) {
  global $tngconfig;

  $nonames = showNames($row);
  if ($row['allow_living'] || $nonames != 1) {
    $namestr = trim($row['lnprefix'] . " " . $row['lastname']);
    if ($tngconfig['ucsurnames']) {
      $namestr = tng_strtoupper($namestr);
    }
  } elseif ($row['private']) {
    $namestr = uiTextSnippet('private');
  } else {
    $namestr = uiTextSnippet('living');
  }

  return $namestr;
}

function getFirstNameOnly($row) {
  $nonames = showNames($row);
  if (($row['allow_living'] && $row['allow_private']) || !$nonames) {
    $namestr = strtok($row['firstname'], " ");
  } elseif ($nonames == 2) {
    $namestr = initials($row['firstname']);
  } elseif ($row['private']) {
    $namestr = uiTextSnippet('private');
  } else {
    $namestr = uiTextSnippet('living');
  }

  return $namestr;
}

function tng_menu($enttype, $currpage, $entityID, $innermenu) {
  global $tree;
  global $disallowgedcreate;
  global $allow_edit;
  global $rightbranch;
  global $allow_ged;
  global $emailaddr;
  global $tngprint;

  $nexttab = 0;
  if (!$tngprint) {
    $menu = "<div id='tngmenu'>\n";
    $menu .= "<ul id='tngnav'>\n";
    if ($enttype == 'I') {
      $menu .= doMenuItem($nexttab++, "getperson.php?personID=$entityID&amp;tree=$tree", "svg/user.svg", uiTextSnippet('indinfo'), $currpage, "person");
      $menu .= doMenuItem($nexttab++, "pedigree.php?personID=$entityID&amp;tree=$tree", "svg/flow-split-horizontal.svg", uiTextSnippet('ancestors'), $currpage, "pedigree");
      $menu .= doMenuItem($nexttab++, "descend.php?personID=$entityID&amp;tree=$tree", "svg/flow-cascade.svg", uiTextSnippet('descendants'), $currpage, "descend");
      $menu .= doMenuItem($nexttab++, "relateform.php?primaryID=$entityID&amp;tree=$tree", "svg/users.svg", uiTextSnippet('relationship'), $currpage, "relate");
      $menu .= doMenuItem($nexttab++, "timeline.php?primaryID=$entityID&amp;tree=$tree", "svg/project.svg", uiTextSnippet('timeline'), $currpage, "timeline");

      if (!$disallowgedcreate || ($allow_ged && $rightbranch)) {
        $menu .= doMenuItem($nexttab++, "gedform.php?personID=$entityID&amp;tree=$tree", "svg/folder.svg", uiTextSnippet('extractgedcom'), $currpage, "gedcom");
      }
      $editstr = "admin_editperson.php?person";
    } elseif ($enttype == 'F') {
      $menu .= doMenuItem($nexttab++, "familygroup.php?familyID=$entityID&amp;tree=$tree", "svg/users.svg", uiTextSnippet('family'), $currpage, "family");
      $editstr = "admin_editfamily.php?family";
    } elseif ($enttype == 'S') {
      $menu .= doMenuItem($nexttab++, "showsource.php?sourceID=$entityID&amp;tree=$tree", "svg/folder.svg", uiTextSnippet('source'), $currpage, "source");
      $editstr = "admin_editsource.php?source";
    } elseif ($enttype == 'R') {
      $menu .= doMenuItem($nexttab++, "showrepo.php?repoID=$entityID&amp;tree=$tree", "svg/folder.svg", uiTextSnippet('repository'), $currpage, "repo");
      $editstr = "admin_editrepo.php?repo";
    } elseif ($enttype == 'L') {

      $treestr = $tngconfig['places1tree'] ? "" : "&amp;tree=$tree";
      $menu .= doMenuItem($nexttab++, "placesearch.php?psearch=$entityID$treestr", "svg/home.svg", uiTextSnippet('place'), $currpage, "place");
      $editstr = "admin_editplace.php?";
      $entityID = urlencode($entityID);
    }
    if ($allow_edit && $rightbranch) {
      $menu .= doMenuItem($nexttab, "$editstr" . "ID=$entityID&amp;tree=$tree&amp;cw=1\" target=\"_blank", "svg/new-message.svg", uiTextSnippet('edit'), $currpage, "");
    } elseif ($emailaddr) {
      $menu .= doMenuItem($nexttab, "suggest.php?enttype=$enttype&amp;ID=$entityID&amp;tree=$tree", "svg/new-message.svg", uiTextSnippet('suggest'), $currpage, "suggest");
    }
    $menu .= "</ul>\n";
    $menu .= "</div>\n";
    $menu .= "<div class='pub-innermenu small rounded4'>\n";
    $menu .= $innermenu;
    $menu .= "</div><br>\n";
  } else {
    $menu = "";
  }
  return $menu;
}

function tng_getRightIcons() {
  global $tngconfig;
  global $gotlastpage;

  $right_icons = "<div class='icons-rt'>\n";

  if ($tngconfig['showshare']) {
    $onclick = "onclick=\"$('#shareicons').toggle(200); if(!share) { $('#share').html('" . uiTextSnippet('hide') . "'); share=1;} else { $('#share').html('" . uiTextSnippet('share') . "'); share=0; }; return false;\"";
    $title = uiTextSnippet('share');
    $right_icons .= " <a id='share' href='#' $onclick title='$title'>\n";
    $right_icons .= "<img class='icon-sm' src='svg/share.svg'>\n";
    $right_icons .= "</a>\n";
  }
  if (!$tngconfig['showprint']) {
    $print_url = getScriptName();
    if (preg_match("/\?/", $print_url)) {
      $print_url .= "&amp;tngprint=1";
    } else {
      $print_url .= "?tngprint=1";
    }
    $onclick = "onclick=\"newwindow = window.open('$print_url', 'tngprint', 'width = 900, height = 600, status = no, resizable = yes, scrollbars = yes'); newwindow.focus(); return false;\"";
    $title = uiTextSnippet('tngprint');
    $right_icons .= " <a href='#' $onclick rel='nofollow' title='$title'>\n";
    $right_icons .= "<img class='icon-sm' src='svg/print.svg'>\n";
    $right_icons .= "</a>\n";
  }
  if (!$tngconfig['showbmarks'] && $gotlastpage) {
    $onclick = "onclick=\"tnglitbox = new ModalDialog('ajx_addbookmark.php?p='); return false;\"";
    $title = uiTextSnippet('bookmark');
    $right_icons .= " <a href='#' $onclick title='$title'>\n";
    $right_icons .= "<img class='icon-sm' src='svg/bookmark.svg'>\n";
    $right_icons .= "</a>\n";
    $tngconfig['menucount']++;
  }
  $right_icons .= "</div>\n";

  $sharemenu = "";
  if ($tngconfig['showshare']) {
    $sharemenu .= "<div id=\"shareicons\" style=\"display:none\">\n";
    $sharemenu .= "<span class='st_facebook_hcount' displayText='Facebook'></span>\n";
    // [ts] $sharemenu .= "<span class='st_twitter_hcount' displayText='Tweet'></span>\n";
    // [ts] $sharemenu .= "<span class='st_pinterest_hcount' displayText='Pinterest'></span>\n";
    $sharemenu .= "<span class='st_googleplus_hcount' displayText='Google +'></span>\n";

    $sharemenu .= "</div>\n";
  }
  $right_icons .= $sharemenu;

  return $right_icons;
}

function treeDropdown($forminfo) {
  global $requirelogin;
  global $assignedtree;
  global $trees_table;
  global $time_offset;
  global $treerestrict;
  global $tree;
  global $numtrees;
  global $tngconfig;

  $ret = "";
  if (!$requirelogin || !$treerestrict || !$assignedtree) {
    $query = "SELECT gedcom, treename, lastimportdate FROM $trees_table ORDER BY treename";
    $treeresult = tng_query($query);
    $numtrees = tng_num_rows($treeresult);
    $foundtree = false;

    if ($numtrees > 1) {
      if ($forminfo['startform']) {
        $ret .= buildFormElement($forminfo['action'], $forminfo['method'], $forminfo['name'], $forminfo['id']);
      }
      $ret .= treeSelect($treeresult, $forminfo['name']);
      $ret .= "&nbsp; <img class='spinner' id='treespinner' src='img/spinner.gif' style='display: none;' alt=''>\n";
      if (is_array($forminfo['hidden'])) {
        foreach ($forminfo['hidden'] as $hidden) {
          $ret .= "<input name=\"" . $hidden['name'] . "\" type='hidden' value=\"" . $hidden['value'] . "\" />\n";
        }
      }
      if ($forminfo['endform']) {
        $ret .= "</form><br>\n";
      } else {
        $ret .= "<br><br>\n";
      }
      $treeresult = tng_query($query);
      if ($tree) {
        $foundtree = true;
        while ($row = tng_fetch_assoc($treeresult)) {
          if ($row['gedcom'] == $tree) {
            break;
          }
        }
      }
    } else {
      $foundtree = true;
      $row = tng_fetch_assoc($treeresult);
    }
    if ($tngconfig['lastimport'] && $foundtree && $forminfo['lastimport']) {
      $lastimport = $row['lastimportdate'];

      if ($lastimport) {
        $importtime = strtotime($lastimport);
        if (substr($lastimport, 11, 8) != "00:00:00") {
          $importtime += ($time_offset * 3600);
        }
        $importdate = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? strftime("%#d %b %Y %H:%M:%S", $importtime) : strftime("%e %b %Y %H:%M:%S", $importtime);
        echo "<p>" . uiTextSnippet('lastimportdate') . ": " . displayDate($importdate) . "</p>";
      }
    }
    tng_free_result($treeresult);
  }
  return $ret;
}

function treeSelect($treeresult, $formname = null) {
  global $tree;

  $ret = "<label for='tree'>" . uiTextSnippet('tree') . "</label>";
  $ret .= "<select class='form-control' id='treeselect' name='tree'";
  if ($formname) {
    $ret .= " onchange=\"$('#treespinner').show(); document.$formname.submit();\"";
  }
  $ret .= ">\n";
  $ret .= "<option value='-x--all--x-'";
  if (!$tree) {
    $ret .= " selected";
  }
  $ret .= ">" . uiTextSnippet('alltrees') . "</option>\n";

  while ($row = tng_fetch_assoc($treeresult)) {
    $ret .= "<option value='{$row['gedcom']}'";
    if ($tree && $row['gedcom'] == $tree) {
      $ret .= " selected";
    }
    $ret .= ">{$row['treename']}</option>\n";
  }
  $ret .= "</select>\n";
  return $ret;
}

function getMediaHREF($row, $mlflag) {
  global $mediatypes_assoc;
  global $mediapath;
  global $imagetypes;
  global $videotypes;
  global $recordingtypes;

  $uselink = "";

  if ($row['form']) {
    $form = strtoupper($row['form']);
  } else {
    preg_match("/\.(.+)$/", $row['path'], $matches);
    $form = strtoupper($matches[1]);
  }
  $thismediatype = $row['mediatypeID'];
  $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$thismediatype] : $mediapath;

  if (!$row['abspath'] && (in_array($form, $imagetypes) || in_array($form, $videotypes) || in_array($form, $recordingtypes) || !$form)) {
    $uselink = "showmedia.php?mediaID=" . $row['mediaID'];
    if ($mlflag == 1) {
      $uselink .= "&amp;medialinkID=" . $row['medialinkID'];
    } elseif ($mlflag == 2) {
      $uselink .= "&amp;albumlinkID=" . $row['albumlinkID'];
    } elseif ($mlflag == 3) {
      $uselink .= "&amp;cemeteryID=" . $row['cemeteryID'];
    }
  } else {
    if ($row['abspath'] || substr($row['path'], 0, 4) == "http" || substr($row['path'], 0, 1) == "/") {
      $uselink = $row['path'];
    } else {
      $url = rawurlencode($row['path']);
      $url = str_replace("%2F", "/", $url);
      $url = str_replace("%3F", "?", $url);
      $url = str_replace("%23", "#", $url);
      $url = str_replace("%26", "&", $url);
      $url = str_replace("%3D", "=", $url);
      $uselink = "$usefolder/$url";
    }
  }
  if ($row['newwindow']) {
    $uselink .= "\" target=\"_blank";
  }
  return $uselink;
}

function insertLinks($notes) {
  if ($notes) {
    $pos = 0;
    while (($pos = strpos($notes, "http", $pos)) !== false) {
      if ($pos) {
        $prevchar = substr($notes, $pos - 1, 1);
      }
      if ($pos == 0 || ($prevchar != "\"" && $prevchar != "=")) {
        $notepos[] = $pos++;
      } else {
        $pos++;
      }
    }
    $posidx = count($notepos);
    while ($posidx > 0) {
      $actual = $posidx - 1;
      $pos = $notepos[$actual];
      $firstpart = substr($notes, 0, $pos);
      $rest = substr($notes, $pos);
      $linkstr = strtok($rest, " <\n\r");
      $lastchar = substr($linkstr, -1);
      if ($lastchar == "." || $lastchar == ",") {
        $linkstr = substr($linkstr, 0, -1);
      }
      $lastpart = substr($notes, $pos + strlen($linkstr));
      $notes = $firstpart . "<a href=\"$linkstr\" target='_blank'>$linkstr</a>" . $lastpart;
      $posidx--;
    }
  }

  return $notes;
}

function getTemplateMessage($key) {
  global $tmp;
  global $session_language;

  $langkey = $key . "_" . $session_language;

  return isset($tmp[$langkey]) ? $tmp[$langkey] : $tmp[$key];
}

function showLinks($linkList) {
  $links = explode("\r", $linkList);
  $finishedList = "";
  if (count($links) == 1) {
    $links = explode("\n", $linkList);
  }
  foreach ($links as $link) {
    $parts = explode(",", $link);
    $len = count($parts);
    if ($len == 1) {
      $title = $href = $parts[0];
    } elseif ($len == 2) {
      $title = trim($parts[0]);
      $href = trim($parts[1]);
    } else {
      $href = trim(array_pop($parts));
      $title = implode("", $parts);
    }
    $finishedList .= "<li><a href=\"$href\" title=\"$title\" target='_blank'>$title</a></li>\n";
  }
  return $finishedList;
}