<?php
include("begin.php");
include("adminlib.php");

$admin_login = true;
include("checklogin.php");

initMediaTypes();

$personID = ucfirst($personID);
$query = "SELECT *, DATE_FORMAT(changedate,\"%d %b %Y %H:%i:%s\") as changedate FROM $people_table WHERE personID = \"$personID\" and gedcom = \"$tree\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['firstname'] = preg_replace("/\"/", "&#34;", $row['firstname']);
$row['lastname'] = preg_replace("/\"/", "&#34;", $row['lastname']);
$row['nickname'] = preg_replace("/\"/", "&#34;", $row['nickname']);
$row['suffix'] = preg_replace("/\"/", "&#34;", $row['suffix']);
$row['title'] = preg_replace("/\"/", "&#34;", $row['title']);
$row['birthplace'] = preg_replace("/\"/", "&#34;", $row['birthplace']);
$row['altbirthplace'] = preg_replace("/\"/", "&#34;", $row['altbirthplace']);
$row['deathplace'] = preg_replace("/\"/", "&#34;", $row['deathplace']);
$row['burialplace'] = preg_replace("/\"/", "&#34;", $row['burialplace']);
$row['baptplace'] = preg_replace("/\"/", "&#34;", $row['baptplace']);
$row['confplace'] = preg_replace("/\"/", "&#34;", $row['confplace']);
$row['initplace'] = preg_replace("/\"/", "&#34;", $row['initplace']);
$row['endlplace'] = preg_replace("/\"/", "&#34;", $row['endlplace']);

if ((!$allow_edit && (!$allow_add || !$added)) || ($assignedtree && $assignedtree != $tree) || !checkbranch($row['branch'])) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$editconflict = determineConflict($row, $people_table);
if ($tngconfig['edit_timeout'] === "") {
  $tngconfig['edit_timeout'] = 15;
}
$warnsecs = (intval($tngconfig['edit_timeout']) - 2) * 60 * 1000;

if ($row['sex'] == 'M') {
  $spouse = 'wife';
  $self = 'husband';
  $spouseorder = 'husborder';
  $selfdisplay = uiTextSnippet('ashusband');
} else {
  if ($row['sex'] == 'F') {
    $spouse = 'husband';
    $self = 'wife';
    $spouseorder = 'wifeorder';
    $selfdisplay = uiTextSnippet('aswife');
  } else {
    $spouse = "";
    $self = "";
    $spouseorder = "";
    $selfdisplay = uiTextSnippet('asspouse');
  }
}
$tng_search_people = $_SESSION['tng_search_people'];

$righttree = checktree($tree);
$rightbranch = $righttree ? checkbranch($row['branch']) : false;

$rights = determineLivingPrivateRights($row, $righttree, $rightbranch);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];

$namestr = getName($row);

$query = "SELECT treename FROM $trees_table WHERE gedcom = \"$tree\"";
$result = tng_query($query);
$treerow = tng_fetch_assoc($result);
tng_free_result($result);

$query = "SELECT DISTINCT eventID as eventID FROM $notelinks_table WHERE persfamID=\"$personID\" AND gedcom =\"$tree\"";
$notelinks = tng_query($query);
$gotnotes = array();
while ($note = tng_fetch_assoc($notelinks)) {
  if (!$note['eventID']) {
    $note['eventID'] = "general";
  }
  $gotnotes[$note['eventID']] = "*";
}
tng_free_result($notelinks);

$citquery = "SELECT DISTINCT eventID FROM $citations_table WHERE persfamID = \"$personID\" AND gedcom = \"$tree\"";
$citresult = tng_query($citquery) or die(uiTextSnippet('cannotexecutequery') . ": $citquery");
$gotcites = array();
while ($cite = tng_fetch_assoc($citresult)) {
  if (!$cite['eventID']) {
    $cite['eventID'] = "general";
  }
  $gotcites[$cite['eventID']] = "*";
}
tng_free_result($citresult);

$assocquery = "SELECT count(assocID) as acount FROM $assoc_table WHERE personID = \"$personID\" AND gedcom = \"$tree\"";
$assocresult = tng_query($assocquery) or die(uiTextSnippet('cannotexecutequery') . ": $assocquery");
$assocrow = tng_fetch_assoc($assocresult);
$gotassoc = $assocrow['acount'] ? "*" : "";
tng_free_result($assocresult);

$query = "SELECT parenttag FROM $events_table WHERE persfamID=\"$personID\" AND gedcom =\"$tree\"";
$morelinks = tng_query($query);
$gotmore = array();
while ($more = tng_fetch_assoc($morelinks)) {
  $gotmore[$more['parenttag']] = "*";
}
$revstar = checkReview('I');

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('modifyperson'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='editperson'>
  <section class='container'>
    <?php
    $photo = showSmallPhoto($personID, $namestr, 1, 0, 'I', $row['sex']);
    
    include_once("eventlib.php");
    
    echo $adminHeaderSection->build('people-modifyperson', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_people.php", uiTextSnippet('search'), "findperson"]);
    $navList->appendItem([$allow_add, "admin_newperson.php", uiTextSnippet('addnew'), "addperson"]);
    $navList->appendItem([$allow_edit, "admin_findreview.php?type=I", uiTextSnippet('review') . $revstar, "review"]);
    $navList->appendItem([$allow_edit && $allow_delete, "admin_merge.php", uiTextSnippet('merge'), "merge"]);
    $navList->appendItem([$allow_edit, "admin_editperson.php?personID=$personID&amp;tree=$tree", uiTextSnippet('edit'), "edit"]);
    echo $navList->build("edit");
    ?>
    <div id="thumbholder" style="margin-right: 5px; <?php if (!$photo) {echo "display: none";} ?>">
      <?php echo $photo; ?>
    </div>
    <?php echo "<h4>$namestr ($personID)</h4><p>" . getYears($row) . "</p>\n" ?>
    <div class='smallest'>
      <?php
      if ($editconflict) {
        echo "<br><p>" . uiTextSnippet('editconflict') . "</p>\n";
        echo "<p><strong><a href='admin_editperson.php?personID=$personID&tree=$tree'>" . uiTextSnippet('retry') . "</a></strong></p>\n";
      } else {
        $iconColor = $gotassoc ? "icon-info" : "icon-muted";
        echo "<a id='person-associations' href='#' title='" . uiTextSnippet('associations') . "' data-person-id='$personID' data-tree='$tree'>\n";
        echo "<img class='icon-md icon-associations $iconColor' data-src='svg/connections.svg'>\n";
        echo "</a>\n";

        $iconColor = $gotnotes['general'] ? "icon-info" : "icon-muted";
        echo "<a id='person-notes' href='#' title='" . uiTextSnippet('notes') . "' data-person-id='$personID'>\n";
        echo "<img class='icon-sm icon-right icon-notes $iconColor' data-src='svg/documents.svg'>\n";
        echo "</a>\n";

        $iconColor = $gotcites['general'] ? "icon-info" : "icon-muted";
        echo "<a id='person-citations' href='#' title='" . uiTextSnippet('citations') . "' data-person-id='$personID'>\n";
        echo "<img class='icon-sm icon-right icon-citations $iconColor' data-src='svg/archive.svg'>\n";
        echo "</a>\n";
      }
      ?>
      <br><br>
    </div>
    <br>
    <a href="getperson.php?personID=<?php echo $personID; ?>&amp;tree=<?php echo $tree; ?>" title='<?php echo uiTextSnippet('preview') ?>'>
      <img class='icon-sm' src='svg/eye.svg'>
    </a>
    <?php if ($allow_add && (!$assignedtree || $assignedtree == $tree)) { ?>
      <a id='addmedia-person' href='#'><?php echo uiTextSnippet('addmedia'); ?></a>
    <?php } ?>

    <form action="admin_updateperson.php" method='post' name='form1' id='form1'>
      <?php if (!$editconflict) { ?>
        <div id='person-names'>
          <div class='row'>
            <div class='col-md-4'>
              <?php echo uiTextSnippet('tree') . ":&nbsp;" . $treerow['treename'] . "&nbsp;"; ?>
              <span>( 
                <a id='change-tree' href='#' data-tree='<?php echo $tree; ?>' data-person-id='<?php echo $personID; ?>'>
                  <img src='img/ArrowDown.gif'><?php echo uiTextSnippet('edit'); ?>
                </a>)
              </span>
            </div>
            <div class='col-md-4'>
              <?php require_once 'branches.php'; ?>
              <?php echo buildBranchSelectControl($row, $tree, $assignedbranch, $branches_table); ?>
            </div>
            <div class='col-md-4'>
              <div class='checkbox-inline'>
                <label>
                  <input name='living' type='checkbox' value='1'<?php if ($row['living']) {echo " checked";} ?>>
                  <?php echo uiTextSnippet('living'); ?>
                </label>
              </div>
              <div class='checkbox-inline'>
                <label>
                  <input name='private' type='checkbox' value='1'<?php if ($row['private']) {echo " checked";} ?>>
                  <?php echo uiTextSnippet('private'); ?>
                </label>
              </div>        
            </div>
          </div>
          <div class='row'>
            <div class='col-md-3'>
              <?php echo uiTextSnippet('givennames'); ?>
              <input class='form-control' name='firstname' type='text' value="<?php echo $row['firstname']; ?>">
            </div>
            <?php if ($lnprefixes) { ?>
              <div class='col-md-2'>
                <?php echo uiTextSnippet('lnprefix'); ?>
                <input class='form-control' name='lnprefix' type='text' value="<?php echo $row['lnprefix']; ?>">
              </div>
              <div class='col-md-3'>
                <?php echo uiTextSnippet('surname'); ?>
                <input class='form-control' name='lastname' type='text' value="<?php echo $row['lastname']; ?>">
              </div>
            <?php } else { ?>
              <div class='col-md-5'>
                <?php echo uiTextSnippet('surname'); ?>
                <input class='form-control' name='lastname' type='text' value="<?php echo $row['lastname']; ?>">
              </div>
            <?php } ?>
            <div class='col-md-2'>
              <?php echo buildSexSelectControl($row['sex']); ?>
            </div>
            <div class='col-md-2'>
              <br>
              <?php
              $iconColor = $gotnotes['NAME'] ? "icon-info" : "icon-muted";
              echo "<a id='person-notes-name' href='#' title='" . uiTextSnippet('notes') . "' data-person-id='$personID'>\n";
              echo "<img class='icon-sm icon-right icon-notes $iconColor' data-src='svg/documents.svg'>\n";
              echo "</a>\n";
              
              $iconColor = $gotcites['NAME'] ? "icon-info" : "icon-muted";
              echo "<a id='person-citations-name' href='#' title='" . uiTextSnippet('citations') . "' data-person-id='$personID' >\n";
              echo "<img class='icon-sm icon-right icon-citations $iconColor' data-src='svg/archive.svg'>\n";
              echo "</a>\n";
              ?>
            </div>
          </div>
          <div class='row'>
            <div class='col-md-3'>
              <?php echo uiTextSnippet('nickname'); ?>
              <input class='form-control' name='nickname' type='text' value="<?php echo $row['nickname']; ?>">
            </div>
            <div class='col-md-2'>
              <?php echo uiTextSnippet('title'); ?>
              <input class='form-control' name='title' type='text' value="<?php echo $row['title']; ?>">
            </div>
            <div class='col-md-2'>
              <?php echo uiTextSnippet('prefix'); ?>
              <input class='form-control' name='prefix' type='text' value="<?php echo $row['prefix']; ?>">
            </div>
            <div class='col-md-2'>
              <?php echo uiTextSnippet('suffix'); ?>
              <input class='form-control' name='suffix' type='text' value="<?php echo $row['suffix']; ?>">
            </div>
            <div class='col-md-3'>
              <?php echo uiTextSnippet('nameorder'); ?>
              <select class='form-control' name="pnameorder">
                <option value='0'>
                  <?php echo uiTextSnippet('default'); ?>
                </option>
                <option value='1' <?php if ($row['nameorder'] == "1") {echo "selected";} ?>>
                  <?php echo uiTextSnippet('western'); ?>
                </option>
                <option value="2" <?php if ($row['nameorder'] == "2") {echo "selected";} ?>>
                  <?php echo uiTextSnippet('oriental'); ?>
                </option>
                <option value="3" <?php if ($row['nameorder'] == "3") {echo "selected";} ?>>
                  <?php echo uiTextSnippet('lnfirst'); ?>
                </option>
              </select>
            </div>
          </div>
        </div> <!-- #person-names -->
        <br>
        <div class='small'>
          <a href='#' id='expandall-editperson'><?php echo uiTextSnippet('expandall'); ?></a>
          <a href='#' id='collapseall-editperson'><?php echo uiTextSnippet('collapseall'); ?></a>
        </div>
        <?php echo displayToggle("plus1", 1, "person-events", uiTextSnippet('events'), ""); ?>
        <div id='person-events'>
          <p><?php echo uiTextSnippet('datenote'); ?></p>
          <?php
          echo buildEventRow('birthdate', 'birthplace', 'BIRT', $personID);
          if (!$tngconfig['hidechr']) {
            echo buildEventRow('altbirthdate', 'altbirthplace', 'CHR', $personID);
          }
          echo buildEventRow('deathdate', 'deathplace', 'DEAT', $personID);
          echo buildEventRow('burialdate', 'burialplace', 'BURI', $personID);
          $checked = $row['burialtype'] == 1 ? " checked" : "";
              echo "<input id='burialtype' name='burialtype' type='checkbox' value='1'$checked> \n";
              echo "<label for='burialtype'>" . uiTextSnippet('cremated') . "</label>\n";
          if ($rights['lds']) {
            echo buildEventRow('baptdate', 'baptplace', 'BAPL', $personID);
            echo buildEventRow('confdate', 'confplace', 'CONL', $personID);
            echo buildEventRow('initdate', 'initplace', 'INIT', $personID);
            echo buildEventRow('endldate', 'endlplace', 'ENDL', $personID);
          }
          ?>
          <?php echo uiTextSnippet('otherevents'); ?>:
            <input id='addnew-event-person' type='button' value=" <?php echo uiTextSnippet('addnew') ?> " data-person-id='<?php echo $personID; ?>' data-tree='<?php echo $tree; ?>'>
          <?php
          showCustEvents($personID);
          ?>
        </div> <!-- #person-events -->
        
        <?php
        $query = "SELECT personID, familyID, sealdate, sealplace, frel, mrel FROM $children_table WHERE personID = \"$personID\" AND gedcom = \"$tree\" ORDER BY parentorder";
        $parents = tng_query($query);
        $parentcount = tng_num_rows($parents);
        $addNewFamilyTitle = "title='" . uiTextSnippet('gotonewfamily') . " ($personID) " . uiTextSnippet('aschild') . "'";
        $newparents = $allow_add && (!$assignedtree || $assignedtree == $tree) ? 
          "&nbsp; <a id='addnew-parents' href='#' $addNewFamilyTitle data-person-id='$personID' data-tree='$tree' data-cw='$cw'>" . uiTextSnippet('addnew') . "</a>\n" : "";
        echo displayToggle("plus2", 1, "parents", uiTextSnippet('parents') . " (<span id=\"parentcount\">$parentcount</span>) $newparents", "");
        ?>
        <div id='parents'>
          <?php
          while ($parent = tng_fetch_assoc($parents)) {
            $familyId =  $parent['familyID'];
            echo "<div class='sortrow' id='parents_{$familyId}' style='clear: both' data-family-id='$familyId'>\n";
            ?>
              <table class='table table-sm'>
                <tr>
                  <?php if ($parentcount > 1) { ?> 
                    <td class='dragarea'>
                      <img src='img/admArrowUp.gif' alt=''><?php echo uiTextSnippet('drag'); ?>
                      <img src='img/admArrowDown.gif' alt=''>
                    </td>
                  <?php
                  }
                  echo "<td>\n";
                    echo "<div id='unlinkp_$familyId' style='float: right; display: none'>\n";
                      echo "<a id='unlink-from-family' href='#'  data-family-id='$familyId' data-tree='$tree'>" . uiTextSnippet('unlinkindividual') . " ($personID) " . uiTextSnippet('aschild') . "</a>\n";
                    echo "</div>\n";
                    echo "<strong>" . uiTextSnippet('family') . ":</strong>\n";
                    echo "<a href=\"admin_editfamily.php?familyID={$familyId}&amp;tree=$tree&amp;cw=$cw\">{$familyId}</a>\n";
                    
                    echo buildParentRow($parent, 'husband', 'father');
                    echo buildParentRow($parent, 'wife', 'mother');
                    $parent['sealplace'] = preg_replace("/\"/", "&#34;", $parent['sealplace']);
                    if ($rights['lds']) {
                      $citquery = "SELECT citationID FROM $citations_table WHERE persfamID = \"$personID" . "::" . "{$familyId}\" AND gedcom = \"$tree\"";
                      $citresult = tng_query($citquery) or die(uiTextSnippet('cannotexecutequery') . ": $citquery");
                      $iconColor = tng_num_rows($citresult) ? "icon-info" : "icon-muted";
                      tng_free_result($citresult);

                      echo "<div class='row'>\n";
                        echo "<div class='col-md-2'>" . uiTextSnippet('SLGC') . ":</div>\n";
                        echo "<div class='col-md-2'>\n";
                          echo "<input class='form-control form-control-sm' id='parent-sealdate' name='sealpdate" . $familyId . "' type='text' value='" . $parent['sealdate'] . "' maxlength='50' placeholder='" . uiTextSnippet('date') . "'>\n";
                        echo "</div>\n";
                        echo "<div class='col-md-5'>\n";
                          echo "<input class='form-control form-control-sm' id='sealpplace" . $familyId . "' name='sealpplace" . $familyId . "' type='text' value='" . $parent['sealplace'] . "' placeholder='" . uiTextSnippet('place') . "'>\n";
                        echo "</div>\n";
                        echo "<div class='col-md-3'>\n";
                          echo "<a id='find-place-seal' href='#' title='" . uiTextSnippet('find') . "'>\n";
                            echo "<img class='icon-sm' src='svg/temple.svg'>\n";
                          echo "</a>\n";
                          echo "<a class='lds-seal-citations' id='citesiconSLGC$personID::" . $familyId . "' href='#' title='" . uiTextSnippet('citations') . "' data-person-id='$personID' data-family-id='$familyId'>\n";
                            echo "<img class='icon-sm icon-right icon-citations $iconColor' data-src='svg/archive.svg'>\n";
                          echo "</a>\n";
                        echo "</div>\n";
                      echo "</div>\n";
                    }
                  echo "</td>\n";
                  ?>
                </tr>
              </table>
              <input name="sealpdate<?php echo $familyId; ?>" type='hidden' value="<?php echo $parent['sealdate']; ?>">
              <input name="sealpplace<?php echo $familyId; ?>" type='hidden' value="<?php echo $parent['sealplace']; ?>">
            <?php
            echo "</div>\n";
          }
          ?>
        </div> <!-- #parents -->
        <?php tng_free_result($parents); ?>

        <table class="table table-sm">
          <?php
          if ($row['sex']) {
            if ($self) {
              $query = "SELECT $spouse, familyID, marrdate FROM $families_table WHERE $families_table.$self = \"$personID\" AND gedcom = \"$tree\" ORDER BY $spouseorder";
            } else {
              $query = "SELECT husband, wife, familyID, marrdate FROM $families_table WHERE ($families_table.husband = \"$personID\" OR $families_table.wife = \"$personID\") AND gedcom = \"$tree\"";
            }
            $marriages = tng_query($query);
            $marrcount = tng_num_rows($marriages);
            ?>
            <tr>
              <td>
                <?php
                $newspouse = $allow_add && (!$assignedtree || $assignedtree == $tree ) && $row['sex'] 
                  ? "&nbsp; <a id='addnew-family-spouses' href='#' data-self='$self' data-person-id='$personID' data-tree='$tree' data-cw='$cw' title=\"" . uiTextSnippet('gotonewfamily') . " ($personID) $selfdisplay\">" . uiTextSnippet('addnew') . "</a>\n" 
                  : "";
                echo displayToggle("plus3", 1, "spouses", uiTextSnippet('spouses') . " (<span id=\"marrcount\">$marrcount</span>) $newspouse", "");
                ?>
                <div id='spouses'>
                  <?php
                  if ($marriages && tng_num_rows($marriages)) {
                    while ($marriagerow = tng_fetch_assoc($marriages)) {
                      $familyId = $marriagerow['familyID'];
                      if (!$spouse) {
                        if ($personID == $marriagerow['husband']) {
                          $self = 'husband';
                          $spouse = 'wife';
                        } else {
                          if ($personID == $marriagerow['wife']) {
                            $self = 'wife';
                          }
                        }
                        $spouse = 'husband';
                      }
                      echo "<div class='sortrow' id='spouses_$familyId' style='clear: both' data-family-id='$familyId'>\n";
                        ?> <table class='table table-sm'> <?php
                          ?> <tr> <?php
                            if ($marrcount > 1) {
                              echo "<td class='dragarea'>";
                                echo "<img src='img/admArrowUp.gif' alt=''>" . uiTextSnippet('drag') . "\n";
                                echo "<img src='img/admArrowDown.gif' alt=''>\n";
                              echo "</td>\n";
                            }
                            ?> <td>
                              
                              <?php
                              echo "<strong>" . uiTextSnippet('family') . ":</strong>\n";
                              echo "<div id='unlinks_$familyId' style='float: right; display: none'>\n";
                                echo "<a id='unlink-from-family' href='#' data-family-id='$familyId' data-tree='$tree'>" . uiTextSnippet('unlinkindividual') . " ($personID) " . uiTextSnippet('asspouse') . "</a>\n";
                              echo "</div>\n";
                              echo "<a href=\"admin_editfamily.php?familyID={$familyId}&amp;tree=$tree&amp;cw=$cw\">{$familyId}</a>\n";
                              
                              if ($marriagerow[$spouse]) {
                                $query = "SELECT personID, lastname, lnprefix, firstname, birthdate, birthplace, altbirthdate, altbirthplace, prefix, suffix, nameorder FROM $people_table WHERE personID = \"{$marriagerow[$spouse]}\" AND gedcom = \"$tree\"";
                                $spouseresult = tng_query($query);
                                $spouserow = tng_fetch_assoc($spouseresult);

                                $srights = determineLivingPrivateRights($spouserow, $righttree);
                                $spouserow['allow_living'] = $srights['living'];
                                $spouserow['allow_private'] = $srights['private'];

                                $birthinfo = $spouserow['birthdate'] ? " (" . uiTextSnippet('birthabbr') . " " . displayDate($spouserow['birthdate']) . ")" : "";
                              } else {
                                $spouserow = $birthinfo = "";
                              }
                              ?>                              
                              <span><br><?php echo uiTextSnippet('spouse'); ?>:</span>
                              <span>
                                <?php
                                if (isset($spouserow['personID']) && $spouserow['personID']) {
                                  echo "<a href=\"admin_editperson.php?personID={$spouserow['personID']}&amp;tree=$tree&amp;cw=$cw\">" . getName($spouserow) . " - {$spouserow['personID']}</a>$birthinfo";
                                }
                                ?>
                              </span>
                              <?php if ($marriagerow['marrdate'] || $marriagerow['marrplace']) { ?>
                                <span><?php echo uiTextSnippet('married'); ?>:</span>
                                <span><?php echo displayDate($marriagerow['marrdate']); ?></span>
                              <?php } ?>
                              <?php
                              $query = "SELECT $people_table.personID as pID, firstname, lnprefix, lastname, birthdate, birthplace, altbirthdate, altbirthplace, haskids, living, private, branch, prefix, suffix, nameorder FROM ($people_table, $children_table) WHERE $people_table.personID = $children_table.personID AND $children_table.familyID = \"{$familyId}\" AND $people_table.gedcom = \"$tree\" AND $children_table.gedcom = \"$tree\" ORDER BY ordernum";
                              $children = tng_query($query);

                              if ($children && tng_num_rows($children)) {
                                echo "<p>" . uiTextSnippet('children') . "</p>";
                              
                                $kidcount = 1;
                                while ($child = tng_fetch_assoc($children)) {
                                  $ifkids = $child['haskids'] ? "+" : "&nbsp;";
                                  $crights = determineLivingPrivateRights($child);
                                  $child['allow_living'] = $crights['living'];
                                  $child['allow_private'] = $crights['private'];
                                  if ($child['firstname'] || $child['lastname']) {
                                    echo "<div class='row'>\n";
                                      echo "<div class='col-sm-2'>$ifkids</div>\n";
                                      echo "<div class='col-md-8'>$kidcount. ";
                                        if ($crights['both']) {
                                          if ($rightbranch) {
                                            echo "<a href=\"admin_editperson.php?personID={$child['pID']}&amp;tree=$tree&amp;cw=$cw\">" . getName($child) . " - {$child['pID']}</a>";
                                          } else {
                                            echo getName($child) . " - " . $child['pID'];
                                          }
                                          echo $child['birthdate'] ? " (" . uiTextSnippet('birthabbr') . " " . displayDate($child['birthdate']) . ")" : "";
                                        } else {
                                          echo ($child['private'] ? uiTextSnippet('private') : uiTextSnippet('living')) . " - " . $child['pID'];
                                        }
                                      echo "</div>\n";
                                    echo "</div>\n";
                                  }
                                  $kidcount++;
                                }
                                tng_free_result($children);
                              }
                              ?>
                            </td>
                          </tr>
                        </table>
                      <?php echo "</div>\n"; ?>
                    <?php
                    }
                    tng_free_result($marriages);
                  }
                ?>
                </div>
              </td>
            </tr>
          <?php } ?>
          <tr>
            <td>
              <p>
                <?php
                echo uiTextSnippet('onsave') . ":<br>";
                if ($allow_add && (!$assignedtree || $assignedtree == $tree)) {
                  echo "<input id='radiochild' name='newfamily' type='radio' value='child'>" . uiTextSnippet('gotonewfamily') . " ($personID) " . uiTextSnippet('aschild') . "<br>\n";
                  if ($row['sex']) {
                    echo "<input id=\"radio$self\" name='newfamily' type='radio' value=\"$self\">" . uiTextSnippet('gotonewfamily') . " ($personID) $selfdisplay<br>\n";
                  }
                }
                echo "<input name='newfamily' type='radio' value='return'> " . uiTextSnippet('savereturn') . "<br>\n";
                if ($cw) {
                  echo "<input name='newfamily' type='radio' value='close' checked> " . uiTextSnippet('closewindow') . "\n";
                } else {
                  echo "<input name='newfamily' type='radio' value='none' checked> " . uiTextSnippet('saveback') . "\n";
                }
                ?>
              </p>
              <input id='newmedia' name='media' type='hidden' value=''>
              <input name='tree' type='hidden' value="<?php echo $tree; ?>">
              <input name='added' type='hidden' value="<?php echo $added; ?>">
              <input name='personID' type='hidden' value="<?php echo "$personID"; ?>">
              <input name='submit2' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
              <?php
              if (!$lnprefixes) {
                echo "<input name='lnprefix' type='hidden' value=\"{$row['lnprefix']}\">";
              }
              if (!$rights['lds']) {
              ?>
                <input name='baptdate' type='hidden' value="<?php echo $row['baptdate']; ?>">
                <input name='baptplace' type='hidden' value="<?php echo $row['baptplace']; ?>">
                <input name='confdate' type='hidden' value="<?php echo $row['confdate']; ?>">
                <input name='confplace' type='hidden' value="<?php echo $row['confplace']; ?>">
                <input name='initdate' type='hidden' value="<?php echo $row['initdate']; ?>">
                <input name='initplace' type='hidden' value="<?php echo $row['initplace']; ?>">
                <input name='endldate' type='hidden' value="<?php echo $row['endldate']; ?>">
                <input name='endlplace' type='hidden' value="<?php echo $row['endlplace']; ?>">
              <?php } ?>
              <input name='cw' type='hidden' value="<?php echo "$cw"; ?>">
            </td>
          </tr>
        </table>
      <?php } // ?editconflict ?>
    </form>
    <p class="smallest"><?php echo uiTextSnippet('lastmodified') . ": {$row['changedate']} ({$row['changedby']})"; ?></p>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/admin.js"></script>
<script src="js/selectutils.js"></script>
<script src="js/datevalidation.js"></script>
<script src="js/associations.js"></script>
<script src="js/citations.js"></script>
<script src="js/notes.js"></script>
<script src="js/more.js"></script>
<script src='js/people.js'></script>
<script>
  var tnglitbox;
  var preferEuro = <?php echo($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : "false"); ?>;
  var preferDateFormat = '<?php echo $preferDateFormat; ?>';

  var allow_cites = true;
  var allow_notes = true;

  var tree = '<?php echo $tree; ?>';
  var spouseOrder = '<?php echo $spouseorder; ?>';
  
  $(document).ready( function() {
      startPersonSorts(tree, spouseOrder);
  });

  function lockExpiring() {
    alert(textSnippet('lockexpiring'));
  }

  <?php if (!$editconflict && $warnsecs >= 0) { ?>
    setTimeout(lockExpiring, <?php echo $warnsecs; ?>);
  <?php } ?>
</script>
</body>
</html>