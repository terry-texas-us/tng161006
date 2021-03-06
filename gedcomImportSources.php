<?php

function handleSource($persfamID, $prevlevel) {
  global $lineinfo;
  global $savestate;

  $cite = [];
  preg_match('/^@(\S+)@/', $lineinfo['rest'], $matches);
  if ($matches[1]) {
    $cite['sourceID'] = adjustID($matches[1], $savestate['soffset']);
    $cite['desc'] = '';
    $lineinfo = getLine();
  } else {
    $cite['sourceID'] = '';
    $cite['desc'] = addslashes($lineinfo['rest']);
    $cite['desc'] .= getContinued();
  }
  $prevlevel++;

  while ($lineinfo['level'] >= $prevlevel) {
    $tag = $lineinfo['tag'];
    switch ($tag) {
      case 'DATE':
        $cite['DATE'] = addslashes($lineinfo['rest']);
        $cite['DATETR'] = convertDate($cite['DATE']);
        $lineinfo = getLine();
        break;
      case 'PAGE':
      case 'QUAY':
        $cite[$tag] = addslashes($lineinfo['rest']);
        $cite[$tag] .= getContinued();
        break;
      case 'TEXT':
      case 'NOTE':
        $notecount++;
        preg_match('/^@(\S+)@/', $lineinfo['rest'], $matches);
        if ($matches[1]) {
          $cite[$tag] = '@' . adjustID($matches[1], $savestate['noffset']) . '@';
          $lineinfo = getLine();
        } else {
          $cite[$tag] = addslashes($lineinfo['rest']);
          $cite[$tag] .= getContinued();
        }
        break;
      default:
        $lineinfo = getLine();
        break;
    }
  }
  return $cite;
}

function getSourceRecord($sourceID, $prevlevel) {
  global $savestate;
  global $lineinfo;
  global $stdnotes;
  global $notecount;
  global $currentuser;
  global $tngimpcfg;
  global $today;
  global $prefix;

  $sourceID = adjustID($sourceID, $savestate['soffset']);

  $prefix = 'S';
  $info = '';
  $changedate = '';
  $events = [];
  $stdnotes = [];
  $notecount = 0;
  $custeventctr = 0;
  $mminfo = [];
  $mmcount = 0;
  $prevlevel++;

  $lineinfo = getLine();
  while ($lineinfo['tag'] && $lineinfo['level'] >= $prevlevel) {
    if ($lineinfo['level'] == $prevlevel) {
      $tag = $lineinfo['tag'];
      switch ($tag) {
        case 'ABBR':
        case 'AUTH':
        case 'CALN':
        case 'PUBL':
        case 'TITL':
          $info[$tag] = addslashes($lineinfo['rest']);
          $info[$tag] .= getContinued();
          break;
        case 'CHAN':
          $lineinfo = getLine();
          $changedate = addslashes($lineinfo['rest']);
          if ($changedate) {
            $lineinfo = getLine();
            if ($lineinfo['tag'] == 'TIME') {
              $changedate .= ' ' . $lineinfo['rest'];
              $lineinfo = getLine();
            }
            $changedate = date('Y-m-d H:i:s', strtotime($changedate));
          }
          break;
        case 'DATA':
          $lineinfo = getLine(); //text should start on next line;
        case 'TEXT':
          $info['TEXT'] = addslashes($lineinfo['rest']);
          $info['TEXT'] .= getContinued();
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
            $stdnotes[$notecount]['SOUR'][$ncitecount] = handleSource($sourceID, $prevlevel + 1);
          }
          break;
        case 'OBJE':
          if ($savestate['media']) {
            preg_match('/^@(\S+)@/', $lineinfo['rest'], $matches);
            $mmcount++;
            $mminfo[$mmcount] = getMoreMMInfo($lineinfo['level'], $mmcount);
            $mminfo[$mmcount]['OBJE'] = $matches[1] ? $matches[1] : $mminfo[$mmcount]['FILE'];
            $mminfo[$mmcount]['linktype'] = 'S';
          } else {
            $lineinfo = getLine();
          }
          break;
        case 'REPO':
          preg_match('/^@(\S+)@/', $lineinfo['rest'], $matches);
          if ($matches[1]) {
            $info['REPO'] = $matches[1];
          }
          $lineinfo = getLine();
          if ($lineinfo['tag'] == 'CALN') {
            $info['CALN'] = addslashes($lineinfo['rest']);
            $info['CALN'] .= getContinued();
          }
          break;
        case '_SUBQ': // RM subsequent quote
        case '_BIBL': // RM bibliography
          $info[$tag] = addslashes($lineinfo['rest']);
          $info[$tag] .= getContinued();
          break;
        case '_TMPLT': // RM template 
          $lineinfo = getLine();
          while ($lineinfo['level'] > $prevlevel) {
            if ($lineinfo['level'] == $prevlevel + 2) {
              $name = strtoupper($lineinfo['rest']);
              $lineinfo = getLine();
              $value = addslashes($lineinfo['rest']);
              switch ($name) {
                case 'AUTHOR':
                  $info['AUTH'] = $value;
                  $info['AUTH'] .= getContinued();
                  break;
                case 'ROLE':
                  $role = $value;
                  break;
                case 'TITLE':
                  $info['TITL'] = $value;
                  $info['TITL'] .= getContinued();
                  break;
                case 'SUBTITLE':
                  $subtitle = $value;
                  $subtitle .= getContinued();
                  break;
                case 'PUBPLACE':
                  $publisherPlace = $value;
                  $publisherPlace .= getContinued();
                  break;
                case 'PUBLISHER':
                  $info['PUBL'] = $value;
                  $info['PUBL'] .= getContinued();
                  break;
                case 'PUBDATE':
                  $publisherDate = $value;
                  $publisherDate .= getContinued();
                  break;
                default;
                  break;
              }
            } else {
              $lineinfo = getLine();
            }
          }
          if ($info['AUTH'] && $role) {
            $info['AUTH'] .= ', ' . $role;
          }
          if ($info['TITL']) { // RM generated ABBR and TITL replaced by _TMPLT values
            $info['ABBR'] = $info['TITL'];
            if ($subtitle) {
              $info['TITL'] .= ': ' . $subtitle;
            }
          }
          if ($info['PUBL']) {
            if ($publisherPlace) {
              $info['PUBL'] = $publisherPlace . ': ' . $info['PUBL'];
            }
            if ($publisherDate) {
              $info['PUBL'] .= ', ' . $publisherDate;
            }
          }
          break;

        default: //custom event -- should be 1 TAG
          $custeventctr++;
          $events[$custeventctr] = handleCustomEvent($sourceID, $prefix, $tag);
          break;
      }
    } else {
      $lineinfo = getLine();
    }
  }
  $inschangedt = $changedate ? $changedate : ($tngimpcfg['chdate'] ? '' : $today);
  $query = "INSERT IGNORE INTO sources (sourceID, callnum, title, author, publisher, shorttitle, repoID, actualtext, changedate, changedby, type, other, comments) VALUES('$sourceID', '{$info['CALN']}', '{$info['TITL']}', '{$info['AUTH']}', '{$info['PUBL']}', '{$info['ABBR']}', '{$info['REPO']}', \"" . trim($info['TEXT']) . "\", '$changedate', '$currentuser', '', '', '')";
  $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
  $success = tng_affected_rows();
  if (!$success && $savestate['del'] != 'no') {
    if ($savestate['neweronly'] && $inschangedt) {
      $query = "SELECT changedate FROM sources WHERE sourceID = '$sourceID'";
      $result = tng_query($query);
      $srcrow = tng_fetch_assoc($result);
      $goahead = $inschangedt > $srcrow['changedate'] ? 1 : 0;
      if ($result) {
        tng_free_result($result);
      }
    } else {
      $goahead = 1;
    }
    if ($goahead) {
      $chdatestr = $inschangedt ? ", changedate='$inschangedt'" : '';
      $query = "UPDATE sources SET callnum = '{$info['CALN']}', title='{$info['TITL']}', author='{$info['AUTH']}', publisher='{$info['PUBL']}', shorttitle='{$info['ABBR']}', repoID='{$info['REPO']}', actualtext=\"" . trim($info['TEXT']) . "\", changedby='$currentuser' $chdatestr WHERE sourceID = '$sourceID'";
      $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
      $success = 1;

      if ($savestate['del'] == 'match') {
        //delete all custom events & notelinks for this source because we didn't before
        deleteLinksOnMatch($sourceID);
      }
    }
  }
  if ($success) {
    if ($custeventctr) {
      saveCustEvents($prefix, $sourceID, $events, $custeventctr);
    }
    if ($notecount) {
      for ($notectr = 1; $notectr <= $notecount; $notectr++) {
        saveNote($sourceID, $stdnotes[$notectr]['TAG'], $stdnotes[$notectr]);
      }
    }
    if ($mmcount) {
      processMedia($mmcount, $mminfo, $sourceID, '');
    }

    incrCounter($prefix);
  }
}

function getRestOfSource($sourceID, $prevlevel) {
  global $lineinfo;
  global $lineending;

  $continued = '';
  $lasttag = '';

  $lineinfo = getLine();
  while ($lineinfo['level'] > $prevlevel) {
    if ($lineinfo['rest']) {
      if ($lineinfo['tag'] == 'CONC') {
        $continued .= addslashes($lineinfo['rest']);
      } elseif ($lineinfo['tag'] == 'CONT') {
        if ($continued) {
          $lineinfo['rest'] = "\n" . $lineinfo['rest'];
        }
        $continued .= addslashes($lineinfo['rest']);
      } else {
        if ($lineinfo['tag'] != $lasttag) {
          if ($continued) {
            $continued .= $lineending;
          }
          $continued .= $lineinfo['tag'] . ':';
          $lasttag = $lineinfo['tag'];
        }
        if ($continued) {
          $lineinfo['rest'] = "\n" . $lineinfo['rest'];
        }
        $continued .= addslashes($lineinfo['rest']);
      }
    }
    $lineinfo = getLine();
  }
  return $continued;
}

function getRepoRecord($repoID, $prevlevel) {
  global $savestate;
  global $lineinfo;
  global $stdnotes;
  global $notecount;
  global $currentuser;
  global $tngimpcfg;
  global $today;
  global $prefix;

  $repoID = adjustID($repoID, $savestate['roffset']);

  $prefix = 'R';
  $info = '';
  $changedate = '';
  $events = [];
  $stdnotes = [];
  $notecount = 0;
  $custeventctr = 0;
  $mminfo = [];
  $mmcount = 0;
  $prevlevel++;

  $lineinfo = getLine();
  while ($lineinfo['tag'] && $lineinfo['level'] >= $prevlevel) {
    if ($lineinfo['level'] == $prevlevel) {
      $tag = $lineinfo['tag'];
      switch ($tag) {
        case 'NAME':
          $info['NAME'] = addslashes($lineinfo['rest']) . getContinued();
          break;
        case 'ADDR':
          $address = handleAddress($lineinfo['level'], 1);
          $info['extra'] = 1;
          break;
        case 'CHAN':
          $lineinfo = getLine();
          $changedate = addslashes($lineinfo['rest']);
          if ($changedate) {
            $lineinfo = getLine();
            if ($lineinfo['tag'] == 'TIME') {
              $changedate .= ' ' . $lineinfo['rest'];
              $lineinfo = getLine();
            }
            $changedate = date('Y-m-d H:i:s', strtotime($changedate));
          }
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
            $stdnotes[$notecount]['SOUR'][$ncitecount] = handleSource($repoID, $prevlevel + 1);
          }
          break;
        case 'OBJE':
          if ($savestate['media']) {
            preg_match('/^@(\S+)@/', $lineinfo['rest'], $matches);
            $mmcount++;
            $mminfo[$mmcount] = getMoreMMInfo($lineinfo['level'], $mmcount);
            $mminfo[$mmcount]['OBJE'] = $matches[1] ? $matches[1] : $mminfo[$mmcount]['FILE'];
            $mminfo[$mmcount]['linktype'] = 'R';
          } else {
            $lineinfo = getLine();
          }
          break;
        default:
          //custom event -- should be 1 TAG
          $custeventctr++;
          $events[$custeventctr] = handleCustomEvent($repoID, $prefix, $tag);
          break;
      }
    } else {
      $lineinfo = getLine();
    }
  }
  $inschangedt = $changedate ? $changedate : ($tngimpcfg['chdate'] ? '' : $today);
  $query = "INSERT IGNORE INTO repositories (repoID, reponame, changedate, changedby)  VALUES('$repoID', '{$info['NAME']}', '$inschangedt', '$currentuser')";
  $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
  $success = tng_affected_rows();
  if (!$success && $savestate['del'] != 'no') {
    if ($savestate['neweronly'] && $inschangedt) {
      $query = "SELECT changedate FROM repositories WHERE repoID = '$repoID'";
      $result = tng_query($query);
      $reporow = tng_fetch_assoc($result);
      $goahead = $inschangedt > $reporow['changedate'] ? 1 : 0;
      if ($result) {
        tng_free_result($result);
      }
    } else {
      $goahead = 1;
    }
    if ($goahead) {
      $chdatestr = $inschangedt ? ", changedate='$inschangedt'" : '';
      if (!isset($info['ADDR'])) {
        $info['ADDR'] = 0;
      }
      $query = "UPDATE repositories SET reponame='{$info['NAME']}', addressID='{$info['ADDR']}', changedby='$currentuser' $chdatestr WHERE repoID = '$repoID'";
      $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
      $success = 1;

      if ($savestate['del'] == 'match') {
        //delete all custom events & notelinks for this source because we didn't before
        deleteLinksOnMatch($repoID);
      }
    }
  }
  if ($success) {
    if ($custeventctr) {
      saveCustEvents($prefix, $repoID, $events, $custeventctr);
    }
    if ($notecount) {
      for ($notectr = 1; $notectr <= $notecount; $notectr++) {
        saveNote($repoID, $stdnotes[$notectr]['TAG'], $stdnotes[$notectr]);
      }
    }
    if ($mmcount) {
      processMedia($mmcount, $mminfo, $repoID, '');
    }
    if (is_array($address)) {
      $query = "INSERT INTO addresses (address1, address2, city, state, zip, country, www, email, phone) VALUES('{$address['ADR1']}', '{$address['ADR2']}', '{$address['CITY']}', '{$address['STAE']}', '{$address['POST']}',  '{$address['CTRY']}', '{$address['WWW']}', '{$address['EMAIL']}', '{$address['PHON']}')";
      $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
      $info['ADDR'] = tng_insert_id();
      $query = "UPDATE repositories SET addressID='{$info['ADDR']}' WHERE repoID = '$repoID'";
      $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
    }
  }
}
