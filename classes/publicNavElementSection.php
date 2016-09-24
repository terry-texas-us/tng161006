<?php

require_once 'classes/NavElementSection.class.php';
require_once 'classes/personSearchForm.class.php';

class PublicNavElementSection extends NavElementSection {
  
  private function buildListItem($link, $id, $label) {
    $title = htmlspecialchars(uiTextSnippet($label), ENT_QUOTES);
    
    $out =  "<a class='nav-item nav-link' tabindex='-1' title='$title' href='$link'><img class='icon-sm' src='$id'>$title</a>\n";

    return $out;
  }

  private function buildFindMenu() {
    global $timeOffset;

    $out = $this->buildListItem('surnames.php', 'svg/users.svg', 'surnames');
    $out .= $this->buildListItem('bookmarks.php', 'svg/bookmarks.svg', 'bookmarks');
    $out .= $this->buildListItem('places.php', 'svg/location.svg', 'places');
    $tngmonth = date('m', time() + (3600 * $timeOffset));
    $out .= $this->buildListItem("calendar.php?m=$tngmonth", 'svg/calendar.svg', 'calendar');
    $out .= $this->buildListItem('cemeteriesShow.php', 'svg/headstone.svg', 'cemeteries');
    $out .= $this->buildListItem('searchform.php', 'svg/magnifying-glass.svg', 'searchnames');
    $out .= $this->buildListItem('famsearchform.php', 'svg/magnifying-glass.svg', 'searchfams');

    global $tngconfig;
    $tngconfig['menucount'] += 8;

    return $out;
  }
  
  private function buildInfoMenu($title) {
    global $tngconfig;

    $out = $this->buildListItem('whatsnew.php', 'svg/megaphone.svg', 'whatsnew');
    $out .= $this->buildListItem('mostwanted.php', 'svg/person-unknown.svg', 'mostwanted');
    $out .= $this->buildListItem('reportsShow.php', 'svg/print.svg', 'reports');
    $out .= $this->buildListItem('statistics.php', 'svg/bar-graph.svg', 'databasestatistics');
    $out .= $this->buildListItem('branchesShow.php', 'svg/flow-branch.svg', 'branches');
    $out .= $this->buildListItem('notesShow.php', 'svg/new-message.svg', 'notes');
    $out .= $this->buildListItem('sourcesShow.php', 'svg/archive.svg', 'sources');
    $out .= $this->buildListItem('repositoriesShow.php', 'svg/building.svg', 'repositories');

    global $allow_admin;
    if ($allow_admin) {
      $out .= $this->buildListItem('showlog.php', 'svg/log-out.svg', 'mnushowlog');
      $tngconfig['menucount'] += 2;
    }
    $out .= $this->buildListItem('contactUs.php?page=' . urlencode($title), 'svg/mail.svg', 'contactus');
    $tngconfig['menucount'] += 10;    //everything except the 2 admin links

    return $out;
  }

  private function buildMediaMenu() {
    global $mediatypes;
    global $tngconfig;

    $out = '';
    foreach ($mediatypes as $mediatype) {
      if (!$mediatype['disabled']) {
        $out .= $this->buildListItem('mediaShow.php?mediatypeID=' . $mediatype['ID'], $mediatype['icon'], $mediatype['ID']);
        $tngconfig['menucount']++;
      }
    }
    $out .= $this->buildListItem('albumsShow.php', 'svg/album.svg', 'albums');
    $out .= $this->buildListItem('mediaShow.php', 'svg/media-mixed.svg', 'allmedia');
    $tngconfig['menucount'] += 2;

    return $out;
  }

  public function build($currentPage = '') {
    global $tngprint;

    $fullmenu = '';
    if ($tngprint) {
      $fullmenu .= "<div style=\"float:right\">\n";
      $fullmenu .= "<a href=\"javascript:{document.getElementById('printlink').style.visibility='hidden'; window.print();}\" style=\"text-decoration:underline\" id=\"printlink\">&gt;&gt; " . uiTextSnippet('tngprint') . " &lt;&lt;</a>\n";
      $fullmenu .= "</div>\n";
    } else {
      $fullmenu .= "<nav class='navbar navbar-light bg-faded' role='navigation'>\n";
      $outermenu .=  "<button type='button' class='navbar-toggler hidden-md-up' data-toggle='collapse' data-target='#public-navbar-collapse'>&#9776;</button>\n";
      $outermenu .=  "<div class='collapse navbar-toggleable-sm' id='public-navbar-collapse'>\n";
            
      $outermenu .=    "<div class='nav navbar-nav'>\n";

      global $allow_admin;
      if ($allow_admin) {
        $outermenu .=    "<a class='nav-item nav-link' href='admin.php' target='_parent'>" . uiTextSnippet('administration') . "</a>\n";
      }
      $outermenu .=      "<div class='nav-item dropdown'>\n";
      $outermenu .=        "<a class='nav-link dropdown-toggle' href='#' data-toggle='dropdown'>" . uiTextSnippet('find_menu') . "<span class='caret'></span></a>\n";
      $outermenu .=        "<div class='dropdown-menu' role='menu' aria-labelledby='dropdownMenu'>\n";
      $outermenu .=          $this->buildFindMenu();
      $outermenu .=        "</div>\n"; // dropdown menu
      $outermenu .=      "</div>\n";

      $outermenu .=      "<div class='nav-item dropdown'>\n";
      $outermenu .=        "<a class='nav-link dropdown-toggle' href='#' data-toggle='dropdown'>" . uiTextSnippet('media') . "<span class='caret'></span></a>\n";
      $outermenu .=        "<div class='dropdown-menu' role='menu' aria-labelledby='dropdownMenu'>\n";
      $outermenu .=          $this->buildMediaMenu();
      $outermenu .=        "</div>\n";
      $outermenu .=      "</div>\n";

      $outermenu .=      "<div class='nav-item dropdown'>\n";
      $outermenu .=        "<a class='nav-link dropdown-toggle' href='#' data-toggle='dropdown'>" . uiTextSnippet('info') . "<span class='caret'></span></a>\n";
      $outermenu .=        "<div class='dropdown-menu' role='menu' aria-labelledby='dropdownMenu'>\n";
      $outermenu .=          $this->buildInfoMenu($currentPage);
      $outermenu .=        "</div>\n";
      $outermenu .=      "</div>\n";

      if (NavElementSection::$maintenanceIsOn) {
        $outermenu .=    "<div class='nav-item'>\n";
        $outermenu .=      "<strong class='orange'>" . PublicNavElementSection::$maintenanceMessage . "</strong>\n";
        $outermenu .=    "</div>\n";
      }
      $form = new PersonSearchForm();
      $outermenu .= $form->get();
      $outermenu .=    "</div>\n"; // nav
      $outermenu .=  "</div> <!-- .navbar-collapse -->\n";
      $outermenu .=  "</nav>\n"; // navbar

      $fullmenu .= $outermenu;

      $fullmenu .= tng_getRightIcons();

      return $fullmenu;
    }
  }
}