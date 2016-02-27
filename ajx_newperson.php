<?php
include("begin.php");
include("adminlib.php");

include("checklogin.php");

if (!$allow_add) {
  $message = uiTextSnippet('norights');
  header("Location: login.php?message=" . urlencode($message));
  exit;
}
$query = "SELECT treename FROM $trees_table WHERE gedcom=\"$tree\" ORDER BY treename";
$result = tng_query($query);
$treerow = tng_fetch_assoc($result);
tng_free_result($result);

if ($father) {
  $query = "SELECT lnprefix, lastname, branch FROM $people_table WHERE gedcom=\"$tree\" AND personID=\"$father\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  tng_free_result($result);
} else {
  $row['lastname'] = $row['lnprefix'] = "";
}
header("Content-type:text/html; charset=" . $session_charset);

include_once("eventlib.php");
?>
<form id='persform1' name='persform1' action='' method='post' onSubmit="return validatePerson(this);">
  <header class='modal-header'>
    <h4><?php echo uiTextSnippet('addnewperson'); ?></h4>
  </header>
  <div class='modal-body'>
    <span><strong><?php echo uiTextSnippet('prefixpersonid'); ?></strong></span>
    <br>
    <?php echo uiTextSnippet('personid'); ?>:
    <div class='row'>
      <div class='col-md-6'>
        <div class='input-group'>
          <span class='input-group-btn'>
            <button class='btn' type='button' value="<?php echo uiTextSnippet('generate'); ?>" onClick="generateIDajax('person', 'personID');"><?php echo uiTextSnippet('generate'); ?></button>
          </span>
          <input class='form-control' id='personID' name='personID' type='text' onBlur="this.value = this.value.toUpperCase()">
          <span class='input-group-btn'>
            <button class='btn' type='button' value="<?php echo uiTextSnippet('check'); ?>" onClick="checkIDajax(document.persform1.personID.value, 'person', 'pcheckmsg');"><?php echo uiTextSnippet('check'); ?></button>
          </span>
        </div>
      </div>
      <div class='col-md-offset-3 col-md-3'>
        <label>
          <input name='living' type='checkbox' value='1' checked>
            <?php echo uiTextSnippet('living'); ?>&nbsp;
        </label>
        <label>
          <input name='private' type='checkbox' value='1'>
            <?php echo uiTextSnippet('private'); ?>
        </label>
      </div>
      <span id="pcheckmsg"></span>
    </div>
    <div class='row'>
      <div class='col-md-6'>
        <?php echo uiTextSnippet('tree') . ": " . $treerow['treename']; ?>
      </div>
      <div class='col-md-6'>
        <?php require_once 'branches.php'; ?>
        <br>
        <?php echo buildBranchSelectControl($row, $tree, $assignedbranch, $branches_table); ?>
      </div>
    </div>
    <div id='person-names'>
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
            <input class='form-control' name='lastname' type='text'>
          </div>
        <?php } else { ?>
          <div class='col-md-6'>
            <?php echo uiTextSnippet('surname'); ?>
            <input class='form-control' name='lastname' type='text'>
          </div>
        <?php } ?>
        <div class='col-md-3'>
          <?php echo buildSexSelectControl($gender); ?>
        </div>
      </div>
      <br>
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
        <div class='col-md-3'>
          <?php echo uiTextSnippet('nameorder'); ?>
          <select class='form-control' name="pnameorder">
            <option value='0'><?php echo uiTextSnippet('default'); ?></option>
            <option value='1'><?php echo uiTextSnippet('western'); ?></option>
            <option value="2"><?php echo uiTextSnippet('oriental'); ?></option>
            <option value="3"><?php echo uiTextSnippet('lnfirst'); ?></option>
          </select>
        </div>
      </div>
    </div>

    <div id='person-events'>
      <p class='smallest'><?php echo uiTextSnippet('datenote'); ?></p>
      <?php
      echo buildEventRow('birthdate', 'birthplace', 'BIRT', '');
      if (!$tngconfig['hidechr']) {
        echo buildEventRow('altbirthdate', 'altbirthplace', 'CHR', '');
      }
      echo buildEventRow('deathdate', 'deathplace', 'DEAT', '');
      echo buildEventRow('burialdate', 'burialplace', 'BURI', '');
      if ($allow_lds) {
        echo "<br>";
        echo buildEventRow('baptdate', 'baptplace', 'BAPL', '');
        echo buildEventRow('confdate', 'confplace', 'CONL', '');
        echo buildEventRow('initdate', 'initplace', 'INIT', '');
        echo buildEventRow('endldate', 'endlplace', 'ENDL', '');
      }
      ?>
    </div>
  </div> <!-- .modal-body -->
  <footer class='modal-footer'>
    <input name='newperson' type='hidden' value='ajax'>
    <input name='tree1' type='hidden' value="<?php echo $tree; ?>">
    <input name='familyID' type='hidden' value="<?php echo $familyID; ?>">
    <input name='type' type='hidden' value="<?php echo $type; ?>">
    <?php if (!$lnprefixes) {echo "<input name='lnprefix' type='hidden' value=''>";} ?>
    <input name='submit2' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
  </footer>
</form>
