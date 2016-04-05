<?php

function getBranchInfo($assignedTree, $trees, $branches, &$ids, &$names) {
  $wherestr = ($assignedTree) ? "WHERE gedcom = '$assignedTree'" : "";
  
  $query = "SELECT gedcom, treename FROM $trees $wherestr ORDER BY treename";
  $treeresult = tng_query($query);

  while ($treerow = tng_fetch_assoc($treeresult)) {
    $nexttree = addslashes($treerow['gedcom']);
    $ids .= "branchids['$nexttree'] = [''";
    $names .= "branchnames['$nexttree'] = ['" . uiTextSnippet('allbranches') . "'";

    $query = "SELECT branch, gedcom, description FROM $branches WHERE gedcom = '$nexttree' ORDER BY description";
    $branchresult = tng_query($query);

    while ($branch = tng_fetch_assoc($branchresult)) {
      $ids .= ", '{$branch['branch']}'";
      $names .= ", '" . addslashes(trim($branch['description'])) . "'";
    }
    tng_free_result($branchresult);
    $ids .= "];\n";
    $names .= "];\n";
  }
  tng_free_result($treeresult);
}

function buildBranchSelectControl($row, $tree, $assignedbranch, $branches_table) {
  $out = uiTextSnippet('branch') . ': ';

  $query = "SELECT branch, description FROM $branches_table WHERE gedcom = \"$tree\" ORDER BY description";
  $branchresult = tng_query($query);
  $branchlist = explode(",", $row['branch']);

  $descriptions = array();
  $options = "";
  while ($branchrow = tng_fetch_assoc($branchresult)) {
    $options .= "  <option value=\"{$branchrow['branch']}\"";
    if (in_array($branchrow['branch'], $branchlist)) {
      $options .= " selected";
      $descriptions[] = $branchrow['description'];
    }
    $options .= ">{$branchrow['description']}</option>\n";
  }
  $desclist = count($descriptions) ? implode(', ', $descriptions) : uiTextSnippet('nobranch');
  $out .= "<span id='branchlist'>$desclist</span>";
  if (!$assignedbranch) {
    $totbranches = tng_num_rows($branchresult) + 1;
    if ($totbranches < 2) {
      $totbranches = 2;
    }
    $selectnum = $totbranches < 8 ? $totbranches : 8;
    $select = $totbranches >= 8 ? uiTextSnippet('scrollbranch') . "<br>" : "";
    $select .= "<select class='form-control' id='branch' name='branch[]' multiple size='$selectnum' style='overflow: auto'>\n";
    $select .= "  <option value=''";
    if ($row['branch'] == "") {
      $select .= " selected";
    }
    $select .= ">" . uiTextSnippet('nobranch') . "</option>\n";

    $select .= "$options\n";
    $select .= "</select>\n";
    $out .= "<span> ( </span>\n";
    $out .= "<a id='show-branchedit-person' href='#'>\n";
    $out .= "<img src='img/ArrowDown.gif'>" . uiTextSnippet('edit') . "</a>\n";
    $out .= "<span> )</span>\n";
    
    $out .= "<div id='branchedit-person' style='position: absolute; display: none;'>\n";
    $out .= $select;
    $out .= "</div>\n";
  } else {
    $out .= "<input name='branch' type='hidden' value=\"{$row['branch']}\">";
  }
  $out .= "<input name='orgbranch' type='hidden' value=\"{$row['branch']}\">";
  return $out;
}

// [ts] variations below

function buildBranchSelectControl_from_admin_newperson($row, $tree, $assignedbranch, $branches_table) {
  $out = uiTextSnippet('branch') . ': ';
  
  $query = "SELECT branch, description FROM $branches_table WHERE gedcom = \"$tree\" ORDER BY description";
  $branchresult = tng_query($query);
  $numbranches = tng_num_rows($branchresult);
  //  $branchlist = explode(",", $row['branch']);

  //  $descriptions = array();
  $assdesc = "";
  $options = "";
  while ($branchrow = tng_fetch_assoc($branchresult)) {
    $options .= "  <option value=\"{$branchrow['branch']}\">{$branchrow['description']}</option>\n";
    if ($branchrow['branch'] == $assignedbranch) { // [ts] nt in ajx_newperson
      $assdesc = $branchrow['description'];        // [ts] nt in ajx_newperson   
    }                                              // [ts] nt in ajx_newperson   
  }
  $out .= "<span id='branchlist'></span>";
  if (!$assignedbranch) {
    if ($numbranches > 8) {
      $select = uiTextSnippet('scrollbranch') . "<br>";
    }
    $select .= "<select id='branch' name=\"branch[]\" multiple size='8'>\n";
      $select .= "<option value=''";
      if ($row['branch'] == "") {
        $select .= " selected";
      }
      $select .= ">" . uiTextSnippet('nobranch');
      $select .= "</option>\n";

      $select .= "$options\n";
    $select .= "</select>\n";
    
    $out .= "<span> (<a id='branchedit' href='#' onclick=\"showBranchEdit('branchedit'); quitBranchEdit('branchedit'); return false;\">\n";
    $out .= "<img src='img/ArrowDown.gif'>" . uiTextSnippet('edit') . "</a> )\n";
    $out .= "</span><br>";
    $out .= "<div id='branchedit' style='position: absolute; display: none;' onmouseover=\"clearTimeout(branchtimer);\" onmouseout=\"closeBranchEdit('branch', 'branchedit', 'branchlist');\">\n";
    $out .= $select;
    $out .= "</div>\n";
  } else {
    $out .= "<input name='branch' type='hidden' value=\"$assignedbranch\">$assdesc ($assignedbranch)"; // [ts] $assdesc ($assignedbranch)" part not in ajx_newperson
  }
  return $out;
}

function buildBranchSelectControl_admin_newperson2($row, $tree, $assignedbranch, $branches_table) {
  $out = uiTextSnippet('branch') . ": ";
  
  $query = "SELECT branch, description FROM $branches_table WHERE gedcom = \"$tree\" ORDER BY description";
  $branchresult = tng_query($query);
  $numbranches = tng_num_rows($branchresult);
  
  //  $branchlist = explode(",", $row[branch]);

  //  $descriptions = array();
  $assdesc = "";
  $options = "";
  while ($branchrow = tng_fetch_assoc($branchresult)) {
    $options .= "  <option value=\"{$branchrow['branch']}\">{$branchrow['description']}</option>\n";
    if ($branchrow['branch'] == $assignedbranch) {
      $assdesc = $branchrow['description'];
    }
  }
  $out .= "<span id='branchlist2'></span>";
  if (!$assignedbranch) {
    if ($numbranches > 8) {
      $select = uiTextSnippet('scrollbranch') . "<br>";
    }
    $select .= "<select id='branch2' name=\"branch[]\" multiple size='8'>\n";
    $select .= "<option value=''";
    if ($row['branch'] == "") {
      $select .= " selected";
    }
    $select .= ">" . uiTextSnippet('nobranch');
    $select .= "</option>\n";

    $select .= "$options\n";
    $select .= "</select>\n";
    
    $out .= "<span> (<a href='#' onclick=\"showBranchEdit('branchedit2'); quitBranchEdit('branchedit2'); return false;\">\n";
    $out .= "<img src='img/ArrowDown.gif'>" . uiTextSnippet('edit') . "</a> )\n";
    $out .= "</span><br>";
    $out .= "<div id='branchedit2' style='position: absolute; display: none;' 
         onmouseover='clearTimeout(branchtimer);'
         onmouseout='closeBranchEdit('branch2', 'branchedit2', 'branchlist2');'>\n";
    $out .= $select;
    $out .= "</div>\n";
  } else {
    $out .= "<input name='branch' type='hidden' value=\"$assignedbranch\">$assdesc ($assignedbranch)";
  }
  return $out;
}

function buildBranchSelectControl_ajx_newperson($row, $tree, $assignedbranch, $branches_table) {
  $out = uiTextSnippet('branch') . ": ";
  
  $query = "SELECT branch, description FROM $branches_table WHERE gedcom = \"$tree\" ORDER BY description";
  $branchresult = tng_query($query);
  $numbranches = tng_num_rows($branchresult);
  $branchlist = explode(",", $row[branch]);

  $descriptions = array();
  $options = "";
  while ($branchrow = tng_fetch_assoc($branchresult)) {
    $options .= "  <option value=\"{$branchrow['branch']}\">{$branchrow['description']}</option>\n";
  }
  $out .= "<span id='pbranchlist'></span>";
  if (!$assignedbranch) {
    if ($numbranches > 8) {
      $select = uiTextSnippet('scrollbranch') . "<br>";
    }
    $select .= "<select id='pbranch' name=\"branch[]\" multiple size=\"8\">\n";
    $select .= "  <option value=''";
    if ($row['branch'] == "") {
      $select .= " selected";
    }
    $select .= ">" . uiTextSnippet('nobranch');
    $select .= "</option>\n";

    $select .= "$options\n";
    $select .= "</select>\n";
    
    $out .= "<span> (<a id='pbranchedit' href='#'>\n";
    $out .= "<img src='img/ArrowDown.gif'>" . uiTextSnippet('edit') . "</a> )\n";
    $out .= "</span><br>";
    $out .= "<div id='pbranchedit' style='position: absolute; display: none;'>\n";
      $out .= $select;
    $out .= "</div>\n";
  } else {
    $out .= "<input name='branch' type='hidden' value=\"$assignedbranch\">";
  }
  return $out;
}