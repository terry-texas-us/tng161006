<?php

class PersonSearchForm {

  private $out = '';

  public function __construct() {
    $args = func_get_args();
    $numArgs = func_num_args();

    if (method_exists($this, $construct = '__construct' . $numArgs)) {
      call_user_func_array([$this, $construct], $args);
    }
  }

  public function __construct0() {
    $this->out = "<div id='personsearchform'>\n";
    $this->out .= "<form class='form-inline pull-md-right' name='personsearchform' method='get' action='search.php'>\n";
      $this->out .= "<label class='sr-only' for='myfirstname'>" . uiTextSnippet('mnufirstname') . "</label>\n";
      $this->out .= "<input class='form-control' id='myfirstname' name='myfirstname' type='text' placeholder='" . uiTextSnippet('mnufirstname') . "'>\n";
      $this->out .= "<label class='sr-only' for='mylastname'>" . uiTextSnippet('mnulastname') . "</label>\n";
      $this->out .= "<input class='form-control' id='mylastname' name='mylastname' type='text' placeholder='" . uiTextSnippet('mnulastname') . "'>\n";
      $this->out .= "<button class='btn btn-outline-primary' type='submit'><img class='icon-sm' src='svg/magnifying-glass.svg'></button>\n";
      $this->out .= "<input name='mybool' type='hidden' value='AND'>\n";
    $this->out .= "</form>\n";
    $this->out .= "</div>\n";
  }

  public function get() {
    return $this->out;
  }

}
