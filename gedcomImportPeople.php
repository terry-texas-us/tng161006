<?php

$orgprefixes = explode(",", $specpfx);
$prefixcount = 0;
define('CREMATION', 1);
foreach ($orgprefixes as $prefix) {
  $newprefix = preg_replace("/'/", "' ", stripslashes($prefix));
  $newprefix = preg_replace("/  /", " ", $newprefix);
  $newprefixes[$prefixcount] = trim(tng_strtoupper($newprefix));
  $prefixcount++;
}

function getIndividualRecord($personID, $prevlevel) {
  global $people_table;
  global $children_table;
  global $families_table;
  global $citations_table;
  global $assoc_table;
  global $savestate;
  global $lineinfo;
  global $custeventlist;
  global $stdnotes;
  global $notecount;
  global $branchlinks_table;
  global $today;
  global $lnprefixes;
  global $lnpfxnum;
  global $specpfx;
  global $currentuser;
  global $newprefixes;
  global $orgprefixes;
  global $tngimpcfg;
  global $pciteevents;
  global $prefix;

  $personID = adjustID($personID, $savestate['ioffset']);

  $prefix = "I";
  $info = "";
  $prifamily = "";
  $changedate = "";
  $burialtype = 0;
  $info['BIRT'] = $info['DEAT'] = $info['BURI'] = $info['BAPL'] = $info['CONL'] = $info['INIT'] = $info['ENDL'] = $info['NAME'] = $info['SLGC'] = [];
  $spouses = [];
  $events = [];
  $stdnotes = [];
  $mminfo = [];
  $cite = [];
  $notecount = 0;
  $mmcount = 0;
  $custeventctr = 0;
  $spousecount = 0;
  $citecount = 0;
  $parentorder = 1;
  $prevlevel++;
  $assocarr = [];
  $living = $private = 0;
  $familySearchID = '';
  
  static $arrayLower = ['á','à','ä','é','è','ó','ò','ö','ú','ù','ü'];
  static $arrayUpper = ['Á','À','Ä','É','È','Ó','Ò','Ö','Ú','Ù','Ü'];

  $lineinfo = getLine();
  while ($lineinfo['tag'] && $lineinfo['level'] >= $prevlevel) {
    if ($lineinfo['level'] == $prevlevel) {
      $tag = $lineinfo['tag'];
      switch ($tag) {
        case "NAME":
          preg_match("/(.*)\s*\/(.*)\/\s*(.*)/", $lineinfo['rest'], $matches);
          if ($info['GIVN'] || $info['SURN']) {
            $newname = "";
            for ($i = 1; $i <= 3; $i++) {
              if ($matches[$i]) {
                if ($newname) {
                  $newname .= " ";
                }
                $newname .= addslashes(trim($matches[$i]));
              }
            }
            if (!$newname) {
              $newname = addslashes(trim($lineinfo['rest']));
            }
            $custeventctr++;
            $events[$custeventctr] = [];
            $events[$custeventctr]['TAG'] = "NAME";
            $thisevent = $prefix . "_NAME_";
            //make sure it's a keeper before continuing by checking against type_tag_desc list
            if (in_array($thisevent, $custeventlist)) {
              $events[$custeventctr]['INFO'] = getMoreInfo($personID, $lineinfo['level'], $tag, "");
              $events[$custeventctr]['INFO']['FACT'] = $newname;
            } else {
              $lineinfo = getLine();
            }
          } else {
            $info['SURN'] = addslashes(trim($matches[2]));
            if ($savestate['ucaselast']) {
              $info['SURN'] = tng_strtoupper($info['SURN']);
              for ($i = 0; $i < count($arrayLower); $i++) {
                $info['SURN'] = str_replace($arrayLower[$i], $arrayUpper[$i], $info['SURN']);
              }
            }
            if ($matches[1]) {
              $info['GIVN'] = addslashes(trim($matches[1]));
              if ($matches[3]) {
                $info['NSFX'] = trim($matches[3]);
                if (substr($info['NSFX'], 0, 1) == ",") {
                  $info['NSFX'] = substr($info['NSFX'], 1);
                }
                $info['NSFX'] = addslashes(trim($info['NSFX']));
              }
            } elseif ($matches[3]) {
              $info['GIVN'] = addslashes(trim($matches[3]));
            } elseif (!$matches[2]) {
              $info['GIVN'] = addslashes(trim($lineinfo['rest']));
            }

            $info['NAME'] = getMoreInfo($personID, $lineinfo['level'], $tag, "");
            if (isset($info['NAME']['NOTES'])) {
              dumpnotes($info['NAME']['NOTES']);
            }
            if ($info['NAME']['NICK']) {
              if ($info['NICK']) {
                $info['NICK'] .= ", ";
              }
              $info['NICK'] .= removeDelims($info['NAME']['NICK']);
            }
            if ($info['NAME']['NPFX']) {
              $info['NPFX'] = $info['NAME']['NPFX'];
            }
            if ($info['NAME']['NSFX']) {
              $info['NSFX'] = $info['NAME']['NSFX'];
            }
            if ($info['NAME']['TITL']) {
              $info['TITL'] = $info['NAME']['TITL'];
            }
            //this may be just a quickie fix for ALIA
            if ($info['NAME']['ALIA']) {
              $custeventctr++;
              $events[$custeventctr] = [];
              $events[$custeventctr]['TAG'] = "ALIA";
              $thisevent = $prefix . "_ALIA_";
              //make sure it's a keeper before continuing by checking against type_tag_desc list
              if (in_array($thisevent, $custeventlist)) {
                $events[$custeventctr]['INFO'] = [];
                $events[$custeventctr]['INFO']['FACT'] = $info['NAME']['ALIA'];
              }
            }
          }
          break;
        case "SEX":
          $lineinfo['rest'] = strtoupper(trim($lineinfo['rest']));
        case "NPFX":
        case "NSFX":
        case "TITL":
          if (isset($info[$tag])) {
            $custeventctr++;
            $events[$custeventctr] = [];
            $events[$custeventctr]['TAG'] = $tag;
            $thisevent = $prefix . "_" . $tag . "_";
            $events[$custeventctr]['INFO']['FACT'] = addslashes($lineinfo['rest']);
          } else {
            $info[$tag] = addslashes($lineinfo['rest']);
          }
          $lineinfo = getLine();
          break;
        case "NICK":
          if ($info['NICK']) {
            $info['NICK'] .= ", ";
          }
          $info['NICK'] .= addslashes(removeDelims($lineinfo['rest']));
          $lineinfo = getLine();
          break;
        case "CREM":
          if (isset($info['BURI'])) {
            $custeventctr++;
            $events[$custeventctr] = handleCustomEvent($personID, $prefix, $tag);
            break;
          } else {
            $tag = "BURI";
            $burialtype = CREMATION;
          }
        case "CHR":
        case "BIRT":
        case "DEAT":
        case "BURI":
        case "BAPL":
        case "CONL":
        case "INIT":
        case "_INIT":
        case "ENDL":
          if (isset($info[$tag]['more'])) {
            $custeventctr++;
            $events[$custeventctr] = [];
            $events[$custeventctr]['TAG'] = $tag;
            $thisevent = $prefix . "_" . $tag . "_";
            //make sure it's a keeper before continuing by checking against type_tag_desc list
            //if( in_array( $thisevent, $custeventlist ) )
            //do it anyway
            $events[$custeventctr]['INFO'] = getMoreInfo($personID, $lineinfo['level'], $tag, "");
            //else
            //  $lineinfo = getLine();
          } else {
            $info[$tag] = getMoreInfo($personID, $lineinfo['level'], $tag, "");
            if (isset($info[$tag]['NOTES'])) {
              dumpnotes($info[$tag]['NOTES']);
            }
            if ($tag == "BIRT" && $info['BIRT']['TYPE'] == "stillborn") {
              $info['BIRT']['NOTES'][] = ["NOTE" => "stillborn"];
            }
            if (isset($info[$tag]['FACT']) && $info[$tag]['FACT']) {
              if (!isset($info[$tag]['DATE']) && !isset($info[$tag]['PLAC'])) {
                $info[$tag]['DATE'] = $info[$tag]['FACT'];
              } elseif ($info[$tag]['FACT'] != "Y") {
                if (!isset($info[$tag]['NOTES'])) {
                  $info[$tag]['NOTES'] = [];
                  $notectr = 1;
                } else {
                  $notectr = count($info[$tag]['NOTES']);
                }
                $info[$tag]['NOTES'][$notectr] = ["NOTE" => $info[$tag]['FACT'], "TAG" => $tag, "XNOTE" => ""];
                dumpnotes($info[$tag]['NOTES']);
              }
            }
            if ($info[$tag]['extra']) {
              $info[$tag]['parent'] = $tag;
              $custeventctr++;
              $events[$custeventctr] = [];
              $events[$custeventctr]['TAG'] = $tag;
              $thisevent = $prefix . "_" . $tag . "_";
              //make sure it's a keeper before continuing by checking against type_tag_desc list
              if (in_array($thisevent, $custeventlist)) {
                $events[$custeventctr]['INFO'] = $info[$tag];
                $events[$custeventctr]['INFO']['NOTES'] = "";
                $events[$custeventctr]['INFO']['SOUR'] = "";
                $events[$custeventctr]['INFO']['MEDIA'] = "";
              }
            }
            $info[$tag]['more'] = 1;
          }
          break;
        case "CHAN":
          $lineinfo = getLine();
          $changedate = addslashes($lineinfo['rest']);
          if ($changedate) {
            $lineinfo = getLine();
            if ($lineinfo['tag'] == "TIME") {
              $changedate .= " " . str_replace("\.", ":", $lineinfo['rest']);
              $lineinfo = getLine();
            }
            $changedate = date("Y-m-d H:i:s", strtotime($changedate));
          }
          break;
        case "FAMC":
          preg_match("/^@(\S+)@/", $lineinfo['rest'], $matches);
          $famc = adjustID($matches[1], $savestate['foffset']);
          if (!$prifamily) {
            $prifamily = $famc;
          }
          $lineinfo = getLine();
          $relationship = $lineinfo['tag'] == "PEDI" ? $lineinfo['rest'] : "";
          $query = "INSERT IGNORE INTO $children_table (familyID, personID, mrel, frel, parentorder) "
              . "VALUES('$famc', '$personID', '$relationship', '$relationship', '$parentorder')";
          $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
          $success = tng_affected_rows();
          if ($success) {
            $parentorder++;
          }
          if ($relationship) {
            if (!$success) {
              $query = "UPDATE $children_table SET mrel = \"$relationship\", frel = \"$relationship\" WHERE familyID = \"$famc\" AND personID = '$personID'";
              $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
            }
            $lineinfo = getLine();
          }
          if ($savestate['del'] != "no") {
            if (!$info['SLGC']['DATETR']) {
              $info['SLGC']['DATETR'] = "0000-00-00";
            }
            $query = "INSERT IGNORE INTO $children_table (familyID, personID, sealdate, sealdatetr, sealplace ) "
                . "VALUES(\"" . $famc . "\", '$personID', \"" . $info['SLGC']['DATE'] . "\", \"" . $info['SLGC']['DATETR'] . "\", '$slgcplace')";
            $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
            $success = tng_affected_rows();
            if (!$success && ($info['SLGC']['DATE'] || $slgplace || $info['SLGC']['SOUR'])) {
              $query = "UPDATE $children_table SET sealdate=\"" . $info['SLGC']['DATE'] . "\", sealdatetr=\"" . $info['SLGC']['DATETR'] . "\", sealplace=\"$slgcplace\" WHERE personID = \"$personID\" AND familyID = \"" . $famc . "\"";
              $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
            }
            if (isset($info['SLGC']['SOUR'])) {
              $query = "DELETE from $citations_table WHERE persfamID = \"$personID" . "::" . $info['SLGC']['FAMC'] . "\"";
              $result = tng_query($query);
              processCitations($personID . "::" . $famc, "SLGC", $info['SLGC']['SOUR']);
            }
          }
          break;
        case "ASSO":
          preg_match("/^@(\S+)@/", $lineinfo['rest'], $matches);
          $thisassoc = [];
          if (substr($matches[1], 0, 1) == 'I' || substr($matches[1], -1) == 'I') {
            $countertouse = $savestate['ioffset'];
            $thisassoc['reltype'] = 'I';
          } else {
            $countertouse = $savestate['foffset'];
            $thisassoc['reltype'] = 'F';
          }
          $thisassoc['asso'] = adjustID($matches[1], $countertouse);
          do {
            $lineinfo = getLine();
            if ($lineinfo['tag'] == "RELA") {
              $thisassoc['rela'] = $lineinfo['rest'];
            }
          } while ($lineinfo['level'] > $prevlevel);
          array_push($assocarr, $thisassoc);
          break;
        case "SLGC":
          $info['SLGC'] = getMoreInfo($personID, $lineinfo['level'], "SLGC", "");
          if (isset($info['SLGC']['NOTES'])) {
            dumpnotes($info['SLGC']['NOTES']);
          }

          if ($savestate['del'] != "no") {
            $slgcplace = trim($info['SLGC']['TEMP'] . " " . $info['SLGC']['PLAC']);
            if ($info['SLGC']['FAMC']) {
              if (!$info['SLGC']['DATETR']) {
                $info['SLGC']['DATETR'] = "0000-00-00";
              }
              $query = "INSERT IGNORE INTO $children_table (familyID, personID, sealdate, sealdatetr, sealplace) "
                  . "VALUES(\"" . $info['SLGC']['FAMC'] . "\", '$personID', \"" . $info['SLGC']['DATE'] . "\", \"" . $info['SLGC']['DATETR'] . "\", '$slgcplace')";
              $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
              $success = tng_affected_rows();
            } else {
              $success = 0;
            }
            if (!$success && ($info['SLGC']['DATE'] || $slgplace || $info['SLGC']['SOUR'])) {
              $query = "UPDATE $children_table SET sealdate=\"" . $info['SLGC']['DATE'] . "\", sealdatetr=\"" . $info['SLGC']['DATETR'] . "\", sealplace=\"$slgcplace\" WHERE personID = \"$personID\" AND familyID = \"" . $info['SLGC']['FAMC'] . "\"";
              $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
            }
            if (isset($info['SLGC']['SOUR'])) {
              $query = "DELETE from $citations_table WHERE persfamID = \"$personID" . "::" . $info['SLGC']['FAMC'] . "\"";
              $result = tng_query($query);
              processCitations($personID . "::" . $info['SLGC']['FAMC'], "SLGC", $info['SLGC']['SOUR']);
            }
          }
          break;
        case "FAMS":
          preg_match("/^@(\S+)@/", $lineinfo['rest'], $matches);
          $spousecount++;
          $spouses[$spousecount] = adjustID($matches[1], $savestate['foffset']);
          $lineinfo = getLine();
          break;
        case "IMAGE":
        case "OBJE":
          if ($savestate['media']) {
            preg_match("/^@(\S+)@/", $lineinfo['rest'], $matches);
            $mmcount++;
            $mminfo[$mmcount] = getMoreMMInfo($lineinfo['level'], $mmcount);
            $mminfo[$mmcount]['OBJE'] = $matches[1] ? $matches[1] : $mminfo[$mmcount]['FILE'];
            $mminfo[$mmcount]['linktype'] = 'I';
          } else {
            $lineinfo = getLine();
          }
          break;
        case "NOTE":
          $notecount++;
          $stdnotes[$notecount]['TAG'] = "";
          preg_match("/^@(\S+)@/", $lineinfo['rest'], $matches);
          if ($matches[1]) {
            $stdnotes[$notecount]['XNOTE'] = adjustID($matches[1], $savestate['noffset']);
            $stdnotes[$notecount]['NOTE'] = "";
            $lineinfo = getLine();
          } else {
            $stdnotes[$notecount]['XNOTE'] = "";
            $stdnotes[$notecount]['NOTE'] .= addslashes($lineinfo['rest']);
            $stdnotes[$notecount]['NOTE'] .= getContinued();
          }
          $ncitecount = 0;
          while ($lineinfo['level'] >= $prevlevel + 1 && $lineinfo['tag'] == "SOUR") {
            $ncitecount++;
            $stdnotes[$notecount]['SOUR'][$ncitecount] = handleSource($personID, $prevlevel + 1);
          }
          break;
        case "SOUR":
          $citecount++;
          $cite[$citecount] = handleSource($personID, $prevlevel, $prevtag, $prevtype);
          break;
        case "_LIVING":
        case "_ALIV":
        case "_FLAG":
          $living = ($lineinfo['rest'] == "Y" || $lineinfo['rest'] == "J" || $lineinfo['rest'] == "LIVING") ? 1 : 0;
          $lineinfo = getLine();
          break;
        case "_PRIVATE":
        case "_PRIV":
          $private = 1;
          $lineinfo = getLine();
          break;
        case "_FLGS":
          $lineinfo = getLine();
          if ($lineinfo['tag'] == "__LIVING") {
            $living = $lineinfo['rest'] == "Living" ? "1" : 0;
            $lineinfo = getLine();
          } elseif ($lineinfo['tag'] == "__PRIVATE") {
            $private = $lineinfo['rest'] == "Private" ? "1" : 0;
            $lineinfo = getLine();
          }
          break;
        case "_UID":
          $lineinfo = getLine();
          break;
        case "_FSFTID": // RM Family Search ID
          $familySearchID = strtoupper(trim($lineinfo['rest']));
          $lineinfo = getLine();
          break;
        default:
          //custom event -- should be 1 TAG
          $custeventctr++;
          $events[$custeventctr] = handleCustomEvent($personID, $prefix, $tag);
          break;
      }
    } else {
      $lineinfo = getLine();
    }
  }
  //do TEMP + PLAC
  $meta = metaphone($info['SURN']);
  $baplplace = trim($info['BAPL']['TEMP'] . " " . $info['BAPL']['PLAC']);
  $confplace = trim($info['CONL']['TEMP'] . " " . $info['CONL']['PLAC']);
  $initplace = trim($info['INIT']['TEMP'] . " " . $info['INIT']['PLAC']);
  $endlplace = trim($info['ENDL']['TEMP'] . " " . $info['ENDL']['PLAC']);
  $slgcplace = trim($info['SLGC']['TEMP'] . " " . $info['SLGC']['PLAC']);
  if ($info['TITL'] && $info['TITL'] == $info['NSFX']) {
    $info['TITL'] = "";
  }

  //determine if living
  if (!$living && !$info['DEAT']['more'] && !$info['BURI']['more'] && $info['BIRT']['TYPE'] != "stillborn") {
    if ($info['BIRT']['DATE'] || $info['CHR']['DATE']) {
      $birthyear = $info['BIRT']['DATE'] ? $info['BIRT']['DATETR'] : $info['CHR']['DATETR'];
      $birthyear = strtok($birthyear, "-");
      if (date("Y") - $birthyear < $tngimpcfg['maxlivingage']) {
        $living = 1;
      }
    } elseif ($tngimpcfg['livingreqbirth']) {
      $living = 1;
    }
  }
  if (!$savestate['norecalc']) {
    $livingstrupd = ", living=\"$living\"";
  } else {
    $livingstrupd = "";
  }

  //determine if private (if we have a buried date but no death date, we'll assume it's been long enough)
  if ($info['DEAT']['DATE'] && $tngimpcfg['maxprivyrs'] && !$private) {
    $deathyear = strtok($info['DEAT']['DATETR'], "-");
    if (strtotime("-{$tngimpcfg['maxprivyrs']} years") < strtotime($info['DEAT']['DATETR'])) {
      $private = 1;
    }
  }

  //process surname prefix if necessary
  //if( $info['NAME']['SPFX'] && $lnprefixes) {
  //$info['lnprefix'] = $info['NAME']['SPFX'];
  //$gotit = 1;
  //}
  //else {
  $gotit = 0;
  if ($info['SURN'] && $lnprefixes) {
    $lastname = preg_replace("/'/", "' ", stripslashes($info['SURN']));
    $lastname = preg_replace("/  /", " ", $lastname);
    if ($specpfx) {
      $fullprefix = tng_strtoupper($lastname);
      $lastspace = strrpos($fullprefix, " ");
      $fullsurname = "";
      while (!$gotit && $lastspace) {
        $fullsurname = substr($lastname, $lastspace + 1);
        $fullprefix = substr($fullprefix, 0, $lastspace);
        if (in_array($fullprefix, $newprefixes)) {
          $gotit = 1;
          $count = 0;
          foreach ($newprefixes as $newprefix) {
            if ($fullprefix == $newprefix) {
              $fullprefix = $orgprefixes[$count];
              break;
            } else {
              $count++;
            }
          }
        } else {
          $lastspace = strrpos($fullprefix, " ");
        }
      }
    }
    if (!$gotit && $lnpfxnum) {
      $pfxcount = 0;
      $parts = explode(" ", $lastname);
      $numparts = count($parts);
      if ($numparts >= 2) {
        $fullprefix = $fullsurname = "";
        foreach ($parts as $part) {
          if (!$gotit) {
            $fullprefix .= $fullprefix ? " $part" : $part;
            $pfxcount++;
            if ($numparts == $pfxcount + 1 || $lnpfxnum == $pfxcount) {
              $gotit = 1;
            }
          } else {
            $fullsurname .= $fullsurname ? " $part" : $part;
          }
        }
      }
    }
  }
  //}
  if ($gotit) {
    $info['lnprefix'] = addslashes($fullprefix);
    $info['SURN'] = addslashes(trim($fullsurname));
  } else {
    $info['lnprefix'] = "";
  }

  $inschangedt = $changedate ? $changedate : ($tngimpcfg['chdate'] == "1" ? "0000-00-00 00:00:00" : $today);
  if (!$info['BIRT']['DATETR']) {
    $info['BIRT']['DATETR'] = "0000-00-00";
  }
  if (!$info['CHR']['DATETR']) {
    $info['CHR']['DATETR'] = "0000-00-00";
  }
  if (!$info['DEAT']['DATETR']) {
    $info['DEAT']['DATETR'] = "0000-00-00";
  }
  if (!$info['BURI']['DATETR']) {
    $info['BURI']['DATETR'] = "0000-00-00";
  }
  if (!$info['BAPL']['DATETR']) {
    $info['BAPL']['DATETR'] = "0000-00-00";
  }
  if (!$info['CONL']['DATETR']) {
    $info['CONL']['DATETR'] = "0000-00-00";
  }
  if (!$info['INIT']['DATETR']) {
    $info['INIT']['DATETR'] = "0000-00-00";
  }
  if (!$info['ENDL']['DATETR']) {
    $info['ENDL']['DATETR'] = "0000-00-00";
  }
  $query = "INSERT IGNORE INTO $people_table (personID, lastname, lnprefix, firstname, living, private, sex, birthdate, birthdatetr, birthplace, altbirthdate, altbirthdatetr, altbirthplace, deathdate, deathdatetr, deathplace, burialdate, burialdatetr, burialplace, burialtype, nickname, title, prefix, suffix, baptdate, baptdatetr, baptplace, confdate, confdatetr, confplace, initdate, initdatetr, initplace, endldate, endldatetr, endlplace, changedate, famc, metaphone, branch, changedby, edituser, edittime) VALUES('$personID', \"{$info['SURN']}\", \"{$info['lnprefix']}\", \"{$info['GIVN']}\", '$living', '$private', \"{$info['SEX']}\", \"" . $info['BIRT']['DATE'] . "\", \"" . $info['BIRT']['DATETR'] . "\", \"" . $info['BIRT']['PLAC'] . "\", \"" . $info['CHR']['DATE'] . "\", \"" . $info['CHR']['DATETR'] . "\", \"" . $info['CHR']['PLAC'] . "\", \"" . $info['DEAT']['DATE'] . "\", \"" . $info['DEAT']['DATETR'] . "\", \"" . $info['DEAT']['PLAC'] . "\", \"" . $info['BURI']['DATE'] . "\", \"" . $info['BURI']['DATETR'] . "\", \"" . $info['BURI']['PLAC'] . "\", $burialtype, \"{$info['NICK']}\", \"{$info['TITL']}\", \"{$info['NPFX']}\", \"{$info['NSFX']}\", \"" . $info['BAPL']['DATE'] . "\", \"" . $info['BAPL']['DATETR'] . "\", '$baplplace', \"" . $info['CONL']['DATE'] . "\", \"" . $info['CONL']['DATETR'] . "\", '$confplace',\"" . $info['INIT']['DATE'] . "\", \"" . $info['INIT']['DATETR'] . "\", \"$initplace\", \"" . $info['ENDL']['DATE'] . "\", \"" . $info['ENDL']['DATETR'] . "\", '$endlplace', '$inschangedt', '$prifamily', '$meta', \"{$savestate['branch']}\", '$currentuser', '', '0' )";
  $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
  $success = tng_affected_rows();
  if (!$success && $savestate['del'] != "no") {
    if ($inschangedt == "0000-00-00 00:00:00") {
      $inschangedt = "";
    }
    if ($savestate['neweronly'] && $inschangedt) {
      $query = "SELECT changedate FROM $people_table WHERE personID = '$personID'";
      $result = tng_query($query);
      $indrow = tng_fetch_assoc($result);
      $goahead = $inschangedt > $indrow['changedate'] ? 1 : 0;
      if ($result) {
        tng_free_result($result);
      }
    } else {
      $goahead = 1;
    }
    if ($goahead) {
      $chdatestr = $inschangedt ? ", changedate=\"$inschangedt\"" : "";
      $branchstr = $savestate['branch'] ? ", branch=\"{$savestate['branch']}\"" : "";
      $query = "UPDATE $people_table SET firstname=\"{$info['GIVN']}\", lnprefix=\"{$info['lnprefix']}\", lastname=\"{$info['SURN']}\"" . $livingstrupd . ", nickname=\"{$info['NICK']}\", prefix=\"{$info['NPFX']}\", suffix=\"{$info['NSFX']}\", title=\"{$info['TITL']}\", birthdate=\"" . $info['BIRT']['DATE'] . "\", birthdatetr=\"" . $info['BIRT']['DATETR'] . "\", birthplace=\"" . $info['BIRT']['PLAC'] . "\", sex=\"{$info['SEX']}\", altbirthdate=\"" . $info['CHR']['DATE'] . "\", altbirthdatetr=\"" . $info['CHR']['DATETR'] . "\", altbirthplace=\"" . $info['CHR']['PLAC'] . "\", deathdate=\"" . $info['DEAT']['DATE'] . "\", deathdatetr=\"" . $info['DEAT']['DATETR'] . "\", deathplace=\"" . $info['DEAT']['PLAC'] . "\", burialdate=\"" . $info['BURI']['DATE'] . "\", burialdatetr=\"" . $info['BURI']['DATETR'] . "\", burialplace=\"" . $info['BURI']['PLAC'] . "\", baptdate=\"" . $info['BAPL']['DATE'] . "\", baptdatetr=\"" . $info['BAPL']['DATETR'] . "\", baptplace=\"$baplplace\", confdate=\"" . $info['CONL']['DATE'] . "\", confdatetr=\"" . $info['CONL']['DATETR'] . "\", confplace=\"$confplace\", initdate=\"" . $info['INIT']['DATE'] . "\", initdatetr=\"" . $info['INIT']['DATETR'] . "\", initplace=\"$initplace\", endldate=\"" . $info['ENDL']['DATE'] . "\", endldatetr=\"" . $info['ENDL']['DATETR'] . "\", endlplace=\"$endlplace\", changedby=\"$currentuser\" $chdatestr$branchstr";
      if ($prifamily) {
        $query .= ", famc=\"$prifamily\"";
      }
      $query .= ", metaphone=\"$meta\" WHERE personID = '$personID'";
      $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
      $success = 1;

      if ($savestate['del'] == "match") {
        //delete all custom events & notelinks for this person because we didn't before
        deleteLinksOnMatch($personID);
      }
    }
  }
  if ($success) {
    $rmID = tng_insert_id();
    if ($rmID != 0) {
      $query = "INSERT INTO extlinks (rmID, extID) VALUES ($rmID, '$familySearchID')";
      tng_query($query);
    }
    if ($savestate['branch']) {
      $query = "INSERT IGNORE INTO $branchlinks_table (branch, persfamID) VALUES(\"{$savestate['branch']}\", '$personID')";
      $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
    }
    if ($custeventctr) {
      saveCustEvents($prefix, $personID, $events, $custeventctr);
    }
    if ($notecount) {
      for ($notectr = 1; $notectr <= $notecount; $notectr++) {
        saveNote($personID, $stdnotes[$notectr]['TAG'], $stdnotes[$notectr]);
      }
    }

    //do associations
    if (count($assocarr)) {
      foreach ($assocarr as $assoc) {
        $query = "INSERT INTO $assoc_table (personID, passocID, relationship, reltype) "
            . "VALUES('$personID', \"{$assoc['asso']}\", \"{$assoc['rela']}\", \"{$assoc['reltype']}\" )";
        $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
      }
    }

    //do citations
    if (isset($cite)) {
      processCitations($personID, "", $cite);
    }
    foreach ($pciteevents as $citeevent) {
      if (isset($info[$citeevent]['SOUR'])) {
        processCitations($personID, $citeevent, $info[$citeevent]['SOUR']);
      }
    }

    if ($spousecount) {
      for ($spousectr = 1; $spousectr <= $spousecount; $spousectr++) {
        $familyID = $spouses[$spousectr];
        if (!$living || $savestate['norecalc']) {
          $famlivingstr = "";
        } else {
          $famlivingstr = "living = \"1\"";
        }
        if ($info['SEX'] == 'M') {
          $uspousestr = "husband = \"$personID\", husborder = \"$spousectr\"";
          $query = "INSERT IGNORE INTO $families_table (familyID, husborder, living, private, changedby) "
              . "VALUES('$familyID', '$spousectr', '$living', '$private', '$currentuser')";
        } elseif ($info['SEX'] == 'F') {
          $uspousestr = "wife = \"$personID\", wifeorder = \"$spousectr\"";
          $query = "INSERT IGNORE INTO $families_table (familyID, wifeorder, living, private, changedby) "
              . "VALUES('$familyID', '$spousectr', '$living', '$private', '$currentuser')";
        } else {
          $uspousestr = "";
          $query = "INSERT IGNORE INTO $families_table (familyID, changedby) VALUES('$familyID', '$currentuser')";
        }
        $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
        $success = tng_affected_rows();
        if (!$success && ($uspousestr || $famlivingstr) && $savestate['del'] != "no") {
          if ($uspousestr && $famlivingstr) {
            $famlivingstr .= ",";
          }
          $query = "UPDATE $families_table SET $famlivingstr $uspousestr, changedby=\"$currentuser\" WHERE familyID = '$familyID'";
          $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
        }
      }
    }
    if ($mmcount) {
      processMedia($mmcount, $mminfo, $personID, "");
    }

    //do event-based media
    foreach ($pciteevents as $stdevtype) {
      if (is_array($info[$stdevtype]['MEDIA'])) {
        $eminfo = $info[$stdevtype]['MEDIA'];
        $emcount = count($eminfo);
        processMedia($emcount, $eminfo, $personID, $stdevtype);
      }
    }

    incrCounter($prefix);
  }
}