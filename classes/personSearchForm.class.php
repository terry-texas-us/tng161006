<?php

/**
 * Search Form - First Name and Last Name
 * @author ts
 */
class personSearchForm {

  private $out = '';

  public function __construct() {
    $args = func_get_args();
    $num_args = func_num_args();

    if (method_exists($this, $construct = '__construct' . $num_args)) {
      call_user_func_array(array($this, $construct), $args);
    }
  }

  public function __construct0() {
    $this->out = "<div id='personsearchform'>\n";
    $this->out .= "<form name='personsearchform' method='get' action='search.php'>\n";
      $this->out .= "<label class='sr-only' for='myfirstname'>" . uiTextSnippet('mnufirstname') . "</label>\n";
      $this->out .= "<input class='btn btn-secondary' id='myfirstname' name='myfirstname' type='text' placeholder='" . uiTextSnippet('mnufirstname') . "'>\n";
      $this->out .= "<label class='sr-only' for='searchlast'>" . uiTextSnippet('mnulastname') . "</label>\n";
      $this->out .= "<input class='btn btn-secondary btn-md' id='mylastname' name='mylastname' type='text' placeholder='" . uiTextSnippet('mnulastname') . "'>\n";
      $this->out .= "<button class='btn btn-primary-outline' type='submit' value=''><img class='icon-sm' src='svg/magnifying-glass.svg'></button>\n";
      $this->out .= "<input name='mybool' type='hidden' value='AND'>\n";
    $this->out .= "</form>\n";
    $this->out .= "</div>\n";
  }

  public function get() {
    return $this->out;
  }

}
