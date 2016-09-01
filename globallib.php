<?php

ini_set("session.bug_compat_warn", "0");
ini_set("allow_url_fopen", "0");
$http = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')) ? 'https' : 'http';

set_time_limit(0);
//set binary to "binary" for more sensitive searches
$binary = "";
$notrunc = 0; //don't truncate if link doesn't go to showmedia
$envelope = false;

if (isset($offset) && $offset && !is_numeric($offset)) {
  die("invalid offset");
}
$endrootpath = "";

$newroot = preg_replace("/\//", "", $rootpath);
$newroot = preg_replace("/ /", "", $newroot);
$newroot = preg_replace("/\./", "", $newroot);
$errorcookiename = "tngerror_$newroot";

if (isset($_COOKIE[$errorcookiename])) {
  $message = $_COOKIE[$errorcookiename];
  $error = $message;
  setcookie("tngerror_$newroot", "", time() - 31536000, "/");
} else {
  $error = "";
}

require_once '_/components/php/textSnippets.php';

function debugPrint($obj) {
  echo "<pre>\n";
  print_r($obj);
  echo "</pre>\n";
}

function constructName($firstnames, $lastnames, $title, $suffix, $order) {
  if ($title) {
    $title .= " ";
  }
  if ($firstnames) {
    $firstnames .= " ";
  }

  switch ($order) {
    case "3":
      if ($lastnames && $firstnames) {
        $lastnames .= ",";
      }
      if ($lastnames) {
        $lastnames .= " ";
      }
      $namestr = trim("$lastnames $title$firstnames$suffix");
      break;
    case "2":
      if ($lastnames) {
        $lastnames .= " ";
      }
      $namestr = trim("$title$lastnames$firstnames");
      if ($suffix) {
        $namestr .= ", $suffix";
      }
      break;
    default:
      $namestr = trim("$title$firstnames$lastnames");
      if ($suffix) {
        $namestr .= ", $suffix";
      }
      break;
  }

  return $namestr;
}

function getName($row, $hcard = null) {
  global $nameorder;

  $locnameorder = $row['nameorder'] ? $row['nameorder'] : ($nameorder ? $nameorder : 1);
  $namestr = getNameUniversal($row, $locnameorder, $hcard);

  return $namestr;
}

function getNameRev($row, $hcard = null) {
  global $nameorder;

  $locnameorder = $row['nameorder'] ? $row['nameorder'] : ($nameorder ? $nameorder : 1);
  if ($locnameorder != 2) {
    $locnameorder = 3;
  }
  $namestr = getNameUniversal($row, $locnameorder, $hcard);

  return $namestr;
}

function getNameUniversal($row, $order, $hcard = null) {
  global $tngconfig;
  global $nonames;

  //$nonames = showNames($row);
  $lastname = trim($row['lnprefix'] . " " . $row['lastname']);
  if ($tngconfig['ucsurnames']) {
    $lastname = tng_strtoupper($lastname);
  }
  if ($hcard) {
    $lastname = "<span class=\"family-name\">" . $lastname . "</span>";
    $title = $suffix = "";
  } else {
    $title = $row['title'] && ($row['title'] == $row['prefix']) ? $row['title'] : trim($row['title'] . " " . $row['prefix']);
    $suffix = $row['suffix'];
  }
  if (($row['allow_living'] || !$nonames) && ($row['allow_private'] || !$tngconfig['nnpriv'])) {
    $firstname = $hcard ? "<span class=\"given-name\">" . $row['firstname'] . "</span>" : $row['firstname'];
    $namestr = constructName($firstname, $lastname, $title, $suffix, $order);
  } elseif ($row['living'] && !$row['allow_living'] && $nonames == 1) {
    $namestr = uiTextSnippet('living');
  } elseif ($row['private'] && !$row['allow_private'] && $tngconfig['nnpriv'] == 1) {
    $namestr = uiTextSnippet('private');
  } else { //initials
    $firstname = $hcard ? "<span class=\"given-name\">" . initials($row['firstname']) . "</span>" : initials($row['firstname']);
    $namestr = constructName($firstname, $lastname, $title, $suffix, $order);
  }

  if ($hcard) {
    $namestr = "<span class=\"n\">$namestr</span>";
  }
  return $namestr;
}

function getFamilyName($row) {
  global $people_table;

  $hquery = "SELECT firstname, lnprefix, lastname, title, prefix, suffix, living, private, branch, nameorder FROM $people_table WHERE personID = '{$row['husband']}'";
  $hresult = tng_query($hquery) or die(uiTextSnippet('cannotexecutequery') . ": $hquery");
  $hrow = tng_fetch_assoc($hresult);

  $hrights = determineLivingPrivateRights($hrow);
  $hrow['allow_living'] = $hrights['living'];
  $hrow['allow_private'] = $hrights['private'];

  $husbname = getName($hrow);
  tng_free_result($hresult);

  $wquery = "SELECT firstname, lnprefix, lastname, title, prefix, suffix, living, private, branch, nameorder FROM $people_table WHERE personID = '{$row['wife']}'";
  $wresult = tng_query($wquery) or die(uiTextSnippet('cannotexecutequery') . ": $wquery");
  $wrow = tng_fetch_assoc($wresult);

  $wrights = determineLivingPrivateRights($wrow);
  $wrow['allow_living'] = $wrights['living'];
  $wrow['allow_private'] = $wrights['private'];

  $wifename = getName($wrow);
  tng_free_result($wresult);

  return "$husbname/$wifename ({$row['familyID']})";
}

function initials($name) {
  global $session_charset;

  $newname = "";
  if ($session_charset == "UTF-8") {
    $name = utf8_decode($name);
  }

  $token = strtok($name, " ");
  do {
    if (substr($token, 0, 1) != "(") { //In case there is a name in brackets, in which case ignore
      if ($session_charset == "UTF-8") {
        $newname .= utf8_encode(substr($token, 0, 1)) . ".";
      } else {
        $newname .= substr($token, 0, 1) . ".";
      }
    }
    $token = strtok(" ");
  } while ($token != "");

  return $newname;
}

function showNames($row) {
  global $nonames, $tngconfig;

  return $row['private'] ? $tngconfig['nnpriv'] : $nonames;
}

function getGenderIcon($gender, $valign) {
  $icon = "";
  if ($gender) {
    if ($gender == 'M') {
      $genderstr = "male";
    } elseif ($gender == 'F') {
      $genderstr = "female";
    }
    if ($genderstr) {
      $icon = "<img src=\"img/tng_$genderstr.gif\" width='11' height='11' alt=\"" . uiTextSnippet($genderstr) . "\" style=\"vertical-align: " . $valign . "px;\" />";
    }
  }
  return $icon;
}

function buildFormElement($action, $method, $name, $id = '', $onsubmit = null) {
  $url = $action ? $action . ".php" : "";

  $out = "<form action='$url'";
  if ($method) {
    $out .= " method='$method'";
  }
  if ($name) {
    $out .= " name='$name'";
  }
  if ($id) {
    $out .= " id='$id'";
  }
  if ($onsubmit) {
    $out .= " onsubmit='$onsubmit'";
  }
  $out .= ">\n";

  return $out;
}

function beginFormElement($action, $method, $name = '', $id = '', $onsubmit = null) {
  echo buildFormElement($action, $method, $name, $id, $onsubmit);
}

function endFormElement() {
  echo "</form>\n";
}

function isPhoto($row) {
  global $imagetypes;

  if ($row['form']) {
    $form = strtoupper($row['form']);
  } else {
    preg_match("/\.(.+)$/", $row['path'], $matches);
    $form = strtoupper($matches[1]);
  }

  if ($row['path'] && !$row['abspath'] && in_array($form, $imagetypes)) {
    return true;
  } else {
    return false;
  }
}

function getEventDisplay($displaystr) {
  global $mylanguage, $languagesPath;

  $dispvalues = explode("|", $displaystr);
  $numvalues = count($dispvalues);
  if ($numvalues > 1) {
    $displayval = "";
    for ($i = 0; $i < $numvalues; $i += 2) {
      $lang = $dispvalues[$i];
      if ($mylanguage == $languagesPath . $lang) {
        $displayval = $dispvalues[$i + 1];
        break;
      }
    }
  } else {
    $displayval = $displaystr;
  }

  return $displayval;
}

function checkbranch($branch) {
  global $assignedbranch;

  return (!$assignedbranch || (false !== ($pos = strpos($branch, $assignedbranch, 0)))) ? 1 : 0;
}

// The following function is now obsolete
function determineLivingRights($row, $usedb = 0, $allow_living_db = 0, $allow_private_db = 0) {
  global $livedefault;
  global $allow_living;
  global $allow_private;
  global $rightbranch;

  $allow_living_loc = $usedb ? $allow_living_db : $allow_living;
  $allow_private_loc = $usedb ? $allow_private_db : $allow_private;

  $rightbranch = checkbranch($row['branch']) ? 1 : 0;
  $living = $row['living'];
  $private = $row['private'];

  if (!$private && !$living) {
    $livingrights = 1;
  } else {
    $yes_living = $yes_private = true;
    $user_person = $_SESSION['mypersonID'] && $_SESSION['mypersonID'] == $row['personID'];
    if ($living) {
      if ($livedefault != 2) {     //everyone has living rights
        if ((!$allow_living_loc || !$rightbranch) && !$user_person) {
          $yes_living = false;
        }
      }
    }
    if ($private) {
      if ((!$allow_private_loc || !$rightbranch) && !$user_person) {
        $yes_private = false;
      }
    }
    $livingrights = $yes_living && $yes_private ? 1 : 0;
  }
  return $livingrights;
} // end obsolete function

function determineLivingPrivateRights($row, $pagerightbranch = -1) {
  global $livedefault;
  global $ldsdefault;
  global $allow_living;
  global $allow_private;
  global $allow_lds;

  $rights = ['private' => true, 'living' => true, 'lds' => (!$ldsdefault ? true : false)];

  $living = $livedefault == 2 ? false : $row['living'];
  $private = $row['private'];

  if ($private || $living || $ldsdefault == 2) {
    $rightbranch = $pagerightbranch >= 0 ? $pagerightbranch : (checkbranch($row['branch']));
    $user_person = $_SESSION['mypersonID'] && $_SESSION['mypersonID'] == $row['personID'];

    if ($living && (!$allow_living || !$rightbranch) && !$user_person) {
      $rights['living'] = false;
    }
    if ($private && (!$allow_private || !$rightbranch) && !$user_person) {
      $rights['private'] = false;
    }
    if ($ldsdefault == 2 && (($allow_lds && $rightbranch) || $user_person)) {
      $rights['lds'] = true;
    }
  }
  $rights['both'] = $rights['private'] && $rights['living'];

  return $rights;
}

function determineLDSRights($notree = false) {
  global $ldsdefault;
  global $allow_lds;

  $ldsOK = !$ldsdefault || ($ldsdefault == 2 && $allow_lds) ? true : false;

  return $ldsOK;
}

function getLivingPrivateRestrictions($table, $firstname, $allOtherInput) {
  global $livedefault;
  global $nonames;
  global $tngconfig;
  global $allow_living;
  global $allow_private;
  global $assignedbranch;
  global $people_table;

  $query = "";
  if ($table) {
    $table .= ".";
  }
  $limitedLivingRights = $allow_living && !$livedefault;
  $limitedPrivateRights = $allow_private;
  $allLivingRights = $livedefault == 2 || ($allow_living);
  $allPrivateRights = $allow_private;
  $livingNameRestrictions = $livedefault == 1 || (!$livedefault && ($nonames == 1 || ($nonames == 2 && $firstname)) && !$allLivingRights);
  $privateNameRestrictions = ($tngconfig['nnpriv'] == 1 || ($tngconfig['nnpriv'] == 2 && $firstname)) && !$allPrivateRights;

  if ($livingNameRestrictions || $privateNameRestrictions || $allOtherInput) {
    $atreestr = $matchperson = "";
    if ($_SESSION['mypersonID'] && $table == $people_table) {
      //this is me (current user)
      $matchperson = " OR ({$table}personID = \"{$_SESSION['mypersonID']}\")";
    }
    if (($livingNameRestrictions && $privateNameRestrictions) || ($allOtherInput && !$allLivingRights && !$allPrivateRights)) {
      if ($limitedLivingRights && $limitedPrivateRights) {
        $query .= "(({$table}living != 1 && {$table}private != 1)$atreestr$matchperson)";
      } elseif ($limitedLivingRights) {
        $query .= "({$table}private != 1 && ({$table}living != 1$atreestr$matchperson))";
      } elseif ($limitedPrivateRights) {
        $query .= "({$table}living != 1 && ({$table}private != 1$atreestr$matchperson))";
      } else {
        $query .= "(({$table}living !=1 && {$table}private != 1)$matchperson)";
      }
    } else {
      if ($livingNameRestrictions || ($allOtherInput && !$allLivingRights)) {
        if ($limitedLivingRights) {
          $query .= "({$table}living != 1$atreestr$matchperson)";
        } else {
          $query .= "({$table}living != 1$matchperson)";
        }
      } elseif ($privateNameRestrictions || ($allOtherInput && !$allPrivateRights)) {
        if ($limitedPrivateRights) {
          $query .= "({$table}private != 1$atreestr$matchperson)";
        } else {
          $query .= "({$table}private != 1$matchperson)";
        }
      }
    }
  }

  return $query;
}

function checkLivingLinks($itemID) {
  global $livedefault;
  global $assignedbranch;
  global $people_table;
  global $medialinks_table;
  global $families_table;
  global $allow_living;
  global $allow_private;

  if (($livedefault == 2 || $allow_living) && $allow_private) {
    return true;
  }
  $icriteria = $fcriteria = "";
  if (!$allow_living && !$allow_private) {
    // Viewer can not see media of Living individuals regardless of tree/branch,
    // So need to check all links to this media for living individuals (don't narrow the search.)
    $icriteria = $fcriteria = "AND (living = 1 OR private = 1)";
  } else {
    // Viewer can see some media of Living individuals, now figure if there are some the viewer should not see
    if (!$allow_living && $livedefault != 2) {
      $icriteria = $icriteria ? "AND (living = 1 OR ($icriteria AND private = 1))" : "AND living = 1";
      $fcriteria = $fcriteria ? "AND (living = 1 OR ($fcriteria AND private = 1))" : "AND living = 1";
    } elseif (!$allow_private) {    //!$allow_private_db
      $icriteria = $icriteria ? "AND (private = 1 OR ($icriteria AND living = 1))" : "AND private = 1";
      $fcriteria = $fcriteria ? "AND (private = 1 OR ($fcriteria AND living = 1))" : "AND private = 1";
    } else {
      if ($icriteria) {
        $icriteria = "AND $icriteria AND (living = 1 OR private = 1)";
      }
      if ($fcriteria) {
        $fcriteria = "AND $fcriteria AND (living = 1 OR private = 1)";
      }
    }
  }
  if ($icriteria) {
    // Now find Living individuals linked to the media that fit the criteria set above.
    $query = "SELECT count(*) AS pcount FROM ($medialinks_table, $people_table) WHERE $medialinks_table.personID = $people_table.personID AND $medialinks_table.mediaID = '$itemID' $icriteria";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    tng_free_result($result);
    if ($row['pcount']) {
      return false;
    } // found at least one
  }

  if ($fcriteria) {
    $query = "SELECT count(*) AS pcount FROM ($medialinks_table, $families_table) WHERE $medialinks_table.personID = $families_table.familyID AND $medialinks_table.mediaID = '$itemID' $fcriteria";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    tng_free_result($result);
    if ($row['pcount']) {
      return false;
    } // found at least one
  }
  // so we made it here ok, so there must not be any Living individulals linked to this media
  return true;
}

function checkMediaFileSize($path) {
  global $maxmediafilesize;

  return file_exists($path) && filesize($path) < $maxmediafilesize;
}

function getScriptName($replace = true) {
  global $_SERVER;

  $scriptname = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME'] . "?" . $_SERVER['QUERY_STRING'];
  if ($replace) {
    $scriptname = str_replace("&", "&amp;", $scriptname);
    $scriptname = str_replace("amp;amp;", "amp;", $scriptname);
  }

  return $scriptname;
}

function getScriptPath() {
  $uri = getScriptName();
  return dirname($uri);
}

function buildSearchResultPagination($total, $address, $perpage, $pagenavpages) {
  global $tngpage;
  global $totalpages;

  if (!$tngpage) {
    $tngpage = 1;
  }
  if (!$perpage) {
    $perpage = 50;
  }
  if ($total <= $perpage) {
    return '';
  }
  $totalpages = ceil($total / $perpage);
  if ($tngpage > $totalpages) {
    $tngpage = $totalpages;
  }
  $out = "<nav class='row'>\n";
  $out .= "<div class='col-md-10'>\n";
  $out .= "<ul class='pagination pagination-sm'>\n";

  if ($tngpage > 1) {
    $prevpage = $tngpage - 1;
    $navoffset = (($prevpage * $perpage) - $perpage);

    $out .= "<li class='page-item'>\n";
      $out .= "<a class='page-link' href='$address=$navoffset&amp;tngpage=$prevpage' aria-label='Previous'>\n";
        $out .= "<span aria-hidden='true'>&laquo;</span>\n";
        $out .= "<span class='sr-only'>Previous</span>\n";
      $out .= "</a>\n";
    $out .= "</li>\n";
  }
  while ($curpage++ < $totalpages) {
    $navoffset = (($curpage - 1) * $perpage);
    if (($curpage <= $tngpage - $pagenavpages || $curpage >= $tngpage + $pagenavpages) && $pagenavpages) {
      if ($curpage == 1) {
        $firstPage = "<li class='page-item'><a class='page-link' href='$address=$navoffset&amp;tngpage=$curpage' title='" . uiTextSnippet('firstpage') . "'>1</a></li>\n";
        $out .= $firstPage;
        $out .= "<li class='page-item disabled'><a class='page-link' href='#'>...</a></li>\n";
      }
      if ($curpage == $totalpages) {
        $out .= "<li class='page-item disabled'><a class='page-link' href='#'>...</a></li>\n";
        $lastPage = "<li class='page-item'><a class='page-link' href='$address=$navoffset&amp;tngpage=$curpage' title='" . uiTextSnippet('lastpage') . "'>$totalpages</a></li>\n";
        $out .= $lastPage;
      }
    } else {
      if ($curpage == $tngpage) {
        $out .= "<li class='page-item active'><a class='page-link' href='#'>$curpage</a></li>\n";
      } else {
        $out .= "<li class='page-item'><a class='page-link' href='$address=$navoffset&amp;tngpage=$curpage'>$curpage</a></li>\n";
      }
    }
  }
  if ($tngpage < $totalpages) {
    $nextpage = $tngpage + 1;
    $navoffset = (($nextpage * $perpage) - $perpage);

    $out .= "<li class='page-item'>\n";
    $out .= "<a class='page-link' href='$address=$navoffset&amp;tngpage=$nextpage' aria-label='Next'>\n";
      $out .= "<span aria-hidden='true'>&raquo;</span>\n";
      $out .= "<span class='sr-only'>Next</span>\n";
      $out .= "</a>\n";
    $out .= "</li>\n";
  }
  $out .= "</ul>\n";
  $out .= "</div>\n";
  $out .= "</nav>\n";

  return $out;
}

function displayDate($date) {
  $newdate = "";
  /* [ts] additional date string from rm (date-range  direction modifier 'begdate-enddate')
          rm gedcom is emmitting an en dash character so what looks like a dash is a 3-byte character.
   * Better to filter this on the gedcom import. Behavior also modified to
          all white-space characters. Need to watch this for a while to see if side-effect */

  // [ts] $dateparts = preg_split("/[\s–]/", $date);
  $dateparts = explode(" ", $date);
  foreach ($dateparts as $datepart) {
    if (!is_numeric($datepart)) {
      $datepartu = strtoupper($datepart);
      if (uiTextSnippet($datepartu) != null) {
        $datepart = uiTextSnippet($datepartu);
      } elseif ($datepartu == "AND") {
        $datepart = uiTextSnippet('and');
      } elseif ($datepartu == "@#DJULIAN@") {
        $datepart = "[J]";
      }
    }
    $newdate .= $newdate ? " $datepart" : $datepart;
  }
  return $newdate;
}

function xmlcharacters($string) {
  global $session_charset;

  $bad = ["&", "\""];
  $good = ["&#038;", "&#034;"];

  $ucharset = strtoupper($session_charset);
  $enc = function_exists('mb_detect_encoding') ? mb_detect_encoding($string) : "";
  if ($enc && strtoupper($enc) == "UTF-8" && $ucharset == "UTF-8") {
    return str_replace($bad, $good, mb_convert_encoding($string, 'UTF-8', $enc));
  } elseif ($ucharset == "ISO-8859-1") {
    $trans = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
    foreach ($trans as $k => $v) {
      $trans[$k] = "&#" . ord($k) . ";";
    }
    $trans[chr(38)] = '&'; // don't translate the & when it is part of &xxx;
    // now translate & into &#38, but only when it is not part of &xxx or &#xxxx;
    return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,5};)/", "&#38;", strtr($string, $trans));
  } else {
    return str_replace($bad, $good, $string);
  }
}

function generatePassword($flag) {
  $password = "";
  $possible = $flag ? "bcdfghjkmnpqrstvwxyz" : "0123456789bcdfghjkmnpqrstvwxyz";
  $length = 8;

  $i = 0;
  while ($i < $length) {
    $char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);
    if (!strstr($password, $char)) {
      $password .= $char;
      $i++;
    }
  }

  return $password;
}

function getXrefNotes($noteref) {
  global $xnotes_table;

  preg_match("/^@(\S+)@/", $noteref, $matches);
  if ($matches[1]) {
    $query = "SELECT note FROM $xnotes_table WHERE noteID = \"$matches[1]\"";
    $xnoteres = tng_query($query);
    if ($xnoteres) {
      $xnote = tng_fetch_assoc($xnoteres);
      $note = trim($xnote['note']);
    }
    tng_free_result($xnoteres);
  } else {
    $note = $noteref;
  }
  return $note;
}

function getDatePrefix($datestr) {
  $prefix = "";
  if ($datestr) {
    $datestr = strtoupper($datestr);
    $prefixes = [uiTextSnippet('BEF'), uiTextSnippet('AFT'), uiTextSnippet('ABT'), uiTextSnippet('CAL'), uiTextSnippet('EST')];
    foreach ($prefixes as $str) {
      if (strpos($datestr, strtoupper($str)) === 0) {
        $prefix = $str . " ";
        break;
      }
    }
  }
  return $prefix;
}

function getDisplayYear($datestr, $trueyear) {
  if ($datestr == "Y") {
    $display = uiTextSnippet('Y');
  } else {
    $newstr = displayDate($datestr); //translated
    $prefix = getDatePrefix($newstr); //first part of translated string
    $rest = trim(substr($newstr, strlen($prefix)));
    $parts = explode(" ", $rest);
    $numParts = count($parts);
    $lastPart = $parts[$numParts - 1];
    if (is_numeric($lastPart)) {
      $display = $prefix . $lastPart;
    } else {
      $display = $trueyear ? $prefix . $trueyear : $newstr;
    }
  }
  //echo "dd=$datestr, ds=$newstr, np=$numParts, r=$rest, lp=" . $parts[$numParts-1];

  return $display;
}

function getYears($row) {
  $years = getGenderIcon($row['sex'], -1);
  if ($row['allow_living'] && $row['allow_private']) {
    $deathdate = $row['deathdate'] ? $row['deathdate'] : $row['burialdate'];
    $displaydeath = getDisplayYear($deathdate, $row['death']);

    $birthdate = $row['birthdate'] ? $row['birthdate'] : $row['altbirthdate'];
    $displaybirth = getDisplayYear($birthdate, $row['birth']);

    if ($displaybirth || $displaydeath) {
      $years .= " $displaybirth - $displaydeath";
      $age = age($row);
      if ($age) {
        $years .= " ($age)";
      }
    }
  }

  return $years;
}

function age($row) {
  // If person is living calculate todays age
  $datum_1_tr = $row['birthdatetr'];
  $datum_1 = $row['birthdate'];
  $datum_alt_1_tr = $row['altbirthdatetr'];
  $datum_alt_1 = $row['altbirthdate'];
  $datum_2_tr = $row['deathdatetr'];
  $datum_2 = $row['deathdate'];
  $datum_alt_2_tr = $row['burialdatetr'];
  $datum_alt_2 = $row['burialdate'];
  $age = "";

  if ($row['living'] == "1" && !$datum_2 && !$datum_alt_2) {
    // Today
    $datum_2_tr = date("Y-m-d", time() + (3600 * $timeOffset));
  }

  // Only if one of the FROM and one of the TO dates are filled
  if (($datum_1_tr != "0000-00-00" || $datum_alt_1_tr != "0000-00-00") && ($datum_2_tr != "0000-00-00" || $datum_alt_2_tr != "0000-00-00")) {

    // FROM date
    // $datum1 = result datum1
    // $datum_1_tr = date numeric, Datum_1 = date alfanumeric
    // $datum_alt_1_tr = alternative date numeric, $datum_alt_1 = alternative date alfanumeric

    if ($datum_1_tr != "0000-00-00") {
      $datum1 = $datum_1_tr;
      if (substr($datum_1, 0, 3) == "BEF") {
        $sign1 = ">";
      } else {
        if (substr($datum_1, 0, 3) == "AFT") {
          $sign1 = "&lt;";
          $datum1 = substr_replace($datum1, "12-31", 5);
        } else {
          if (substr($datum_1, 1, 4) == substr($datum1, 0, 4)) {
            $sign1 = "~";
            $datum1 = substr_replace($datum1, "07-15", 5);
          } else {
            if (substr($datum_1, 0, 2) < 1) {
              $sign1 = "~";
              $datum1 = substr_replace($datum1, "15", 8);
            }
          }
        }
      }
    } else {
      $datum1 = $datum_alt_1_tr;
      $sign1 = "~";
      if (substr($datum_alt_1, 0, 3) == "BEF") {
        $sign1 = ">";
      } else {
        if (substr($datum_alt_1, 0, 3) == "AFT") {
          $sign1 = "&lt;";
          $datum1 = substr_replace($datum1, "12-31", 5);
        } else {
          if (substr($datum_alt_1, 1, 4) == substr($datum1, 0, 4)) {
            $datum1 = substr_replace($datum1, "07-15", 5);
          } else {
            if (substr($datum_alt_1, 0, 2) < 1) {
              $datum1 = substr_replace($datum1, "15", 8);
            }
          }
        }
      }
    }

    // TO date
    // $datum2 = result datum2
    // $datum_2_tr = date numeric, Datum_2 = datum alfanumeric
    // $datum_alt_2_tr = alternative date numeric, $datum_alt_2 = alternative date alfanumeric

    if ($datum_2_tr != "0000-00-00") {
      $datum2 = $datum_2_tr;
      if (substr($datum_2, 0, 3) == "BEF") {
        $sign2 = "&lt;";
      } else {
        if (substr($datum_2, 0, 3) == "AFT") {
          $sign2 = "&gt;";
          $datum2 = substr_replace($datum2, "12-31", 5);
        } else {
          if (substr($datum_2, 1, 4) == substr($datum2, 0, 4)) {
            $datum2 = substr_replace($datum2, "07-15", 5);
          } else {
            if (substr($datum2, 8, 2) < 1) {
              $datum2 = substr_replace($datum2, "15", 8);
            }
          }
        }
      }
    } else {
      $datum2 = $datum_alt_2_tr;
      $sign2 = "~";
      if (substr($datum_alt_2, 0, 3) == "BEF") {
        $sign2 = "&lt;";
      } else {
        if (substr($datum_alt_2, 0, 3) == "AFT") {
          $sign2 = "&gt;";
          $datum2 = substr_replace($datum2, "12-31", 5);
        } else {
          if (substr($datum_alt_2, 1, 4) == substr($datum2, 0, 4)) {
            $datum2 = substr_replace($datum2, "07-15", 5);
          } else {
            if (substr($datum_alt_2, 0, 2) < 1) {
              $datum2 = substr_replace($datum2, "15", 8);
            }
          }
        }
      }
    }

    // age = date2 - date1

    $datum1 = substr($datum1, 0, 4) . substr($datum1, 5, 2) . substr($datum1, 8, 2);
    $datum2 = substr($datum2, 0, 4) . substr($datum2, 5, 2) . substr($datum2, 8, 2);
    $age = $datum2 - $datum1;

    // format age

    if ($age < 0) {
      $age = "";
    } else {
      if ($age >= 0 && $age < 10000) {
        $age = "0 ";
      } else {
        if ($age > 9999 && $age < 100000) {
          $age = substr($age, 0, 1);
        } else {
          if ($age > 99999 && $age < 1000000) {
            $age = substr($age, 0, 2);
          } else {
            if ($age > 999999) {
              $age = substr($age, 0, 3);
            }
          }
        }
      }
    }

    // format sign

    if ((($sign1 == "<") || ($sign1 == ">")) && (($sign2 == "<") || ($sign2 == ">"))) {
      $sign = "~";
    } else {
      if (($sign1 == "~") || ($sign2 == "~")) {
        $sign = "~";
      } else {
        if ($sign1 && $sign1 <> " ") {
          $sign = $sign1;
        }
        if ($sign2 && $sign2 <> " ") {
          $sign = $sign2;
        }
      }
    }

    if ($age && $sign <> "") {
      $age = $sign . " " . $age;
    }
  }

  if ($age <> "") {
    $age .= " " . uiTextSnippet('years');
  }

  return $age;
}

function showSmallPhoto($persfamID, $alttext, $rights, $height, $type = false, $gender = "") {
  global $rootpath;
  global $photopath;
  global $mediapath;
  global $mediatypes_assoc;
  global $photosext;
  global $medialinks_table;
  global $media_table;
  global $tngconfig;

  $photo = "";
  $photocheck = "";

  $query = "SELECT $media_table.mediaID, medialinkID, alwayson, thumbpath, mediatypeID, usecollfolder, newwindow "
          . "FROM ($media_table, $medialinks_table) "
          . "WHERE personID = \"$persfamID\" "
          . "AND $media_table.mediaID = $medialinks_table.mediaID "
          . "AND defphoto = '1'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);

  if ($row['thumbpath']) {
    $targettext = $row['newwindow'] ? " target='_blank'" : "";

    if ($adm || $row['alwayson'] || $rights || checkLivingLinks($row['mediaID'])) {
      $mediatypeID = $row['mediatypeID'];
      $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
      $photocheck = "$usefolder/" . $row['thumbpath'];
      $photoref = "$usefolder/" . str_replace("%2F", "/", rawurlencode($row['thumbpath']));
      if ($type) {
        $prefix = "<a href=\"admin_editmedia.php?mediaID={$row['mediaID']}\"$targettext>";
      } else {
        $prefix = "<a href=\"showmedia.php?mediaID={$row['mediaID']}&amp;medialinkID={$row['medialinkID']}\" title=\"" . str_replace("\"", "&#34;", $alttext) . "\"$targettext>";
      }
      $suffix = "</a>";
    }
  } elseif ($rights) {
    $photoref = $photocheck = "$photopath/$persfamID.$photosext";
    $prefix = $suffix = "";
  }

  $gotfile = $photocheck ? file_exists("$rootpath$photocheck") : false;
  if (!$gotfile) {
    if ($type) {
      $query = "SELECT medialinkID "
              . "FROM ($media_table, $medialinks_table) "
              . "WHERE personID = \"$persfamID\" "
              . "AND $media_table.mediaID = $medialinks_table.mediaID "
              . "AND mediatypeID = \"photos\" "
              . "AND thumbpath != \"\"";
      $result2 = tng_query($query);
      $numphotos = tng_num_rows($result2);
      tng_free_result($result2);
      if ($numphotos) {
        //if photos exist, show box with link to sort page where they can pick a default
        $photo = "<a href=\"mediaSortFormAction.php?newlink1=$persfamID&amp;mediatypeID=photos&amp;linktype1=$type\" class=\"small\" style=\"display:block;padding:8px;border:1px solid black;margin-right:6px;text-align:center\">" . uiTextSnippet('choosedef') . "</a>";
      } elseif ($gender && $tngconfig['usedefthumbs']) {
        if ($gender == 'M') {
          $photocheck = "img/male.jpg";
        } elseif ($gender == 'F') {
          $photocheck = "img/female.jpg";
        }
        $photoref = $photocheck;
        $gotfile = file_exists("$rootpath$photocheck");
      }
    }
  }
  if ($gotfile) {
    $align = $height ? "" : " style=\"float:left;\"";
    $photoinfo = getimagesize("$rootpath$photocheck");
    $photohtouse = $height ? $height : 100;
    if ($photoinfo[1] <= $photohtouse) {
      $photohtouse = $photoinfo[1];
      $photowtouse = $photoinfo[0];
    } else {
      $photowtouse = intval($photohtouse * $photoinfo[0] / $photoinfo[1]);
    }
    $photo = "$prefix<img src=\"$photoref\" alt=\"" . str_replace("\"", "&#34;", $alttext) . "\" width=\"$photowtouse\" height=\"$photohtouse\" class=\"smallimg\"{$align}>$suffix";
  }
  tng_free_result($result);

  return $photo;
}

function buildSilentPlaceLink($place) {
  $findPlacesSnippet = uiTextSnippet('findplaces');
  return "<a class='place' href='placesearch.php?psearch=" . urlencode($place) . "' title='$findPlacesSnippet'>$place</a>";
}

function checkMaintenanceMode($area) {
  global $tngconfig;

  if (strpos($_SERVER['SCRIPT_NAME'], "/mixedSuggest.php") === false && strpos($_SERVER['SCRIPT_NAME'], "admin") === false && $tngconfig['maint'] && (!$_SESSION['allow_admin']) && strpos($_SERVER['SCRIPT_NAME'], "/index.") === false) {
    $maint_url = $area ? "adminmaint.php" : "maint.php";
    header("Location:$maint_url");
    exit;
  }
}

function cleanIt($string) {
  global $session_charset;
  $string = htmlspecialchars(preg_replace("/\n/", " ", $string), ENT_QUOTES, $session_charset);
  $string = preg_replace("/\"/", "&#34;", $string);
  $string = preg_replace("/</", "&lt;", $string);
  $string = preg_replace("/>/", "&gt;", $string);
  $string = preg_replace("/\t/", "&#09;", $string);

  return $string;
}

function truncateIt($string, $length) {
  global $notrunc;

  if ($length > 0 && !$notrunc && strlen($string) > $length) {
    $truncated = substr(strip_tags($string), 0, $length);
    $truncated = substr($truncated, 0, strrpos($truncated, ' ')) . '&hellip;';
  } else {
    $truncated = $string;
  }
  return $truncated;
}

function tng_strtoupper($string) {
  global $session_charset;

  $ucharset = strtoupper($session_charset);
  $enc = function_exists(mb_detect_encoding) ? mb_detect_encoding($string) : "";
  if ($enc && strtoupper($enc) == "UTF-8" && $ucharset == "UTF-8") {
    $string = mb_strtoupper($string, "UTF-8");
  } else {
    $string = strtoupper($string);
  }

  return $string;
}

function tng_strtolower($string) {
  global $session_charset;

  $ucharset = strtoupper($session_charset);
  $enc = function_exists(mb_detect_encoding) ? mb_detect_encoding($string) : "";
  if ($enc && strtoupper($enc) == "UTF-8" && $ucharset == "UTF-8") {
    $string = mb_strtolower($string, "UTF-8");
  } else {
    $string = strtolower($string);
  }

  return $string;
}

function tng_utf8_decode($text) {
  global $session_charset;

  $ucharset = strtoupper($session_charset);
  if ($ucharset == "ISO-8859-1") {
    $text = utf8_decode($text);
  } elseif ($ucharset == "ISO-8859-2") {
    $text = utf82iso88592($text);
  }
  return $text;
}

function utf82iso88592($text) {
  $text = str_replace("\xC4\x85", '±', $text);
  $text = str_replace("\xC4\x84", '¡', $text);
  $text = str_replace("\xC4\x87", 'æ', $text);
  $text = str_replace("\xC4\x86", 'Æ', $text);
  $text = str_replace("\xC4\x99", 'ê', $text);
  $text = str_replace("\xC4\x98", 'Ê', $text);
  $text = str_replace("\xC5\x82", '³', $text);
  $text = str_replace("\xC5\x81", '£', $text);
  $text = str_replace("\xC3\xB3", 'ó', $text);
  $text = str_replace("\xC3\x93", 'Ó', $text);
  $text = str_replace("\xC5\x9B", '¶', $text);
  $text = str_replace("\xC5\x9A", '¦', $text);
  $text = str_replace("\xC5\xBC", '¿', $text);
  $text = str_replace("\xC5\xBB", '¯', $text);
  $text = str_replace("\xC5\xBA", '¼', $text);
  $text = str_replace("\xC5\xB9", '¬', $text);
  $text = str_replace("\xc5\x84", 'ñ', $text);
  $text = str_replace("\xc5\x83", 'Ñ', $text);

  return $text;
}

function getAllTextPath() {
  global $rootpath;
  global $mylanguage;
  global $language;
  global $languagesPath;

  $rootpath = trim($rootpath);
  if ($rootpath && strpos($rootpath, "http") !== 0) {
    $thislanguage = trim($mylanguage ? $mylanguage : $languagesPath . $language);
  }
}

function buildParentRow($parent, $spouse, $label) {
  global $people_table;
  global $families_table;

  $out = "";
  $query = "SELECT personID, lastname, lnprefix, firstname, birthdate, birthplace, altbirthdate, altbirthplace, prefix, suffix, nameorder FROM $people_table, $families_table WHERE $people_table.personID = $families_table.$spouse AND $families_table.familyID = \"{$parent['familyID']}\"";
  $gotparent = tng_query($query);

  if ($gotparent) {
    $prow = tng_fetch_assoc($gotparent);

    $prights = determineLivingPrivateRights($row);
    $prow['allow_living'] = $prights['living'];
    $prow['allow_private'] = $prights['private'];

    $birthinfo = $prow['birthdate'] ? " (" . uiTextSnippet('birthabbr') . " " . displayDate($prow['birthdate']) . ")" : "";

    $out = "<div class='form-group row'>\n";
      $out .= "<label class='col-sm-2 form-control-label' for='parent'>" . uiTextSnippet($label) . "</label>\n";
      $out .= "<div class='col-md-6'>\n";
        if ($prow['personID']) {
          $out .= "<p id='parent'><a href=\"peopleEdit.php?personID={$prow['personID']}&amp;cw=$cw\">" . getName($prow) . " - {$prow['personID']}</a>$birthinfo</p>";
        }
      $out .= "</div>\n";

      $out .= "<label class='col-sm-2 form-control-label' for='relationship'>" . uiTextSnippet('relationship') . "</label>\n";
      $out .= "<div class='col-sm-2'>\n";
        $fieldname = $label == 'father' ? 'frel' : 'mrel';
        $out .= "<select class='form-control form-control-sm' id='relationship' name=\"$fieldname{$parent['familyID']}\">\n";
        $out .= "<option value=''></option>\n";

        $reltypes = ["adopted", "birth", "foster", "sealing", "step"];
        foreach ($reltypes as $reltype) {
          $out .= "<option value=\"$reltype\"";
          if ($parent[$fieldname] == $reltype || $parent[$fieldname] == uiTextSnippet($reltype)) {
            $out .= " selected";
          }
          $out .= ">" . uiTextSnippet($reltype) . "</option>\n";
        }
        $out .= "</select>\n";
      $out .= "</div>\n";
    $out .= "</div>\n";

    tng_free_result($gotparent);
  }
  return $out;
}

function buildSexSelectControl($sex) {
  $out = "<label>" . uiTextSnippet('sex') . "</label>\n";
  $out .= "<select class='form-control' name='sex'>\n";
    $out .= "<option value='U'" . (($sex == 'U') ? " selected>" : ">");
      $out .= uiTextSnippet('unknown');
    $out .= "</option>\n";
    $out .= "<option value='M'" . (($sex == 'M') ? " selected>" : ">");
      $out .= uiTextSnippet('male');
    $out .= "</option>\n";
    $out .= "<option value='F'" . (($sex == 'F') ? " selected>" : ">");
      $out .= uiTextSnippet('female');
    $out .= "</option>\n";
  $out .= "</select>\n";
  return $out;
}
