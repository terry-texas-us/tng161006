<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

require 'adminlog.php';

$query = "DELETE FROM associations WHERE assocID = '$assocID'";
$result = tng_query($query);

$query = "SELECT count(assocID) AS acount FROM associations WHERE personID = '$personID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

adminwritelog(uiTextSnippet('deleted') . ': ' . uiTextSnippet('association') . " $assocID");

echo $row['acount'];
