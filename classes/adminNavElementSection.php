<?php

require_once 'NavElementSection.class.php';

class AdminNavElementSection extends NavElementSection {

  public function build($currentPage) {

    $levelDelimiter = mb_strpos($currentPage, '-');
    $topLevel = $levelDelimiter != 0 ? mb_substr($currentPage, 0, $levelDelimiter) : $currentPage;

    $this->out = "<nav class='navbar navbar-dark bg-inverse' role='navigation'>\n";
    $this->out .= "<button type='button' class='navbar-toggler hidden-md-up bg-inverse' data-toggle='collapse' data-target='#admin-navbar-collapse'>&#9776;</button>\n";
    $this->out .= "<div class='collapse navbar-toggleable-sm' id='admin-navbar-collapse'>\n";
    $this->out .= "<div class='nav navbar-nav'>\n";
    $this->out .= "<a class='nav-item nav-link active' href='admin.php' target='_parent'>$this->adminhome</a>\n";
    $this->out .= "<a class='nav-item nav-link' href='" . NavElementSection::$homePage . "' target='_parent'>$this->publichome</a>\n";

    switch ($topLevel) {
      case 'admin':
      case 'mostrecentactions':
        if (NavElementSection::$allowAdmin) {
          $this->out .= "<a class='nav-item nav-link' href='adminshowlog.php' target='main'>$this->showlog</a>\n";
        }
        break;

      case 'people':
       break;

      case 'families':
        break;

      case 'places':
        break;

      case 'setup':
        break;

      case 'sources':
        break;

      case 'users':
        break;

      default:
        break;
    }
    $this->out .= "<a class='nav-item nav-link' href='logout.php?adminLogin=1' target='_parent'>$this->logout (<strong>" . NavElementSection::$currentUser . "</strong>)</a>\n";

    $helpFile = $topLevel . '_help.php';
    $helpPath = findhelp($helpFile) . '/' . $helpFile;

    $this->out .= "<a class='nav-item nav-link' href='#' onclick=\"return openHelp('$helpPath');\">" . uiTextSnippet('help') . "</a>\n";

    // [ts] not a linking menu item. move to section below header as in public

    if (NavElementSection::$maintenanceIsOn) {
      $this->out .= "<span class='nav-item'><strong class='yellow'>" . NavElementSection::$maintenanceMessage . "</strong></span>\n";
    }
    $this->out .= "</div>\n";
    $this->out .= "</div> <!-- .navbar-collapse -->\n";
    $this->out .= "</nav>\n";

    return $this->out;
  }
  
}
