<?php
require 'tng_begin.php';

require 'functions.php';
if (!isset($csv)) {
  $csv = false;
}

if (!is_numeric($reportID)) {
  die("Sorry!");
}

function processfield($field)
{
  global $need_families, $cejoins, $evfields, $people_table, $events_table, $familyfields_nonss;

  if (in_array($field, $familyfields_nonss)) {
    $newfield = "if(sex='M',families1." . $field . ",families2." . $field . ")";
    $need_families = 1;
  } elseif (substr($field, 0, 2) == "ss") {
    $newfield = "if(sex='M',families1." . substr($field, 1) . ",families2." . substr($field, 1) . ")";
    $need_families = 1;
  } elseif (substr($field, 0, 2) == "ce") {
    $eventtypeID = substr($field, 6);
    $subtype = substr($field, 3, 2);
    $newfield = "e$eventtypeID.$evfields[$subtype]";
    if (!isset($cejoins[$eventtypeID])) {
      $cejoins[$eventtypeID] = "LEFT JOIN $events_table e$eventtypeID ON $people_table.personID = e$eventtypeID.persfamID AND e$eventtypeID.eventtypeID = \"$eventtypeID\"";
    }
  } else {
    $newfield = $field;
  }
  return $newfield;
}
$ldsfields = ["baptdate", "baptplace", "confdate", "confplace", "initdate", "initplace", "endldate", "endlplace", "ssealdate", "ssealplace", "psealdate", "psealplace"];

$max_browsesearch_pages = 5;
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $newoffset = "";
  $offsetplus = 1;
  $page = 1;
}

$query = "SELECT * FROM $reports_table WHERE reportID = $reportID";
$testurl = $test ? "&amp;test=$test" : "";
$result = tng_query($query);
$rrow = tng_fetch_assoc($result);
if (!tng_num_rows($result) || (!$rrow['active'] && !$allow_admin)) {
  tng_free_result($result);
  header("Location: thispagedoesnotexist.html");
  exit;
} else {
  tng_free_result($result);
}

if ($rrow['sqlselect']) {
  $rrow['sqlselect'] = preg_replace("/\r\n/", " ", $rrow['sqlselect']);
  $rrow['sqlselect'] = preg_replace("/\r/", " ", $rrow['sqlselect']);
  $rrow['sqlselect'] = preg_replace("/\n/", " ", $rrow['sqlselect']);
  if (strtoupper(substr($rrow['sqlselect'], 0, 7)) == "SELECT ") {
    $sqlselect = substr($rrow['sqlselect'], 7);
    $query = $rrow['sqlselect'];
  } else {
    $sqlselect = $rrow['sqlselect'];
    $query = "SELECT " . $rrow['sqlselect'];
  }
  $fromat = strpos(strtoupper($sqlselect), " FROM ");
  $from = substr($sqlselect, $fromat);
  $sqlselect = substr($sqlselect, 0, $fromat);

  $displayfields = explode(",", $sqlselect);
  $newds = [];
  $newdsctr = 0;
  for ($i = 0; $i < count($displayfields); $i++) {
    $displaymsg = "";
    $tempmsg = $displayfields[$i];
    do {
      $numopen = substr_count($tempmsg, "(");
      $numclosed = substr_count($tempmsg, ")");
      if ($numopen != $numclosed && $i < (count($displayfields) - 1)) {
        $i++;
        $tempmsg = $tempmsg . "," . $displayfields[$i];
      }
    } while ($numopen != $numclosed);

    $gotas = strpos(strtoupper($displayfields[$i]), " AS ");
    $gotas = strpos(strtoupper($tempmsg), " AS ");
    if ($gotas) {
      $tempmsg = substr($tempmsg, $gotas + 4);
    } else {
      $gotperiod = strpos($tempmsg, ".");
      if ($gotperiod) {
        $tempmsg = substr($tempmsg, $gotperiod + 1);
      }
    }
    $dfield = $tempmsg = trim($tempmsg);
    if ($dfield == "personID") {
      $dfield = "personid";
      $gotpersonid = true;
    } else {
      $gotpersonid = false;
    }
    if (!$displaymsg) {
      $displaymsg = uiTextSnippet($dfield);
    }
    if (!$displaymsg) {
      $displaymsg = uiTextSnippet(strtolower($dfield));
    }
    if (!$displaymsg) {
      $displaymsg = $dfield;
    }
    $displaytext .= $csv ? ($displaytext ? ",\"$displaymsg\"" : "\"$displaymsg\"") : "<td>$displaymsg</td>\n";

    $newds[$newdsctr] = $tempmsg;
    $newdsctr++;
  }
  $newds[$newdsctr] = "";
  $displayfields = $newds;
  $query = str_replace(";", "", $rrow['sqlselect']);
} else {
  $peopletreestr = "";
  $childrentreestr = "";
  
  $treestr = $peopletreestr;
  $trees_join = "";

  $need_families = 0;
  $need_children = 0;

  $subtypes = [];
  $evfields['dt'] = "eventdate";
  $evfields['tr'] = "eventdatetr";
  $evfields['pl'] = "eventplace";
  $evfields['fa'] = "info";

  $subtypes['dt'] = uiTextSnippet('date');
  $subtypes['pl'] = uiTextSnippet('place');
  $subtypes['fa'] = uiTextSnippet('fact');

  $displaytext = "";
  $truedates = ["birthdatetr", "altbirthdatetr", "deathdatetr", "burialdatetr", "baptdatetr", "confdatetr", "initdatetr", "endldatetr", "ssealdatetr", "psealdatetr", "marrdatetr", "changedate"];
  $familyfields = ["marrdate", "marrdatetr", "marrplace", "divdate", "divdatetr", "divplace", "ssealdate", "ssealdatetr", "ssealplace"];
  $familyfields_nonss = ["marrdate", "marrdatetr", "marrplace", "divdate", "divdatetr", "divplace"];
  $cejoins = [];

  $displaystr = "$people_table.living, $people_table.private, lnprefix, prefix, suffix, $people_table.branch";
  $displayfields = explode($lineending, $rrow['display']);
  $dtreestr = "";
  for ($i = 0; $i < count($displayfields) - 1; $i++) {
    $dfield = $displayfields[$i];
    $tngprefix = substr($dfield, 0, 2);
    $displaymsg = "";

    if ($dfield != "personID" && (determineLDSRights() || !in_array($dfield, $ldsfields))) {
      if ($displaystr) {
        $displaystr .= ",";
      }
      if (in_array($dfield, $familyfields_nonss)) {
        if ($dfield == "marrdatetr" || $dfield == "divdatetr" || $dfield == "ssealdatetr") {
          $displayfields[$i] = "if(sex='M',DATE_FORMAT(families1." . $dfield . ",'%d %b %Y'),DATE_FORMAT(families2." . $dfield . ",'%d %b %Y'))";
        } else {
          $displayfields[$i] = "if(sex='M',families1." . $dfield . ",families2." . $dfield . ")";
        }
        $need_families = 1;
      }
      if ($tngprefix == "ss") {
        $need_families = 1;
        $displayfields[$i] = "if(sex='M',families1." . substr($dfield, 1) . ",families2." . substr($dfield, 1) . ")";
      }
      if ($tngprefix == "ps") {
        $displayfields[$i] = "$children_table." . substr($dfield, 1);
        $need_children = 1;
      }
      if (substr($dfield, 0, 6) == "spouse") {
        $need_families = 1;
        $displaystr .= "(if(sex='M',families1.wife,families2.husband)) as spouse";
      } elseif (in_array($dfield, $truedates)) {
        $displaystr .= "DATE_FORMAT($people_table.$dfield,'%d %b %Y') as $dfield" . "_disp";
      } elseif ($dfield == "gedcom") {
        $trees_join = ", $treesTable";
        if (!$dtreestr) {
          $dtreestr = " $people_table.gedcom = $treesTable.gedcom";
        }
        $displaystr .= "treename";
        $displayfields[$i] = "treename";
      } elseif ($tngprefix == "ce") {
        $eventtypeID = substr($dfield, 6);
        $query = "SELECT display FROM $eventtypes_table WHERE eventtypeID=\"$eventtypeID\"";
        $evresult = tng_query($query);
        $evrow = tng_fetch_assoc($evresult);
        tng_free_result($evresult);

        $subtype = substr($dfield, 3, 2);
        $displaymsg = getEventDisplay($evrow['display']) . ": " . $subtypes[$subtype];

        $displaystr .= "e$eventtypeID.$evfields[$subtype] as $evfields[$subtype]$eventtypeID";
        $displayfields[$i] = "$evfields[$subtype]$eventtypeID";
        if (!isset($cejoins[$eventtypeID])) {
          $cejoins[$eventtypeID] = "LEFT JOIN $events_table e$eventtypeID ON $people_table.personID = e$eventtypeID.persfamID AND e$eventtypeID.eventtypeID = '$eventtypeID'";
        }
      } elseif ($dfield == "lastfirst") {
        $displaystr .= "lastname, firstname";
      } elseif ($dfield == "fullname") {
        $displaystr .= "firstname, lastname";
      } else {
        $displaystr .= $displayfields[$i];
      }
    }
    if (!$displaymsg) {
      $displaymsg = uiTextSnippet($dfield);
    }
    if (!$displaymsg) {
      $displaymsg = uiTextSnippet(strtolower($dfield));
    }
    $displaytext .= $csv ? ($displaytext ? ",\"$displaymsg\"" : "\"$displaymsg\"") : "<td>$displaymsg</td>\n";
  }
  if ($dtreestr) {
    if ($treestr) {
      $treestr .= " AND";
    }
    $treestr .= $dtreestr;
  }
  $displaystr .= ", $people_table.personID, $people_table.gedcom, nameorder";

  $criteriastr = "";
  $criteriafields = explode($lineending, $rrow['criteria']);
  $mnemonics = ["eq", "neq", "gt", "gte", "lt", "lte"];
  $symbols = ["=", "!=", ">", ">=", "<", "<="];
  for ($i = 0; $i < count($criteriafields) - 1; $i++) {
    $table = "";
    if ($criteriastr) {
      $criteriastr .= " ";
    }
    if (in_array($criteriafields[$i], $familyfields)) {
      $need_families = 1;
    }

    if ($criteriafields[$i] == "currmonth") {
      $criteriafields[$i] = "\"" . strtoupper(date("M", time() + (3600 * $timeOffset))) . "\"";
    } else {
      if ($criteriafields[$i] == "currmonthnum") {
        $criteriafields[$i] = "\"" . date("m", time() + (3600 * $timeOffset)) . "\"";
      } else {
        if ($criteriafields[$i] == "curryear") {
          $criteriafields[$i] = "\"" . date("Y", time() + (3600 * $timeOffset)) . "\"";
        } else {
          if ($criteriafields[$i] == "currday") {
            $criteriafields[$i] = "\"" . date("d", time() + (3600 * $timeOffset)) . "\"";
          } else {
            if ($criteriafields[$i] == "personID") {
              $criteriafields[$i] = "$people_table.personID";
            } else {
              if ($criteriafields[$i] == "today") {
                if ($timeOffset) {
                  if ($timeOffset > 0) {
                    $criteriafields[$i] .= "DATE_ADD(NOW(), INTERVAL " . $timeOffset . " HOUR)";
                  } else {
                    $criteriafields[$i] .= "DATE_SUB(NOW(), INTERVAL " . $timeOffset . " HOUR)";
                  }
                } else {
                  $criteriafields[$i] = "NOW()";
                }
                $truedate = 1;
              } else {
                if (in_array($criteriafields[$i], $truedates)) {
                  $truedate = 1;
                }
              }
            }
          }
        }
      }
    }

    switch ($criteriafields[$i]) {
      case "dayonly":
      case "monthonly":
      case "yearonly":
      case "to_days":
        $criteriastr .= "";
        break;
      case "contains":
      case "starts with":
      case "ends with":
        $criteriastr .= "LIKE";
        break;
      case "living":
        $criteriastr .= "$people_table.living = 1";
        break;
      case "dead":
        $criteriastr .= "$people_table.living != 1";
        break;
      case "private":
        $criteriastr .= "$people_table.private = 1";
        break;
      case "open":
        $criteriastr .= "$people_table.private != 1";
        break;
      default:
        if (in_array($criteriafields[$i], $familyfields_nonss)) {
          $newcriteria = "if(sex='M',families1." . $criteriafields[$i] . ",families2." . $criteriafields[$i] . ")";
          $need_families = 1;
        } else {
          if (substr($criteriafields[$i], 0, 2) == "ce") {
            $eventtypeID = substr($criteriafields[$i], 6);
            $subtype = substr($criteriafields[$i], 3, 2);
            $newcriteria = "e$eventtypeID.$evfields[$subtype]";
            if (!isset($cejoins[$eventtypeID])) {
              $cejoins[$eventtypeID] = "LEFT JOIN $events_table e$eventtypeID ON $people_table.personID = e$eventtypeID.persfamID AND e$eventtypeID.eventtypeID = '$eventtypeID'";
            }
          } else {
            $newcriteria = $criteriafields[$i];
            if ($newcriteria == "changedate") {
              $newcriteria = "$people_table." . $newcriteria;
            } else {
              $position = array_search($newcriteria, $mnemonics);
              if ($position !== false) {
                $newcriteria = $symbols[$position];
              }
            }
          }
        }

        switch ($criteriafields[$i - 1]) {
          case "dayonly":
            if ($truedate) {
              $criteriastr .= "DAYOFMONTH($newcriteria)";
            } else {
              $criteriastr .= "LPAD(SUBSTRING_INDEX($newcriteria, ' ', 1),2,'0')";
            }
            break;
          case "monthonly":
            if ($truedate) {
              $criteriastr .= "MONTH($newcriteria)";
            } else {
              $criteriastr .= "substring(SUBSTRING_INDEX($newcriteria, ' ', -2),1,3)";
            }
            break;
          case "yearonly":
            if ($truedate) {
              $criteriastr .= "YEAR($newcriteria)";
            } else {
              $criteriastr .= "LPAD(SUBSTRING_INDEX($newcriteria, ' ', -1),4,'0')";
            }
            break;
          case "to_days":
            if ($truedate) {
              $criteriastr .= "TO_DAYS($newcriteria)";
            } else {
              $criteriastr .= "LPAD(SUBSTRING_INDEX($newcriteria, ' ', -1),4,'0')";
            }
            break;
          case "contains":
            if (substr($criteriafields[$i], 0, 1) == "\"") {
              $newcriteria = substr($criteriafields[$i], 1, -1);
            }
            $criteriastr .= "\"%" . $newcriteria . "%\"";
            break;
          case "starts with":
            if (substr($criteriafields[$i], 0, 1) == "\"") {
              $newcriteria = substr($criteriafields[$i], 1, -1);
            }
            $criteriastr .= "\"" . $newcriteria . "%\"";
            break;
          case "ends with":
            if (substr($criteriafields[$i], 0, 1) == "\"") {
              $newcriteria = substr($criteriafields[$i], 1, -1);
            }
            $criteriastr .= "\"%" . $newcriteria . "\"";
            break;
          default:
            if (substr($criteriafields[$i], 0, 2) == "ps") {
              $criteriastr .= "$children_table." . substr($criteriafields[$i], 1);
              $need_children = 1;
            } else {
              if (substr($criteriafields[$i], 0, 2) == "ss") {
                $criteriastr .= "if(sex='M',families1." . substr($criteriafields[$i], 1) . ",families2." . substr($criteriafields[$i], 1) . ")";
                $need_families = 1;
              } else {
                if ($criteriafields[$i] == "gedcom") {
                  $criteriastr .= "$people_table.$newcriteria";
                } else {
                  $criteriastr .= $newcriteria;
                }
              }
            }
            break;
        }
        break;
    }
  }

  $more = getLivingPrivateRestrictions($people_table, null, null);
  if ($more) {
    if ($criteriastr) {
      $criteriastr = "($criteriastr) AND ";
    }
    $criteriastr .= $more;
  }
  if ($criteriastr) {
    $criteriastr = "WHERE ($criteriastr)";
  }

  $orderbystr = "";
  $orderbyfields = explode($lineending, $rrow['orderby']);
  for ($i = 0; $i < count($orderbyfields) - 1; $i++) {
    if ($orderbystr) {
      if ($orderbyfields[$i] == "desc") {
        $orderbystr .= " ";
      } else {
        $orderbystr .= ",";
      }
    }
    $tngprefix = "";
    if ($orderbyfields[$i] == "dayonly") {
      $i++;
      $newfield = processfield($orderbyfields[$i]);
      if (in_array($orderbyfields[$i], $truedates)) {
        $newfield = "DAYOFMONTH($newfield)";
      } else {
        $newfield = "LPAD(SUBSTRING_INDEX($newfield, ' ', 1),2,'0')";
      }
      $displaystr .= ", $newfield as dayonly$orderbyfields[$i]";
      $orderbystr .= "dayonly$orderbyfields[$i]";
    } else {
      if ($orderbyfields[$i] == "monthonly") {
        $i++;
        $newfield = processfield($orderbyfields[$i]);
        if (in_array($orderbyfields[$i], $truedates)) {
          $newfield = "MONTH($newfield)";
        } else {
          $newfield = "SUBSTRING_INDEX($newfield, ' ', 2)";
        }
        $displaystr .= ", $newfield as monthonly$orderbyfields[$i]";
        $orderbystr .= "monthonly$orderbyfields[$i]";
      } else {
        if ($orderbyfields[$i] == "yearonly") {
          $i++;
          $newfield = processfield($orderbyfields[$i]);
          if (in_array($orderfields[$i], $truedates)) {
            $newfield = "YEAR($newfield)";
          } else {
            $newfield = "LPAD(SUBSTRING_INDEX($newfield, ' ', -1),4,'0')";
          }
          $displaystr .= ", $newfield as yearonly$orderbyfields[$i]";
          $orderbystr .= "yearonly$orderbyfields[$i]";
        } else {
          if ($orderbyfields[$i] == "personID") {
            $orderbystr .= "$people_table.personID";
          } else {
            if (substr($orderbyfields[$i], 0, 2) == "ps") {
              $orderbystr .= "$children_table." . substr($orderbyfields[$i], 1);
              $need_children = 1;
            } else {
              $orderbystr .= processfield($orderbyfields[$i]);
            }
          }
        }
      }
    }
  }
  if ($orderbystr) {
    $orderbystr = "ORDER BY $orderbystr";
  }
  if ($need_families) {
    $families_join = "LEFT JOIN $families_table AS families1 ON ($people_table.personID = families1.husband) LEFT JOIN $families_table AS families2 ON ($people_table.personID = families2.wife) ";
  } else {
    $families_join = "";
  }
  if ($need_children) {
    $children_join = "LEFT JOIN $children_table ON $people_table.personID = $children_table.personID";
    if ($childrentreestr) {
      $treestr .= " AND $childrentreestr";
    }
  } else {
    $children_join = "";
  }

  if ($treestr) {
    $treestr = $criteriastr ? "AND $treestr" : "WHERE $treestr";
  }

  $cejoin = "";
  foreach ($cejoins as $join) {
    $cejoin .= " $join";
  }

  $query = "SELECT $displaystr FROM ($people_table $trees_join) $families_join $children_join $cejoin $criteriastr $treestr $orderbystr";
}
//echo $query . " LIMIT $newoffset" . $maxsearchresults;
$limitstr = $csv ? "" : " LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query . $limitstr);

if ($rrow['active'] && !$csv) {
  $logstring = "<a href=\"reportsShowReport.php?reportID=$reportID\">" . xmlcharacters(uiTextSnippet('report') . ": {$rrow['reportname']}") . "</a>";
  writelog($logstring);
  preparebookmark($logstring);
}
if ($csv) {
  header("Content-type:text/html;charset=" . $session_charset);
  $truncname = str_replace(" ", "-", strtolower($rrow['reportname']));
  header("Content-Disposition: attachment; filename={$truncname}.csv\n\n");
} else {
  scriptsManager::setShowShare($tngconfig['showshare'], $http);
  initMediaTypes();

  header("Content-type: text/html; charset=" . $session_charset);
  $headSection->setTitle($rrow['reportname']);
  ?>
  <!DOCTYPE html>
  <html>
  <?php echo $headSection->build($flags, 'public', $session_charset); ?>
  <body id='public'>
  <?php echo $publicHeaderSection->build(); ?>
  <h2><img class='icon-md' src='svg/print.svg'><?php echo "" . uiTextSnippet('report') . ": {$rrow['reportname']}"; ?></h2>
  <p>&nbsp;<?php echo uiTextSnippet('description') . ": " . nl2br($rrow['reportdesc']); ?></p>
  <br clear='left'>
  <?php
  if ($test) {
    echo "<p><strong>SQL:</strong> $query</p>\n";
  }
}
if (!$result) {
  ?>

  <p><?php echo "<b>" . uiTextSnippet('error') . ":</b> " . uiTextSnippet('reportsyntax') . " (ID: {$rrow['reportID']}) " . uiTextSnippet('wasincorrect') . " "; ?>
    <?php echo "<a href=\"mailto:$emailaddr\">$emailaddr</a>"; ?>.</p>
  <p><?php echo uiTextSnippet('query') . ": $query <br>" . uiTextSnippet('errormessage') . ":"; ?>
    <?php echo tng_error(); ?></p>

  <?php
} else {
  if (!$csv) {
    $numrows = tng_num_rows($result);
    if ($numrows == $maxsearchresults || $offsetplus > 1) {
      if ($rrow['sqlselect']) {
        if ($gotpersonid) {
          $query = "SELECT count( $people_table.personID ) as rcount $from";
        } else {
          $result2 = tng_query($query);
          $totrows = tng_num_rows($result2);
          $query = "";
        }
      } else {
        $query = "SELECT count($people_table.personID) as rcount FROM ($people_table $trees_join) $families_join $children_join $cejoin $criteriastr $treestr";
      }
      if ($query) {
        $result2 = tng_query($query);
        $countrow = tng_fetch_assoc($result2);
        $totrows = $countrow['rcount'];
      }
    } else {
      $totrows = $numrows;
    }

    $numrowsplus = $numrows + $offset;
    if ($totrows) {
      echo "<p>" . uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows &nbsp; <a href=\"reportsShowReport.php?reportID=$reportID&amp;csv=1\" target='_blank'>&raquo; " . uiTextSnippet('csv') . "</a></p>";
    }
  }
  if ($csv) {
    echo $displaytext . $lineending;
  } else {
    ?>

    <table class='table table-sm'>
    <tr>
      <td>#</td>
      <?php
      //Column headings print here
      echo $displaytext;
      ?>
    </tr>
    <?php
  }
  $rowcount = $offset;
  $treestr = "";
  while ($row = tng_fetch_assoc($result)) {
    $rowcount++;

    $rights = determineLivingPrivateRights($row);
    $row['allow_living'] = $rights['living'];
    $row['allow_private'] = $rights['private'];

    if (!$csv) {
      echo "<tr>\n";
      echo "<td><span>$rowcount</span></td>\n";
    }
    $datastr = "";
    for ($i = 0; $i < count($displayfields) - 1; $i++) {
      $thisfield = $displayfields[$i];
      if ($thisfield == "lastfirst") {
        $data = $csv ? getNameRev($row) : "<a href=\"peopleShowPerson.php?personID={$row['personID']}\">" . getNameRev($row) . "</a>";
      } else {
        if ($thisfield == "fullname") {
          $namestr = getName($row);
          $data = $csv ? $namestr : showSmallPhoto($row['personID'], $namestr, $rights['both'], 0, false, $row['sex']) . "<a href=\"peopleShowPerson.php?personID={$row['personID']}\">$namestr</a>";
        } else {
          if (strtoupper(substr($thisfield, -8)) == strtoupper("personID")) {
            $data = $csv ? $row[$thisfield] : "<a href=\"peopleShowPerson.php?personID=$row[$thisfield]\">$row[$thisfield]</a>";
          } else {
            if ($thisfield == "treename") {
              $data = $csv ? $row['treename'] : "<a href=\"showtree.php?tree={$row['gedcom']}\">{$row['treename']}</a>";
            } else {
              if (substr($thisfield, 0, 6) == "spouse") {
                $spouseID = $row['spouse'];
                if ($thisfield == "spousename") {
                  $query = "SELECT lastname, lnprefix, firstname, prefix, suffix, nameorder, living, private, branch FROM $people_table WHERE personID = '$spouseID'";
                  $spresult = tng_query($query);
                  if ($spresult) {
                    $sprow = tng_fetch_assoc($spresult);

                    $srights = determineLivingPrivateRights($sprow);
                    $sprow['allow_living'] = $srights['living'];
                    $sprow['allow_private'] = $srights['private'];

                    $data = $csv ? getName($sprow) : "<a href=\"peopleShowPerson.php?personID=$spouseID\">" . getName($sprow) . "</a>";
                    tng_free_result($spresult);
                  } else {
                    $data = "";
                  }
                } else {
                  $data = $csv ? $spouseID : "<a href=\"peopleShowPerson.php?personID=$spouseID\">$spouseID</a>";
                }
              } else {
                if ($rights['both'] && (!in_array($thisfield, $ldsfields) || $rights['lds'])) {
                  if (strpos($thisfield, "date")) {
                    $tempdate = $row[$thisfield];
                    if ($tempdate) {
                      $rawdate = $tempdate;
                    } else {
                      $datedisp = $thisfield . "_disp";
                      $rawdate = $row[$datedisp];
                    }
                    $data = displayDate($rawdate);
                  } else {
                    $data = nl2br($row[$thisfield]);
                    if (strpos($thisfield, "place") && $data && !$csv) {
                      $data .= " <a href=\"placesearch.php?{$treestr}psearch=" . urlencode($data) . "\"><img class='icon-xs-inline' src='svg/magnifying-glass.svg' alt=''></a>";
                    }
                  }
                } else {
                  $data = "";
                }
              }
            }
          }
        }
      }
      if ($csv) {
        $data = str_replace("\"", "\"\"", $data);
      }
      $datastr .= $csv ? ($datastr ? ",\"$data\"" : "\"$data\"") : "<td><span>$data&nbsp;</span></td>\n";
    }
    echo $datastr . $lineending;

    if (!$csv) {
      echo "</tr>\n";
    }
  }
  tng_free_result($result);
  if (!$csv) {
    ?>
    </table>
    <?php
    echo buildSearchResultPagination($totrows, "reportsShowReport.php?reportID=$reportID$testurl&amp;offset", $maxsearchresults, $max_browsesearch_pages);
  }
}
if (!$csv) {
  echo $publicFooterSection->build();
  echo scriptsManager::buildScriptElements($flags, 'public');
}
?>
</body>
</html>