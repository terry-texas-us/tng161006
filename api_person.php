<?php
require 'begin.php';
require 'genlib.php';
require 'getlang.php';

require 'api_checklogin.php';
require 'personlib.php';
require 'api_library.php';
require 'log.php';

header("Content-Type: application/json; charset=" . $session_charset);

$query = "SELECT *, DATE_FORMAT(changedate,\"%e %b %Y\") AS changedate FROM $people_table WHERE personID = '$personID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
if (!tng_num_rows($result)) {
  tng_free_result($result);
  echo "{\"error\":\"No one in database with that ID and tree\"}";
  exit;
} else {
  tng_free_result($result);
}
echo "{\n";

$rightbranch = checkbranch($row['branch']);
$rights = determineLivingPrivateRights($row);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];

$namestr = getName($row);

$logname = $tngconfig['nnpriv'] && $row['private'] ? uiTextSnippet('private') : ($nonames && $row['living'] ? uiTextSnippet('living') : $namestr);

writelog("<a href=\"peopleShowPerson.php?personID=$personID\">" . uiTextSnippet('indinfofor') . " $logname ($personID)</a>");

$events = [];
echo api_person($row, $fullevents);

echo "}";