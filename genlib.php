<?php
require_once 'version.php';

require_once 'pwdlib.php';
require_once 'globallib.php';
require_once 'mediatypes.php';
require_once 'tngfiletypes.php';

require_once 'trees.php';

checkMaintenanceMode(0);
if ($needMap) {
  require $subroot . 'mapconfig.php';
  if ($map['key']) {
    include_once 'googlemaplib.php';
  }
}
require 'tngrobots.php';

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
require_once 'classes/HeadElementSection.php';
require_once 'classes/publicNavElementSection.php';
require_once 'classes/publicHeaderElementSection.php';
require_once 'classes/footerElementSection.php';
require_once 'classes/scriptsManager.php';

$headSection = new HeadElementSection($sitename);

NavElementSection::maintenanceState(isset($tngconfig['maint']) && $tngconfig['maint'] != "", uiTextSnippet('mainton'));

$publicHeaderSection = new PublicHeaderElementSection();
$publicHeaderSection->setTitle(getTemplateMessage('headtitle'));
$publicHeaderSection->setSubtitle(getTemplateMessage('headsubtitle'));
$publicHeaderSection->setImageUrl($tmp['headimage']);

$publicNavSection = new PublicNavElementSection('public');

$publicFooterSection = new FooterElementSection('public');
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

function doMenuItem($index, $link, $icon, $label, $page, $thispage) {
  $class = $page == $thispage ? " class=\"here\"" : "";
  $imagetext = $icon ? "<img class='icon-sm' src='{$icon}'>" : "";

  return "<li><a id=\"a$index\" href=\"$link\"$class>$imagetext$label</a></li>\n";
}

function tng_menu($enttype, $currpage, $entityID) {
  global $tree;
  global $disallowgedcreate;
  global $allowEdit;
  global $rightbranch;
  global $allow_ged;
  global $emailaddr;
  
  $nexttab = 0;
  $menu = "<div id='tngmenu'>\n";
  $menu .= "<ul id='tngnav'>\n";
  if ($enttype == 'I') {
    $menu .= doMenuItem($nexttab++, "peopleShowPerson.php?personID=$entityID&amp;tree=$tree", "svg/user.svg", uiTextSnippet('indinfo'), $currpage, "person");
    $menu .= doMenuItem($nexttab++, "pedigree.php?personID=$entityID&amp;tree=$tree", "svg/flow-split-horizontal.svg", uiTextSnippet('ancestors'), $currpage, "pedigree");
    $menu .= doMenuItem($nexttab++, "descend.php?personID=$entityID&amp;tree=$tree", "svg/flow-cascade.svg", uiTextSnippet('descendants'), $currpage, "descend");
    $menu .= doMenuItem($nexttab++, "relateform.php?primaryID=$entityID&amp;tree=$tree", "svg/users.svg", uiTextSnippet('relationship'), $currpage, "relate");
    $menu .= doMenuItem($nexttab++, "timeline.php?primaryID=$entityID&amp;tree=$tree", "svg/project.svg", uiTextSnippet('timeline'), $currpage, "timeline");

    if (!$disallowgedcreate || ($allow_ged && $rightbranch)) {
      $menu .= doMenuItem($nexttab++, "gedform.php?personID=$entityID&amp;tree=$tree", "svg/folder.svg", uiTextSnippet('extractgedcom'), $currpage, "gedcom");
    }
    $editstr = "peopleEdit.php?person";
  } elseif ($enttype == 'F') {
    $menu .= doMenuItem($nexttab++, "familiesShowFamily.php?familyID=$entityID&amp;tree=$tree", "svg/users.svg", uiTextSnippet('family'), $currpage, "family");
    $editstr = "familiesEdit.php?family";
  } elseif ($enttype == 'S') {
    $menu .= doMenuItem($nexttab++, "showsource.php?sourceID=$entityID&amp;tree=$tree", "svg/folder.svg", uiTextSnippet('source'), $currpage, "source");
    $editstr = "admin_editsource.php?source";
  } elseif ($enttype == 'R') {
    $menu .= doMenuItem($nexttab++, "repositoriesShowItem.php?repoID=$entityID&amp;tree=$tree", "svg/folder.svg", uiTextSnippet('repository'), $currpage, "repo");
    $editstr = "repositoriesEdit.php?repo";
  } elseif ($enttype == 'L') {

    $treestr = $tngconfig['places1tree'] ? "" : "&amp;tree=$tree";
    $menu .= doMenuItem($nexttab++, "placesearch.php?psearch=$entityID$treestr", "svg/home.svg", uiTextSnippet('place'), $currpage, "place");
    $editstr = "admin_editplace.php?";
    $entityID = urlencode($entityID);
  }
  if ($allowEdit && $rightbranch) {
    $menu .= doMenuItem($nexttab, "$editstr" . "ID=$entityID&amp;tree=$tree&amp;cw=1\" target=\"_blank", "svg/new-message.svg", uiTextSnippet('edit'), $currpage, "");
  } elseif ($emailaddr) {
    $menu .= doMenuItem($nexttab, "mixedSuggest.php?enttype=$enttype&amp;ID=$entityID&amp;tree=$tree", "svg/new-message.svg", uiTextSnippet('suggest'), $currpage, "suggest");
  }
  $menu .= "</ul>\n";
  $menu .= "</div>\n";
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
