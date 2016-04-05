<?php
require 'begin.php';
require 'adminlib.php';
if (!$familyID) {
  die("no args");
}
require 'checklogin.php';

initMediaTypes();

function getBirth($row) {

  $birthdate = "";
  if ($row['birthdate']) {
    $birthdate = uiTextSnippet('birthabbr') . ' ' . displayDate($row['birthdate']);
  } else {
    if ($row['altbirthdate']) {
      $birthdate = uiTextSnippet('chrabbr') . ' ' . displayDate($row['altbirthdate']);
    }
  }
  if ($birthdate) {
    $birthdate = " ($birthdate)";
  }
  return $birthdate;
}

$familyID = ucfirst($familyID);
$query = "SELECT *, DATE_FORMAT(changedate,\"%d %b %Y %H:%i:%s\") as changedate FROM $families_table WHERE familyID = \"$familyID\" AND gedcom = \"$tree\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['marrplace'] = preg_replace("/\"/", "&#34;", $row['marrplace']);
$row['sealplace'] = preg_replace("/\"/", "&#34;", $row['sealplace']);
$row['divplace'] = preg_replace("/\"/", "&#34;", $row['divplace']);
$row['notes'] = preg_replace("/\"/", "&#34;", $row['notes']);

if ((!$allowEdit && (!$allowAdd || !$added)) || ($assignedtree && $assignedtree != $tree) || !checkbranch($row['branch'])) {
  $message = uiTextSnippet('norights');
  header("Location: ajx_login.php?message=" . urlencode($message));
  exit;
}

$editconflict = determineConflict($row, $families_table);

$righttree = checktree($tree);
$rightbranch = $righttree ? checkbranch($row['branch']) : false;
$rights = determineLivingPrivateRights($row, $righttree, $rightbranch);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];

$namestr = getFamilyName($row);

$query = "SELECT treename FROM $treesTable WHERE gedcom = \"$tree\"";
$result = tng_query($query);
$treerow = tng_fetch_assoc($result);
tng_free_result($result);

$query = "SELECT DISTINCT eventID as eventID FROM $notelinks_table WHERE persfamID=\"$familyID\" AND gedcom =\"$tree\"";
$notelinks = tng_query($query);
$gotnotes = array();
while ($note = tng_fetch_assoc($notelinks)) {
  if (!$note['eventID']) {
    $note['eventID'] = "general";
  }
  $gotnotes[$note['eventID']] = "*";
}

$citquery = "SELECT DISTINCT eventID FROM $citations_table WHERE persfamID = \"$familyID\" AND gedcom = \"$tree\"";
$citresult = tng_query($citquery) or die(uiTextSnippet('cannotexecutequery') . ": $citquery");
$gotcites = array();
while ($cite = tng_fetch_assoc($citresult)) {
  if (!$cite['eventID']) {
    $cite['eventID'] = "general";
  }
  $gotcites[$cite['eventID']] = "*";
}

$assocquery = "SELECT count(assocID) as acount FROM $assoc_table WHERE personID = \"$familyID\" AND gedcom = \"$tree\"";
$assocresult = tng_query($assocquery) or die(uiTextSnippet('cannotexecutequery') . ": $assocquery");
$assocrow = tng_fetch_assoc($assocresult);
$gotassoc = $assocrow['acount'] ? "*" : "";
tng_free_result($assocresult);

$query = "SELECT parenttag FROM $events_table WHERE persfamID=\"$familyID\" AND gedcom =\"$tree\"";
$morelinks = tng_query($query);
$gotmore = array();
while ($more = tng_fetch_assoc($morelinks)) {
  $gotmore[$more['parenttag']] = "*";
}

$query = "SELECT $people_table.personID as pID, firstname, lastname, lnprefix, prefix, suffix, nameorder, birthdate, altbirthdate, living, private, branch FROM $people_table, $children_table WHERE $people_table.personID = $children_table.personID AND $children_table.familyID = \"$familyID\" AND $people_table.gedcom = \"$tree\" AND $children_table.gedcom = \"$tree\" ORDER BY ordernum";
$children = tng_query($query);

$kidcount = tng_num_rows($children);

$helplang = findhelp("families_help.php");

$photo = showSmallPhoto($familyID, $namestr, 1, 0, true);
header("Content-type:text/html; charset=" . $session_charset);

$righttree = checktree($tree);

require_once 'eventlib.php';
?>
<form id='famform1' name='famform1' action='' method='post' onsubmit="return updateFamily(this, <?php echo $slot; ?>, 'familiesEditFormAction.php');">
  <header class='modal-header'>
    <div id='thumbholder' style="margin-right: 5px; <?php if (!$photo) {echo "display: none";} ?>">
      <?php echo $photo; ?>
    </div>
    <h4><?php echo $namestr; ?></h4>
    <div class='smallest'>
      <?php
      if ($editconflict) {
        echo "<br><p>" . uiTextSnippet('editconflict') . "</p>";
      } else {
        $iconColor = $gotassoc ? "icon-info" : "icon-muted";
        echo "<a id='family-associations' href='#' data-family-id='$familyID' data-tree='$tree' title='" . uiTextSnippet('associations') . "'>\n";
        echo "<img class='icon-md icon-associations $iconColor' data-src='svg/connections.svg'>\n";
        echo "</a>\n";

        $iconColor = $gotnotes['general'] ? "icon-info" : "icon-muted";
        echo "<a id='family-notes' href='#' data-family-id='$familyID' title='" . uiTextSnippet('notes') . "'>\n";
        echo "<img class='icon-sm icon-right icon-notes $iconColor' data-src='svg/documents.svg'>\n";
        echo "</a>\n";
        
        $iconColor = $gotcites['general'] ? "icon-info" : "icon-muted";
        echo "<a id='family-citations' href='#' data-family-id='$familyID' title='" . uiTextSnippet('citations') . "'>\n";
        echo "<img class='icon-sm icon-right icon-citations $iconColor' data-src='svg/archive.svg'>\n";
        echo "</a>\n";
      }
      ?>
      <br clear='all'>
    </div>
    <span class="smallest"><?php echo uiTextSnippet('lastmodified') . ": {$row['changedate']} ({$row['changedby']})"; ?></span>
  </header>
  <div class='modal-body'>
    <table class='table table-sm'>
      <?php if (!$editconflict) { ?>
        <tr>
          <td>
            <?php echo displayToggle("plus0", 1, "spouses", uiTextSnippet('spouses'), ""); ?>

            <div id="spouses">
              <table class='table table-sm'>
                <?php
                if ($row['husband']) {
                  $query = "SELECT lastname, lnprefix, firstname, prefix, suffix, nameorder, living, private, branch, birthdate, altbirthdate FROM $people_table WHERE personID = \"{$row['husband']}\" AND gedcom = \"$tree\"";
                  $spouseresult = tng_query($query);
                  $spouserow = tng_fetch_assoc($spouseresult);
                  tng_free_result($spouseresult);
                }
                if ($row['husband']) {
                  $hrights = determineLivingPrivateRights($spouserow, $righttree);
                  $spouserow['allow_living'] = $hrights['living'];
                  $spouserow['allow_private'] = $hrights['private'];
                  $husbstr = getName($spouserow) . getBirth($spouserow) . " - " . $row['husband'];
                }
                if (!isset($husbstr)) {
                  $husbstr = uiTextSnippet('clickfind');
                }
                ?>
                <tr>
                  <td><?php echo uiTextSnippet('husband'); ?>:</td>
                  <td>
                    <input id='husbnameplusid' name='husbnameplusid' type='text' value="<?php echo "$husbstr"; ?>" readonly>
                    <input id='husband' name='husband' type='hidden' value="<?php echo $row['husband']; ?>">
                    <input id='find-husband' type='button' value="<?php echo uiTextSnippet('find'); ?>" data-tree='<?php echo $tree; ?>' data-assigned-branch='<?php echo $assignedbranch; ?>'>
                    <input id='addnew-husband' type='button' value="<?php echo uiTextSnippet('addnew'); ?>">
                    <input id='edit-husband' type='button' value="  <?php echo uiTextSnippet('edit'); ?>  ">
                    <input id='remove-husband' type='button' value="<?php echo uiTextSnippet('remove'); ?>">
                  </td>
                </tr>
                <?php
                if ($row['wife']) {
                  $query = "SELECT lastname, lnprefix, firstname, prefix, suffix, nameorder, living, private, branch, birthdate, altbirthdate FROM $people_table WHERE personID = \"{$row['wife']}\" AND gedcom = \"$tree\"";
                  $spouseresult = tng_query($query);
                  $spouserow = tng_fetch_assoc($spouseresult);
                  tng_free_result($spouseresult);
                } else {
                  $spouserow = "";
                }
                if ($row['wife']) {
                  $wrights = determineLivingPrivateRights($spouserow, $righttree);
                  $spouserow['allow_living'] = $wrights['living'];
                  $spouserow['allow_private'] = $wrights['private'];
                  $wifestr = getName($spouserow) . getBirth($spouserow) . " - " . $row['wife'];
                }
                if (!isset($wifestr)) {
                  $wifestr = uiTextSnippet('clickfind');
                }
                ?>
                <tr>
                  <td><?php echo uiTextSnippet('wife'); ?>:</td>
                  <td>
                    <input id='wifenameplusid' name='wifenameplusid' type='text' value="<?php echo "$wifestr"; ?>" readonly>
                    <input id='wife' name='wife' type='hidden' value="<?php echo $row['wife']; ?>">
                    <input id='find-wife' type='button' value="<?php echo uiTextSnippet('find'); ?>" data-tree='<?php echo $tree; ?>' data-assigned-branch='<?php echo $assignedbranch; ?>'>
                    <input id='addnew-wife' type='button' value="<?php echo uiTextSnippet('addnew'); ?>">
                    <input id='edit-wife' type='button' value="  <?php echo uiTextSnippet('edit'); ?>  ">
                    <input id='remove-wife' type='button' value="<?php echo uiTextSnippet('remove'); ?>">
                  </td>
                </tr>
              </table>

              <table class='table table-sm'>
                <tr>
                  <td>
                    <input name='living' type='checkbox' value='1'<?php if ($row['living']) {echo " checked";} ?>> <?php echo uiTextSnippet('living'); ?>
                    &nbsp;&nbsp;
                    <input name='private' type='checkbox' value='1'<?php if ($row['private']) {echo " checked=\"$checked\"";} ?>> <?php echo uiTextSnippet('private'); ?>
                  </td>
                  <td><?php echo uiTextSnippet('tree') . ": " . $treerow['treename']; ?></td>
                  <td><?php echo uiTextSnippet('branch') . ": "; ?>

                    <?php
                    $query = "SELECT branch, description FROM $branches_table WHERE gedcom = \"$tree\" ORDER BY description";
                    $branchresult = tng_query($query);
                    $branchlist = explode(",", $row['branch']);

                    $descriptions = array();
                    $options = "";
                    while ($branchrow = tng_fetch_assoc($branchresult)) {
                      $options .= "  <option value=\"{$branchrow['branch']}\"";
                      if (in_array($branchrow['branch'], $branchlist)) {
                        $options .= " selected";
                        $descriptions[] = $branchrow['description'];
                      }
                      $options .= ">{$branchrow['description']}</option>\n";
                    }
                    $desclist = count($descriptions) ? implode(', ', $descriptions) : uiTextSnippet('nobranch');
                    echo "<span id='branchlist'>$desclist</span>";
                    if (!$assignedbranch) {
                      $totbranches = tng_num_rows($branchresult) + 1;
                      if ($totbranches < 2) {
                        $totbranches = 2;
                      }
                      $selectnum = $totbranches < 8 ? $totbranches : 8;
                      $select = $totbranches >= 8 ? uiTextSnippet('scrollbranch') . "<br>" : "";
                      $select .= "<select id='branch' name=\"branch[]\" multiple size=\"$selectnum\" style=\"overflow:auto\">\n";
                      $select .= "  <option value=''";
                      if ($row['branch'] == "") {
                        $select .= " selected";
                      }
                      $select .= ">" . uiTextSnippet('nobranch') . "</option>\n";

                      $select .= "$options</select>\n";

                      echo " &nbsp;<span>(<a id='show-branchedit' href='#'>\n";
                      echo "<img src='img/ArrowDown.gif'>" . uiTextSnippet('edit') . "</a> )</span><br>";
                      ?>
                      <div id='branchedit' style='position: absolute; display: none;'>
                        <?php echo $select; ?>
                      </div>
                    <?php 
                    } else {
                      echo "<input name='branch' type='hidden' value=\"{$row['branch']}\">";
                    }
                    echo "<input name='orgbranch' type='hidden' value=\"{$row['branch']}\">";
                    ?>
                  </td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <?php echo displayToggle("plus1", 1, "events", uiTextSnippet('events'), ""); ?>

            <div id='events'>
              <p class='smallest'><?php echo uiTextSnippet('datenote'); ?></p>
              <table class='table table-sm'>
                <tr>
                  <td>&nbsp;</td>
                  <td><?php echo uiTextSnippet('date'); ?></td>
                  <td><?php echo uiTextSnippet('place'); ?></td>
                  <td colspan='4'>&nbsp;</td>
                </tr>
                <?php
                echo showEventRow('marrdate', 'marrplace', 'MARR', $familyID);
                ?>
                <tr>
                  <td><?php echo uiTextSnippet('marriagetype'); ?>:</td>
                  <td colspan='6'>
                    <input name='marrtype' type='text' value="<?php echo $row['marrtype']; ?>" style='width: 494px' maxlength='50'>
                  </td>
                </tr>
                <?php
                if ($rights['lds']) {
                  echo showEventRow('sealdate', 'sealplace', 'SLGS', $familyID);
                }
                echo showEventRow('divdate', 'divplace', 'DIV', $familyID);
                ?>
                <tr>
                  <td colspan='7'>&nbsp;</td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('otherevents'); ?>:</td>
                  <td colspan='6'>
                    <input id='addnew-event' type='button' value=" <?php echo uiTextSnippet('addnew'); ?> " data-family-id='<?php echo $familyID; ?>' data-tree='<?php echo $tree; ?>'>
                  </td>
                </tr>
              </table>
              <?php showCustEvents($familyID); ?>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <?php echo displayToggle("plus2", 1, "children", uiTextSnippet('children') . " (<span id=\"childcount\">$kidcount</span>)", ""); ?>
            <div id='children'>
              <table id="ordertbl">
                <tr>
                  <td style="width:55px"><?php echo uiTextSnippet('text_sort'); ?></td>
                  <td><?php echo uiTextSnippet('child'); ?></td>
                </tr>
              </table>
              <div id='childrenlist'>
                <?php
                if ($children && $kidcount) {
                  while ($child = tng_fetch_assoc($children)) {
                    $crights = determineLivingPrivateRights($child, $righttree);
                    $child['allow_living'] = $crights['living'];
                    $child['allow_private'] = $crights['private'];
                    if ($child['firstname'] || $child['lastname']) {
                      $childId = $child['pID'];
                      echo "<div class='sortrow' id='child_{$childId}' data-child-id='{$childId}' data-allow-delete='{$allowDelete}' style='width: 500px; clear: both'";
                      echo ">\n";
                        echo "<table class='table table-sm'>\n";
                          echo "<tr>\n";
                            echo "<td class='dragarea'>";
                              echo "<img src='img/admArrowUp.gif' alt=''>" . uiTextSnippet('drag') . "\n";
                              echo "<img src='img/admArrowDown.gif' alt=''>\n";
                            echo "</td>\n";
                            echo "<td class='childblock'>\n";
                              if ($allowDelete) {
                                echo "<div class='small hide-right' id=\"unlinkc_{$childId}\">\n";
                                  echo "<a id='remove-child' href='#' data-child-id='{$childId}'>" . uiTextSnippet('remove') . "</a> &nbsp; | &nbsp;\n";
                                  echo "<a id='delete-child' href='#' data-child-id='{$childId}'>" . uiTextSnippet('delete') . "</a>\n";
                                echo "</div>";
                              }
                              if ($crights['both']) {
                                if ($child['birthdate']) {
                                  $birthstring = uiTextSnippet('birthabbr') . " " . displayDate($child['birthdate']);
                                } else {
                                  if ($child['altbirthdate']) {
                                    $birthstring = uiTextSnippet('chrabbr') . " " . displayDate($child['altbirthdate']);
                                  } else {
                                    $birthstring = uiTextSnippet('nobirthinfo');
                                  }
                                }
                                echo getName($child);
                                echo " - {$childId}<br>$birthstring";
                              } else {
                                echo uiTextSnippet('living') . " - " . $childId;
                              }
                            echo "</td>\n";
                          echo "</tr>\n";
                        echo "</table>\n";
                      echo "</div>\n";
                    }
                  }
                  tng_free_result($children);
                }
                ?>
              </div> <!-- .childrenlist -->

              <input name='tree' type='hidden' value="<?php echo $tree; ?>">
              <p><?php echo uiTextSnippet('newchildren'); ?>:
                <input id='find-children' type='button' value="<?php echo uiTextSnippet('find'); ?>" data-tree='<?php echo $tree; ?>' data-assigned-branch='<?php echo $assignedbranch; ?>'>
                <input id='addnew-child' type='button' value="<?php echo uiTextSnippet('addnew'); ?>" data-family-id='<?php echo $familyID; ?>' data-assigned-branch='<?php echo $assignedbranch; ?>'>
                <input name='familyID' type='hidden' value="<?php echo "$familyID"; ?>">
                <input name='lastperson' type='hidden' value="<?php echo "$lastperson"; ?>">
                <input name='newfamily' type='hidden' value='ajax'>
                <?php if (!$rights['lds']) { ?>
                  <input name='sealdate' type='hidden' value="<?php echo $row['sealdate']; ?>">
                  <input name='sealsrc' type='hidden' value="<?php echo $row['sealsrc']; ?>">
                  <input name='sealplace' type='hidden' value="<?php echo $row['sealplace']; ?>">
                <?php } ?>
              </p>
            </div>
          </td>
        </tr>
      <?php } //end of the editconflict conditional ?>
    </table>
  </div> <!-- .modal-body -->
  <footer class='modal-footer'>
    <input name='submit2' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
  </footer>
</form>
<script src='js/associations.js'></script>
<script src='js/citations.js'></script>
<script src='js/notes.js'></script>
<script src='js/families.js'></script>
<script>
  $('#find-husband').on('click', function () {
      var $tree = $(this).data('tree');
      var $assignedBranch = $(this).data('assignedBranch');
      return findItem('I', 'husband', 'husbnameplusid', $tree, $assignedBranch);
  });

  $('#addnew-husband').on('click', function () {
      return newPerson('M', 'spouse');
  });

  $('#edit-husband').on('click', function () {
      editPerson(document.famform1.husband.value, 0, 'M');
  });

  $('#remove-husband').on('click', function () {
      removeSpouse(document.famform1.husband, document.famform1.husbnameplusid);
  });
  
  $('#find-wife').on('click', function () {
      var $tree = $(this).data('tree');
      var $assignedBranch = $(this).data('assignedBranch');
      return findItem('I', 'wife', 'wifenameplusid', $tree, $assignedBranch);
  });

  $('#addnew-wife').on('click', function () {
      return newPerson('F', 'spouse');
  });

  $('#edit-wife').on('click', function () {
      editPerson(document.famform1.wife.value, 0, 'F');
  });

  $('#remove-wife').on('click', function () {
      removeSpouse(document.famform1.wife, document.famform1.wifenameplusid);
  });
  
  $('#show-branchedit').on('click', function () {
      $('#branchedit').slideToggle(200);
      quitBranchEdit('branchedit');
      return false;
  });

  $('#branchedit').on('mouseover', function () {
      clearTimeout(branchtimer);
  });
    
  $('#branchedit').on('mouseout', function () {
      closeBranchEdit('branch', 'branchedit', 'branchlist');
  });

  $('#addnew-event').on('click', function () {
      var $familyId = $(this).data('familyId');
      var $tree = $(this).data('tree');
      newEvent('F',$familyId, $tree);
  });
  
  $('#childrenlist .sortrow').on('mouseover', function () {
      if ($(this).data('allowDelete')) {
          var $childId = $(this).data('childId');
          $('#unlinkc_' + $childId).css('visibility', 'visible');
      }
  });

  $('#childrenlist .sortrow').on('mouseout', function () {
      if ($(this).data('allowDelete')) {
          var $childId = $(this).data('childId');
          $('#unlinkc_' + $childId).css('visibility', 'hidden');
      }
  });

  $('#find-children').on('click', function () {
      var $tree = $(this).data('tree');
      return findItem('I', 'children', null, $tree);
  });

  $('#addnew-child').on('click', function () {
      var $familyId = $(this).data('familyId');
      var $assignedBranch = $(this).data('assignedBranch');
      return newPerson('', 'child', document.famform1.husband.value, $familyId, $assignedBranch);
  });
  
  $('#remove-child').on('click', function () {
      var element = document.getElementById('remove-child');
      return unlinkChild(element.dataset.childId, 'child_unlink');
  });

  $('#delete-child').on('click', function () {
      var element = document.getElementById('delete-child');
      return unlinkChild(element.dataset.childId, 'child_delete');
  });  
</script>