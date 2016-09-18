<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

require 'adminlog.php';

if ($table == 'all') {
  $tablelist = ['cemeteries', 'people', 'families', 'children', 'languages', 'places', 'states', 'countries', 'sources', 'citations', 'reports', 'events', 'eventtypes', 'trees', 'notelinks', 'xnotes', 'users', 'timelineevents', 'saveimport', 'temp_events', 'branches', 'branchlinks', 'addresses', 'albums', 'albumlinks', 'albumplinks', 'associations', 'media', 'medialinks', 'mediatypes'];
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