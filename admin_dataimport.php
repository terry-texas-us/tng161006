<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");
include($subroot . "importconfig.php");

if (!$allow_add || !$allow_edit || $assignedbranch) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
if ($assignedtree) {
  $wherestr = "WHERE gedcom = \"$assignedtree\"";
} else {
  $wherestr = "";
}
$query = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";
$result = tng_query($query);
$numtrees = tng_num_rows($result);

$treenum = 0;
$trees = [];
$treename = [];
while ($treerow = tng_fetch_assoc($result)) {
  $trees[$treenum] = $treerow['gedcom'];
  $treename[$treenum] = $treerow['treename'];
  $treenum++;
}
tng_free_result($result);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('datamaint'));

$allow_export = 1;
if (!$allow_ged && $assignedtree) {
  $query = "SELECT disallowgedcreate FROM $trees_table WHERE gedcom = \"$assignedtree\"";
  $disresult = tng_query($query);
  $row = tng_fetch_assoc($disresult);
  
  if ($row['disallowgedcreate']) {
    $allow_export = 0;
  }
  tng_free_result($disresult);
}
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='datamaint-gedimport'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('datamaint-gedimport', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_dataimport.php", uiTextSnippet('import'), "import"]);
    $navList->appendItem([$allow_export, "admin_export.php", uiTextSnippet('export'), "export"]);
    $navList->appendItem([true, "admin_secondmenu.php", uiTextSnippet('secondarymaint'), "second"]);
    echo $navList->build("import");
    ?>
    <form name='form1' action='admin_gedimport.php' target='results' method='post' ENCTYPE='multipart/form-data' onsubmit="return checkFile(this);">
      <table class='table table-sm'>
        <tr>
          <td>
            <em><?php echo uiTextSnippet('addreplacedata'); ?></em>
            <br><br>
            <h4><?php echo uiTextSnippet('importgedcom'); ?>:</h4>
            <table class='table'>
              <tr>
                <td><?php echo uiTextSnippet('fromyourcomputer'); ?>:</td>
                <td><input name='remotefile' type='file'></td>
              </tr>
              <tr>
                <td>
                  <strong><?php echo uiTextSnippet('cap_or'); ?></strong>&nbsp;<?php echo uiTextSnippet('onwebserver'); ?>:
                </td>
                <td>
                  <input id='database' name='database' type='text' size='50'>
                  <input id='database_org' type='hidden' value=''>
                  <input id='database_last' type='hidden' value=''> 
                  <input id='gedselect' name='gedselect' type='button' value="<?php echo uiTextSnippet('select') . "..."; ?>">
                </td>
              </tr>
              <tr>
                <td colspan='2'><br>
                  <input id='allevents' name='allevents' type='checkbox' value='yes'> <?php echo uiTextSnippet('allevents'); ?>&nbsp;
                  <input id='eventsonly' name='eventsonly' type='checkbox' value='yes'> <?php echo uiTextSnippet('eventsonly'); ?>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td id='desttree'>
            <h4><?php echo uiTextSnippet('selectexisting'); ?>:</h4>
            <table class='table table-sm'>
              <tr id='desttree2'>
                <td><?php echo uiTextSnippet('desttree'); ?>:</td>
                <td>
                  <select id='tree1' name='tree1' onchange="getBranches(this, this.selectedIndex);">
                    <?php
                    if ($numtrees != 1) {
                      echo "  <option value=''></option>\n";
                    }
                    $treectr = 0;
                    for ($i = 0; $i < $treenum; $i++) {
                      echo "  <option value=\"{$trees[$treectr]}\"";
                      if ($newtree && $newtree == $trees[$treectr]) {
                        echo ' selected';
                      }
                      echo ">{$treename[$treectr]}</option>\n";
                      $treectr++;
                    }
                    ?>
                  </select>
                  <?php if (!$assignedtree) { ?>
                    <input id='addnewtree' name='newtree' type='button' value="<?php echo uiTextSnippet('addnewtree'); ?>">
                  <?php } ?>
                </td>
              </tr>
              <tr id='destbranch' style='display: none'>
                <td><?php echo uiTextSnippet('destbranch'); ?>:</td>
                <td>
                  <div id='branch1div'>
                    <select id='branch1' name='branch1'></select>
                  </div>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
            <table class='table table-sm'>
              <tr id='replace'>
                <td colspan='2'>
                  <h4><?php echo uiTextSnippet('replace'); ?>:</h4>
                  <input id='allcurrentdata' name='del' type='radio' value='yes'<?php if ($tngimpcfg['defimpopt'] == 1) {echo " checked";} ?>> <?php echo uiTextSnippet('allcurrentdata'); ?> &nbsp;
                  <input id='matchingonly' name='del' type='radio' value="match"<?php if (!$tngimpcfg['defimpopt']) {echo " checked";} ?>> <?php echo uiTextSnippet('matchingonly'); ?> &nbsp;
                  <input id='donotreplace' name='del' type='radio' value="no"<?php if ($tngimpcfg['defimpopt'] == 2) {echo " checked";} ?>> <?php echo uiTextSnippet('donotreplace'); ?> &nbsp;
                  <input id='appendall' name='del' type='radio' value="append"<?php if ($tngimpcfg['defimpopt'] == 3) {echo " checked";} ?>> <?php echo uiTextSnippet('appendall'); ?><br><br>
                  <span class="small"><em><?php echo uiTextSnippet('imphints'); ?></em></span>
                </td>
              </tr>
              <tr id="ioptions">
                <td>
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
                </td>
                <td>
                  <br>
                  <div id='appenddiv'<?php if ($tngimpcfg['defimpopt'] != 3) {echo " style='display: none;'";} ?>>
                    <input name='offsetchoice' type='radio' value='auto' checked> <?php echo uiTextSnippet('autooffset'); ?>&nbsp;<br>
                    <input name='offsetchoice' type='radio' value='user'> <?php echo uiTextSnippet('useroffset'); ?>&nbsp;
                    <input name='useroffset' type='text' size='10' maxlength='9'>
                  </div>
                </td>
              </tr>
            </table>
            <br>
            <div style='float: right'>
              <input id='oldimport' name='old' type='checkbox' value='1'> <?php echo uiTextSnippet('oldimport'); ?>
            </div>
            <input name='submit' type='submit' value="<?php echo uiTextSnippet('importdata'); ?>">
          </td>
        </tr>
      </table>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src='js/admin.js'></script>
<script src='js/mediautils.js'></script>
<script>
  var helpLang = '<?php echo findhelp('tree_help.php'); ?>';

  var saveimport = "<?php echo $saveimport; ?>";
  //var checksecs = <?php echo $checksecs; ?>000;

  var branches = [];
  var branchcounts = new Array();

  <?php
  $treectr = 0;
  for ($i = 0; $i < $treenum; $i++) {
    $treeref = $trees[$i] ? $trees[$i] : "none";
    echo "branchcounts['$treeref']=-1;\n";
    $treectr++;
  }
  if ($treectr == 1) {
    echo "$(document).ready(function(){getBranches(document.getElementById('tree1'),1);});\n";
  }
  ?>
</script>
<script src='js/dataimport.js'></script>
<?php if ($debug) { ?>
  <iframe id="results" height="300" width="400" name="results" onload="iframeLoaded();"></iframe>
<?php } else { ?>
  <iframe id="results" height="0" width="0" name="results" onload="iframeLoaded();"></iframe>
<?php } ?>
</body>
</html>
