<?php

function uiTextSnippet($name, $extra = null) {
  static $snippets = [];
  if (!isset($name) || $name === '') {
    $value = '';
  } else if (array_key_exists($name, $snippets)) {
    $value = $snippets[$name];
  } else {
    $language = $_SESSION['session_language'];

    $query = "SELECT value FROM snippets JOIN languages ON snippets.languageID = languages.languageID WHERE snippets.name = '$name' AND languages.folder = '$language';";

    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    tng_free_result($result);

    $value = $row && array_key_exists('value', $row) ? $row['value'] : '';

    if ($value === '') {
      // if (preg_match('/^[iIfFsSrR][0-9]+$/', $name)) { // [ts] Individual (i###), Family (f###), Source (s###) and Repository (R###) ids are passed to this function. Modify callers to always call with values expected to be snippets?
      //   echo("<script>console.log('Undefined User Interface Text Snippet: [" . $name . "]');</script>");
      // } else if (preg_match('/^\d{4}.+\d{4}$/', $name)) {  // [ts] rm date year date range ex. 1899-1901. Notice the .+ to interprete the unicode dash/hyphen codes.
      //   echo("<script>console.log('Undefined User Interface Text Snippet: [" . $name . "]');</script>");
      // } else {
      //   trigger_error('Undefined User Interface Text Snippet: [' . $name . ']');
      // }
    } else {
      $snippets[$name] = $value;
    }
  }
  // [ts] this extra is likely not worth the effort .. looks clunky when used with <b>, <i>, <em>
  if (isset($extra)) {
    if (array_key_exists('before', $extra)) {
      $value = $extra['before'] . $value;
    } elseif (array_key_exists('after', $extra)) {
      $value .= $extra['after'];
    } elseif (array_key_exists('wrap', $extra)) {
      $value = $extra['wrap'] . $value . $extra['wrap'];
    } elseif (array_key_exists('html', $extra)) {
      $value = '<' . $extra['html'] . '>' . $value . '</' . $extra['html'] . '>';
    }
  }
  return $value;
}
