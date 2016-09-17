<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowDelete) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
require 'adminlog.php';

tng_query("DELETE FROM $people_table");
tng_query("ALTER TABLE $people_table AUTO_INCREMENT = 1");

tng_query('DELETE FROM extlinks');
tng_query('ALTER TABLE extlinks AUTO_INCREMENT = 1');

tng_query("DELETE FROM $families_table");
tng_query("ALTER TABLE $families_table AUTO_INCREMENT = 1");

tng_query("DELETE FROM $children_table");
tng_query("ALTER TABLE $children_table AUTO_INCREMENT = 1");

tng_query("DELETE FROM $assoc_table");
tng_query("ALTER TABLE $assoc_table AUTO_INCREMENT = 1");

tng_query("DELETE FROM $address_table");
tng_query("ALTER TABLE $address_table AUTO_INCREMENT = 1");

tng_query('DELETE FROM sources');
tng_query('ALTER TABLE sources AUTO_INCREMENT = 1');

tng_query('DELETE FROM repositories');
tng_query('ALTER TABLE repositories AUTO_INCREMENT = 1');

tng_query('DELETE FROM events');
tng_query('ALTER TABLE events AUTO_INCREMENT = 1');

tng_query('DELETE FROM notelinks');
tng_query('ALTER TABLE notelinks AUTO_INCREMENT = 1');

tng_query('DELETE FROM xnotes');
tng_query('ALTER TABLE xnotes AUTO_INCREMENT = 1');

tng_query('DELETE FROM citations');
tng_query('ALTER TABLE citations AUTO_INCREMENT = 1');

tng_query('DELETE FROM places');
tng_query('ALTER TABLE places AUTO_INCREMENT = 1');

$query = "UPDATE $people_table SET branch = '' WHERE branch = '$branch'";
$result = tng_query($query);

$query = "UPDATE $families_table SET branch = '' WHERE branch = '$branch'";
$result = tng_query($query);

$query = "DELETE FROM $branchlinks_table WHERE branch = '$branch'";
$result = tng_query($query);

$message = uiTextSnippet('tree') . " $gedcom " . uiTextSnippet('succcleared') . '.';

adminwritelog(uiTextSnippet('deleted') . ': ' . uiTextSnippet('tree') . " $tree");

header('Location: treesBrowse.php?message=' . urlencode($message));
