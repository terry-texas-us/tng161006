<?php
include("begin.php");
include("adminlib.php");

$admin_login = true;
include("checklogin.php");
include("version.php");

if (!$allow_add) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

require_once 'branches.php';

if ($assignedtree) {
  $wherestr = "WHERE gedcom = \"$assignedtree\"";
} else {
  $wherestr = "";
}
$query = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";
$result = tng_query($query);

$revstar = checkReview('I');

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('addnewperson'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='newperson'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('people-addnewperson', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_people.php", uiTextSnippet('search'), "findperson"]);
    $navList->appendItem([$allow_add, "admin_newperson.php", uiTextSnippet('addnew'), "addperson"]);
    $navList->appendItem([$allow_edit, "admin_findreview.php?type=I", uiTextSnippet('review') . $revstar, "review"]);
    $navList->appendItem([$allow_edit && $allow_delete, "admin_merge.php", uiTextSnippet('merge'), "merge"]);
    echo $navList->build("addperson");
    ?>
    <form name="form1" action="admin_addperson.php" method='post' onSubmit="return trimCheckPersonRequired();">
      <header id='person-header'>
        <?php echo uiTextSnippet('personid'); ?>:
        <div class='row'>
          <div class='col-sm-6'>
            <div class='input-group'>
              <span class='input-group-btn'>
                <button class='btn btn-secondary' id='generate' type='button'><?php echo uiTextSnippet('generate'); ?></button>
              </span>
              <input class='form-control' id='person-id' name='personID' type='text' data-check-result=''>
              <span class='input-group-btn'>
                <button class='btn btn-secondary' id='check' type='button'><?php echo uiTextSnippet('check'); ?></button>
              </span>
            </div>
          </div>
          <div class='col-sm-2'>
            <button class='btn btn-primary-outline' id='lockid' name='submit' type='submit'><?php echo uiTextSnippet('lockid'); ?></button>
          </div>
          <div class='col-sm-4'>
            <label class='checkbox-inline'>
              <input name='living' type='checkbox' value='1' checked>
              <?php echo uiTextSnippet('living'); ?>
            </label>
            <label class='checkbox-inline'>
              <input name='private' type='checkbox' value='1'>
              <?php echo uiTextSnippet('private'); ?>
            </label>
          </div>
        </div>
        <div class='row'>
          <div class='col-md-6'>
            <?php echo uiTextSnippet('tree'); ?>:
            <select class='form-control' id='gedcom' name='tree1'>
              <?php
              $firsttree = $assignedtree;
              while ($row = tng_fetch_assoc($result)) {
                if (!$firsttree) {
                  $firsttree = $row['gedcom'];
                }
                echo "  <option value='{$row['gedcom']}'>{$row['treename']}</option>\n";
              }
              ?>
            </select>
          </div>
          <div class='col-md-6'>
            <br>
            <?php echo buildBranchSelectControl($row, $firsttree, $assignedbranch, $branches_table); ?>
          </div>
        </div>
      </header>
      <div id='person-names'>
        <div class='row'>
          <div class='col-md-3'>
            <label><?php echo uiTextSnippet('givennames'); ?></label>
            <input class='form-control' name='firstname' type='text'>
          </div>
          <?php if ($lnprefixes) { ?>
            <div class='col-md-2'>
              <label><?php echo uiTextSnippet('lnprefix'); ?></label>
              <input class='form-control' name='lnprefix' type='text'>
            </div>
            <div class='col-md-3'>
              <label><?php echo uiTextSnippet('surname'); ?></label>
              <input class='form-control' name='lastname' type='text'>
            </div>
          <?php } else { ?>
            <div class='col-md-5'>
              <label><?php echo uiTextSnippet('surname'); ?></label>
              <input class='form-control' name='lastname' type='text'>
            </div>
          <?php } ?>
          <div class='col-md-2'>
            <?php echo buildSexSelectControl('unknown'); ?>
          </div>
        </div>
        <br>
        <div class='row'>
          <div class='col-md-3'>
            <label><?php echo uiTextSnippet('nickname'); ?></label>
            <input class='form-control' name='nickname' type='text'>
          </div>
          <div class='col-md-2'>
            <label><?php echo uiTextSnippet('title'); ?></label>
            <input class='form-control' name='title' type='text'>
          </div>
          <div class='col-md-2'>
            <label><?php echo uiTextSnippet('prefix'); ?></label>
            <input class='form-control' name='prefix' type='text'>
          </div>
          <div class='col-md-2'>
            <label><?php echo uiTextSnippet('suffix'); ?></label>
            <input class='form-control' name='suffix' type='text'>
          </div>
          <div class='col-md-3'>
            <label><?php echo uiTextSnippet('nameorder'); ?></label>
            <select class='form-control' name="pnameorder">
              <option value='0'><?php echo uiTextSnippet('default'); ?></option>
              <option value='1'><?php echo uiTextSnippet('western'); ?></option>
              <option value="2"><?php echo uiTextSnippet('oriental'); ?></option>
              <option value="3"><?php echo uiTextSnippet('lnfirst'); ?></option>
            </select>          </div>
        </div>
      </div>
      <div id='person-events'>
        <small class='text-muted'><?php echo uiTextSnippet('datenote'); ?></small>
        <?php
        echo buildEventRow('birthdate', 'birthplace', 'BIRT', '');
        if (!$tngconfig['hidechr']) {
          echo buildEventRow('altbirthdate', 'altbirthplace', 'CHR', '');
        }
        echo buildEventRow('deathdate', 'deathplace', 'DEAT', '');
        echo buildEventRow('burialdate', 'burialplace', 'BURI', '');
        echo "<input id='burialtype' name='burialtype' type='checkbox' value='1'> <label for='burialtype'>" . uiTextSnippet('cremated') . "</label>\n";
        if ($allow_lds) {
          echo buildEventRow('baptdate', 'baptplace', 'BAPL', '');
          echo buildEventRow('confdate', 'confplace', 'CONL', '');
          echo buildEventRow('initdate', 'initplace', 'INIT', '');
          echo buildEventRow('endldate', 'endlplace', 'ENDL', '');
        }
        ?>
      </div>
      <footer id='person-footer'>
        <p class='text-muted'><?php echo uiTextSnippet('pevslater'); ?></p>
        <button class='btn btn-primary-outline' name='save' type='submit'><?php echo uiTextSnippet('savecont'); ?></button>
        <input name='cw' type='hidden' value="<?php echo "$cw"; ?>">
        <?php if (!$lnprefixes) { ?>
          <input name='lnprefix' type='hidden' value=''>
        <?php } ?>
      </footer>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<?php include_once("eventlib.php"); ?>
<script src="js/admin.js"></script>
<script src="js/selectutils.js"></script>
<script src="js/datevalidation.js"></script>
<script src='js/branches.js'></script>
<script src='js/people.js'></script>
<script>
var tnglitbox;
var preferEuro = <?php echo($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : "false"); ?>;
var preferDateFormat = '<?php echo $preferDateFormat; ?>';

var allow_cites = false;
var allow_notes = false;

var persfamID = "";

var tree = '<?php echo $tree; ?>';

$(document).ready(function() {
    generateID('person', document.form1.personID, document.form1.tree1);
    $('#person-id').addClass('form-control-success').css({'z-index' : '10'}).parent().addClass('has-success');
});

$('#generate').on('click', function () {
    generateID('person', document.form1.personID, document.form1.tree1);
    $('#person-id').removeClass('form-control-warning').addClass('form-control-success')
        .parent().removeClass('has-warning').addClass('has-success');
});

$('#person-id').on('blur', function () {
    this.value = (isNaN(Number(this.value))) ? this.value.toUpperCase() : 'I' + this.value;
    checkPersonId(document.form1.personID, document.form1.tree1);
});

$('#check').on('click', function () {
    checkPersonId(document.form1.personID, document.form1.tree1);
});

$('#lockid').on('click', function () {
    document.form1.newfamily['2'].checked = true;
});

function trimCheckPersonRequired() {
    var rval = true;

    document.form1.personID.value = TrimString(document.form1.personID.value);
    if (document.form1.personID.value.length === 0) {
        alert(textSnippet('enterpersonid'));
        rval = false;
    }
    document.form1.firstname.value = (document.form1.firstname.value).trim();
    document.form1.lastname.value = (document.form1.lastname.value).trim();
    
    if (document.form1.firstname.value.length === 0 && document.form1.lastname.value.length === 0) {
        alert(textSnippet('entergivennameorsurname'));
        rval = false;
    }
    return rval;
}

<?php if (!$assignedtree && !$assignedbranch) { ?>
    
//----
  var branchids = [];
  branchids.none = [''];
  var branchnames = [];
  branchnames.none = [textSnippet('allbranches')];

  <?php
  $swapbranches = "swapBranches(branchids, branchnames, document.form1.branch);\n";
  
  $dispid = "";
  $dispname = "";

  getBranchInfo($assignedtree, $trees_table, $branches_table, $dispid, $dispname);
  
  echo $dispid;
  echo $dispname;
  ?>
  //----    
    
  <?php  
} else {
    $query = "SELECT description FROM $branches_table WHERE gedcom = \"$assignedtree\" AND branch = \"$assignedbranch\" ORDER BY description";
    $branchresult = tng_query($query);
    $branch = tng_fetch_assoc($branchresult);
    $dispname = $branch['description'];
    $swapbranches = "";
}
?>
$('#gedcom').on('change', function () {
    <?php echo $swapbranches; ?>
    generateID('person', document.form1.personID, document.form1.tree1);
});

</script>
</body>
</html>
