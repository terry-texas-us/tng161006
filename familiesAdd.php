<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowAdd) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

if ($child) {
  $newperson = $child;
} else {
  if ($husband) {
    $newperson = $husband;
  } else {
    if ($wife) {
      $newperson = $wife;
    } else {
      $newperson = "";
    }
  }
}

if ($newperson) {
  $query = "SELECT personID, firstname, lnprefix, lastname, prefix, suffix, nameorder, living, private, branch, gedcom FROM $people_table WHERE personID = \"$newperson\" AND gedcom = \"$tree\"";
  $result = tng_query($query);
  $newpersonrow = tng_fetch_assoc($result);

  $righttree = checktree($tree);
  $rightbranch = $righttree ? checkbranch($newpersonrow['branch']) : false;
  $rights = determineLivingPrivateRights($newpersonrow, $righttree, $rightbranch);
  $newpersonrow['allow_living'] = $rights['living'];
  $newpersonrow['allow_private'] = $rights['private'];
  tng_free_result($result);
}

if ($husband) {
  $husbstr = getName($newpersonrow) . " - $husband";
} else {
  if ($wife) {
    $wifestr = getName($newpersonrow) . " - $wife";
  }
}
if (!isset($husbstr)) {
  $husbstr = uiTextSnippet('clickfind');
}
if (!isset($wifestr)) {
  $wifestr = uiTextSnippet('clickfind');
}

if ($assignedtree) {
  $wherestr = "WHERE gedcom = \"$assignedtree\"";
} else {
  $wherestr = "";
}
$query = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";
$result = tng_query($query);

$revstar = checkReview('F');

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('addnewfamily'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='newfamily'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('families-addnewfamily', $message);
    $navList = new navList('');
    $navList->appendItem([true, "familiesBrowse.php", uiTextSnippet('browse'), "findfamily"]);
//    $navList->appendItem([$allowAdd, "familiesAdd.php", uiTextSnippet('add'), "addfamily"]);
    $navList->appendItem([$allowEdit, "admin_findreview.php?type=F", uiTextSnippet('review') . $revstar, "review"]);
    echo $navList->build("addfamily");
    ?>
    <div class='small'>
      <a href='#' onClick="toggleAll('on');"><?php echo uiTextSnippet('expandall'); ?></a>
      <a href='#' onClick="toggleAll('off');"><?php echo uiTextSnippet('collapseall'); ?></a>
    </div>
    <form name='form1' action='familiesAddFormAction.php' method='post' onSubmit="return validateFamily(this);">
      <input name='lastperson' type='hidden' value="<?php echo $child; ?>">
      <table class='table table-sm'>
        <tr>
          <td>
            <table class='table table-sm'>
              <tr>
                <td colspan='2'>
                  <span><strong><?php echo uiTextSnippet('prefixfamilyid'); ?></strong></span></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('tree'); ?>:</td>
                <td>
                  <select id='gedcom' name='tree1'>
                    <?php
                    $firsttree = $assignedtree;
                    while ($row = tng_fetch_assoc($result)) {
                      if (!$firsttree) {
                        $firsttree = $row['gedcom'];
                      }
                      echo "  <option value=\"{$row['gedcom']}\"";
                      if ($tree == $row['gedcom']) {
                        echo " selected";
                      }
                      echo ">{$row['treename']}</option>\n";
                    }
                    ?>
                  </select>
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('branch'); ?>:</td>
                <td style="height:2em">
                  <?php
                  $query = "SELECT branch, description FROM $branches_table WHERE gedcom = \"$firsttree\" ORDER BY description";
                  $branchresult = tng_query($query);
                  $numbranches = tng_num_rows($branchresult);
                  $branchlist = explode(",", $row['branch']);

                  $descriptions = array();
                  $assdesc = "";
                  $options = "";
                  while ($branchrow = tng_fetch_assoc($branchresult)) {
                    $options .= "  <option value=\"{$branchrow['branch']}\">{$branchrow['description']}</option>\n";
                    if ($branchrow['branch'] == $assignedbranch) {
                      $assdesc = $branchrow['description'];
                    }
                  }
                  echo "<span id='branchlist'></span>";
                  if (!$assignedbranch) {
                    if ($numbranches > 8) {
                      $select = uiTextSnippet('scrollbranch') . "<br>";
                    }
                    $select .= "<select id='branch' name=\"branch[]\" multiple size=\"8\">\n";
                    $select .= "  <option value=''";
                    if ($row['branch'] == "") {
                      $select .= " selected";
                    }
                    $select .= ">" . uiTextSnippet('nobranch') . "</option>\n";

                    $select .= "$options</select>\n";
                    echo " &nbsp;<span>(<a href='#' onclick=\"showBranchEdit('branchedit'); quitBranchEdit('branchedit'); return false;\"><img src='img/ArrowDown.gif'>" . uiTextSnippet('edit') . "</a> )</span><br>";
                    ?>
                    <div id='branchedit' style='position: absolute; display: none;'
                         onmouseover="clearTimeout(branchtimer);"
                         onmouseout="closeBranchEdit('branch', 'branchedit', 'branchlist');">
                      <?php echo $select; ?>
                    </div>
                  <?php 
                  } else {
                    echo "<input name='branch' type='hidden' value=\"$assignedbranch\">$assdesc ($assignedbranch)";
                  }
                  ?>
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('familyid'); ?>:</td>
                <td>
                  <input name='familyID' type='text' value="<?php echo $newID; ?>" size='10'
                         onBlur="this.value = this.value.toUpperCase()">
                  <input type='button' value="<?php echo uiTextSnippet('generate'); ?>"
                         onClick="generateID('family', document.form1.familyID, document.form1.tree1);">
                  <input name='lock' type='button' value="<?php echo uiTextSnippet('lockid'); ?>" onClick="document.form1.newfamily[0].checked = true;
                      if (gatherChildren()) {
                        document.form1.submit();
                      }">
                  <input type='button' value="<?php echo uiTextSnippet('check'); ?>"
                         onClick="checkID(document.form1.familyID.value, 'family', 'checkmsg', document.form1.tree1);">
                  <span id="checkmsg"></span>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
            <?php echo displayToggle("plus0", 1, "spouses", uiTextSnippet('spouses'), ""); ?>

            <div id='spouses'>
              <table class='table table-sm'>
                <tr>
                  <td><?php echo uiTextSnippet('husband'); ?>:</td>
                  <td>
                    <input id='husbnameplusid' name='husbnameplusid' type='text' size='40' value="<?php echo "$husbstr"; ?>" readonly>
                    <input id='husband' name='husband' type='hidden' value="<?php echo $husband; ?>">
                    <input type='button' value="<?php echo uiTextSnippet('find'); ?>"
                           onclick="return findItem('I', 'husband', 'husbnameplusid', document.form1.tree1.options[document.form1.tree1.selectedIndex].value, '<?php echo $assignedbranch; ?>');">
                    <input type='button' value="<?php echo uiTextSnippet('create'); ?>"
                           onclick="return openCreatePersonForm('husband', 'husbnameplusid', 'spouse', 'M');">
                    <input type='button' value="  <?php echo uiTextSnippet('edit'); ?>  "
                           onclick="EditSpouse(document.form1.husband);">
                    <input type='button' value="<?php echo uiTextSnippet('remove'); ?>"
                           onclick="RemoveSpouse(document.form1.husband, document.form1.husbnameplusid);">
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('wife'); ?>:</td>
                  <td><input id='wifenameplusid' name='wifenameplusid' type='text' size='40' value="<?php echo "$wifestr"; ?>" readonly>
                    <input id='wife' name='wife' type='hidden' value="<?php echo $wife; ?>">
                    <input type='button' value="<?php echo uiTextSnippet('find'); ?>"
                           onclick="return findItem('I', 'wife', 'wifenameplusid', document.form1.tree1.options[document.form1.tree1.selectedIndex].value, '<?php echo $assignedbranch; ?>');">
                    <input type='button' value="<?php echo uiTextSnippet('create'); ?>"
                           onclick="return openCreatePersonForm('wife', 'wifenameplusid', 'spouse', 'F');">
                    <input type='button' value="  <?php echo uiTextSnippet('edit'); ?>  "
                           onclick="EditSpouse(document.form1.wife);">
                    <input type='button' value="<?php echo uiTextSnippet('remove'); ?>"
                           onclick="RemoveSpouse(document.form1.wife, document.form1.wifenameplusid);">
                  </td>
                </tr>
              </table>

              <table class='table table-sm'>
                <tr>
                  <td>
                    <input name='living' type='checkbox' value='1' checked> <?php echo uiTextSnippet('living'); ?>
                    <input name='private' type='checkbox' value='1'> <?php echo uiTextSnippet('private'); ?>
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
              <p><?php echo uiTextSnippet('datenote'); ?></p>
              <table>
                <tr>
                  <td>&nbsp;</td>
                  <td><?php echo uiTextSnippet('date'); ?></td>
                  <td><?php echo uiTextSnippet('place'); ?></td>
                  <td colspan='4'>&nbsp;</td>
                </tr>
                <?php
                echo showEventRow('marrdate', 'marrplace', 'MARR', '');
                ?>
                <tr>
                  <td><?php echo uiTextSnippet('marriagetype'); ?>:</td>
                  <td colspan='6'>
                    <input name='marrtype' type='text' value='' maxlength='50'
                           style='width: 494px' />
                  </td>
                </tr>
                <?php
                if (determineLDSRights()) {
                  echo showEventRow('sealdate', 'sealplace', 'SLGS', '');
                }
                echo showEventRow('divdate', 'divplace', 'DIV', '');
                ?>
              </table>

            </div>
          </td>
        </tr>
        <tr>
          <td>
            <p><strong><?php echo uiTextSnippet('fevslater'); ?></strong></p>
            <input name='cw' type='hidden' value="<?php echo "$cw"; ?>">
            <input name='save' type='submit' value="<?php echo uiTextSnippet('savecont'); ?>">
          </td>
        </tr>
      </table>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<?php require_once 'eventlib.php'; ?>
<script>
  var tnglitbox;
  var preferEuro = <?php echo($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : "false"); ?>;
  var preferDateFormat = '<?php echo $preferDateFormat; ?>';

  var tree = '<?php echo $tree; ?>';
</script>
<script src="js/selectutils.js"></script>
<script src="js/datevalidation.js"></script>
<script>
  var persfamID = "";
  var allow_cites = false;
  var allow_notes = false;

  $(document).ready(function() {
      generateID('family', document.form1.familyID, document.form1.tree1);
  });
  
  function toggleAll(display) {
      toggleSection('spouses', 'plus0', display);
      toggleSection('events', 'plus1', display);
      return false;
  }

  <?php
  if (!$assignedtree && !$assignedbranch) {
    include 'branchlibjs.php';
  } else {
    $swapbranches = "";
  }
  ?>

  $('#gedcom').on('change', function () {
     <?php echo $swapbranches; ?>
     generateID('family', document.form1.familyID, document.form1.tree1);
  });

  function validateFamily(form) {
    var rval = true;

    form.familyID.value = TrimString(form.familyID.value);
    if (form.familyID.value.length === 0) {
      alert(textSnippet('enterfamilyid'));
      return false;
    }
    return true;
  }

  function EditSpouse(field) {
    var tree = getTree(document.form1.tree1);
    if (field.value.length)
      deepOpen('peopleEdit.php?personID=' + field.value + '&tree=' + tree + '&cw=1', 'editspouse');
  }

  function RemoveSpouse(spouse, spousedisplay) {
    spouse.value = "";
    spousedisplay.value = textSnippet('clickfind');
  }

  var nplitbox;
  var activeidbox = null;
  var activenamebox = null;
  function openCreatePersonForm(idfield, namefield, type, gender) {
    activeidbox = idfield;
    activenamebox = namefield;
    var url = 'admin_newperson2.php?tree=' + document.form1.tree1.options[document.form1.tree1.selectedIndex].value + '&type=' + type + '&familyID=' + persfamID + '&gender=' + gender; 
    tnglitbox = new ModalDialog(url);
//      generateID('person', document.npform.personID, document.form1.tree1);
    $('#firstname').focus();
    return false;
  }

  function saveNewPerson(form) {
    form.personID.value = TrimString(form.personID.value);
    var personID = form.personID.value;
    if (personID.length === 0) {
      alert(textSnippet('enterpersonid'));
    } else {
      var params = $(form).serialize();
      $.ajax({
        url: 'admin_addperson2.php',
        data: params,
        dataType: 'json',
        type: 'post',
        success: function (vars) {
          if (vars.error) {
            $('#errormsg').html(vars.error);
            $('#errormsg').show();
          } else {
            nplitbox.remove();
            $('#' + activenamebox).val(vars.name + ' - ' + vars.id);
            $('#' + activenamebox).effect('highlight', {}, 400);
            $('#' + activeidbox).val(vars.id);
          }
        }
      });
    }
    return false;
  }
</script>
<script src="js/admin.js"></script>
</body>
</html>
