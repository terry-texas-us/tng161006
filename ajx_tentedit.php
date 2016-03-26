<?php
require 'tng_begin.php';

$factfield = $datefield = $placefield = "";

$righttree = checktree($tree);

if ($type == 'I' || $type == "C") {
  if ($type == 'I') {
    $personID = $persfamID;
    $familyID = "";
  } else { //type C
    $ids = explode("::", $persfamID);
    $personID = $ids[0];
    $familyID = $ids[1];
  }
  $result = getPersonSimple($tree, $personID);
  $namerow = tng_fetch_assoc($result);

  $rights = determineLivingPrivateRights($namerow, $righttree);
  $namerow['allow_living'] = $rights['living'];
  $namerow['allow_private'] = $rights['private'];

  $name = getName($namerow);
  tng_free_result($result);
} elseif ($type == 'F') {
  $personID = "";
  $familyID = $persfamID;

  $result = getFamilyData($tree, $familyID);
  $frow = tng_fetch_assoc($result);
  $hname = $wname = "";

  $rights = determineLivingPrivateRights($frow, $righttree);
  $frow['allow_living'] = $rights['living'];
  $frow['allow_private'] = $rights['private'];

  if ($frow['husband']) {
    $result = getPersonSimple($tree, $frow['husband']);
    $prow = tng_fetch_assoc($result);
    tng_free_result($result);

    $prights = determineLivingPrivateRights($prow, $righttree);
    $prow['allow_living'] = $prights['living'];
    $prow['allow_private'] = $prights['private'];

    $hname = getName($prow);
  }
  if ($frow['wife']) {
    $result = getPersonSimple($tree, $frow['wife']);
    $prow = tng_fetch_assoc($result);
    tng_free_result($result);

    $prights = determineLivingPrivateRights($prow, $righttree);
    $prow['allow_living'] = $prights['living'];
    $prow['allow_private'] = $prights['private'];

    $wname = getName($prow);
  }

  $persfamID = $familyID;
  $plus = $hname && $wname ? " + " : "";
  $name = uiTextSnippet('family') . " $familyID<br>$hname$plus$wname";
}

if (is_numeric($event)) {
  //custom event type
  $datefield = "eventdate";
  $placefield = "eventplace";
  $factfield = "info";

  if ($rights['both']) {
    $query = "SELECT eventdate, eventplace, info FROM $events_table WHERE eventID = \"$event\"";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    tng_free_result($result);
  } else {
    $row = array();
  }
} else {
  //standard, do switch
  $needfamilies = 0;
  $needchildren = 0;
  switch ($event) {
    case "TITL":
      $factfield = "title";
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

  if ($rights['both']) {
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
    $row = tng_fetch_assoc($result);
    tng_free_result($result);
  } else {
    $row = array();
  }
}
header("Content-type:text/html; charset=" . $session_charset);
?>

<div id='tentedit'>
  <form action="" method="post" name="form1" id="form1" onsubmit="return saveTentEdit(this);">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('editevent'); ?></h4>
      <h2 class='header'><?php echo "$name: $title"; ?></h2>
    </header>
    <div class='modal-body'>
      <input name='tree' type='hidden' value="<?php echo $tree; ?>">
      <input name='personID' type='hidden' value="<?php echo $personID; ?>">
      <input name='familyID' type='hidden' value="<?php echo $familyID; ?>">
      <input name='eventID' type='hidden' value="<?php echo $event; ?>">
      <input name='type' type='hidden' value="<?php echo $type; ?>">
      <table class='table table-sm'>
        <?php
        if ($datefield) {
          echo "<tr><td><span>" . uiTextSnippet('date') . ": </span></td><td><span>$row[$datefield]</span></td></tr>\n";
          echo "<tr><td><span>" . uiTextSnippet('suggested') . ": </span></td><td><input name='newdate' type='text' value=\"$row[$datefield]\" onblur=\"checkDate(this);\"></td></tr>\n";
          echo "<tr><td colspan='2'>&nbsp;</td></tr>\n";
        }
        if ($placefield) {
          $row[$placefield] = preg_replace("/\"/", "&#34;", $row[$placefield]);
          echo "<tr><td><span>" . uiTextSnippet('place') . ": </span></td><td><span>$row[$placefield]</span></td></tr>\n";
          echo "<tr><td><span>" . uiTextSnippet('suggested') . ": </span></td><td><input name='newplace' type='text' size='40' value=\"$row[$placefield]\"></td></tr>\n";
          echo "<tr><td colspan='2'>&nbsp;</td></tr>\n";
        }
        if ($factfield) {
          $row[$factfield] = preg_replace("/\"/", "&#34;", $row[$factfield]);
          $factmsg = $event == "MARR" ? uiTextSnippet('type') : uiTextSnippet('detail');
          echo "<tr><td><span>$factmsg: </span></td><td><span>$row[$factfield]</span></td></tr>\n";
          echo "<tr><td><span>" . uiTextSnippet('suggested') . ": </span></td><td>";
          if ($event == "MARR") {
            echo "<input name='newinfo' type='text' size='40' value=\"$row[$factfield]\">";
          } else {
            echo "<textarea cols=\"40\" rows=\"3\" name=\"newinfo\">$row[$factfield]</textarea>";
          }
          echo "</td></tr>\n";
          echo "<tr><td colspan='2'>&nbsp;</td></tr>\n";
        }
        ?>
        <tr>
          <td><span><?php echo uiTextSnippet('notes'); ?>: </span></td>
          <td><textarea cols="40" rows="3" name="usernote"></textarea></td>
        </tr>
      </table>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <input type='submit' value="<?php echo uiTextSnippet('savechanges'); ?>"> 
      <span id='tspinner' style="display: none"><img src="img/spinner.gif"></span>
    </footer>
  </form>
</div>
<div id='finished' style="margin: 10px; border: 0; display: none">
  <h2 class='header'><?php echo uiTextSnippet('thanks'); ?></h2>
  <p>
    <?php echo uiTextSnippet('received'); ?>
    <br><br>
    <a href="#" onclick="tnglitbox.remove();"><?php echo uiTextSnippet('closewindow'); ?></a>
  </p>
</div>