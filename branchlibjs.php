<?php
echo "var branchids = new Array();\n";
echo "branchids['none'] = new Array(\"\");\n";
echo "var branchnames = new Array();\n";
echo "branchnames['none'] = new Array(\"" . uiTextSnippet('allbranches') . "\");\n";
$swapbranches = "swapBranches();\n";
$dispid = '';
$dispname = '';

$query = "SELECT gedcom FROM trees $wherestr";
$treeresult = tng_query($query);
while ($treerow = tng_fetch_assoc($treeresult)) {
  $nexttree = addslashes($treerow['gedcom']);
  $dispid .= "branchids['$nexttree'] = new Array(\"\"";
  $dispname .= "branchnames['$nexttree'] = new Array(\"" . uiTextSnippet('allbranches') . '"';

  $query = "SELECT branch, gedcom, description FROM $branches_table WHERE gedcom = \"$nexttree\" ORDER BY description";
  $branchresult = tng_query($query);

  while ($branch = tng_fetch_assoc($branchresult)) {
    $dispid .= ",\"{$branch['branch']}\"";
    $dispname .= ',"' . addslashes(trim($branch['description'])) . '"';
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
    var tree = $('#gedcom').val();
    var len = 0;
    document.form1.branch.options.length = 0;
    if ($('#branchlist').length) {
        $('#branchlist').html('');
    }
    var newOptionElement;
    var i;
    for (i = 0; i < branchids[tree].length; i += 1) {
        newOptionElement = document.createElement("OPTION");
        len = len + 1;
        newOptionElement.text = branchnames[tree][i];
        newOptionElement.value = branchids[tree][i];
        if (!i) {
            newOptionElement.selected = true;
        }
        document.form1.branch.options.add(newOptionElement);
    }
}