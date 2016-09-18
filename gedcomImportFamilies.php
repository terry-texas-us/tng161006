<?php

function getFamilyRecord($familyID, $prevlevel) {
  global $fciteevents;
  global $prefix;
  global $savestate;
  global $lineinfo;
  global $custeventlist;
  global $stdnotes;
  global $notecount;
  global $today;
  global $currentuser;
  global $tngimpcfg;

  $familyID = adjustID($familyID, $savestate['foffset']);

  $prefix = 'F';
  $info = '';
  $changedate = '';
  $info['MARR'] = [];
  $info['SLGS'] = [];
  $events = [];
  $stdnotes = [];
  $notecount = 0;
  $childorder = 1;
  $custeventctr = 0;
  $cite = [];
  $mminfo = [];
  $mmcount = 0;
  $prevlevel++;
  $assocarr = [];
  $citecount = 0;
  $living = $private = 0;

  $lineinfo = getLine();
  while ($lineinfo['tag'] && $lineinfo['level'] >= $prevlevel) {
    if ($lineinfo['level'] == $prevlevel) {
      $tag = $lineinfo['tag'];
      switch ($tag) {
        case 'HUSB':
        case 'WIFE':
          preg_match('/^@(\S+)@/', $lineinfo['rest'], $matches);
          $info[$tag] = adjustID($matches[1], $savestate['ioffset']);
          $lineinfo = getLine();
          break;
        case 'MARR':
        case 'DIV':
        case 'SLGS':
          if (isset($info[$tag]['more'])) {
            $custeventctr++;
            $events[$custeventctr] = [];
            $events[$custeventctr]['TAG'] = $tag;
            $thisevent = $prefix . '_' . $tag . '_';
            //make sure it's a keeper before continuing by checking against type_tag_desc list
            if (in_array($thisevent, $custeventlist)) {
              $events[$custeventctr]['INFO'] = getMoreInfo($familyID, $lineinfo['level'], $tag, '');
            } else {
              $lineinfo = getLine();
            }
          } else {
            $info[$tag] = getMoreInfo($familyID, $lineinfo['level'], $tag, '');
            if (isset($info[$tag]['NOTES'])) {
              dumpnotes($info[$tag]['NOTES']);
            }
            if ($info[$tag]['FACT'] && !isset($info[$tag]['DATE']) && !isset($info[$tag]['PLAC'])) {
              $info[$tag]['DATE'] = $info[$tag]['FACT'];
            }
            if ($info[$tag]['extra']) {
              $info[$tag]['parent'] = $tag;
              $custeventctr++;
              $events[$custeventctr] = [];
              $events[$custeventctr]['TAG'] = $tag;
              $thisevent = $prefix . '_' . $tag . '_';
              //make sure it's a keeper before continuing by checking against type_tag_desc list
              if (in_array($thisevent, $custeventlist)) {
                $events[$custeventctr]['INFO'] = $info[$tag];
                $events[$custeventctr]['INFO']['NOTES'] = '';
                $events[$custeventctr]['INFO']['SOUR'] = '';
              }
            }
            $info[$tag]['more'] = 1;
          }
          break;
          break;
        case '_LIVING':
        case '_ALIV':
        case '_FLAG':
          $living = ($lineinfo['rest'] == 'Y' || $lineinfo['rest'] == 'J' || $lineinfo['rest'] == 'LIVING') ? 1 : 0;
          $lineinfo = getLine();
          break;
        case '_PRIVATE':
        case '_PRIV':
          $private = 1;
          $lineinfo = getLine();
          break;
        case 'CHAN':
          $lineinfo = getLine();
          $changedate = addslashes($lineinfo['rest']);
          if ($changedate) {
            $lineinfo = getLine();
            if ($lineinfo['tag'] == 'TIME') {
              $changedate .= ' ' . str_replace("\.", ':', $lineinfo['rest']);
              $lineinfo = getLine();
            }
            $changedate = date('Y-m-d H:i:s', strtotime($changedate));
          }
          break;
        case 'CHIL':
          preg_match('/^@(\S+)@/', $lineinfo['rest'], $matches);
          $child = adjustID($matches[1], $savestate['ioffset']);
          $frelstr = $mrelstr = '';
          $startlevel = $lineinfo['level'];
          do {
            $lineinfo = getLine();
            if ($lineinfo['tag'] == '_FREL') {
              $frelstr = ", frel=\"{$lineinfo['rest']}\"";
            } elseif ($lineinfo['tag'] == '_MREL') {
              $mrelstr = ", mrel=\"{$lineinfo['rest']}\"";
            }
          } while ($lineinfo['level'] > $startlevel);

          $query = "UPDATE children SET ordernum=$childorder$frelstr$mrelstr WHERE personID = \"$child\" AND familyID = '$familyID'";
          $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
          $childorder++;
          break;
        case 'ASSO':
          preg_match('/^@(\S+)@/', $lineinfo['rest'], $matches);
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
            if ($lineinfo['tag'] == 'RELA') {
              $thisassoc['rela'] = $lineinfo['rest'];
            }
          } while ($lineinfo['level'] > $prevlevel);
          array_push($assocarr, $thisassoc);
          break;
        case 'NOTE':
          $notecount++;
          $stdnotes[$notecount]['TAG'] = '';
          preg_match('/^@(\S+)@/', $lineinfo['rest'], $matches);
          if ($matches[1]) {
            $stdnotes[$notecount]['XNOTE'] = adjustID($matches[1], $savestate['noffset']);
            $stdnotes[$notecount]['NOTE'] = '';
            $lineinfo = getLine();
          } else {
            $stdnotes[$notecount]['XNOTE'] = '';
            $stdnotes[$notecount]['NOTE'] .= addslashes($lineinfo['rest']);
            $stdnotes[$notecount]['NOTE'] .= getContinued();
          }
          $ncitecount = 0;
          while ($lineinfo['level'] >= $prevlevel && $lineinfo['tag'] == 'SOUR') {
            $ncitecount++;
            $stdnotes[$notecount]['SOUR'][$ncitecount] = handleSource($familyID, $prevlevel + 1);
          }
          break;
        case 'OBJE':
          if ($savestate['media']) {
            preg_match('/^@(\S+)@/', $lineinfo['rest'], $matches);
            $mmcount++;
            $mminfo[$mmcount] = getMoreMMInfo($lineinfo['level'], $mmcount);
            $mminfo[$mmcount]['OBJE'] = $matches[1] ? $matches[1] : $mminfo[$mmcount]['FILE'];
            $mminfo[$mmcount]['linktype'] = 'F';
          } else {
            $lineinfo = getLine();
          }
          break;
        case 'SOUR':
          $citecount++;
          $cite[$citecount] = handleSource($familyID, $prevlevel, $prevtag, $prevtype);
          break;
        default:
          //custom event -- should be 1 TAG
          $custeventctr++;
          $events[$custeventctr] = handleCustomEvent($familyID, $prefix, $tag);
          break;
      }
    } else {
      $lineinfo = getLine();
    }
  }
  //do TEMP + PLAC
  $slgsplace = trim($info['SLGS']['TEMP'] . ' ' . $info['SLGS']['PLAC']);

  $inschangedt = $changedate ? $changedate : ($tngimpcfg['chdate'] == '1' ? '0000-00-00 00:00:00' : $today);
  if (!$info['MARR']['DATETR']) {
    $info['MARR']['DATETR'] = '0000-00-00';
  }
  if (!$info['DIV']['DATETR']) {
    $info['DIV']['DATETR'] = '0000-00-00';
  }
  if (!$info['SLGS']['DATETR']) {
    $info['SLGS']['DATETR'] = '0000-00-00';
  }
  $query = "INSERT IGNORE INTO families (familyID, marrdate, marrdatetr, marrplace, marrtype, divdate, divdatetr, divplace, husband, wife, sealdate, sealdatetr, sealplace, changedate, branch, living, private, changedby ) VALUES('$familyID', \"" . $info['MARR']['DATE'] . '", "' . $info['MARR']['DATETR'] . '", "' . $info['MARR']['PLAC'] . '", "' . $info['MARR']['TYPE'] . '", "' . $info['DIV']['DATE'] . '", "' . $info['DIV']['DATETR'] . '", "' . $info['DIV']['PLAC'] . '", "' . $info['HUSB'] . '", "' . $info['WIFE'] . '", "' . $info['SLGS']['DATE'] . '", "' . $info['SLGS']['DATETR'] . "\", '$slgsplace', '$inschangedt', \"{$savestate['branch']}\", '$living', '$private', '$currentuser')";
  $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
  $success = tng_affected_rows();
  if (!$success && $savestate['del'] != 'no') {
    if ($savestate['neweronly'] && $inschangedt) {
      $query = "SELECT changedate FROM families WHERE familyID = '$familyID'";
      $result = tng_query($query);
      $famrow = tng_fetch_assoc($result);
      $goahead = $inschangedt > $famrow['changedate'] ? 1 : 0;
      if ($result) {
        tng_free_result($result);
      }
    } else {
      $goahead = 1;
    }
    if ($goahead) {
      $chdatestr = $inschangedt ? ", changedate=\"$inschangedt\"" : '';
      $branchstr = $savestate['branch'] ? ", branch=\"{$savestate['branch']}\"" : '';
      $query = "UPDATE families SET marrdate=\"" . $info['MARR']['DATE'] . '", marrdatetr="' . $info['MARR']['DATETR'] . '", marrplace="' . $info['MARR']['PLAC'] . '", marrtype="' . $info['MARR']['TYPE'] . '", divdate="' . $info['DIV']['DATE'] . '", divdatetr="' . $info['DIV']['DATETR'] . '", divplace="' . $info['DIV']['PLAC'] . '", husband="' . $info['HUSB'] . '", wife="' . $info['WIFE'] . '", sealdate="' . $info['SLGS']['DATE'] . '", sealdatetr="' . $info['SLGS']['DATETR'] . "\", sealplace = '$slgsplace', changedby = '$currentuser' $chdatestr$branchstr WHERE familyID = '$familyID'";
      $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
      $success = 1;

      if ($savestate['del'] == 'match') {
        //delete all custom events for this family because we didn't before
        deleteLinksOnMatch($familyID);
      }
    }
  }
  if ($success) {
    if ($savestate['branch']) {
      $query = "INSERT IGNORE INTO branchlinks (branch, persfamID) VALUES(\"{$savestate['branch']}\", '$familyID')";
      $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
    }
    if ($custeventctr) {
      saveCustEvents($prefix, $familyID, $events, $custeventctr);
    }
    if (isset($cite)) {
      processCitations($familyID, '', $cite);
    }
    foreach ($fciteevents as $citeevent) {
      if (isset($info[$citeevent]['SOUR'])) {
        processCitations($familyID, $citeevent, $info[$citeevent]['SOUR']);
      }
    }
    if ($notecount) {
      for ($notectr = 1; $notectr <= $notecount; $notectr++) {
        saveNote($familyID, $stdnotes[$notectr]['TAG'], $stdnotes[$notectr]);
      }
    }
    //do associations
    if (count($assocarr)) {
      foreach ($assocarr as $assoc) {
        $query = "INSERT INTO associations (personID, passocID, relationship, reltype) VALUES('$familyID', \"{$assoc['asso']}\", \"{$assoc['rela']}\", \"{$assoc['reltype']}\" )";
        $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
      }
    }

    if ($mmcount) {
      processMedia($mmcount, $mminfo, $familyID, '');
    }

    //do event-based media
    foreach ($fciteevents as $stdevtype) {
      if (is_array($info[$stdevtype]['MEDIA'])) {
        $eminfo = $info[$stdevtype]['MEDIA'];
        $emcount = count($eminfo);
        processMedia($emcount, $eminfo, $familyID, $stdevtype);
      }
    }

    incrCounter($prefix);
  }
}