<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
include("version.php");


$tng_search_tlevents = $_SESSION['tng_search_tlevents'];

$query = "SELECT * FROM $tlevents_table WHERE tleventID = \"$tleventID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['evdetail'] = preg_replace("/\"/", "&#34;", $row['evdetail']);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('modifytlevent'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="tlevents-modifytlevent">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('tlevents-modifytlevent', $message);
    $navList = new navList('');
    $navList->appendItem([true, "timelineeventsBrowse.php", uiTextSnippet('browse'), "findtlevent"]);
    $navList->appendItem([$allowAdd, "timelineeventsAdd.php", uiTextSnippet('add'), "addtlevent"]);
    $navList->appendItem([$allowEdit, "#", uiTextSnippet('edit'), "edit"]);
    echo $navList->build("edit");
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <form id='form1' name='form1' action='timelineeventsEditFormAction.php' method='post' onSubmit="return validateForm();">
            <table>
              <?php

              function doEventRow($label, $row, $dayname, $monthname, $yearname, $help) {
                ?>
                <tr>
                  <td><?php echo $label; ?>:</td>
                  <td>
                    <select name="<?php echo $dayname; ?>">
                      <option value=''></option>
                      <?php
                      for ($i = 1; $i <= 31; $i++) {
                        echo "<option value=\"$i\"";
                        if ($row[$dayname] == $i) {
                          echo " selected";
                        }
                        echo ">$i</option>\n";
                      }
                      ?>
                    </select>
                    <select name="<?php echo $monthname; ?>">
                      <option value=''></option>
                      <option value='1'<?php if ($row[$monthname] == 1) {echo " selected";} ?>><?php echo uiTextSnippet('JAN'); ?></option>
                      <option value="2"<?php if ($row[$monthname] == 2) {echo " selected";} ?>><?php echo uiTextSnippet('FEB'); ?></option>
                      <option value="3"<?php if ($row[$monthname] == 3) {echo " selected";} ?>><?php echo uiTextSnippet('MAR'); ?></option>
                      <option value="4"<?php if ($row[$monthname] == 4) {echo " selected";} ?>><?php echo uiTextSnippet('APR'); ?></option>
                      <option value="5"<?php if ($row[$monthname] == 5) {echo " selected";} ?>><?php echo uiTextSnippet('MAY'); ?></option>
                      <option value="6"<?php if ($row[$monthname] == 6) {echo " selected";} ?>><?php echo uiTextSnippet('JUN'); ?></option>
                      <option value="7"<?php if ($row[$monthname] == 7) {echo " selected";} ?>><?php echo uiTextSnippet('JUL'); ?></option>
                      <option value="8"<?php if ($row[$monthname] == 8) {echo " selected";} ?>><?php echo uiTextSnippet('AUG'); ?></option>
                      <option value="9"<?php if ($row[$monthname] == 9) {echo " selected";} ?>><?php echo uiTextSnippet('SEP'); ?></option>
                      <option value="10"<?php if ($row[$monthname] == 10) {echo " selected";} ?>><?php echo uiTextSnippet('OCT'); ?></option>
                      <option value="11"<?php if ($row[$monthname] == 11) {echo " selected";} ?>><?php echo uiTextSnippet('NOV'); ?></option>
                      <option value="12"<?php if ($row[$monthname] == 12) {echo " selected";} ?>><?php echo uiTextSnippet('DEC'); ?></option>
                    </select>
                    <input name="<?php echo $yearname; ?>" type='text' size='4' value="<?php echo $row[$yearname]; ?>"/> <span><?php echo $help; ?></span>
                  </td>
                </tr>
                <?php
              }

              doEventRow(uiTextSnippet('startdt'), $row, "evday", "evmonth", "evyear", uiTextSnippet('yrreq'));
              doEventRow(uiTextSnippet('enddt'), $row, "endday", "endmonth", "endyear", "");
              ?>
              <tr>
                <td><?php echo uiTextSnippet('evtitle'); ?>:</td>
                <td>
                  <input name='evtitle' type='text' size='100' value="<?php echo $row['evtitle']; ?>"/>
                </td>
              </tr>
              <tr>
                <td><span><?php echo uiTextSnippet('evdetail'); ?>:</span></td>
                <td colspan='2'><textarea cols="80" rows="8"
                                          name="evdetail"><?php echo $row['evdetail']; ?></textarea></td>
              </tr>
              <tr>
                <td colspan='2'>
                  <span>
                    <?php
                    echo uiTextSnippet('onsave') . ":<br>";
                    echo "<input name='newscreen' type='radio' value='return'> " . uiTextSnippet('savereturn') . "<br>\n";
                    if ($tng_search_tlevents) {
                      echo "<input name='newscreen' type='radio' value='none' checked> " . uiTextSnippet('saveback') . "\n";
                    }
                    ?>
                  </span>
                </td>
              </tr>
            </table>
            <br>
            <input name='tleventID' type='hidden' value="<?php echo $tleventID; ?>">
            <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
          </form>
        </td>
      </tr>

    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script>
    function validateForm() {
      var rval = true;
      if (document.form1.evyear.value.length === 0) {
        alert(textSnippet('enterevyear'));
        rval = false;
      } else if (document.form1.evdetail.value.length === 0) {
        alert(textSnippet('enterevdetail'));
        rval = false;
      } else if (document.form1.endyear.value.length === 0 && (document.form1.endmonth.selectedIndex > 0 || document.form1.endday.selectedIndex > 0)) {
        alert("If you enter a day or month for the ending date, you must also enter an ending year.");
        rval = false;
      } else if ((document.form1.evday.selectedIndex > 0 && document.form1.evmonth.selectedIndex <= 0) || (document.form1.endday.selectedIndex > 0 && document.form1.endmonth.selectedIndex <= 0)) {
        alert("If you select a day, you must also select a month.");
        rval = false;
      } else if (document.form1.endyear.value && parseInt(document.form1.endyear.value) < parseInt(document.form1.evyear.value)) {
        alert("Ending year is less than beginning year.");
        rval = false;
      }
      return rval;
    }
  </script>
</body>
</html>