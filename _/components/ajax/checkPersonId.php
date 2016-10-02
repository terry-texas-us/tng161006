<?php
require_once '../../../begin.php';

$scriptName = is_string($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
if (strpos($scriptName, 'ajax/checkPersonId.php') !== false) {
  include_once '../php/textSnippets.php';

  $query = "SELECT personID FROM people WHERE personID = '$checkID'";
  $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");

  header('Content-type: text/html; charset=' . $sessionCharset);

  $out = '';
  if ($result && tng_num_rows($result)) {
    $out .= '{"result" : "idinuse", "message" : "' . uiTextSnippet('idinuse') . '"}';
  } else {
    if (substr($checkID, 0, 1) != 'I' || !is_numeric(substr($checkID, 1))) {
      $out .= '{"result" : "idnotvalid", "message" : "' . uiTextSnippet('idnotvalid') . '"}';
    } else {
      $out .= '{"result" : "idok", "message" : "' . uiTextSnippet('idok') . '"}';
    }
  }
  tng_free_result($result);
  echo $out;
}
