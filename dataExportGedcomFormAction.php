<?php
ini_set('memory_limit', '200M');
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

require $subroot . 'importconfig.php';
require 'adminlog.php';
require 'prefixes.php';

$allsources = [];
$allrepos = [];
$xnotes = [];
$citations = [];
$placelist = [];

if (!$exportmedia) {
  $exportmedia = 0;
}
header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('gedexport'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    foreach ($mediatypes as $mediatype) {
      $msgID = $mediatype['ID'];
      eval("\$incl['$msgID'] = \${'incl_" . $msgID . "'};");
      if (isset($incl[$msgID])) {
        eval("\$exppath['$msgID'] = \${'exp_path_" . $msgID . "'};");
        if ($exppath[$msgID]) {
          if (strpos($exppath[$msgID], '/') !== false) {
            $expdir[$msgID] = 1;
          } elseif (strpos($exppath[$msgID], '\\') !== false) {
            $expdir[$msgID] = -1;
          } else {
            $expdir[$msgID] = 0;
          }
          //1 = do forward slashes, -1 = backslashes
          if (substr($exppath[$msgID], -1) != '/' && substr($exppath[$msgID], -1) != "\\") {
            $exppath[$msgID] .= '/';
          }
        }
      }
    }
    echo $adminHeaderSection->build('datamaint-gedexport', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'dataImportGedcom.php', uiTextSnippet('import'), 'import']);
    $navList->appendItem([true, 'dataExportGedcom.php', uiTextSnippet('export'), 'export']);
    $navList->appendItem([true, 'dataSecondaryProcesses.php', uiTextSnippet('secondarymaint'), 'second']);
    echo $navList->build('export');

    function getCitations($persfamID) {
      $citations = [];
      $citquery = "SELECT citationID, page, quay, citedate, citetext, note, sourceID, description, eventID FROM citations WHERE persfamID = '$persfamID' ORDER BY eventID";
      $citresult = tng_query($citquery) or die(uiTextSnippet('cannotexecutequery') . ": $query");

      while ($cite = tng_fetch_assoc($citresult)) {
        $eventID = $cite['eventID'] ? $cite['eventID'] : 'NAME';
        $citations[$eventID][] = ['page' => $cite['page'], 'quay' => $cite['quay'], 'citedate' => $cite['citedate'], 'citetext' => $cite['citetext'], 'note' => $cite['note'], 'sourceID' => $cite['sourceID'], 'description' => $cite['description']];
      }
      return $citations;
    }

    function writeCitation($citelist, $level) {
      global $allsources, $branch, $lineending;

      $levelplus1 = $level + 1;
      $citestr = '';

      $citecount = count($citelist);
      if ($citecount) {
        foreach ($citelist as $cite) {
          if ($cite['sourceID']) {
            if ($branch) {
              array_push($allsources, $cite['sourceID']);
            }
            $citestr .= "$level SOUR @" . trim($cite['sourceID']) . "@$lineending";
            if ($cite['citedate'] || $cite['citetext']) {
              $levelplus2 = $level + 2;
              $citestr .= "$levelplus1 DATA$lineending";
              if ($cite['citedate']) {
                $citestr .= "$levelplus2 DATE {$cite['citedate']}$lineending";
              }
              if ($cite['citetext']) {
                $citestr .= doNote($levelplus2, 'TEXT', $cite['citetext']);
              }
            }
          } else {
            $citestr = "$level SOUR {$cite['description']}$lineending";
            if ($cite['citetext']) {
              $citestr .= doNote($levelplus1, 'TEXT', $cite['citetext']);
            }
          }
          if ($cite['page']) {
            $citestr .= doNote($levelplus1, 'PAGE', $cite['page']);
          }
          if ($cite['quay'] && $cite['quay'] != '0') {
            $citestr .= "$levelplus1 QUAY {$cite['quay']}$lineending";
          }
          if ($cite['note']) {
            $citestr .= doNote($levelplus1, 'NOTE', $cite['note']);
          }
        }
      }
      return $citestr;
    }

    function getFact($row, $level) {
      global $lineending;

      $fact = '';
      if ($row['age']) {
        $fact .= "$level AGE {$row['age']}$lineending";
      }
      if ($row['agency']) {
        $fact .= "$level AGNC {$row['agency']}$lineending";
      }
      if ($row['cause']) {
        $fact .= "$level CAUS {$row['cause']}$lineending";
      }
      if ($row['addressID']) {
        $query = "SELECT address1, address2, city, state, zip, country, phone, email, www FROM addresses WHERE addressID = \"{$row['addressID']}\"";
        $addrresults = tng_query($query);
        $addr = tng_fetch_assoc($addrresults);
        if ($row['tag'] != 'ADDR') {
          $fact .= "$level ADDR$lineending";
          $level++;
        }
        if ($addr['address1']) {
          $fact .= "$level ADR1 {$addr['address1']}$lineending";
        }
        if ($addr['address2']) {
          $fact .= "$level ADR2 {$addr['address2']}$lineending";
        }
        if ($addr['city']) {
          $fact .= "$level CITY {$addr['city']}$lineending";
        }
        if ($addr['state']) {
          $fact .= "$level STAE {$addr['state']}$lineending";
        }
        if ($addr['zip']) {
          $fact .= "$level POST {$addr['zip']}$lineending";
        }
        if ($addr['country']) {
          $fact .= "$level CTRY {$addr['country']}$lineending";
        }
        if ($addr['phone']) {
          $fact .= "$level PHON {$addr['phone']}$lineending";
        }
        if ($addr['email']) {
          $fact .= "$level EMAIL {$addr['email']}$lineending";
        }
        if ($addr['www']) {
          $fact .= "$level WWW {$addr['www']}$lineending";
        }
      }
      return $fact;
    }

    function getStdExtras($persfamID, $level) {
      $stdex = [];
      $query = "SELECT age, agency, cause, addressID, parenttag FROM events WHERE persfamID = '$persfamID' AND parenttag != \"\" ORDER BY parenttag";
      $stdextras = tng_query($query);
      while ($stdextra = tng_fetch_assoc($stdextras)) {
        $stdex[$stdextra['parenttag']] = getFact($stdextra, $level);
      }
      return $stdex;
    }

    function doEvent($custevent, $level) {
      global $lineending;

      $infolen = strlen($custevent['info']);
      if ($custevent['tag'] != 'EVEN' || $infolen < 150) {
        $info = doNote($level, $custevent['tag'], $custevent['info']);
      } else {
        $info = "$level " . $custevent['tag'] . $lineending;
      }
      $nextlevel = $level + 1;
      if ($custevent['description']) {
        $info .= "2 TYPE {$custevent['description']}$lineending";
      }
      if ($custevent['eventdate']) {
        $info .= "2 DATE {$custevent['eventdate']}$lineending";
      }
      if ($custevent['eventplace']) {
        $info .= writePlace($custevent['eventplace'], 2);
      }
      if ($custevent['tag'] == 'EVEN' && $infolen >= 150) {
        $info .= doNote($nextlevel, 'NOTE', $custevent['info']);
      }

      return $info;
    }

    function getNotes($id) {
      global $xnotes;

      $query = "SELECT notelinks.ID AS ID, secret, xnotes.note AS note, xnotes.noteID AS noteID, notelinks.eventID FROM notelinks LEFT JOIN xnotes ON notelinks.xnoteID = xnotes.ID LEFT JOIN events ON notelinks.eventID = events.eventID LEFT JOIN eventtypes ON eventtypes.eventtypeID = events.eventtypeID WHERE notelinks.persfamID = '$id' ORDER BY eventdatetr, eventtypes.ordernum, tag, notelinks.ordernum, ID";
      $notelinks = tng_query($query);
      $notearray = [];
      while ($notelink = tng_fetch_assoc($notelinks)) {
        $eventid = $notelink['eventID'] ? $notelink['eventID'] : '-x--general--x-';
        $newnote = $notelink['noteID'] ? "@{$notelink['noteID']}@" : $notelink['note'];
        if (!is_array($notearray[$eventid])) {
          $notearray[$eventid] = [];
        }
        $innerarray = [];
        $innerarray['text'] = $newnote;
        $innerarray['id'] = 'N' . $notelink['ID'];
        $innerarray['private'] = $notelink['secret'];
        array_push($notearray[$eventid], $innerarray);

        if ($notelink['noteID'] && !in_array($notelink['noteID'], $xnotes)) {
          array_push($xnotes, $notelink['noteID']);
        }
      }
      tng_free_result($notelinks);

      return $notearray;
    }

    function getNoteLine($level, $label, $note, $delta) {
      global $lineending, $session_charset;

      $noteconc = '';
      $notelen = strlen($note);
      if ($notelen > 245) {
        $orgnote = trim($note);
        $offset = 245;
        if ($session_charset == 'UTF-8' && function_exists(mb_substr)) {
          while (mb_substr($orgnote, $offset, 1, 'UTF-8') == ' ' || mb_substr($orgnote, $offset - 1, 1, 'UTF-8') == ' ') {
            $offset--;
          }
          $note = mb_substr($note, 0, $offset, 'UTF-8');
        } else {
          while (substr($orgnote, $offset, 1) == ' ' || substr($orgnote, $offset - 1, 1) == ' ') {
            $offset--;
          }
          $note = substr($note, 0, $offset);
        }
        $newlevel = $level + $delta;
        while ($offset < $notelen) {
          $endnext = 245;
          if ($session_charset == 'UTF-8' && function_exists(mb_substr)) {
            while (mb_substr($orgnote, $offset + $endnext, 1, 'UTF-8') == ' ' || mb_substr($orgnote, $offset + $endnext - 1, 1, 'UTF-8') == ' ') {
              $offset--;
            }
            $nextpart = trim(mb_substr($orgnote, $offset, $endnext, 'UTF-8'), $lineending);
          } else {
            while (substr($orgnote, $offset + $endnext, 1) == ' ' || substr($orgnote, $offset + $endnext - 1, 1) == ' ') {
              $endnext--;
            }
            $nextpart = trim(substr($orgnote, $offset, $endnext), $lineending);
          }
          $noteconc .= trim("$newlevel CONC $nextpart") . $lineending;
          $offset += $endnext;
        }
      }

      return trim("$level $label $note") . "$lineending$noteconc";
    }

    function doNote($level, $label, $notetxt, $private = '') {
      global $savestate;
      global $saveimport;
      global $saveimport_table;

      $noteinfo = '';
      $notetxt = str_replace("\r", '', $notetxt);
      if (!preg_match('/^@.+@$/', $notetxt)) {
        $notetxt = str_replace('@', '@@', $notetxt);
      }
      $notes = preg_split('/\r\n|\n/', $notetxt);

      if ($level) {
        $note = array_shift($notes);
        $noteinfo .= getNoteLine($level, $label, $note, 1);
      }
      $level++;
      foreach ($notes as $note) {

        $noteinfo .= getNoteLine($level, 'CONT', $note, 0);
      }
      if ($private) {
        $noteinfo .= getNoteLine($level, '_PRIVATE', 'Y', 0);
      }

      $savestate['ncount']++;
      if ($savestate['ncount'] % 10 == 0) {
        if ($saveimport) {
          $query = "UPDATE $saveimport_table SET ncount=\"{$savestate['ncount']}\"";
          $saveresult = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
        }
        echo "<strong>N{$savestate['ncount']}</strong> ";
      }

      return $noteinfo;
    }

    function writeNote($level, $label, $notes) {
      global $citations;

      $noteinfo = '';
      if (is_array($notes)) {
        foreach ($notes as $notearray) {
          $noteinfo .= doNote($level, $label, $notearray['text'], $notearray['private']);
          $id = $notearray['id'];
          $noteinfo .= writeCitation($citations[$id], $level + 1);
        }
      } elseif ($notes) {
        $noteinfo .= doNote($level, $label, $notes);
      }
      return $noteinfo;
    }

    function doXNotes() {
      global $savestate;
      global $noteprefix;
      global $xnotes;
      global $exliving;
      global $exprivate;

      $xnotestr = '';

      //if excluding private or living
      //join xnotes and notelinks
      if ($branch || $exliving || $exprivate) {
        if ($xnotes) {
          foreach ($xnotes as $xnote) {
            $query = "SELECT note, noteID FROM xnotes WHERE noteID = '$xnote' ORDER BY noteID";
            $xnotearray = tng_query($query);
            $xnotetxt = tng_fetch_assoc($xnotearray);
            $xnotestr .= writeXNote($xnotetxt);
            tng_free_result($xnotearray);
          }
        }
      } else {
        $prefixlen = strlen($noteprefix) + 1;

        $query = "SELECT note, noteID, (0+SUBSTRING(noteID,$prefixlen)) AS num FROM xnotes WHERE noteID != \"\" {$savestate['wherestr']} ORDER BY num";
        $xnotearray = tng_query($query);
        while ($xnotetxt = tng_fetch_assoc($xnotearray)) {
          $xnotestr .= writeXNote($xnotetxt);
        }
        tng_free_result($xnotearray);
      }

      return $xnotestr;
    }

    function writeXNote($xnotetxt) {
      global $savestate;
      global $lineending;
      global $citations;
      global $fp;
      global $saveimport;
      global $saveimport_table;

      $xnotestr = '';

      $xnotestr .= "0 @{$xnotetxt['noteID']}@ NOTE$lineending";

      $xnotestr .= doNote(0, 'NOTE', $xnotetxt['note']);
      $citations = getCitations($xnotetxt['noteID']);
      $xnotestr .= writeCitation($citations['NAME'], $level + 2);

      if ($saveimport) {
        $savestate['offset'] = ftell($fp);
        $query = "UPDATE $saveimport_table SET offset={$savestate['offset']}, lasttype={$savestate['lasttype']}, lastid=\"{$xnotetxt['noteID']}\"";
        $saveresult = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
      }

      return $xnotestr;
    }

    function getMediaLinks($id) {
      global $savestate;
      global $expdir;
      global $exppath;
      global $incl;

      $allmedia = [];
      if ($savestate['media']) {
        $query = "SELECT notes, altnotes, description, altdescription, path, mediatypeID, eventID, form, abspath, defphoto FROM (media, medialinks) WHERE medialinks.personID=\"" . addslashes($id) . "\" AND media.mediaID = medialinks.mediaID ORDER BY eventID, ordernum";
        $media = tng_query($query);

        while ($prow = tng_fetch_assoc($media)) {
          $mediatypeID = $prow['mediatypeID'];
          if (isset($incl[$mediatypeID])) {
            $eventID = $prow['eventID'] ? $prow['eventID'] : '-x--general--x-';
            if ($prow['abspath']) {
              preg_match('/\.(.+)$/', $prow['path'], $matches);
              $prow['form'] = $matches[1];
            } else {
              $thisexppath = $exppath[$mediatypeID];
              $thisexpdir = $expdir[$mediatypeID];

              if (!$prow['form']) {
                preg_match('/\.(.+)$/', $prow['path'], $matches);
                $prow['form'] = $matches[1];
              }
              if (!$thisexpdir && strpos($prow['path'], '/')) {
                $thisexpdir = 1;
              }
              $prow['path'] = $thisexpdir == 1 ? str_replace("\\", '/', $thisexppath . $prow['path']) : str_replace('/', "\\", $thisexppath . $prow['path']);
            }
            $prow['title'] = $prow['altdescription'] ? $prow['altdescription'] : $prow['description'];

            $prow['notes'] = $prow['altnotes'] ? $prow['altnotes'] : $prow['notes'];
            if (!is_array($allmedia[$eventID])) {
              $allmedia[$eventID] = [];
            }
            array_push($allmedia[$eventID], $prow);
          }
        }
        tng_free_result($media);
      }

      return $allmedia;
    }

    function writeMediaLinks($media_array, $level) {
      global $lineending, $savestate, $mediatypeObjs;

      $linktxt = '';
      $newlevel = $level + 1;
      foreach ($media_array as $media) {
        if ($media['form']) {
          $mediatitle = preg_replace('/\n/', ' ', $media['title']);
          $mediatitle = preg_replace('/\r/', ' ', $mediatitle);
          $linktxt .= "$level OBJE$lineending";
          $linktxt .= "$newlevel FORM {$media['form']}$lineending";
          $linktxt .= "$newlevel FILE {$media['path']}$lineending";
          $linktxt .= "$newlevel TITL $mediatitle$lineending";
          if ($media['defphoto']) {
            $linktxt .= "$newlevel _PRIM Y$lineending";
          }
          $type = $media['mediatypeID'];
          $exportas = $mediatypeObjs[$type]['exportas'];
          if (!$exportas) {
            $exportas = strtoupper($media['mediatypeID']);
            if (substr($exportas, -1) == 'S') {
              $exportas = substr($exportas, 0, -1);
            }
            if ($exportas == 'HISTORIE') {
              $exportas = 'HISTORY';
            }
          }
          $linktxt .= "$newlevel _TYPE $exportas$lineending";
          $linktxt .= writeNote($newlevel, 'NOTE', $media['notes']);
          $savestate['mcount']++;
          if ($savestate['mcount'] % 10 == 0) {
            echo "<strong>M{$savestate['mcount']}</strong> ";
          }
        }
      }
      return $linktxt;
    }

    function appendParents($child) {
      global $lineending;

      $info = "1 FAMC @{$child['familyID']}@$lineending";
      $crights = determineLivingPrivateRights($child);
      $child['allow_living'] = $crights['living'];
      $child['allow_private'] = $crights['private'];
      if ($cright['both']) {
        //if( $child['relationship'] ) $info .= "2 PEDI {$child['relationship']}$lineending";
        if ($crights['lds']) {
          if ($child['sealdate'] || $child['sealplace']) {
            $childnotes = getNotes($child['personID']);
            $citations = getCitations($child['personID'] . $child['familyID']);

            $info .= "1 SLGC$lineending";
            $info .= "2 FAMC @{$child['familyID']}@$lineending";
            if ($child['sealdate']) {
              $info .= "2 DATE {$child['sealdate']}$lineending";
            }
            if ($child['sealplace']) {
              $tok = strtok($child['sealplace'], ' ');
              if (strlen($tok) == 5) {
                $info .= "2 TEMP $tok$lineending";
                $tok = strtok(' ');
                if ($tok) {
                  $info .= writePlace($tok, 2);
                }
              } else {
                $info .= writePlace($child['sealplace'], 2);
              }
            }
            if ($childnotes['SLGC']) {
              $info .= writeNote(2, 'NOTE', $childnotes['SLGC']);
            }
            if ($citations['SLGC']) {
              $info .= writeCitation($citations['SLGC'], 2);
            }
          }
        }
      }

      return $info;
    }

    function getEligibility($ind) {
      $rval = 0;

      $birthdate = $ind['birthdatetr'] != '0000-00-00' ? $ind['birthdatetr'] : $ind['altbirthdatetr'];
      $birthplace = $ind['birthplace'] ? $ind['birthplace'] : $ind['altbirthplace'];
      if ($birthplace && $birthdate > '1500-00-00') {
        $deathdate = $ind['deathdatetr'] != '0000-00-00' ? $ind['deathdatetr'] : $ind['burialdatetr'];
        if ($deathdate != '0000-00-00') {
          $deathinfo = split('-', $deathdate);
          $deathyeardiff = date('Y') - $deathinfo[0];
          if ($deathyeardiff > 1 || ($deathyeardiff && (date('m') > $deathinfo[1] || (date('m') == $deathinfo[1] && date('d') > $deathinfo[2])))) {
            $rval = 1;
          }
        } else {
          $birthinfo = split('-', $birthdate);
          $birthyeardiff = date('Y') - $birthinfo[0];
          if ($birthyeardiff > 110 || ($birthyeardiff == 110 && (date('m') > $birthinfo[1] || (date('m') == $birthinfo[1] && date('d') > $birthinfo[2])))) {
            $rval = 1;
          }
        }
      }

      return $rval;
    }

    function doNotesAndMedia($notes, $media, $tag, $level) {
      $rval = '';
      if ($notes[$tag]) {
        $rval .= writeNote($level, 'NOTE', $notes[$tag]);
      }
      if ($media[$tag]) {
        $rval .= writeMediaLinks($media[$tag], $level);
      }

      return $rval;
    }

    function doAssociations($entityID) {
      global $lineending;

      $assocstr = '';
      $query = "SELECT passocID, relationship FROM associations WHERE personID = '$entityID'";
      $assocresult = tng_query($query);
      while ($assoc = tng_fetch_assoc($assocresult)) {
        $assocstr .= "1 ASSO @{$assoc['passocID']}@$lineending";
        if ($assoc['relationship']) {
          $assocstr .= "2 RELA {$assoc['relationship']}$lineending";
        }
      }

      return $assocstr;
    }

    function writeIndividual($ind) {
      global $people_table;
      global $lnprefixes;
      global $children_table;
      global $families_table;
      global $citations;
      global $templeready;
      global $savestate;
      global $fp;
      global $lineending;
      global $exprivatestr;
      global $exlivingstr;

      $rights = determineLivingPrivateRights($ind);
      $ind['allow_living'] = $rights['living'];
      $ind['allow_private'] = $rights['private'];
      $rights['both'] = $ind['allow_private'] && $ind['allow_living'];
      $doit = !$templeready;

      if (!$doit && ((!$ind['baptdate'] && !$ind['baptplace']) || (!$ind['confdate'] && !$ind['confplace']) || (!$ind['initdate'] && !$ind['initplace']) || (!$ind['endldate'] && !$ind['endlplace']))) {
        $doit = 1;
      }

      //check eligibility
      if ($doit && $templeready) {
        $doit = getEligibility($ind);
      }
      $spousedata = '';
      if ($ind['sex'] == 'M') {
        $orderfield = ', wifeorder';
        $orderby = ' ORDER BY wifeorder';
      } elseif ($ind['sex'] == 'F') {
        $orderfield = ', husborder';
        $orderby = ' ORDER BY husborder';
      } else {
        $orderfield = $orderby = '';
      }
      $query = "(SELECT familyID, changedate, sealdate, sealplace, marrplace, marrdatetr$orderfield FROM $families_table WHERE husband = \"{$ind['personID']}\" $exlivingstr $exprivatestr) UNION (SELECT familyID, changedate, sealdate, sealplace, marrplace, marrdatetr$orderfield FROM $families_table WHERE wife = \"{$ind['personID']}\" $exlivingstr $exprivatestr)$orderby";

      $result2 = tng_query($query);
      while ($spouse = tng_fetch_assoc($result2)) {
        $spousedata .= "1 FAMS @{$spouse['familyID']}@$lineending";
        if (!$doit && !$spouse['sealdate'] && !$spouse['sealplace'] && $spouse['marrplace'] && $spouse['marrdatetr'] > '1500-00-00') {
          $doit = 1;
        }
        //if $doit still false, loop through children to see if sealing needs to be done for any of them
        if (!$doit) {
          $query = "SELECT personID, sealdate, sealplace FROM $children_table WHERE familyID = \"{$spouse['familyID']}\"";
          $children = tng_query($query);
          if ($children) {
            while (!$doit && $child = tng_fetch_assoc($children)) {
              if (!$child['sealdate'] && !$child['sealplace']) {
                //make sure child is eligible
                $query = "SELECT birthdate, birthdatetr, birthplace, altbirthdate, altbirthdatetr, altbirthplace, deathdatetr, burialdatetr FROM $people_table WHERE personID = \"{$child['personID']}\" $exlivingstr $exprivatestr";
                $childresult = tng_query($query);
                $childind = tng_fetch_assoc($childresult);
                $doit = getEligibility($childind);
                tng_free_result($childresult);
              }
            }
            tng_free_result($children);
          }
        }
      }
      tng_free_result($result2);

      $childdata = '';
      $query = "SELECT * FROM $children_table WHERE personID = \"{$ind['personID']}\" ORDER BY parentorder";
      $children = tng_query($query);
      if ($children) {
        while ($child = tng_fetch_assoc($children)) {
          $childdata .= appendParents($child);
          if (!$doit && !$child['sealdate'] && !$child['sealplace']) {
            $doit = 1;
          }
        }
        tng_free_result($children);
      }

      if ($doit) {
        if ($rights['both']) {
          $indnotes = getNotes($ind['personID']);
          $indmedia = getMediaLinks($ind['personID']);
        } else {
          $indnotes = [];
          $indmedia = [];
        }

        $citations = getCitations($ind['personID']);
        $extras = getStdExtras($ind['personID'], 2);

        $info = "0 @{$ind['personID']}@ INDI$lineending";
        $nonamesloc = showNames($ind);
        if ($rights['both'] || !$nonamesloc) {
          $info .= "1 NAME {$ind['firstname']} /" . trim($ind['lnprefix'] . ' ' . $ind['lastname']) . '/';
          $info .= $ind['suffix'] ? " {$ind['suffix']}$lineending" : $lineending;
          if ($ind['firstname']) {
            $info .= "2 GIVN {$ind['firstname']}$lineending";
          }
          if ($lnprefixes && $ind['lnprefix']) {
            $info .= "2 SPFX {$ind['lnprefix']}$lineending";
          }
          if ($ind['lastname']) {
            $info .= "2 SURN {$ind['lastname']}$lineending";
          }

          $info .= doNotesAndMedia($indnotes, $indmedia, 'NAME', 2);
          //if( $indnotes['NAME'] )
          //  $info .= writeNote( 2, 'NOTE', $indnotes['NAME'] );
          if ($ind['prefix']) {
            $info .= "2 NPFX {$ind['prefix']}$lineending";
            if ($indnotes['NPFX']) {
              $info .= writeNote(3, 'NOTE', $indnotes['NPFX']);
            }
          }
          if ($ind['suffix']) {
            $info .= "2 NSFX {$ind['suffix']}$lineending";
            if ($indnotes['NSFX']) {
              $info .= writeNote(3, 'NOTE', $indnotes['NSFX']);
            }
          }
          if ($ind['nickname']) {
            $info .= "2 NICK {$ind['nickname']}$lineending";
            if ($indnotes['NICK']) {
              $info .= writeNote(3, 'NOTE', $indnotes['NICK']);
            }
          }
          if ($citations['NAME']) {
            $info .= writeCitation($citations['NAME'], 2);
          }
          if ($ind['title']) {
            $info .= "1 TITL {$ind['title']}$lineending";
            if ($indnotes['TITL']) {
              $info .= writeNote(2, 'NOTE', $indnotes['TITL']);
            }
          }
          $info .= "1 SEX {$ind['sex']}$lineending";
          if ($ind['living']) {
            $info .= "1 _LIVING Y$lineending";
          }
          if ($ind['private']) {
            $info .= "1 _PRIVATE Y$lineending";
          }
          if ($citations['-x--general--x-']) {
            $info .= writeCitation($citations['-x--general--x-'], 1);
          }
        } elseif ($nonamesloc == 2) {
          $info .= '1 NAME ' . initials($ind['firstname']) . ' /' . trim($ind['lnprefix'] . ' ' . $ind['lastname']) . "/$lineending";
        } else {
          $info .= '1 NAME ' . uiTextSnippet('living') . " //$lineending";
        }

        if ($rights['both']) {
          if ($ind['birthdate'] || $ind['birthplace'] || $indnotes['BIRT'] || $citations['BIRT'] || $extras['BIRT']) {
            if ($ind['birthdate'] == 'Y' || (!$ind['birthdate'] && !$ind['birthplace'])) {
              $info .= "1 BIRT Y$lineending";
            } else {
              $info .= "1 BIRT$lineending";
              if ($ind['birthdate']) {
                $info .= "2 DATE {$ind['birthdate']}$lineending";
              }
              if ($ind['birthplace']) {
                $info .= writePlace($ind['birthplace'], 2);
              }
            }
            $info .= doNotesAndMedia($indnotes, $indmedia, 'BIRT', 2);
            if ($citations['BIRT']) {
              $info .= writeCitation($citations['BIRT'], 2);
            }
            $info .= $extras['BIRT'];
          }
          if ($ind['altbirthdate'] || $ind['altbirthplace'] || $indnotes['CHR'] || $citations['CHR'] || $extras['CHR']) {
            if ($ind['altbirthdate'] == 'Y' || (!$ind['altbirthdate'] && !$ind['altbirthplace'])) {
              $info .= "1 CHR Y$lineending";
            } else {
              $info .= "1 CHR$lineending";
              if ($ind['altbirthdate']) {
                $info .= "2 DATE {$ind['altbirthdate']}$lineending";
              }
              if ($ind['altbirthplace']) {
                $info .= writePlace($ind['altbirthplace'], 2);
              }
            }
            $info .= doNotesAndMedia($indnotes, $indmedia, 'CHR', 2);
            if ($citations['CHR']) {
              $info .= writeCitation($citations['CHR'], 2);
            }
            $info .= $extras['CHR'];
          }
        }
        if ($ind['deathdate'] || $ind['deathplace'] || $indnotes['DEAT'] || $citations['DEAT'] || $extras['DEAT']) {
          if ($ind['deathdate'] == 'Y' || (!$ind['deathdate'] && !$ind['deathplace'])) {
            $info .= "1 DEAT Y$lineending";
          } else {
            $info .= "1 DEAT$lineending";
            if ($ind['deathdate']) {
              $info .= "2 DATE {$ind['deathdate']}$lineending";
            }
            if ($ind['deathplace']) {
              $info .= writePlace($ind['deathplace'], 2);
            }
          }
          $info .= doNotesAndMedia($indnotes, $indmedia, 'DEAT', 2);
          if ($citations['DEAT']) {
            $info .= writeCitation($citations['DEAT'], 2);
          }
          $info .= $extras['DEAT'];
        }
        if ($ind['burialdate'] || $ind['burialplace'] || $indnotes['BURI'] || $citations['BURI'] || $extras['BURI']) {
          $btag = $ind['burialtype'] ? 'CREM' : 'BURI';
          if ($ind['burialdate'] == 'Y' || (!$ind['burialdate'] && !$ind['burialplace'])) {
            $info .= "1 $btag Y$lineending";
          } else {
            $info .= "1 $btag$lineending";
            if ($ind['burialdate']) {
              $info .= "2 DATE {$ind['burialdate']}$lineending";
            }
            if ($ind['burialplace']) {
              $info .= writePlace($ind['burialplace'], 2);
            }
          }
          $info .= doNotesAndMedia($indnotes, $indmedia, 'BURI', 2);
          if ($citations['BURI']) {
            $info .= writeCitation($citations['BURI'], 2);
          }
          $info .= $extras['BURI'];
        }
        $info .= $parentdata;

        if ($rights['both']) {
          $query = "SELECT tag, description, eventdate, eventplace, age, agency, cause, addressID, info, eventID FROM events, eventtypes WHERE persfamID = \"{$ind['personID']}\" AND events.eventtypeID = eventtypes.eventtypeID AND parenttag = \"\" AND keep = \"1\" ORDER BY eventdate, ordernum, tag";
          $custevents = tng_query($query);
          while ($custevent = tng_fetch_assoc($custevents)) {
            $info .= doEvent($custevent, 1);
            $eventID = $custevent['eventID'];
            $info .= doNotesAndMedia($indnotes, $indmedia, $eventID, 2);
            if ($citations[$eventID]) {
              $info .= writeCitation($citations[$eventID], 2);
            }
            $info .= getFact($custevent, 2);
          }

          if ($rights['lds']) {
            $info .= doLDSEvent('BAPL', 'bapt', $indnotes, $indmedia, $citations['BAPL'], $extras['BAPL'], $ind);
            $info .= doLDSEvent('CONL', 'conf', $indnotes, $indmedia, $citations['CONL'], $extras['CONL'], $ind);
            $info .= doLDSEvent('INIT', 'init', $indnotes, $indmedia, $citations['INIT'], $extras['INIT'], $ind);
            $info .= doLDSEvent('ENDL', 'endl', $indnotes, $indmedia, $citations['ENDL'], $extras['ENDL'], $ind);
          }
          $info .= doAssociations($ind['personID']);

          if ($indnotes['-x--general--x-']) {
            $info .= writeNote(1, 'NOTE', $indnotes['-x--general--x-']);
          }

          if ($indmedia['-x--general--x-']) {
            $info .= writeMediaLinks($indmedia['-x--general--x-'], 1);
          }
        }
        $info .= $childdata;
        $info .= $spousedata;

        if ($ind['changedate']) {
          $info .= "1 CHAN$lineending";
          $info .= "2 DATE {$ind['changedate']}$lineending";
        }

        fwrite($fp, $info);
        $savestate['icount']++;
        if ($savestate['icount'] % 10 == 0) {
          echo "<strong>I{$savestate['icount']}</strong> ";
        }
      }
      return $info;
    }

    function doLDSEvent($tag, $key, $notes, $media, $citations, $extras, $row) {
      global $lineending;

      $event = '';
      $prefix = $tag == 'INIT' ? '_' : '';
      if ($ind[$key . 'date'] || $ind[$key . 'place']) {
        $event .= "1 $prefix$tag$lineending";
        if ($row[$key . 'date']) {
          $event .= "2 DATE {$row[$key . 'date']}$lineending";
        }
        if ($row[$key . 'place']) {
          $tok = strtok($row[$key . 'place'], ' ');
          if (strlen($tok) == 5) {
            $event .= "2 TEMP $tok$lineending";
            $tok = strtok(' ');
            if ($tok) {
              $event .= writePlace($tok, 2);
            }
          } else {
            $event .= writePlace($row[$key . 'place'], 2);
          }
        }
        $event .= doNotesAndMedia($notes, $media, $tag, 2);
        if ($citations) {
          $event .= writeCitation($citations, 2);
        }
        $event .= $extras;
      }
      return $event;
    }

    function writeFamily($family) {
      global $citations;
      global $savestate;
      global $children_table;
      global $people_table;
      global $templeready;
      global $fp;
      global $lineending;

      $familyID = $family['familyID'];
      $doit = !$templeready;

      if (!$doit && !$family['sealdate'] && !$family['sealplace'] && $family['marrplace'] && $family['marrdatetr'] > '1500-00-00') {
        $doit = 1;
      }

      $childdata = '';
      $query = "SELECT personID, sealdate, sealplace, mrel, frel FROM $children_table WHERE familyID = \"$familyID\" AND personID != \"\" ORDER BY ordernum";
      $result = tng_query($query);
      if ($result) {
        while ($child = tng_fetch_assoc($result)) {
          $childdata .= "1 CHIL @{$child['personID']}@$lineending";
          if ($child['frel']) {
            $childdata .= "2 _FREL {$child['frel']}$lineending";
          }
          if ($child['mrel']) {
            $childdata .= "2 _MREL {$child['mrel']}$lineending";
          }
          if (!$doit && !$child['sealdate'] && !$child['sealplace']) {
            $doit = 1;
          }
        }
      }
      tng_free_result($result);

      if ($doit) {
        $info = "0 @$familyID@ FAM$lineending";
        if ($family['status']) {
          $info .= "1 _STAT {$family['status']}$lineending";
        }
        if ($family['husband']) {
          $info .= "1 HUSB @{$family['husband']}@$lineending";
        }
        if ($family['wife']) {
          $info .= "1 WIFE @{$family['wife']}@$lineending";
        }

        //look up family's rights

        $frights = determineLivingPrivateRights($family);
        $family['allow_living'] = $frights['living'];
        $family['allow_private'] = $frights['private'];
        if ($frights['both']) {
          //look up husband and wife
          $query = "SELECT personID, living, private, branch FROM $people_table WHERE personID = '{$family['husband']}'";
          $result2 = tng_query($query);
          $hrow = tng_fetch_assoc($result2);
          $frights = determineLivingPrivateRights($hrow);
          if ($frights['both']) {
            $query = "SELECT personID, living, private, branch FROM $people_table WHERE personID = '{$family['wife']}'";
            $result2 = tng_query($query);
            $wrow = tng_fetch_assoc($result2);
            $frights = determineLivingPrivateRights($wrow);
          }
          tng_free_result($result2);
        }
        if ($frights['both']) {
          $famnotes = getNotes($familyID);
          $citations = getCitations($familyID);
          $fammedia = getMediaLinks($familyID);
          $extras = getStdExtras($familyID, 2);
          if ($family['marrdate'] || $family['marrplace'] || $famnotes['MARR'] || $citations['MARR'] || $extras['MARR'] || $family['marrtype']) {
            if ($family['marrdate'] == 'Y' || (!$family['marrdate'] && !$family['marrplace'])) {
              $info .= "1 MARR Y$lineending";
            } else {
              $info .= "1 MARR$lineending";
              if ($family['marrdate']) {
                $info .= "2 DATE {$family['marrdate']}$lineending";
              }
              if ($family['marrplace']) {
                $info .= writePlace($family['marrplace'], 2);
              }
            }
            if ($family['marrtype']) {
              $info .= '2 TYPE ' . $family['marrtype'] . $lineending;
            }
            $info .= doNotesAndMedia($famnotes, $fammedia, 'MARR', 2);
            if ($citations['MARR']) {
              $info .= writeCitation($citations['MARR'], 2);
            }
            $info .= $extras['MARR'];
          }
          if ($family['divdate'] || $family['divplace'] || $famnotes['DIV'] || $citations['DIV'] || $extras['DIV']) {
            if ($family['divdate'] == 'Y' || (!$family['divdate'] && !$family['divplace'])) {
              $info .= "1 DIV Y$lineending";
            } else {
              $info .= "1 DIV$lineending";
              if ($family['divdate']) {
                $info .= "2 DATE {$family['divdate']}$lineending";
              }
              if ($family['divplace']) {
                $info .= writePlace($family['divplace'], 2);
              }
            }
            $info .= doNotesAndMedia($famnotes, $fammedia, 'DIV', 2);
            if ($citations['DIV']) {
              $info .= writeCitation($citations['DIV'], 2);
            }
            $info .= $extras['DIV'];
          }

          $query = "SELECT tag, description, eventdate, eventplace, age, agency, cause, addressID, info, eventID FROM events, eventtypes WHERE persfamID = \"$familyID\" AND events.eventtypeID = eventtypes.eventtypeID AND parenttag = \"\" AND keep = \"1\" ORDER BY eventdate, ordernum, tag";
          $custevents = tng_query($query);
          while ($custevent = tng_fetch_assoc($custevents)) {
            $info .= doEvent($custevent, 1);
            $eventID = $custevent['eventID'];
            $info .= doNotesAndMedia($famnotes, $fammedia, $eventID, 2);
            if ($citations[$eventID]) {
              $info .= writeCitation($citations[$eventID], 2);
            }
            $info .= getFact($custevent, 2);
          }

          if ($frights['lds']) {
            if ($family['sealdate'] || $family['sealplace']) {
              $info .= "1 SLGS$lineending";
              if ($family['sealdate']) {
                $info .= "2 DATE {$family['sealdate']}$lineending";
              }
              if ($family['sealplace']) {
                $tok = strtok($family['sealplace'], ' ');
                if (strlen($tok) == 5) {
                  $info .= "2 TEMP $tok$lineending";
                  $tok = strtok(' ');
                  if ($tok) {
                    $info .= writePlace($tok, 2);
                  }
                } else {
                  $info .= writePlace($family['sealplace'], 2);
                }
              }
              $info .= doNotesAndMedia($famnotes, $fammedia, 'SLGS', 2);
              if ($citations['SLGS']) {
                $info .= writeCitation($citations['SLGS'], 2);
              }
              $info .= $extras['SLGS'];
            }
          }
          $info .= doAssociations($familyID);

          if ($citations['NAME']) {
            $info .= writeCitation($citations['NAME'], 1);
          }

          if ($famnotes['-x--general--x-']) {
            $info .= writeNote(1, 'NOTE', $famnotes['-x--general--x-']);
          }

          if ($fammedia['-x--general--x-']) {
            $info .= writeMediaLinks($fammedia['-x--general--x-'], 1);
          }
        }
        $info .= $childdata;

        if ($family['changedate']) {
          $info .= "1 CHAN$lineending";
          $info .= "2 DATE {$family['changedate']}$lineending";
        }

        fwrite($fp, $info);
        $savestate['fcount']++;
        if ($savestate['fcount'] % 10 == 0) {
          echo "<strong>F{$savestate['fcount']}</strong> ";
        }
      }
      return $info;
    }

    function doSources() {
      global $savestate;
      global $sourceprefix;
      global $sourcesuffix;
      global $allsources;
      global $allrepos;
      global $branch;

      $sourcestr = '';
      if ($branch) {
        $newsources = array_unique($allsources);
        if ($newsources) {
          foreach ($newsources as $nextsource) {
            $srcquery = "SELECT * FROM sources WHERE sourceID = '$nextsource'";
            $srcresult = tng_query($srcquery) or die(uiTextSnippet('cannotexecutequery') . ": $query");
            if ($srcresult) {
              $source = tng_fetch_assoc($srcresult);
              if ($branch) {
                array_push($allrepos, $source['repoID']);
              }
              $sourcestr .= writeSource($source);
              tng_free_result($srcresult);
            }
          }
        }
      } else {
        if ($sourceprefix) {
          $prefixlen = strlen($sourceprefix) + 1;
          $numstr = "(0+SUBSTRING(sourceID,$prefixlen))";
        } else {
          $numstr = "(0+SUBSTRING_INDEX(sourceID,'$sourcesuffix',1))";
        }

        $srcquery = "SELECT *, $numstr AS num FROM sources WHERE 1 {$savestate['wherestr']} ORDER BY num";
        $srcresult = tng_query($srcquery) or die(uiTextSnippet('cannotexecutequery') . ": $query");
        while ($source = tng_fetch_assoc($srcresult)) {
          $sourcestr .= writeSource($source);
        }
        tng_free_result($srcresult);
      }

      return $sourcestr;
    }

    function writeSource($source) {
      global $saveimport_table;
      global $savestate;
      global $lineending;
      global $saveimport;
      global $fp;

      $sourcestr = '';
      $srcnotes = getNotes($source['sourceID']);
      $srcmedia = getMediaLinks($source['sourceID']);

      $sourcestr .= "0 @{$source['sourceID']}@ SOUR$lineending";
      if ($source['title']) {
        $sourcestr .= "1 TITL {$source['title']}$lineending";
      }
      if ($source['shorttitle']) {
        $sourcestr .= "1 ABBR {$source['shorttitle']}$lineending";
      }
      if ($source['author']) {
        $sourcestr .= "1 AUTH {$source['author']}$lineending";
      }
      if ($source['publisher']) {
        $sourcestr .= "1 PUBL {$source['publisher']}$lineending";
      }
      if ($source['repoID']) {
        $sourcestr .= "1 REPO @{$source['repoID']}@$lineending";
        if ($source['callnum']) {
          $sourcestr .= "2 CALN {$source['callnum']}$lineending";
        }
      } else {
        if ($source['callnum']) {
          $sourcestr .= "1 CALN {$source['callnum']}$lineending";
        }
      }

      $query = "SELECT tag, description, eventdate, eventplace, info FROM events, eventtypes WHERE persfamID = \"{$source['sourceID']}\" AND events.eventtypeID = eventtypes.eventtypeID AND type = \"S\" AND keep = \"1\" ORDER BY ordernum";
      $custevents = tng_query($query);
      while ($custevent = tng_fetch_assoc($custevents)) {
        $sourcestr .= doEvent($custevent, 1);
        $eventID = $custevent['eventID'];
        $sourcestr .= doNotesAndMedia($srcnotes, $srcmedia, $eventID, 2);
      }
      tng_free_result($custevents);

      if ($source['actualtext']) {
        $sourcestr .= writeNote(1, 'TEXT', $source['actualtext']);
      }
      if ($srcnotes['-x--general--x-']) {
        $sourcestr .= writeNote(1, 'NOTE', $srcnotes['-x--general--x-']);
      }

      if ($srcmedia['-x--general--x-']) {
        $sourcestr .= writeMediaLinks($srcmedia['-x--general--x-'], 1);
      }

      $savestate['scount']++;
      if ($saveimport) {
        $savestate['offset'] = ftell($fp);
        $query = "UPDATE $saveimport_table SET offset={$savestate['offset']}, lasttype={$savestate['lasttype']}, lastid=\"{$source['sourceID']}\", scount=\"{$savestate['scount']}\"";
        $saveresult = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
      }
      if ($savestate['scount'] % 10 == 0) {
        echo "<strong>S{$savestate['scount']}</strong> ";
      }

      return $sourcestr;
    }

    function doRepositories() {
      global $branch;
      global $savestate;
      global $repoprefix;
      global $reposuffix;
      global $allrepos;

      $repostr = '';

      if ($branch) {
        $newrepos = array_unique($allrepos);
        if ($newrepos) {
          foreach ($newrepos as $nextrepo) {
            $repoquery = "SELECT * FROM repositories WHERE repoID = '$nextrepo'";
            $reporesult = tng_query($repoquery) or die(uiTextSnippet('cannotexecutequery') . ": $query");
            if ($reporesult) {
              $repo = tng_fetch_assoc($reporesult);
              $repostr .= writeRepository($repo);
              tng_free_result($reporesult);
            }
          }
        }
      } else {
        if ($repoprefix) {
          $prefixlen = strlen($repoprefix) + 1;
          $numstr = "(0+SUBSTRING(repoID,$prefixlen))";
        } else {
          $numstr = "(0+SUBSTRING_INDEX(repoID,'$reposuffix',1))";
        }

        $repoquery = "SELECT *, $numstr AS num FROM repositories WHERE 1 {$savestate['wherestr']} ORDER BY num";
        $reporesult = tng_query($repoquery) or die(uiTextSnippet('cannotexecutequery') . ": $query");

        while ($repo = tng_fetch_assoc($reporesult)) {
          $repostr .= writeRepository($repo);
        }
        tng_free_result($reporesult);
      }

      return $repostr;
    }

    function writeRepository($repo) {
      global $savestate;
      global $lineending;
      global $saveimport_table;

      $repostr = '';
      $reponotes = getNotes($repo['repoID']);
      $repomedia = getMediaLinks($repo['repoID']);

      $repostr .= "0 @{$repo['repoID']}@ REPO$lineending";
      if ($repo['reponame']) {
        $repostr .= "1 NAME {$repo['reponame']}$lineending";
      }
      if ($repo['addressID']) {
        $repostr .= getFact($repo, 1);
      }

      $query = "SELECT tag, description, eventdate, eventplace, info FROM events, eventtypes WHERE persfamID = \"{$repo['repoID']}\" AND events.eventtypeID = eventtypes.eventtypeID AND type = \"R\" AND keep = \"1\" ORDER BY ordernum";
      $custevents = tng_query($query);
      while ($custevent = tng_fetch_assoc($custevents)) {
        $repostr .= doEvent($custevent, 1);
      }
      tng_free_result($custevents);

      if ($reponotes['-x--general--x-']) {
        $repostr .= writeNote(1, 'NOTE', $reponotes['-x--general--x-']);
      }

      if ($repomedia['-x--general--x-']) {
        $repostr .= writeMediaLinks($repomedia['-x--general--x-'], 1);
      }

      $savestate['rcount']++;
      if ($saveimport) {
        $savestate['offset'] = ftell($fp);
        $query = "UPDATE $saveimport_table SET offset={$savestate['offset']}, lasttype={$savestate['lasttype']}, lastid=\"{$repo['repoID']}\", rcount=\"{$savestate['rcount']}\"";
        $saveresult = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
      }
      if ($savestate['rcount'] % 10 == 0) {
        echo "<strong>R{$savestate['rcount']}</strong> ";
      }

      return $repostr;
    }

    function writePlace($place, $level) {
      global $branch, $placelist, $lineending;

      if ($branch && !in_array($place, $placelist)) {
        array_push($placelist, $place);
      }
      return "$level PLAC $place$lineending";
    }

    function doPlaces() {
      global $branch;
      global $placelist;
      global $savestate;
      global $lineending;

      $places = [];

      if ($branch) {
        foreach ($placelist as $place) {
          $query = "SELECT place, notes, latitude, longitude, placelevel, zoom FROM places WHERE place = \"" . addslashes($place) . '"';
          $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
          $row = tng_fetch_assoc($result);
          if ($row['latitude'] || $row['longitude'] || $row['notes']) {
            $places[] = $row;
          }
          tng_free_result($result);
        }
      } else {
        $query = "SELECT place, notes, latitude, longitude, placelevel, zoom FROM places WHERE (latitude != \"\" OR longitude != \"\" OR notes != \"\")";
        $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
        while ($row = tng_fetch_assoc($result)) {
          $places[] = $row;
        }
        tng_free_result($result);

        $query = "SELECT medialinks.personID AS place, places.notes AS notes, latitude, longitude FROM (places, medialinks) WHERE linktype = 'L' places.place = medialinks.personID";
        $result = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
        while ($row = tng_fetch_assoc($result)) {
          if (!in_array($place, $places)) {
            $places[] = $row;
          } //should be OK to overwrite, because it should all be the same
        }
        tng_free_result($result);
      }
      $placestr = '';
      foreach ($places as $place) {
        $placemedia = getMediaLinks($place['place']);
        //export
        $placestr .= "0 _PLAC {$place['place']}$lineending";
        if ($place['latitude'] || $place['longitude']) {
          $placestr .= "1 MAP$lineending";
          $placestr .= "2 LATI {$place['latitude']}$lineending";
          $placestr .= "2 LONG {$place['longitude']}$lineending";
          $placestr .= "2 ZOOM {$place['zoom']}$lineending";
          $placestr .= "2 PLEV {$place['placelevel']}$lineending";
        }
        if ($place['notes']) {
          $placestr .= writeNote(1, 'NOTE', $place['notes']);
        }
        if ($placemedia) {
          $placestr .= writeMediaLinks($placemedia['-x--general--x-'], 1);
        }
        $savestate['pcount']++;
        if ($saveimport) {
          $savestate['offset'] = ftell($fp);
          $query = "UPDATE $saveimport_table SET offset={$savestate['offset']}, lasttype={$savestate['lasttype']}, lastid=\"{$place['place']}\", pcount=\"{$savestate['pcount']}\"";
          $saveresult = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
        }
        if ($savestate['pcount'] % 10 == 0) {
          echo "<strong>P{$savestate['pcount']}</strong> ";
        }
      }
      return $placestr;
    }
    
    ?>
    <p><strong><?php echo uiTextSnippet('exporting'); ?></strong></p>
    <?php
    if ($saveimport) {
      echo '<p>' . uiTextSnippet('ifexportfails') . ' <a href="dataExportGedcomFormAction.php?resume=1">' . uiTextSnippet('clickresume') . "</a>.</p>$lineending";
    }
    set_time_limit(0);
    $xnotes = [];

    $largechunk = 1000;
    $filename = "$rootpath$gedpath/$tree.ged";
    $found = 0;

    //if saving is enabled and URL flag is set, check the db table to see if a record exists
    if ($saveimport) {
      if ($resume) {
        $checksql = "SELECT filename, offset, lasttype, lastid, icount, fcount, scount, ncount, rcount, mcount, pcount FROM $saveimport_table";
        $result = tng_query($checksql) or die(uiTextSnippet('cannotexecutequery') . ": $checksql");
        $found = tng_num_rows($result);
        if ($found) {
          $row = tng_fetch_assoc($result);
          $filename = $row['filename'];
          $savestate['offset'] = $row['offset'];
          $savestate['icount'] = $row['icount'];
          $savestate['fcount'] = $row['fcount'];
          $savestate['scount'] = $row['scount'];
          $savestate['ncount'] = $row['ncount'];
          $savestate['rcount'] = $row['rcount'];
          $savestate['media'] = $row['media'];
          $savestate['mcount'] = $row['mcount'];
          $savestate['pcount'] = $row['pcount'];
          //retrieve entity type (I = 1, F = 2, S = 3, X = 4, R = 5)
          $savestate['lasttype'] = $row['lasttype'];
          $prefix = $suffix = '';
          switch ($savestate['lasttype']) {
            case 1:
              $prefix = $personprefix;
              $suffix = $personsuffix;
              $idstring = 'personID';
              break;
            case 2:
              $prefix = $familyprefix;
              $suffix = $familysuffix;
              $idstring = 'familyID';
              break;
            case 3:
              $prefix = $sourceprefix;
              $suffix = $sourcesuffix;
              $idstring = 'sourceID';
              break;
            case 4:
              $prefix = $noteprefix;
              $suffix = $notesuffix;
              $idstring = 'noteID';
              break;
            case 5:
              $prefix = $repoprefix;
              $suffix = $reposuffix;
              $idstring = 'repoID';
              break;
          }
          if ($prefix) {
            $prefixlen = strlen($prefix) + 1;
            $savestate['wherestr'] = " AND (0+SUBSTRING($idstring,$prefixlen)) > " . substr($row['lastid'], $prefixlen - 1);
          } else {
            $suffixlen = strlen($suffix) * -1;
            $savestate['wherestr'] = " AND (0+SUBSTRING_INDEX($idstring,'$suffix',1)) > " . substr($row['lastid'], 0, $suffixlen);
          }
        }
        tng_free_result($result);
      } else {
        $query = "DELETE from $saveimport_table";
        $result = tng_query($query);

        $sql = "INSERT INTO $saveimport_table (filename, offset, media) VALUES('$filename', 0, '$exportmedia')";
        $result = tng_query($sql) or die(uiTextSnippet('cannotexecutequery') . ": $sql");
      }
    }
    if ($found) {
      $fp = fopen($filename, 'r+');
      fseek($fp, $savestate['offset']);
    } else {
      if (file_exists($filename)) {
        unlink($filename);
      }
      $fp = fopen($filename, 'w');
      $savestate['lasttype'] = 1;
      $savestate['icount'] = 0;
      $savestate['fcount'] = 0;
      $savestate['scount'] = 0;
      $savestate['ncount'] = 0;
      $savestate['rcount'] = 0;
      $savestate['mcount'] = 0;
      $savestate['pcount'] = 0;
      $savestate['media'] = $exportmedia;
    }
    if (!$fp) {
      die(uiTextSnippet('cannotopen') . ' ' . $filename);
    }
    flock($fp, LOCK_EX);

    //if saving is enabled, write out new information after each person/family/source/repo

    $numrows = 0;

    $maxgcgen = 999;

    if (!$found) {
      $query = 'SELECT email, owner FROM trees';
      $treeresult = tng_query($query);
      $treerow = tng_fetch_assoc($treeresult);
      tng_free_result($treeresult);

      $owneremail = $treerow['email'] ? $treerow['email'] : $emailaddr;
      $ownername = $treerow['owner'] ? $treerow['owner'] : $dbowner;

      $firstpart = "0 HEAD$lineending"
              . "1 SOUR The Next Generation of Genealogy Sitebuilding$lineending"
              . "2 VERS $tng_version$lineending"
              . "2 NAME The Next Generation of Genealogy Sitebuilding (R)$lineending"
              . "2 CORP Next Generation Software, LLC$lineending"
              . "3 ADDR Sandy, UT$lineending"
              . "1 FILE $tree.ged$lineending"
              . "1 GEDC$lineending"
              . "2 VERS 5.5$lineending"
              . "2 FORM LINEAGE-LINKED$lineending"
              . '1 CHAR ' . ($session_charset == 'UTF-8' ? 'UTF-8' : 'ANSI') . $lineending
              . "1 SUBM @SUB1@$lineending"
              . "0 @SUB1@ SUBM$lineending"
              . "1 NAME $ownername$lineending"
              . "1 EMAIL $owneremail$lineending";

      fwrite($fp, "$firstpart");
    }
    $prefixlen = strlen($personprefix) + 1;
    $branchstr = $branch ? " AND branch LIKE \"%$branch%\"" : '';
    $exlivingstr = $exliving ? ' AND living != "1"' : '';
    $exprivatestr = $exprivate ? ' AND private != "1"' : '';

    if ($savestate['lasttype'] < 2) {
      $nextchunk = -1;
      do {
        $nextone = $nextchunk + 1;
        $nextchunk += $largechunk;

        if ($personprefix) {
          $prefixlen = strlen($personprefix) + 1;
          $numstr = "(0+SUBSTRING(personID,$prefixlen))";
        } else {
          $numstr = "(0+SUBSTRING_INDEX(personID,'$personsuffix',1))";
        }
        $query = "SELECT personID, $numstr AS num, lastname, lnprefix, firstname, sex, title, prefix, suffix, nickname, birthdate, birthdatetr, birthplace, altbirthdate, altbirthdatetr, altbirthplace, deathdate, deathdatetr, deathplace, burialdate, burialdatetr, burialplace, burialtype, baptdate, baptplace, endldate, endlplace, famc, living, private, branch, DATE_FORMAT(changedate,\"%d %b %Y\") AS changedate "
          . "FROM $people_table "
          . "WHERE 1 $branchstr $exlivingstr $exprivatestr {$savestate['wherestr']} "
          . 'ORDER BY num '
          . "LIMIT $nextone, $largechunk";
        $result = tng_query($query);
        if ($result) {
          $numrows = tng_num_rows($result);
          while ($ind = tng_fetch_assoc($result)) {
            if ($ind['personID']) {
              writeIndividual($ind);
              if ($saveimport) {
                $savestate['offset'] = ftell($fp);
                $query = "UPDATE $saveimport_table SET offset={$savestate['offset']}, lasttype={$savestate['lasttype']}, lastid=\"{$ind['personID']}\", icount=\"{$savestate['icount']}\"";
                $saveresult = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
              }
            }
          }
          tng_free_result($result);
        }
      } while ($numrows);
      $savestate['lasttype'] = 2;
      $savestate['wherestr'] = '';
    }
    $prefixlen = strlen($familyprefix) + 1;

    if ($savestate['lasttype'] < 3) {
      $nextchunk = -1;
      do {
        $nextone = $nextchunk + 1;
        $nextchunk += $largechunk;

        if ($familyprefix) {
          $prefixlen = strlen($familyprefix) + 1;
          $numstr = "(0+SUBSTRING(familyID,$prefixlen))";
        } else {
          $numstr = "(0+SUBSTRING_INDEX(familyID,'$familysuffix',1))";
        }

        $query = "SELECT *, (0+SUBSTRING(familyID,$prefixlen)) AS num, DATE_FORMAT(changedate,\"%d %b %Y\") AS changedate "
          . "FROM $families_table "
          . "WHERE 1 $branchstr {$savestate['wherestr']} $exlivingstr $exprivatestr "
          . 'ORDER BY num '
          . "LIMIT $nextone, $largechunk";
        $result = tng_query($query);
        if ($result) {
          $numrows = tng_num_rows($result);
          while ($fam = tng_fetch_assoc($result)) {
            if ($fam['familyID']) {
              $famarray[$fam['familyID']] = writeFamily($fam);
              if ($saveimport) {
                $savestate['offset'] = ftell($fp);
                $query = "UPDATE $saveimport_table SET offset={$savestate['offset']}, lasttype={$savestate['lasttype']}, lastid=\"{$fam['familyID']}\", fcount=\"{$savestate['fcount']}\"";
                $saveresult = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");
              }
            }
          }
          tng_free_result($result);
        }
      } while ($numrows);
      $savestate['lasttype'] = 3;
      $savestate['wherestr'] = '';
    }
    if ($savestate['lasttype'] < 4) {
      fwrite($fp, doSources());
      $savestate['lasttype'] = 4;
      $savestate['wherestr'] = '';
    }
    if ($savestate['lasttype'] < 5) {
      fwrite($fp, doXNotes());
      $savestate['lasttype'] = 5;
      $savestate['wherestr'] = '';
    }
    if ($savestate['lasttype'] < 6) {
      fwrite($fp, doRepositories());
      $savestate['lasttype'] = 6;
      $savestate['wherestr'] = '';
    }
    if ($savestate['lasttype'] < 7) {
      fwrite($fp, doPlaces());
      $savestate['lasttype'] = 7;
      $savestate['wherestr'] = '';
    }
    fwrite($fp, "0 TRLR$lineending");

    flock($fp, LOCK_UN);
    fclose($fp);
    chmod($filename, 0644);

    if ($saveimport) {
      $sql = "DELETE from $saveimport_table";
      $result = tng_query($sql) or die(uiTextSnippet('cannotexecutequery') . ": $query");
    }
    ?>
    <p>
      <?php
      adminwritelog(uiTextSnippet('export'));
      echo uiTextSnippet('finishedexporting');
      echo "<br>\n";
      echo "{$savestate['icount']} " . uiTextSnippet('people')
        . ", {$savestate['fcount']} " . uiTextSnippet('families')
        . ", {$savestate['scount']} " . uiTextSnippet('sources')
        . ", {$savestate['rcount']} " . uiTextSnippet('repositories')
        . ", {$savestate['ncount']} " . uiTextSnippet('notes')
        . ", {$savestate['mcount']} " . uiTextSnippet('media')
        . ", {$savestate['pcount']} " . uiTextSnippet('places');
      ?>
    </p>
    <p>
      <a href="<?php echo "$gedpath/$tree" . '.ged' ?>"><?php echo uiTextSnippet('downloadged'); ?></a>
    </p>
    <?php echo $adminFooterSection->build(); ?>
  </section>
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
</body>
</html>

