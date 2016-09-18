<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

$query = "SELECT *, DATE_FORMAT(postdate,\"%d %b %Y %H:%i:%s\") AS postdate FROM temp_events WHERE tempID = '$tempID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$personID = $row['personID'];
$familyID = $row['familyID'];
$eventID = $row['eventID'];

//look up person or family
if ($row['type'] == 'I' || $row['type'] == 'C') {
  $tng_search_preview = $_SESSION['tng_search_preview'];
  $reviewmsg = uiTextSnippet('reviewpeople');

  $query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix, branch FROM $people_table WHERE personID = '$personID'";
  $result = tng_query($query);
  $prow = tng_fetch_assoc($result);
  tng_free_result($result);

  $persfamID = $personID;
  $rightbranch = checkbranch($prow['branch']);
  $rights = determineLivingPrivateRights($prow, $rightbranch);
  $prow['allow_living'] = $rights['living'];
  $prow['allow_private'] = $rights['private'];

  $name = getName($prow);
  
  $teststr = "<br>\n";
  $teststr .= "<a href=\"peopleShowPerson.php?personID=$personID\" title=\"<?php echo uiTextSnippet('preview') ?>\">\n";
  $teststr .= "<img class='icon-sm' src='svg/eye.svg'>\n";
  $teststr .= "</a>\n";

  $editstr = "  | <a href=\"peopleEdit.php?personID=$personID\" target='_blank'>" . uiTextSnippet('edit') . '</a>';
} elseif ($row['type'] == 'F') {
  
  $query = "SELECT husband, wife FROM $families_table WHERE familyID = '$familyID'";
  $result = tng_query($query);
  $frow = tng_fetch_assoc($result);
  $hname = $wname = '';
  if ($frow['husband']) {
    $query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix, branch FROM $people_table WHERE personID = '{$frow['husband']}'";
    $result = tng_query($query);
    $prow = tng_fetch_assoc($result);
    $rightbranch = checkbranch($prow['branch']);
    $prights = determineLivingPrivateRights($prow, $rightbranch);
    $prow['allow_living'] = $prights['living'];
    $prow['allow_private'] = $prights['private'];
    tng_free_result($result);
    $hname = getName($prow);
  }
  if ($frow['wife']) {
    $query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix, branch FROM $people_table WHERE personID = '{$frow['wife']}'";
    $result = tng_query($query);
    $prow = tng_fetch_assoc($result);
    $rightbranch = checkbranch($prow['branch']);
    $prights = determineLivingPrivateRights($prow, $rightbranch);
    $prow['allow_living'] = $prights['living'];
    $prow['allow_private'] = $prights['private'];
    tng_free_result($result);
    $wname = getName($prow);
  }
  $persfamID = $familyID;
  $plus = $hname && $wname ? ' + ' : '';
  $name = "$hname$plus$wname";

  $checkbranch = 1;

  $teststr = "<br>\n";
  $teststr .= "<a href=\"familiesShowFamily.php?familyID=$familyID\" title=\"<?php echo uiTextSnippet('preview') ?>\">\n";
  $teststr .= "<img class='icon-sm' src='svg/eye.svg'>\n";
  $teststr .= "</a>\n";
  $editstr = "  | <a href=\"familiesEdit.php?familyID=$familyID\" target='_blank'>" . uiTextSnippet('edit') . '</a>';
}

if (!$allowEdit || !$rightbranch) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}

if (is_numeric($eventID)) {
  //custom event type
  $datefield = 'eventdate';
  $placefield = 'eventplace';
  $factfield = 'info';

  $query = "SELECT eventdate, eventplace, info FROM events WHERE eventID = '$eventID'";
  $result = tng_query($query);
  $evrow = tng_fetch_assoc($result);
  tng_free_result($result);

  $query = "SELECT display, tag FROM eventtypes, events WHERE eventID = $eventID AND eventtypes.eventtypeID = events.eventtypeID";
  $evresult = tng_query($query);
  $evtrow = tng_fetch_assoc($evresult);

  if ($evtrow['display']) {
    $dispvalues = explode('|', $evtrow['display']);
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
    case 'TITL':
      $factfield = 'title';
      break;
    case 'NPFX':
      $factfield = 'prefix';
      break;
    case 'NSFX':
      $factfield = 'suffix';
      break;
    case 'NICK':
      $factfield = 'nickname';
      break;
    case 'BIRT':
      $datefield = 'birthdate';
      $placefield = 'birthplace';
      break;
    case 'CHR':
      $datefield = 'altbirthdate';
      $placefield = 'altbirthplace';
      break;
    case 'BAPL':
      $datefield = 'baptdate';
      $placefield = 'baptplace';
      break;
    case 'CONL':
      $datefield = 'confdate';
      $placefield = 'confplace';
      break;
    case 'INIT':
      $datefield = 'initdate';
      $placefield = 'initplace';
      break;
    case 'ENDL':
      $datefield = 'endldate';
      $placefield = 'endlplace';
      break;
    case 'DEAT':
      $datefield = 'deathdate';
      $placefield = 'deathplace';
      break;
    case 'BURI':
      $datefield = 'burialdate';
      $placefield = 'burialplace';
      break;
    case 'MARR':
      $datefield = 'marrdate';
      $placefield = 'marrplace';
      $factfield = 'marrtype';
      $needfamilies = 1;
      break;
    case 'DIV':
      $datefield = 'divdate';
      $placefield = 'divplace';
      $needfamilies = 1;
      break;
    case 'SLGS':
      $datefield = 'sealdate';
      $placefield = 'sealplace';
      $needfamilies = 1;
      break;
    case 'SLGC':
      $datefield = 'sealdate';
      $placefield = 'sealplace';
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
    $query = "SELECT $fieldstr FROM $families_table WHERE familyID = '$familyID'";
  } elseif ($needchildren) {
    $query = "SELECT $fieldstr FROM children WHERE familyID = '$familyID' AND personID = '$personID'";
  } else {
    $query = "SELECT $fieldstr FROM $people_table WHERE personID = '$personID'";
  }
  $result = tng_query($query);
  $evrow = tng_fetch_assoc($result);
  tng_free_result($result);

  $query = "SELECT count(eventID) AS evcount FROM events WHERE persfamID = '$persfamID' AND eventID = '$eventID'";
  $morelinks = tng_query($query);
  $more = tng_fetch_assoc($morelinks);
  $gotmore = $more['evcount'] ? '*' : '';
  tng_free_result($morelinks);

  $displayval = uiTextSnippet($eventID);
}
$query = "SELECT count(ID) AS notecount FROM notelinks WHERE persfamID = '$persfamID' AND eventID = '$eventID'";
$notelinks = tng_query($query);
$note = tng_fetch_assoc($notelinks);
$gotnotes = $note['notecount'] ? '*' : '';
tng_free_result($notelinks);

$citequery = "SELECT count(citationID) AS citecount FROM citations WHERE persfamID = '$persfamID' AND eventID = '$eventID'";
$citeresult = tng_query($citequery) or die(uiTextSnippet('cannotexecutequery') . ": $citequery");
$cite = tng_fetch_assoc($citeresult);
$gotcites = $cite['citecount'] ? '*' : '';
tng_free_result($citeresult);

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('review'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    $hmsg = $row['type'] == 'I' ? 'people' : 'families';
    echo $adminHeaderSection->build($hmsg . '-review', $message);
    $navList = new navList('');
    if ($row['type'] == 'I') {
      $navList->appendItem([true, 'peopleBrowse.php', uiTextSnippet('browse'), 'findperson']);
      $navList->appendItem([$allowAdd, 'peopleAdd.php', uiTextSnippet('add'), 'addperson']);
      $navList->appendItem([$allowEdit, 'admin_findreview.php?type=I', uiTextSnippet('review'), 'review']);
      $navList->appendItem([$allowEdit && $allowDelete, 'peopleMerge.php', uiTextSnippet('merge'), 'merge']);
    } else {
      $navList->appendItem([true, 'familiesBrowse.php', uiTextSnippet('browse'), 'findperson']);
      $navList->appendItem([$allowAdd, 'familiesAdd.php', uiTextSnippet('add'), 'addfamily']);
      $navList->appendItem([$allowEdit, 'admin_findreview.php?type=F', uiTextSnippet('review'), 'review']);
    }
    echo $navList->build('review');
    ?>
    <span class='h4'><?php echo "$persfamID: $name</strong> $teststr $editstr"; ?></span><br>
    <form action="admin_savereview.php" method='post' name='form1'>
      <table>
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
          echo '<tr><td>' . uiTextSnippet('eventdate') . ": </span></td><td><span>{$evrow[$datefield]}</td></tr>\n";
          echo '<tr><td><strong>' . uiTextSnippet('suggested') . ":</strong></td><td colspan='2'>\n";
          echo "<input name='newdate' type='text' value=\"{$row['eventdate']}\" onblur=\"checkDate(this);\">\n";
          echo "</td></tr>\n";
        }
        if ($placefield) {
          $row['eventplace'] = preg_replace('/\"/', '&#34;', $row['eventplace']);
          echo '<tr><td>' . uiTextSnippet('eventplace') . ":</td><td><span>{$evrow[$placefield]}</td></tr>\n";
          echo '<tr><td><strong>' . uiTextSnippet('suggested') . ":</strong></td><td><input class='verylongfield' id='newplace' name='newplace' type='text' size='40' value=\"{$row['eventplace']}\"></td>";
          echo "<td>\n";
            echo "<a href='#' onclick=\"return openFindPlaceForm('newplace');\" title='" . uiTextSnippet('find') . "'>\n";
            echo "<img class='icon-sm' src='svg/magnifying-glass.svg'>\n";
            echo "</a>\n";
          echo "</td></tr>\n";
        }
        if ($factfield) {
          $row['info'] = preg_replace('/\"/', '&#34;', $row['info']);
          echo '<tr><td>' . uiTextSnippet('detail') . ":</td><td>{$row[$factfield]}</td></tr>\n";
          echo '<tr><td><strong>' . uiTextSnippet('suggested') . ":</strong></td><td colspan='2'><textarea cols=\"60\" rows=\"4\" name=\"newinfo\">{$row['info']}</textarea></td></tr>\n";
        }
        $row['note'] = preg_replace('/\"/', '&#34;', $row['note']);
        ?>
        <tr>
          <td>&nbsp;</td>
          <td>
            <?php
            if (!is_numeric($eventID)) {
              $iconColor = $gotmore ? 'icon-info' : 'icon-muted';
              echo "<a class='event-more' href='#' title='" . uiTextSnippet('more') . "' data-event-id='$eventID' data-persfam-id='$persfamID'>\n";
              echo "<img class='icon-sm icon-right icon-more $iconColor' data-event-id='$label' data-src='svg/plus.svg'>\n";
              echo "</a>\n";
            }
            $iconColor = $gotnotes ? 'icon-info' : 'icon-muted';
            echo "<a class='event-notes' href='#' title='" . uiTextSnippet('notes') . "' data-event-id='$eventID' data-persfam-id='$persfamID'>\n";
            echo "<img class='icon-sm icon-right icon-notes $iconColor' data-src='svg/documents.svg'>\n";
            echo "</a>\n";

            $iconColor = $gotcites ? 'icon-info' : 'icon-muted';
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
          <td><textarea cols="60" rows='4'
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
      <input name='choice' type='hidden' value="<?php echo uiTextSnippet('savedel'); ?>">
      <input type='submit' value="<?php echo uiTextSnippet('savedel'); ?>">
      <input type='submit' value="<?php echo uiTextSnippet('postpone'); ?>"
             onClick="document.form1.choice.value = '<?php echo uiTextSnippet('postpone'); ?>';">
      <input type='submit' value="<?php echo uiTextSnippet('igndel'); ?>"
             onClick="document.form1.choice.value = '<?php echo uiTextSnippet('igndel'); ?>';">
      <br>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <?php require_once 'eventlib.php'; ?>
<script>
    var tnglitbox;
    var preferEuro = <?php echo($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : 'false'); ?>;
    var preferDateFormat = '<?php echo $preferDateFormat; ?>';
</script>
<script src="js/selectutils.js"></script>
<script src="js/datevalidation.js"></script>
<script src="js/citations.js"></script>
<script>
    var persfamID = "<?php echo $personID; ?>";
</script>
</body>
</html>

