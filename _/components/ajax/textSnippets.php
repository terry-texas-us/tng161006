<?php
require_once '../../../begin.php';

$scriptName = is_string($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
if (strpos($scriptName, 'ajax/textSnippets.php') !== false) {
  include_once '../php/textSnippets.php';
  echo uiTextSnippet($snippetID);
}
