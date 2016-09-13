<?php

require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

$query = "SELECT branch, description FROM $branches_table";
$result = tng_query($query);
$numrows = tng_num_rows($result);

if (!$numrows) {
  echo '0';
} else {
  echo "<option value=''></option>\n";
  while ($row = tng_fetch_assoc($result)) {
    echo "<option value=\"{$row['branch']}\">{$row['description']}</option>\n";
  }
}
tng_free_result($result);
