<?php
include("begin.php");
include("adminlib.php");

$admin_login = true;
include("checklogin.php");
include("version.php");

$query = "SELECT *, DATE_FORMAT(postdate,\"%d %b %Y %H:%i:%s\") as postdate FROM $temp_events_table WHERE tempID = \"$tempID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$tree = $row['gedcom'];
$personID = $row['personID'];
$familyID = $row['familyID'];
$eventID = $row['eventID'];

$righttree = checktree($tree);

//look up person or family
if ($row['type'] == 'I' || $row['type'] == "C") {
  $tng_search_preview = $_SESSION['tng_search_preview'];
  $reviewmsg = uiTextSnippet('reviewpeople');

  $query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix, gedcom, branch FROM $people_table WHERE personID = \"$personID\" AND gedcom = \"$tree\"";
  $result = tng_query($query);
  $prow = tng_fetch_assoc($result);
  tng_free_result($result);

  $persfamID = $personID;
  $rightbranch = $righttree ? checkbranch($prow['branch']) : false;
  $rights = determineLivingPrivateRights($prow, $righttree, $rightbranch);
  $prow['allow_living'] = $rights['living'];
  $prow['allow_private'] = $rights['private'];

  $name = getName($prow);
  
  $teststr = "<br>\n";
  $teststr .= "<a href=\"getperson.php?personID=$personID&amp;tree=$tree\" title=\"<?php echo uiTextSnippet('preview') ?>\">\n";
  $teststr .= "<img class='icon-sm' src='svg/eye.svg'>\n";
  $teststr .= "</a>\n";

  $editstr = "  | <a href=\"admin_editperson.php?personID=$personID&amp;tree=$tree\" target='_blank'>" . uiTextSnippet('edit') . "</a>";
} elseif ($row['type'] == 'F') {
  
  $query = "SELECT husband, wife FROM $families_table WHERE familyID = \"$familyID\" AND gedcom = \"$tree\"";
  $result = tng_query($query);
  $frow = tng_fetch_assoc($result);
  $hname = $wname = "";
  if ($frow['husband']) {
    $query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix, gedcom, branch FROM $people_table WHERE personID = \"{$frow['husband']}\" AND gedcom = \"$tree\"";
    $result = tng_query($query);
    $prow = tng_fetch_assoc($result);
    $rightbranch = $righttree ? checkbranch($prow['branch']) : false;
    $prights = determineLivingPrivateRights($prow, $righttree, $rightbranch);
    $prow['allow_living'] = $prights['living'];
    $prow['allow_private'] = $prights['private'];
    tng_free_result($result);
    $hname = getName($prow);
  }
  if ($frow['wife']) {
    $query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix, gedcom, branch FROM $people_table WHERE personID = \"{$frow['wife']}\" AND gedcom = \"$tree\"";
    $result = tng_query($query);
    $prow = tng_fetch_assoc($result);
    $rightbranch = $righttree ? checkbranch($prow['branch']) : false;
    $prights = determineLivingPrivateRights($prow, $righttree, $rightbranch);
    $prow['allow_living'] = $prights['living'];
    $prow['allow_private'] = $prights['private'];
    tng_free_result($result);
    $wname = getName($prow);
  }

  $persfamID = $familyID;
  $plus = $hname && $wname ? " + " : "";
  $name = "$hname$plus$wname";

  $checkbranch = 1;

  $teststr = "<br>\n";
  $teststr .= "<a href=\"familygroup.php?familyID=$familyID&amp;tree=$tree\" title=\"<?php echo uiTextSnippet('preview') ?>\">\n";
  $teststr .= "<img class='icon-sm' src='svg/eye.svg'>\n";
  $teststr .= "</a>\n";
  $editstr = "  | <a href=\"admin_editfamily.php?familyID=$familyID&amp;tree=$tree\" target='_blank'>" . uiTextSnippet('edit') . "</a>";
}

if (!$allow_edit || ($assignedtree && $assignedtree != $tree) || !$rightbranch) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

if (is_numeric($eventID)) {
  //custom event type
  $datefield = "eventdate";
  $placefield = "eventplace";
  $factfield = "info";

  $query = "SELECT eventdate, eventplace, info FROM $events_table WHERE eventID = \"$eventID\"";
  $result = tng_query($query);
  $evrow = tng_fetch_assoc($result);
  tng_free_result($result);

  $query = "SELECT display, tag FROM $eventtypes_table, $events_table WHERE eventID = $eventID AND $eventtypes_table.eventtypeID = $events_table.eventtypeID";
  $evresult = tng_query($query);
  $evtrow = tng_fetch_assoc($evresult);

  if ($evtrow['display']) {
    $dispvalues = explode("|", $evtrow['display']);
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
      $displayval = $evtrow['display'];
    }
  } elseif ($evtrow['tag']) {
    $displayval = $eventtype['tag'];
  } else {
    $displayval = uiTextSnippet($eventID);
  }
} else {
  //standard, do switch
  $needfamilies = 0;
  $needchildren = 0;
  switch ($eventID) {
    case "TITL":
      $factfield = "title";
      break;
    case "NPFX":
      $factfield = "prefix";
      break;
    case "NSFX":
      $factfield = "suffix";
      break;
    case "NICK":
      $factfield = "nickname";
      break;
    case "BIRT":
      $datefield = "birthdate";
      $placefield = "birthplace";
      break;
    case "CHR":
      $datefield = "altbirthdate";
      $placefield = "altbirthplace";
      break;
    case "BAPL":
      $datefield = "baptdate";
      $placefield = "baptplace";
      break;
    case "CONL":
      $datefield = "confdate";
      $placefield = "confplace";
      break;
    case "INIT":
      $datefield = "initdate";
      $placefield = "initplace";
      break;
    case "ENDL":
      $datefield = "endldate";
      $placefield = "endlplace";
      break;
    case "DEAT":
      $datefield = "deathdate";
      $placefield = "deathplace";
      break;
    case "BURI":
      $datefield = "burialdate";
      $placefield = "burialplace";
      break;
    case "MARR":
      $datefield = "marrdate";
      $placefield = "marrplace";
      $factfield = "marrtype";
      $needfamilies = 1;
      break;
    case "DIV":
      $datefield = "divdate";
      $placefield = "divplace";
      $needfamilies = 1;
      break;
    case "SLGS":
      $datefield = "sealdate";
      $placefield = "sealplace";
      $needfamilies = 1;
      break;
    case "SLGC":
      $datefield = "sealdate";
      $placefield = "sealplace";
      $needchildren = 1;
      break;
  }

  $fieldstr = $datefield;
  if ($placefield) {
    $fieldstr .= $fieldstr ? ", $placefield" : $placefield;
  }
  if ($factfield) {
    $fieldstr .= $fieldstr ? ", $factfield" : $factfield;
  }

  if ($needfamilies) {
    $query = "SELECT $fieldstr FROM $families_table WHERE familyID = \"$familyID\" AND gedcom = \"$tree\"";
  } elseif ($needchildren) {
    $query = "SELECT $fieldstr FROM $children_table WHERE familyID = \"$familyID\" AND personID = \"$personID\" AND gedcom = \"$tree\"";
  } else {
    $query = "SELECT $fieldstr FROM $people_table WHERE personID = \"$personID\" AND gedcom = \"$tree\"";
  }
  $result = tng_query($query);
  $evrow = tng_fetch_assoc($result);
  tng_free_result($result);

  $query = "SELECT count(eventID) as evcount FROM $events_table WHERE persfamID=\"$persfamID\" AND gedcom =\"$tree\" AND eventID =\"$eventID\"";
  $morelinks = tng_query($query);
  $more = tng_fetch_assoc($morelinks);
  $gotmore = $more['evcount'] ? "*" : "";
  tng_free_result($morelinks);

  $displayval = uiTextSnippet($eventID);
}

$query = "SELECT treename FROM $trees_table WHERE gedcom = \"$tree\"";
$result = tng_query($query);
$treerow = tng_fetch_assoc($result);
tng_free_result($result);

$query = "SELECT count(ID) as notecount FROM $notelinks_table WHERE persfamID=\"$persfamID\" AND gedcom =\"$tree\" AND eventID =\"$eventID\"";
$notelinks = tng_query($query);
$note = tng_fetch_assoc($notelinks);
$gotnotes = $note['notecount'] ? "*" : "";
tng_free_result($notelinks);

$citequery = "SELECT count(citationID) as citecount FROM $citations_table WHERE persfamID=\"$persfamID\" AND gedcom =\"$tree\" AND eventID = \"$eventID\"";
$citeresult = tng_query($citequery) or die(uiTextSnippet('cannotexecutequery') . ": $citequery");
$cite = tng_fetch_assoc($citeresult);
$gotcites = $cite['citecount'] ? "*" : "";
tng_free_result($citeresult);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('review'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <?php
  $hmsg = $row['type'] == 'I' ? 'people' : 'families';
  echo $adminHeaderSection->build($hmsg . '-review', $message);
  $navList = new navList('');
  if ($row['type'] == 'I') {
    $navList->appendItem([true, "admin_people.php", uiTextSnippet('search'), "findperson"]);
    $navList->appendItem([$allow_add, "admin_newperson.php", uiTextSnippet('addnew'), "addperson"]);
    $navList->appendItem([$allow_edit, "admin_findreview.php?type=I", uiTextSnippet('review'), "review"]);
    $navList->appendItem([$allow_edit && $allow_delete, "admin_merge.php", uiTextSnippet('merge'), "merge"]);
  } else {
    $navList->appendItem([true, "admin_families.php", uiTextSnippet('search'), "findperson"]);
    $navList->appendItem([$allow_add, "admin_newfamily.php", uiTextSnippet('addnew'), "addfamily"]);
    $navList->appendItem([$allow_edit, "admin_findreview.php?type=F", uiTextSnippet('review'), "review"]);
  }
  echo $navList->build("review");
  ?>
  <table class='table table-sm'>
    <tr>
      <td>
				<span class='h4'><?php echo "$persfamID: $name</strong> $teststr $editstr"; ?><br><br>
					<div>

            <form action="admin_savereview.php" method='post' name='form1'>
              <table>
                <tr>
                  <td><span><?php echo uiTextSnippet('tree'); ?>:</span></td>
                  <td><?php echo $treerow['treename']; ?></td>
                </tr>
                <tr>
                  <td colspan='2'>&nbsp;</td>
                </tr>
                <tr>
                  <td><span class='h4'><?php echo uiTextSnippet('event'); ?>
                      :</span></td>
                  <td><span class='h4'><?php echo $displayval; ?></span></td>
                </tr>
                <tr>
                  <td colspan='2'>&nbsp;</td>
                </tr>
                <?php
                if ($datefield) {
                  echo "<tr><td>" . uiTextSnippet('eventdate') . ": </span></td><td><span>{$evrow[$datefield]}</td></tr>\n";
                  echo "<tr><td><strong>" . uiTextSnippet('suggested') . ":</strong></td><td colspan='2'>\n";
                  echo "<input name='newdate' type='text' value=\"{$row['eventdate']}\" onblur=\"checkDate(this);\">\n";
                  echo "</td></tr>\n";
                }
                if ($placefield) {
                  $row['eventplace'] = preg_replace('/\"/', '&#34;', $row['eventplace']);
                  echo "<tr><td>" . uiTextSnippet('eventplace') . ":</td><td><span>{$evrow[$placefield]}</td></tr>\n";
                  echo "<tr><td><strong>" . uiTextSnippet('suggested') . ":</strong></td><td><input class='verylongfield' id='newplace' name='newplace' type='text' size='40' value=\"{$row['eventplace']}\"></td>";
                  echo "<td>\n";
                    echo "<a href='#' onclick=\"return openFindPlaceForm('newplace');\" title='" . uiTextSnippet('find') . "'>\n";
                    echo "<img class='icon-sm' src='svg/magnifying-glass.svg'>\n";
                    echo "</a>\n";
                  echo "</td></tr>\n";
                }
                if ($factfield) {
                  $row['info'] = preg_replace('/\"/', '&#34;', $row['info']);
                  echo "<tr><td>" . uiTextSnippet('detail') . ":</td><td>{$row[$factfield]}</td></tr>\n";
                  echo "<tr><td><strong>" . uiTextSnippet('suggested') . ":</strong></td><td colspan='2'><textarea cols=\"60\" rows=\"4\" name=\"newinfo\">{$row['info']}</textarea></td></tr>\n";
                }
                $row['note'] = preg_replace('/\"/', '&#34;', $row['note']);
                ?>
                <tr>
                  <td>&nbsp;</td>
                  <td>
                    <?php
                    if (!is_numeric($eventID)) {
                      $iconColor = $gotmore ? "icon-info" : "icon-muted";
                      echo "<a class='event-more' href='#' title='" . uiTextSnippet('more') . "' data-event-id='$eventID' data-persfam-id='$persfamID' data-tree='$tree'>\n";
                      echo "<img class='icon-sm icon-right icon-more $iconColor' data-event-id='$label' data-src='svg/plus.svg'>\n";
                      echo "</a>\n";
                    }
                    $iconColor = $gotnotes ? "icon-info" : "icon-muted";
                    echo "<a class='event-notes' href='#' title='" . uiTextSnippet('notes') . "' data-event-id='$eventID' data-persfam-id='$persfamID'>\n";
                    echo "<img class='icon-sm icon-right icon-notes $iconColor' data-src='svg/documents.svg'>\n";
                    echo "</a>\n";

                    $iconColor = $gotcites ? "icon-info" : "icon-muted";
                    echo "<a class='event-citations' href='#' title='" . uiTextSnippet('citations') . "' data-event-id='$eventID' data-persfam-id='$persfamID'>\n";
                    echo "<img class='icon-sm icon-right icon-citations $iconColor' data-src='svg/archive.svg'>\n";
                    echo "</a>\n";
                    ?>
                  </td>
                </tr>
                <tr>
                  <td colspan='2'>&nbsp;</td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('usernotes'); ?>:</td>
                  <td><textarea cols="60" rows="4"
                                             name="usernote"><?php echo $row['note']; ?></textarea>
                  </td>
                </tr>
                <tr>
                  <td colspan='2'>&nbsp;</td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('postdate'); ?>:</td>
                  <td><?php echo "{$row['postdate']} ({$row['user']})"; ?></td>
                </tr>
              </table>
              <br>
              <input name='tempID' type='hidden' value="<?php echo $tempID; ?>">
              <input name='type' type='hidden' value="<?php echo $row['type']; ?>">
              <input name='tree' type='hidden' value="<?php echo $tree; ?>">
              <input name='choice' type='hidden' value="<?php echo uiTextSnippet('savedel'); ?>">
              <input type='submit' value="<?php echo uiTextSnippet('savedel'); ?>">
              <input type='submit' value="<?php echo uiTextSnippet('postpone'); ?>"
                     onClick="document.form1.choice.value = '<?php echo uiTextSnippet('postpone'); ?>';">
              <input type='submit' value="<?php echo uiTextSnippet('igndel'); ?>"
                     onClick="document.form1.choice.value = '<?php echo uiTextSnippet('igndel'); ?>';">
              <br>
            </form>
          </div>
      </td>
    </tr>

  </table>

  <?php
  echo $adminFooterSection->build();
echo scriptsManager::buildScriptElements($flags, 'admin');
?>
<?php include_once("eventlib.php"); ?>
<script>
  var tnglitbox;
  var preferEuro = <?php echo($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : "false"); ?>;
  var preferDateFormat = '<?php echo $preferDateFormat; ?>';
  
  var tree = '<?php echo $tree; ?>';
</script>
<script src="js/selectutils.js"></script>
<script src="js/datevalidation.js"></script>
<script src="js/citations.js"></script>
<script>
  var persfamID = "<?php echo $personID; ?>";
</script>
</body>
</html>

