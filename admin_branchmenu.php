<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowEdit || $assignedbranch) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

$query = "SELECT treename FROM $treesTable WHERE gedcom = \"$tree\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

$query = "SELECT description FROM $branches_table WHERE gedcom = \"$tree\" and branch = \"$branch\"";
$result = tng_query($query);
$brow = tng_fetch_assoc($result);
tng_free_result($result);

$query = "SELECT count(persfamID) as pcount FROM $branchlinks_table WHERE gedcom = \"$tree\" AND branch = \"$branch\"";
$result = tng_query($query);
$prow = tng_fetch_assoc($result);
$pcount = $prow['pcount'];
tng_free_result($result);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('labelbranches'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="branches-labelbranches">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('branches-labelbranches', $message);
    $navList = new navList('');
    $navList->appendItem([true, "branchesBrowse.php", uiTextSnippet('browse'), "findbranch"]);
    $navList->appendItem([$allowAdd, "branchesAdd.php", uiTextSnippet('add'), "addbranch"]);
    $navList->appendItem([$allowEdit, "#", uiTextSnippet('labelbranches'), "label"]);
    echo $navList->build("label");
    ?>
    <form action="admin_branchlabels.php" method='post' id="form1" name="form1"
          onSubmit="return validateForm();">
      <table class='table table-sm'>
        <tr>
          <td><strong><?php echo uiTextSnippet('tree'); ?>:</strong></td>
          <td><?php echo $row['treename']; ?>
            <input name='tree' type='hidden' value="<?php echo $tree; ?>">
          </td>
        </tr>
        <tr>
          <td><strong><?php echo uiTextSnippet('branch'); ?>:</strong></td>
          <td><?php echo $brow['description'] . "<br>(" . uiTextSnippet('people') . " + " . uiTextSnippet('families') . " = $pcount*)"; ?>
            <input name='branch' type='hidden' value="<?php echo $branch; ?>"></td>
        </tr>
        <tr>
          <td colspan='2'><br><strong><?php echo uiTextSnippet('action'); ?>:</strong></td>
        </tr>
        <tr>
          <td colspan='2'>
            <input name='branchaction' type='radio' value='add' checked
                   onClick="toggleAdd();"> <?php echo uiTextSnippet('addlabels'); ?>
            <input name='branchaction' type='radio' value='clear'
                   onClick="toggleClear(0);"> <?php echo uiTextSnippet('clearlabels'); ?>
            <input name='branchaction' type='radio' value='delete'
                   onClick="toggleClear(1);"> <?php echo uiTextSnippet('delpeople'); ?>
          </td>
        </tr>
        <tr>
          <td colspan='2'>
            <div id="allpart" style="display: none">
              <input name='set' type='radio' value='all'
                     onClick="toggleAll();"> <?php echo uiTextSnippet('all'); ?>
              <input name='set' type='radio' value='partial' checked
                     onClick="togglePartial();"> <?php echo uiTextSnippet('partial'); ?>
            </div>
          </td>
        </tr>
        <tr>
          <td colspan='2'>
            <div id="startind1"><br><strong><?php echo uiTextSnippet('startingind'); ?>
                :</strong></div>
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
                  <input id='personID' name='personID' type='text'>
                  &nbsp;<?php echo uiTextSnippet('or'); ?>&nbsp;
                </td>
                <td>
                  <a href="#" onclick="return findItem('I', 'personID', '', '<?php echo $tree; ?>', '<?php echo $assignedbranch; ?>');" title="<?php echo uiTextSnippet('find'); ?>">
                    <img class='icon-sm' src='svg/magnifying-glass.svg'>
                  </a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan='2'>
            <div id='numgens1'>
              <br>
              <strong><?php echo uiTextSnippet('numgenerations'); ?>:</strong>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <div id='numgens2'>&nbsp;&nbsp;<?php echo uiTextSnippet('ancestors'); ?>:</div>
          </td>
          <td>
            <div id='numgens3'>
              <input name='agens' type='text' size="3" maxlength="3" value='0'>
              <?php echo uiTextSnippet('descofanc'); ?>:
              <select name='dagens'>
                <option value='0'>0</option>
                <option value='1' selected>1</option>
                <option value='2'>2</option>
                <option value='3'>3</option>
                <option value='4'>4</option>
                <option value='5'>5</option>
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
              <input name='dgens' type='text' size='3' maxlength='3' value='0'>
              <input name='dospouses' type='checkbox' checked> <?php echo uiTextSnippet('inclspouses'); ?>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <div id='overwrite1'><br><strong><?php echo uiTextSnippet('existlabels'); ?>
                :</strong></div>
          </td>
          <td>
            <div id='overwrite2'><br>
              <select name='overwrite'>
                <option value='2' selected><?php echo uiTextSnippet('append'); ?></option>
                <option value='1'><?php echo uiTextSnippet('overwrite'); ?></option>
                <option value='0'><?php echo uiTextSnippet('leave'); ?></option>
              </select>
            </div>
          </td>
        </tr>
      </table>
      <br>
      <input id='labelsub' type='submit' value="<?php echo uiTextSnippet('addlabels'); ?>"> 
      <input type='button' value="<?php echo uiTextSnippet('showpeople'); ?>"
              onclick="window.location.href = 'admin_showbranch.php?tree=<?php echo $tree; ?>&branch=<?php echo $branch; ?>';">
    </form>
    <p>*<?php echo uiTextSnippet('branchdiscl'); ?></p>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src='js/selectutils.js'></script>
  <script>
    var tree = "<?php echo $tree; ?>";
    
    function toggleClear(option) {
      $('#overwrite1').fadeOut(300);
      $('#overwrite2').fadeOut(300);
      $('#allpart').fadeIn(300);
      textSnippetInto('#labelsub', option ? 'delete' : 'clearlabels');
    }

    function toggleAdd() {
      $('#overwrite1').fadeIn(300);
      $('#overwrite2').fadeIn(300);
      $('#allpart').fadeOut(300);
      document.form1.set[1].checked = true;
      textSnippetInto('#labelsub', 'addlabels');
      togglePartial();
    }

    function confirmDelete() {
      return confirm(textSnippet('confbrdel')) ? validateForm() : false;
    }

    function toggleAll() {
      $('#startind1').fadeOut(300);
      $('#startind2').fadeOut(300);
      $('#startind3').fadeOut(300);
      $('#numgens1').fadeOut(300);
      $('#numgens2').fadeOut(300);
      $('#numgens3').fadeOut(300);
      $('#numgens4').fadeOut(300);
      $('#numgens5').fadeOut(300);
    }

    function togglePartial() {
      $('#startind1').fadeIn(300);
      $('#startind2').fadeIn(300);
      $('#startind3').fadeIn(300);
      $('#numgens1').fadeIn(300);
      $('#numgens2').fadeIn(300);
      $('#numgens3').fadeIn(300);
      $('#numgens4').fadeIn(300);
      $('#numgens5').fadeIn(300);
    }

    function validateForm() {
      var rval = true;
      var option = true;

      if ($('#labelsub').val() === textSnippet('delete')) {
        option = confirm(textSnippet('confbrdel'));
      }
      if (option) {
        if (document.form1.set[1].checked) {
          if (document.form1.personID.value.length === 0) {
            alert(textSnippet('enterstartingind'));
            rval = false;
          } else if (isNaN(document.form1.agens.value) || isNaN(document.form1.dgens.value)) {
            alert(textSnippet('gensnumeric'));
            rval = false;
          }
        }
        return rval;
      } else
        return false;
    }
  </script>
</body>
</html>