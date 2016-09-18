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

tng_query('DELETE FROM people');
tng_query('ALTER TABLE people AUTO_INCREMENT = 1');

tng_query('DELETE FROM extlinks');
tng_query('ALTER TABLE extlinks AUTO_INCREMENT = 1');

tng_query('DELETE FROM families');
tng_query('ALTER TABLE families AUTO_INCREMENT = 1');

tng_query('DELETE FROM children');
tng_query('ALTER TABLE children AUTO_INCREMENT = 1');

tng_query('DELETE FROM associations');
tng_query('ALTER TABLE associations AUTO_INCREMENT = 1');

tng_query('DELETE FROM addresses');
tng_query('ALTER TABLE addresses AUTO_INCREMENT = 1');

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

$query = "UPDATE people SET branch = '' WHERE branch = '$branch'";
$result = tng_query($query);

$query = "UPDATE families SET branch = '' WHERE branch = '$branch'";
$result = tng_query($query);

$query = "DELETE FROM branchlinks WHERE branch = '$branch'";
$result = tng_query($query);

$message = uiTextSnippet('tree') . " $gedcom " . uiTextSnippet('succcleared') . '.';

adminwritelog(uiTextSnippet('deleted') . ': ' . uiTextSnippet('tree') . " $tree");

header('Location: treesBrowse.php?message=' . urlencode($message));
