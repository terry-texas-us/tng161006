<?php

function treeSelect($treeresult, $formname = null)
{
  global $tree;

  $ret = "<label for='tree'>" . uiTextSnippet('tree') . "</label>";
  $ret .= "<select class='form-control' id='treeselect' name='tree'";
  if ($formname) {
    $ret .= " onchange=\"$('#treespinner').show(); document.$formname.submit();\"";
  }
  $ret .= ">\n";
  $ret .= "<option value='-x--all--x-'";
  if (!$tree) {
    $ret .= " selected";
  }
  $ret .= ">" . uiTextSnippet('alltrees') . "</option>\n";

  while ($row = tng_fetch_assoc($treeresult)) {
    $ret .= "<option value='{$row['gedcom']}'";
    if ($tree && $row['gedcom'] == $tree) {
      $ret .= " selected";
    }
    $ret .= ">{$row['treename']}</option>\n";
  }
  $ret .= "</select>\n";
  return $ret;
}

function treeDropdown($forminfo)
{
  global $requirelogin;
  global $assignedtree;
  global $trees_table;
  global $time_offset;
  global $treerestrict;
  global $tree;
  global $numtrees;
  global $tngconfig;

  $ret = "";
  if (!$requirelogin || !$treerestrict || !$assignedtree) {
    $query = "SELECT gedcom, treename, lastimportdate FROM $trees_table ORDER BY treename";
    $treeresult = tng_query($query);
    $numtrees = tng_num_rows($treeresult);
    $foundtree = false;

    if ($numtrees > 1) {
      if ($forminfo['startform']) {
        $ret .= buildFormElement($forminfo['action'], $forminfo['method'], $forminfo['name'], $forminfo['id']);
      }
      $ret .= treeSelect($treeresult, $forminfo['name']);
      $ret .= "&nbsp; <img class='spinner' id='treespinner' src='img/spinner.gif' style='display: none;' alt=''>\n";
      if (is_array($forminfo['hidden'])) {
        foreach ($forminfo['hidden'] as $hidden) {
          $ret .= "<input name=\"" . $hidden['name'] . "\" type='hidden' value=\"" . $hidden['value'] . "\" />\n";
        }
      }
      if ($forminfo['endform']) {
        $ret .= "</form><br>\n";
      } else {
        $ret .= "<br><br>\n";
      }
      $treeresult = tng_query($query);
      if ($tree) {
        $foundtree = true;
        while ($row = tng_fetch_assoc($treeresult)) {
          if ($row['gedcom'] == $tree) {
            break;
          }
        }
      }
    } else {
      $foundtree = true;
      $row = tng_fetch_assoc($treeresult);
    }
    if ($tngconfig['lastimport'] && $foundtree && $forminfo['lastimport']) {
      $lastimport = $row['lastimportdate'];

      if ($lastimport) {
        $importtime = strtotime($lastimport);
        if (substr($lastimport, 11, 8) != "00:00:00") {
          $importtime += ($time_offset * 3600);
        }
        $importdate = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? strftime("%#d %b %Y %H:%M:%S", $importtime) : strftime("%e %b %Y %H:%M:%S", $importtime);
        echo "<p>" . uiTextSnippet('lastimportdate') . ": " . displayDate($importdate) . "</p>";
      }
    }
    tng_free_result($treeresult);
  }
  return $ret;
}

