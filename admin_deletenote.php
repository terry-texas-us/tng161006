<?php

include("begin.php");
include("adminlib.php");

include("checklogin.php");
include("prefixes.php");

require("adminlog.php");

if ($noteID) {
  $query = "DELETE FROM $citations_table WHERE eventID=\"$noteprefix$noteID$notesuffix\"";
  $result = tng_query($query);
}

deleteNote($noteID, 1);

$query = "SELECT count(ID) as ncount FROM $notelinks_table WHERE gedcom=\"$tree\" AND persfamID=\"$personID\" AND eventID=\"$eventID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

adminwritelog(uiTextSnippet('deleted') . ": " . uiTextSnippet('note') . " $noteID");

echo $row['ncount'];
