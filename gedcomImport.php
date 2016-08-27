<?php

function getLine() {
  global $fp;
  global $lineending;
  global $saveimport;
  global $savestate;

  $lineinfo = [];
  if ($line = ltrim(fgets($fp, 1024))) {
    if ($saveimport) {
      $savestate['len'] = strlen($line);
    }
    $line = ltrim($line, "\xEF\xBB\xBF"); // [ts] characters are only encountered at file head (at least on RM gedcom)
    $patterns = ["/®®.*¯¯/", "/®®.*/", "/.*¯¯/", "/@@/"]; 
    $replacements = ["", "", "", "@"];
    $line = preg_replace($patterns, $replacements, $line);

    preg_match("/^(\d+)\s+(\S+) ?(.*)$/", $line, $matches);

    $lineinfo['level'] = trim($matches[1]);
    $lineinfo['tag'] = trim($matches[2]);
    $lineinfo['rest'] = trim($matches[3], $lineending);
  } else {
    $lineinfo['level'] = "";
    $lineinfo['tag'] = "";
    $lineinfo['rest'] = "";
  }
  if (!$lineinfo['tag'] && !feof($fp)) {
    $lineinfo = getLine();
  }

  return $lineinfo;
}

function adjustID($ID, $offset) {
  if ($offset) {
    //find first numeric in ID
    preg_match("/^(\D*)(\d*)(\D*)/", $ID, $matches);
    $prefix = $matches[1];
    $numericpart = $matches[2];
    $postfix = $matches[3];
    //add offset, make right length + add prefix
    $thistrim = strlen($numericpart);
    $newID = $prefix . str_pad($numericpart + $offset, $thistrim, "0", STR_PAD_LEFT) . $postfix;
  } else {
    $newID = $ID;
  }

  return $newID;
}

function getMoreInfo($persfamID, $prevlevel, $prevtag, $prevtype) {
  global $lineinfo;
  global $savestate;
  global $address_table;
  global $prefix;

  $moreinfo = [];

  if ($prevtag == "ALIA" || $prevtag == "AKA" || $prevtag == "NAME") {
    $moreinfo['FACT'] = addslashes(removeDelims($lineinfo['rest']));
  } else {
    $moreinfo['FACT'] = addslashes($lineinfo['rest']);
  }
  if ($lineinfo['tag'] == "ADDR") {
    $address = handleAddress($lineinfo['level'], 0);
    $moreinfo['extra'] = 1;
  } elseif ($prevtag == "EVEN") {
    $lineinfo['level']++;
  } else {
    $moreinfo['FACT'] .= getContinued();
  }

  $moreinfo['TYPE'] = "";
  $moreinfo['parent'] = "";
  $prevlevel++;
  $citecnt = 0;
  $notecnt = 0;
  $mminfo = [];
  $mmcount = 0;

  while ($lineinfo['level'] >= $prevlevel) {
    if ($lineinfo['level'] == $prevlevel) {
      $tag = $lineinfo['tag'];
      switch ($tag) {
        case "STAT":
        case "DATE":
          $moreinfo['DATE'] = addslashes($lineinfo['rest']);
          $moreinfo['DATETR'] = convertDate($moreinfo['DATE']);
          $lineinfo = getLine();
          break;
        case "_AKA":
        case "_ALIA":
          $tag = "ALIA";
        case "ALIA":
        case "NPFX":
        case "TYPE":
        case "NSFX":
        case "NICK":
        case "TITL":
        case "SPFX":
          $moreinfo[$tag] = addslashes($lineinfo['rest']);
          $lineinfo = getLine();
          break;
        case "AGE":
        case "AGNC":
        case "CAUS":
          $moreinfo[$tag] = addslashes($lineinfo['rest']);
          $moreinfo['extra'] = 1;
          $lineinfo = getLine();
          break;
        case "ADDR":
          $address = handleAddress($lineinfo['level'], 1);
          $moreinfo['extra'] = 1;
          break;
        case "ADR1":
        case "ADR2":
        case "CITY":
        case "STAE":
        case "POST":
        case "CTRY":
        case "WWW":
        case "PHON":
        case "EMAIL":
          $address[$tag] = addslashes($lineinfo['rest']) . getContinued();
          break;
        case "PLAC":
        case "TEMP":
          $moreinfo['PLAC'] = addslashes($lineinfo['rest']);
          getPlaceRecord($lineinfo['rest'], $lineinfo['level']);
          //savePlace( $moreinfo['PLAC'] );
          //$lineinfo = getLine();
          break;
        case "FAMC":
          preg_match("/^@(\S+)@/", $lineinfo['rest'], $matches);
          $moreinfo[$tag] = adjustID($matches[1], $savestate['foffset']);
          $lineinfo = getLine();
          break;
        //case "TEXT":
        case "NOTE":
          //$notecount++;
          if (!$notecnt) {
            $moreinfo['NOTES'] = [];
          }
          $notecnt++;

          $moreinfo['NOTES'][$notecnt] = [];
          preg_match("/^@(\S+)@/", $lineinfo['rest'], $matches);
          if ($matches[1]) {
            $moreinfo['NOTES'][$notecnt]['XNOTE'] = adjustID($matches[1], $savestate['noffset']);
            $lineinfo = getLine();
          } else {
            $moreinfo['NOTES'][$notecnt]['NOTE'] = addslashes($lineinfo['rest']);
            $moreinfo['NOTES'][$notecnt]['XNOTE'] = "";
            $moreinfo['NOTES'][$notecnt]['NOTE'] .= getContinued();
          }
          $moreinfo['NOTES'][$notecnt]['TAG'] = $prevtag;
          $ncitecount = 0;
          while ($lineinfo['level'] >= $prevlevel + 1 && $lineinfo['tag'] == "SOUR") {
            $ncitecount++;
            $moreinfo['NOTES'][$notecnt]['SOUR'][$ncitecount] = handleSource($persfamID, $prevlevel + 1);
          }
          break;
        case "SOUR":
          if (!$citecnt) {
            $moreinfo['SOUR'] = [];
          }
          $citecnt++;
          $moreinfo['SOUR'][$citecnt] = handleSource($persfamID, $prevlevel);
          break;
        case "IMAGE":
        case "OBJE":
          if ($savestate['media']) {
            preg_match("/^@(\S+)@/", $lineinfo['rest'], $matches);
            $mmcount++;
            $mminfo[$mmcount] = getMoreMMInfo($lineinfo['level'], $mmcount);
            $mminfo[$mmcount]['OBJE'] = $matches[1] ? $matches[1] : $mminfo[$mmcount]['FILE'];
            $mminfo[$mmcount]['linktype'] = $prefix;
          } else {
            $lineinfo = getLine();
          }
          break;
        default:
          $lineinfo = getLine();
          break;
      }
    } else {
      $lineinfo = getLine();
    }
  }

  if ($mmcount) {
    $moreinfo['MEDIA'] = $mminfo;
  }
  if (is_array($address)) {
    $query = "INSERT INTO $address_table (address1, address2, city, state, zip, country, www, email, phone) "
        . "VALUES('{$address['ADR1']}', '{$address['ADR2']}', '{$address['CITY']}', '{$address['STAE']}', '{$address['POST']}', '{$address['CTRY']}', '{$address['WWW']}', '{$address['EMAIL']}', '{$address['PHON']}')";
    $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
    $moreinfo['ADDR'] = tng_insert_id();
    if ($moreinfo['FACT'] == $address['ADR1']) {
      $moreinfo['FACT'] = "";
    }
  }
  return $moreinfo;
}

function handleCustomEvent($id, $prefix, $tag) {
  global $lineinfo, $custeventlist, $allevents;

  $event = [];
  $event['TAG'] = $tag;
  $needmore = 1;
  $savelevel = $lineinfo['level'];
  if ($tag == "EVEN") {
    $fact = addslashes($lineinfo['rest'] . getContinued());
    $needfact = 1;
    //next one must be TYPE
    //$lineinfo = getLine();
    if ($lineinfo['tag'] == "TYPE") {
      $event['TYPE'] = trim(addslashes($lineinfo['rest']));
    } else {
      if ($fact) {
        $event['TYPE'] = $fact;
      } else {
        do {
          $lineinfo = getLine();
        } while ($lineinfo['tag'] != "TYPE" && $lineinfo['level'] > $savelevel);
        if ($lineinfo['tag'] == "TYPE") {
          $event['TYPE'] = trim(addslashes($lineinfo['rest']));
        } else {
          $event['TYPE'] = "";
        }
      }
    }
    if ($event['TYPE']) {
      $lineinfo = getLine();
    }
    if ($lineinfo['level'] <= $savelevel) {
      $needmore = 0;
    } else {
      $lineinfo['level']--;
    }
  } else {
    $fact = "";
    $needfact = 0;
  }
  $thisevent = strtoupper($prefix . "_" . $tag . "_" . $event['TYPE']);
  //make sure it's a keeper before continuing by checking against type_tag_desc list
  if ($allevents || in_array($thisevent, $custeventlist)) {
    if ($needmore) {
      $event['INFO'] = getMoreInfo($id, $lineinfo['level'], $tag, $event['TYPE']);
    }
    if ($needfact) {
      $event['INFO']['FACT'] = $fact;
    }
  } elseif ($needmore) {
    $lineinfo = getLine();
  }

  return $event;
}

function handleAddress($prevlevel, $flag) {
  global $lineinfo;

  $address = [];
  $address['ADR1'] = addslashes($lineinfo['rest']);
  $gotaddr = $address['ADR1'] ? 1 : 0;
  $prevlevel++;

  $notdone = 1;
  $addr[0] = "ADR1";
  $addr[1] = "CITY";
  $addr[2] = "STAE";
  $addr[3] = "POST";
  $addr[4] = "CTRY";
  $counter = 0;

  while ($notdone) {
    $lineinfo = getLine();
    if ($lineinfo['tag'] == "CONC") {
      $addrtag = $addr[$counter];
      $address[$addrtag] .= addslashes($lineinfo['rest']);
    } elseif ($lineinfo['tag'] == "CONT") {
      if ($counter < 4) {
        $counter++;
      }
      $addrtag = $addr[$counter];
      $address[$addrtag] .= addslashes($lineinfo['rest']);
    } else {
      $notdone = 0;
    }
  }
  if ($flag) {
    while ($lineinfo['level'] >= $prevlevel) {
      if ($lineinfo['level'] == $prevlevel) {
        $tag = $lineinfo['tag'];
        switch ($tag) {
          case "ADR1":
          case "ADR2":
          case "CITY":
          case "STAE":
          case "POST":
          case "CTRY":
          case "WWW":
          case "PHON":
          case "EMAIL":
            $address[$tag] = addslashes($lineinfo['rest']) . getContinued();
            if ($address[$tag]) {
              $gotaddr = 1;
            }
            break;
          default:
            $lineinfo = getLine();
            break;
        }
      } else {
        $lineinfo = getLine();
      }
    }
  }
  return $gotaddr ? $address : null;
}

function getContinued() {
  global $lineinfo;

  $continued = "";
  $notdone = 1;

  while ($notdone) {
    $lineinfo = getLine();
    if ($lineinfo['tag'] == "CONC") {
      $continued .= addslashes($lineinfo['rest']);
    } elseif ($lineinfo['tag'] == "CONT") {
      //if( $continued ) $lineinfo['rest'] = "\n$lineinfo['rest']";
      $continued .= addslashes("\n" . $lineinfo['rest']);
    } else {
      $notdone = 0;
    }
  }
  return $continued;
}

function deleteLinksOnMatch($entityID) {
  global $events_table;
  global $notelinks_table;
  global $citations_table;
  global $xnotes_table;
  global $address_table;
  global $assoc_table;

  $query = "SELECT addressID FROM $events_table WHERE persfamID = '$entityID'";
  $result = tng_query($query);
  while ($row = tng_fetch_assoc($result)) {
    $query = "DELETE from $address_table WHERE addressID = '{$row['addressID']}'";
    tng_query($query);
  }
  tng_free_result($result);

  $query = "DELETE from $events_table WHERE persfamID = '$entityID'";
  tng_query($query);

  $query = "DELETE from $assoc_table WHERE personID = '$entityID'";
  tng_query($query);

  $query = "SELECT xnoteID FROM $notelinks_table WHERE persfamID = '$entityID'";
  $result = tng_query($query);
  while ($row = tng_fetch_assoc($result)) {
    $query = "DELETE from $xnotes_table WHERE ID = '{$row['xnoteID']}'";
    tng_query($query);
  }
  tng_free_result($result);
  $query = "DELETE from $notelinks_table WHERE persfamID = '$entityID'";
  tng_query($query);
  $query = "DELETE from $citations_table WHERE persfamID = '$entityID'";
  tng_query($query);
}

function getHeadRecord() {
  $lineinfo = getLine();

  while ($lineinfo['tag'] && $lineinfo['level'] > 0) {
    $lineinfo = getLine();
  }
  return $lineinfo;
}

function getEventDefinitionRecord($event) {
  $lineinfo = getLine();

  while ($lineinfo['tag'] && $lineinfo['level'] > 0) {
    $lineinfo = getLine();
  }
  return $lineinfo;
}

function getPlaceRecord($place, $prevlevel) {
  global $savestate;
  global $lineinfo;
  global $places_table;

  $place = addslashes($place);
  $note = "";
  $map = [];
  $map['long'] = "";
  $map['lati'] = "";
  $map['zoom'] = $map['placelevel'] = "0";
  $mminfo = [];
  $mmcount = 0;
  $prevlevel++;

  $lineinfo = getLine();
  while ($lineinfo['tag'] && $lineinfo['level'] >= $prevlevel) {
    if ($lineinfo['level'] == $prevlevel) {
      $tag = $lineinfo['tag'];
      switch ($tag) {
        case "PLAC":
          $place = addslashes($lineinfo['rest']);
          $info = getPlaceRecord("", $lineinfo['level']);
          if ($info['NOTE']) {
            $note .= $info['NOTE'];
          }
          if ($info['MAP']) {
            $map = $info['MAP'];
          }
          if ($info['media']) {
            $mminfo = array_merge($mminfo, $info['media']);
            $mmcount = count($mminfo);
          }
          break;
        case "NOTE":
          $note .= addslashes($lineinfo['rest']);
          $note .= getContinued();
          break;
        case "MAP":
        case "_MAP":
          $map = getMapCoords($lineinfo['level']);
          break;
        case "OBJE":
          if ($savestate['media']) {
            preg_match("/^@(\S+)@/", $lineinfo['rest'], $matches);
            $mmcount++;
            $mminfo[$mmcount] = getMoreMMInfo($lineinfo['level'], $mmcount);
            $mminfo[$mmcount]['OBJE'] = $matches[1] ? $matches[1] : $mminfo[$mmcount]['FILE'];
            $mminfo[$mmcount]['linktype'] = 'L';
          } else {
            $lineinfo = getLine();
          }
          break;
        default:
          $lineinfo = getLine();
          break;
      }
    } else {
      $lineinfo = getLine();
    }
  }

  if ($place) {
    $temple = isTemple($place);
    $query = "INSERT IGNORE INTO $places_table (place, longitude, latitude, zoom, placelevel, temple, notes, geoignore) "
        . "VALUES('$place', '{$map['long']}', '{$map['lati']}', '{$map['zoom']}', '{$map['placelevel']}', '$temple', '$note', '0')";
    $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");

    $success = tng_affected_rows();
    if (!$success && $savestate['del'] != "no" && (($savestate['latlong'] && ($map['long'] || $map['lati'])) || $note)) {
      $query = "UPDATE $places_table SET temple='$temple'";
      $query1 = "";
      if ($savestate['latlong']) {
        if ($map['long'] || $map['lati']) {
          $query1 .= ", longitude='{$map['long']}', latitude='{$map['lati']}'";
        }
        if ($map['zoom']) {
          $query1 .= ", zoom='{$map['zoom']}'";
        }
        if ($map['placelevel']) {
          $query1 .= ", placelevel='{$map['placelevel']}'";
        }
      }
      if ($note) {
        $query1 .= ", notes='$note'";
      }
      $query = $query . $query1 . " WHERE place = '$place'";
      $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
      $success = 1;
    }
    if ($success) {
      incrCounter("P");
    }
    if ($mmcount) {
      processMedia($mmcount, $mminfo, $place, "");
    }
  } else {
    $info = [];
    $info['MAP'] = $map;
    $info['NOTE'] = $note;
    $info['media'] = $mminfo;
    return $info;
  }
}

function isTemple($place) {
  global $ldsOK;

  $isTemple = ($ldsOK && strlen($place) == 5 && $place == preg_replace("/[^A-Z]/", "", $place)) ? 1 : 0;

  return $isTemple;
}

function getMapCoords($prevlevel) {
  global $lineinfo;

  $map = [];
  $map['long'] = "";
  $map['lati'] = "";
  $map['zoom'] = $map['placelevel'] = "0";
  $prevlevel++;

  $lineinfo = getLine();
  while ($lineinfo['tag'] && $lineinfo['level'] >= $prevlevel) {
    if ($lineinfo['level'] == $prevlevel) {
      $tag = $lineinfo['tag'];
      switch ($tag) {
        case "LATI":
        case "_LATI":
          $map['lati'] = getLatLong($lineinfo['rest'], 'S');
          $lineinfo = getLine();
          break;
        case "LONG":
        case "_LONG":
          $map['long'] = getLatLong($lineinfo['rest'], "W");
          $lineinfo = getLine();
          break;
        case "ZOOM":
          $map['zoom'] = $lineinfo['rest'];
          $lineinfo = getLine();
          break;
        case "PLEV":
          $map['placelevel'] = $lineinfo['rest'];
          $lineinfo = getLine();
          break;
        default:
          $lineinfo = getLine();
          break;
      }
    } else {
      $lineinfo = getLine();
    }
  }

  return $map;
}

function getLatLong($value, $negdir) {
  $value = strtoupper($value);
  $neednegative = strpos($value, $negdir) !== false ? true : false;
  $value = trim(preg_replace('/[^0-9. \-]+/', '', $value));

  $degs = explode(" ", $value);
  if (count($degs) == 3) {
    $value = intval($degs[0]) + (intval($degs[1]) / 60) + (intval($degs[2]) / 3600);
  } else {
    if (substr($value, 0, 1) == ".") {
      $value = "0" . $value;
    }
  }
  if ($neednegative) {
    $value = "-" . $value;
  }

  return $value;
}

function incrCounter($prefix) {
  global $savestate;
  global $saveimport;
  global $saveimport_table; 
  global $fp;
  global $allcount;
  global $fstat;
  global $old;

  $allcount++;
  switch ($prefix) {
    case 'F':
      $savestate['fcount']++;
      $counter = $savestate['fcount'];
      break;
    case 'I':
      $savestate['icount']++;
      $counter = $savestate['icount'];
      break;
    case 'S':
      $savestate['scount']++;
      $counter = $savestate['scount'];
      break;
    case 'M':
      $savestate['mcount']++;
      $counter = $savestate['mcount'];
      break;
    case 'N':
      $savestate['ncount']++;
      $counter = $savestate['ncount'];
      break;
    case "P":
      $savestate['pcount']++;
      $counter = $savestate['pcount'];
      break;
  }
  $offset = ftell($fp) - $savestate['len'];
  $newwidth = $fstat['size'] ? ceil(500 * $offset / $fstat['size']) : 0;
  if ($old) {
    if ($counter % 10 == 0) {
      echo "<strong>$prefix$counter</strong> ";
      ob_flush();
      flush();
    }
  } else {
    if ($allcount % 100 == 0) {
      $newtext = "<div class=\"impc\"><span id=\"pr\">$newwidth</span>";
      if ($savestate['icount']) {
        $newtext .= "<span id=\"ic\">" . $savestate['icount'] . "</span>";
      }
      if ($savestate['fcount']) {
        $newtext .= "<span id=\"fc\">" . $savestate['fcount'] . "</span>";
      }
      if ($savestate['scount']) {
        $newtext .= "<span id=\"sc\">" . $savestate['scount'] . "</span>";
      }
      if ($savestate['ncount']) {
        $newtext .= "<span id=\"nc\">" . $savestate['ncount'] . "</span>";
      }
      if ($savestate['mcount']) {
        $newtext .= "<span id=\"mc\">" . $savestate['mcount'] . "</span>";
      }
      if ($savestate['pcount']) {
        $newtext .= "<span id=\"pc\">" . $savestate['pcount'] . "</span>";
      }
      $newtext .= "</div>\n";
      echo $newtext;
      ob_flush();
      flush();
    }
  } // $old
  if ($saveimport) {
    $query = "UPDATE $saveimport_table SET icount = {$savestate['icount']}, fcount = {$savestate['fcount']}, scount = {$savestate['scount']}, mcount = {$savestate['mcount']}, ncount = {$savestate['ncount']}, mcount = {$savestate['mcount']}, pcount = {$savestate['pcount']}, offset = $offset";
    $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
  }
}

function adjustMediaFileName($mm) {
  global $assignnames, $wholepath;

  if ($mm['path'] && $assignnames) {
    $newname = $mm['path'];
  } else {
    $newname = $mm['FILE'];
    if ($newname && strpos($mm['FILE'], 'http://') !== 0 && strpos($mm['FILE'], 'https://') !== 0) {
      $found = 0;
      $pathlist = getLocalPathList($mm['mediatypeID']);
      if ($pathlist) {
        $paths = explode(",", $pathlist);
        foreach ($paths as $path) {
          if (substr_count($newname, $path)) {
            $newname = substr($newname, strlen($path));
            $found = 1;
            break;
          }
        }
      }
      $newname = str_replace("\\", "/", $newname);
      if (!$found && !$wholepath) {
        $newname = basename($newname);
      } elseif (substr($newname, 0, 1) == "/") {
        $newname = substr($newname, 1);
      }
    }
  }

  return $newname;
}

function getLocalPathList($mediatypeID) {
  global $locimppath;

  switch ($mediatypeID) {
    case "photos":
      $locpath = $locimppath['photos'];
      break;
    case "histories":
      $locpath = $locimppath['histories'];
      break;
    case "documents":
      $locpath = $locimppath['documents'];
      break;
    case "headstones":
      $locpath = $locimppath['headstones'];
      break;
    default:
      $locpath = $locimppath['other'];
      break;
  }

  return $locpath;
}

function getMoreMMInfo($prevlevel, $mmcount) {
  global $lineinfo;

  $moreinfo = [];
  $origlevel = $prevlevel;
  $prevlevel++;
  $moreinfo['FORM'] = "";
  $moreinfo['defphoto'] = "";

  $lineinfo = getLine();
  while ($lineinfo['level'] >= $prevlevel) {
    $tag = $lineinfo['tag'];
    switch ($tag) {
      case "FILE":
      case "_FILE":
        $moreinfo['FILE'] = $lineinfo['rest'];
        $moreinfo['FILE'] .= getContinued();
        break;
      case "TITL":
        $moreinfo[$tag] = addslashes($lineinfo['rest']);
        $lineinfo = getLine();
        break;
      case "FORM":
        $moreinfo[$tag] = addslashes(strtoupper($lineinfo['rest']));
        $lineinfo = getLine();
        break;
      case "NOTE":
      case "TEXT":
        $moreinfo['NOTE'] = addslashes($lineinfo['rest']);
        $moreinfo['NOTE'] .= getContinued();
        break;
      case "CHAN":
        $lineinfo = getLine();
        $moreinfo['CHAN'] = addslashes($lineinfo['rest']);
        if ($moreinfo['CHAN']) {
          $moreinfo['CHAN'] = date("Y-m-d H:i:s", strtotime($moreinfo['CHAN']));
          $lineinfo = getLine();
        }
        break;
      case "_TYPE":
      case "TYPE":
        $moreinfo['mediatypeID'] = getMediaCollection2($lineinfo['rest']);
        $lineinfo = getLine();
        break;
      case "_PRIM":
        if ($origlevel == 1 && $lineinfo['rest'] == "Y") {
          $moreinfo['defphoto'] = 1;
        }
        $lineinfo = getLine();
        break;
      default:
        $lineinfo = getLine();
        break;
    }
  }
  if (!$moreinfo['FORM'] && $moreinfo['FILE']) {
    $lastperiod = strrpos($moreinfo['FILE'], ".");
    if ($lastperiod) {
      $moreinfo['FORM'] = strtoupper(substr($moreinfo['FILE'], $lastperiod + 1));
    }
  }
  $moreinfo['mediatypeID'] = getMediaCollection($moreinfo);
  $moreinfo['FILE'] = adjustMediaFileName($moreinfo);

  return $moreinfo;
}

function getMediaCollection($mediaobj) {
  global $historytypes, $documenttypes, $videotypes, $recordingtypes, $locimppath;

  $mediatypeID = $mediaobj['mediatypeID'];
  $found = false;
  foreach ($locimppath as $locMediatypeID => $pathlist) {
    if ($pathlist) {
      $paths = explode(",", $pathlist);
      foreach ($paths as $path) {
        if (substr_count($mediaobj['FILE'], $path)) {
          $mediatypeID = $locMediatypeID;
          $found = true;
          break;
        }
      }
    }
    if ($found) {
      break;
    }
  }

  if (!$mediatypeID && isset($mediaobj['FORM']) && $mediaobj['FORM']) {
    $form = $mediaobj['FORM'];
    if (in_array($form, $historytypes)) {
      $mediatypeID = "histories";
    } elseif (in_array($form, $documenttypes)) {
      $mediatypeID = "documents";
    } elseif (in_array($form, $videotypes)) {
      $mediatypeID = "videos";
    } elseif (in_array($form, $recordingtypes)) {
      $mediatypeID = "recordings";
    } else {
      $mediatypeID = "photos";
    }
  }

  return $mediatypeID;
}

function getMediaCollection2($type) {
  $newtype = substr(strtolower($type), 0, 5);
  $mediatypeID = "";
  switch ($newtype) {
    case "photo":
      if (strtolower($type) == "photo document") {
        $mediatypeID = "documents";
      } else {
        $mediatypeID = "photos";
      }
      break;
    case "histo":
    case "biogr":
      $mediatypeID = "histories";
      break;
    case "pdf":
    case "docum":
      $mediatypeID = "documents";
      break;
    case "video":
      $mediatypeID = "videos";
      break;
    case "heads":
      $mediatypeID = "headstones";
      break;
    default:
      $mediatypeID = strtolower($type);
      break;
  }

  return $mediatypeID;
}

function getMediaFolder($mediatypeID) {
  global $rootpath, $mediapath, $photopath, $documentpath, $historypath;

  switch ($mediatypeID) {
    case "photos":
      $mmpath = "$rootpath$photopath";
      break;
    case "histories":
      $mmpath = "$rootpath$historypath";
      break;
    case "documents":
      $mmpath = "$rootpath$documentpath";
      break;
    default:
      $mmpath = "$rootpath$mediapath";
      break;
  }

  return $mmpath;
}

function processMedia($mmcount, $mminfo, $persfamID, $eventID) {
  global $medialinks_table;
  global $media_table;
  global $savestate;
  global $today;
  global $tngimpcfg;

  for ($mmctr = 1; $mmctr <= $mmcount; $mmctr++) {
    $mm = $mminfo[$mmctr];
    //insert ignore into media
    if (!$mm['CHAN'] && !$tngimpcfg['chdate']) {
      $mm['CHAN'] = $today;
    }
    if (!$mm['TITL']) {
      $mm['TITL'] = $mm['FILE'];
    }
    $query = "INSERT IGNORE INTO $media_table (mediatypeID, mediakey, path, description, notes, form, usecollfolder, changedate) "
        . "VALUES('{$mm['mediatypeID']}', '{$mm['OBJE']}', '{$mm['FILE']}', '{$mm['TITL']}', '{$mm['NOTE']}', '{$mm['FORM']}', '1', '{$mm['CHAN']}')";
    $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");

    $success = tng_affected_rows();
    if ($success) {
      $mediaID = tng_insert_id();
      incrCounter('M');
    } else {
      //update if necessary
      $query = "SELECT mediaID FROM $media_table WHERE mediakey = \"{$mm['OBJE']}\"";
      $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
      $row = tng_fetch_assoc($result);
      $mediaID = $row['mediaID'];
      tng_free_result($result);
      if ($savestate['del'] != "no") {
        if ($mm['FILE'] || $mm['TITL'] || $mm['NOTE']) {
          $changedatestr = $mm['CHAN'] ? ", changedate=\"{$mm['CHAN']}\"" : "";
          //$query = "UPDATE $media_table SET path=\"$mm['FILE']\", description=\"$mm['TITL']\", notes=\"$mm['NOTE']\", form=\"$mm['FORM']\"$changedatestr WHERE mediakey = \"$mm['OBJE']\"";
          $descstr = $mm['TITL'] ? ", description=\"{$mm['TITL']}\"" : "";
          $notestr = $mm['NOTE'] ? ", notes=\"{$mm['NOTE']}\"" : "";
          $query = "UPDATE $media_table SET path=\"{$mm['FILE']}\"$descstr$notestr, form=\"{$mm['FORM']}\"$changedatestr WHERE mediakey = \"{$mm['OBJE']}\"";
        } elseif ($mm['CHAN']) {
          $query = "UPDATE $media_table SET changedate=\"{$mm['CHAN']}\" WHERE mediakey = \"{$mm['OBJE']}\"";
        }
        $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
        incrCounter('M');
      }
    }
    //get ordernum according to collection/mediatypeID
    $query = "SELECT count(medialinkID) AS count FROM ($medialinks_table, $media_table) WHERE $media_table.mediaID = $medialinks_table.mediaID AND personID = '$persfamID' AND mediatypeID = \"{$mm['mediatypeID']}\"";
    $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
    $row = tng_fetch_assoc($result);
    $orderctr = $row['count'] ? $row['count'] + 1 : 1;
    tng_free_result($result);

    //insert ignore or update medialink
    $query = "INSERT IGNORE INTO $medialinks_table (personID, mediaID, linktype, altdescription, altnotes, ordernum, dontshow, eventID, defphoto)  "
        . "VALUES('$persfamID', '$mediaID', '{$mm['linktype']}', '{$mm['TITL']}', '{$mm['NOTE']}', '$orderctr', '0', '$eventID', '{$mm['defphoto']}')";
    $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
    $psuccess = tng_affected_rows();
    if (!$psuccess && $savestate['del'] != "no") {
      $defphotostr = $mm['defphoto'] ? ", defphoto = \"1\"" : "";
      $query = "UPDATE $medialinks_table SET altdescription=\"{$mm['TITL']}\", altnotes=\"{$mm['NOTE']}\"$defphotostr WHERE personID = '$persfamID' AND mediaID = '$mediaID' AND eventID = '$eventID'";
      $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
    }
    if ($mm['defphoto']) {
      $query = "UPDATE $medialinks_table SET defphoto=\"\" WHERE personID = '$persfamID' AND mediaID != '$mediaID'";
      $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
    }
  }
}

function getCodedMedia() {
  global $lineinfo;

  $continued = "";
  $notdone = 1;

  while ($notdone) {
    $lineinfo = getLine();
    //echo "$lineinfo['level'] $lineinfo['tag'] $lineinfo['rest']<br>\n";
    if ($lineinfo['tag'] == "CONT" || $lineinfo['tag'] == "CONC") {
      $continued .= $lineinfo['rest'];
    } else {
      $notdone = 0;
    }
  }
  return $continued;
}

function mmd($nextchar) {
  if (ord($nextchar) <= 57) {
    $offset = 46;
  } elseif (ord($nextchar) <= 90) {
    $offset = 53;
  } elseif (ord($nextchar) <= 122) {
    $offset = 59;
  } else {
    $offset = 0;
  }

  if ($offset) {
    $rval = str_pad(decbin(ord($nextchar) - $offset), 6, "0", STR_PAD_LEFT);
  } else {
    $rval = "";
  }

  return $rval;
}

function getMultimediaRecord($objectID, $prevlevel) {
  global $tree;
  global $savestate;
  global $lineinfo;
  global $media_table;
  global $mminfo;
  global $today;
  global $tngimpcfg;
  global $mediapath;
  global $thumbprefix;
  global $thumbsuffix;

  $prefix = 'M';
  $info = "";
  $changedate = "";
  $prevlevel++;
  $continued = 0;
  $gotfile = 0;
  $mmpath = "";

  $mminfo['ID'] = $objectID;
  //echo "doing $objectID<br>\n";
  $lineinfo = getLine();
  while ($lineinfo['tag'] && $lineinfo['level'] >= $prevlevel) {
    if ($lineinfo['level'] == $prevlevel) {
      $tag = $lineinfo['tag'];
      switch ($tag) {
        case "BLOB":
          if (!isset($mminfo[$objectID])) {
            $mminfo['path'] = $tree . $mminfo['ID'] . "." . $mminfo['FORM'];
            $mmpath = getMediaFolder($mminfo['mediatypeID']) . "/" . $mminfo['path'];
            $mminfo[$objectID] = fopen($mmpath, "wb");
            flock($mminfo[$objectID], 2);
            $gotfile = 1;
          }
          $mminfo['ID'] = $mminfo['saved'];
          $encodedstr = getCodedMedia();
          $chars = preg_split('//', $encodedstr, -1, PREG_SPLIT_NO_EMPTY);
          $end = count($chars);
          $ptr = 0;
          while ($ptr < $end) {
            $newstr = mmd($chars[$ptr]) . mmd($chars[$ptr + 1]) . mmd($chars[$ptr + 2]) . mmd($chars[$ptr + 3]);
            $packed = pack("c*", bindec(substr($newstr, 16, 8)), bindec(substr($newstr, 8, 8)), bindec(substr($newstr, 0, 8)));
            fwrite($mminfo[$objectID], $packed);
            $ptr += 4;
          }
          break;
        case "OBJE":
          //continue a previous one
          $continued = 1;
          //echo "continuing $objectID<br>";
          $mminfo['saved'] = $objectID;
          preg_match("/^@(\S+)@/", $lineinfo['rest'], $matches);
          $index = $matches[1];
          $mminfo[$index] = $mminfo[$objectID];
          $lineinfo = getLine();
          break;
        case "FILE":
        case "_FILE":
          $mminfo['FILE'] = $lineinfo['rest'];
          $mminfo['FILE'] .= getContinued();
          if (!$mminfo['mediatypeID']) {
            $lastperiod = strrpos($moreinfo['FILE'], ".");
            if ($lastperiod) {
              $mminfo['FORM'] = strtoupper(substr($mminfo['FILE'], $lastperiod + 1));
            }
          }
          break;
        case "TITL":
          $mminfo['TITL'] = addslashes($lineinfo['rest']);
          $lineinfo = getLine();
          break;
        case "_TYPE":
        case "TYPE":
          $mminfo['mediatypeID'] = getMediaCollection2($lineinfo['rest']);
          $lineinfo = getLine();
          break;
        case "FORM":
          $mminfo['FORM'] = addslashes(strtoupper($lineinfo['rest']));
          $lineinfo = getLine();
          break;
        case "NOTE":
        case "TEXT":
          $mminfo['NOTE'] = addslashes($lineinfo['rest']);
          $mminfo['NOTE'] .= getContinued();
          break;
        case "CHAN":
          $lineinfo = getLine();
          $changedate = addslashes($lineinfo['rest']);
          if ($changedate) {
            $changedate = date("Y-m-d H:i:s", strtotime($changedate));
            $lineinfo = getLine();
          }
          break;
        default:
          $lineinfo = getLine();
          break;
      }
    } else {
      $lineinfo = getLine();
    }
    $mminfo['mediatypeID'] = getMediaCollection($mminfo);
  }
  if (!$continued) {
    if ($gotfile) {
      flock($mminfo[$objectID], 3);
      fclose($mminfo[$objectID]);
    }

    $inschangedt = $changedate ? $changedate : ($tngimpcfg['chdate'] ? "" : $today);
    if ($savestate['del'] != "no") {
      $mminfo['FILE'] = adjustMediaFileName($mminfo);
      if ($mminfo['FILE'] != $mminfo['path']) {
        if ($mminfo['FILE'] && $mminfo['path']) {
          $mmpath = getMediaFolder($mminfo['mediatypeID']);
          rename($mmpath . "/" . $mminfo['path'], $mmpath . "/" . $mminfo['FILE']);
        }
      }

      $thumbpath = ($thumbprefix || $thumbsuffix) ? $thumbprefix . $mminfo['path'] . $thumbsuffix : $mminfo['path'];
      if (!$mminfo['mediatypeID']) {
        $mminfo['mediatypeID'] = "photos";
      }
      $mminfo['ucf'] = ($mmpath && $mmpath == $mediapath) ? 0 : 1;

      //get the mediatypeID, hs & history items, mediakey--should it always be the file?
      if (!$mminfo['TITL']) {
        $mminfo['TITL'] = $mminfo['FILE'];
      }
      $query = "INSERT IGNORE INTO $media_table (mediakey, path, thumbpath, description, notes, form, mediatypeID, usecollfolder, changedate) "
          . "VALUES('{$mminfo['ID']}', '{$mminfo['FILE']}', '$thumbpath', '{$mminfo['TITL']}', '{$mminfo['NOTE']}', '{$mminfo['FORM']}', '{$mminfo['mediatypeID']}', '{$mminfo['ucf']}', '$inschangedt')";
      $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");

      $success = tng_affected_rows();
      if (!$success) {
        $query = "SELECT mediatypeID FROM $media_table WHERE mediakey = \"{$mminfo['ID']}\"";
        $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
        $row = tng_fetch_assoc($result);
        tng_free_result($result);

        $mediatypeIDstr = !$row['mediatypeID'] ? " mediatypeID=\"{$mminfo['mediatypeID']}\"," : "";
        //$mediatypeIDstr = " mediatypeID=\"{$mminfo['mediatypeID']}\",";
        $query = "UPDATE $media_table SET path=\"{$mminfo['FILE']}\", description=\"{$mminfo['TITL']}\", notes=\"{$mminfo['NOTE']}\", form=\"{$mminfo['FORM']}\",$mediatypeIDstr changedate=\"$inschangedt\" WHERE mediakey = \"{$mminfo['ID']}\"";
        $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
      }
    }

    unset($mminfo);
    incrCounter($prefix);
  }
}

function processCitations($persfamID, $eventID, $citearray) {
  if (is_array($citearray)) {
    foreach ($citearray as $cite) {
      saveCitation($persfamID, $eventID, $cite);
    }
  }
}

function saveCitation($persfamID, $eventID, $cite) {
  global $citations_table;

  if (!$cite['DATETR']) {
    $cite['DATETR'] = "0000-00-00";
  }
  $query = "INSERT INTO $citations_table (persfamID, eventID, sourceID, description, citedate, citedatetr, citetext, page, quay, note, ordernum ) "
      . "VALUES('$persfamID', '$eventID', '{$cite['sourceID']}', '{$cite['desc']}', '{$cite['DATE']}', '{$cite['DATETR']}', '{$cite['TEXT']}', '{$cite['PAGE']}', '{$cite['QUAY']}', '{$cite['NOTE']}', '0')";
  $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
}

function processNotes($persfamID, $eventID, $notearray) {
  if (is_array($notearray)) {
    foreach ($notearray as $note) {
      saveNote($persfamID, $eventID, $note);
    }
  }
}

function saveNote($persfamID, $eventID, $note) {
  global $notelinks_table;
  global $xnotes_table;
  global $tngimpcfg;

  $found = 0;
  if ($note['XNOTE']) {
    $query = "SELECT ID FROM $xnotes_table WHERE noteID = \"{$note['XNOTE']}\"";
    $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
    $row = tng_fetch_assoc($result);
    if (tng_num_rows($result)) {
      $xnoteID = $row['ID'];
      $found = 1;
    }
    tng_free_result($result);
  }
  if (!$found) {
    $query = "INSERT INTO $xnotes_table (noteID, note) VALUES('{$note['XNOTE']}', '{$note['NOTE']}')";
    $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
    $xnoteID = tng_insert_id();
    incrCounter('N');
  }

  $privlen = strlen($tngimpcfg['privnote']);
  $secret = ($privlen && substr($note['NOTE'], 0, $privlen) == $tngimpcfg['privnote']) ? 1 : 0;
  $query = "INSERT IGNORE INTO $notelinks_table (persfamID, eventID, xnoteID, secret, ordernum) "
      . "VALUES('$persfamID', '$eventID', '$xnoteID', '$secret', '0')";
  $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
  $ID = tng_insert_id();

  if (isset($note['SOUR'])) {
    processCitations($persfamID, "N$ID", $note['SOUR']);
  }
}

function getNoteRecord($noteID, $prevlevel) {
  global $savestate;
  global $lineinfo;
  global $xnotes_table;
  global $citations_table;
  global $tngimpcfg;
  global $notelinks_table;

  $noteID = adjustID($noteID, $savestate['noffset']);

  $prefix = 'N';
  $info = "";
  $prevlevel++;

  preg_match("/^NOTE ?(.*)$/", $lineinfo['rest'], $matches);
  if ($matches[1]) {
    $note = addslashes($matches[1]);
  } else {
    $note = "";
  }
  $lineinfo = getLine();
  if ($lineinfo['level'] && ($lineinfo['tag'] == "NOTE" || $lineinfo['tag'] == "CONT" || $lineinfo['tag'] == "CONC")) {
    if ($note && $lineinfo['tag'] != "CONC") {
      $note .= "\n";
    }
    $note .= addslashes($lineinfo['rest']);
    $note .= getContinued();
  }
  $notectr = 0;
  while ($lineinfo['level'] >= $prevlevel && $lineinfo['tag'] == "SOUR") {
    $notectr++;
    $notesource[$notectr] = handleSource($noteID, $lineinfo['level']);
  }

  $query = "SELECT ID FROM $xnotes_table WHERE noteID = '$noteID'";
  $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
  $row = tng_fetch_assoc($result);
  if (tng_num_rows($result) && $savestate['del'] != "no") {
    $ID = $row['ID'];
    $query = "UPDATE $xnotes_table SET note=\"$note\" WHERE noteID = '$noteID'";
    $xresult = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
  } else {
    $query = "INSERT INTO $xnotes_table (noteID, note) VALUES('$noteID', '$note')";
    $xresult = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
    $ID = tng_insert_id();
    incrCounter($prefix);
  }

  //see if private character exists
  $privlen = strlen($tngimpcfg['privnote']);
  if ($privlen && substr($note, 0, $privlen) == $tngimpcfg['privnote']) {
    $query = "UPDATE $notelinks_table SET secret=\"1\" WHERE xnoteID=\"$ID\"";
    $nresult = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
  }

  if ($notectr) {
    if ($savestate['del'] == "match") {
      $query = "DELETE from $citations_table WHERE persfamID = '$noteID'";
      tng_query($query);
    }
    processCitations($noteID, "", $notesource);
  }
  tng_free_result($result);
}

function dumpnotes($notearray) {
  global $stdnotes, $notecount;

  foreach ($notearray as $note) {
    $notecount++;
    $stdnotes[$notecount] = $note;
  }
}

function saveCustEvents($prefix, $persfamID, $events, $totevents) {
  global $events_table;
  global $eventtypes_table;
  global $custevents;
  global $medialinks;
  global $num_medialinks;
  global $medialinks_table;
  global $num_albumlinks;
  global $album2entities_table;
  global $albumlinks;
  global $allevents;
  global $savestate;

  for ($eventnum = 1; $eventnum <= $totevents; $eventnum++) {
    $event = $events[$eventnum]['TAG'];
    $eventptr = $events[$eventnum]['INFO'];
    $description = $events[$eventnum]['TYPE'];
    $wherestr = $event == "EVEN" ? "AND description = \"$description\"" : "";
    if ($description) {
      $display = $description;
    } else {
      $display = uiTextSnippet($event);
      if (!$display) {
        $display = $event;
      }
    }
    $eventinfo = $eventptr['FACT'];
    $eventtype = strtoupper($prefix . "_" . $event . "_" . $description);

    if (!$custevents[$eventtype]) {
      //if not in  custevents array, add to eventtypes_table with keep=ignore
      $keep = $allevents ? 1 : 0;
      $query = "INSERT IGNORE INTO $eventtypes_table (tag, description, display, keep, type)  VALUES(\"$event\", \"$description\", \"$display\", $keep, \"$prefix\")";
      $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
      $custevents[$eventtype]['eventtypeID'] = tng_insert_id();
      $custevents[$eventtype]['keep'] = $keep;
    }

    //save the event
    if (isset($custevents[$eventtype]) && $custevents[$eventtype]['keep']) {
      $eventtypeID = $custevents[$eventtype]['eventtypeID'];
      //always insert, never update in this case
      if (!$eventptr['DATETR']) {
        $eventptr['DATETR'] = "0000-00-00";
      }
      preg_match("/^@(\S+)@/", $eventinfo, $matches);
      if ($matches[1]) {
        $eventinfo = "@" . adjustId($matches[1], $savestate['noffset']) . "@";
      }
      $query = "INSERT INTO $events_table (eventtypeID, persfamID, eventdate, eventdatetr, eventplace, age, agency, cause, addressID, parenttag, info) "
          . "VALUES('$eventtypeID', '$persfamID', \"" . $eventptr['DATE'] . "\", \"" . $eventptr['DATETR'] . "\", \"" . $eventptr['PLAC'] . "\", \"" . $eventptr['AGE'] . "\", \"" . $eventptr['AGNC'] . "\", \"" . $eventptr['CAUS'] . "\", \"" . $eventptr['ADDR'] . "\",  \"" . $eventptr['parent'] . "\", '$eventinfo')";
      $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
      $eventID = tng_insert_id();

      if ($num_medialinks || $num_albumlinks) {
        $key = $persfamID . "::" . $eventtypeID . "::" . $eventptr['DATE'] . "::" . substr(stripslashes($eventptr['PLAC']), 0, 40) . "::" . substr(stripslashes($eventinfo), 0, 40);
        $key = preg_replace("/[^A-Za-z0-9:]/", "", $key);
        if ($num_medialinks) {
          if (isset($medialinks[$key])) {
            foreach ($medialinks[$key] as $medialinkID) {

              $query = "UPDATE $medialinks_table SET eventID = \"$eventID\" WHERE medialinkID = \"$medialinkID\"";
              $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
            }
            unset($medialinks[$key]);
          }
        }
        if ($num_albumlinks) {
          if (isset($albumlinks[$key])) {
            foreach ($albumlinks[$key] as $albumlinkID) {
              //put new eventID in old medialink records for this event
              $query = "UPDATE $album2entities_table SET eventID = \"$eventID\" WHERE alinkID = \"$albumlinkID\"";
              $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
            }
            unset($albumlinks[$key]);
          }
        }
      }

      if (isset($eventptr['SOUR'])) {
        processCitations($persfamID, $eventID, $eventptr['SOUR']);
      }
      if (isset($eventptr['NOTES'])) {
        processNotes($persfamID, $eventID, $eventptr['NOTES']);
      }
    }
    //save media, if any
    if (is_array($eventptr['MEDIA'])) {
      $mminfo = $eventptr['MEDIA'];
      foreach ($mminfo as $m) {
        $m['linktype'] = $prefix;
      }
      processMedia(count($mminfo), $mminfo, $persfamID, $eventID);
    }
  }
}

function removeDelims($fact) {
  preg_match("/(.*)\s*\/(.*)\/\s*(.*)/", $fact, $matches);
  if (count($matches) && substr($fact, 0, 1) != '<' && substr($fact, 0, 4) != "http") {
    $fact = trim($matches[1] . " " . $matches[2] . " " . $matches[3]);
  }

  return $fact;
}
