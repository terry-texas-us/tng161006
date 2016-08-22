<?php

$nodate_all = "0000-00-00";
$eventctr_all = 1;

function getBirthInfo($thisperson, $noicon = null) {
  $birthstring = "";

  if (!$noicon) {
    $findPlaces_ui = uiTextSnippet('findplaces');
    $icon = "<img class='icon-xs-inline' src='svg/magnifying-glass.svg' alt='$findPlaces_ui'>";
    $placelinkbegin = " <a href='placesearch.php?psearch=";
    $placelinkend = "' title='$findPlaces_ui'>$icon</a>";
  }
  if ($thisperson['birthdate'] || ($thisperson['birthplace'] && !$thisperson['altbirthdate'])) {
    $birthstring .= ", " . uiTextSnippet('birthabbr', ['html' => 'strong']) . " ";
    if ($thisperson['birthdate']) {
      $birthstring .= displayDate($thisperson['birthdate']);
    }
    if ($thisperson['birthplace']) {
      if ($thisperson['birthdate']) {
        $birthstring .= ", ";
      }
      $birthstring .= $thisperson['birthplace'];
      if (!$noicon) {
        $birthstring .= $placelinkbegin . urlencode($thisperson['birthplace']) . $placelinkend;
      }
    }
  } else {
    if ($thisperson['altbirthdate'] || $thisperson['altbirthplace']) {
      $birthstring .= ", " . uiTextSnippet('chrabbr', ['html' => 'strong']) . " ";
      if ($thisperson['altbirthdate']) {
        $birthstring .= displayDate($thisperson['altbirthdate']);
      }
      if ($thisperson['altbirthplace']) {
        if ($thisperson['altbirthdate']) {
          $birthstring .= ", ";
        }
        $birthstring .= $thisperson['altbirthplace'];
        if (!$noicon) {
          $birthstring .= $placelinkbegin . urlencode($thisperson['altbirthplace']) . $placelinkend;
        }
      }
    }
  }
  //the "noicon" flag is only set in the person preview screen. We don't want to see death info there (to keep it short)
  if (!$noicon) {
    if ($thisperson['deathdate'] || ($thisperson['deathplace'] && !$thisperson['burialdate'])) {
      $birthstring .= ", " . uiTextSnippet('deathabbr', ['html' => 'strong']) . " ";
      if ($thisperson['deathdate']) {
        $birthstring .= displayDate($thisperson['deathdate']);
      }
      if ($thisperson['deathplace']) {
        if ($thisperson['deathdate']) {
          $birthstring .= ", ";
        }
        $birthstring .= $thisperson['deathplace'];
        $birthstring .= $placelinkbegin . urlencode($thisperson['deathplace']) . $placelinkend;
      }
    } else {
      if ($thisperson['burialdate'] || $thisperson['burialplace']) {
        $birthstring .= ", " . uiTextSnippet('burialabbr', ['html' => 'strong']) . " ";
        if ($thisperson['burialdate']) {
          $birthstring .= displayDate($thisperson['burialdate']);
        }
        if ($thisperson['burialplace']) {
          if ($thisperson['burialdate']) {
            $birthstring .= ", ";
          }
          $birthstring .= $thisperson['burialplace'];
          $birthstring .= $placelinkbegin . urlencode($thisperson['burialplace']) . $placelinkend;
        }
      }
    }
  }
  return $birthstring;
}

function getCitations($persfamID, $shortcite = 1) {
  global $sources_table;
  global $citations_table;
  global $citations;
  global $citationsctr;
  global $citedisplay;

  $actualtext = $shortcite ? "" : ", actualtext";
  $citquery = "SELECT citationID, title, shorttitle, author, other, publisher, callnum, page, quay, citedate, citetext, $citations_table.note as note, $citations_table.sourceID, description, eventID{$actualtext}
    FROM $citations_table LEFT JOIN $sources_table ON $citations_table.sourceID = $sources_table.sourceID
    WHERE persfamID = '$persfamID' ORDER BY ordernum, citationID";
  $citresult = tng_query($citquery) or die(uiTextSnippet('cannotexecutequery') . ": $citquery");

  while ($citrow = tng_fetch_assoc($citresult)) {
    $source = $citrow['sourceID'] ? "[<a href=\"showsource.php?sourceID={$citrow['sourceID']}\">{$citrow['sourceID']}</a>] " : "";
    $newstring = $source ? "" : $citrow['description'];
    $key = $persfamID . "_" . $citrow['eventID'];
    $citationsctr++;
    $citations[$key] .= $citations[$key] ? ",$citationsctr" : $citationsctr;

    if ($citrow['shorttitle']) {
      if ($newstring) {
        $newstring .= ", ";
      }
      $newstring .= $citrow['shorttitle'];
    } else {
      if ($citrow['title']) {
        if ($newstring) {
          $newstring .= ", ";
        }
        $newstring .= $citrow['title'];
      }
    }
    if ($citrow['author']) {
      if ($newstring) {
        $newstring .= ", ";
      }
      $newstring .= $citrow['author'];
    }
    if ($citrow['publisher']) {
      if ($newstring) {
        $newstring .= ", ";
      }
      $newstring .= "({$citrow['publisher']})";
    }
    if ($citrow['callnum']) {
      if ($newstring) {
        $newstring .= ", ";
      }
      $newstring .= $citrow['callnum'] . ".";
    }
    if ($citrow['other']) {
      if ($newstring) {
        $newstring .= ", ";
      }
      $newstring .= $citrow['other'];
    }
    if ($citrow['page']) {
      if ($newstring) {
        $newstring .= ", ";
      }
      $newstring .= nl2br(insertLinks($citrow['page']));
    }
    if ($citrow['quay'] != "") {
      if ($newstring) {
        $newstring .= " ";
      }
      $newstring .= "(" . uiTextSnippet('reliability') . ": {$citrow['quay']})";
    }
    if ($citrow['citedate']) {
      if ($newstring) {
        $newstring .= ", ";
      }
      $newstring .= displayDate($citrow['citedate']);
    }
    $newstring .= substr($newstring, -1) == "." ? "" : ".";
    if ($citrow['citetext']) {
      if ($newstring) {
        $newstring .= "<br>\n";
      }
      $newstring .= nl2br(insertLinks($citrow['citetext']));
    }
    if ($citrow['note']) {
      if ($newstring) {
        $newstring .= "<br>\n";
      }
      $xarr = checkXNote($citrow['note']);
      $citrow['note'] = insertLinks($xarr[0]);
      $newstring .= nl2br($citrow['note']);
    }
    if (!$shortcite && $citrow['actualtext']) {
      if ($newstring) {
        $newstring .= "\n\n";
      }
      $newstring .= insertLinks($citrow['actualtext']);
    }
    $citedisplay[$citationsctr] = "$source $newstring";
  }
  tng_free_result($citresult);
}

function reorderCitation($citekey, $withlink = 1) {
  global $citedispctr;
  global $citestring;
  global $citations;
  global $citedisplay;

  $newstring = "";
  $newcitearr = [];
  if ($citations[$citekey]) {
    $citationlist = explode(',', $citations[$citekey]);
    foreach ($citationlist as $citation) {
      $newcite = $citedisplay[$citation];
      if (function_exists('array_search')) {
        $newcitecnt = array_search($newcite, $citestring);
      } else {
        $newcitecnt = 0;
      }
      if (!$newcitecnt) {
        $citedispctr++;
        $newcitecnt = $citedispctr;
        $arridx = count($citestring) + 1;
        $citestring[$arridx] = $newcite;
      }
      array_push($newcitearr, $newcitecnt);
    }
    $citations[$citekey] = "";
  }
  $newcitearr = array_unique($newcitearr);
  asort($newcitearr);
  foreach ($newcitearr as $newcite) {
    $newstring .= $newstring ? ", " : "";
    if ($withlink) {
      $newstring .= "<a href=\"#cite$newcite\" onclick=\"$('citations').style.display = '';\">$newcite</a>";
    } else {
      $newstring .= $newcite;
    }
  }
  return $newstring;
}

function getNotes($persfamID, $flag) {
  global $notelinks_table;
  global $xnotes_table;
  global $eventtypes_table;
  global $events_table;
  global $allow_private;

  $custnotes = [];
  $gennotes = [];
  $precustnotes = [];
  $postcustnotes = [];
  $finalnotesarray = [];

  if ($flag == 'I') {
    $precusttitles = ["BIRT" => uiTextSnippet('born'), "CHR" => uiTextSnippet('christened'), "NAME" => uiTextSnippet('name'), "TITL" => uiTextSnippet('title'), "NPFX" => uiTextSnippet('prefix'), "NSFX" => uiTextSnippet('suffix'), "NICK" => uiTextSnippet('nickname'), "BAPL" => uiTextSnippet('baptizedlds'), "CONL" => uiTextSnippet('conflds'), "INIT" => uiTextSnippet('initlds'), "ENDL" => uiTextSnippet('endowedlds')];
    $postcusttitles = ["DEAT" => uiTextSnippet('died'), "BURI" => uiTextSnippet('buried'), "SLGC" => uiTextSnippet('sealedplds')];
  } elseif ($flag == 'F') {
    $precusttitles = ["MARR" => uiTextSnippet('married'), "SLGS" => uiTextSnippet('sealedslds'), "DIV" => uiTextSnippet('divorced')];
    $postcusttitles = [];
  } else {
    $precusttitles = ["ABBR" => uiTextSnippet('shorttitle'), "CALN" => uiTextSnippet('callnum'), "AUTH" => uiTextSnippet('author'), "PUBL" => uiTextSnippet('publisher'), "TITL" => uiTextSnippet('title')];
    $postcusttitles = [];
  }

  $secretstr = $allow_private ? "" : " AND secret != \"1\"";
  $query = "SELECT display, $xnotes_table.note as note, $notelinks_table.eventID as eventID, $notelinks_table.xnoteID as xnoteID, $notelinks_table.ID as ID, noteID FROM $notelinks_table
    LEFT JOIN  $xnotes_table ON $notelinks_table.xnoteID = $xnotes_table.ID
    LEFT JOIN $events_table ON $notelinks_table.eventID = $events_table.eventID
    LEFT JOIN $eventtypes_table ON $eventtypes_table.eventtypeID = $events_table.eventtypeID
    WHERE $notelinks_table.persfamID = '$persfamID' $secretstr
    ORDER BY eventdatetr, $eventtypes_table.ordernum, tag, $notelinks_table.ordernum, ID";
  $notelinks = tng_query($query);

  $currevent = "";
  $currsig = "";
  $type = 0;
  while ($note = tng_fetch_assoc($notelinks)) {
    if ($note['noteID']) {
      getCitations($note['noteID']);
    }
    //else
    //getCitations( $note['ID'] );
    if (!$note['eventID']) {
      $note['eventID'] = "--x-general-x--";
    }
    $signature = $note['eventID'] . "_" . $note['xnoteID'];
    if ($signature != $currsig) {
      $currsig = $signature;
      $currevent = $note['eventID'];
      $currtitle = "";
    }
    if (!$currtitle) {
      if ($note['display']) {
        $currtitle = getEventDisplay($note['display']);
        $key = "$currsig";
        $custnotes[$key] = ["title" => $currtitle, "text" => ""];
        $type = 2;
      } else {
        if ($postcusttitles[$currevent]) {
          $currtitle = $postcusttitles[$currevent];
          $postcustnotes[$currsig] = ["title" => $postcusttitles[$currevent], "text" => ""];
          $type = 3;
        } else {
          $currtitle = $precusttitles[$currevent] ? $precusttitles[$currevent] : " ";
          if (substr($note['eventID'], 0, 15) == "--x-general-x--") {
            $gennotes[$currsig] = ["title" => $precusttitles[$currevent], "text" => ""];
            $type = 0;
          } else {
            $precustnotes[$currsig] = ["title" => $precusttitles[$currevent], "text" => ""];
            $type = 1;
          }
        }
      }
    }
    switch ($type) {
      case 0:
        if ($gennotes[$currsig]['text']) {
          $gennotes[$currsig]['text'] .= "</li>\n";
        }
        $gennotes[$currsig]['text'] .= "<li>" . nl2br($note['note']);
        $gennotes[$currsig]['cite'] .= "N{$note['ID']}";
        $gennotes[$currsig]['xnote'] .= $note['noteID'];
        break;
      case 1:
        if ($precustnotes[$currsig]['text']) {
          $precustnotes[$currsig]['text'] .= "</li>\n";
        }
        $precustnotes[$currsig]['text'] .= "<li>" . nl2br($note['note']);
        $precustnotes[$currsig]['cite'] .= "N{$note['ID']}";
        $precustnotes[$currsig]['xnote'] .= $note['noteID'];
        break;
      case 2:
        if ($custnotes[$key]['text']) {
          $custnotes[$key]['text'] .= "</li>\n";
        }
        $custnotes[$key]['text'] .= "<li>" . nl2br($note['note']);
        $custnotes[$key]['cite'] .= "N{$note['ID']}";
        $custnotes[$key]['xnote'] .= $note['noteID'];
        break;
      case 3:
        if ($postcustnotes[$currsig]['text']) {
          $postcustnotes[$currsig]['text'] .= "</li>\n";
        }
        $postcustnotes[$currsig]['text'] .= "<li>" . nl2br($note['note']);
        $postcustnotes[$currsig]['cite'] .= "N{$note['ID']}";
        $postcustnotes[$currsig]['xnote'] .= $note['noteID'];
        break;
    }
  }
  $finalnotesarray = array_merge($gennotes, $precustnotes, $custnotes, $postcustnotes);
  tng_free_result($notelinks);

  return $finalnotesarray;
}

function buildNotes($notearray, $entity) {
  $notes = "";
  $lasttitle = "---";
  foreach ($notearray as $key => $note) {
    if ($note['title'] != $lasttitle) {
      if ($notes) {
        $notes .= "</ul>\n<br>\n";
      }
      if ($note['title']) {
        $notes .= "<a name=\"$key\"><span>{$note['title']}:</span></a><br>\n";
      }
    }
    $cite = reorderCitation($entity . "_" . $note['cite']);
    if ($note['xnote']) {
      $cite2 = reorderCitation($note['xnote'] . "_");
      $cite = $cite && $cite2 ? $cite . "," . $cite2 : $cite . $cite2;
    }
    if ($cite) {
      $cite = " [$cite]";
    }
    if ($note['title'] != $lasttitle) {
      $notes .= "<ul>\n";
      $lasttitle = $note['title'];
    }
    $notes .= $note['text'] . "$cite</li>\n";
  }
  if ($notes) {
    $notes .= "</ul>\n";
  }
  return insertLinks($notes);
}

function buildGenNotes($notearray, $entity, $eventlist) {
  $notes = "";
  $lasttitle = "---";
  if (is_array($notearray)) {
    $events = explode(",", $eventlist);
    $eventctr = 0;
    foreach ($events as $event) {
      //$eventlen = strlen( $event );
      foreach ($notearray as $key => $note) {
        //if( substr($key,0,$eventlen) == $event ) {
        if (strtok($key, "_") == $event) {
          if ($note['title'] != $lasttitle && $eventctr) {
            if ($notes) {
              $notes .= "</ul>\n<br>\n";
            }
            if ($note['title']) {
              $notes .= "<a name=\"$key\"><span>{$note['title']}:</span></a><br>\n";
            }
          }
          $cite = reorderCitation($entity . "_" . $note['cite']);
          if ($note['xnote']) {
            $cite2 = reorderCitation($note['xnote'] . "_");
            $cite = $cite && $cite2 ? $cite . "," . $cite2 : $cite . $cite2;
          }
          if ($cite) {
            $cite = " [$cite]";
          }
          if ($note['title'] != $lasttitle) {
            $notes .= "<ul>\n";
            $lasttitle = $note['title'];
          }
          $notes .= $note['text'] . "$cite</li>\n";
        }
      }
      $eventctr++;
    }
    if ($notes) {
      $notes .= "</ul>\n";
    }
  }
  return insertLinks($notes);
}

function checkXnote($fact) {
  global $xnotes_table;

  $newfact = [];
  preg_match("/^@(\S+)@/", $fact, $matches);
  if ($matches[1]) {
    $query = "SELECT note, ID from $xnotes_table WHERE noteID = \"$matches[1]\"";
    $xnoteres = tng_query($query);
    if ($xnoteres) {
      $xnote = tng_fetch_assoc($xnoteres);
      $newfact[0] = trim($xnote['note']);
      $newfact[1] = $matches[1];
      getCitations($matches[1]);
    }
    tng_free_result($xnoteres);
  } else {
    $newfact[0] = $fact;
  }
  return $newfact;
}

function strpos_array($notes, $needle) {
  while (($pos = strpos($haystack, $needle, $pos)) !== false) {
    $array[] = $pos++;
  }
  return $array;
}

function resetEvents() {
  global $eventctr;
  global $events;
  global $nodate;

  $events = [];
  $nodate = "0000-00-00";
  $eventctr = 1;
}

function setEvent($data, $datetr) {
  global $eventctr, $events, $nodate, $map, $eventctr_all, $nodate_all, $tngconfig;

  //make a copy of datetr
  $datetr_all = $datetr;
  if ($datetr_all == "0000-00-00") {
    $datetr_all = $nodate_all;
  } elseif ($datetr_all > $nodate_all) {
    $nodate_all = $datetr_all;
  }
  $index_all = $datetr_all . sprintf("%03d", $eventctr_all);
  $eventctr_all++;

  if ($datetr == "0000-00-00") {
    $datetr = $nodate;
  } elseif ($datetr > $nodate) {
    $nodate = $datetr;
  }
  $index = $datetr . sprintf("%03d", $eventctr);
  $events[$index] = $data;
  $eventctr++;

  if ($map['key'] && $data['place'] && !$data['nomap']) {
    global $locations2map;
    global $l2mCount;
    global $places_table;
    global $pinplacelevel0;

    $safeplace = tng_real_escape_string($data['place']);
    $query = "SELECT place, placelevel, latitude, longitude, zoom, notes
      FROM $places_table WHERE $places_table.place = '$safeplace' and (latitude is not null and latitude != '') and (longitude is not null and longitude != '')";
    $custevents = tng_query($query);

    $numrows = tng_num_rows($custevents);
    if ($numrows) {
      $fixedplace = htmlspecialchars($safeplace, ENT_QUOTES);
      $custevent = tng_fetch_assoc($custevents);
      $info = $data['fact'];
      $pinplacelevel = $custevent['placelevel'] ? ${"pinplacelevel" . $custevent['placelevel']} : $pinplacelevel0;
      //using $index above will ensure that this array gets sorted in the same order as the events on the page
      $locations2map[$l2mCount] = [$index_all,
              "placelevel" => $custevent['placelevel'],
              "pinplacelevel" => $pinplacelevel,
              "event" => $data['text'],
              "htmlcontent" => "",
              "lat" => $custevent['latitude'],
              "long" => $custevent['longitude'],
              "zoom" => $custevent['zoom'],
              "place" => $custevent['place'],
              "notes" => truncateIt($custevent['notes'], 600),
              "eventdate" => $data['date'],
              "description" => $info[0],
              "fixedplace" => $fixedplace
      ];
      $l2mCount++;
    }
    tng_free_result($custevents);
  }
}
$datewidth = $thumbmaxw + 20 > 104 ? $thumbmaxw + 20 : 104;
$eventcounter = 0;

function showEvent($data) {
  global $notestogether;
  global $tableid;
  global $cellnumber;
  global $tentative_edit;
  global $indnotes;
  global $famnotes;
  global $srcnotes;
  global $reponotes;
  global $indmedia;
  global $fammedia;
  global $srcmedia;
  global $repomedia;
  global $indalbums;
  global $famalbums;
  global $srcalbums;
  global $repoalbums;
  global $eventcounter;

  switch ($data['type']) {
    case 'I':
      $notearray = $indnotes;
      $media = $indmedia;
      $albums = $indalbums;
      break;
    case 'F':
      $notearray = $famnotes;
      $media = $fammedia;
      $albums = $famalbums;
      break;
    case 'S':
      $notearray = $srcnotes;
      $media = $srcmedia;
      $albums = $srcalbums;
      break;
    case 'R':
      $notearray = $reponotes;
      $media = $repomedia;
      $albums = $repoalbums;
      break;
  }
  $dateplace = $data['date'] || $data['place'] ? 1 : 0;
  $eventcounter += 1;
  $toggle = $data['collapse'] ? " style=\"display:none\"" : "";
  $notes = $notestogether && $data['event'] ? buildGenNotes($notearray, $data['entity'], $data['event']) : "";
  $rows = $dateplace;
  if ($tableid && !$cellnumber && ($dateplace || $data['fact'] || $notes)) {
    $cellid = " id=\"$tableid" . "1\"";
    $cellnumber++;
  } else {
    $cellid = "";
  }

  if ($data['fact']) {
    $rows += is_array($data['fact']) ? count($data['fact']) : 1;
  }
  $output = "";
  $cite = $data['entity'] ? reorderCitation($data['entity'] . "_" . $data['event']) : "";

  if ($dateplace) {
    if ($data['date']) {
      $output .= "<td ";
      if (!$data['place']) {
        $output .= " colspan='2'";
      }
      $output .= ">" . displayDate($data['date']);
      if (!$data['place'] && $cite) {
        $output .= "<sup> $cite</sup>";
        $cite = "";
      }
      $output .= "</td>\n";
    }
    if ($data['place']) {
      $output .= "<td ";
      if ($cite) {
        $cite = "<sup> $cite</sup>";
      }
      if (!$data['date']) {
        $output .= " colspan='2'";
      }
      $output .= ">" . $data['place'];
      if (!isset($data['np'])) {
        $output .= " <a href=\"placesearch.php?psearch=" . urlencode($data['place']) . "\" title=\"" . uiTextSnippet('findplaces') . "\">\n";
        $output .= "<img class='icon-xs-inline' src='svg/magnifying-glass.svg' alt=\"" . uiTextSnippet('findplaces') . "\">\n";
        $output .= "</a>$cite</td>\n";
      } else {
        $output .= "</td>\n";
      }
      $cite = "";
    }
    $output .= "</tr>\n";
  } elseif ($data['fact'] == "" && $cite) {
    $data['fact'] = uiTextSnippet('yesabbr');
    $rows++;
  }
  if ($data['fact'] != "") {
    $cite .= $data['xnote'] ? reorderCitation($data['xnote'] . "_") : "";
    if (is_array($data['fact'])) {
      for ($i = 0; $i < count($data['fact']); $i++) {
        if ($output) {
          $output .= "<tr class=\"t{$eventcounter}\"$toggle>\n";
        }
        if ($cite) {
          $cite = "<sup> $cite</sup>";
        }
        $output .= "<td colspan='2'>" . nl2br(insertLinks($data['fact'][$i])) . "$cite</td></tr>\n";
        $cite = "";
      }
    } else {
      if ($output) {
        $output .= "<tr class=\"t{$eventcounter}\"$toggle>\n";
      }
      if (strpos($data['fact'], "http") === false && strpos($data['fact'], "www") === false) {
        preg_match("/(.*)\s*\/(.*)\/$/", $data['fact'], $matches);
        $count = count($matches);
        if ($count) {
          $newfact = "";
          for ($i = 1; $i <= $count; $i++) {
            if ($newfact) {
              $newfact .= " ";
            }
            $newfact .= addslashes($matches[$i]);
          }
          $data['fact'] = $newfact;
        }
      }
      if ($cite) {
        $cite = "<sup> $cite</sup>";
      }
      $output .= "<td colspan='2'>" . nl2br(insertLinks($data['fact'])) . "$cite</td></tr>\n";
      $cite = "";
    }
  }
  if ($notestogether) {
    if ($notes) {
      $rows++;
      if ($output) {
        $output .= "<tr class=\"t{$eventcounter}\"$toggle>\n";
      }
      $output .= "<td colspan='2'>$notes</td>\n";
      $output .= "</tr>\n";
    }
  }
  $event = $data['event'];

  if (!isset($media[$event])) {
    $media[$event] = [];
  }
  if (!isset($albums[$event])) {
    $albums[$event] = [];
  }
  $media_array = array_merge($media[$event], $albums[$event]);

  $mediaoutput = "";
  $thumbcount = 0;
  if (count($media_array)) {
    foreach ($media_array as $item) {
      $rows++;
      if ($output) {
        $mediaoutput .= "<tr class=\"t{$eventcounter}\"$toggle>\n";
      }
      if ($item['imgsrc']) {
        $mediaoutput .= "<td>{$item['imgsrc']}</td>\n";
        $thumbcount++;
      } else {
        $mediaoutput .= "<td></td>";
      }
      $mediaoutput .= "<td>{$item['name']}<br>" . nl2br($item['description']) . "</td>\n";
      $mediaoutput .= "</tr>\n";
    }
    if (!$thumbcount) {
      $mediaoutput = str_replace("<td></td><td>", "<td colspan='2'>", $mediaoutput);
    }
    $output .= $mediaoutput;
  }
  if ($output) {
    $editicon = $tentative_edit && $data['event'] && $data['event'] != "NAME" ? "<img class='icon-sm' src='svg/new-message.svg' alt=\"" . uiTextSnippet('editevent') . "\" title=\"" . uiTextSnippet('editevent') . "\" onclick=\"tnglitbox = new ModalDialog('ajx_tentedit.php?persfamID={$data['entity']}&amp;type={$data['type']}&amp;event={$data['event']}&amp;title={$data['text']}');\" class=\"fakelink\">" : "";
    $toggleicon = $data['collapse'] && $rows > 1 ? "<img src=\"img/tng_sort_desc.gif\" class=\"toggleicon\" id=\"t{$eventcounter}\" title=\"" . uiTextSnippet('expand') . "\">" : "";
    $class = $cellid ? "indleftcol" : "";
    $rowspan = $rows > 1 && !$data['collapse'] ? " rowspan=\"$rows\"" : "";
    $preoutput = "<tr>\n<td class=\"$class lt{$eventcounter}\" $rowspan$cellid>$toggleicon<span>{$data['text']}$editicon</span></td>\n";
    $final = $preoutput . $output;
  } else {
    $final = "";
  }

  return $final;
}

function showBreak($breaksize) {
  return "<tr><td colspan=\"3\" class=\"$breaksize\">&nbsp;</td></tr>\n";
}

function doCustomEvents($entityID, $type, $nomap = 0) {
  global $events_table;
  global $eventtypes_table;
  global $tngprint;

  $query = "SELECT display, eventdate, eventdatetr, eventplace, age, agency, cause, addressID, info, tag, description, eventID, collapse FROM "
          . "($events_table, $eventtypes_table) WHERE persfamID = \"$entityID\" AND $events_table.eventtypeID = $eventtypes_table.eventtypeID AND keep = \"1\" AND parenttag = \"\" ORDER BY ordernum, tag, description, eventdatetr, info, eventID";
  $custevents = tng_query($query);
  while ($custevent = tng_fetch_assoc($custevents)) {
    $displayval = getEventDisplay($custevent['display']);
    $eventID = $custevent['eventID'];
    
    $fact = [];
    if ($custevent['info']) {
      $fact = checkXnote($custevent['info']);
      if ($fact[1]) {
        $xnote = $fact[1];
        array_pop($fact);
      }
    }
    $extras = getFact($custevent);
    $fact = (count($fact) && $fact[0] != "") ? array_merge($fact, $extras) : $extras;
    if ($displayval != '_UID') { // [ts] is this identifier used .. it still comes in on many gedcoms
      setEvent(["text" => $displayval, "date" => $custevent['eventdate'], "place" => $custevent['eventplace'], "fact" => $fact, "xnote" => $xnote, "event" => $eventID, "entity" => $entityID, "type" => $type, "nomap" => $nomap, "collapse" => $custevent['collapse'] && !$tngprint], $custevent['eventdatetr']);
    }
  }
  tng_free_result($custevents);
}

function doMediaSection($entity, $medialist, $albums) {
  global $mediatypes, $cellnumber, $tableid, $datewidth;

  $media = "";
  $tableid = "media";
  $cellnumber = 0;
  foreach ($mediatypes as $mediatype) {
    $mediatypeID = $mediatype['ID'];
    $newmedia = writeMedia($medialist, $mediatypeID);
    if ($newmedia) {
      if ($media) {
        $media .= "<br>\n";
      }
      $media .= "<table class=\"table tfixed\">\n";
      $media .= "<col class=\"labelcol\"/><col style=\"width:{$datewidth}px\"/><col/>\n";
      $media .= "$newmedia\n</table>\n";
    }
  }
  $albumtext = writeAlbums($albums);
  if ($albumtext) {
    //$media .= showBreak("smallbreak");
    if ($media) {
      $media .= "<br>\n";
    }
    $media .= "<table class=\"table tfixed\">\n";
    $media .= "<col class=\"labelcol\"/><col style=\"width:{$datewidth}px\"/><col/>\n";
    $media .= "$albumtext\n</table>\n";;
  }
  return $media;
}

function getLinkTypeMisc($entity, $linktype) {
  $misc = [];
  switch ($linktype) {
    case 'I':
      $misc['personID'] = $entity['personID'];
      $misc['always'] = $entity['allow_living'] && $entity['allow_private'] ? "" : "AND alwayson = \"1\"";
      break;
    case 'F':
      $misc['personID'] = $entity['familyID'];
      $misc['always'] = $entity['allow_living'] && $entity['allow_private'] ? "" : "AND alwayson = \"1\"";
      break;
    case 'S':
      $misc['personID'] = $entity['sourceID'];
      $misc['always'] = "";
      break;
    case 'R':
      $misc['personID'] = $entity['repoID'];
      $misc['always'] = "";
      break;
    case 'L':
      $misc['personID'] = $entity;
      $misc['always'] = "";
      break;
  }

  return $misc;
}

function getAlbums($entity, $linktype) {
  global $album2entities_table;
  global $albums_table;
  global $albumlinks_table;
  global $people_table;
  global $families_table;
  global $livedefault;

  $albums = [];

  $misc = getLinkTypeMisc($entity, $linktype);
  $ID = $misc['personID'];
  $always = $misc['always'];

  $query = "SELECT $albums_table.albumID, albumname, description, eventID, alwayson
    FROM ($albums_table,$album2entities_table) 
    WHERE entityID = '$ID' AND $album2entities_table.albumID=$albums_table.albumID AND active = \"1\" 
    ORDER BY ordernum, albumname";
  $albumlinks = tng_query($query);

  while ($albumlink = tng_fetch_assoc($albumlinks)) {
    $thisalbum = [];
    $eventID = $albumlink['eventID'] && $entity['allow_living'] && $entity['allow_private'] ? $albumlink['eventID'] : "-x--general--x-";

    //check to see if we have rights to view this album
    $query = "SELECT $album2entities_table.entityID as personID, people.living as living, people.private as private, people.branch as branch, families.branch as fbranch, families.living as fliving, families.private as fprivate, familyID, people.personID as personID2
      FROM $album2entities_table
      LEFT JOIN $people_table AS people ON $album2entities_table.entityID = people.personID
      LEFT JOIN $families_table AS families ON $album2entities_table.entityID = families.familyID
      WHERE albumID = '{$albumlink['albumID']}'";
    $presult = tng_query($query);
    $foundliving = 0;
    $foundprivate = 0;
    if (!$albumlink['alwayson'] && $livedefault != 2) {
      while ($prow = tng_fetch_assoc($presult)) {
        if ($prow['fbranch'] != null) {
          $prow['branch'] = $prow['fbranch'];
        }
        if ($prow['fliving'] != null) {
          $prow['living'] = $prow['fliving'];
        }
        if ($prow['fprivate'] != null) {
          $prow['private'] = $prow['fprivate'];
        }

        $rights = determineLivingPrivateRights($prow);
        $prow['allow_living'] = $rights['living'];
        $prow['allow_private'] = $rights['private'];

        if (!$rights['living']) {
          $foundliving = 1;
        }
        if (!$rights['private']) {
          $foundprivate = 1;
        }

        if ($foundliving || $foundprivate) {
          break;
        }
      }
    }
    tng_free_result($presult);

    //putting this count in the albums table would make this faster
    $query = "SELECT count($albumlinks_table.albumlinkID) as acount FROM $albumlinks_table WHERE albumID = \"{$albumlink['albumID']}\"";
    $result2 = tng_query($query);
    $arow = tng_fetch_assoc($result2);
    tng_free_result($result2);

    if (!$foundliving && !$foundprivate) {
      $thisalbum['imgsrc'] = getAlbumPhoto($albumlink['albumID'], $albumlink['albumname']);
      $thisalbum['name'] = "<a href=\"albumsShowAlbum.php?albumID={$albumlink['albumID']}\">{$albumlink['albumname']}</a> ({$arow['acount']})";
      $thisalbum['description'] = $albumlink['description'];
    } else {
      $thisalbum['imgsrc'] = "";
      $thisalbum['name'] = uiTextSnippet('living');
      $thisalbum['description'] = "(" . uiTextSnippet('livingphoto') . ")";
    }

    if (!isset($albums[$eventID])) {
      $albums[$eventID] = [];
    }
    array_push($albums[$eventID], $thisalbum);
  }
  tng_free_result($albumlinks);

  return $albums;
}

function writeAlbums($albums_array) {
  global $tableid, $cellnumber, $datewidth;

  $albumtext = "";
  $albums = $albums_array['-x--general--x-'];

  $cellid = $tableid && !$cellnumber ? " id=\"$tableid" . "1\"" : "";

  if (is_array($albums)) {
    $totalalbums = count($albums);
    $albumcount = 0;
    $albumrows = "";

    if ($totalalbums) {
      $cellnumber++;
      $thumbcount = 0;

      foreach ($albums as $item) {
        if ($albumcount) {
          $albumrows .= "<tr>";
        }
        if ($item['imgsrc']) {
          $albumrows .= "<td style=\"width:$datewidth" . "px\">{$item['imgsrc']}</td><td>";
          $thumbcount++;
        } else {
          $albumrows .= "<td style=\"width:$datewidth" . "px\">&nbsp;</td><td>";
        }
        $albumrows .= "<span>{$item['name']}<br>" . nl2br($item['description']) . "</span></td></tr>\n";
        $albumcount++;
      }
      $albumtext .= "<tr>\n";
      $albumtext .= "<td class=\"indleftcol\"$cellid rowspan=\"$totalalbums\"><span>" . uiTextSnippet('albums') . "</span></td>\n";

      if (!$thumbcount) {
        $albumrows = str_replace("/<td style=\"width:$datewidth" . "px\">&nbsp;<\/td><td>/", "<td colspan='2'>", $albumrows);
      }
      $albumtext .= $albumrows;
    }
  }

  return $albumtext;
}

function getMedia($entity, $linktype) {
  global $medialinks_table;
  global $media_table;
  global $nonames;
  global $mediapath;
  global $mediatypes_assoc;
  global $tngconfig;
  global $rootpath;

  $media = [];
  //if mediatypeID, do it in media type sections, otherwise, do it all together
  $misc = getLinkTypeMisc($entity, $linktype);
  $personID = $misc['personID'];
  $always = $misc['always'];

  $query = "SELECT medialinkID, description, notes, altdescription, altnotes, usecollfolder, mediatypeID, personID, $medialinks_table.mediaID as mediaID, thumbpath, status, plot, eventID, alwayson, path, form, abspath, newwindow
    FROM ($medialinks_table, $media_table)
    WHERE $medialinks_table.personID=\"$personID\"
    AND $media_table.mediaID = $medialinks_table.mediaID and dontshow != 1";
  $query .= " $always  ORDER BY eventID, mediatypeID, ordernum";
  $medialinks = tng_query($query);
  $gotImageJpeg = function_exists(imageJpeg);

  while ($medialink = tng_fetch_assoc($medialinks)) {
    $imgsrc = "";
    $thismedia = [];
    $eventID = $medialink['eventID'] && $entity['allow_living'] ? $medialink['eventID'] : "-x--general--x-";
    $mediatypeID = $medialink['mediatypeID'];
    $usefolder = $medialink['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
    $medialink['allow_living'] = $medialink['alwayson'] || checkLivingLinks($medialink['mediaID']) ? 1 : 0;
    $thismedia['imgsrc'] = getSmallPhoto($medialink);
    if (!$medialink['allow_living'] && ($nonames || $tngconfig['nnpriv'])) {
      $thismedia['name'] = uiTextSnippet('livingphoto');
      $thismedia['description'] = "";
    } else {
      $thismedia['name'] = $medialink['altdescription'] ? $medialink['altdescription'] : $medialink['description'];
      $thismedia['description'] = truncateIt(getXrefNotes(($medialink['altnotes'] ? $medialink['altnotes'] : $medialink['notes'])), $tngconfig['maxnoteprev']);
      if (!$medialink['allow_living']) {
        $thismedia['description'] .= " (" . uiTextSnippet('livingphoto') . ")";
      } else {
        $thismedia['href'] = getMediaHREF($medialink, 1);
        if ($thismedia['name']) {
          $thismedia['name'] = "<a href=\"{$thismedia['href']}\">{$thismedia['name']}</a>";
        }
        if ($thismedia['imgsrc']) {
          $imgsrc = $thismedia['imgsrc'];
          $medialinkID = $medialink['medialinkID'];
          $thismedia['imgsrc'] = "<div class=\"media-img\">";
          $thismedia['imgsrc'] .= "<div class=\"media-prev\" id=\"prev{$medialink['mediaID']}_$medialinkID\" style=\"display:none\"></div>";
          $thismedia['imgsrc'] .= "</div>\n";
          $thismedia['imgsrc'] .= "<a href=\"{$thismedia['href']}\"";
          if ($gotImageJpeg && isPhoto($medialink) && checkMediaFileSize("$rootpath$usefolder/" . $medialink['path'])) {
            $thismedia['imgsrc'] .= " class=\"media-preview\" id=\"img-{$medialink['mediaID']}-{$medialinkID}-" . urlencode("$usefolder/{$medialink['path']}") . "\"";
          }
          $thismedia['imgsrc'] .= ">$imgsrc</a>";
        }
      }

      if ($medialink['plot']) {
        if ($thismedia['description']) {
          $thismedia['description'] .= "<br>";
        }
        $thismedia['description'] .= "<strong>" . uiTextSnippet('plot') . ": </strong>" . $medialink['plot'];
      }
    }
    if ($medialink['eventID'] && $entity['allow_living'] && $entity['allow_private']) {
      if (!isset($media[$eventID])) {
        $media[$eventID] = [];
      }
      array_push($media[$eventID], $thismedia);
    } else {
      if (!isset($media[$eventID][$mediatypeID])) {
        $media[$eventID][$mediatypeID] = [];
      }
      array_push($media[$eventID][$mediatypeID], $thismedia);
    }
  }
  tng_free_result($medialinks);

  return $media;
}

function writeMedia($media_array, $mediatypeID, $prefix = "") {
  global $tableid, $cellnumber, $datewidth, $mediatypes_display, $tngconfig;

  $mediatext = "";
  $media = $media_array['-x--general--x-'][$mediatypeID];

  $cellid = $tableid && !$cellnumber ? " id=\"$tableid" . "1\"" : "";

  if (is_array($media)) {
    $totalmedia = count($media);
    $mediacount = 0;
    $slidelink = "";
    $mediarows = "";
    $gotHref = false;

    if ($totalmedia) {
      $cellnumber++;
      $thumbcount = 0;

      $titlemsg = uiTextSnippet($mediatypeID) ? uiTextSnippet($mediatypeID) : $mediatypes_display[$mediatypeID];
      $hidemedia = $tngconfig['hidemedia'] && $totalmedia > 1;
      if ($hidemedia) {
        $mediacount = 1;
        $mediarows .= "<td colspan='2' id=\"drm{$mediatypeID}\">$totalmedia " . strtolower($titlemsg) . "</td></tr>\n";
        $totalmedia += 1;
      }
      foreach ($media as $item) {
        if ($item['href'] && !$gotHref) {
          $goodone = strpos($item['href'], "showmedia.php");
          if ($goodone !== false) {
            $slidelink = $item['href'];
            $gotHref = true;
          }
        }
        if ($mediacount) {
          $mediarows .= "<tr class=\"m{$prefix}{$mediatypeID}\"";
          if ($hidemedia) {
            $mediarows .= " style=\"display:none\"";
          }
          $mediarows .= ">";
        }
        if ($item['imgsrc']) {
          $mediarows .= "<td style=\"width:$datewidth" . "px\">{$item['imgsrc']}</td><td>";
          $thumbcount++;
        } else {
          $mediarows .= "<td style=\"width:$datewidth" . "px\">&nbsp;</td><td>";
        }
        $mediarows .= "<span>{$item['name']}<br>" . nl2br($item['description']) . "</span></td></tr>\n";
        $mediacount++;
      }
      if (!$tngconfig['ssdisabled'] && $mediacount >= 3 && $slidelink) {
        $titlemsg .= "<div id=\"ssm{$prefix}{$mediatypeID}\"";
        if ($hidemedia) {
          $titlemsg .= " style=\"display:none\"";
        }
        if (strpos($slidelink, "\" target=") !== false) {
          $slidelink = str_replace("\" target=", "&amp;ss=1\" target=", $slidelink);
        } else {
          $slidelink .= "&amp;ss=1";
        }
        $titlemsg .= "><br><a href=\"$slidelink\" class=\"small\">&raquo; " . uiTextSnippet('slidestart') . "</a></div>\n";
      }
      $mediatext .= "<tr>\n";
      $toggleicon = $hidemedia ? "<img src=\"img/tng_sort_desc.gif\" class=\"toggleicon\" id=\"m{$prefix}{$mediatypeID}\" title=\"" . uiTextSnippet('expand') . "\">" : "";
      $mediatext .= "<td class=\"indleftcol lm{$prefix}{$mediatypeID}\"$cellid";
      if (!$hidemedia) {
        $mediatext .= " rowspan=\"$totalmedia\"";
      }
      $mediatext .= ">$toggleicon";
      $mediatext .= "<span>$titlemsg</span></td>\n";

      if (!$thumbcount) {
        $mediarows = str_replace("<td style=\"width:$datewidth" . "px\">&nbsp;</td><td>", "<td colspan='2'>", $mediarows);
      }
      $mediatext .= $mediarows;
    }
  }

  return $mediatext;
}

function getAlbumPhoto($albumID, $albumname) {
  global $livedefault;
  global $rootpath;
  global $media_table;
  global $albumlinks_table;
  global $people_table;
  global $families_table;
  global $citations_table;
  global $medialinks_table;
  global $mediatypes_assoc;
  global $mediapath;

  $query2 = "SELECT path, thumbpath, usecollfolder, mediatypeID, $albumlinks_table.mediaID as mediaID, alwayson FROM ($media_table, $albumlinks_table)
    WHERE albumID = \"$albumID\" AND $media_table.mediaID = $albumlinks_table.mediaID AND defphoto=\"1\"";
  $result2 = tng_query($query2) or die(uiTextSnippet('cannotexecutequery') . ": $query2");
  $trow = tng_fetch_assoc($result2);
  $mediaID = $trow['mediaID'];
  $tmediatypeID = $trow['mediatypeID'];
  $tusefolder = $trow['usecollfolder'] ? $mediatypes_assoc[$tmediatypeID] : $mediapath;
  tng_free_result($result2);

  $imgsrc = "";
  if ($trow['thumbpath'] && file_exists("$rootpath$tusefolder/{$trow['thumbpath']}")) {
    $foundliving = 0;
    $foundprivate = 0;
    if (!$trow['alwayson'] && $livedefault != 2) {
      $query = "SELECT people.living as living, people.private as private, people.branch as branch, $families_table.branch as fbranch, $families_table.living as fliving, $families_table.private as fprivate, linktype
        FROM $medialinks_table
        LEFT JOIN $people_table AS people ON $medialinks_table.personID = people.personID
        LEFT JOIN $families_table ON $medialinks_table.personID = $families_table.familyID
        WHERE mediaID = '$mediaID'";
      $presult = tng_query($query);
      while ($prow = tng_fetch_assoc($presult)) {
        if ($prow['fbranch'] != null) {
          $prow['branch'] = $prow['fbranch'];
        }
        if ($prow['fliving'] != null) {
          $prow['living'] = $prow['fliving'];
        }
        if ($prow['fprivate'] != null) {
          $prow['private'] = $prow['fprivate'];
        }

        $rights = determineLivingPrivateRights($prow);
        $prow['allow_living'] == $rights['living'];
        $prow['allow_private'] == $rights['private'];

        //if living still null, must be a source
        if ($prow['living'] == null && $prow['private'] == null && $prow['linktype'] == 'I') {
          $query = "SELECT count(personID) as ccount FROM $citations_table, $people_table
              WHERE $citations_table.sourceID = '{$prow['personID']}' AND $citations_table.persfamID = $people_table.personID AND living = '1'";
          $presult2 = tng_query($query);
          $prow2 = tng_fetch_assoc($presult2);
          if ($prow2['ccount']) {
            $prow['living'] = 1;
          }
          tng_free_result($presult2);
        } elseif ($prow['living'] == null && $prow['private'] == null && $prow['linktype'] == 'F') {
          $query = "SELECT count(familyID) as ccount FROM $citations_table, $families_table
              WHERE $citations_table.sourceID = '{$prow['personID']}' AND $citations_table.persfamID = $families_table.familyID AND living = '1'";
          $presult2 = tng_query($query);
          $prow2 = tng_fetch_assoc($presult2);
          if ($prow2['ccount']) {
            $prow['living'] = 1;
          }
          tng_free_result($presult2);
        }
        if ($prow['living'] && !$rights['living']) {
          $foundliving = 1;
        }
        if ($prow['private'] && !$rights['private']) {
          $foundprivate = 1;
        }
      }
    }
    if (!$foundliving && !$foundprivate) {
      $size = getimagesize("$rootpath$tusefolder/{$trow['thumbpath']}");
      $imgsrc = "<div class=\"media-img\">";
      $imgsrc .= "<div class=\"media-prev\" id=\"prev$albumID\" style=\"display:none\"></div>";
      $imgsrc .= "</div>\n";
      $imgsrc .= "<a href=\"albumsShowAlbum.php?albumID=$albumID\" title=\"" . uiTextSnippet('albclicksee') . "\"";
      if (function_exists(imageJpeg)) {
        $imgsrc .= " class=\"media-preview\" id=\"img-{$albumID}-0-" . urlencode("$tusefolder/{$trow['path']}") . "\"";
      }
      $imgsrc .= "><img src=\"$tusefolder/" . str_replace("%2F", "/", rawurlencode($trow['thumbpath'])) . "\" class=\"thumb\" $size[3] alt=\"$albumname\"></a>";
    }
  }
  return $imgsrc;
}

function getFact($row) {
  global $address_table;

  $fact = [];
  $i = 0;
  if ($row['age']) {
    $fact[$i++] = uiTextSnippet('age') . ": " . $row['age'];
  }
  if ($row['agency']) {
    $fact[$i++] = uiTextSnippet('agency') . ": " . $row['agency'];
  }
  if ($row['cause']) {
    $fact[$i++] = uiTextSnippet('cause') . ": " . $row['cause'];
  }
  if ($row['addressID']) {
    $fact[$i] = $row['isrepo'] ? "" : uiTextSnippet('address') . ":";
    $query = "SELECT address1, address2, city, state, zip, country, www, email, phone FROM $address_table WHERE addressID = \"{$row['addressID']}\"";
    $addrresults = tng_query($query);
    $addr = tng_fetch_assoc($addrresults);
    if ($addr['address1']) {
      $fact[$i] .= ($fact[$i] ? "<br>" : "") . $addr['address1'];
    }
    if ($addr['address2']) {
      $fact[$i] .= ($fact[$i] ? "<br>" : "") . $addr['address2'];
    }
    if ($addr['city']) {
      $fact[$i] .= ($fact[$i] ? "<br>" : "") . $addr['city'];
    }
    if ($addr['state']) {
      if ($addr['city']) {
        $fact[$i] .= ", " . $addr['state'];
      } else {
        $fact[$i] .= ($fact[$i] ? "<br>" : "") . $addr['state'];
      }
    }
    if ($addr['zip']) {
      if ($addr['city'] || $addr['state']) {
        $fact[$i] .= " " . $addr['zip'];
      } else {
        $fact[$i] .= ($fact[$i] ? "<br>" : "") . $addr['zip'];
      }
    }
    if ($addr['country']) {
      $fact[$i] .= ($fact[$i] ? "<br>" : "") . $addr['country'];
    }
    if ($addr['phone']) {
      $fact[$i] .= ($fact[$i] ? "<br>" : "") . $addr['phone'];
    }
    if ($addr['email']) {
      $fact[$i] .= ($fact[$i] ? "<br>" : "") . "<a href=\"mailto:{$addr['email']}\">{$addr['email']}</a>";
    }
    if ($addr['www']) {
      $link = strpos($addr['www'], "http") !== 0 ? "http://" . $addr['www'] : $addr['www'];
      $fact[$i] .= ($fact[$i] ? "<br>" : "") . "<a href=\"$link\">{$addr['www']}</a>";
    }
  }
  return $fact;
}

function getStdExtras($persfamID) {
  global $events_table;

  $stdex = [];
  $query = "SELECT age, agency, cause, addressID, parenttag FROM $events_table WHERE persfamID = '$persfamID' AND parenttag != \"\" ORDER BY parenttag";
  $stdextras = tng_query($query);
  while ($stdextra = tng_fetch_assoc($stdextras)) {
    $stdex[$stdextra['parenttag']] = getFact($stdextra);
  }
  return $stdex;
}

function formatAssoc($assoc) {
  global $people_table;
  global $families_table;

  $assocstr = $namestr = "";

  if ($assoc['reltype'] == 'I' || $assoc['reltype'] == "") {
    $query = "SELECT firstname, lastname, lnprefix, prefix, suffix, nameorder, living, private, branch FROM $people_table WHERE personID = '{$assoc['passocID']}'";
    $result = tng_query($query);

    $row = tng_fetch_assoc($result);
    $rights = determineLivingPrivateRights($row);
    $row['allow_living'] = $rights['living'];
    $row['allow_private'] = $rights['private'];
    $assocstr = getName($row);
    tng_free_result($result);

    if (!$assocstr) {
      $assocstr = $assoc['passocID'];
    }
    $assocstr = "<a href=\"peopleShowPerson.php?personID={$assoc['passocID']}\">$assocstr</a>";
  } elseif ($assoc['reltype'] == 'F') {
    $query = "SELECT familyID, husband, wife, living, private, marrdate, gedcom, branch FROM $families_table WHERE familyID = '{$assoc['passocID']}'";
    $result = tng_query($query);

    $row = tng_fetch_assoc($result);
    $rights = determineLivingPrivateRights($row);
    $row['allow_living'] = $rights['living'];
    $row['allow_private'] = $rights['private'];
    $assocstr = getFamilyName($row);
    tng_free_result($result);

    if (!$assocstr) {
      $assocstr = $assoc['passocID'];
    }
    $assocstr = "<a href=\"familiesShowFamily.php?familyID={$assoc['passocID']}\">" . uiTextSnippet('family') . ": $assocstr</a>";
  }
  $assocstr .= $assoc['relationship'] ? " (" . uiTextSnippet('relationship2') . ": {$assoc['relationship']})" : "";

  return $assocstr;
}

function beginListItem($section) {
  global $tableid;
  global $cellnumber;
  global $firstsection;
  global $firstsectionsave;
  global $tngconfig;

  $sectext = "";
  $tableid = $section;
  $cellnumber = 0;
  if ($firstsection) {
    $firstsection = 0;
    $firstsectionsave = $section;
  }
  $sectext .= "<li id='$section' style='list-style-type: none; ";
  if ($tngconfig['istart'] && $section != 'info') {
    $sectext .= "display: none;";
  }
  $sectext .= "'>\n";

  return $sectext;
}

function endListItem($section) {
  return "</li> <!-- #$section -->\n";
}

function buildPersonListItem($index, $link, $icon, $label, $page, $thispage) {
  $out = '';
  if ($page != $thispage) {
    $out .= "<li>\n";
      $out .= "<a id=\"a$index\" href=\"$link\"><img class='icon-sm' src='{$icon}'>$label</a>\n";
    $out .= "</li>\n";
  }
  return $out;
}

function buildPersonMenu($currpage, $entityID) {
  global $disallowgedcreate;
  global $allowEdit;
  global $rightbranch;
  global $allow_ged;
  global $emailaddr;

  $nexttab = 0;
  $menu = "<div id='tngmenu'>\n";

  if ($allowEdit && $rightbranch) {
    $menu .= "<a id=\"a$nexttab\" href=\"peopleEdit.php?personID=$entityID&amp;cw=1\" title='" . uiTextSnippet('edit') . "'>\n";
      $menu .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
    $menu .= "</a>\n";
  } elseif ($emailaddr && $currpage != 'suggest') {
    $menu .= "<a id='a$nexttab' href='personSuggest.php?ID=$entityID' title='" . uiTextSnippet('suggest') . "'>\n";
      $menu .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
    $menu .= "</a>\n";
  }
  $menu .= "<ul id='tngnav'>\n";

  $menu .= buildPersonListItem($nexttab++, "peopleShowPerson.php?personID=$entityID", "svg/user.svg", uiTextSnippet('indinfo'), $currpage, "person");
  $menu .= buildPersonListItem($nexttab++, "pedigree.php?personID=$entityID", "svg/flow-split-horizontal.svg", uiTextSnippet('ancestors'), $currpage, "pedigree");
  $menu .= buildPersonListItem($nexttab++, "descend.php?personID=$entityID", "svg/flow-cascade.svg", uiTextSnippet('descendants'), $currpage, "descend");
  $menu .= buildPersonListItem($nexttab++, "relateform.php?primaryID=$entityID", "svg/users.svg", uiTextSnippet('relationship'), $currpage, "relate");
  $menu .= buildPersonListItem($nexttab++, "timeline.php?primaryID=$entityID", "svg/project.svg", uiTextSnippet('timeline'), $currpage, "timeline");

  if (!$disallowgedcreate || ($allow_ged && $rightbranch)) {
    $menu .= buildPersonListItem($nexttab++, "gedform.php?personID=$entityID", "svg/folder.svg", uiTextSnippet('extractgedcom'), $currpage, "gedcom");
  }
  $menu .= "</ul>\n";
  $menu .= "</div>\n";
  return $menu;
}

