<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

require("adminlog.php");

if (!$allow_edit || !$allow_delete) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

if ($assignedtree) {
  $wherestr = "WHERE gedcom = \"$assignedtree\"";
} else {
  $wherestr = "";
}

$query = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";
$treeresult = tng_query($query);

function doRow($field, $textmsg, $boxname) {
  global $s1row;
  global $s2row;
  global $repositories_table;
  global $tree;

  $s1field = isset($s1row[$field]) ? $s1row[$field] : "";
  $s2field = isset($s2row[$field]) ? $s2row[$field] : "";

  if ($field == "repoID") {
    if ($s1field) {
      $query = "SELECT reponame FROM $repositories_table WHERE repoID = \"$s1field\" and gedcom = \"$tree\"";
      $result = tng_query($query);
      $reporow = tng_fetch_assoc($result);
      $s1field = $reporow['reponame'] ? "{$reporow['reponame']} ($s1field)" : $s1field;
      tng_free_result($result);
    }
    if ($s2field) {
      $query = "SELECT reponame FROM $repositories_table WHERE repoID = \"$s2field\" and gedcom = \"$tree\"";
      $result = tng_query($query);
      $reporow = tng_fetch_assoc($result);
      $s2field = $reporow['reponame'] ? "{$reporow['reponame']} ($s2field)" : $s2field;
      tng_free_result($result);
    }
  }

  if ($s1field || $s2field) {
    echo "<tr>\n";
    echo "<td width='15%'><span>" . uiTextSnippet($textmsg) . ":</span></td>";
    echo "<td width=\"31%\"><span>$s1field&nbsp;</span></td>";
    if (is_array($s2row)) {
      echo "<td width='10'></td>";
      echo "<td width='15%'><span>" . uiTextSnippet($textmsg) . ":</span></td>";
      echo "<td width=\"5\"><span>";
      if ($boxname) {
        if ($s2field) {
          echo "<input name=\"$boxname\" type='checkbox' value=\"$field\"";
          if ($s2row[$field] && !$s1row[$field]) {
            echo " checked";
          }
          echo ">";
        }
      } else {
        echo "&nbsp;";
      }
      echo "</span></td>";
      echo "<td width=\"31%\"><span>$s2field&nbsp;</span></td>";
    } else {
      echo "<td width='10'></td>";
      echo "<td width='15%'><span>" . uiTextSnippet($textmsg) . ":</span></td>";
      echo "<td width=\"5\"><span>&nbsp;</span></td>";
      echo "<td width=\"31%\"><span>&nbsp;</span></td>";
    }
    echo "</tr>\n";
  }
}

function getEvent($event) {
  global $mylanguage, $languages_path;

  $dispvalues = explode("|", $event['display']);
  $numvalues = count($dispvalues);
  if ($numvalues > 1) {
    $displayval = "";
    for ($i = 0; $i < $numvalues; $i += 2) {
      $lang = $dispvalues[$i];
      if ($mylanguage == $languages_path . $lang) {
        $displayval = $dispvalues[$i + 1];
        break;
      }
    }
  } else {
    $displayval = $event['display'];
  }

  $eventstr = "<strong>$displayval</strong>: ";
  $eventstr2 = $event['eventdate'];
  if ($eventstr2 && $event['eventplace']) {
    $eventstr2 .= ", ";
  }
  $eventstr2 .= $event['eventplace'];
  if ($eventstr2 && $event['info']) {
    $eventstr2 .= ". ";
  }
  $eventstr2 .= $event['info'] . "<br>\n";
  $eventstr .= $eventstr2;

  return $eventstr;
}

function addCriteria($row) {
  global $cshorttitle, $clongtitle, $cauthor, $cpublisher, $crepository, $cactualtext, $cignoreblanks;

  $criteria = "";
  if ($cshorttitle == "yes") {
    $criteria .= " AND " . "shorttitle" . " = \"" . addslashes($row['shorttitle']) . "\"";
    $criteria .= $cignoreblanks == "yes" ? " AND shorttitle != \"\"" : "";
  }
  if ($clongtitle == "yes") {
    $criteria .= " AND " . "title" . " = \"" . addslashes($row['title']) . "\"";
    $criteria .= $cignoreblanks == "yes" ? " AND title != \"\"" : "";
  }
  if ($cauthor == "yes") {
    $criteria .= " AND author = \"" . addslashes($row['author']) . "\"";
    $criteria .= $cignoreblanks == "yes" ? " AND author != \"\"" : "";
  }
  if ($cpublisher == "yes") {
    $criteria .= " AND publisher = \"" . addslashes($row['publisher']) . "\"";
    $criteria .= $cignoreblanks == "yes" ? " AND publisher = \"\"" : "";
  }
  if ($crepository == "yes") {
    $criteria .= " AND repoID = \"" . addslashes($row['repoID']) . "\"";
    $criteria .= $cignoreblanks == "yes" ? " AND repoID != \"\"" : "";
  }
  if ($cactualtext == "yes") {
    $criteria .= " AND actualtext = \"" . addslashes($row['actualtext']) . "\"";
    $criteria .= $cignoreblanks == "yes" ? " AND actualtext = \"\"" : "";
  }

  return $criteria;
}

function doNotes($persfam1, $persfam2, $varname) {
  global $ccombinenotes, $notelinks_table, $tree;

  if ($varname) {
    if ($varname == "general") {
      $varname = "";
    }
    $wherestr = "AND eventID = \"$varname\"";
  } else {
    $wherestr = "";
  }

  if ($ccombinenotes != "yes") {
    $query = "DELETE from $notelinks_table WHERE persfamID = \"$persfam1\" AND gedcom = \"$tree\" $wherestr";
    tng_query($query);
  }
  $query = "UPDATE $notelinks_table set persfamID = \"$persfam1\" WHERE persfamID = \"$persfam2\" AND gedcom = \"$tree\" $wherestr";
  tng_query($query);
}

$s1row = $s2row = "";
if ($sourceID1) {
  $query = "SELECT *, DATE_FORMAT(changedate,\"%d %b %Y %H:%i:%s\") as changedate FROM $sources_table WHERE sourceID = \"$sourceID1\" and gedcom = \"$tree\"";
  $result = tng_query($query);
  if ($result && tng_num_rows($result)) {
    $s1row = tng_fetch_assoc($result);
    tng_free_result($result);
  } else {
    $sourceID1 = $sourceID2 = "";
  }
}

@set_time_limit(0);
if (!$mergeaction) {
  $cshorttitle = "yes";
  $clongtitle = "yes";
}
if ($mergeaction == uiTextSnippet('nextmatch') || $mergeaction == uiTextSnippet('nextdup')) {
  if ($mergeaction == uiTextSnippet('nextmatch')) {
    $wherestr2 = $sourceID2 ? " AND sourceID > \"$sourceID2\"" : "";
    $wherestr2 .= $sourceID1 ? " AND sourceID > \"$sourceID1\"" : "";

    $wherestr = $sourceID1 ? "AND sourceID > \"$sourceID1\"" : "";
    $largechunk = 1000;
    $nextchunk = -1;
    $numrows = 0;
    $still_looking = 1;
    $sourceID2 = "";

    do {
      $nextone = $nextchunk + 1;
      $nextchunk += $largechunk;

      $query = "SELECT * FROM $sources_table WHERE gedcom = \"$tree\" $wherestr ORDER BY sourceID LIMIT $nextone, $largechunk";
      $result = tng_query($query);
      $numrows = tng_num_rows($result);
      if ($result && $numrows) {
        while ($still_looking && $row = tng_fetch_assoc($result)) {
          //echo "compare $row['firstname'] $row['lastname']<br>\n";
          $wherestr2 = addCriteria($row);

          $query = "SELECT * FROM $sources_table WHERE sourceID > \"{$row['sourceID']}\" AND gedcom = \"$tree\" $wherestr2 ORDER BY sourceID LIMIT 1";
          //echo "q2: $query<br>\n";
          $result2 = tng_query($query);
          if ($result2 && tng_num_rows($result2)) {
            //set sourceID1, sourceID2
            $s1row = $row;
            $sourceID1 = $s1row['sourceID'];
            $s2row = tng_fetch_assoc($result2);
            //echo "found $s2row['title'] $s2row['shorttitle']<br>\n";
            $sourceID2 = $s2row['sourceID'];
            tng_free_result($result2);
            $still_looking = 0;
          }
        }
        tng_free_result($result);
      }
    } while ($numrows && $still_looking);
    if (!$sourceID2) {
      $sourceID1 = $s1row = "";
    }
  } else {
    //search with sourceID1 for next duplicate
    $wherestr2 = $sourceID2 ? " AND sourceID > \"$sourceID2\"" : "";
    $wherestr2 .= addCriteria($s1row);

    $query = "SELECT * FROM $sources_table WHERE sourceID != \"{$s1row['sourceID']}\" AND gedcom = \"$tree\" $wherestr2 ORDER BY sourceID LIMIT 1";
    $result2 = tng_query($query);
    if ($result2 && tng_num_rows($result2)) {
      $s2row = tng_fetch_assoc($result2);
      $sourceID2 = $s2row['sourceID'];
      tng_free_result($result2);
    } else {
      $sourceID2 = "";
    }
  }
} elseif ($sourceID2) {
  $query = "SELECT *, DATE_FORMAT(changedate,\"%d %b %Y %H:%i:%s\") as changedate FROM $sources_table WHERE sourceID = \"$sourceID2\" AND gedcom = \"$tree\"";
  $result2 = tng_query($query);
  if ($result2 && tng_num_rows($result2) && $sourceID1 != $sourceID2) {
    $s2row = tng_fetch_assoc($result2);
    $sourceID2 = $s2row['sourceID'];
    tng_free_result($result2);
  } else {
    $mergeaction = uiTextSnippet('comprefresh');
    $sourceID2 = "";
  }
}
if ($mergeaction == uiTextSnippet('merge')) {
  $updatestr = "";

  foreach ($_POST as $key => $value) {
    $prefix = substr($key, 0, 2);
    switch ($prefix) {
      case "s2":
        $varname = substr($key, 2);
        $s1row[$varname] = $s2row[$varname];
        $updatestr .= ", $varname = \"$s1row[$varname]\" ";
        doNotes($sourceID1, $sourceID2, $varname);
        break;
      case "ev":
        if (strpos($key, "::")) {
          $halves = explode("::", substr($key, 5));
          $varname = substr(strstr($halves[0], "_"), 1);
          $query = "DELETE from $events_table WHERE persfamID = \"$sourceID1\" AND gedcom = \"$tree\" and eventID = \"$varname\"";
          $evresult = tng_query($query);
          $varname = substr(strstr($halves[1], "_"), 1);

          $query = "SELECT eventID FROM $events_table WHERE persfamID = \"$sourceID2\" AND  gedcom = \"$tree\" and eventID = \"$varname\"";
          $evresult = tng_query($query);
          while ($evrow = tng_fetch_assoc($evresult)) {
            doNotes($sourceID1, $sourceID2, $evrow['eventID']);
          }
          tng_free_result($evresult);
        } else {
          $varname = substr($key, 5);
          doNotes($sourceID1, $sourceID2, $varname);
        }

        $query = "UPDATE $events_table set persfamID = \"$sourceID1\" WHERE persfamID = \"$sourceID2\" AND gedcom = \"$tree\" AND eventID = \"$varname\"";
        $evresult = @tng_query($query);
        break;
    }
  }
  if ($ccombinenotes) {
    doNotes($sourceID1, $sourceID2, "general");

    //convert all remaining notes and citations
    $query = "UPDATE $notelinks_table set persfamID = \"$sourceID1\" WHERE persfamID = \"$sourceID2\" AND gedcom = \"$tree\"";
    $noteresult = tng_query($query);
  }
  if ($updatestr) {
    $updatestr = substr($updatestr, 2);
    $query = "UPDATE $sources_table set $updatestr WHERE sourceID = \"$sourceID1\" AND gedcom = \"$tree\"";
    $combresult = tng_query($query);
  }

  $query = "DELETE from $sources_table WHERE sourceID = \"$sourceID2\" AND gedcom = \"$tree\"";
  $combresult = tng_query($query);

  //delete remaining notes & events for source 2
  $query = "DELETE from $events_table WHERE persfamID = \"$sourceID2\" AND gedcom = \"$tree\"";
  $combresult = tng_query($query);

  $query = "DELETE from $notelinks_table WHERE persfamID = \"$sourceID2\" AND gedcom = \"$tree\"";
  $combresult = tng_query($query);

  //point citations for s2 to s1
  $query = "UPDATE $citations_table set sourceID = \"$sourceID1\" WHERE sourceID = \"$sourceID2\" AND gedcom = \"$tree\"";
  $combresult = tng_query($query);

  //construct name for default photo 2
  $defaultphoto2 = $tree ? "$rootpath$photopath/$tree.$sourceID2.$photosext" : "$rootpath$photopath/$sourceID2.$photosext";
  if ($ccombineextras) {
    $query = "UPDATE $medialinks_table set personID = \"$sourceID1\", defphoto = \"\" WHERE personID = \"$sourceID2\" AND gedcom = \"$tree\"";
    $mediaresult = @tng_query($query);

    //construct name for default photo 1
    if (file_exists($defaultphoto2)) {
      $defaultphoto1 = $tree ? "$rootpath$photopath/$tree.$sourceID1.$photosext" : "$rootpath$photopath/$sourceID1.$photosext";
      if (!file_exists($defaultphoto1)) {
        rename($defaultphoto2, $defaultphoto1);
      } else {
        unlink($defaultphoto2);
      }
    }
  } else {
    $query = "DELETE FROM $medialinks_table WHERE personID = \"$sourceID2\" AND gedcom = \"$tree\"";
    $mediaresult = tng_query($query);

    if (file_exists($defaultphoto2)) {
      unlink($defaultphoto2);
    }
  }
  $sourceID2 = "";
  $s2row = "";
  adminwritelog(uiTextSnippet('merge') . ": $tree/$sourceID2 => $sourceID1");
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('merge'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="sources-merge">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('sources-merge', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_sources.php", uiTextSnippet('search'), "findsource"]);
    $navList->appendItem([$allow_add, "admin_newsource.php", uiTextSnippet('addnew'), "addsource"]);
    $navList->appendItem([$allow_edit && $allow_delete, "admin_mergesources.php", uiTextSnippet('merge'), "merge"]);
    echo $navList->build("merge");
    ?>
    <div><em><?php echo uiTextSnippet('choosemergesources'); ?></em><br><br>
      <form action="admin_mergesources.php" method='post' name='form1' id='form1'>
        <table class='table table-sm'>
          <tr>
            <td><?php echo uiTextSnippet('tree'); ?>:</td>
            <td>
              <select name='tree'>
                <?php
                $trees = "";
                while ($treerow = tng_fetch_assoc($treeresult)) {
                  $trees .= "			<option value=\"{$treerow['gedcom']}\"";
                  if ($treerow['gedcom'] == $tree) {
                    $trees .= " selected";
                  }
                  $trees .= ">{$treerow['treename']}</option>\n";
                }
                echo $trees;
                ?>
              </select>
            </td>
          </tr>
          <tr>
            <td></td>
          </tr>
        </table>
        <br>
        <table class='table table-sm'>
          <tr>
            <td>
              <div style="float:left"><?php echo uiTextSnippet('sourceid'); ?> 1: <input
                        type='text' name="sourceID1" id="sourceID1" size='10'
                        value="<?php echo $sourceID1; ?>">
                &nbsp;<?php echo uiTextSnippet('text_or'); ?>&nbsp;</div>
              <a href="#" onclick="return findItem('S', 'sourceID1', 'sourceTitle1', document.form1.tree.options[document.form1.tree.selectedIndex].value);"
                 title="<?php echo uiTextSnippet('find'); ?>">
                <img class='icon-sm' src='svg/magnifying-glass.svg'>
              </a>
            </td>
            <td width="80">&nbsp;</td>
            <td>
              <div style="float:left"><?php echo uiTextSnippet('sourceid'); ?> 2: <input
                        type='text' name="sourceID2" id="sourceID2" size='10'
                        value="<?php echo $sourceID2; ?>">
                &nbsp;<?php echo uiTextSnippet('text_or'); ?>&nbsp;</div>
              <a href="#" onclick="return findItem('S', 'sourceID2', 'sourceTitle2', document.form1.tree.options[document.form1.tree.selectedIndex].value);"
                 title="<?php echo uiTextSnippet('find'); ?>">
                <img class='icon-sm' src='svg/magnifying-glass.svg'>
              </a>
            </td>
          </tr>
          <tr>
            <td id="sourceTitle1"><?php if (isset($s1row['title'])) {
                echo truncateIt($s1row['title'], 100);
              } ?></td>
            <td width="80"></td>
            <td id="sourceTitle2"><?php if (isset($s2row['title'])) {
                echo truncateIt($s2row['title'], 100);
              } ?></td>
          </tr>
        </table>
        <br>
        <table class='table table-sm'>
          <tr>
            <td colspan='5'>
              <strong><?php echo uiTextSnippet('matchthese'); ?></strong>
            </td>
            <td></td>
            <td colspan='3'>
              <span><strong><?php echo uiTextSnippet('otheroptions'); ?></strong></span>
            </td>
          </tr>
          <tr>
            <td>
              <span>
                <input name='cshorttitle' type='checkbox' value='yes'<?php if ($cshorttitle) {echo " checked";} ?>> <?php echo uiTextSnippet('shorttitle'); ?>
                <br>
                <input name='clongtitle' type='checkbox' value='yes'<?php if ($clongtitle) {echo " checked";} ?>> <?php echo uiTextSnippet('title'); ?>
              </span>
            </td>
            <td></td>
            <td>
              <span>
                <input name='cauthor' type='checkbox' value='yes'<?php if ($cauthor == "yes") {echo " checked";} ?>> <?php echo uiTextSnippet('author'); ?>
                <br>
                <input name='cpublisher' type='checkbox' value='yes'<?php if ($cpublisher == "yes") {echo " checked";} ?>> <?php echo uiTextSnippet('publisher'); ?>
              </span>
            </td>
            <td></td>
            <td>
              <span>
                <input name='crepository' type='checkbox' value='yes'<?php if ($crepository == "yes") {echo " checked";} ?>> <?php echo uiTextSnippet('repository'); ?>
                <br>
                <input name='cactualtext' type='checkbox' value='yes'<?php if ($cactualtext == "yes") {echo " checked";} ?>> <?php echo uiTextSnippet('actualtext'); ?>
              </span>
            </td>
            <td></td>
            <td>
              <span>
                <input name='ccombinenotes' type='checkbox' value='yes'<?php if ($ccombinenotes == "yes") {echo " checked";} ?>> <?php echo uiTextSnippet('combinenotesonly'); ?>
                <br>
                <input name='ccombineextras' type='checkbox' value='yes'<?php if ($ccombineextras == "yes") {echo " checked";} ?>> <?php echo uiTextSnippet('combineextras'); ?>
              </span>
            </td>
            <td></td>
            <td>
              <span>
                <input name='cignoreblanks' type='checkbox' value='yes'<?php if ($cignoreblanks == "yes") {echo " checked";} ?>> <?php echo uiTextSnippet('ignoreblanks'); ?>
                <br>
              </span>
            </td>
          </tr>
        </table>
        <br>
        <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('nextmatch'); ?>">
        <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('nextdup'); ?>">
        <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('comprefresh'); ?>">
        <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('mswitch'); ?>"
               onClick="document.form1.mergeaction.value = '<?php echo uiTextSnippet('comprefresh'); ?>';
                       return switchsources();">
        <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('merge'); ?>"
               onClick="return validateForm();">
        <br><br>
        <table class='table table-sm'>
          <?php
          if (is_array($s1row)) {
            $eventlist = array();
            echo "<tr>\n";
            echo "<td colspan=\"3\"><input type='button' value=\"" . uiTextSnippet('edit') . "\" onClick=\"deepOpen('admin_editsource.php?sourceID={$s1row['sourceID']}&amp;tree=$tree&amp;cw=1','edit')\"></td>\n";
            if (is_array($s2row)) {
              echo "<td colspan=\"3\"><input type='button' value=\"" . uiTextSnippet('edit') . "\" onClick=\"deepOpen('admin_editsource.php?sourceID={$s2row['sourceID']}&amp;tree=$tree&amp;cw=1','edit')\"></td>\n";

              $query = "SELECT display, eventdate, eventplace, info, $events_table.eventtypeID as eventtypeID, $events_table.eventID as eventID FROM $events_table, $eventtypes_table WHERE persfamID = \"{$s2row['sourceID']}\" AND gedcom = \"$tree\" AND $events_table.eventtypeID = $eventtypes_table.eventtypeID ORDER BY ordernum";
              $evresult = tng_query($query);
              $eventcount = tng_num_rows($evresult);

              if ($evresult && $eventcount) {
                while ($event = tng_fetch_assoc($evresult)) {
                  $ekey = $event['eventID'];
                  $ename = "event$ekey";
                  $s2row[$ename] .= getEvent($event);
                  if ($eventlist[$ekey]) {
                    $eventlist[$ekey] .= "::" . "{$event['eventtypeID']}_{$event['eventID']}";
                  } else {
                    $eventlist[$ekey] = "{$event['eventtypeID']}_{$event['eventID']}";
                  }
                }
                tng_free_result($evresult);
              }
            }
            echo "</tr>\n";
            doRow("sourceID", "sourceid", "");
            doRow("shorttitle", "shorttitle", "s2shorttitle");
            doRow("title", "title", "s2title");
            doRow("author", "author", "s2author");
            doRow("callnum", "callnumber", "s2callnum");
            doRow("publisher", "publisher", "s2publisher");
            doRow("repoID", "repository", "s2repository");
            doRow("actualtext", "actualtext", "s2actualtext");
            $query = "SELECT display, eventdate, eventplace, info, $events_table.eventtypeID as eventtypeID, $events_table.eventID as eventID FROM $events_table, $eventtypes_table WHERE persfamID = \"{$s1row['sourceID']}\" AND gedcom = \"$tree\" AND $events_table.eventtypeID = $eventtypes_table.eventtypeID ORDER BY ordernum";
            $evresult = tng_query($query);
            $eventcount = tng_num_rows($evresult);

            if ($evresult && $eventcount) {
              while ($event = tng_fetch_assoc($evresult)) {
                $ekey = $event['eventID'];
                $ename = "event$ekey";
                $s1row[$ename] .= getEvent($event);
                if ($eventlist[$ekey]) {
                  $eventlist[$ekey] .= "::" . "{$event['eventtypeID']}_{$event['eventID']}";
                } else {
                  $eventlist[$ekey] = "{$event['eventtypeID']}_{$event['eventID']}";
                }
              }
              tng_free_result($evresult);
            }

            foreach ($eventlist as $key => $event) {
              $ename = "event$key";
              $inputname = "event$key";
              doRow($ename, "otherevents", $inputname);
            }
          } else {
            echo "<tr><td>" . uiTextSnippet('nomatches') . "</td></tr>";
          }
          ?>
        </table>
        <?php if ($sourceID1 || $sourceID2) { ?>
          <br>
          <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('nextmatch'); ?>">
          <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('nextdup'); ?>">
          <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('comprefresh'); ?>">
          <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('mswitch'); ?>"
                 onClick="document.form1.mergeaction.value = '<?php echo uiTextSnippet('comprefresh'); ?>';
                         return switchsources();">
          <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('merge'); ?>"
                 onClick="return validateForm();">
        <?php } ?>
      </form>
    </div>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/admin.js"></script>
  <script src="js/selectutils.js"></script>
  <script>
    var tnglitbox;
    function validateForm() {
      var rval = true;

      if (document.form1.sourceID1.value === '' || document.form1.sourceID2.value === '' || document.form1.sourceID1.value === document.form1.sourceID2.value)
        rval = false;
      else
        rval = confirm(textSnippet('confirmmergesources'));

      return rval;
    }

    function switchsources() {
      var formname = document.form1;

      if (formname.sourceID1.value && formname.sourceID2.value) {
        var temp = formname.sourceID1.value;

        formname.sourceID1.value = formname.sourceID2.value;
        formname.sourceID2.value = temp;

        return true;
      } else
        return false;
    }
  </script>
</body>
</html>
