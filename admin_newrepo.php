<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if (!$allow_add) {
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
$headSection->setTitle(uiTextSnippet('addnewrepo'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='newrepo'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('repositories-addnewrepo', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_repositories.php", uiTextSnippet('search'), "findrepo"]);
    $navList->appendItem([$allow_add, "admin_newrepo.php", uiTextSnippet('addnew'), "addrepo"]);
    $navList->appendItem([$allow_edit && $allow_delete, "admin_mergerepos.php", uiTextSnippet('merge'), "merge"]);
    echo $navList->build("addrepo");
    ?>
    <form name='form1' action='admin_addrepo.php' method='post' onSubmit="return validateForm();">
      <table class='table table-sm'>
        <tr>
          <td>
            <table class='table table-sm'>
              <tr>
                <td colspan='2'><span><strong><?php echo uiTextSnippet('prefixrepoid'); ?></strong></span>
                </td>
              </tr>
              <tr>
                <td><span><?php echo uiTextSnippet('tree'); ?>:</span></td>
                <td>
                  <select name="tree1"
                          onChange="generateID('repo', document.form1.repoID, document.form1.tree1);">
                    <?php
                    $query = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";
                    $result = tng_query($query);
                    $numtrees = tng_num_rows($result);
                    while ($row = tng_fetch_assoc($result)) {
                      echo "		<option value=\"{$row['gedcom']}\">{$row['treename']}</option>\n";
                    }
                    tng_free_result($result);
                    ?>
                  </select>
                </td>
              </tr>
              <tr>
                <td><span><?php echo uiTextSnippet('repoid'); ?>:</span></td>
                <td>
                  <input name='repoID' type='text' size='10'
                         onBlur="this.value = this.value.toUpperCase()">
                  <input type='button' value="<?php echo uiTextSnippet('generate'); ?>"
                         onClick="generateID('repo', document.form1.repoID, document.form1.tree1);">
                  <input name='submit' type='submit' value="<?php echo uiTextSnippet('lockid'); ?>"
                         onClick="document.form1.newscreen[0].checked = true;">
                  <input type='button' value="<?php echo uiTextSnippet('check'); ?>"
                         onClick="checkID(document.form1.repoID.value, 'repo', 'checkmsg', document.form1.tree1);">
                  <span id="checkmsg"></span>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
            <table class='table table-sm'>
              <tr>
                <td><?php echo uiTextSnippet('name'); ?>:</td>
                <td><input name='reponame' type='text' size='40'>
                  (<?php echo uiTextSnippet('required'); ?>)
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('address1'); ?>:</td>
                <td><input name='address1' type='text' size='50'></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('address2'); ?>:</td>
                <td><input name='address2' type='text' size='50'></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('city'); ?>:</td>
                <td><input name='city' type='text' size='50'></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('stateprov'); ?>:</td>
                <td><input name='state' type='text' size='50'></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('zip'); ?>:</td>
                <td><input name='zip' type='text'></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('countryaddr'); ?>:</td>
                <td><input name='country' type='text' size='50'></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('phone'); ?>:</td>
                <td><input name='phone' type='text' size='30' value=''></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('email'); ?>:</td>
                <td><input name='email' type='text' size='50' value=''></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('website'); ?>:</td>
                <td><input name='www' type='text' size='50' value=''></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
            <p><strong><?php echo uiTextSnippet('revslater'); ?></strong></p>
            <input name='save' type='submit' value="<?php echo uiTextSnippet('savecont'); ?>">
          </td>
        </tr>
      </table>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/selectutils.js"></script>
<script>
  $(document).ready(function() {
      generateID('repo', document.form1.repoID, document.form1.tree1);
  });

  function validateForm() {
    var rval = true;

    document.form1.repoID.value = TrimString(document.form1.repoID.value);
    if (document.form1.repoID.value.length === 0) {
      alert(textSnippet('enterrepoid'));
      return false;
    } else if (document.form1.reponame.value.length === 0) {
      alert(textSnippet('enterreponame'));
      return false;
    }
    return rval;
  }
</script>
</body>
</html>
