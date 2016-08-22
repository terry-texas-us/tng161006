<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowDelete) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
require 'adminlog.php';

$query = "DELETE from $people_table";
$result = tng_query($query);

$query = "DELETE from $families_table";
$result = tng_query($query);

$query = "DELETE from $children_table";
$result = tng_query($query);

$query = "DELETE from $assoc_table";
$result = tng_query($query);

$query = "DELETE from $address_table";
$result = tng_query($query);

$query = "DELETE from $sources_table";
$result = tng_query($query);

$query = "DELETE from $repositories_table";
$result = tng_query($query);

$query = "DELETE from $events_table";
$result = tng_query($query);

$query = "DELETE from $notelinks_table";
$result = tng_query($query);

$query = "DELETE from $xnotes_table";
$result = tng_query($query);

$query = "DELETE from $citations_table";
$result = tng_query($query);

$query = "DELETE from $places_table";
$result = tng_query($query);

$query = "UPDATE $people_table SET branch=\"\" WHERE branch = '$branch'";
$result = tng_query($query);

$query = "UPDATE $families_table SET branch=\"\" WHERE branch = '$branch'";
$result = tng_query($query);

$query = "DELETE from $branchlinks_table WHERE branch = '$branch'";
$result = tng_query($query);

$message = uiTextSnippet('tree') . " $gedcom " . uiTextSnippet('succcleared') . '.';

adminwritelog(uiTextSnippet('deleted') . ": " . uiTextSnippet('tree') . " $tree");

header("Location: treesBrowse.php?message=" . urlencode($message));
