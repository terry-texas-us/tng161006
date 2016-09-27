<?php

require_once 'HeaderElementSection.php';

class PublicHeaderElementSection extends HeaderElementSection {

  public function __construct($id='public') {
    parent::__construct($id);
  }//end __construct()

  public function build($currentPage='public') {
    $this->out = "<header id='$this->id' class='clearfix'>\n";

    $this->out .= "<h2><a href='index.php'>$this->title</a></h2>\n";
    $this->out .= "<h3>$this->subtitle</h3>\n";

    global $publicNavSection;
    $this->out .= $publicNavSection->build($currentPage);

    $this->out .= "</header>\n";
    return $this->out;
  }//end build()

}//end class
