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

if ($assignedtree) {
  $wherestr = "WHERE gedcom = \"$assignedtree\"";
} else {
  $wherestr = "";
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('addnewsource'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='newsource'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('sources-addnewsource', $message);
    $navList = new navList('');
    $navList->appendItem([true, "sourcesBrowse.php", uiTextSnippet('browse'), "findsource"]);
    $navList->appendItem([$allowAdd, "sourcesAdd.php", uiTextSnippet('add'), "addsource"]);
    $navList->appendItem([$allowEdit && $allowDelete, "sourcesMerge.php", uiTextSnippet('merge'), "merge"]);
    echo $navList->build("addsource");
    ?>
    <form name='form1' action='sourcesAddFormAction.php' method='post' onSubmit="return validateForm();">
      <strong><?php echo uiTextSnippet('prefixsourceid'); ?></strong>
      <?php echo uiTextSnippet('sourceid'); ?>:
      <div class='row'>
        <div class='col-md-6'>
          <div class='input-group'>
            <span class='input-group-btn'>
              <button class='btn btn-secondary' id='generate' type='button' onClick="generateID('source', document.form1.sourceID, document.form1.tree1);"><?php echo uiTextSnippet('generate'); ?></button>
            </span>
            <input class='form-control' id='source-id' name='sourceID' type='text' onBlur="this.value = this.value.toUpperCase()" data-check-result=''>
            <span class='input-group-btn'>
              <button class='btn btn-secondary' id='check' type='button' onClick="checkID(document.form1.sourceID.value, 'source', 'checkmsg', document.form1.tree1);"><?php echo uiTextSnippet('check'); ?></button>
            </span>
          </div>
        </div>
        <div id="checkmsg"></div>
        <div class='col-md-offset-1 col-md-2'>
          <button class='btn btn-primary-outline' name='submit' type='submit' value="<?php echo uiTextSnippet('lockid'); ?>" onClick="document.form1.newscreen[0].checked = true;"><?php echo uiTextSnippet('lockid'); ?></button>
        </div>          
      </div>
      <div class='row'>
        <div class='col-md-6'>
          <?php echo uiTextSnippet('tree'); ?>
          <select class='form-control' name='tree1' onChange="generateID('source', document.form1.sourceID, document.form1.tree1);">
            <?php
            $query = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";
            $result = tng_query($query);
            $numtrees = tng_num_rows($result);
            while ($row = tng_fetch_assoc($result)) {
              echo "  <option value=\"{$row['gedcom']}\">{$row['treename']}</option>\n";
            }
            tng_free_result($result);
            ?>
          </select>
        </div>
      </div>
      <?php include("micro_newsource.php"); ?>
      <p><strong><?php echo uiTextSnippet('sevslater'); ?></strong></p>
        <input name='save' type='submit' value="<?php echo uiTextSnippet('savecont'); ?>">
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/selectutils.js"></script>
<script>
  $(document).ready(function() {
      generateID('source', document.form1.sourceID, document.form1.tree1);
  });
  
  function validateForm() {
      var rval = true;

      document.form1.sourceID.value = TrimString(document.form1.sourceID.value);
      if (document.form1.sourceID.value.length === 0) {
          alert(textSnippet('entersourceid'));
          return false;
      }
      return rval;
  }
</script>
</body>
</html>
