<?php
echo "var branchids = new Array();\n";
echo "branchids['none'] = new Array(\"\");\n";
echo "var branchnames = new Array();\n";
echo "branchnames['none'] = new Array(\"" . uiTextSnippet('allbranches') . "\");\n";
$swapbranches = "swapBranches();\n";
$dispid = "";
$dispname = "";

$query = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";
$treeresult = tng_query($query);
while ($treerow = tng_fetch_assoc($treeresult)) {
  $nexttree = addslashes($treerow['gedcom']);
  $dispid .= "branchids['$nexttree'] = new Array(\"\"";
  $dispname .= "branchnames['$nexttree'] = new Array(\"" . uiTextSnippet('allbranches') . "\"";

  $query = "SELECT branch, gedcom, description FROM $branches_table WHERE gedcom = \"$nexttree\" ORDER BY description";
  $branchresult = tng_query($query);

  while ($branch = tng_fetch_assoc($branchresult)) {
    $dispid .= ",\"{$branch['branch']}\"";
    $dispname .= ",\"" . addslashes(trim($branch['description'])) . "\"";
  }
  tng_free_result($branchresult);
  $dispid .= ");\n";
  $dispname .= ");\n";
}
tng_free_result($treeresult);
echo $dispid;
echo $dispname;
?>
function swapBranches() {
var tree = jQuery('#gedcom').val();
var len = 0;
document.form1.branch.options.length = 0;
if(jQuery('#branchlist').length)
jQuery('#branchlist').html('');

for( var i = 0; i &lt; branchids[tree].length; i++ ) {
var newElem = document.createElement("OPTION");
len = len + 1;
newElem.text = branchnames[tree][i];
newElem.value = branchids[tree][i];
if( !i ) newElem.selected = true;
document.form1.branch.options.add(newElem);
}
}