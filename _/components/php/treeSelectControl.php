<?php
$treequery = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";
$treeresult = tng_query($treequery);
$numtrees = tng_num_rows($treeresult);
if ($numtrees > 1) {
  echo "<select class='form-control' name='tree'>\n";
  if (!$assignedtree) {
    echo "  <option value=''>" . uiTextSnippet('alltrees') . "</option>\n";
  }
  while ($treerow = tng_fetch_assoc($treeresult)) {
    echo "  <option value='{$treerow['gedcom']}'";
    if ($treerow['gedcom'] == $tree) {
      echo " selected";
    }
    echo ">{$treerow['treename']}</option>\n";
  }
  echo "</select>\n";
} else {
  echo "<input name='tree' type='hidden' value='$assignedtree'>\n";
}
tng_free_result($treeresult);
