<?php

class HeaderElementSection {

  protected $out;
  protected $id = "admin";
  protected $title = "Title";
  protected $subtitle = "";
  protected $imageUrl = "";
  protected $version = "1.0.0";

  protected $data = [];

  public function __construct($id) {
    $this->id = $id;
  }

  public function __set($name, $value) {
    $this->data[$name] = $value;
  }

  public function __get($name) {
    if (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    }
    $trace = debug_backtrace();
    trigger_error('Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_NOTICE);
    return null;
  }

  public function setTitle($title) {
    $this->title = $title;
  }

  public function setSubtitle($subtitle) {
    $this->subtitle = $subtitle;
  }

  public function setVersion($version) {
    $this->version = $version;
  }
  
  public function setImageUrl($imageUrl) {
    $this->imageUrl = $imageUrl;
  }
}
