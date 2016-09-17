<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

require 'adminlog.php';

if ($table == 'all') {
  $tablelist = [$cemeteries_table, $people_table, $families_table, $children_table, $languagesTable, $places_table, $states_table, $countries_table, $sources_table, $citations_table, $reports_table, $events_table, $eventtypes_table, 'trees', $notelinks_table, $xnotes_table, 'users', $tlevents_table, $saveimport_table, $temp_events_table, $branches_table, $branchlinks_table, $address_table, $albums_table, $albumlinks_table, $album2entities_table, $assoc_table, $media_table, $medialinks_table, $mediatypes_table];
  $tablename = uiTextSnippet('alltables');
  $message = "$tablename " . uiTextSnippet('succoptimized') . '.';
} else {
  $tablelist = ["$table"];
  $tablename = $table;
  $message = uiTextSnippet('table') . " $tablename " . uiTextSnippet('succoptimized') . '.';
}
foreach ($tablelist as $thistable) {
  $query = "OPTIMIZE TABLE $thistable";
  $result = tng_query($query);
}

header('Content-type:text/html; charset=' . $session_charset);
adminwritelog(uiTextSnippet('optimize') . ": $tablename");
if ($table == 'all') {
  header('Location: admin_utilities.php?message=' . urlencode($message));
} else {
  echo $table . '&' . uiTextSnippet('succoptimized');
}