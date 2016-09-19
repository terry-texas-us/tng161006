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
$headSection->setTitle(uiTextSnippet('addnewtlevent'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('tlevents-addnewtlevent', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'timelineeventsBrowse.php', uiTextSnippet('browse'), 'findtimeline']);
    // $navList->appendItem([$allowAdd, 'timelineeventsAdd.php', uiTextSnippet('add'), 'addtlevent']);
    echo $navList->build('addtlevent');
    ?>
    <form name='form1' action='timelineeventsAddFormAction.php' method='post' onSubmit="return validateForm();">
      <table class='table table-sm'>
        
        <?php 
        function doEventRow($label, $dayname, $monthname, $yearname, $help) {
        ?>
          <tr>
            <td><?php echo $label; ?>:</td>
            <td>
              <select name="<?php echo $dayname; ?>">
                <option value=''></option>
                <?php
                for ($i = 1; $i <= 31; $i++) {
                  echo "<option value=\"$i\">$i</option>\n";
                }
                ?>
              </select>
              <select name="<?php echo $monthname; ?>">
                <option value=''></option>
                <option value='1'><?php echo uiTextSnippet('JAN'); ?></option>
                <option value='2'><?php echo uiTextSnippet('FEB'); ?></option>
                <option value='3'><?php echo uiTextSnippet('MAR'); ?></option>
                <option value='4'><?php echo uiTextSnippet('APR'); ?></option>
                <option value='5'><?php echo uiTextSnippet('MAY'); ?></option>
                <option value="6"><?php echo uiTextSnippet('JUN'); ?></option>
                <option value="7"><?php echo uiTextSnippet('JUL'); ?></option>
                <option value="8"><?php echo uiTextSnippet('AUG'); ?></option>
                <option value="9"><?php echo uiTextSnippet('SEP'); ?></option>
                <option value="10"><?php echo uiTextSnippet('OCT'); ?></option>
                <option value="11"><?php echo uiTextSnippet('NOV'); ?></option>
                <option value="12"><?php echo uiTextSnippet('DEC'); ?></option>
              </select>
              <input name="<?php echo $yearname; ?>" type='text' size='4' />
              <span><?php echo $help; ?></span>
            </td>
          </tr>
          <?php
        }
        
        doEventRow(uiTextSnippet('startdt'), 'evday', 'evmonth', 'evyear', uiTextSnippet('yrreq'));
        doEventRow(uiTextSnippet('enddt'), 'endday', 'endmonth', 'endyear', '');
        ?>
      </table>
      <?php echo uiTextSnippet('evtitle'); ?>:
      <input class='form-control' name='evtitle' type='text' size='100'>
      <?php echo uiTextSnippet('evdetail'); ?>:
      <textarea class='form-control' name='evdetail' rows='8'></textarea>
      <br>
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    </form>
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
    