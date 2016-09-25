<?php

require_once 'headerElementSection.php';

class PublicHeaderElementSection extends HeaderElementSection {

  public function __construct($id = 'public') {
    parent::__construct($id);
  }

  public function build($currentPage = 'public') {
    $this->out = "<header id='$this->id' class='clearfix'>\n";

    $this->out .= "<h2><a href='index.php'>$this->title</a></h2>\n";
    $this->out .= "<h3>$this->subtitle</h3>\n";
    //    $this->out .= "<div class='headimage'>\n";
    //    $this->out .=   "<img src='$this->imageUrl' alt=''>\n";
    //    $this->out .= "</div>\n";

    global $publicNavSection;
    $this->out .= $publicNavSection->build($currentPage);

    $this->out .= "</header>\n";
    return $this->out;
  }
  
}
