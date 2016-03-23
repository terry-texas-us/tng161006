<?php

class footerElementSection {

    private $out;
    private $id = "admin";
    private $title = "The Next Generation";
    private $version = "10.1.3";

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
      trigger_error('Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] .
        ' on line ' . $trace[0]['line'], E_USER_NOTICE);
      $value = null;
    }
    return $value;
    }

    public function setTitle($title) {
    $this->title = $title;
    }

    public function build() {
    $this->out = "\n";
    $this->out .= "<footer class='row' id='$this->id'>\n<br>\n<hr>\n";
    if ($this->id === 'admin') {
      $this->out .= "<strong>Admin interface.</strong><span>$this->title, v.$this->version</span>\n";
    } else {
      $this->out .= "<span class='pull-xs-right'>\n";
      $this->out .= "$this->poweredBy <a href='http://lythgoes.net/genealogy/software.php'>{$this->title}</a>";
      $this->out .= " &copy;, v. $this->version,  $this->writtenBy Darrin Lythgoe 2001-" . date('Y') . ".";
      $this->out .= "</span>\n";
    }
    $this->out .= "</footer>\n";

    return $this->out;
    }
}
