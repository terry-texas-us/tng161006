<?php
require 'begin.php';
require 'adminlib.php';
if (!$personID) {
  die('no args');
}
require 'checklogin.php';

initMediaTypes();

$personID = ucfirst($personID);
$query = "SELECT *, DATE_FORMAT(changedate,\"%d %b %Y %H:%i:%s\") AS changedate FROM $people_table WHERE personID = '$personID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['firstname'] = preg_replace('/\"/', '&#34;', $row['firstname']);
$row['lastname'] = preg_replace('/\"/', '&#34;', $row['lastname']);
$row['nickname'] = preg_replace('/\"/', '&#34;', $row['nickname']);
$row['suffix'] = preg_replace('/\"/', '&#34;', $row['suffix']);
$row['title'] = preg_replace('/\"/', '&#34;', $row['title']);
$row['birthplace'] = preg_replace('/\"/', '&#34;', $row['birthplace']);
$row['altbirthplace'] = preg_replace('/\"/', '&#34;', $row['altbirthplace']);
$row['deathplace'] = preg_replace('/\"/', '&#34;', $row['deathplace']);
$row['burialplace'] = preg_replace('/\"/', '&#34;', $row['burialplace']);
$row['baptplace'] = preg_replace('/\"/', '&#34;', $row['baptplace']);
$row['endlplace'] = preg_replace('/\"/', '&#34;', $row['endlplace']);

if ((!$allowEdit && (!$allowAdd || !$added)) || !checkbranch($row['branch'])) {
  $message = uiTextSnippet('norights');
  header('Location: ajx_login.php?message=' . urlencode($message));
  exit;
}
$editconflict = determineConflict($row, $people_table);

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
    $spouse = '';
    $self = '';
    $spouseorder = '';
    $selfdisplay = uiTextSnippet('asspouse');
  }
}
$rights = determineLivingPrivateRights($row);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];

$namestr = getName($row);

$query = "SELECT DISTINCT eventID AS eventID FROM notelinks WHERE persfamID = '$personID'";
$notelinks = tng_query($query);
$gotnotes = [];
while ($note = tng_fetch_assoc($notelinks)) {
  if (!$note['eventID']) {
    $note['eventID'] = 'general';
  }
  $gotnotes[$note['eventID']] = '*';
}
tng_free_result($notelinks);

$citquery = "SELECT DISTINCT eventID FROM citations WHERE persfamID = '$personID'";
$citresult = tng_query($citquery) or die(uiTextSnippet('cannotexecutequery') . ": $citquery");
$gotcites = [];
while ($cite = tng_fetch_assoc($citresult)) {
  if (!$cite['eventID']) {
    $cite['eventID'] = 'general';
  }
  $gotcites[$cite['eventID']] = '*';
}
tng_free_result($citresult);

$assocquery = "SELECT count(assocID) AS acount FROM associations WHERE personID = '$personID'";
$assocresult = tng_query($assocquery) or die(uiTextSnippet('cannotexecutequery') . ": $assocquery");
$assocrow = tng_fetch_assoc($assocresult);
$gotassoc = $assocrow['acount'] ? '*' : '';
tng_free_result($assocresult);

$query = "SELECT parenttag FROM events WHERE persfamID = '$personID'";
$morelinks = tng_query($query);
$gotmore = [];
while ($more = tng_fetch_assoc($morelinks)) {
  $gotmore[$more['parenttag']] = '*';
}
$reltypes = ['adopted', 'birth', 'foster', 'sealing', 'step'];
$photo = showSmallPhoto($personID, $namestr, 1, 0, 'I', $row['sex']);

header('Content-type: text/html; charset=' . $session_charset);

require_once 'eventlib.php';
?>
<section class='container-fluid'>
  <form id='form1' name='form1' action='' method='post' onsubmit="return updatePerson(this, <?php echo $slot; ?>);">
    <header class='modal-header'>
      <div id="thumbholder" style="margin-right: 5px; <?php if (!$photo) {echo 'display: none';} ?>">
        <?php echo $photo; ?>
      </div>
      <?php echo "<h4>$namestr ($personID)</h4><p>" . getYears($row) . "</p>\n"; ?>
      <div>
        <?php
        if ($editconflict) {
          echo '<br><p>' . uiTextSnippet('editconflict') . '</p>';
        } else {
          $iconColor = $gotassoc ? 'icon-info' : 'icon-muted';
          echo "<a id='person-associations' href='#' data-family-id='$personID' title='" . uiTextSnippet('associations') . "'>\n";
          echo "<img class='icon-md icon-associations $iconColor' data-src='svg/connections.svg'>\n";
          echo "</a>\n";
          
          $iconColor = $gotnotes['general'] ? 'icon-info' : 'icon-muted';
          echo "<a id='person-notes' href='#' title='" . uiTextSnippet('notes') . "' data-person-id='$personID'>\n";
          echo "<img class='icon-sm icon-right icon-notes $iconColor' data-src='svg/documents.svg'>\n";
          echo "</a>\n";
          
          $iconColor = $gotcites['general'] ? 'icon-info' : 'icon-muted';
          echo "<a id='person-citations' href='#' title='" . uiTextSnippet('citations') . "' data-person-id='$personID'>\n";
          echo "<img class='icon-sm icon-right icon-citations $iconColor' data-src='svg/archive.svg'>\n";
          echo "</a>\n";
        }
        ?>
        <br>
      </div>
      <span class="smallest"><?php echo uiTextSnippet('lastmodified') . ": {$row['changedate']} ({$row['changedby']})"; ?></span>
    </header>
    <div class='modal-body'>
      <?php if (!$editconflict) { ?>
        <div id="person-names">
          <div class='row'>
            <div class='col-md-4'>
              <label><?php echo uiTextSnippet('givennames'); ?></label>
              <input class='form-control' name='firstname' type='text' value="<?php echo $row['firstname']; ?>">
            </div>
            <?php if ($lnprefixes) { ?>
              <div class='col-md-2'>
                <label><?php echo uiTextSnippet('lnprefix'); ?></label>
                <input class='form-control' name='lnprefix' type='text' value="<?php echo $row['lnprefix']; ?>">
              </div>
              <div class='col-md-3'>
                <label><?php echo uiTextSnippet('surname'); ?></label>
                <input class='form-control' name='lastname' type='text' value="<?php echo $row['lastname']; ?>">
              </div>  
            <?php } else { ?>
              <div class='col-md-5'>
                <label><?php echo uiTextSnippet('surname'); ?></label>
                <input class='form-control' name='lastname' type='text' value="<?php echo $row['lastname']; ?>">
              </div>
            <?php } ?>
            <div class='col-md-3'>
              <br>
              <?php
              $iconColor = $gotnotes['NAME'] ? 'icon-info' : 'icon-muted';
              echo "<a id='person-notes-name' href='#' title='" . uiTextSnippet('notes') . "' data-person-id='$personID'>\n";
              echo "<img class='icon-sm icon-right icon-notes $iconColor' data-src='svg/documents.svg'>\n";
              echo "</a>\n";
              
              $iconColor = $gotcites['NAME'] ? 'icon-info' : 'icon-muted';
              echo "<a id='person-citations-name' href='#' title='" . uiTextSnippet('citations') . "' data-person-id='$personID' >\n";
              echo "<img class='icon-sm icon-right icon-citations $iconColor' data-src='svg/archive.svg'>\n";
              echo "</a>\n";
              ?>
            </div>
          </div>
          <br>
          <div class='row'>
            <div class='col-md-3'>
              <label><?php echo uiTextSnippet('nickname'); ?></label>
              <input class='form-control' name='nickname' type='text' value="<?php echo $row['nickname']; ?>">
            </div>
            <div class='col-md-2'>
              <label><?php echo uiTextSnippet('title'); ?></label>
              <input class='form-control' name='title' type='text' value="<?php echo $row['title']; ?>">
            </div>
            <div class='col-md-2'>
              <label><?php echo uiTextSnippet('prefix'); ?></label>
              <input class='form-control' name='prefix' type='text' value="<?php echo $row['prefix']; ?>">
            </div>
            <div class='col-md-2'>
              <label><?php echo uiTextSnippet('suffix'); ?></label>
              <input class='form-control' name='suffix' type='text' value="<?php echo $row['suffix']; ?>">
            </div>
            <div class='col-md-3'>
              <label><?php echo uiTextSnippet('nameorder'); ?></label>
              <select class='form-control' name='pnameorder'>
                <option value='0'>
                  <?php echo uiTextSnippet('default'); ?>
                </option>
                <option value='1' <?php if ($row['nameorder'] == '1') {echo 'selected';} ?>>
                  <?php echo uiTextSnippet('western'); ?>
                </option>
                <option value='2' <?php if ($row['nameorder'] == '2') {echo 'selected';} ?>>
                  <?php echo uiTextSnippet('oriental'); ?>
                </option>
                <option value='3' <?php if ($row['nameorder'] == '3') {echo 'selected';} ?>>
                  <?php echo uiTextSnippet('lnfirst'); ?>
                </option>
              </select>
            </div>
          </div>
          <br>
          <div class='row'>
            <div class='col-md-4'>
              <?php include_once 'branches.php'; ?>
              <?php echo buildBranchSelectControl($row, $assignedbranch); ?>
            </div>
            <div class='col-md-4'>
              <label class='form-check-inline'>
                <input class='form-check-input' name='living' type='checkbox' value='1'<?php if ($row['living']) {echo ' checked';} ?>>
                <?php echo uiTextSnippet('living'); ?>
              </label>
              <label class='form-check-inline'>
                <input class='form-check-input' name='private' type='checkbox' value='1'<?php if ($row['private']) {echo ' checked';} ?>>
                <?php echo uiTextSnippet('private'); ?>
              </label>
            </div>
            <div class='col-lg-4'>
              <?php echo buildSexSelectControl($row['sex']); ?>
            </div>
          </div>                
        </div> <!-- #person-names -->

        <?php echo displayToggle('plus1', 1, 'person-events', uiTextSnippet('events'), ''); ?>
        <div id='person-events'>
          <p class='smallest'><?php echo uiTextSnippet('datenote'); ?></p>
          <?php
          echo buildEventRow('birthdate', 'birthplace', 'BIRT', $personID);
          if (!$tngconfig['hidechr']) {
            echo buildEventRow('altbirthdate', 'altbirthplace', 'CHR', $personID);
          }
          echo buildEventRow('deathdate', 'deathplace', 'DEAT', $personID);
          echo buildEventRow('burialdate', 'burialplace', 'BURI', $personID);
          if ($rights['lds']) {
            echo buildEventRow('baptdate', 'baptplace', 'BAPL', $personID);
            echo buildEventRow('confdate', 'confplace', 'CONL', $personID);
            echo buildEventRow('initdate', 'initplace', 'INIT', $personID);
            echo buildEventRow('endldate', 'endlplace', 'ENDL', $personID);
          }
          echo uiTextSnippet('otherevents') . ": \n";
          echo "<input type='button' value=\"  " . uiTextSnippet('addnew') . "  \" onClick=\"newEvent('I','$personID');\">\n";
          showCustEvents($personID);
          ?>
          <input name='personID' type='hidden' value="<?php echo "$personID"; ?>" />
          <input name='newfamily' type='hidden' value='ajax' />
          <?php
          if (!$lnprefixes) {
            echo "<input name='lnprefix' type='hidden' value=\"{$row['lnprefix']}\" />";
          }
          if (!$rights['lds']) {
            ?>
            <input name='baptdate' type='hidden' value="<?php echo $row['baptdate']; ?>" />
            <input name='baptplace' type='hidden' value="<?php echo $row['baptplace']; ?>" />
            <input name='confdate' type='hidden' value="<?php echo $row['confdate']; ?>" />
            <input name='confplace' type='hidden' value="<?php echo $row['confplace']; ?>" />
            <input name='initdate' type='hidden' value="<?php echo $row['initdate']; ?>" />
            <input name='initplace' type='hidden' value="<?php echo $row['initplace']; ?>" />
            <input name='endldate' type='hidden' value="<?php echo $row['endldate']; ?>" />
            <input name='endlplace' type='hidden' value="<?php echo $row['endlplace']; ?>" />
          <?php } ?>
        </div> <!-- #person-events -->
        
        <?php
        $query = "SELECT personID, familyID, sealdate, sealplace, frel, mrel FROM children WHERE personID = '$personID' ORDER BY parentorder";
        $parents = tng_query($query);
        $parentcount = tng_num_rows($parents);

        if ($parentcount) {
        ?>
          <?php echo displayToggle('plus2', 0, 'parents', uiTextSnippet('parents') . " (<span id=\"parentcount\">$parentcount</span>)", ''); ?>
          <div id='parents' style='display: none'>
            <?php
            while ($parent = tng_fetch_assoc($parents)) {
              $familyId =  $parent['familyID'];
              echo "<div class='sortrow' id='parents_{$familyId}' style='clear: both' onmouseover=\"$('unlinkp_{$familyId}').style.display='';\" onmouseout=\"$('unlinkp_{$familyId}').style.display='none';\">\n";
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
                        echo "<a id='unlink-from-family' href='#' data-family-id='$familyId' onclick=\"return unlinkParents('{$familyId}');\">" . uiTextSnippet('unlinkindividual') . " ($personID) " . uiTextSnippet('aschild') . "</a>\n";
                      echo "</div>\n";
                      echo '<strong>' . uiTextSnippet('family') . ":</strong>\n";
                      
                      echo buildParentRow($parent, 'husband', 'father');
                      echo buildParentRow($parent, 'wife', 'mother');
                      
                      $parent['sealplace'] = preg_replace('/\"/', '&#34;', $parent['sealplace']);
                      if ($rights['lds']) {
                        $citquery = "SELECT citationID FROM citations WHERE persfamID = \"$personID" . '::' . "{$familyId}\"";
                        $citresult = tng_query($citquery) or die(uiTextSnippet('cannotexecutequery') . ": $citquery");
                        $iconColor = tng_num_rows($citresult) ? 'icon-info' : 'icon-muted';
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
              <?php
              echo "</div>\n";
            }
            ?>
          </div> <!-- #parents -->
          <?php tng_free_result($parents); ?>
        <?php } ?>
        
        <table class='table table-sm'>
          <?php
          if ($row['sex']) {
            if ($self) {
              $query = "SELECT $spouse, familyID, marrdate FROM $families_table WHERE $families_table.$self = '$personID' ORDER BY $spouseorder";
            } else {
              $query = "SELECT husband, wife, familyID, marrdate FROM $families_table WHERE ($families_table.husband = \"$personID\" OR $families_table.wife = \"$personID\")";
            }
            $marriages = tng_query($query);
            $marrcount = tng_num_rows($marriages);
            ?>
            <tr>
              <td>
                <?php echo displayToggle('plus3', 0, 'spouses', uiTextSnippet('spouses') . " (<span id=\"marrcount\">$marrcount</span>)", ''); ?>

                <div id='spouses' style='display: none'>
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
                              echo '<strong>' . uiTextSnippet('family') . ":</strong>\n";
                              echo "<div id='unlinks_$familyId' style='float: right; display: none'>\n";
                                echo "<a id='unlink-from-family' href='#' onclick=\"return unlinkSpouse('{$familyId}');\" data-family-id='$familyId'>" . uiTextSnippet('unlinkindividual') . " ($personID) " . uiTextSnippet('asspouse') . "</a>\n";
                              echo "</div>\n";
                              echo $familyId . "\n";
                              
                              if ($marriagerow[$spouse]) {
                                  $query = "SELECT personID, lastname, lnprefix, firstname, prefix, suffix, nameorder, living, private, branch FROM $people_table WHERE personID = \"{$marriagerow[$spouse]}\"";
                                  $spouseresult = tng_query($query);
                                  $spouserow = tng_fetch_assoc($spouseresult);
                                  
                                  $srights = determineLivingPrivateRights($spouserow);
                                  $spouserow['allow_living'] = $srights['living'];
                                  $spouserow['allow_private'] = $srights['private'];

                                  $birthinfo = $spouserow['birthdate'] ? ' (' . uiTextSnippet('birthabbr') . ' ' . displayDate($spouserow['birthdate']) . ')' : '';
                              } else {
                                  $spouserow = $birthinfo = '';
                              }
                              ?>
                              <span><br><?php echo uiTextSnippet('spouse'); ?>:</span>
                              <span>
                                <?php
                                if (isset($spouserow['personID']) && $spouserow['personID']) {
                                  echo "<a href=\"peopleEdit.php?personID={$spouserow['personID']}&amp;cw=$cw\">" . getName($spouserow) . " - {$spouserow['personID']}</a>$birthinfo";
                                }
                                ?>
                              </span>
                              <?php if ($marriagerow['marrdate'] || $marriagerow['marrplace']) { ?>
                                <span><?php echo uiTextSnippet('married'); ?>:</span>
                                <span><?php echo $marriagerow['marrdate']; ?></span>
                              <?php } ?>
                              <?php
                              $query = "SELECT $people_table.personID AS pID, firstname, lnprefix, lastname, haskids, living, private, branch, prefix, suffix, nameorder FROM ($people_table, children) WHERE $people_table.personID = children.personID AND children.familyID = \"{$familyId}\" ORDER BY ordernum";
                              $children = tng_query($query);

                              if ($children && tng_num_rows($children)) {
                                echo '<p>' . uiTextSnippet('children') . '</p>';

                                $kidcount = 1;
                                while ($child = tng_fetch_assoc($children)) {
                                  $ifkids = $child['haskids'] ? '&gt' : '&nbsp';
                                  $crights = determineLivingPrivateRights($child);
                                  $child['allow_living'] = $crights['living'];
                                  $child['allow_private'] = $crights['private'];
                                  if ($child['firstname'] || $child['lastname']) {
                                    echo "<div class='row'>\n";
                                      echo "<div class='col-sm-2'>$ifkids</div>\n";
                                      echo "<div class='col-md-8'>$kidcount . ";
                                        if ($crights['both']) {
                                          if ($rightbranch) {
                                            echo "<a href=\"peopleEdit.php?personID={$child['pID']}&amp;cw=$cw\">" . getName($child) . " - {$child['pID']}</a>";
                                          } else {
                                            echo getName($child) . " - {$child['pID']}";
                                          }
                                          echo $child['birthdate'] ? ' (' . uiTextSnippet('birthabbr') . ' ' . displayDate($child['birthdate']) . ')' : '';
                                        } else {
                                          echo ($child['private'] ? uiTextSnippet('private') : uiTextSnippet('living')) . ' - ' . $child['pID'];
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
        </table>
      <?php } // ?editconflict ?>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <input name='submit2' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    </footer>
  </form>
</section> <!-- .container -->
<script src='js/associations.js'></script>
<script src='js/citations.js'></script>
<script src='js/notes.js'></script>
<script src='js/more.js'></script>
<script src='js/people.js'></script>
