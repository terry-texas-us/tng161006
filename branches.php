<?php

function getBranchInfo($trees, $branches, &$ids, &$names) {
  $query = "SELECT gedcom FROM $trees";
  $treeresult = tng_query($query);

  while ($treerow = tng_fetch_assoc($treeresult)) {
    $nexttree = addslashes($treerow['gedcom']);
    $ids .= "branchids['$nexttree'] = [''";
    $names .= "branchnames['$nexttree'] = ['" . uiTextSnippet('allbranches') . "'";

    $query = "SELECT branch, gedcom, description FROM $branches ORDER BY description";
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

function buildBranchSelectControl($row, $assignedbranch, $branches_table) {
  $out = uiTextSnippet('branch') . ': ';

  $query = "SELECT branch, description FROM $branches_table ORDER BY description";
  $branchresult = tng_query($query);
  $branchlist = explode(",", $row['branch']);

  $descriptions = [];
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

function buildBranchSelectControl_admin_newperson2($row, $assignedbranch, $branches_table) {
  $out = uiTextSnippet('branch') . ": ";
  
  $query = "SELECT branch, description FROM $branches_table ORDER BY description";
  $branchresult = tng_query($query);
  $numbranches = tng_num_rows($branchresult);
  
  //  $branchlist = explode(",", $row[branch]);

  //  $descriptions = [];
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
