<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

require 'prefixes.php';

if ($type == "person") {
  $query = "SELECT personID FROM $people_table WHERE personID = \"$checkID\" AND gedcom = \"$tree\"";
  $prefix = $personprefix;
  $suffix = $personsuffix;
} else {
  if ($type == "family") {
    $query = "SELECT familyID FROM $families_table WHERE familyID = \"$checkID\" AND gedcom = \"$tree\"";
    $prefix = $familyprefix;
    $suffix = $familysuffix;
  } else {
    if ($type == "source") {
      $query = "SELECT sourceID FROM $sources_table WHERE sourceID = \"$checkID\" AND gedcom = \"$tree\"";
      $prefix = $sourceprefix;
      $suffix = $sourcesuffix;
    } else {
      if ($type == "repo") {
        $query = "SELECT repoID FROM $repositories_table WHERE repoID = \"$checkID\" AND gedcom = \"$tree\"";
        $prefix = $repoprefix;
        $suffix = $reposuffix;
      }
    }
  }
}
$result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
$prefixlen = strlen($prefix);
$suffixlen = strlen($suffix) * -1;

header("Content-type:text/html; charset=" . $session_charset);
if ($result && tng_num_rows($result)) {
  echo "<span class=\"msgerror\">ID $checkID " . uiTextSnippet('idinuse') . "</span>";
} else {
  if (($prefix && (substr($checkID, 0, $prefixlen) != $prefix || !is_numeric(substr($checkID, $prefixlen)))) ||
          ($suffix && (substr($checkID, $suffixlen) != $suffix || !is_numeric(substr($checkID, 0, $suffixlen))))
  ) {
    echo "<span class=\"msgerror\">$checkID " . uiTextSnippet('idnotvalid') . " $prefix</span>";
  } else {
    echo "<span class=\"msgapproved\">ID $checkID " . uiTextSnippet('idok') . "</span>";
  }
}
tng_free_result($result);
