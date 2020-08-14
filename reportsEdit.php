<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
$query = "SELECT * FROM reports WHERE reportID = \"$reportID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
$row['sqlselect'] = preg_replace('/\"/', '&#34;', $row['sqlselect']);

tng_free_result($result);

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
$dfields['suffix'] = 'suffix';
$dfields['prefix'] = 'prefix';
$dfields['gedcom'] = 'tree';
if ($allowLds) {
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
if ($allowLds) {
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
$query = 'SELECT eventtypeID, tag, display FROM eventtypes WHERE keep="1" AND type="I" ORDER BY display';
$ceresult = tng_query($query);
while ($cerow = tng_fetch_assoc($ceresult)) {
  if (!in_array($cerow['tag'], $dontdo)) {
    $eventtypeID = $cerow['eventtypeID'];
    $cetypes[$eventtypeID] = $cerow;
  }
}
header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('modifyreport'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
<body id='reports-modifyreport'>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('reports-modifyreport', $message);
    $navList = new navList('');
    $navList->appendItem([1, 'reportsBrowse.php', uiTextSnippet('browse'), 'findreport']);
    $navList->appendItem([$allowAdd, 'reportsAdd.php', uiTextSnippet('add'), 'addreport']);
    $navList->appendItem([$allowEdit, '#', uiTextSnippet('edit'), 'edit']);
    echo $navList->build('edit');
    ?>
    <form id='form1' name='form1' action='reportsEditFormAction.php' method='post' onSubmit="return validateForm();">
      <div class='row'>
        <div class='col-md-3'>
          <?php echo uiTextSnippet('reportname'); ?>:
        </div>
        <div class='col-md-6'>
          <input class='form-control' name='reportname' type='text' size='50' maxlength='80' value="<?php echo $row['reportname']; ?>">
        </div>
      </div>
      <div class='row'>
        <div class='col-md-3'>
          <span><?php echo uiTextSnippet('description'); ?>:</span>
        </div>
        <div class='col-md-6'>
          <textarea class='form-control' name="reportdesc" cols="50" rows='3'><?php echo $row['reportdesc']; ?></textarea>
        </div>
      </div>
      <br>
      <div class='row'>
        <div class='col-md-2'>
          <span><?php echo uiTextSnippet('rankpriority'); ?>:</span>
        </div>
        <div class='col-md-3'>
          <input class='form-control' name='ranking' type='text' size='3' maxlength='3' value="<?php echo $row['ranking']; ?>">
        </div>
        <div class='offset-md-1 col-md-2'>
          <?php echo uiTextSnippet('active'); ?>:
        </div>
        <div class='col-md-3'>
          <label class='form-check-inline'>
            <input class='form-check-input' name='active' type='radio' value='1' <?php if ($row['active']) {echo 'checked';} ?>>
            <?php echo uiTextSnippet('yes'); ?>
          </label>
          <label class='form-check-inline'>
            <input class='form-check-put' name='active' type='radio' value='0' <?php if (!$row['active']) {echo 'checked';} ?>>
            <?php echo uiTextSnippet('no'); ?>
          </label>
        </div>
      </div>
      <br><hr>
      <h4><?php echo uiTextSnippet('choosedisplay'); ?>:</h4>
      <div class='row'>
        <div class='col-md-5'>
          <select class="form-control" name="availfields" size="8" style="overflow-y: auto;" onDblClick="AddtoDisplay(document.form1.availfields, document.form1.displayfields);">
            <?php
            foreach ($dfields as $key => $value) {
              echo "<option value=\"$key\">" . uiTextSnippet($value) . "</option>\n";
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
        </div>
        <div class='col-md-1'>
          <a href="javascript:AddtoDisplay(document.form1.availfields,document.form1.displayfields);" title="<?php echo uiTextSnippet('add'); ?>"><img src="img/tng_right.gif" alt="<?php echo uiTextSnippet('add'); ?>" width="17" height="15"></a>
          <br><br>
          <a href="javascript:RemovefromDisplay(document.form1.displayfields);" title="<?php echo uiTextSnippet('remove'); ?>"><img src="img/tng_left.gif" alt="<?php echo uiTextSnippet('remove'); ?>" width="17" height="15"></a>
        </div>
        <div class='col-md-5'>
          <select class="form-control" name="displayfields" size="8"  style="overflow-y: auto;" onDblClick="RemovefromDisplay(document.form1.displayfields);">
            <?php
            $displayfields = explode($lineending, $row['display']);
            for ($i = 0; $i < count($displayfields) - 1; $i++) {
              $dfield = $displayfields[$i];
              if ($dfield == 'lastname, firstname') {
                $dfield = 'lastfirst';
              } elseif ($dfield == 'firstname, lastname') {
                $dfield = 'fullname';
              }
              if (isset($dfields[$dfield])) {
                $dtext = $dfields[$dfield];
                $displaymsg = uiTextSnippet($dtext);
              } elseif (substr($dfield, 0, 3) == 'ce_') { // custom events
                $eventtypeID = substr($dfield, 6);
                $subtype = substr($dfield, 3, 2);
                $stdisplay = $subtypes[$subtype];
                $cerow = $cetypes[$eventtypeID];
                $displaymsg = getEventDisplay($cerow['display']) . ': ' . $stdisplay;
              }
              echo "<option value=\"$dfield\">$displaymsg</option>\n";
            }
            ?>
          </select>
        </div>
        <div class='col-md-1'>
          <a href="javascript:Move(document.form1.displayfields,1);"><img src="img/tng_up.gif" alt="<?php echo uiTextSnippet('moveup'); ?>" width="17" height="15"></a><br>
          <a href="javascript:Move(document.form1.displayfields,0);"><img src="img/tng_down.gif" alt="<?php echo uiTextSnippet('movedown'); ?>" width="17" height="15"></a>
        </div>
      </div>
      <br>
      <h4><?php echo uiTextSnippet('choosecriteria'); ?>:</h4>
      <div class='row'>
        <div class='col-md-5'>
          <div class='row'>
            <div class='col-md-10'>
              <select class="form-control" name="availcriteria" size="8" style="overflow-y: auto;" onDblClick="AddtoDisplay(document.form1.availcriteria, document.form1.finalcriteria);">
                <?php
                foreach ($cfields as $key => $value) {
                  if ($key != 'desc') {
                    echo "<option value=\"$key\">" . uiTextSnippet($value) . "</option>\n";
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
            </div>
            <div class='col-md-2'>
              <a href="javascript:AddtoDisplay(document.form1.availcriteria,document.form1.finalcriteria);"><img src="img/tng_right.gif" alt="<?php echo uiTextSnippet('add'); ?>" width="17" height="15"></a>
              <a href="javascript:RemovefromDisplay(document.form1.finalcriteria);"><img src="img/tng_left.gif" alt="<?php echo uiTextSnippet('remove'); ?>" width="17" height="15"></a>
            </div>
          </div>
          <br>
          <span><?php echo uiTextSnippet('operators'); ?>:<br></span>
          <div class='row'>
            <div class='col-md-10'>
              <select class="form-control" name="availoperators" size="8" style="overflow-y: auto;" onDblClick="AddtoDisplay(document.form1.availoperators, document.form1.finalcriteria);">
                <option value="eq">=</option>
                <option value="neq">!=</option>
                <option value="gt">&gt;</option>
                <option value="gte">&gt;=</option>
                <option value="lt">&lt;</option>
                <option value="lte">&lt;=</option>
                <?php
                foreach ($ofields as $key => $value) {
                  echo "<option value=\"$key\">" . uiTextSnippet($value) . "</option>\n";
                }
                ?>
                <option value="(">(</option>
                <option value=")">)</option>
                <option value="+">+</option>
                <option value="-">-</option>
              </select>
            </div>
            <div class='col-md-2'>
              <a href="javascript:AddtoDisplay(document.form1.availoperators,document.form1.finalcriteria);"><img src="img/tng_right.gif" alt="<?php echo uiTextSnippet('add'); ?>" width="17" height="15"></a>
            </div>
          </div>
          <br>
          <div class='row'>
            <div class='col-md-10'>
              <?php echo uiTextSnippet('constantstring'); ?>:*
              <input class='form-control' name='constantstring' type='text'>
            </div>
            <div class='col-md-2'>
              <a href="javascript:AddConstant(document.form1.constantstring,document.form1.finalcriteria,1);"><img src="img/tng_right.gif" alt="<?php echo uiTextSnippet('add'); ?>" width="17" height="15"></a>
            </div>
          </div>  
          <br>
          <div class='row'>
            <div class='col-md-10'>
              <?php echo uiTextSnippet('constantvalue'); ?>:
              <input class='form-control' name='constantvalue' type='number'>
            </div>
            <div class='col-md-2'>
              <a href="javascript:AddConstant(document.form1.constantvalue,document.form1.finalcriteria,0);"><img src="img/tng_right.gif" alt="<?php echo uiTextSnippet('add'); ?>" width="17" height="15"></a>
            </div>
          </div>
        </div>
        <div class='offset-md-1 col-md-5'>
          <select class="form-control" name="finalcriteria" size="28" style="overflow-y: auto;" onDblClick="RemovefromDisplay(document.form1.finalcriteria);">
            <?php
            $criteriafields = explode($lineending, $row['criteria']);
            $mnemonics = ['eq', 'neq', 'gt', 'gte', 'lt', 'lte'];
            $symbols = ['=', '!=', '>', '>=', '<', '<='];
            for ($i = 0; $i < count($criteriafields) - 1; $i++) {
              $cfield = preg_replace("'\"'", '', $criteriafields[$i]);
              if (isset($cfields[$cfield])) {
                $ctext = $cfields[$cfield];
                $displaymsg = uiTextSnippet($ctext);
              } elseif (isset($ofields[$cfield])) {
                $ctext = $ofields[$cfield];
                $displaymsg = uiTextSnippet($ctext);
              } elseif (substr($cfield, 0, 3) == 'ce_') {
                $eventtypeID = substr($cfield, 6);
                $subtype = substr($cfield, 3, 2);
                $stdisplay = $subtypes[$subtype];
                $cerow = $cetypes[$eventtypeID];
                $displaymsg = getEventDisplay($cerow['display']) . ': ' . $stdisplay;
              } else {
                $position = array_search($cfield, $symbols);
                if ($position !== false) {
                  $cfield = $mnemonics[$position];
                  $displaymsg = $criteriafields[$i];
                } else {
                  $position = array_search($cfield, $mnemonics);
                  if ($position !== false) {
                    $displaymsg = $symbols[$position];
                  } else {
                    $displaymsg = $criteriafields[$i];
                  }
                }
              }
              echo "<option value=\"$cfield\">$displaymsg</option>\n";
            }
            ?>
          </select>
        </div>
        <div class='col-md-1'>
          <a href="javascript:Move(document.form1.finalcriteria,1);"><img src="img/tng_up.gif" alt="<?php echo uiTextSnippet('moveup'); ?>" width="17" height="15"></a><br>
          <a href="javascript:Move(document.form1.finalcriteria,0);"><img src="img/tng_down.gif" alt="<?php echo uiTextSnippet('movedown'); ?>" width="17" height="15"></a>
        </div>

      </div>
      <span>*<?php echo uiTextSnippet('foremptystring'); ?></span>
      <h4><?php echo uiTextSnippet('choosesort'); ?>:</h4>
      <div class='row'>
        <div class='col-md-4'>
          <select class="form-control" name="availsort" size='4' style="overflow-y: auto;" onDblClick="AddtoDisplay(document.form1.availsort, document.form1.finalsort);">
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
        </div>
        <div class='col-md-1'>
          <a href="javascript:AddtoDisplay(document.form1.availsort,document.form1.finalsort);"><img src="img/tng_right.gif" alt="<?php echo uiTextSnippet('add'); ?>" width="17" height="15"></a>
        </div>
        <div class='col-md-1'>
          <a href="javascript:RemovefromDisplay(document.form1.finalsort);"><img src="img/tng_left.gif" alt="<?php echo uiTextSnippet('remove'); ?>" width="17" height="15"></a>
        </div>
        <div class='col-md-4'>
          <select class="form-control" name="finalsort" size='4' style="overflow-y: auto;" onDblClick="RemovefromDisplay(document.form1.finalsort);">
            <?php
            $orderbyfields = explode($lineending, $row['orderby']);
            for ($i = 0; $i < count($orderbyfields) - 1; $i++) {
              $sfield = $orderbyfields[$i];
              if (isset($cfields[$sfield])) {
                $stext = $cfields[$sfield];
                $displaymsg = uiTextSnippet($stext);
              } elseif (substr($sfield, 0, 3) == 'ce_') {
                $eventtypeID = substr($sfield, 6);
                $subtype = substr($sfield, 3, 2);
                $stdisplay = $subtypes[$subtype];
                $cerow = $cetypes[$eventtypeID];
                $displaymsg = getEventDisplay($cerow['display']) . ': ' . $stdisplay;
              }
              echo "<option value=\"$sfield\">$displaymsg</option>\n";
            }
            ?>
          </select>
        </div>
        <div class='col-md-2'>
          <a href="javascript:Move(document.form1.finalsort,1);"><img src="img/tng_up.gif" alt="<?php echo uiTextSnippet('moveup'); ?>" width="17" height="15"></a><br>
          <a href="javascript:Move(document.form1.finalsort,0);"><img src="img/tng_down.gif" alt="<?php echo uiTextSnippet('movedown'); ?>" width="17" height="15"></a>
        </div>
      </div>
      <hr>
      <div class='row'>
        <div class='col-md-12'>
          <strong><?php echo uiTextSnippet('altreport'); ?>:</strong><br>
        </div>
      </div>
      <div class='row'>
        <div class='col-md-12'>
          <textarea class='form-control' name="sqlselect" rows='4'><?php echo $row['sqlselect']; ?></textarea>
        </div>
      </div>
      <br>
      <div class='row'>
        <div class='col-md-2'>
          <button class='btn btn-outline-secondary' name='submit' type='submit'><?php echo uiTextSnippet('savereport'); ?></button>
        </div>
        <div class='col-md-2'>
          <button class='btn btn-outline-primary' name='submitx' type='submit'><?php echo uiTextSnippet('saveexit'); ?></button>
        </div>
      </div>
      <input name='display' type='hidden' value=''>
      <input name='criteria' type='hidden' value=''>
      <input name='orderby' type='hidden' value=''>
      <input name='reportID' type='hidden' value="<?php echo $reportID; ?>">
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