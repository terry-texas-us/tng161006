<?php
/**
 * naming history: rpt_pdfform.php
 */

$tngprint = 1;
require 'tng_begin.php';
require $subroot . 'pedconfig.php';

if ($type === 'ped') {
  $dest = 'rpt_pedigree';
  $genmax = !$pedigree['maxgen'] || $pedigree['maxgen'] > 6 ? 6 : $pedigree['maxgen'];
  $genmin = 3;
  $allowBlank = 1;
  $allowCite = 0;
  $hdrFontSizes = [9, 10, 12, 14];
  $hdrFontDefault = 12;
  $rptFontSizes = [8];
  $titleidx = 'pedigreefor';
} elseif ($type === 'desc') {
  $dest = 'rpt_descend';
  $genmin = 3;
  $genmax = !$pedigree['maxdesc'] || $pedigree['maxdesc'] > 12 ? 12 : $pedigree['maxdesc'];
  $allowBlank = 0;
  $allowCite = 0;
  $hdrFontSizes = [9, 10, 12, 14];
  $hdrFontDefault = 12;
  $rptFontSizes = [9, 10, 12, 14];
  $rptFontDefault = 10;
  $titleidx = 'descendfor';
} elseif ($type === 'fam') {
  $dest = 'rpt_fam';
  $genmin = 0;
  $genmax = 0;
  $allowBlank = 1;
  $allowCite = 1;
  $hdrFontSizes = [9, 10, 12, 14];
  $hdrFontDefault = 12;
  $lblFontSizes = [10];
  $rptFontSizes = [9, 10, 12, 14];
  $rptFontDefault = 10;
  $titleidx = 'familygroupfor';
} else {
  $dest = 'rpt_ind';
  $genmin = 0;
  $genmax = 0;
  $allowBlank = 1;
  $allowCite = 1;
  $hdrFontSizes = [9, 10, 12, 14];
  $hdrFontDefault = 12;
  $lblFontSizes = [9];
  $rptFontSizes = [9, 10, 12, 14];
  $rptFontDefault = 10;
  $titleidx = 'indreportfor';
}

function doGenOptions($generations, $first, $last) {
  echo "<select class='custom-select btn-block' name='genperpage'>";
  for ($i = $first; $i <= $last; $i++) {
    echo "<option value='$i'";
    if ($i === $generations) {
      echo ' selected';
    }
    echo ">$i</option>\n";
  }
  echo '</select>';
}

function doFontOptions($field, $default = 'helvetica') {
  global $font_list;

  echo "<select class='custom-select btn-block' name='$field'>";
  $fonts = array_keys($font_list);
  sort($fonts);
  foreach ($fonts as $font) {
    echo "<option value='$font'";
    if ($font == $default) {
      echo ' selected';
    }
    echo ">$font_list[$font]</option>";
  }
  echo '</select>';
}

function doFontSizeOptions($field, $options, $default) {
  if (count($options) == 1) {
    echo "<p class='form-control-static'>$options[0] pt</p>";
    echo "<input name='$field' type='hidden' value='$options[0]'>";
  } else {
    echo "<select class='custom-select btn-block' name='$field'>";
    foreach ($options as $size) {
      echo "<option value='$size'";
      if ($default == $size) {
        echo ' selected';
      }
      echo ">$size</option>";
    }
    echo '</select>';
  }
}

$savetype = $type;
// Load the list of available fonts.
$font_dir = $rootpath . $endrootpath . 'font';
if (is_dir($font_dir)) {
  if ($dh = opendir($font_dir)) {
    while (($fontfamily = readdir($dh)) !== false) {
      if ($fontfamily == 'makefont') {
        continue;
      }
      $charset_dir = '';
      if ($sessionCharset == 'UTF-8') {
        $charset_dir = '/utf8';
      }
      if (is_dir("$font_dir/$fontfamily$charset_dir") && is_file("$font_dir/$fontfamily$charset_dir/$fontfamily.php")) {
        include "$font_dir/$fontfamily$charset_dir/$fontfamily.php";
        $font_list[$fontfamily] = $name;
      }
    }
  }
}
$type = $savetype;

if ($type === 'fam') {
  $result = getFamilyData($familyID);
  $famrow = tng_fetch_assoc($result);
  $titletext = getFamilyName($famrow);
} else {
  $result = getPersonSimple($personID);
  if ($result) {
    $row = tng_fetch_assoc($result);

    $rights = determineLivingPrivateRights($row);
    $row['allow_living'] = $rights['living'];
    $row['allow_private'] = $rights['private'];

    $pedname = getName($row);
    tng_free_result($result);
    $titletext = "$pedname ($personID)";
  }
}
header('Content-type:text/html; charset=' . $sessionCharset);
?>
<div id='finddiv'>
  <?php beginFormElement($dest, 'post', 'pdfform', 'pdfform'); ?>
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('pdfgen'); ?></h4>
      <p><?php echo uiTextSnippet($titleidx); ?><?php echo $titletext; ?></p>
    </header>
    <div class='modal-body'>
      <?php
      if (count($font_list) === 0) {
        echo "ERROR: There are no fonts installed to support character set $sessionCharset.";
        return;
      }
      ?>
      <?php
      // Determine if we need to draw a generations option.
      if ($genmin > 0 || $genmax > 0) {
        if ($generations < $genmin) {
          $generations = $genmin;
        }
        if ($generations > $genmax) {
          $generations = $genmax;
        }
        ?>
      <div class='row'>
        <div class='col-sm-3'>
          <label class='form-control-label'><?php echo uiTextSnippet('generations'); ?>:</label>
        </div>
        <div class='col-sm-2'>
          <?php echo doGenOptions($generations, $genmin, $genmax); ?>
        </div>
        <?php
        if ($type === 'ped' || $type === 'desc') {
        ?>
          <div class='col-sm-3'>
            <label class='form-control-label'><?php echo uiTextSnippet('startnum'); ?>:</label>
          </div>
          <div class='col-sm-2'>
            <input class='form-control' name='startnum' type='text' value='1' size='4'/>
          </div>
        <?php
        }
        ?>
      </div>
      <?php
      }
      if ($allowBlank === 1) {
        echo "<label class='form-check-inline'>";
        echo "<input class=form-check-input' id='blankform' name='blankform' type='checkbox' value='1'> " . uiTextSnippet('blank');
        echo '</label>';
      }
      if ($allowCite === 1) {
        echo "<label class='form-check-inline'>";
        echo "<input class=form-check-input' id='citesources' name='citesources' type='checkbox' value='1' checked> " . uiTextSnippet('inclsrcs');
        echo '</label>';
      }
      if ($type === 'fam') {
        echo "<input name='familyID' type='hidden' value=\"$familyID\"/>\n";
      } else {
        echo "<input name='personID' type='hidden' value=\"$personID\"/>\n";
      }
      // Options specific to certain report types.
      if ($type === 'desc') {
      ?>
        <hr>
        <div id='dispopts'>
          <div class='row'>
            <div class='col-sm-3'>
              <label class='form-control-label'><?php echo uiTextSnippet('datesloc'); ?>:&nbsp;</label>
            </div>
            <div class='col-sm-6'>
              <select class='custom-select btn-block' name="getPlace">
                <option value='1' selected><?php echo uiTextSnippet('borchr'); ?></option>
                <option value='2'><?php echo uiTextSnippet('nobd'); ?></option>
                <option value='3'><?php echo uiTextSnippet('bcdb'); ?></option>
              </select>
            </div>
          </div>
          <div class='row'>
            <div class='col-sm-3'>
              <label class='form-control-label'><?php echo uiTextSnippet('numsys'); ?>:&nbsp;</label>
            </div>
            <div class='col-sm-6'>
              <select class='custom-select btn-block' name="numbering">
                <option value='0'><?php echo uiTextSnippet('none'); ?></option>
                <option value='1' selected><?php echo uiTextSnippet('gennums'); ?></option>
                <option value='2'><?php echo uiTextSnippet('henrynums'); ?></option>
                <option value='3'><?php echo uiTextSnippet('abovnums'); ?></option>
                <option value='4'><?php echo uiTextSnippet('devnums'); ?></option>
              </select>
            </div>
          </div>
        </div>
      <?php
      }
      ?>
      <hr>
      <div id='font'>
        <div class='row'>
          <?php if (count($hdrFontSizes) > 0) { ?>
            <div class='col-sm-3'>
              <label class='form-control-label'><?php echo uiTextSnippet('headerfont'); ?>:&nbsp;</label>
            </div>
            <div class='col-sm-4'>
              <?php doFontOptions('hdrFont'); ?>
            </div>
            <div class='offset-sm-1 col-sm-2'>
              <?php doFontSizeOptions('hdrFontSize', $hdrFontSizes, $hdrFontDefault); ?>
            </div>
          <?php } ?>
        </div>
        <div class='row'>
          <?php if (count($lblFontSizes) > 0) { ?>
            <div class='col-sm-3'>
              <label class='form-control-label'><?php echo uiTextSnippet('labelfont'); ?>:&nbsp;</label>
            </div>
            <div class='col-sm-4'>
              <?php doFontOptions('lblFont'); ?>
            </div>
            <div class='offset-sm-1 col-sm-2'>
              <?php doFontSizeOptions('lblFontSize', $lblFontSizes, $lblFontDefault); ?>
            </div>
          <?php } ?>
        </div>
        <div class='row'>
          <?php if (count($rptFontSizes) > 0) { ?>
            <div class='col-sm-3'>
              <label class='form-control-label'><?php echo uiTextSnippet('datafont'); ?>:&nbsp;</label>
            </div>
            <div class='col-sm-4'>
              <?php doFontOptions('rptFont'); ?>
            </div>
            <div class='offset-sm-1 col-sm-2'>
              <?php doFontSizeOptions('rptFontSize', $rptFontSizes, $rptFontDefault); ?>
            </div>
          <?php } ?>
        </div>
        <?php
        $pagesize = $_COOKIE['tng_pagesize'] ? $_COOKIE['tng_pagesize'] : $pedigree['pagesize'];
        ?>
      </div>
      <hr>
      <div id='pgsetup'>
        <div class='row'>
          <div class='col-sm-3'>
            <label class='form-control-label'><?php echo uiTextSnippet('pgsize'); ?>:&nbsp;</label>
          </div>
          <div class='col-sm-4'>
            <select class='custom-select btn-block' name='pagesize'>
              <option value='a3'<?php if ($pagesize === 'a3') {echo ' selected';} ?>>A3</option>
              <option value='a4'<?php if ($pagesize === 'a4') {echo ' selected';} ?>>A4</option>
              <option value='a5'<?php if ($pagesize === 'a5') {echo ' selected';} ?>>A5</option>
              <option value='letter'<?php if (!$pagesize || $pagesize === 'letter') {echo ' selected';} ?>><?php echo uiTextSnippet('letter'); ?></option>
              <option value='legal'<?php if ($pagesize === 'legal') {echo ' selected';} ?>><?php echo uiTextSnippet('legal'); ?></option>
            </select>
          </div>
        </div>
        <div class='row'>
          <div class='col-sm-3'>
            <label class='form-control-label'><?php echo uiTextSnippet('orient'); ?>:&nbsp;</label>
          </div>
          <div class='col-sm-4'>
            <select class='custom-select btn-block' name='orient'>
              <option value=p selected><?php echo uiTextSnippet('portrait'); ?></option>
              <option value=l><?php echo uiTextSnippet('landscape'); ?></option>
            </select>
          </div>
        </div>
        <hr>
        <div class='row'>
          <div class='col-sm-3'>
            <label class='form-control-label'><?php echo uiTextSnippet('tmargin'); ?>:&nbsp;</label>
          </div>
          <div class='col-sm-2'>
            <input class='form-control' name='topmrg' type='text' value='0.5' size='5'>
          </div>
          <div class='col-sm-3'>
            <label class='form-control-label'><?php echo uiTextSnippet('bmargin'); ?>:&nbsp;</label>
          </div>
          <div class='col-sm-2'>
            <input class='form-control' name='botmrg' type='text' value='0.5' size='5'>
          </div>
        </div>
        <div class='row'>
          <div class='col-sm-3'>
            <label class='form-control-label'><?php echo uiTextSnippet('lmargin'); ?>:&nbsp;</label>
          </div>
          <div class='col-sm-2'>
            <input class='form-control' name='lftmrg' type='text' value='0.5' size='5'>
          </div>
          <div class='col-sm-3'>
            <label class='form-control-label'><?php echo uiTextSnippet('rmargin'); ?>:&nbsp;</label>
          </div>
          <div class='col-sm-2'>
            <input class='form-control' name='rtmrg' type='text' value='0.5' size='5'>
          </div>
        </div>
      </div>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <button class='btn btn-outline-primary' type='submit' onclick="this.form.target = '_blank'"><?php echo uiTextSnippet('createch'); ?></button>
    </footer>
  <?php endFormElement(); ?>
</div>