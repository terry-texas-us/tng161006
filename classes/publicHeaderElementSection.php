<?php

/**
 * publicHeaderElementSection
 *
 * @author ts
 */

require_once './classes/headerElementSection.php';

class publicHeaderElementSection extends headerElementSection {

  public function __construct($id = 'public') {
    parent::__construct($id);
  }

  public function build($currentPage = 'public') {
    $this->out = "<header id='$this->id' class='clearfix'>\n";

    $this->out .= "<h2 class='headtitle'><a href='index.php'>$this->title</a></h2>\n";
    $this->out .= "<h3 class='headsubtitle'>$this->subtitle</h3>\n";
//    $this->out .= "<div class='headimage'>\n";
//    $this->out .=   "<img src='$this->imageUrl' alt=''>\n";
//    $this->out .= "</div>\n";

    global $publicNavSection;
    $this->out .= $publicNavSection->build($currentPage);

    $this->out .= "</header>\n";
    return $this->out;
  }
}
