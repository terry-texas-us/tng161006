<?php
function ClearData($tree) {
  global $people_table;
  global $families_table;
  global $children_table;
  global $sources_table;
  global $events_table;
  global $repositories_table;
  global $treesTable;
  global $notelinks_table;
  global $xnotes_table;
  global $citations_table;
  global $places_table;
  global $address_table;
  global $assoc_table;

  $clear_files = [
          $address_table,
          $assoc_table,
          $children_table,
          $citations_table,
    //$events_table,
          $families_table,
          $notelinks_table,
          $people_table,
          $repositories_table,
          $sources_table,
          $xnotes_table
  ];
  $query = "SELECT COUNT(*) AS trees FROM $treesTable";
  if (!($result = tng_query($query))) {
    die(uiTextSnippet('cannotexecutequery') . ": $query");
  }
  $row = tng_fetch_assoc($result);
  $tree_cnt = $row['trees'];

  for ($i = 0; $i < sizeof($clear_files); $i++) {
    $query = (($tree_cnt >= 2) ? 'DELETE FROM ' : 'TRUNCATE ') . $clear_files[$i];
    
    if (!($result = tng_query($query))) {
      die(uiTextSnippet('cannotexecutequery') . ": $query");
    }
  } // End for

  //we won't be able to match media links for custom events, since the custom event IDs will be renumbered, so delete the media links and start again

  $query = "DELETE from $events_table WHERE persfamID != \"XXX\"";
  tng_query($query);

  $query = "DELETE from $places_table WHERE (latitude is null OR latitude = \"\") AND (longitude is null OR longitude = \"\") AND (notes is null OR notes = \"\")";
  tng_query($query);
}
