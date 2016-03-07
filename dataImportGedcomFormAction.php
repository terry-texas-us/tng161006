<?php
@ini_set("magic_quotes_runtime", "0");
@ini_set("auto_detect_line_endings", "1");
@ini_set('memory_limit', '200M');
$umfs = substr(ini_get("upload_max_filesize"), 0, -1);
if ($umfs < 15) {
  @ini_set("upload_max_filesize", "15M");
  @ini_set("post_max_filesize", "15M");
}

include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if (!$allow_add || !$allow_edit || $assignedbranch) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
include($subroot . "importconfig.php");
require("datelib.php");
require 'gedcomImportTrees.php';
require 'gedcomImportFamilies.php';
require 'gedcomImportSources.php';
require 'gedcomImportPeople.php';
require 'gedcomImportMisc.php';
require("adminlog.php");
$today = date("Y-m-d H:i:s");

$readmsecs = $tngimpcfg['readmsecs'] && is_numeric($tngimpcfg['readmsecs']) ? $tngimpcfg['readmsecs'] : 750; //every 750 milliseconds
$writeinterval = $tngimpcfg['rrnum'] && is_numeric($tngimpcfg['rrnum']) ? $tngimpcfg['rrnum'] : 100; //every 100 records

global $prefix, $medialinks, $albumlinks;
$medialinks = $albumlinks = array();
$ldsOK = determineLDSRights();

//added in 9.0.3
@ob_implicit_flush(true);

//@ob_end_flush();

function getMediaLinksToSave() {
  global $events_table, $tree, $medialinks_table;

  $medialinks = array();
  $query = "SELECT medialinkID, mediaID, $medialinks_table.eventID, persfamID, eventtypeID, eventdate, eventplace, info
    FROM ($medialinks_table,$events_table)
    WHERE $medialinks_table.gedcom = \"$tree\" AND $medialinks_table.eventID != \"\" AND $medialinks_table.eventID = $events_table.eventID";
  $result = @tng_query($query);
  while ($row = tng_fetch_assoc($result)) {
    $key = $row['persfamID'] . "::" . $row['eventtypeID'] . "::" . $row['eventdate'] . "::" . substr($row['eventplace'], 0, 40) . "::" . substr($row['info'], 0, 40);
    $key = preg_replace("/[^A-Za-z0-9:]/", "", $key);
    $value = $row['medialinkID'];
    $medialinks[$key][] = $value;
    //$eventlinks[$value] = $row['eventID'];
  }
  return $medialinks;
}

function getAlbumLinksToSave() {
  global $events_table, $tree, $album2entities_table;

  $albumlinks = array();
  $query = "SELECT alinkID, albumID, $album2entities_table.eventID, entityID, eventtypeID, eventdate, eventplace, info
    FROM ($album2entities_table,$events_table)
    WHERE $album2entities_table.gedcom = \"$tree\" AND $album2entities_table.eventID != \"\" AND $album2entities_table.eventID = $events_table.eventID";
  $result = @tng_query($query);
  while ($row = tng_fetch_assoc($result)) {
    $key = $row['entityID'] . "::" . $row['eventtypeID'] . "::" . $row['eventdate'] . "::" . substr($row['eventplace'], 0, 40) . "::" . substr($row['info'], 0, 40);
    $key = preg_replace("/[^A-Za-z0-9:]/", "", $key);
    $value = $row['alinkID'];
    $albumlinks[$key][] = $value;
  }
  return $albumlinks;
}

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('datamaint'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="datamaint-gedimport">

  <?php
  if ($old) {
    echo $adminHeaderSection->build('datamaint-gedimport', $message);
    $navList = new navList('');
    $navList->appendItem([true, "dataImportGedcom.php", uiTextSnippet('import'), "import"]);
    $navList->appendItem([$allow_export, "dataExportGedcom.php", uiTextSnippet('export'), "export"]);
    $navList->appendItem([true, "admin_secondmenu.php", uiTextSnippet('secondarymaint'), "second"]);
    echo $navList->build("import");
    
    echo "<div style=\"padding:2px\">\n";
    echo "<div style=\"padding:5px\">\n";
  }
  $stdevents = array("BIRT", "SEX", "DEAT", "BURI", "MARR", "SLGS", "SLGC", "NICK", "NSFX", "TITL", "BAPL", "CONL", "INIT", "ENDL", "CHAN", "CALN", "AUTH", "PUBL", "ABBR", "TEXT");
  $pciteevents = array("NAME", "BIRT", "CHR", "DEAT", "BURI", "BAPL", "CONL", "INIT", "ENDL", "SLGC");
  $fciteevents = array("MARR", "DIV", "SLGS");

  //read first line into $line
  set_time_limit(0);
  $fp = false;
  $savestate['filename'] = "";
  $openmsg = "";
  $clearedtogo = "false";
  if ($remotefile && $remotefile != "none") {
    $basefilename = preg_replace("/[^a-zA-Z0-9\._-]/", "_", basename($_FILES['remotefile']['name']));
    $gedfilename = "$rootpath$gedpath/$basefilename";
    $savegedfilename = $basefilename;

    if (@move_uploaded_file($remotefile, $gedfilename)) {
      @chmod($gedfilename, 0644);

      $fp = @fopen($gedfilename, "r");
      //$fp = @fopen("xxx","r");
      if ($fp === false) {
        $openmsg = uiTextSnippet('cannotopen') . " $basefilename. " . uiTextSnippet('umps');
      } else {
        $fstat = fstat($fp);
        $openmsg = uiTextSnippet('importinggedcom');
        $savestate['filename'] = $gedfilename;
        $clearedtogo = "true";
        if ($old) {
          echo "<strong>$remotefile " . uiTextSnippet('opened') . "</strong><br>\n";
        }
      }
    } else {
      $openmsg = uiTextSnippet('cannotupload') . " " . $_FILES['remotefile']['name'] . ". " . uiTextSnippet('invfperms');
    }
  } elseif ($database) {
    $gedfilename = $gedpath == "admin" || $gedpath == "" ? $database : "$rootpath$gedpath/$database";
    $savegedfilename = $database;
    $fp = @fopen($gedfilename, "r");
    if ($fp === false) {
      $openmsg = uiTextSnippet('cannotopen') . " $database";
    } else {
      $fstat = fstat($fp);
      $openmsg = uiTextSnippet('importinggedcom');
      $savestate['filename'] = $gedfilename;
      $clearedtogo = "true";
      if ($old) {
        echo "<strong>$database " . uiTextSnippet('opened') . "</strong><br>\n";
      }
    }
  } elseif (!$resuming) {
    $openmsg = uiTextSnippet('cannotopen') . ". " . uiTextSnippet('umps');
  }

  $allcount = 0;
  if ($savestate['filename']) {
    $tree = $tree1; //selected
    $query = "UPDATE $trees_table SET lastimportdate=\"$today\", importfilename=\"$savegedfilename\" WHERE gedcom=\"$tree\"";
    $result = @tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");

    if ($del == "append") {
      //calculate offsets
      if ($offsetchoice == "auto") {
        $savestate['ioffset'] = getNewNumericID("person", "person", $people_table);
        $savestate['foffset'] = getNewNumericID("family", "family", $families_table);
        $savestate['soffset'] = getNewNumericID("source", "source", $sources_table);
        $savestate['noffset'] = getNewNumericID("note", "note", $xnotes_table);
        $savestate['roffset'] = getNewNumericID("repo", "repo", $repositories_table);
      } else {
        $savestate['ioffset'] = $savestate['foffset'] = $savestate['soffset'] = $savestate['noffset'] = $savestate['roffset'] = $useroffset;
      }
      $savestate['del'] = "match";
    } else {
      $savestate['del'] = $del;
      $savestate['ioffset'] = $savestate['foffset'] = $savestate['soffset'] = $savestate['noffset'] = $savestate['roffset'] = 0;
      //get all medialinks+events where eventID is not blank
      if ($del != "no") {
        $medialinks = getMediaLinksToSave();
        $num_medialinks = count($medialinks);

        $albumlinks = getAlbumLinksToSave();
        $num_albumlinks = count($albumlinks);
      }
      if ($del == "yes") {
        ClearData($tree);
      }
    }

    $savestate['icount'] = 0;
    $savestate['fcount'] = 0;
    $savestate['scount'] = 0;
    $savestate['mcount'] = 0;
    $savestate['ncount'] = 0;
    $savestate['pcount'] = 0;
    $savestate['offset'] = 0;
    $savestate['ucaselast'] = $ucaselast ? 1 : 0;
    $savestate['norecalc'] = $norecalc ? 1 : 0;
    $savestate['neweronly'] = $neweronly ? 1 : 0;
    $savestate['media'] = $importmedia ? 1 : 0;
    $savestate['latlong'] = $importlatlong ? 1 : 0;
    $savestate['branch'] = $branch1;
    $allcount = 0;
    $mll = $savestate['media'] * 10 + $savestate['latlong'];

    if ($saveimport) {
      $query = "DELETE from $saveimport_table";
      $result = @tng_query($query);

      $sql = "INSERT INTO $saveimport_table (filename, icount, ioffset, fcount, foffset, scount, soffset, mcount, pcount, ncount, noffset, roffset, offset, delvar, ucaselast, norecalc, neweronly, media, gedcom, branch)  VALUES(\"{$savestate['filename']}\", 0, \"{$savestate['ioffset']}\", 0, \"{$savestate['foffset']}\", 0, \"{$savestate['soffset']}\", 0, 0, 0, \"{$savestate['noffset']}\", \"{$savestate['roffset']}\", 0, \"$del\", {$savestate['ucaselast']}, {$savestate['norecalc']}, {$savestate['neweronly']}, $mll, \"$tree\", \"$branch\")";
      $result = @tng_query($sql) or die(uiTextSnippet('cannotexecutequery') . ": $sql");
    }
  } elseif ($saveimport && !$openmsg) {
    $checksql = "SELECT filename, icount, ioffset, fcount, foffset, scount, soffset, mcount, pcount, ncount, noffset, offset, ucaselast, norecalc, neweronly, media, branch, delvar from $saveimport_table WHERE gedcom = \"$tree\"";
    $result = @tng_query($checksql) or die(uiTextSnippet('cannotexecutequery') . ": $checksql");
    $found = tng_num_rows($result);
    if ($found) {
      $row = tng_fetch_assoc($result);
      $savestate['icount'] = $row['icount'];
      $savestate['fcount'] = $row['fcount'];
      $savestate['scount'] = $row['scount'];
      $savestate['mcount'] = $row['mcount'];
      $savestate['ncount'] = $row['ncount'];
      $savestate['pcount'] = $row['pcount'];
      $allcount = $savestate['icount'] + $savestate['fcount'] + $savestate['scount'] + $savestate['ncount'] + $savestate['mcount'] + $savestate['pcount'];
      $savestate['ioffset'] = $row['ioffset'];
      $savestate['foffset'] = $row['foffset'];
      $savestate['soffset'] = $row['soffset'];
      $savestate['noffset'] = $row['noffset'];
      $savestate['roffset'] = $row['roffset'];
      $savestate['filename'] = $row['filename'];
      $savestate['offset'] = $row['offset'];
      $savestate['del'] = $row['delvar'];
      $savestate['ucaselast'] = $row['ucaselast'];
      $savestate['norecalc'] = $row['norecalc'];
      $savestate['neweronly'] = $row['neweronly'];
      $savestate['media'] = ($row['media'] > 9) ? 1 : 0;
      $savestate['latlong'] = $row['media'] % 2;
      $savestate['branch'] = $row['branch'];
      if ($savestate['del'] == "yes") {
        $savestate['del'] = "match";
      }
      $gedfilename = $savestate['filename'];
      $fp = fopen($savestate['filename'], "r");
      if ($fp !== false) {
        $fstat = fstat($fp);
        fseek($fp, $savestate['offset']);

        $openmsg = uiTextSnippet('importinggedcom');
        $clearedtogo = "true";

        if ($del != "no") {
          $medialinks = getMediaLinksToSave();
          $num_medialinks = count($medialinks);

          $albumlinks = getAlbumLinksToSave();
          $num_albumlinks = count($albumlinks);
        }
      } else {
        $openmsg = uiTextSnippet('cannotopen') . " " . $savestate['filename'] . " " . uiTextSnippet('toresume');
      }
    } else {
      $openmsg = uiTextSnippet('notresumed') . " " . uiTextSnippet('maybedone');
    }
  } elseif (!$openmsg) {
    $openmsg = uiTextSnippet('notresumed') . " " . uiTextSnippet('turnonsis');
  }

  if ($old) {
    echo "<p>$openmsg</p>\n";
    if ($clearedtogo == "true" && $saveimport && (!$remotefile || $remotefile == "none")) {
      echo "<p>" . uiTextSnippet('ifimportfails') . " <a href=\"dataImportGedcomFormAction.php?tree=$tree&amp;old=1\">" . uiTextSnippet('clickresume') . "</a>.</p>\n";
    }
  } else {
    ?>
    <script>
      var idivs, timeoutID;
      parent.started = <?php echo $clearedtogo; ?>;
      var icount = parent.document.getElementById('personcount');
      var fcount = parent.document.getElementById('familycount');
      var scount = parent.document.getElementById('sourcecount');
      var ncount = parent.document.getElementById('notecount');
      var mcount = parent.document.getElementById('mediacount');
      var pcount = parent.document.getElementById('placecount');
      var pbar = parent.document.getElementById('bar');
      function updateCount() {
        idivs = jQuery('div.impc');
        if (idivs.length) {
          var ilen = idivs.length - 1;
          //console.log('file at ' + idivs['ilen'].down('#pr').innerHTML);
          var pr = jQuery(idivs[ilen]).find('#pr');
          if (pr.length)
            pbar.style.width = pr.html();

          var ic = jQuery(idivs[ilen]).find('#ic');
          if (ic.length)
            icount.innerHTML = ic.html();

          var fc = jQuery(idivs[ilen]).find('#fc');
          if (fc.length)
            fcount.innerHTML = fc.html();

          var sc = jQuery(idivs[ilen]).find('#sc');
          if (sc.length)
            scount.innerHTML = sc.html();

          var nc = jQuery(idivs[ilen]).find('#nc');
          if (nc.length)
            ncount.innerHTML = nc.html();

          var mc = jQuery(idivs[ilen]).find('#mc');
          if (mc.length)
            mcount.innerHTML = mc.html();

          var pc = jQuery(idivs[ilen]).find('#pc');
          if (pc.length)
            pcount.innerHTML = pc.html();
        }
        if (!parent.done)
          timeoutID = setTimeout(updateCount, <?php echo $readmsecs; ?>);
        else if (!parent.suspended) {
          msgdiv.innerHTML = textSnippet('finishedimporting') + ' &nbsp;<img src="img/tng_check.gif">';
          showCloseMenu();
        }
      }
      function showCloseMenu() {
        var closemsg = '<a href="#" onclick="tnglitbox.remove();return false;"><img src="img/tng_close.gif" style="margin-right:5px">' + textSnippet('closewindow') + '</a>';
        if (parent.started)
          parent.document.getElementById('implinks').innerHTML = '<span id="toremove"><a href="#" onclick="return removeFile(\'<?php echo $gedfilename; ?>\');">' + textSnippet('removeged') + '</a></span><p>' + closemsg + ' | <a href="admin_secondmenu.php">' + textSnippet('moreoptions') + '</a></p>';
        else
          parent.document.getElementById('implinks').innerHTML = '<p>' + closemsg + '</p>';
      }
      var msgdiv = parent.document.getElementById('importmsg');
      if (parent.started) {
        parent.document.getElementById('impdata').style.visibility = "visible";
        timeoutID = setTimeout(updateCount, <?php echo $readmsecs; ?>);
      } else
        showCloseMenu();
      msgdiv.innerHTML = "<?php echo $openmsg; ?>";
    </script>

    <?php
  }

  if ($fp !== false) {
    @ob_flush();
    @flush();

    $savestate['livingstr'] = $savestate['norecalc'] ? "" : ", living";
    if (!$tngimpcfg['maxlivingage']) {
      $tngimpcfg['maxlivingage'] = 110;
    }

    //get custom event types
    $query = "SELECT eventtypeID, tag, description, keep, type, display FROM $eventtypes_table";
    $result = @tng_query($query);
    $custeventlist = array();
    while ($row = tng_fetch_assoc($result)) {
      $eventtype = strtoupper($row['type'] . "_" . $row['tag'] . "_" . $row['description']);
      $custevents[$eventtype]['keep'] = $row['keep'];
      $custevents[$eventtype]['display'] = $row['display'];
      $custevents[$eventtype]['eventtypeID'] = $row['eventtypeID'];
      if ($row['keep'] && !in_array($eventtype, $custeventlist)) {
        array_push($custeventlist, $eventtype); //used to be $row['tag']
      }
    }
    tng_free_result($result);

    $stdnotes = array();
    $notecount = 0;

    $lineinfo = getLine();
    while ($lineinfo['tag']) {
      if ($lineinfo['level'] == 0) {
        preg_match("/^@(\S+)@/", $lineinfo['tag'], $matches);
        $id = $matches[1];
        switch (trim($lineinfo['rest'])) {
          case "FAM":
            getFamilyRecord($id, 0);
            break;
          case "INDI":
            getIndividualRecord($id, 0);
            break;
          case "SOUR":
            getSourceRecord($id, 0);
            break;
          case "REPO":
            getRepoRecord($id, 0);
            break;
          case "NOTE":
            getNoteRecord($id, 0);
            break;
          case "OBJE":
            if ($savestate['media']) {
              $mminfo = array();
              getMultimediaRecord($id, 0);
            } else {
              $lineinfo = getLine();
            }
            break;
          default:
            if (strtok($lineinfo['rest'], " ") == "NOTE") {
              getNoteRecord($id, 0);
            } elseif ($lineinfo['tag'] == "_PLAC" || $lineinfo['tag'] == "_PLAC_DEFN" || $lineinfo['tag'] == "PLAC") {
              getPlaceRecord($lineinfo['rest'], 0);
            } else {
              $lineinfo = getLine();
            }
            break;
        }
      } else {
        $lineinfo = getLine();
      }
      @ob_flush();
      @flush();
    }
    @fclose($fp);

    //blank out remaining event-based media links
    //foreach($medialinks as $medialinkarr) {
    //foreach($medialinkarr as $medialinkID) {
    //$query = "UPDATE $medialinks_table SET eventID = \"\" WHERE medialinkID = \"$medialinkID\"";
    //$result = @tng_query($query);
    //if(!tng_affected_rows()) {
    //$query = "DELETE FROM $medialinks_table WHERE medialinkID = \"$medialinkID\"";
    //$result = @tng_query($query);
    //}
    //}
    //}
    //delete remaining holdover events (used for media link preservation)
    //$query = "DELETE from $events_table WHERE gedcom = \"$tree\" AND persfamID = \"XXX\"";
    //$result = @tng_query($query);

    if ($saveimport) {
      $sql = "DELETE from $saveimport_table WHERE gedcom = \"$tree\"";
      $result = @tng_query($sql) or die(uiTextSnippet('cannotexecutequery') . ": $query");
    }
    $treemsg = $tree ? ", " . uiTextSnippet('tree') . ": $tree/" : "";
    adminwritelog(uiTextSnippet('gedimport') . ": " . basename(uiTextSnippet('filename')) . ":{$savestate['filename']}$treemsg; {$savestate['icount']} " . uiTextSnippet('people')
            . ", {$savestate['fcount']} " . uiTextSnippet('families')
            . ", {$savestate['scount']} " . uiTextSnippet('sources')
            . ", {$savestate['ncount']} " . uiTextSnippet('notes')
            . ", {$savestate['mcount']} " . uiTextSnippet('media')
            . ", {$savestate['pcount']} " . uiTextSnippet('places'));
    if ($old) {
      echo "<p>" . uiTextSnippet('finishedimporting') . "<br>"
              . number_format($savestate['icount']) . " " . uiTextSnippet('people')
              . " &nbsp; " . number_format($savestate['fcount']) . " " . uiTextSnippet('families')
              . " &nbsp; " . number_format($savestate['scount']) . " " . uiTextSnippet('sources')
              . " &nbsp; " . number_format($savestate['ncount']) . " " . uiTextSnippet('notes')
              . " &nbsp; " . number_format($savestate['mcount']) . " " . uiTextSnippet('media')
              . " &nbsp; " . number_format($savestate['pcount']) . " " . uiTextSnippet('places') . "</p>";
    } else {
      echo "<div class=\"impc\"><span id=\"pr\">500</span><span id=\"ic\">" . $savestate['icount'] . "</span><span id=\"fc\">" . $savestate['fcount'] . "</span><span id=\"sc\">" . $savestate['scount'] . "</span><span id=\"nc\">" . $savestate['ncount'] . "</span><span id=\"mc\">" . $savestate['mcount'] . "</span><span id=\"pc\">" . $savestate['pcount'] . "</span></div>\n";
      ?>
      <script>
        parent.done = true;
      </script>
      <?php
    }
  }

  if ($old) {
    echo "<p><a href=\"admin_secondary.php?secaction=" . uiTextSnippet('tracklines') . "&tree=$tree\">" . uiTextSnippet('tracklines') . "</a></p>";
    echo "<p><a href=\"dataImportGedcom.php\">" . uiTextSnippet('backtodataimport') . "</a></p>\n";
    echo "</div></div>\n";
    echo "<div align=\"right\"><span>$tng_title, v.$tng_version</span></div>";
  }
  ?>
  </body>
</html>
