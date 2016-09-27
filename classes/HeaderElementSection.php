<?php

class HeaderElementSection {

  protected $out;
  protected $id = 'admin';
  protected $title = 'Title';
  protected $subtitle = '';
  protected $imageUrl = '';
  protected $version = '0.09';

  protected $data = [];

  public function __construct($id) {
    $this->id = $id;
  }//end __construct()

  public function __set($name, $value) {
    $this->data[$name] = $value;
  }//end __set()

  public function __get($name) {
    if (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    }
    $trace = debug_backtrace();
    trigger_error('Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_NOTICE);
    return null;
  }//end __get()

  public function setTitle($title) {
    $this->title = $title;
  }//end setTitle()

  public function setSubtitle($subtitle) {
    $this->subtitle = $subtitle;
  }//end setSubtitle()

  public function setVersion($version) {
    $this->version = $version;
  }//end setVersion()

  public function setImageUrl($imageUrl) {
    $this->imageUrl = $imageUrl;
  }//end setImageUrl()

}//end class
