<?php

require_once 'scriptsManager.php';

class HeadElementSection {

  protected $out;

  private static $siteName = '';
  private $title = '';
  
  public function __construct($siteName) {
    HeadElementSection::$siteName = $siteName;
  }
  
  public function setTitle($title) {
    $this->title = $title;
  }
  
  public function build($flags, $id, $sessionCharset) { 
    $siteName = HeadElementSection::$siteName;
    $this->out = "<head>\n";
    if ($sessionCharset) {
      $this->out .= "<meta charset=\"$sessionCharset\">\n";
    }
    $this->out .= "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n";
    $this->out .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
    if ($id === 'admin') {
      $this->out .= "<meta name=\"robots\" content=\"noindex,nofollow\">\n";
      
      $usesitename = $siteName ? stripslashes($siteName) . ': ' : '';
      $this->out .= "<title>$usesitename" . "$this->title</title>\n";

      initMediaTypes();
    } elseif ($id === 'public') {
      $this->out .= "<meta name=\"Keywords\" content=\"$this->title\" />\n";
      $this->out .= "<meta name=\"Description\" content=\"$this->title$this->siteprefix\"/>\n";

      if (isset($flags['norobots'])) {
        $this->out .= $flags['norobots'];
      }
      $siteprefix = $siteName ? htmlspecialchars($this->title ? ': ' . $siteName : $siteName, ENT_QUOTES, $sessionCharset) : '';
      $title = htmlspecialchars($this->title, ENT_QUOTES, $sessionCharset);
      $this->out .= "<title>$title$siteprefix</title>\n";

      $this->out .= "<link rel='alternate' type='application/rss+xml' title='RSS' href='tngrss.php' />\n";
    }
    $this->out .= "<!-- Bootstrap styles -->\n";
    $this->out .= "<link rel='stylesheet' type='text/css' href='_/css/bootstrap.css'>\n";
    
    $this->out .= "<link rel='stylesheet' type='text/css' href='css/genstyle.css'>\n";
      
    if (isset($flags['styles'])) {
      $this->out .= $flags['styles'];
    }
    $this->out .= "</head>\n";
    
    return $this->out;
  }
  
}
