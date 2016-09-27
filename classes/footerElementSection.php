<?php

require_once 'classes/ChooseLanguage.php';

class FooterElementSection {

  private $out;
  private $id = 'admin';
  private $title = 'Hueman Heritage';
  private $version = '0.09';

  private $data = []; // [ts] overloaded data

  public function __construct($id) {
    $this->id = $id;
  }

  public function __set($name, $value) {
    $this->data[$name] = $value;
  }

  public function __get($name) {
    if (array_key_exists($name, $this->data)) {
      $value = $this->data[$name];
    } else {
      $trace = debug_backtrace();
      trigger_error('Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_NOTICE);
      $value = null;
    }
    return $value;
  }

  public function setTitle($title) {
    $this->title = $title;
  }

  public function build() {
    $this->out .= "<footer class='$this->id'> \n";
    $this->out .= "<div class='row'>\n";
    $this->out .= "<div class='col-md-9'>\n";
    $this->out .= "<strong>$this->title</strong>  v.$this->version, " . date('Y');
    if ($this->id === 'admin') {
      $this->out .= ' (Admin interface)';
    }
    $this->out .= "</div>\n";
    
    $this->out .= "<div class='col-md-3'>";
    $chooseLanguage = new ChooseLanguage();
    $this->out .= $chooseLanguage->buildForm($instance);
    $this->out .= "</div>\n";
    $this->out .= "</div>\n";
    $this->out .= "</footer>\n";

    return $this->out;
  }
  
}
