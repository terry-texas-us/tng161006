<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';
require 'version.php';

$familyID = ucfirst($familyID);
$query = "SELECT *, DATE_FORMAT(changedate,\"%d %b %Y %H:%i:%s\") AS changedate FROM $families_table WHERE familyID = '$familyID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['marrplace'] = preg_replace('/\"/', '&#34;', $row['marrplace']);
$row['sealplace'] = preg_replace('/\"/', '&#34;', $row['sealplace']);
$row['divplace'] = preg_replace('/\"/', '&#34;', $row['divplace']);
$row['notes'] = preg_replace('/\"/', '&#34;', $row['notes']);

if ((!$allowEdit && (!$allowAdd || !$added)) || !checkbranch($row['branch'])) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
$editconflict = determineConflict($row, $families_table);
if ($tngconfig['edit_timeout'] === '') {
  $tngconfig['edit_timeout'] = 15;
}
$warnsecs = (intval($tngconfig['edit_timeout']) - 2) * 60 * 1000;

function getBirth($row) {
  $birthdate = '';
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

$rightbranch = checkbranch($row['branch']);
$rights = determineLivingPrivateRights($row, $rightbranch);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];

$namestr = getFamilyName($row);

$query = "SELECT DISTINCT eventID AS eventID FROM notelinks WHERE persfamID = '$familyID'";
$notelinks = tng_query($query);
$gotnotes = [];
while ($note = tng_fetch_assoc($notelinks)) {
  if (!$note['eventID']) {
    $note['eventID'] = 'general';
  }
  $gotnotes[$note['eventID']] = '*';
}
$citquery = "SELECT DISTINCT eventID FROM citations WHERE persfamID = '$familyID'";
$citresult = tng_query($citquery) or die(uiTextSnippet('cannotexecutequery') . ": $citquery");
$gotcites = [];
while ($cite = tng_fetch_assoc($citresult)) {
  if (!$cite['eventID']) {
    $cite['eventID'] = 'general';
  }
  $gotcites[$cite['eventID']] = '*';
}
$assocquery = "SELECT count(assocID) AS acount FROM associations WHERE personID = '$familyID'";
$assocresult = tng_query($assocquery) or die(uiTextSnippet('cannotexecutequery') . ": $assocquery");
$assocrow = tng_fetch_assoc($assocresult);
$gotassoc = $assocrow['acount'] ? '*' : '';
tng_free_result($assocresult);

$query = "SELECT parenttag FROM events WHERE persfamID = '$familyID'";
$morelinks = tng_query($query);
$gotmore = [];
while ($more = tng_fetch_assoc($morelinks)) {
  $gotmore[$more['parenttag']] = '*';
}
$query = "SELECT $people_table.personID AS pID, firstname, lastname, lnprefix, prefix, suffix, nameorder, birthdate, altbirthdate, living, private, branch FROM $people_table, $children_table WHERE $people_table.personID = $children_table.personID AND $children_table.familyID = '$familyID' ORDER BY ordernum";
$children = tng_query($query);

$kidcount = tng_num_rows($children);

$revstar = checkReview('F');

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('modifyfamily'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='families-modifyfamily'>
  <section class='container'>
    <?php
    $photo = showSmallPhoto($familyID, $namestr, 1, 0, 'F');

    require_once 'eventlib.php';

    echo $adminHeaderSection->build('families-modifyfamily', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'familiesBrowse.php', uiTextSnippet('browse'), 'findfamily']);
    $navList->appendItem([$allowAdd, 'familiesAdd.php', uiTextSnippet('add'), 'addfamily']);
    $navList->appendItem([$allowEdit, 'admin_findreview.php?type=F', uiTextSnippet('review') . $revstar, 'review']);
    $navList->appendItem([$allowEdit, "familiesEdit.php?familyID=$familyID", uiTextSnippet('edit'), 'edit']);
    echo $navList->build('edit');
    ?>
    <br>
    <header id='family-header'>
      <div class='row'>
        <div class='col-sm-12' id='thumbholder' style="margin-right: 5px; <?php if (!$photo) {} ?>">
          <?php echo $photo; ?>
          <h4><?php echo $namestr; ?></h4>
          <?php
          if ($editconflict) {
            echo '<br><p>' . uiTextSnippet('editconflict') . "</p>\n";
            echo "<p><strong><a href='familiesEdit.php?familyID=$familyID'>" . uiTextSnippet('retry') . "</a></strong></p>\n";
          } else {
            $iconColor = $gotassoc ? 'icon-info' : 'icon-muted';
            echo "<a id='family-associations' href='#' data-family-id='$familyID' title='" . uiTextSnippet('associations') . "'>\n";
            echo "<img class='icon-md icon-associations $iconColor' data-src='svg/connections.svg'>\n";
            echo "</a>\n";

            $iconColor = $gotnotes['general'] ? 'icon-info' : 'icon-muted';
            echo "<a id='family-notes' href='#' data-family-id='$familyID' title='" . uiTextSnippet('notes') . "'>\n";
            echo "<img class='icon-sm icon-right icon-notes $iconColor' data-src='svg/documents.svg'>\n";
            echo "</a>\n";

            $iconColor = $gotcites['general'] ? 'icon-info' : 'icon-muted';
            echo "<a id='family-citations' href='#' data-family-id='$familyID' title='" . uiTextSnippet('citations') . "'>\n";
            echo "<img class='icon-sm icon-right icon-citations $iconColor' data-src='svg/archive.svg'>\n";
            echo "</a>\n";
          }
          ?>
        </div>
      </div>
    </header>
    <br>
    <a href="familiesShowFamily.php?familyID=<?php echo $familyID; ?>" title='<?php echo uiTextSnippet('preview') ?>'>
      <img class='icon-sm' src='svg/eye.svg'>
    </a>
    <?php if ($allowAdd) { ?>
      <a id='addmedia-family' href='#'><?php echo uiTextSnippet('addmedia'); ?></a>
    <?php } ?>
    <div class='small'>
      <a id='expandall' href='#'><?php echo uiTextSnippet('expandall'); ?></a>
      <a id='collapseall' href='#'><?php echo uiTextSnippet('collapseall'); ?></a>
    </div>
    <form id='form1' name='form1' action='familiesEditFormAction.php' method='post'>
      <table class='table table-sm'>
        <?php
        if (!$editconflict) {
          ?>
          <tr>
            <td>
              <?php echo displayToggle('plus0', 1, 'spouses', uiTextSnippet('spouses'), ''); ?>

              <div id='spouses'>
                <table class='table table-sm'>
                  <?php
                  if ($row['husband']) {
                    $query = "SELECT lastname, lnprefix, firstname, prefix, suffix, nameorder, birthdate, altbirthdate FROM $people_table WHERE personID = \"{$row['husband']}\"";
                    $spouseresult = tng_query($query);
                    $spouserow = tng_fetch_assoc($spouseresult);
                    tng_free_result($spouseresult);

                    $srights = determineLivingPrivateRights($spouserow);
                    $spouserow['allow_living'] = $srights['living'];
                    $spouserow['allow_private'] = $srights['private'];

                    $husbstr = getName($spouserow) . getBirth($spouserow) . ' - ' . $row['husband'];
                    $husbstr = preg_replace('/\"/', '&#34;', $husbstr);
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
                      <input id='find-husband' type='button' value="<?php echo uiTextSnippet('find'); ?>" data-assigned-branch='<?php echo $assignedbranch; ?>'>
                      <input id='create-husband' type='button' value="<?php echo uiTextSnippet('create'); ?>">
                      <input id='edit-husband' type='button' value="  <?php echo uiTextSnippet('edit'); ?>  ">
                      <input id='remove-husband' type='button' value="<?php echo uiTextSnippet('remove'); ?>">
                    </td>
                  </tr>
                  <?php
                  if ($row['wife']) {
                    $query = "SELECT lastname, lnprefix, firstname, prefix, suffix, nameorder, birthdate, altbirthdate FROM $people_table WHERE personID = \"{$row['wife']}\"";
                    $spouseresult = tng_query($query);
                    $spouserow = tng_fetch_assoc($spouseresult);
                    tng_free_result($spouseresult);

                    $srights = determineLivingPrivateRights($spouserow);
                    $spouserow['allow_living'] = $srights['living'];
                    $spouserow['allow_private'] = $srights['private'];

                    $wifestr = getName($spouserow) . getBirth($spouserow) . ' - ' . $row['wife'];
                    $wifestr = preg_replace('/\"/', '&#34;', $wifestr);
                  } else {
                    $spouserow = '';
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
                      <input id='find-wife' type='button' value="<?php echo uiTextSnippet('find'); ?>" data-assigned-branch='<?php echo $assignedbranch; ?>'>
                      <input id='create-wife' type='button' value="<?php echo uiTextSnippet('create'); ?>">
                      <input id='edit-wife' type='button' value="  <?php echo uiTextSnippet('edit'); ?>  ">
                      <input id='remove-wife' type='button' value="<?php echo uiTextSnippet('remove'); ?>">
                    </td>
                  </tr>
                </table>

                <table class='table table-sm'>
                  <tr>
                    <td>
                      <input name='living' type='checkbox' value='1'<?php if ($row['living']) {echo ' checked';} ?>> <?php echo uiTextSnippet('living'); ?>
                      <input name='private' type='checkbox' value='1'<?php if ($row['private']) {echo " checked=\"$checked\"";} ?>> <?php echo uiTextSnippet('private'); ?>
                    </td>
                    <td></td>
                    <td><?php echo uiTextSnippet('branch') . ': '; ?>

                      <?php
                      $query = "SELECT branch, description FROM $branches_table ORDER BY description";
                      $branchresult = tng_query($query);
                      $branchlist = explode(',', $row['branch']);

                      $descriptions = [];
                      $options = '';
                      while ($branchrow = tng_fetch_assoc($branchresult)) {
                        $options .= "  <option value=\"{$branchrow['branch']}\"";
                        if (in_array($branchrow['branch'], $branchlist)) {
                          $options .= ' selected';
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
                        $select = $totbranches >= 8 ? uiTextSnippet('scrollbranch') . '<br>' : '';
                        $select .= "<select id='branch' name=\"branch[]\" multiple size=\"$selectnum\" style=\"overflow:auto\">\n";
                        $select .= "  <option value=''";
                        if ($row['branch'] == '') {
                          $select .= ' selected';
                        }
                        $select .= '>' . uiTextSnippet('nobranch') . "</option>\n";

                        $select .= "$options</select>\n";
                        echo " &nbsp;<span>(<a id='show-branchedit' href='#'>\n";
                        echo "<img src='img/ArrowDown.gif'>" . uiTextSnippet('edit') . '</a> )</span><br>';
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
              <?php echo displayToggle('plus1', 1, 'events', uiTextSnippet('events'), ''); ?>

              <div id='events'>
                <p><?php echo uiTextSnippet('datenote'); ?></p>
                <table>
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
                      <input id='addnew-event' type='button' value=" <?php echo uiTextSnippet('addnew'); ?> " data-family-id='<?php echo $familyID; ?>'>
                    </td>
                  </tr>
                </table>
                <?php showCustEvents($familyID); ?>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <?php echo displayToggle('plus2', 1, 'children', uiTextSnippet('children') . " (<span id=\"childcount\">$kidcount</span>)", ''); ?>
              <div id='children'>
                <table id="ordertbl">
                  <tr>
                    <td style="width:55px"><span><?php echo uiTextSnippet('text_sort'); ?></span></td>
                    <td><?php echo uiTextSnippet('child'); ?></td>
                  </tr>
                </table>
                <div id='childrenlist'>
                  <?php
                  if ($children && $kidcount) {
                    while ($child = tng_fetch_assoc($children)) {
                      $crights = determineLivingPrivateRights($child);
                      $child['allow_living'] = $crights['living'];
                      $child['allow_private'] = $crights['private'];
                      if ($child['firstname'] || $child['lastname']) {
                        $childId = $child['pID'];
                        echo "<div class='sortrow' id='child_{$childId}' data-child-id='{$childId}' data-allow-delete='{$allowDelete}' style='width: 500px; clear: both'>\n";
                        echo "<table class='table table-sm'>\n";
                        echo "<tr>\n";
                        echo "<td class='dragarea'>";
                        echo "<img src='img/admArrowUp.gif' alt=''>" . uiTextSnippet('drag') . "\n";
                        echo "<img src='img/admArrowDown.gif' alt=''>\n";
                        echo "</td>\n";
                        echo "<td class='childblock'>\n";
                          if ($allowDelete) {
                            echo "<div class='small hide-right' id='unlinkc_{$childId}'>\n";
                            echo "<a id='remove-child' href='#' data-child-id='{$childId}'>" . uiTextSnippet('remove') . "</a> &nbsp; | &nbsp;\n";
                            echo "<a id='delete-child' href='#' data-child-id='{$childId}'>" . uiTextSnippet('delete') . "</a>\n";
                            echo '</div>';
                          }
                          if ($crights['both']) {
                            if ($child['birthdate']) {
                              $birthstring = uiTextSnippet('birthabbr') . ' ' . displayDate($child['birthdate']);
                            } else {
                              if ($child['altbirthdate']) {
                                $birthstring = uiTextSnippet('chrabbr') . ' ' . displayDate($child['altbirthdate']);
                              } else {
                                $birthstring = uiTextSnippet('nobirthinfo');
                              }
                            }
                            echo "<a id='edit-child' href='#' data-child-id='{$childId}'>" . getName($child) . "</a>\n";
                            echo " - {$childId}<br>$birthstring";
                          } else {
                            echo uiTextSnippet('living') . ' - ' . $childId;
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
                </div> <!-- .childrenslist -->

                <input id='newmedia' name='media' type='hidden' value=''>
                <p><?php echo uiTextSnippet('newchildren'); ?>:
                  <input id='find-children' type='button' value="<?php echo uiTextSnippet('find'); ?>" data-assigned-branch='<?php echo $assignedbranch; ?>'>
                  <input id='create-child' type='button' value="<?php echo uiTextSnippet('create'); ?>">
                  <input name='familyID' type='hidden' value="<?php echo "$familyID"; ?>"/>
                </p>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <?php
              echo uiTextSnippet('onsave') . ':<br>';
              echo "<input name='newfamily' type='radio' value='return'> " . uiTextSnippet('savereturn') . "<br>\n";
              if ($cw) {
                echo "<input name='newfamily' type='radio' value='close' checked> " . uiTextSnippet('closewindow') . "\n";
              } else {
                echo "<input name='newfamily' type='radio' value='none' checked> " . uiTextSnippet('saveback') . "\n";
              }
              ?>
              <br><br><input name='submit2' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
              <?php if (!$rights['lds']) { ?>
                <input name='sealdate' type='hidden' value="<?php echo $row['sealdate']; ?>">
                <input name='sealsrc' type='hidden' value="<?php echo $row['sealsrc']; ?>">
                <input name='sealplace' type='hidden' value="<?php echo $row['sealplace']; ?>">
              <?php } ?>
              <input name='cw' type='hidden' value="<?php echo "$cw"; ?>">
            </td>
          </tr>
          <?php
        } //end of the editconflict conditional
        ?>
      </table>
    </form>
    <span class="smallest"><?php echo uiTextSnippet('lastmodified') . ": {$row['changedate']} ({$row['changedby']})"; ?></span>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/admin.js"></script>
<script src="js/selectutils.js"></script>
<script src="js/datevalidation.js"></script>
<script src="js/associations.js"></script>
<script src="js/citations.js"></script>
<script src="js/notes.js"></script>
<script src='js/families.js'></script>
<script>
var tnglitbox;
  var nplitbox;
  var activeidbox = null;
  var activenamebox = null;

  var preferEuro = <?php echo($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : 'false'); ?>;
  var preferDateFormat = '<?php echo $preferDateFormat; ?>';

  var allow_cites = true;
  var allow_notes = true;
 
  var childcount = <?php echo $kidcount; ?>;

  $('#addmedia-family').on('click', function () {
      if (confirm(textSnippet('savefirst'))) {
        $('#newmedia').val(1);
        document.form1.submit();
      }
      return false;
  });
  
  $('#expandall').on('click', function () {
      toggleAll('on');
  });
    
  $('#collapseall').on('click', function () {
      toggleAll('off');
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
      newEvent('F', $familyId);
  });
  
  function toggleAll(display) {
    toggleSection('spouses', 'plus0', display);
    toggleSection('events', 'plus1', display);
    toggleSection('children', 'plus2', display);
    return false;
  }

  function startSort() {
    $('#childrenlist').sortable({
      helper: 'clone',
      axis: 'y',
      scroll: false,
      items: '.sortrow',
      update: updateChildrenOrder
    });
  }

  $(document).ready(startSort);

  function updateChildrenOrder(id) {
    var childrenlist = removePrefixFromArray($('#childrenlist').sortable('toArray'), 'child_');

    var params = {
      sequence: childrenlist.join(','),
      action: 'childorder',
      familyID: document.form1.familyID.value
    };
    $.ajax({
      url: 'ajx_updateorder.php',
      data: params,
      dataType: 'html'
    });
  }

  function unlinkChild(personID, action) {
    var confmsg = action === "child_delete" ? textSnippet('confdeletepers') : textSnippet('confremchild');
    if (confirm(confmsg)) {
      var params = {
        personID: personID,
        familyID: document.form1.familyID.value,
        t: action
      };
      $.ajax({
        url: 'ajx_delete.php',
        data: params,
        success: function (req) {
          $('#child_' + personID).fadeOut(300, function () {
            $('#child_' + personID).remove();
            childcount -= 1;
            $('#childcount').html(childcount);
          });
        }
      });
    }
    return false;
  }

  function EditChild(id) {
    deepOpen('peopleEdit.php?personID=' + id + '&cw=1', 'editchild');
  }

  function saveNewPerson(form) {
    form.personID.value = TrimString(form.personID.value);
    var personID = form.personID.value;
    if (personID.length === 0) {
      alert(textSnippet('enterpersonid'));
    } else {
      var params = $(form).serialize();
      params += '&order=' + (childcount + 1);
      $.ajax({
        url: 'admin_addperson2.php',
        data: params,
        type: 'post',
        dataType: 'html',
        success: function (req) {
          if (req.indexOf('error') >= 0) {
            var vars = eval('(' + req + ')');
            $('#errormsg').html(vars.error);
            $('#errormsg').show();
          } else {
            nplitbox.remove();
            if (form.type.value === "child") {
              $('#childrenlist').append(req);
              $('#child_' + personID).fadeIn(400);
              childcount += 1;
              $('#childcount').html(childcount);
              startSort();
            } else if (form.type.value === 'spouse') {
              var vars = eval('(' + req + ')');
              $('#' + activenamebox).val(vars.name + ' - ' + vars.id);
              $('#' + activenamebox).effect('highlight', {}, 400);
              $('#' + activeidbox).val(vars.id);
            }
          }
        }
      });
    }
    return false;
  }

  function lockExpiring() {
    alert(textSnippet('lockexpiring'));
  }

  <?php if (!$editconflict && $warnsecs >= 0) { ?>
    setTimeout(lockExpiring, <?php echo $warnsecs; ?>);
  <?php } ?>
</script>
</body>
</html>
