<?php

class navList {

  private $id = '';
  private $list = [];
  private $out = '';

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
  
  public function appendItem($item) {
    array_push($this->list, $item);
  }

  function buildListItem($index, $link, $label, $page, $thispage) {
    $class = "class='nav-link";
    $class .= $page == $thispage ? " active'" : "'";

    $listItem = "<li class='nav-item'>\n";
      $listItem .= "<a $class id='a$index' href='$link'>$label</a>\n";
    $listItem .= "</li>\n";

    return $listItem;
  }

  function build($activeItem) {
    $this->out = "<div role='navigation'>\n";
    $this->out .=   "<ul class='nav nav-pills'>\n";

    $tabctr = 0;
    foreach ($this->list as $listItem) {
      if ($listItem[0]) {
        $this->out .= $this->buildListItem($tabctr++, $listItem[1], $listItem[2], $activeItem, $listItem[3]);
      }
    }
    $this->out .=  "</ul>\n";
    $this->out .= "</div>\n";
    return $this->out;
  }
  
}
