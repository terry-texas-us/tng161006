<?php

/**
 * adminNavElementSection
 *
 * @author tsmit
 */
require_once './classes/navElementSection.class.php';

class adminNavElementSection extends navElementSection {

    public function build($currentPage) {

    $levelDelimiter = mb_strpos($currentPage, '-');
    $topLevel = $levelDelimiter != 0 ? mb_substr($currentPage, 0, $levelDelimiter) : $currentPage;

    $this->out =      "<nav class='navbar navbar-dark bg-inverse' role='navigation'>\n";
      $this->out .=     "<button type='button' class='navbar-toggler hidden-md-up' data-toggle='collapse' data-target='#admin-navbar-collapse'>&#9776;</button>\n";
        $this->out .=   "<div class='collapse navbar-toggleable-sm' id='admin-navbar-collapse'>\n";

        $this->out .=     "<ul class='nav navbar-nav'>\n";
        $this->out .=       "<li class='nav-item active'><a class='nav-link' href='admin.php' target='_parent'>$this->adminhome</a></li>\n";
        $this->out .=       "<li class='nav-item'><a class='nav-link' href='" . navElementSection::$homePage . "' target='_parent'>$this->publichome</a></li>\n";

        switch ($topLevel) {
          case 'admin':
          case 'mostrecentactions':
            if (navElementSection::$allowAdmin) {
              $this->out .= "<li class='nav-item'>\n";
              $this->out .=    "<a class='nav-link' href='adminshowlog.php' target='main'>$this->showlog</a>\n";
              $this->out .=  "</li>\n";
            }
//            if (isset(navElementSection::$helpPath) && navElementSection::$helpPath != "") {
//              $this->out .=  "<li class='nav-item dropdown'>\n";
//              $this->out .=   "<a href='#' class='nav-link dropdown-toggle' data-toggle='dropdown'>{$this->getstart}<span class='caret'></span></a>\n";
//              $this->out .=    "<ul class='dropdown-menu bg-inverse' role='menu' aria-labelledby='dropdownMenu'>\n";
//              $this->out .=      "<li class='nav-item'><a class='nav-link' href='#' onclick=\"return openHelp('" . navElementSection::$helpPath . "');\">$this->getstart</a></li>\n";
//              $this->out .=    "</ul>\n";
//              $this->out .=  "</li>\n";
//            }
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
        $this->out .=      "<li class='nav-item'><a class='nav-link' href='logout.php?admin_login=1' target='_parent'>$this->logout (<strong>" . navElementSection::$currentUser . "</strong>)</a></li>\n";

        $helpFile = $topLevel . "_help.php";
        $helpPath = findhelp($helpFile) . '/' . $helpFile;

        $this->out .=     "<li class='nav-item'>\n";
        $this->out .=       "<a class='nav-link' href='#' onclick=\"return openHelp('$helpPath');\">" . uiTextSnippet('help') . "</a>";
        $this->out .=     "</li>\n";

        // [ts] not a linking menu item. move to section below header as in public

        if (navElementSection::$maintenanceIsOn) {
          $this->out .=    "<li class='nav-item'>\n";
          $this->out .=      "<strong class='yellow'>" . navElementSection::$maintenanceMessage . "</strong>\n";
          $this->out .=    "</li>\n";
        }
        $this->out .=      "</ul>\n";
      $this->out .=     "</div> <!-- .navbar-collapse -->\n";
    $this->out .=      "</nav>\n";

    return $this->out;
    }
}
