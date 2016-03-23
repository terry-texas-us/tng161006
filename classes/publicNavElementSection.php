<?php

require_once 'classes/navElementSection.class.php';

class publicNavElementSection extends navElementSection {
  
  private function buildListItem($link, $id, $label) {
    $title = htmlspecialchars(uiTextSnippet($label), ENT_QUOTES);
    
    $out =  "<li class='nav-item'>\n";
    $out .=   "<a class='nav-link' tabindex='-1' title='$title' href='$link'><img class='icon-sm icon-menu' src='$id'>$title</a>\n";
    $out .= "</li>\n";

    return $out;
  }

  private function buildFindMenu() {
    global $time_offset;

    $out = $this->buildListItem("surnames.php", "svg/users.svg", "surnames");
    $out .= $this->buildListItem("bookmarks.php", "svg/bookmarks.svg", "bookmarks");
    $out .= $this->buildListItem("placesMain.php", "svg/location.svg", "places");
    $tngmonth = date("m", time() + (3600 * $time_offset));
    $out .= $this->buildListItem("calendar.php?m=$tngmonth", "svg/calendar.svg", "calendar");
    $out .= $this->buildListItem("cemeteriesShow.php", "svg/headstone.svg", "cemeteries");
    $out .= $this->buildListItem("searchform.php", "svg/magnifying-glass.svg", "searchnames");
    $out .= $this->buildListItem("famsearchform.php", "svg/magnifying-glass.svg", "searchfams");

    global $tngconfig;
    $tngconfig['menucount'] += 8;

    return $out;
  }
  
  private function buildInfoMenu($title) {
    global $tngconfig;

    $out = $this->buildListItem("whatsnew.php", "svg/megaphone.svg", "whatsnew");
    $out .= $this->buildListItem("mostwanted.php", "svg/person-unknown.svg", "mostwanted");
    $out .= $this->buildListItem("reportsShow.php", "svg/print.svg", "reports");
    $out .= $this->buildListItem("statistics.php", "svg/bar-graph.svg", "databasestatistics");
    $out .= $this->buildListItem("treesShow.php", "svg/tree.svg", "trees");
    $out .= $this->buildListItem("branchesShow.php", "svg/flow-branch.svg", "branches");
    $out .= $this->buildListItem("browsenotes.php", "svg/new-message.svg", "notes");
    $out .= $this->buildListItem("sourcesShow.php", "svg/archive.svg", "sources");
    $out .= $this->buildListItem("repositoriesShow.php", "svg/building.svg", "repositories");

    global $allow_admin;
    if ($allow_admin) {
      $out .= $this->buildListItem("showlog.php", "svg/log-out.svg", "mnushowlog");
      $tngconfig['menucount'] += 2;
    }
    $out .= $this->buildListItem("contactUs.php?page=" . urlencode($title), "svg/mail.svg", "contactus");
    $tngconfig['menucount'] += 10;    //everything except the 2 admin links

    return $out;
  }

  private function buildMediaMenu() {
    global $mediatypes;
    global $tngconfig;

    $out = "";
    foreach ($mediatypes as $mediatype) {
      if (!$mediatype['disabled']) {
        $out .= $this->buildListItem("mediaShow.php?mediatypeID=" . $mediatype['ID'], $mediatype['icon'], $mediatype['ID']);
        $tngconfig['menucount']++;
      }
    }
    $out .= $this->buildListItem("albumsShow.php", "svg/album.svg", "albums");
    $out .= $this->buildListItem("mediaShow.php", "svg/media-mixed.svg", "allmedia");
    $tngconfig['menucount'] += 2;

    return $out;
  }

  public function build($currentPage = "") {
    global $tngprint;

    $fullmenu = "";
    if ($tngprint) {
      $fullmenu .= "<div style=\"float:right\"><a href=\"javascript:{document.getElementById('printlink').style.visibility='hidden'; window.print();}\" style=\"text-decoration:underline\" id=\"printlink\">&gt;&gt; " . uiTextSnippet('tngprint') . " &lt;&lt;</a></div>\n";
    } else {
      $fullmenu .= "<nav class='navbar navbar-dark bg-inverse' role='navigation'>\n";
      $outermenu .=  "<button type='button' class='navbar-toggler hidden-md-up' data-toggle='collapse' data-target='#public-navbar-collapse'>&#9776;</button>\n";
      $outermenu .=  "<div class='collapse navbar-toggleable-sm' id='public-navbar-collapse'>\n";
            
      $outermenu .=    "<ul class='nav navbar-nav'>\n";

      global $allow_admin;
      if ($allow_admin) {
        $outermenu .=    "<li class='nav-item'><a class='nav-link' href='admin.php' target='_parent'>" . uiTextSnippet('administration') . "</a></li>\n";
      }
      $outermenu .=      "<li class='nav-item dropdown'>\n";
      $outermenu .=        "<a class='nav-link dropdown-toggle' href='#' data-toggle='dropdown'>" . uiTextSnippet('find_menu') . "<span class='caret'></span></a>\n";
      $outermenu .=        "<ul class='dropdown-menu bg-inverse' role='menu' aria-labelledby='dropdownMenu'>\n";
      $outermenu .=          $this->buildFindMenu();
      $outermenu .=        "</ul>\n"; // dropdown menu
      $outermenu .=      "</li>\n";

      $outermenu .=      "<li class='nav-item dropdown'>\n";
      $outermenu .=        "<a class='nav-link dropdown-toggle' href='#' data-toggle='dropdown'>" . uiTextSnippet('media') . "<span class='caret'></span></a>\n";
      $outermenu .=        "<ul class='dropdown-menu bg-inverse' role='menu' aria-labelledby='dropdownMenu'>\n";
      $outermenu .=          $this->buildMediaMenu();
      $outermenu .=        "</ul>\n";
      $outermenu .=      "</li>\n";

      $outermenu .=      "<li class='nav-item dropdown'>\n";
      $outermenu .=        "<a class='nav-link dropdown-toggle' href='#' data-toggle='dropdown'>" . uiTextSnippet('info') . "<span class='caret'></span></a>\n";
      $outermenu .=        "<ul class='dropdown-menu bg-inverse' role='menu' aria-labelledby='dropdownMenu'>\n";
      $outermenu .=          $this->buildInfoMenu($currentPage);
      $outermenu .=        "</ul>\n";
      $outermenu .=      "</li>\n";

      if (navElementSection::$maintenanceIsOn) {
        $outermenu .=    "<li class='nav-item'>\n";
        $outermenu .=      "<strong class='yellow'>" . publicNavElementSection::$maintenanceMessage . "</strong>\n";
        $outermenu .=    "</li>\n";
      }
      $outermenu .=    "</ul>\n"; // nav
      $outermenu .=  "</div> <!-- .navbar-collapse -->\n";

    $outermenu .=  "</nav>\n"; // navbar

    $fullmenu .= $outermenu;

    $fullmenu .= tng_getRightIcons();

    return $fullmenu;
  }
}
}