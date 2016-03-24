<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
include("version.php");

if (!$allowEdit || ($assignedtree && $assignedtree != $tree)) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

$query = "SELECT * FROM $branches_table WHERE gedcom = \"$tree\" AND branch = \"$branch\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
$row['description'] = preg_replace('/\"/', '&#34;', $row['description']);
tng_free_result($result);

$query = "SELECT treename FROM $trees_table where gedcom = \"$tree\"";
$treeresult = tng_query($query);
$treerow = tng_fetch_assoc($treeresult);
tng_free_result($treeresult);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('modifytree'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="branches-modifybranch">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('branches-modifybranch', $message);
    $navList = new navList('');
    $navList->appendItem([true, "branchesBrowse.php", uiTextSnippet('browse'), "findbranch"]);
    $navList->appendItem([$allowAdd, "branchesAdd.php", uiTextSnippet('add'), "addbranch"]);
//    $navList->appendItem([$allowEdit, "#", uiTextSnippet('edit'), "edit"]);
    echo $navList->build("edit");
    ?>
    <form id='form1' name='form1' action='branchesEditFormAction.php' method='post' onSubmit="return validateForm();">
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('tree'); ?>:</td>
          <td><?php echo $treerow['treename']; ?></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('branchid'); ?>:</td>
          <td><?php echo $branch; ?></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('description'); ?>:</td>
          <td><input name='description' type='text' value="<?php echo $row['description']; ?>"></td>
        </tr>

        <tr>
          <td colspan='2'>
            <div id="startind1"><br><strong><?php echo uiTextSnippet('startingind'); ?>:</strong></div>
          </td>
        </tr>
        <tr>
          <td>
            <div id="startind2">&nbsp;&nbsp;<?php echo uiTextSnippet('personid'); ?>:</div>
          </td>
          <td>
            <table id="startind3">
              <tr>
                <td>
                  <input id='personID' name='personID' type='text' value="<?php echo $row['personID']; ?>" size='10'>
                  &nbsp;<?php echo uiTextSnippet('or'); ?>&nbsp;
                </td>
                <td>
                  <a href="#" title="<?php echo uiTextSnippet('find'); ?>" onclick="return findItem('I', 'personID', '', '<?php echo $tree; ?>', '<?php echo $assignedbranch; ?>');">
                    <img class='icon-sm' src='svg/magnifying-glass.svg'>
                  </a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan='2'>
            <div id="numgens1"><br><strong><?php echo uiTextSnippet('numgenerations'); ?>
                :</strong></div>
          </td>
        </tr>
        <tr>
          <td>
            <div id="numgens2">&nbsp;&nbsp;<?php echo uiTextSnippet('ancestors'); ?>:</div>
          </td>
          <td>
            <div id="numgens3">
              <input name='agens' type='text' size='3' maxlength='3' value="<?php echo $row['agens'] ? $row['agens'] : 0; ?>"/>
              &nbsp;&nbsp; <?php echo uiTextSnippet('descofanc'); ?>:
              <select name="dagens" id="dagens">
                <?php
                $dagens = $row['dagens'] != "" ? $row['dagens'] : 1;
                for ($i = 0; $i < 6; $i++) {
                  echo "<option value=\"$i\"";
                  if ($i == $dagens) {
                    echo " selected";
                  }
                  echo ">$i</option>";
                }
                ?>
              </select>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <div id='numgens4'>&nbsp;&nbsp;<?php echo uiTextSnippet('descendants'); ?>:</div>
          </td>
          <td>
            <div id='numgens5'>
              <input name='dgens' type='text' size='3' maxlength='3' value="<?php echo $row['dgens'] ? $row['dgens'] : 0; ?>"/>
              &nbsp;&nbsp;
              <input id='dospouses' name='dospouses' type='checkbox'<?php if ($row['inclspouses']) {echo " checked";} ?> value='1'/> <?php echo uiTextSnippet('inclspouses'); ?>
            </div>
          </td>
        </tr>
      </table>
      <span>
        <br></span>
      <input name='tree' type='hidden' value="<?php echo $tree; ?>">
      <input name='branch' type='hidden' value="<?php echo $branch; ?>">

      <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
      <input name='submitx' type='submit' value="<?php echo uiTextSnippet('saveexit'); ?>">
      <input type='button' value="<?php echo uiTextSnippet('addlabels'); ?>"
             onclick="return startLabels(document.form1);">
      <input type='button' value="<?php echo uiTextSnippet('showpeople'); ?>"
             onclick="return showBranchPeople(document.form1.tree.value, document.form1.branch.value, document.form1.description.value);">
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src='js/selectutils.js'></script>
<script>
  var tree = '<?php echo $tree; ?>';
  var branch = '<?php echo $branch; ?>';

function validateForm() {
    'use strict';
    var rval = true;
    if (form1.description.value.length === 0) {
        textSnippetAlert(enterbranchdesc);
        rval = false;
    }
    return rval;
}

function startLabels(form) {
    'use strict';
    var args = '&personID=' + form.personID.value + '&agens=' + form.agens.value + '&dagens=' + form.dagens.value + '&dgens=' + form.dgens.value + '&dospouses=' + form.dospouses.value;
    var url = 'ajx_branchmenu.php?branch=' + form.branch.value + '&tree=' + form.tree.value + args;

    if (form.personID.value.length === 0) {
        textSnippetAlert('enterstartingind');
    } else if (isNaN(form.agens.value) || isNaN(form.dgens.value)) {
        textSnippetAlert('gensnumeric');
    } else {
        tnglitbox = new ModalDialog(url);
    }
    return false;
}

function showBranchPeople(tree, branch, description) {
    'use strict';
    var url = 'ajx_showbranch.php?branch=' + branch + '&description=' + encodeURIComponent(description) + '&tree=' + tree;
    tnglitbox = new ModalDialog(url);
    return false;
}

function addLabels() {
    'use strict';
    var form1 = document.form1;

    $('#branchresults').html('');
    $('#labelspinner').show();
    var params = {
        branchaction: $("input:radio[name ='branchaction']:checked").val(),
        set: $("input:radio[name ='set']:checked").val(),
        overwrite: $('#overwrite').val(),
        personID: form1.personID.value,
        agens: form1.agens.value,
        dagens: $('#dagens').val(),
        dgens: form1.dgens.value,
        dospouses: $('#dospouses').attr('checked') ? true : "",
        tree: tree,
        branch: branch
    };
    $.ajax({
        url: 'ajx_labels.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            $('#labelspinner').hide();
            $('#branchresults').html(req);
        }
    });
    return false;
}

function toggleClear(option) {
    'use strict';
    
    $('#overwrite1').fadeOut(300);
    $('#allpart').fadeIn(300);
    textSnippetInto('#labelsub', option ? 'delete' : 'clearlabels');
}

function toggleAdd() {
    'use strict';
    $('#overwrite1').fadeIn(300);
    $('#allpart').fadeOut(300);
    document.form2.set[1].checked = true;
    textSnippetInto('#labelsub', 'addlabels');
}
</script>
</body>
</html>
