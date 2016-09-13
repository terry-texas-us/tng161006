<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowAdd) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
if ($father) {
  $query = "SELECT lnprefix, lastname, branch FROM $people_table WHERE personID = '$father'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  tng_free_result($result);
} else {
  $row['lastname'] = $row['lnprefix'] = '';
}

function relateSelect($label) {
  $fieldname = $label == 'father' ? 'frel' : 'mrel';
  $pout = "<select name=\"$fieldname\">\n";
  $pout .= "<option value=''></option>\n";

  $reltypes = ['adopted', 'birth', 'foster', 'sealing', 'step'];
  foreach ($reltypes as $reltype) {
    $pout .= "<option value=\"$reltype\"";
    if ($parent[$fieldname] == $reltype || $parent[$fieldname] == uiTextSnippet($reltype)) {
      $pout .= ' selected';
    }
    $pout .= '>' . uiTextSnippet($reltype) . "</option>\n";
  }
  $pout .= "</select>\n";

  return $pout;
}
header('Content-type:text/html; charset=' . $session_charset);
?>
<div id='newperson'>
  <form name='npform' method='post' <?php if ($needped) {echo "action='admin_addperson2.php'";} else {echo "action='' onsubmit='return saveNewPerson(this);'";} ?>>
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('addnewperson'); ?></h4>
    </header>
    <div class='modal-body'>
      <strong><?php echo uiTextSnippet('prefixpersonid'); ?></strong>
      <div class='row'>
        <div class='col-md-6'>
          <div class='input-group'>
            <span class='input-group-btn'>
              <button class='btn' type='button' value="<?php echo uiTextSnippet('generate'); ?>" onclick="generateID('person', document.npform.personID);"><?php echo uiTextSnippet('generate'); ?></button>
            </span>
            <input class='form-control' id='personID' name='personID' type='text' onBlur="this.value = this.value.toUpperCase()">
            <span class='input-group-btn'>
              <button class='btn' type='button' value="<?php echo uiTextSnippet('check'); ?>" onclick="checkID(document.npform.personID.value, 'person', 'checkmsg2');"><?php echo uiTextSnippet('check'); ?></button>
            </span>
          </div>
        </div>
        <div class='offset-md-3 col-md-3'>
          <label>
            <input name='living' type='checkbox' value='1' checked>
              <?php echo uiTextSnippet('living'); ?>&nbsp;
          </label>
          <label>
            <input name='private' type='checkbox' value='1'>
              <?php echo uiTextSnippet('private'); ?>
          </label>
        </div>
        <span id="checkmsg2"></span>
      </div>
      <div class='row'>
        <div class='col-md-6'>
        </div>
        <div class='col-md-6'>
          <?php require_once 'branches.php'; ?>
          <?php echo buildBranchSelectControl_admin_newperson2($row, $assignedbranch, $branches_table); ?>
        </div>
      </div>
      <div class='row'>
        <div class='col-md-3'>
          <?php echo uiTextSnippet('givennames'); ?>
          <input class='form-control' id='firstname' name='firstname' type='text'>
        </div>
        <?php if ($lnprefixes) { ?>
          <div class='col-md-2'>  
            <?php echo uiTextSnippet('lnprefix'); ?>
            <input class='form-control' name='lnprefix' type='text' value="<?php echo $row['lnprefix']; ?>">
          </div>
          <div class='col-md-4'>
            <?php echo uiTextSnippet('surname'); ?>
            <input class='form-control' name='lastname' type='text' value="<?php echo $row['lastname']; ?>">
          </div>
        <?php } else { ?>
          <div class='col-md-6'>
            <?php echo uiTextSnippet('surname'); ?>
            <input class='form-control' name='lastname' type='text' value="<?php echo $row['lastname']; ?>">
          </div>
        <?php } ?>
        <div class='col-md-2'>
          <?php echo buildSexSelectControl($gender); ?>
        </div>
      </div>     
      <div class='row'>
        <div class='col-md-3'>
          <?php echo uiTextSnippet('nickname'); ?>
          <input class='form-control' name='nickname' type='text'>
        </div>
        <div class='col-md-2'>
          <?php echo uiTextSnippet('title'); ?>
          <input class='form-control' name='title' type='text'>
        </div>
        <div class='col-md-2'>
          <?php echo uiTextSnippet('prefix'); ?>
          <input class='form-control' name='prefix' type='text'>
        </div>
        <div class='col-md-2'>
          <?php echo uiTextSnippet('suffix'); ?>
         <input class='form-control' name='suffix' type='text'>
        </div>
      </div>

      <p class='smallest'><?php echo uiTextSnippet('datenote'); ?></p>
      <?php
      $noclass = true;
      echo buildEventRow('birthdate', 'birthplace', 'BIRT', '');
      if (!$tngconfig['hidechr']) {
        echo buildEventRow('altbirthdate', 'altbirthplace', 'CHR', '');
      }
      echo buildEventRow('deathdate', 'deathplace', 'DEAT', '');
      echo buildEventRow('burialdate', 'burialplace', 'BURI', '');
      echo "<input id='burialtype' name='burialtype' type='checkbox' value='1'> <label for='burialtype'>" . uiTextSnippet('cremated') . "</label>\n";
      if (determineLDSRights()) {
        echo buildEventRow('baptdate', 'baptplace', 'BAPL', '');
        echo buildEventRow('confdate', 'confplace', 'CONL', '');
        echo buildEventRow('initdate', 'initplace', 'INIT', '');
        echo buildEventRow('endldate', 'endlplace', 'ENDL', '');
      }
      if ($type == 'child') {
        echo "<br>\n";
        echo uiTextSnippet('relationship') . ' (' . uiTextSnippet('father') . '): ' . relateSelect('father') . '&nbsp;&nbsp;';
        echo uiTextSnippet('relationship') . ' (' . uiTextSnippet('mother') . '): ' . relateSelect('mother');
      }
      ?>
      <div id='errormsg' class='red' style='display: none;'></div>
      <strong><?php echo uiTextSnippet('pevslater2'); ?></strong>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <input name='needped' type='hidden' value="<?php echo $needped; ?>">
      <input name='familyID' type='hidden' value="<?php echo $familyID; ?>">
      <?php if ($type == '') {
        $type = 'text';
      } ?>
      <input name='type' type='hidden' value="<?php echo $type; ?>">
      <?php
      if (!$lnprefixes) {
        echo "<input name='lnprefix' type='hidden' value=''>";
      }
      ?>
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    </footer>
  </form>
</div>

