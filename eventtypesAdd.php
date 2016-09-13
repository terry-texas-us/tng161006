<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowAdd) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('addnewevtype'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="customeventtypes-addnewevtype">
  <section class='container'>
    <script src="js/eventtypes.js"></script>
    <script>
      function validateForm() {
        var rval = true;
        var display = '';

        <?php
        $query = "SELECT languageID, display, folder FROM $languagesTable ORDER BY display";
        $langresult = tng_query($query);
        if (tng_num_rows($langresult)) {
          $displayrows = '';
          while ($langrow = tng_fetch_assoc($langresult)) {
            $lang = $langrow['folder'];
            $display = uiTextSnippet('display') . " ({$langrow['display']})";
            $displayname = 'display' . $langrow['languageID'];
            $displayrows .= "<tr><td><span>$display</span></td><td><input name=\"$displayname\" type='text' size='40' value='' onFocus=\"if(this.value == '') this.value = document.form1.defdisplay.value;\"></td></tr>\n";
            echo "if( document.form1.$displayname.value ) display = display + \"$lang\" + \"|\" + document.form1.$displayname.value + \"|\";\n";
          }
        } else {
          $displayrows = '';
        }
        ?>
        if (document.form1.tag2.value.length === 0 && document.form1.tag1.value.length === 0) {
          alert(textSnippet('selectentertag'));
          rval = false;
        } else if ((document.form1.tag2.value === 'EVEN' || (document.form1.tag2.value === '' && document.form1.tag1.value === 'EVEN')) && document.form1.description.value.length === 0) {
          alert(textSnippet('entertypedesc'));
          rval = false;
        } else if (display === '' && document.form1.defdisplay.value === '') {
          alert(textSnippet('enterdisplay'));
          rval = false;
        } else
          document.form1.display.value = display;

        return rval;
      }

      var messages = new Array();
      <?php
      $messages = ['EVEN', 'ADOP', 'ADDR', 'ALIA', 'ANCI', 'BARM', 'BASM', 'CAST', 'CENS', 'CHRA', 'CONF', 'CREM', 'DESI', 'DSCR', 'EDUC', 'EMIG', 'FCOM', 'GRAD', 'IDNO', 'IMMI', 'LANG', 'NATI', 'NATU', 'NCHI', 'NMR', 'OCCU', 'ORDI', 'ORDN', 'PHON', 'PROB', 'PROP', 'REFN', 'RELI', 'RESI', 'RESN', 'RETI', 'RFN', 'RIN', 'SSN', 'WILL', 'ANUL', 'DIV', 'DIVF', 'ENGA', 'MARB', 'MARC', 'MARR', 'MARL'];
      foreach ($messages as $msg) {
        echo "messages['$msg'] = \"" . uiTextSnippet($msg) . "\";\n";
      }
      ?>
    </script>
    <script src="js/admin.js"></script>

    <?php
    echo $adminHeaderSection->build('customeventtypes-addnewevtype', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'eventtypesBrowse.php', uiTextSnippet('browse'), 'findevent']);
    //    $navList->appendItem([$allowAdd, 'eventtypesAdd.php', uiTextSnippet('add'), 'addevent']);
    echo $navList->build('addevent');
    ?>
    <form name='form1' action='eventtypesAddFormAction.php' method='post' onSubmit="return validateForm();">
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('assocwith'); ?>:</td>
          <td>
            <select name='type'
                    onChange="populateTags(this.options[this.selectedIndex].value, '');">
              <option value='I'><?php echo uiTextSnippet('individual'); ?></option>
              <option value='F'><?php echo uiTextSnippet('family'); ?></option>
              <option value='S'><?php echo uiTextSnippet('source'); ?></option>
              <option value='R'><?php echo uiTextSnippet('repository'); ?></option>
            </select>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('selecttag'); ?>:</td>
          <td>
            <select name="tag1" onChange="if (this.options[this.selectedIndex].value === 'EVEN') {
                  toggleTdesc(1);
                } else {
                  toggleTdesc(0);
                }">
            </select>
          </td>
        </tr>
        <tr>
          <td>
            &nbsp; <?php echo uiTextSnippet('orenter'); ?>:
          </td>
          <td>
            <input name='tag2' type='text' size='10' onblur="if (this.value === 'EVEN') {
                  toggleTdesc(1);
                } else {
                  toggleTdesc(0);
                }"> (<?php echo uiTextSnippet('ifbothdata'); ?>)
          </td>
        </tr>
        <tr id="tdesc">
          <td><?php echo uiTextSnippet('typedescription'); ?>*:</td>
          <td><input name='description' type='text' size='40'></td>
        </tr>
        <tr id="displaytr">
          <td><?php echo uiTextSnippet('display'); ?>:</td>
          <td><input name='defdisplay' type='text' size='40'></td>
        </tr>
        <?php
        if ($displayrows) {
          ?>
          <tr>
            <td colspan='2'>
              <br>
              <hr style="text-align:left; margin-left:0; width:400px; height:1px;"/>
              <?php echo displayToggle('plus0', 0, 'otherlangs', uiTextSnippet('othlangs'), ''); ?>
              <table style="display:none" id="otherlangs">
                <tr>
                  <td colspan='2'>
                    <br><b><?php echo uiTextSnippet('allnone'); ?></b><br><br></td>
                </tr>
                <?php
                echo $displayrows;
                ?>
              </table>
              <hr style="text-align:left; margin-left:0; width:400px; height:1px;"/>
              <br>
            </td>
          </tr>
          <?php
        }
        ?>
        <tr>
          <td><?php echo uiTextSnippet('displayorder'); ?>:</td>
          <td><input name='ordernum' type='text' size='4' value='0'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('evdata'); ?>:</td>
          <td>
            <input name='keep' type='radio' value='1' checked> <?php echo uiTextSnippet('accept'); ?> &nbsp; 
            <input name='keep' type='radio' value='0'> <?php echo uiTextSnippet('ignore'); ?>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('collapseev'); ?>:</td>
          <td>
            <input name='collapse' type='radio' value='1'> <?php echo uiTextSnippet('yes'); ?> &nbsp; 
            <input name='collapse' type='radio' value='0' checked> <?php echo uiTextSnippet('no'); ?>
          </td>
        </tr>
      </table>
      <br>
      <input name='eventtypeID' type='hidden' value="<?php echo $eventtypeID; ?>">
      <input name='display' type='hidden' value=''>
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    </form>
    <p>*<?php echo uiTextSnippet('typerequired'); ?></p>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script>
    populateTags('I', '');
  </script>
</body>
</html>
