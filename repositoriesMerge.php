<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

require 'adminlog.php';

if (!$allowEdit || !$allowDelete) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
$wherestr = '';

function doRow($field, $textmsg, $boxname) {
  global $r1row;
  global $r2row;

  if ($field == 'addressID') {
    if ($r1row[$field]) {
      $r1field = '';
      if ($r1row['address1']) {
        $r1field .= $r1row['address1'] . '<br>';
      }
      if ($r1row['address2']) {
        $r1field .= $r1row['address2'] . '<br>';
      }
      if ($r1row['city']) {
        $r1field .= $r1row['city'];
      }
      if ($r1row['state']) {
        if ($r1row['city']) {
          $r1field .= ', ';
        }
        $r1field .= $r1row['state'];
      }
      if ($r1row['country']) {
        if ($r1row['city'] || $r1row['state']) {
          $r1field .= ', ';
        }
        $r1field .= $r1row['country'];
      }
      if (!$r1field) {
        $r1field = $r1row['addressID'];
      }
    }
    if ($r2row[$field]) {
      $r2field = '';
      if ($r2row['address1']) {
        $r2field .= $r2row['address1'] . '<br>';
      }
      if ($r2row['address2']) {
        $r2field .= $r2row['address2'] . '<br>';
      }
      if ($r2row['city']) {
        $r2field .= $r2row['city'];
      }
      if ($r2row['state']) {
        if ($r2row['city']) {
          $r2field .= ', ';
        }
        $r2field .= $r2row['state'];
      }
      if ($r2row['country']) {
        if ($r2row['city'] || $r2row['state']) {
          $r2field .= ', ';
        }
        $r2field .= $r2row['country'];
      }
      if (!$r2field) {
        $r2field = $r2row['addressID'];
      }
    }
  } else {
    $r1field = $r1row[$field];
    $r2field = $r2row[$field];
  }

  if ($r1field || $r2field) {
    echo "<tr>\n";
    echo "<td width='15%'><span>" . uiTextSnippet($textmsg) . ':</span></td>';
    echo "<td width=\"31%\"><span>$r1field&nbsp;</span></td>";
    if (is_array($r2row)) {
      echo "<td width='10'></td>";
      echo "<td width='15%'><span>" . uiTextSnippet($textmsg) . ':</span></td>';
      echo "<td width='5'><span>";
      if ($boxname) {
        if ($r2field) {
          echo "<input name=\"$boxname\" type='checkbox' value=\"$field\"";
          if ($r2row[$field] && !$r1row[$field]) {
            echo ' checked';
          }
          echo '>';
        } else {
          echo '&nbsp;';
        }
      } else {
        echo '&nbsp;';
      }
      echo '</span></td>';
      echo "<td width=\"31%\"><span>$r2field&nbsp;</span></td>";
    } else {
      echo "<td width='10'></td>";
      echo "<td width='15%'><span>" . uiTextSnippet($textmsg) . ':</span></td>';
      echo "<td width='5'><span>&nbsp;</span></td>";
      echo "<td width='31%'><span>&nbsp;</span></td>";
    }
    echo "</tr>\n";
  }
}

function getEvent($event) {
  global $mylanguage, $languagesPath;

  $dispvalues = explode('|', $event['display']);
  $numvalues = count($dispvalues);
  if ($numvalues > 1) {
    $displayval = '';
    for ($i = 0; $i < $numvalues; $i += 2) {
      $lang = $dispvalues[$i];
      if ($mylanguage == $languagesPath . $lang) {
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
    $eventstr2 .= ', ';
  }
  $eventstr2 .= $event['eventplace'];
  if ($eventstr2 && $event['info']) {
    $eventstr2 .= '. ';
  }
  $eventstr2 .= $event['info'] . "<br>\n";
  $eventstr .= $eventstr2;

  return $eventstr;
}

function addCriteria($row) {
  $criteria = '';
  $criteria .= ' AND reponame = "' . addslashes($row['reponame']) . '"';

  return $criteria;
}

function doNotes($persfam1, $persfam2, $varname) {
  global $ccombinenotes;

  if ($varname) {
    if ($varname == 'general') {
      $varname = '';
    }
    $wherestr = "AND eventID = \"$varname\"";
  } else {
    $wherestr = '';
  }

  if ($ccombinenotes != 'yes') {
    $query = "DELETE from notelinks WHERE persfamID = '$persfam1' $wherestr";
    tng_query($query);
  }
  $query = "UPDATE notelinks set persfamID = \"$persfam1\" WHERE persfamID = '$persfam2' $wherestr";
  tng_query($query);
}

$r1row = $r2row = '';
if ($repoID1) {
  $query = "SELECT reponame, repoID, repositories.addressID AS addressID, address1, address2, city, state, zip, country, DATE_FORMAT(changedate,\"%d %b %Y %H:%i:%s\") AS changedate FROM repositories LEFT JOIN $address_table ON repositories.addressID = $address_table.addressID WHERE repoID = '$repoID1'";
  $result = tng_query($query);
  if ($result && tng_num_rows($result)) {
    $r1row = tng_fetch_assoc($result);
    tng_free_result($result);
  } else {
    $repoID1 = $repoID2 = '';
  }
}

set_time_limit(0);
if ($mergeaction == uiTextSnippet('nextmatch') || $mergeaction == uiTextSnippet('nextdup')) {
  if ($mergeaction == uiTextSnippet('nextmatch')) {
    $wherestr2 = $repoID2 ? " AND repoID > \"$repoID2\"" : '';
    $wherestr2 .= $repoID1 ? " AND repoID > \"$repoID1\"" : '';

    $wherestr = $repoID1 ? "AND repoID > \"$repoID1\"" : '';
    $largechunk = 1000;
    $nextchunk = -1;
    $numrows = 0;
    $still_looking = 1;
    $repoID2 = '';

    do {
      $nextone = $nextchunk + 1;
      $nextchunk += $largechunk;

      $query = "SELECT * FROM repositories WHERE 1 $wherestr ORDER BY repoID LIMIT $nextone, $largechunk";
      $result = tng_query($query);
      $numrows = tng_num_rows($result);
      if ($result && $numrows) {
        while ($still_looking && $row = tng_fetch_assoc($result)) {
          $wherestr2 = addCriteria($row);

          $query = "SELECT * FROM repositories WHERE repoID > \"{$row['repoID']}\" $wherestr2 ORDER BY repoID";
          $result2 = tng_query($query);
          if ($result2 && tng_num_rows($result2)) {
            //set repoID1, repoID2
            $r1row = $row;
            $repoID1 = $r1row['repoID'];
            $r2row = tng_fetch_assoc($result2);
            //echo "found $r2row['title'] $r2row['shorttitle']<br>\n";
            $repoID2 = $r2row['repoID'];
            tng_free_result($result2);
            $still_looking = 0;
          }
        }
        tng_free_result($result);
      }
    } while ($numrows && $still_looking);
    if (!$repoID2) {
      $repoID1 = $r1row = '';
    }
  } else {
    //search with repoID1 for next duplicate
    $wherestr2 = $repoID2 ? " AND repoID > \"$repoID2\"" : '';
    $wherestr2 .= addCriteria($r1row);

    $query = "SELECT * FROM repositories WHERE repoID != \"{$r1row['repoID']}\" $wherestr2 ORDER BY repoID LIMIT 1";
    $result2 = tng_query($query);
    if ($result2 && tng_num_rows($result2)) {
      $r2row = tng_fetch_assoc($result2);
      $repoID2 = $r2row['repoID'];
      tng_free_result($result2);
    } else {
      $repoID2 = '';
    }
  }
} elseif ($repoID2) {
  $query = "SELECT reponame, repoID, repositories.addressID AS addressID, address1, address2, city, state, zip, country, DATE_FORMAT(changedate,\"%d %b %Y %H:%i:%s\") AS changedate FROM repositories LEFT JOIN $address_table ON repositories.addressID = $address_table.addressID WHERE repoID = '$repoID2'";
  $result2 = tng_query($query);
  if ($result2 && tng_num_rows($result2) && $repoID1 != $repoID2) {
    $r2row = tng_fetch_assoc($result2);
    $repoID2 = $r2row['repoID'];
    tng_free_result($result2);
  } else {
    $mergeaction = uiTextSnippet('comprefresh');
    $repoID2 = '';
  }
}
if ($mergeaction == uiTextSnippet('merge')) {
  $updatestr = '';

  foreach ($_POST as $key => $value) {
    $prefix = substr($key, 0, 2);
    switch ($prefix) {
      case 'p2':
        $varname = substr($key, 2);
        $r1row[$varname] = $r2row[$varname];
        $updatestr .= ", $varname = \"{$r1row[$varname]}\" ";
        doNotes($repoID1, $repoID2, $varname);
        break;
      case 'ev':
        if (strpos($key, '::')) {
          $halves = explode('::', substr($key, 5));
          $varname = substr(strstr($halves[0], '_'), 1);
          $query = "DELETE from events WHERE persfamID = '$repoID1' and eventID = \"$varname\"";
          $evresult = tng_query($query);
          $varname = substr(strstr($halves[1], '_'), 1);

          $query = "SELECT eventID FROM events WHERE persfamID = '$repoID2' AND eventID = '$varname'";
          $evresult = tng_query($query);
          while ($evrow = tng_fetch_assoc($evresult)) {
            doNotes($repoID1, $repoID2, $evrow['eventID']);
          }
          tng_free_result($evresult);
        } else {
          $varname = substr($key, 5);
          doNotes($repoID1, $repoID2, $varname);
        }

        $query = "UPDATE events set persfamID = \"$repoID1\" WHERE persfamID = '$repoID2' AND eventID = \"$varname\"";
        $evresult = tng_query($query);
        break;
    }
  }
  if ($ccombinenotes) {
    doNotes($repoID1, $repoID2, 'general');

    //convert all remaining notes and citations
    $query = "UPDATE notelinks set persfamID = \"$repoID1\" WHERE persfamID = '$repoID2'";
    $noteresult = tng_query($query);
  }
  if ($updatestr) {
    $updatestr = substr($updatestr, 2);
    $query = "UPDATE repositories set $updatestr WHERE repoID = '$repoID1'";
    $combresult = tng_query($query);
  }

  $query = "DELETE from repositories WHERE repoID = '$repoID2'";
  $combresult = tng_query($query);

  //delete remaining notes & events for repo 2
  $query = "DELETE from events WHERE persfamID = '$repoID2'";
  $combresult = tng_query($query);

  $query = "DELETE from notelinks WHERE persfamID = '$repoID2'";
  $combresult = tng_query($query);

  //point sources for r2 to r1
  $query = "UPDATE sources set repoID = \"$repoID1\" WHERE repoID = '$repoID2'";
  $combresult = tng_query($query);

  //construct name for default photo 2
  $defaultphoto2 = "$rootpath$photopath/$repoID2.$photosext";
  if ($ccombineextras) {
    $query = "UPDATE $medialinks_table set personID = \"$repoID1\", defphoto = \"\" WHERE personID = '$repoID2'";
    $mediaresult = tng_query($query);

    //construct name for default photo 1
    if (file_exists($defaultphoto2)) {
      $defaultphoto1 = "$rootpath$photopath/$repoID1.$photosext";
      if (!file_exists($defaultphoto1)) {
        rename($defaultphoto2, $defaultphoto1);
      }
      //else
      //unlink( $defaultphoto2 );
    }
  } else {
    $query = "DELETE FROM $medialinks_table WHERE personID = '$repoID2'";
    $mediaresult = tng_query($query);

    //if( file_exists( $defaultphoto2 ) )
    //unlink( $defaultphoto2 );
  }
  $repoID2 = '';
  $r2row = '';
  adminwritelog(uiTextSnippet('merge') . ": $repoID2 => $repoID1");
}
header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('merge'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('repositories-merge', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'repositoriesBrowse.php', uiTextSnippet('search'), 'findrepo']);
    $navList->appendItem([$allowAdd, 'repositoriesAdd.php', uiTextSnippet('add'), 'addrepo']);
    //    $navList->appendItem([$allowEdit && $allowDelete, 'repositoriesMerge.php', uiTextSnippet('merge'), 'merge']);
    echo $navList->build('merge');
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
        <div><em><?php echo uiTextSnippet('choosemergerepos'); ?></em><br><br>
          <form id='form1' name='form1' action="repositoriesMerge.php" method='post'>
            <br>
            <table>
              <tr>
                <td>
                  <div style="float:left">
                    <?php echo uiTextSnippet('repoid'); ?> 1: <input id='repoID1' name='repoID1' type='text' size='10' value="<?php echo $repoID1; ?>">
                    &nbsp;<?php echo uiTextSnippet('or'); ?>&nbsp;
                  </div>
                  <a href="#"  title="<?php echo uiTextSnippet('find'); ?>" onclick="return findItem('R', 'repoID1', 'reponame1');">
                    <img class='icon-sm' src='svg/magnifying-glass.svg'>
                  </a>
                </td>
                <td width="80">&nbsp;</td>
                <td>
                  <div style="float:left">
                    <?php echo uiTextSnippet('repoid'); ?> 2: <input id='repoID2' name='repoID2' type='text' size='10' value="<?php echo $repoID2; ?>">
                    &nbsp;<?php echo uiTextSnippet('or'); ?>&nbsp;
                  </div>
                  <a href="#" title="<?php echo uiTextSnippet('find'); ?>" onclick="return findItem('R', 'repoID2', 'reponame2');">
                    <img class='icon-sm' src='svg/magnifying-glass.svg'>
                  </a>
                </td>
              </tr>
              <tr>
                <td id="reponame1"><?php if (isset($r1row['reponame'])) {echo truncateIt($r1row['reponame'], 75);} ?></td>
                <td width="80"></td>
                <td id="reponame2"><?php if (isset($r2row['reponame'])) {echo truncateIt($r2row['reponame'], 75);} ?></td>
              </tr>
            </table>
            <br>
            <table>
              <tr>
                <td colspan='3'>
                  <span><strong><?php echo uiTextSnippet('otheroptions'); ?></strong></span></td>
              </tr>
              <tr>
                <td>
                    <span>
                      <input name='ccombinenotes' type='checkbox' value='yes'<?php if ($ccombinenotes == 'yes') {echo ' checked';} ?>> <?php echo uiTextSnippet('combinenotesonly'); ?>
                      <br>
                      <input name='ccombineextras' type='checkbox' value='yes'<?php if ($ccombineextras == 'yes') {echo ' checked';} ?>> <?php echo uiTextSnippet('combineextras'); ?>
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
                           return switchrepositories();">
            <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('merge'); ?>"
                   onClick="return validateForm();">
            <br><br>
            <table>
              <?php
              if (is_array($r1row)) {
                $eventlist = [];
                echo "<tr>\n";
                echo "<td colspan=\"3\"><input type='button' value=\"" . uiTextSnippet('edit') . "\" onClick=\"deepOpen('repositoriesEdit.php?repoID={$r1row['repoID']}&amp;cw=1','edit')\"></td>\n";
                if (is_array($r2row)) {
                  echo "<td colspan=\"3\"><input type='button' value=\"" . uiTextSnippet('edit') . "\" onClick=\"deepOpen('repositoriesEdit.php?repoID={$r2row['repoID']}&amp;cw=1','edit')\"></td>\n";

                  $query = "SELECT display, eventdate, eventplace, info, events.eventtypeID AS eventtypeID, events.eventID AS eventID FROM events, eventtypes WHERE persfamID = \"{$r2row['repoID']}\" AND events.eventtypeID = eventtypes.eventtypeID ORDER BY ordernum";
                  $evresult = tng_query($query);
                  $eventcount = tng_num_rows($evresult);

                  if ($evresult && $eventcount) {
                    while ($event = tng_fetch_assoc($evresult)) {
                      $ekey = $event['eventID'];
                      $ename = "event$ekey";
                      $r2row[$ename] .= getEvent($event);
                      if ($eventlist[$ekey]) {
                        $eventlist[$ekey] .= '::' . "{$event['eventtypeID']}_{$event['eventID']}";
                      } else {
                        $eventlist[$ekey] = "{$event['eventtypeID']}_{$event['eventID']}";
                      }
                    }
                    tng_free_result($evresult);
                  }
                }
                echo "</tr>\n";
                doRow('repoID', 'repoid', '');
                doRow('reponame', 'name', 'r2reponame');
                doRow('addressID', 'address', 'r2addressID');
                $query = "SELECT display, eventdate, eventplace, info, events.eventtypeID AS eventtypeID, events.eventID AS eventID FROM events, eventtypes WHERE persfamID = \"{$r1row['repoID']}\" AND events.eventtypeID = eventtypes.eventtypeID ORDER BY ordernum";
                $evresult = tng_query($query);
                $eventcount = tng_num_rows($evresult);

                if ($evresult && $eventcount) {
                  while ($event = tng_fetch_assoc($evresult)) {
                    $ekey = $event['eventID'];
                    $ename = "event$ekey";
                    $r1row[$ename] .= getEvent($event);
                    if ($eventlist[$ekey]) {
                      $eventlist[$ekey] .= '::' . "{$event['eventtypeID']}_{$event['eventID']}";
                    } else {
                      $eventlist[$ekey] = "{$event['eventtypeID']}_{$event['eventID']}";
                    }
                  }
                  tng_free_result($evresult);
                }

                foreach ($eventlist as $key => $event) {
                  $ename = "event$key";
                  $inputname = "event$key";
                  doRow($ename, 'otherevents', $inputname);
                }
              } else {
                echo '<tr><td>' . uiTextSnippet('nomatches') . '</td></tr>';
              }
              ?>
            </table>
            <?php
            if ($repoID1 || $repoID2) {
              ?>
              <br>
              <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('nextmatch'); ?>">
              <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('nextdup'); ?>">
              <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('comprefresh'); ?>">
              <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('mswitch'); ?>"
                     onClick="document.form1.mergeaction.value = '<?php echo uiTextSnippet('comprefresh'); ?>';
                             return switchrepositories();">
              <input name='mergeaction' type='submit' value="<?php echo uiTextSnippet('merge'); ?>"
                     onClick="return validateForm();">
              <?php
            }
            ?>
          </form>
        </div>
        </td>
      </tr>
    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/admin.js"></script>
  <script src="js/selectutils.js"></script>
  <script>
    var tnglitbox;
    function validateForm() {
      var rval = true;

      if (document.form1.repoID1.value === '' || document.form1.repoID2.value === '' || document.form1.repoID1.value === document.form1.repoID2.value)
        rval = false;
      else
        rval = confirm(textSnippet('confirmmergerepos'));

      return rval;
    }

    function switchrepositories() {
      var formname = document.form1;

      if (formname.repoID1.value && formname.repoID2.value) {
        var temp = formname.repoID1.value;

        formname.repoID1.value = formname.repoID2.value;
        formname.repoID2.value = temp;

        return true;
      } else
        return false;
    }
  </script>
</body>
</html>

