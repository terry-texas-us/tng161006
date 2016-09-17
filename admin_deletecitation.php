<?php

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

require 'adminlog.php';

$query = "DELETE FROM citations WHERE citationID=\"$citationID\"";
$result = tng_query($query);

$query = "SELECT count(citationID) AS ccount FROM citations WHERE persfamID = '$personID' AND eventID = '$eventID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

adminwritelog(uiTextSnippet('deleted') . ': ' . uiTextSnippet('citation') . " $citationID");

echo $row['ccount'];
