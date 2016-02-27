<?php

include("begin.php");
include("adminlib.php");

include("checklogin.php");

require("adminlog.php");

$query = "DELETE FROM $assoc_table WHERE assocID=\"$assocID\"";
$result = tng_query($query);

$query = "SELECT count(assocID) as acount FROM $assoc_table WHERE gedcom=\"$tree\" AND personID=\"$personID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

adminwritelog(uiTextSnippet('deleted') . ": " . uiTextSnippet('association') . " $assocID");

echo $row['acount'];
