<?php

require_once 'headerElementSection.php';

class AdminHeaderElementSection extends HeaderElementSection {

  public function __construct($id = 'admin') {
    parent::__construct($id);
  }

  public function build($currentPage = 'admin', $message = '') {
    $this->id = $currentPage;

    $levelDelimiter = mb_strpos($currentPage, '-');
    $topLevel = $levelDelimiter != 0 ? mb_substr($currentPage, 0, $levelDelimiter) : $currentPage;

    $iconPath = 'img/' . $topLevel . '_icon.gif';

    $this->out = "<header id='$this->id' class='clearfix'>\n";

    $this->out .= "<a class='logo' title='twhs [placeholder]' href='#'><span>twhs logo [placeholder]</span></a>\n";
    $this->out .= "<h3><a href='admin.php'>$this->title, v.$this->version</a></h3>\n";

    global $adminNavSection;
    switch ($topLevel) {
      case 'admin':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'albums':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'backuprestore':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'branches':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'cemeteries':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'customeventtypes':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'datamaint':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'families':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'languages':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'media':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'misc':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'mostrecentactions':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'people':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'places':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'reports':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'repositories':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'setup':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'sources':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'tlevents':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'trees':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      case 'users':
        $this->out .= $adminNavSection->build($topLevel);
        break;

      default:
        $this->out .= $adminNavSection->build($currentPage);
    }
    $this->out .= "</header>\n";
    return $this->out;
  }

}
