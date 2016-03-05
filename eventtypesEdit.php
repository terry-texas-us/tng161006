<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if (!$allow_edit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$query = "SELECT * FROM $eventtypes_table WHERE eventtypeID = \"$eventtypeID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['display'] = preg_replace('/\"/', '&#34;', $row['display']);
$row['tag'] = preg_replace('/\"/', '&#34;', $row['tag']);
$row['type'] = preg_replace('/\"/', '&#34;', $row['type']);

switch ($row[type]) {
  case 'I':
    $displaystr = uiTextSnippet('individual');
    break;
  case 'F':
    $displaystr = uiTextSnippet('family');
    break;
  case 'S':
    $displaystr = uiTextSnippet('source');
    break;
  case 'R':
    $displaystr = uiTextSnippet('repository');
    break;
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('modifyeventtype'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('customeventtypes-modifyeventtype', $message);
    $navList = new navList('');
    $navList->appendItem([true, "eventtypesBrowse.php", uiTextSnippet('browse'), "findevent"]);
    $navList->appendItem([$allow_add, "eventtypesAdd.php", uiTextSnippet('add'), "addevent"]);
//    $navList->appendItem([$allow_edit, "#", uiTextSnippet('edit'), "edit"]);
    echo $navList->build("edit");
    ?>
    <form name='form1' action='eventtypesEditFormAction.php' method='post' onsubmit="return validateForm();">
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('assocwith'); ?>:</td>
          <td>
            <select name='type'
                    onChange="populateTags(this.options[this.selectedIndex].value, '');">
              <option value='I'<?php if ($row['type'] == 'I') {echo " selected";} ?>><?php echo uiTextSnippet('individual'); ?></option>
              <option value='F'<?php if ($row['type'] == 'F') {echo " selected";} ?>><?php echo uiTextSnippet('family'); ?></option>
              <option value='S'<?php if ($row['type'] == 'S') {echo " selected";} ?>><?php echo uiTextSnippet('source'); ?></option>
              <option value='R'<?php if ($row['type'] == 'R') {echo " selected";} ?>><?php echo uiTextSnippet('repository'); ?></option>
            </select>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('selecttag'); ?>:</td>
          <td>
            <select name='tag1' onChange="if (this.options[this.selectedIndex].value === 'EVEN') {
                  toggleTdesc(1);
                } else {
                  toggleTdesc(0);
                }">
              <option value="<?php echo $row['tag']; ?>"><?php echo $row['tag']; ?></option>
            </select>
          </td>
        </tr>
        <tr>
          <td>
            &nbsp; <?php echo uiTextSnippet('orenter'); ?>:
          </td>
          <td>
            <input name='tag2' type='text' size='10' onBlur="if (this.value === 'EVEN') {
                  toggleTdesc(1);
                } else {
                  toggleTdesc(0);
                }"> (<?php echo uiTextSnippet('ifbothdata'); ?>)
          </td>
        </tr>
        <tr id="tdesc">
          <td><?php echo uiTextSnippet('typedescription'); ?>*:</td>
          <td><input name='description' type='text' size='40' value="<?php echo $row['description']; ?>"></td>
        </tr>
        <tr id="displaytr"<?php echo $displaytrstyle; ?>>
          <td><?php echo uiTextSnippet('display'); ?>:</td>
          <td><input name='defdisplay' type='text' size='40' value="<?php echo $defdisplay; ?>"></td>
        </tr>
        <?php
        if ($displayrows) {
          ?>
          <tr>
            <td colspan='2'>
              <br>
              <hr style="text-align:left; margin-left:0; width:400px; height:1px;"/>
              <?php echo displayToggle("plus0", 0, "otherlangs", uiTextSnippet('othlangs'), ''); ?>
              <table id="otherlangs"<?php echo $otherlangsstyle; ?>>
                <tr>
                  <td colspan='2'>
                    <br><b><?php echo uiTextSnippet('allnone'); ?></b><br><br></td>
                </tr>
                <?php
                echo $displayrows;
                ?>
              </table>
              <hr style="text-align:left; margin-left:0; width:400px; height:1px;"/>
            </td>
          </tr>
          <?php
        }
        ?>
        <tr>
          <td><?php echo uiTextSnippet('displayorder'); ?>:</td>
          <td><input name='ordernum' type='text' size='4' value="<?php echo $row['ordernum']; ?>">
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('evdata'); ?>:</td>
          <td><input name='keep' type='radio' value='1' <?php if ($row['keep']) {echo "checked";} ?>> <?php echo uiTextSnippet('accept'); ?>
            &nbsp; <input name='keep' type='radio' value='0' <?php if ($row['keep'] != 1) {echo "checked";} ?>> <?php echo uiTextSnippet('ignore'); ?>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('collapseev'); ?>:</td>
          <td><input name='collapse' type='radio' value='1' <?php if ($row['collapse']) {echo "checked";} ?>> <?php echo uiTextSnippet('yes'); ?>
            &nbsp; <input name='collapse' type='radio' value='0' <?php if ($row['collapse'] != 1) {
              echo "checked";
            } ?>> <?php echo uiTextSnippet('no'); ?>
          </td>
        </tr>
      </table>
      <br>
      <input name='eventtypeID' type='hidden' value="<?php echo $eventtypeID; ?>">
      <input name='display' type='hidden' value=''>
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('savechanges'); ?>">
    </form>
    <p>*<?php echo uiTextSnippet('typerequired'); ?></p>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src='js/eventtypes.js'></script>
<script>
  var display = "";
  function addToDisplay(lang, newdisplay) {
    if (display)
      display += "|";
    display += lang + "|" + newdisplay;
  }

  function validateForm() {
    var rval = true;

    <?php
    $dispvalues = explode("|", $row['display']);
    $numvalues = count($dispvalues);
    $disppairs = array();
    if ($numvalues > 1) {
      for ($i = 0; $i < $numvalues; $i += 2) {
        $lang = $dispvalues[$i];
        $disppairs[$lang] = $dispvalues[$i + 1];
      }
    }
    if (count($disppairs) > 1) {
      $defdisplay = "";
      $displaytrstyle = " style=\"display:none\"";
      $otherlangsstyle = "";
    } else {
      $displaytrstyle = "";
      $otherlangsstyle = " style=\"display:none\"";
      if (count($disppairs) == 1) {
        $defdisplay = $dispvalues[1];
      } else {
        $defdisplay = $row['display'];
      }
      $disppairs = null;
    }
    $query = "SELECT languageID, display, folder FROM $languages_table ORDER BY display";
    $langresult = tng_query($query);
    if (tng_num_rows($langresult)) {
      $displayrows = "";
      while ($langrow = tng_fetch_assoc($langresult)) {
        $lang = $langrow['folder'];
        $displayval = "";
        if (is_array($disppairs)) {
          $displayval = isset($disppairs[$lang]) ? $disppairs[$lang] : "";
        } else {
          $displayval = "";
        }
        $display = uiTextSnippet('display') . " ({$langrow['display']})";
        $displayname = "display" . $langrow['languageID'];
        $displayrows .= "<tr><td>$display</td><td><input name=\"$displayname\" type='text' size='40' value=\"$displayval\" onFocus=\"if(this.value == '') this.value = document.form1.defdisplay.value;\"></td></tr>\n";
        echo "if( document.form1.$displayname.value ) addToDisplay('$lang',document.form1.$displayname.value);\n";
      }
    } else {
      $displayrows = "";
    }
    ?>
    if (document.form1.tag2.value.length === 0 && document.form1.tag1.value.length === 0) {
      alert(textSnippet('selectentertag'));
      rval = false;
    } else if ((document.form1.tag2.value === "EVEN" || (document.form1.tag2.value === "" && document.form1.tag1.value === "EVEN")) && document.form1.description.value.length === 0) {
      alert(textSnippet('entertypedesc'));
      rval = false;
    } else if (display === "" && document.form1.defdisplay.value === "") {
      alert(textSnippet('enterdisplay'));
      rval = false;
    } else
      document.form1.display.value = display;

    return rval;
  }
  <?php
  $messages = array('EVEN', 'ADOP', 'ADDR', 'ALIA', 'ANCI', 'BARM', 'BASM', 'CAST', 'CENS', 'CHRA', 'CONF', 'CREM', 'DESI', 'DSCR', 'EDUC', 'EMIG', 'FCOM', 'GRAD', 'IDNO', 'IMMI', 'LANG', 'NATI', 'NATU', 'NCHI', 'NMR', 'OCCU', 'ORDI', 'ORDN', 'PHON', 'PROB', 'PROP', 'REFN', 'RELI', 'RESI', 'RESN', 'RETI', 'RFN', 'RIN', 'SSN', 'WILL', 'ANUL', 'DIV', 'DIVF', 'ENGA', 'MARB', 'MARC', 'MARR', 'MARL');
  foreach ($messages as $msg) {
    echo "messages['$msg'] = \"" . uiTextSnippet($msg) . "\";\n";
  }
  ?>
</script>
<script src="js/admin.js"></script>
<script>
  populateTags(<?php echo "\"{$row['type']}\",\"{$row['tag']}\""; ?>);
</script>
</body>
</html>