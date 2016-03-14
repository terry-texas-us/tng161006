<?php
include("begin.php");
include($subroot . "importconfig.php");
include("adminlib.php");

if ($link) {
  $admin_login = 1;
  include("checklogin.php");
  include("version.php");

  if ($assignedtree || !$allow_edit) {
    $message = uiTextSnippet('norights');
    header("Location: admin_login.php?message=" . urlencode($message));
    exit;
  }

  $query = "SELECT gedcom, treename FROM $trees_table ORDER BY treename";
  $result = @tng_query($query);
} else {
  $result = false;
}

if (!$tngimpcfg['maxlivingage']) {
  $tngimpcfg['maxlivingage'] = "110";
}

//for upgrading to 6
if ($localphotopathdisplay && !$locimppath['photos']) {
  $locimppath['photos'] = $localphotopathdisplay;
}
if ($localdocpathdisplay && !$locimppath['histories']) {
  $locimppath['histories'] = $localdocpathdisplay;
}

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('modifyimportsettings'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="setup-configuration-importconfigsettings">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('setup-configuration-importconfigsettings', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_setup.php", uiTextSnippet('configuration'), "configuration"]);
    $navList->appendItem([true, "admin_diagnostics.php", uiTextSnippet('diagnostics'), "diagnostics"]);
    $navList->appendItem([true, "admin_setup.php?sub=tablecreation", uiTextSnippet('tablecreation'), "tablecreation"]);
    $navList->appendItem([true, "#", uiTextSnippet('importconfigsettings'), "import"]);
    echo $navList->build("import");
    ?>
    <form action="admin_updateimportconfig.php" method='post' name='form1'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('gedpath'); ?>:</td>
          <td>
            <input name='gedpath' type='text' value="<?php echo $gedpath; ?>" size='50'>
            <input type='button' value="<?php echo uiTextSnippet('makefolder'); ?>"
                   onclick="makeFolder('gedcom', document.form1.gedpath.value);"> <span
                    id="msg_gedcom"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('saveimportstate'); ?>:</td>
          <td>
            <input name='saveimport' type='checkbox' value='1' <?php if ($saveimport) {echo "checked";} ?>> <?php echo uiTextSnippet('allowresume'); ?>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('rrnum'); ?>:</td>
          <td><input name='rrnum' type='text' value="<?php echo $tngimpcfg['rrnum']; ?>" size='5'>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('defimpopt'); ?>:</td>
          <td>
            <select name="defimpopt">
              <option value='1'<?php if ($tngimpcfg['defimpopt'] == 1) {
                echo " selected";
              } ?>><?php echo uiTextSnippet('allcurrentdata'); ?></option>
              <option value='0'<?php if (!$tngimpcfg['defimpopt']) {
                echo " selected";
              } ?>><?php echo uiTextSnippet('matchingonly'); ?></option>
              <option value="2"<?php if ($tngimpcfg['defimpopt'] == 2) {
                echo " selected";
              } ?>><?php echo uiTextSnippet('donotreplace'); ?></option>
              <option value="3"<?php if ($tngimpcfg['defimpopt'] == 3) {
                echo " selected";
              } ?>><?php echo uiTextSnippet('appendall'); ?></option>
            </select>

          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('blankchangedt'); ?>:</td>
          <td>
            <select name="blankchangedt">
              <option value='0'<?php if (!$tngimpcfg['chdate']) {
                echo " selected";
              } ?>><?php echo uiTextSnippet('usetoday'); ?></option>
              <option value='1'<?php if ($tngimpcfg['chdate']) {
                echo " selected";
              } ?>><?php echo uiTextSnippet('useblank'); ?></option>
            </select>

          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('nobirthdate'); ?>:</td>
          <td>
            <select name="livingreqbirth">
              <option value='0'<?php if (!$tngimpcfg['livingreqbirth']) {
                echo " selected";
              } ?>><?php echo uiTextSnippet('persdead'); ?></option>
              <option value='1'<?php if ($tngimpcfg['livingreqbirth']) {
                echo " selected";
              } ?>><?php echo uiTextSnippet('persliving'); ?></option>
            </select>

          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('nodeathdate'); ?>:</td>
          <td><input name='maxlivingage' type='text' value="<?php echo $tngimpcfg['maxlivingage']; ?>" size='5'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('assumepriv'); ?>:</td>
          <td><input name='maxprivyrs' type='text' value="<?php echo $tngimpcfg['maxprivyrs']; ?>" size='5'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('embeddedmedia'); ?>:</td>
          <td>
            <input name='assignnames' type='checkbox' value='yes' <?php if ($assignnames) {echo "checked";} ?>> <?php echo uiTextSnippet('assignnames'); ?>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('localphotopath'); ?>*:</td>
          <td><input type='text' value="<?php echo $locimppath['photos']; ?>"
                     name="localphotopathdisplay" class="verylongfield"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('localdocpath'); ?>*:</td>
          <td><input type='text' value="<?php echo $locimppath['histories']; ?>"
                     name="localhistorypathdisplay" class="verylongfield"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('localdocumentpath'); ?>*:</td>
          <td><input type='text' value="<?php echo $locimppath['documents']; ?>"
                     name="localdocumentpathdisplay" class="verylongfield"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('localhspath'); ?>*:</td>
          <td><input type='text' value="<?php echo $locimppath['headstones']; ?>"
                     name="localhspathdisplay" class="verylongfield"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('localotherpath'); ?>*:</td>
          <td><input type='text' value="<?php echo $locimppath['other']; ?>"
                     name="localotherpathdisplay" class="verylongfield"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('nopathmatch'); ?>:</td>
          <td colspan='4'>
            <input name='wholepath' type='radio' value='1' <?php if ($wholepath) {echo "checked";} ?>> <?php echo uiTextSnippet('wholepath'); ?>
            <input name='wholepath' type='radio' value='0' <?php if (!$wholepath) {echo "checked";} ?>> <?php echo uiTextSnippet('justname'); ?>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('privnote'); ?>:</td>
          <td><input name='privnote' type='text' value="<?php echo $tngimpcfg['privnote']; ?>" size='5'></td>
        </tr>
      </table>
      <br>&nbsp;
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    </form>
    <p>*<?php echo uiTextSnippet('commas'); ?></p>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/admin.js"></script>
</body>
</html>
