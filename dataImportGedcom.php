<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';
require $subroot . 'importconfig.php';

if (!$allowAdd || !$allowEdit || $assignedbranch) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
$query = 'SELECT gedcom FROM trees';
$result = tng_query($query);

$treenum = 0;
$trees = [];
$treename = [];
while ($treerow = tng_fetch_assoc($result)) {
  $trees[$treenum] = $treerow['gedcom'];
  $treename[$treenum] = $treerow['treename'];
  $treenum++;
}
tng_free_result($result);

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('datamaint'));

$allow_export = 1;
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
<body id='datamaint-gedimport'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('datamaint-gedimport', $message);
    $navList = new navList('');
    //    $navList->appendItem([true, 'dataImportGedcom.php', uiTextSnippet('import'), 'import']);
    $navList->appendItem([$allow_export, 'dataExportGedcom.php', uiTextSnippet('export'), 'export']);
    $navList->appendItem([true, 'dataSecondaryProcesses.php', uiTextSnippet('secondarymaint'), 'second']);
    echo $navList->build('import');
    ?>
    <form name='form1' action='dataImportGedcomFormAction.php' target='results' method='post' ENCTYPE='multipart/form-data' onsubmit="return checkFile(this);">
      <em><?php echo uiTextSnippet('addreplacedata'); ?></em>
      <br>
      <h4><?php echo uiTextSnippet('importgedcom'); ?>:</h4>
      <br>
      <div class='row'>
        <div class='col-md-3'>
          <?php echo uiTextSnippet('fromyourcomputer'); ?>:
        </div>
        <div class='col-md-9'>
          <input class='form-control' id='remotefile' name='remotefile' type='file'>
          <input name='gedcomFile' type='hidden' value=''>
        </div>
      </div>
      <br>
      <div class='row'>
        <div class='col-md-3'>
          <strong><?php echo uiTextSnippet('cap_or'); ?></strong>&nbsp;<?php echo uiTextSnippet('onwebserver'); ?>:
        </div>
        <div class='col-md-6'>
          <input class='form-control' id='database' name='database' type='text'>
        </div>
        <div class='col-md-3'>
          <input id='database_org' type='hidden' value=''>
          <input id='database_last' type='hidden' value=''> 
          <button class='btn btn-outline-primary' id='gedselect' name='gedselect' type='button'><?php echo uiTextSnippet('select') . '...'; ?></button>
        </div>
      </div>
      <br>
      <div class='row'>
        <div class='col-md-9 offset-md-3'>
          <label class='checkbox'>
            <input id='allevents' name='allevents' type='checkbox' value='yes'> <?php echo uiTextSnippet('allevents'); ?>
          </label>
          <label class='checkbox'>
            <input id='eventsonly' name='eventsonly' type='checkbox' value='yes'> <?php echo uiTextSnippet('eventsonly'); ?>
          </label>
        </div>
      </div>
      <hr>
      <div class='row'>
        <div class='col-md-12' id='desttree'>
          <h4><?php echo uiTextSnippet('selectexisting'); ?>:</h4>
          <div id='desttree2'>
            <div class='col-md-3'><?php echo uiTextSnippet('desttree'); ?>:</div>
            <div class='col-md-6'>
              <select class='form-control' id='tree1' name='tree1' onchange="getBranches(this, this.selectedIndex);">
                <?php
                $treectr = 0;
                for ($i = 0; $i < $treenum; $i++) {
                  echo " <option value=\"{$trees[$treectr]}\"";
                  if ($newtree && $newtree == $trees[$treectr]) {
                    echo ' selected';
                  }
                  echo ">{$treename[$treectr]}</option>\n";
                  $treectr++;
                }
                ?>
              </select>
            </div>
            <div class='col-md-3'>
              <button class='btn btn-outline-primary' id='addnewtree' name='newtree' type='button'><?php echo uiTextSnippet('addnewtree'); ?></button>
            </div>
          </div>
          <div class='row' id='destbranch' style='display: none'>
            <?php echo uiTextSnippet('destbranch'); ?>:
            <div id='branch1div'>
              <select id='branch1' name='branch1'></select>
            </div>
          </div>
        </div>
      </div>
      <br>
      <div class='row' id='replace'>
        <div class='col-md-12'>
          <h4><?php echo uiTextSnippet('replace'); ?>:</h4>
          <input id='allcurrentdata' name='del' type='radio' value='yes'<?php if ($tngimpcfg['defimpopt'] == 1) {echo ' checked';} ?>> <?php echo uiTextSnippet('allcurrentdata'); ?> &nbsp;
          <input id='matchingonly' name='del' type='radio' value="match"<?php if (!$tngimpcfg['defimpopt']) {echo ' checked';} ?>> <?php echo uiTextSnippet('matchingonly'); ?> &nbsp;
          <input id='donotreplace' name='del' type='radio' value="no"<?php if ($tngimpcfg['defimpopt'] == 2) {echo ' checked';} ?>> <?php echo uiTextSnippet('donotreplace'); ?> &nbsp;
          <input id='appendall' name='del' type='radio' value="append"<?php if ($tngimpcfg['defimpopt'] == 3) {echo ' checked';} ?>> <?php echo uiTextSnippet('appendall'); ?><br><br>
          <span class="small"><em><?php echo uiTextSnippet('imphints'); ?></em></span>
        </div>
      </div>
      <div class='row' id='ioptions'>
        <div class='col-md-6'>
          <br>
          <div>
            <input name='ucaselast' type='checkbox' value='1'> <?php echo uiTextSnippet('ucaselast'); ?>
          </div>
          <div id='norecalcdiv'<?php if ($tngimpcfg['defimpopt']) {echo " style='display: none'";} ?>>
            <input name='norecalc' type='checkbox' value='1'> <?php echo uiTextSnippet('norecalc'); ?><br>
            <input name='neweronly' type='checkbox' value='1'> <?php echo uiTextSnippet('neweronly'); ?><br>
          </div>
          <div>
            <input name='importmedia' type='checkbox' value='1'> <?php echo uiTextSnippet('importmedia'); ?>
          </div>
          <div>
            <input name='importlatlong' type='checkbox' value='1'> <?php echo uiTextSnippet('importlatlong'); ?>
          </div>
        </div>
        <div class='col-md-6'>
          <br>
          <div id='appenddiv'<?php if ($tngimpcfg['defimpopt'] != 3) {echo " style='display: none;'";} ?>>
            <input name='offsetchoice' type='radio' value='auto' checked> <?php echo uiTextSnippet('autooffset'); ?>&nbsp;<br>
            <input name='offsetchoice' type='radio' value='user'> <?php echo uiTextSnippet('useroffset'); ?>&nbsp;
            <input name='useroffset' type='text' size='10' maxlength='9'>
          </div>
        </div>
      </div>
      <br>
      <div style='float: right'>
        <input id='oldimport' name='old' type='checkbox' value='1'> <?php echo uiTextSnippet('oldimport'); ?>
      </div>
      <button class='btn btn-outline-primary' name='submit' type='submit'><?php echo uiTextSnippet('importdata'); ?></button>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src='js/admin.js'></script>
<script src='js/mediautils.js'></script>
<script>
  var helpLang = '<?php echo findhelp('tree_help.php'); ?>';

  var saveimport = "<?php echo $saveimport; ?>";
  
  var branches = [];
  var branchcounts = [];

  <?php
  $treectr = 0;
  for ($i = 0; $i < $treenum; $i++) {
    $treeref = $trees[$i] ? $trees[$i] : 'none';
    echo "branchcounts['$treeref']=-1;\n";
    $treectr++;
  }
  if ($treectr == 1) {
    echo "$(document).ready(function(){getBranches(document.getElementById('tree1'),1);});\n";
  }
  ?>
</script>
<script src='js/modalAlert.js'></script>
<script src='js/dataimport.js'></script>
<?php if ($debug) { ?>
  <iframe id="results" height="300" width="400" name="results" onload="iframeLoaded();"></iframe>
<?php } else { ?>
  <iframe id="results" height='0' width='0' name="results" onload="iframeLoaded();"></iframe>
<?php } ?>
</body>
</html>
