<?php

class ChooseLanguage {

  private $out;

  public function buildForm($instance) {
    global $chooselang;
    global $mylanguage;
    global $languagesPath;

    $this->out = '';
    if ($chooselang) {
      $query = 'SELECT languageID, display, folder FROM languages ORDER BY display';
      $result = tng_query($query);
      $numlangs = tng_num_rows($result);

      if ($numlangs > 1) {
        $this->out .= "<div class='language-change'>\n";
        $this->out .= buildFormElement('savelanguage2', 'get', "tngmenu$instance");
        $this->out .= "<select class='form-control' name='newlanguage$instance' id='newlanguage$instance' onchange='document.tngmenu$instance.submit();'>";

        while ($row = tng_fetch_assoc($result)) {
          $this->out .= "<option value='{$row['languageID']}'";
          if ($languagesPath . $row['folder'] == $mylanguage) {
            $this->out .= ' selected';
          }
          $this->out .= ">{$row['display']}</option>\n";
        }
        $this->out .= "</select>\n";
        $this->out .= "<input name='instance' type='hidden' value='$instance' /></form>\n";
        $this->out .= "</div>\n";
      }
      tng_free_result($result);
    }
    return $this->out;
  }//end buildForm()

}//end class
