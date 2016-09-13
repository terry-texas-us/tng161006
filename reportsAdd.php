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

$dontdo = ['ADDR', 'BIRT', 'CHR', 'DEAT', 'BURI', 'NAME', 'NICK', 'TITL', 'NSFX', 'NPFX'];

$dfields = [];
$dfields['personID'] = 'personid';
$dfields['fullname'] = 'fullname';
$dfields['lastfirst'] = 'lastfirst';
$dfields['birthdate'] = 'birthdate';
$dfields['birthplace'] = 'birthplace';
if (!$tngconfig['hidechr']) {
  $dfields['altbirthdate'] = 'chrdate';
  $dfields['altbirthplace'] = 'chrplace';
}
$dfields['marrdate'] = 'marriagedate';
$dfields['marrplace'] = 'marriageplace';
$dfields['divdate'] = 'divdate';
$dfields['divplace'] = 'divplace';
$dfields['spouseid'] = 'spouseid';
$dfields['spousename'] = 'spousename';
$dfields['deathdate'] = 'deathdate';
$dfields['deathplace'] = 'deathplace';
$dfields['burialdate'] = 'burialdate';
$dfields['burialplace'] = 'burialplace';
$dfields['changedate'] = 'lastmodified';
$dfields['sex'] = 'sex';
$dfields['title'] = 'title';
$dfields['prefix'] = 'prefix';
$dfields['suffix'] = 'suffix';
$dfields['gedcom'] = 'tree';
if ($allow_lds) {
  $dfields['baptdate'] = 'ldsbapldate';
  $dfields['baptplace'] = 'ldsbaplplace';
  $dfields['confdate'] = 'ldsconfdate';
  $dfields['confplace'] = 'ldsconfplace';
  $dfields['initdate'] = 'ldsinitdate';
  $dfields['initplace'] = 'ldsinitplace';
  $dfields['endldate'] = 'ldsendldate';
  $dfields['endlplace'] = 'ldsendlplace';
  $dfields['ssealdate'] = 'ldssealsdate';
  $dfields['ssealplace'] = 'ldssealsplace';
  $dfields['psealdate'] = 'ldssealpdate';
  $dfields['psealplace'] = 'ldssealpplace';
}

$cfields = [];
$cfields['personID'] = 'personid';
$cfields['firstname'] = 'firstname';
$cfields['lastname'] = 'lastname';
$cfields['lnprefix'] = 'lnprefix';
$cfields['monthonly'] = 'monthonlyfrom';
$cfields['yearonly'] = 'yearonlyfrom';
$cfields['dayonly'] = 'dayonlyfrom';
$cfields['desc'] = 'desc';
$cfields['birthdate'] = 'birthdate';
$cfields['birthdatetr'] = 'birthdatetr';
$cfields['birthplace'] = 'birthplace';
if (!$tngconfig['hidechr']) {
  $cfields['altbirthdate'] = 'chrdate';
  $cfields['altbirthdatetr'] = 'chrdatetr';
  $cfields['altbirthplace'] = 'chrplace';
}
$cfields['marrdate'] = 'marriagedate';
$cfields['marrdatetr'] = 'marriagedatetr';
$cfields['marrplace'] = 'marriageplace';
$cfields['divdate'] = 'divdate';
$cfields['divdatetr'] = 'divdatetr';
$cfields['divplace'] = 'divplace';
$cfields['deathdate'] = 'deathdate';
$cfields['deathdatetr'] = 'deathdatetr';
$cfields['deathplace'] = 'deathplace';
$cfields['burialdate'] = 'burialdate';
$cfields['burialdatetr'] = 'burialdatetr';
$cfields['burialplace'] = 'burialplace';
$cfields['changedate'] = 'lastmodified';
$cfields['sex'] = 'sex';
$cfields['title'] = 'title';
$cfields['prefix'] = 'prefix';
$cfields['suffix'] = 'suffix';
$cfields['gedcom'] = 'tree';
if ($allow_lds) {
  $cfields['baptdate'] = 'ldsbapldate';
  $cfields['baptdatetr'] = 'ldsbapldatetr';
  $cfields['baptplace'] = 'ldsbaplplace';
  $cfields['confdate'] = 'ldsconfdate';
  $cfields['confdatetr'] = 'ldsconfdatetr';
  $cfields['confplace'] = 'ldsconfplace';
  $cfields['initdate'] = 'ldsinitdate';
  $cfields['inittdatetr'] = 'ldsinitdatetr';
  $cfields['initplace'] = 'ldsinitplace';
  $cfields['endldate'] = 'ldsendldate';
  $cfields['endldatetr'] = 'ldsendldatetr';
  $cfields['endlplace'] = 'ldsendlplace';
  $cfields['ssealdate'] = 'ldssealsdate';
  $cfields['ssealdatetr'] = 'ldssealsdatetr';
  $cfields['ssealplace'] = 'ldssealsplace';
  $cfields['psealdate'] = 'ldssealpdate';
  $cfields['psealdatetr'] = 'ldssealpdatetr';
  $cfields['psealplace'] = 'ldssealpplace';
}

$ofields = [];
$ofields['contains'] = 'contains';
$ofields['starts with'] = 'startswith';
$ofields['ends with'] = 'endswith';
$ofields['OR'] = 'or';
$ofields['AND'] = 'and';
$ofields['currmonth'] = 'currentmonth';
$ofields['currmonthnum'] = 'currentmonthnum';
$ofields['curryear'] = 'currentyear';
$ofields['currday'] = 'currentday';
$ofields['today'] = 'today';
$ofields['to_days'] = 'convtodays';

$subtypes = [];
$subtypes['dt'] = uiTextSnippet('rptdate');
$subtypes['tr'] = uiTextSnippet('rptdatetr');
$subtypes['pl'] = uiTextSnippet('place');
$subtypes['fa'] = uiTextSnippet('fact');

$cetypes = [];
$query = "SELECT eventtypeID, tag, display FROM $eventtypes_table WHERE keep=\"1\" AND type=\"I\" ORDER BY display";
$ceresult = tng_query($query);
while ($cerow = tng_fetch_assoc($ceresult)) {
  if (!in_array($cerow['tag'], $dontdo)) {
    $eventtypeID = $cerow['eventtypeID'];
    $cetypes[$eventtypeID] = $cerow;
  }
}
header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('addnewreport'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('reports-addnewreport', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'reportsBrowse.php', uiTextSnippet('browse'), 'findreport']);
    $navList->appendItem([$allowAdd, 'reportsAdd.php', uiTextSnippet('add'), 'addreport']);
    echo $navList->build('addreport');
    ?>
    <form action="reportsAddFormAction.php" method='post' name='form1' id='form1'
          onSubmit="return validateForm();">
      <table class='table table-sm'>
        <tr>
          <td>
            <?php echo uiTextSnippet('reportname'); ?>:
          </td>
          <td>
            <input name='reportname' type='text' size='50' maxlength='80'>
          </td>
        </tr>
        <tr>
          <td>
            <?php echo uiTextSnippet('description'); ?>:
          </td>
          <td>
            <textarea cols="50" rows='3' name="reportdesc"></textarea>
          </td>
        </tr>
        <tr>
          <td><span><?php echo uiTextSnippet('rankpriority'); ?>:</span></td>
          <td><input name='rank' type='text' size='3' maxlength='3' value='1'></td>
        </tr>
        <tr>
          <td><span><?php echo uiTextSnippet('active'); ?>:</span></td>
          <td>
            <input name='active' type='radio' value='1'> <?php echo uiTextSnippet('yes'); ?>
            <input name='active' type='radio' value='0' checked> <?php echo uiTextSnippet('no'); ?>
          </td>
        </tr>
        <tr>
          <td colspan='2'><span><br>
              <img src="img/tng_right.gif" width="17" height="15"
                   align="middle"> = <?php echo uiTextSnippet('add'); ?> &nbsp;&nbsp;
              <img src="img/tng_left.gif" width="17" height="15"
                   align="middle"> = <?php echo uiTextSnippet('remove'); ?> &nbsp;&nbsp;
              <img src="img/tng_up.gif" width="17" height="15"
                   align="middle"> = <?php echo uiTextSnippet('moveup'); ?> &nbsp;&nbsp;
              <img src="img/tng_down.gif" width="17" height="15"
                   align="middle"> = <?php echo uiTextSnippet('movedown'); ?>
              &nbsp;&nbsp;</span><br><br>
            <h4><?php echo uiTextSnippet('choosedisplay'); ?>:</h4></td>
        </tr>
        <tr>
          <td colspan='2'>
            <table class='table table-sm'>
              <tr>
                <td>
                  <select name="availfields" size="15" class="reportcol"
                          onDblClick="AddtoDisplay(document.form1.availfields, document.form1.displayfields);">
                    <?php
                    foreach ($dfields as $key => $value) {
                      echo "<option value='$key'>" . uiTextSnippet($value) . "</option>\n";
                    }
                    //now do custom event types
                    foreach ($cetypes as $cerow) {
                      $displaymsg = getEventDisplay($cerow['display']);
                      echo "<option value=\"ce_dt_{$cerow['eventtypeID']}\">$displaymsg: " . uiTextSnippet('rptdate') . "</option>\n";
                      echo "<option value=\"ce_pl_{$cerow['eventtypeID']}\">$displaymsg: " . uiTextSnippet('place') . "</option>\n";
                      echo "<option value=\"ce_fa_{$cerow['eventtypeID']}\">$displaymsg: " . uiTextSnippet('fact') . "</option>\n";
                    }
                    ?>
                  </select>
                </td>
                <td width="40">
                  &nbsp;<a
                          href="javascript:AddtoDisplay(document.form1.availfields,document.form1.displayfields);"><img
                            src="img/tng_right.gif"
                            alt="<?php echo uiTextSnippet('add'); ?>" width="17"
                            height="15"></a>&nbsp;<br><br>
                  &nbsp;<a href="javascript:RemovefromDisplay(document.form1.displayfields);"><img
                            src="img/tng_left.gif"
                            alt="<?php echo uiTextSnippet('remove'); ?>" width="17"
                            height="15"></a>&nbsp;
                </td>
                <td>
                  <select name="displayfields" size="15" class="reportcol"
                          onDblClick="RemovefromDisplay(document.form1.displayfields);">
                  </select><br><br>
                </td>
                <td>
                  &nbsp;&nbsp;&nbsp;<a
                          href="javascript:Move(document.form1.displayfields,1);"><img
                            src="img/tng_up.gif"
                            alt="<?php echo uiTextSnippet('moveup'); ?>" width="17"
                            height="15"></a>&nbsp;<br><br>
                  &nbsp;&nbsp;&nbsp;<a
                          href="javascript:RemovefromDisplay(document.form1.displayfields);"><img
                            src="img/tng_left.gif"
                            alt="<?php echo uiTextSnippet('remove'); ?>" width="17"
                            height="15"></a>&nbsp;<br><br>
                  &nbsp;&nbsp;&nbsp;<a
                          href="javascript:Move(document.form1.displayfields,0);"><img
                            src="img/tng_down.gif"
                            alt="<?php echo uiTextSnippet('movedown'); ?>" width="17"
                            height="15"></a>&nbsp;<br><br>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan='2'>
            <h4><?php echo uiTextSnippet('choosecriteria'); ?>:</h4>
          </td>
        </tr>
        <tr>
          <td colspan='2'>
            <table class='table table-sm'>
              <tr>
                <td>
                  <select name="availcriteria" size='12' class="reportcol"
                          onDblClick="AddtoDisplay(document.form1.availcriteria, document.form1.finalcriteria);">
                    <?php
                    foreach ($cfields as $key => $value) {
                      if ($key != 'desc') {
                        echo "<option value='$key'>" . uiTextSnippet($value) . "</option>\n";
                      }
                    }
                    echo "<option value='living'>" . uiTextSnippet('livingtrue') . "</option>\n";
                    echo "<option value='dead'>" . uiTextSnippet('livingfalse') . "</option>\n";
                    echo "<option value='private'>" . uiTextSnippet('privatetrue') . "</option>\n";
                    echo "<option value='open'>" . uiTextSnippet('privatefalse') . "</option>\n";

                    //now do custom event types, prefix with "ce_"
                    foreach ($cetypes as $cerow) {
                      $displaymsg = getEventDisplay($cerow['display']);
                      echo "<option value=\"ce_dt_{$cerow['eventtypeID']}\">$displaymsg: " . uiTextSnippet('rptdate') . "</option>\n";
                      echo "<option value=\"ce_tr_{$cerow['eventtypeID']}\">$displaymsg: " . uiTextSnippet('rptdatetr') . "</option>\n";
                      echo "<option value=\"ce_pl_{$cerow['eventtypeID']}\">$displaymsg: " . uiTextSnippet('place') . "</option>\n";
                      echo "<option value=\"ce_fa_{$cerow['eventtypeID']}\">$displaymsg: " . uiTextSnippet('fact') . "</option>\n";
                    }
                    ?>
                  </select>
                </td>
                <td width="40">
                  &nbsp;<a
                          href="javascript:AddtoDisplay(document.form1.availcriteria,document.form1.finalcriteria);"><img
                            src="img/tng_right.gif"
                            alt="<?php echo uiTextSnippet('add'); ?>" width="17"
                            height="15"></a>&nbsp;<br><br>
                  &nbsp;<a href="javascript:RemovefromDisplay(document.form1.finalcriteria);"><img
                            src="img/tng_left.gif"
                            alt="<?php echo uiTextSnippet('remove'); ?>" width="17"
                            height="15"></a>&nbsp;
                </td>
                <td rowspan='4'>
                  <select name="finalcriteria" size="28" class="reportcol" onDblClick="RemovefromDisplay(document.form1.finalcriteria);">
                  </select>
                </td>
                <td width="40" rowspan='4'>
                  &nbsp;<a href="javascript:Move(document.form1.finalcriteria,1);"><img
                            src="img/tng_up.gif"
                            alt="<?php echo uiTextSnippet('moveup'); ?>" width="17"
                            height="15"></a>&nbsp;<br><br>
                  &nbsp;<a href="javascript:RemovefromDisplay(document.form1.finalcriteria);"><img
                            src="img/tng_left.gif"
                            alt="<?php echo uiTextSnippet('remove'); ?>" width="17"
                            height="15"></a>&nbsp;<br><br>
                  &nbsp;<a href="javascript:Move(document.form1.finalcriteria,0);"><img
                            src="img/tng_down.gif"
                            alt="<?php echo uiTextSnippet('movedown'); ?>" width="17"
                            height="15"></a>&nbsp;<br><br><br><br><br><br><br><br>
                </td>
              </tr>
              <tr>
                <td>
                  <span><?php echo uiTextSnippet('operators'); ?>:<br></span>
                  <select name="availoperators" size="8" class="reportcol"
                          onDblClick="AddtoDisplay(document.form1.availoperators, document.form1.finalcriteria);">
                    <option value="eq">=</option>
                    <option value="neq">!=</option>
                    <option value="gt">&gt;</option>
                    <option value="gte">&gt;=</option>
                    <option value="lt">&lt;</option>
                    <option value="lte">&lt;=</option>
                    <?php
                    foreach ($ofields as $key => $value) {
                      echo "<option value='$key'>" . uiTextSnippet($value) . "</option>\n";
                    }
                    ?>
                    <option value="(">(</option>
                    <option value=")">)</option>
                    <option value="+">+</option>
                    <option value="-">-</option>
                  </select>
                </td>
                <td width="40">
                  &nbsp;<a
                          href="javascript:AddtoDisplay(document.form1.availoperators,document.form1.finalcriteria);"><img
                            src="img/tng_right.gif"
                            alt="<?php echo uiTextSnippet('add'); ?>" width="17"
                            height="15"></a>&nbsp;
                </td>
              </tr>
              <tr>
                <td>
                  <span><?php echo uiTextSnippet('constantstring'); ?>:*<br></span>
                  <input name='constantstring' type='text'>
                </td>
                <td width="40"><br>
                  &nbsp;<a
                          href="javascript:AddConstant(document.form1.constantstring,document.form1.finalcriteria,1);"><img
                            src="img/tng_right.gif"
                            alt="<?php echo uiTextSnippet('add'); ?>" width="17"
                            height="15"></a>&nbsp;
                </td>
              </tr>
              <tr>
                <td>
                  <span><?php echo uiTextSnippet('constantvalue'); ?>:<br></span>
                  <input name='constantvalue' type='text'>
                </td>
                <td width="40"><br>
                  &nbsp;<a
                          href="javascript:AddConstant(document.form1.constantvalue,document.form1.finalcriteria,0);"><img
                            src="img/tng_right.gif"
                            alt="<?php echo uiTextSnippet('add'); ?>" width="17"
                            height="15"></a>&nbsp;
                </td>
              </tr>
            </table>
            <span>*<?php echo uiTextSnippet('foremptystring'); ?></span>
          </td>
        </tr>
        <tr>
          <td colspan='2'><br>
            <h4><?php echo uiTextSnippet('choosesort'); ?>:</h4>
          </td>
        </tr>
        <tr>
          <td colspan='2'>
            <table class='table table-sm'>
              <tr>
                <td>
                  <select name="availsort" size='10' class="reportcol"
                          onDblClick="AddtoDisplay(document.form1.availsort, document.form1.finalsort);">
                    <?php
                    foreach ($cfields as $key => $value) {
                      echo "<option value='$key'>" . uiTextSnippet($value) . "</option>\n";
                    }
                    //now do custom event types, prefix with "ce_"
                    foreach ($cetypes as $cerow) {
                      $displaymsg = getEventDisplay($cerow['display']);
                      echo "<option value=\"ce_dt_{$cerow['eventtypeID']}\">$displaymsg: " . uiTextSnippet('rptdate') . "</option>\n";
                      echo "<option value=\"ce_tr_{$cerow['eventtypeID']}\">$displaymsg: " . uiTextSnippet('rptdatetr') . "</option>\n";
                      echo "<option value=\"ce_pl_{$cerow['eventtypeID']}\">$displaymsg: " . uiTextSnippet('place') . "</option>\n";
                      echo "<option value=\"ce_fa_{$cerow['eventtypeID']}\">$displaymsg: " . uiTextSnippet('fact') . "</option>\n";
                    }
                    ?>
                  </select>
                </td>
                <td width="40">
                  &nbsp;<a
                          href="javascript:AddtoDisplay(document.form1.availsort,document.form1.finalsort);"><img
                            src="img/tng_right.gif"
                            alt="<?php echo uiTextSnippet('add'); ?>" width="17"
                            height="15"></a>&nbsp;<br><br>
                  &nbsp;<a href="javascript:RemovefromDisplay(document.form1.finalsort);"><img
                            src="img/tng_left.gif"
                            alt="<?php echo uiTextSnippet('remove'); ?>" width="17"
                            height="15"></a>&nbsp;
                </td>
                <td>
                  <select name="finalsort" size='10' class="reportcol"
                          onDblClick="RemovefromDisplay(document.form1.finalsort);">
                  </select>
                </td>
                <td width="40">
                  &nbsp;<a href="javascript:Move(document.form1.finalsort,1);"><img
                            src="img/tng_up.gif"
                            alt="<?php echo uiTextSnippet('moveup'); ?>" width="17"
                            height="15"></a>&nbsp;<br><br>
                  &nbsp;<a href="javascript:RemovefromDisplay(document.form1.finalsort);"><img
                            src="img/tng_left.gif"
                            alt="<?php echo uiTextSnippet('remove'); ?>" width="17"
                            height="15"></a>&nbsp;<br><br>
                  &nbsp;<a href="javascript:Move(document.form1.finalsort,0);"><img
                            src="img/tng_down.gif"
                            alt="<?php echo uiTextSnippet('movedown'); ?>" width="17"
                            height="15"></a>&nbsp;
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan='2'><span><br><b><?php echo uiTextSnippet('altreport'); ?>:</b><br></span>
          </td>
        </tr>
        <tr>
          <td colspan='2'><textarea cols="60" rows='4' name="sqlselect"></textarea></td>
        </tr>
      </table>
      <br>
      <input name='display' type='hidden' value=''>
      <input name='criteria' type='hidden' value=''>
      <input name='orderby' type='hidden' value=''>
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('savereport'); ?>">
      <input name='submitx' type='submit' value="<?php echo uiTextSnippet('saveexit'); ?>">
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/selectutils.js"></script>
<script src="js/reports.js"></script>
<script>
  function validateForm() {
    var rval = true;
    if (document.form1.reportname.value.length === 0) {
      alert(textSnippet('enterreportname'));
      rval = false;
    } else if (document.form1.displayfields.options.length === 0 && document.form1.sqlselect.value.length === 0) {
      alert(textSnippet('selectdisplayfield'));
      rval = false;
    }
    if (rval)
      finishValidation();
    return rval;
  }
</script>
</body>
</html>
