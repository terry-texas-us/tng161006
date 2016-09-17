<?php

$criteria_limit = 8;
$criteria_count = 0;

function buildColumn($qualifier, $column, $usevalue) {
  $criteria = '';
  switch ($qualifier) {
    case 'equals':
      $criteria .= "$column = \"$usevalue\"";
      $qualifystr = uiTextSnippet('equals');
      break;
    case 'startswith':
      $criteria .= "$column LIKE \"$usevalue%\"";
      $qualifystr = uiTextSnippet('startswith');
      break;
    case 'endswith':
      $criteria .= "$column LIKE \"%$usevalue\"";
      $qualifystr = uiTextSnippet('endswith');
      break;
    case 'exists':
      $criteria .= "$column != \"\"";
      $qualifystr = uiTextSnippet('exists');
      break;
    case 'dnexist':
      $criteria .= "$column = \"\"";
      $qualifystr = uiTextSnippet('dnexist');
      break;
    case 'soundexof':
      $criteria .= "SOUNDEX($column) = SOUNDEX(\"$usevalue\")";
      $qualifystr = uiTextSnippet('soundexof');
      break;
    case 'metaphoneof':
      $criteria .= 'metaphone = "' . metaphone($usevalue) . '"';
      $qualifystr = uiTextSnippet('metaphoneof');
      break;
    default:
      $criteria .= "$column LIKE \"%$usevalue%\"";
      $qualifystr = uiTextSnippet('contains');
      break;
  }
  $returnarray['criteria'] = $criteria;
  $returnarray['qualifystr'] = $qualifystr;

  return $returnarray;
}

function buildYearCriteria($column, $colvar, $qualifyvar, $altcolumn, $qualifier, $value, $textstr) {
  global $criteria_limit;
  global $criteria_count;

  if ($qualifier == 'exists' || $qualifier == 'dnexist') {
    $value = '';
  } else {
    $value = urldecode(trim($value));
    $value = addslashes($value);

    $yearstr1 = $altcolumn ? "IF($column!='0000-00-00',YEAR($column),YEAR($altcolumn))" : "YEAR($column)";
    $yearstr2 = $altcolumn ? "IF($column,YEAR($column), YEAR($altcolumn))" : "YEAR($column)";
  }

  $criteria_count++;
  if ($criteria_count >= $criteria_limit) {
    die('sorry');
  }
  $criteria = '';
  $numvalue = is_numeric($value) ? $value : preg_replace('/[^0-9]/', '', $value);
  switch ($qualifier) {
    case 'plusminus2':
      $criteria = "($yearstr1 < $numvalue + 2 AND $yearstr2 > $numvalue - 2)";
      $qualifystr = uiTextSnippet('plusminus2');
      break;
    case 'plusminus5':
      $criteria = "($yearstr1 < $numvalue + 5 AND $yearstr2 > $numvalue - 5)";
      $qualifystr = uiTextSnippet('plusminus5');
      break;
    case 'plusminus10':
      $criteria = "($yearstr1 < $numvalue + 10 AND $yearstr2 > $numvalue - 10)";
      $qualifystr = uiTextSnippet('plusminus10');
      break;
    case 'lessthan':
      $criteria = "($yearstr1 != \"\" AND $yearstr1 < \"$numvalue\")";
      $qualifystr = uiTextSnippet('lessthan');
      break;
    case 'greaterthan':
      $criteria = "$yearstr1 > \"$numvalue\"";
      $qualifystr = uiTextSnippet('greaterthan');
      break;
    case 'lessthanequal':
      $criteria = "($yearstr1 != \"\" AND $yearstr1 <= \"$numvalue\")";
      $qualifystr = uiTextSnippet('lessthanequal');
      break;
    case 'greaterthanequal':
      $criteria = "$yearstr1 >= \"$numvalue\"";
      $qualifystr = uiTextSnippet('greaterthanequal');
      break;
    case 'exists':
      $criteria = "YEAR($column) != \"\"";
      if ($altcolumn) {
        $criteria = "($criteria OR YEAR($altcolumn) != \"\")";
      }
      $qualifystr = uiTextSnippet('exists');
      break;
    case 'dnexist':
      $criteria = "YEAR($column) = \"\"";
      if ($altcolumn) {
        $criteria .= " AND YEAR($altcolumn) = \"\"";
      }
      $qualifystr = uiTextSnippet('dnexist');
      break;
    case 'equals':
    default:
      $criteria = "$yearstr1 = \"$value\"";
      $qualifystr = uiTextSnippet('equalto');
      break;
  }
  addtoQuery($textstr, $colvar, $criteria, $qualifyvar, $qualifier, $qualifystr, $value);
}

function addtoQuery($textstr, $colvar, $criteria, $qualifyvar, $qualifier, $qualifystr, $value) {
  global $allwhere;
  global $mybool;
  global $querystring;
  global $urlstring;
  global $mybooltext;

  if ($urlstring) {
    $urlstring .= '&amp;';
  }
  $urlstring .= "$colvar=" . urlencode($value) . "&amp;$qualifyvar=$qualifier";

  if ($querystring) {
    $querystring .= " $mybooltext ";
  }
  if ($textstr == uiTextSnippet('gender')) {
    switch ($value) {
      case 'M':
        $value = uiTextSnippet('male');
        break;
      case 'F':
        $value = uiTextSnippet('female');
        break;
      case 'U':
        $value = uiTextSnippet('unknown');
        break;
      case '':
      case 'N':
        $value = uiTextSnippet('none');
        break;
    }
  }
  $querystring .= "$textstr $qualifystr " . stripslashes($value);

  if ($criteria) {
    if ($allwhere) {
      $allwhere .= ' ' . $mybool;
    }
    $allwhere .= ' ' . $criteria;
  }
}

function doCustomEvents($type) {
  global $dontdo;
  global $cejoin;
  global $allwhere;
  global $mybool;

  $cejoin = '';
  $query = "SELECT eventtypeID, tag, display FROM eventtypes WHERE keep = '1' AND type = '$type' ORDER BY display";
  $result = tng_query($query);
  $needce = 0;
  $ecount = 0;
  if ($type == 'F') {
    $persfamfield = 'f.familyID';
  } else { //assume for now that $type == 'I'
    $persfamfield = 'p.personID';
  }

  while ($row = tng_fetch_assoc($result)) {
    if (!in_array($row['tag'], $dontdo)) {
      $needecount = 1;
      $display = getEventDisplay($row['display']);

      $cefstr = 'cef' . $row['eventtypeID'];
      eval("global \$$cefstr;");
      eval("\$cef = \$$cefstr;");
      $cfqstr = "cfq{$row['eventtypeID']}";
      eval("global \$$cfqstr;");
      eval("\$cfq = \$$cfqstr;");
      if ($cef || $cfq == 'exists' || $cfq == 'dnexist') {
        if ($needecount) {
          $needecount = 0;
          $ecount++;
        }
        $tablepfx = "e$ecount.";
        buildCriteria($tablepfx . 'info', $cefstr, $cfqstr, $cfq, $cef, "$display (" . uiTextSnippet('fact') . ')');
        $needce = 1;
      }
      $cepstr = 'cep' . $row['eventtypeID'];
      eval("global \$$cepstr;");
      eval("\$cep = \$$cepstr;");
      $cpqstr = 'cpq' . $row['eventtypeID'];
      eval("global \$$cpqstr;");
      eval("\$cpq = \$$cpqstr;");
      if ($cep || $cpq == 'exists' || $cpq == 'dnexist') {
        if ($needecount) {
          $needecount = 0;
          $ecount++;
        }
        $tablepfx = "e$ecount.";
        buildCriteria($tablepfx . 'eventplace', $cepstr, $cpqstr, $cpq, $cep, "$display (" . uiTextSnippet('place') . ')');
        $needce = 1;
      }
      $ceystr = 'cey' . $row['eventtypeID'];
      eval("global \$$ceystr;");
      eval("\$cey = \$$ceystr;");
      $cyqstr = 'cyq' . $row['eventtypeID'];
      eval("global \$$cyqstr;");
      eval("\$cyq = \$$cyqstr;");
      if ($cey || $cyq == 'exists' || $cyq == 'dnexist') {
        if ($needecount) {
          $needecount = 0;
          $ecount++;
        }
        $tablepfx = "e$ecount.";
        buildYearCriteria($tablepfx . 'eventdatetr', $ceystr, $cyqstr, '', $cyq, $cey, "$display (" . uiTextSnippet('year') . ')');
        $needce = 1;
      }
      if ($needce) {
        if ($mybool == 'AND') {
          $cejoin .= "INNER JOIN events as e$ecount ON $persfamfield = $tablepfx" . 'persfamID ';
          if ($allwhere) {
            $allwhere .= " $mybool ";
          }
          $allwhere .= $tablepfx . "eventtypeID = \"{$row['eventtypeID']}\" ";
        } else {    //OR
          $cejoin .= "LEFT JOIN events as e$ecount ON $persfamfield = $tablepfx" . "persfamID AND $tablepfx" . "eventtypeID = \"{$row['eventtypeID']}\" ";
        }
        $needce = 0;
      }
    }
  }
  tng_free_result($result);
  return $cejoin;
}