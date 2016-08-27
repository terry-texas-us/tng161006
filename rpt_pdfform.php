<?php
$tngprint = 1;
require 'tng_begin.php';
require $subroot . 'pedconfig.php';

if ($pdftype == "ped") {
  $dest = "rpt_pedigree";
  $genmax = !$pedigree['maxgen'] || $pedigree['maxgen'] > 6 ? 6 : $pedigree['maxgen'];
  $genmin = 2;
  $allow_blank = 1;
  $allow_cite = 0;
  $hdrFontSizes = [9, 10, 12, 14];
  $hdrFontDefault = 12;
  $rptFontSizes = [8];
  $titleidx = 'pedigreefor';
} elseif ($pdftype == "desc") {
  $dest = "rpt_descend";
  $genmin = 2;
  $genmax = !$pedigree['maxdesc'] || $pedigree['maxdesc'] > 12 ? 12 : $pedigree['maxdesc'];
  $allow_blank = 0;
  $allow_cite = 0;
  $hdrFontSizes = [9, 10, 12, 14];
  $hdrFontDefault = 12;
  $rptFontSizes = [9, 10, 12, 14];
  $rptFontDefault = 10;
  $titleidx = 'descendfor';
} elseif ($pdftype == "fam") {
  $dest = "rpt_fam";
  $genmin = 0;
  $genmax = 0;
  $allow_blank = 1;
  $allow_cite = 1;
  $hdrFontSizes = [9, 10, 12, 14];
  $hdrFontDefault = 12;
  $lblFontSizes = [10];
  $rptFontSizes = [9, 10, 12, 14];
  $rptFontDefault = 10;
  $titleidx = 'familygroupfor';
} else {
  $dest = "rpt_ind";
  $genmin = 0;         // no generations option
  $genmax = 0;
  $allow_blank = 1;
  $allow_cite = 1;
  $hdrFontSizes = [9, 10, 12, 14];
  $hdrFontDefault = 12;
  $lblFontSizes = [9];
  $rptFontSizes = [9, 10, 12, 14];
  $rptFontDefault = 10;
  $titleidx = 'indreportfor';
}

function doGenOptions($generations, $first, $last) {
  echo '<select name="genperpage">';
  for ($i = $first; $i <= $last; $i++) {
    echo "<option value=\"$i\"";
    if ($i == $generations) {
      echo " selected";
    }
    echo ">$i</option>\n";
  }
  echo '</select>';
}

function doFontOptions($field, $default = 'helvetica') {
  global $font_list;

  echo "<select name=\"$field\">";
  $fonts = array_keys($font_list);
  sort($fonts);
  foreach ($fonts as $font) {
    echo "<option value=\"$font\"";
    if ($font == $default) {
      print " selected";
    }
    echo ">$font_list[$font]</option>";
  }
  echo '</select>';
}

function doFontSizeOptions($field, $options, $default) {
  if (count($options) == 1) {
    echo "<span>$options[0] pt</span>";
    echo "<input name=\"$field\" type='hidden' value=\"$options[0]\" />";
  } else {
    echo "<select name=\"$field\">";
    foreach ($options as $size) {
      echo "<option value=\"$size\"";
      if ($default == $size) {
        print " selected";
      }
      echo ">$size</option>";
    }
    echo '</select>';
  }
}

$savetype = $pdftype;
// load the list of available fonts
$font_dir = $rootpath . $endrootpath . 'font';
if (is_dir($font_dir)) {
  if ($dh = opendir($font_dir)) {
    while (($fontfamily = readdir($dh)) !== false) {
      if ($fontfamily == 'makefont') {
        continue;
      }
      $charset_dir = '';
      if ($session_charset == 'UTF-8') {
        $charset_dir = '/utf8';
      }
      if (is_dir("$font_dir/$fontfamily$charset_dir") && is_file("$font_dir/$fontfamily$charset_dir/$fontfamily.php")) {
        include "$font_dir/$fontfamily$charset_dir/$fontfamily.php";
        $font_list[$fontfamily] = $name;
      }
    }
  }
}
$pdftype = $savetype;

if ($pdftype == "fam") {
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
header("Content-type:text/html; charset=" . $session_charset);
?>
<div id='finddiv'>
  <?php beginFormElement($dest, "post", "pdfform", "pdfform"); ?>
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('pdfgen'); ?></h4>
      <p><span><?php echo uiTextSnippet($titleidx); ?> </span><?php echo $titletext; ?></p>
    </header>
    <div class='modal-body'>
      <?php
      if (count($font_list) == 0) {
        echo "ERROR: There are no fonts installed to support character set $session_charset.";
        return;
      }
      ?>
      <?php
      // determine if we need to draw a generations option
      if ($genmin > 0 || $genmax > 0) {
        if ($generations < $genmin) {
          $generations = $genmin;
        }
        if ($generations > $genmax) {
          $generations = $genmax;
        }
        ?>
        <table class='table table-sm' id='genselect'>
          <tr>
            <td>
              <span><?php echo uiTextSnippet('generations'); ?>:</span>
            </td>
            <td>
              <?php echo doGenOptions($generations, $genmin, $genmax); ?>
            </td>
          </tr>
          <?php
          if ($pdftype == "ped" || $pdftype == "desc") {
            ?>
            <tr>
              <td class="ws">
                <span><?php echo uiTextSnippet('startnum'); ?>:</span>
              </td>
              <td>
                <input name='startnum' type='text' value='1' size='4'/>
              </td>
            </tr>
            <?php
          }
          ?>
        </table>
        <?php
      }
      // draw the blank form checkbox
      if ($allow_blank) {
      ?>
        <div class="pdfblock">
          <input id='blankform' name='blankform' type='checkbox' value='1'> <?php echo uiTextSnippet('blank'); ?>
        </div>
        <?php
      }
      // draw the citations checkbox
      if ($allow_cite) {
      ?>
        <div class="pdfblock">
            <input id='citesources' name='citesources' type='checkbox' value='1' checked> <?php echo uiTextSnippet('inclsrcs'); ?>
        </div>
        <?php
      }
      if ($pdftype == "fam") {
        echo "<input name='familyID' type='hidden' value=\"$familyID\"/>\n";
      } else {
        echo "<input name='personID' type='hidden' value=\"$personID\"/>\n";
      }
      // options specific to certain report types
      if ($pdftype == "desc") {
        ?>
        <div class='pdfblock h4'>
          <a href="#" onClick="return toggleSection('dispopts', 'dispicon', '');" class="pdftoggle">
            <img src="img/tng_expand.gif" width="15" height="15" id="dispicon"> <?php echo uiTextSnippet('dispopts'); ?>
          </a>
        </div>
        <div style="display:none" id="dispopts">
          <table class='table table-sm' id='display'>
            <tr>
              <td>
                <span><?php echo uiTextSnippet('datesloc'); ?>:&nbsp;</span>
              </td>
              <td>
                <select name="getPlace">
                  <option value='1' selected><?php echo uiTextSnippet('borchr'); ?></option>
                  <option value="2"><?php echo uiTextSnippet('nobd'); ?></option>
                  <option value="3"><?php echo uiTextSnippet('bcdb'); ?></option>
                </select>
              </td>
            </tr>
            <td>
              <span><?php echo uiTextSnippet('numsys'); ?>:&nbsp;</span>
            </td>
            <td>
              <select name="numbering">
                <option value='0'><?php echo uiTextSnippet('none'); ?></option>
                <option value='1' selected><?php echo uiTextSnippet('gennums'); ?></option>
                <option value="2"><?php echo uiTextSnippet('henrynums'); ?></option>
                <option value="3"><?php echo uiTextSnippet('abovnums'); ?></option>
                <option value="4"><?php echo uiTextSnippet('devnums'); ?></option>
              </select>
            </td>
            <tr>
          </table>
          <br>
        </div>
        <?php
      }
      ?>
      <!-- Font section -->
      <div class='pdfblock h4'>
        <a href="#" onClick="return toggleSection('font', 'fonticon', '');" class="pdftoggle">
          <img src="img/tng_expand.gif" width="15" height="15" id="fonticon"> <?php echo uiTextSnippet('fonts'); ?>
        </a>
      </div>
      <div style="display:none" id="font">
        <table class='table table-sm'>
          <?php
          // header fonts
          if (count($hdrFontSizes) > 0) {
            ?>
            <tr>
              <td>
                <span><?php echo uiTextSnippet('header'); ?>:&nbsp;</span>
              </td>
              <td>
                <?php doFontOptions('hdrFont'); ?>
              </td>
              <td>
                <?php doFontSizeOptions('hdrFontSize', $hdrFontSizes, $hdrFontDefault); ?>
              </td>
            </tr>
            <?php
          }

          // label fonts
          if (count($lblFontSizes) > 0) {
            ?>
            <tr>
              <td>
                <span><?php echo uiTextSnippet('labels'); ?>:&nbsp;</span>
              </td>
              <td>
                <?php doFontOptions('lblFont'); ?>
              </td>
              <td>
                <?php doFontSizeOptions('lblFontSize', $lblFontSizes, $lblFontDefault); ?>
              </td>
            </tr>
            <?php
          }

          // data fonts
          if (count($rptFontSizes) > 0) {
            ?>
            <tr>
              <td>
                <span><?php echo uiTextSnippet('data'); ?>:&nbsp;</span>
              </td>
              <td>
                <?php doFontOptions('rptFont'); ?>
              </td>
              <td>
                <?php doFontSizeOptions('rptFontSize', $rptFontSizes, $rptFontDefault); ?>
              </td>
            </tr>
            <?php
          }
          $pagesize = $_COOKIE['tng_pagesize'] ? $_COOKIE['tng_pagesize'] : $pedigree['pagesize'];
          ?>
        </table>
        <br>
      </div>

      <!-- Page setup section -->
      <div class='pdfblock h4'>
        <a href="#" onClick="return toggleSection('pgsetup', 'pgicon', '');" class="pdftoggle">
          <img src="img/tng_expand.gif" width="15" height="15" id="pgicon"> <?php echo uiTextSnippet('pgsetup'); ?>
        </a>
      </div>
      <div style="display:none" id="pgsetup">
        <table class='table table-sm'>
          <tr>
            <td>
              <span><?php echo uiTextSnippet('pgsize'); ?>:&nbsp;</span>
            </td>
            <td>
              <select name="pagesize">
                <option value="a3"<?php if ($pagesize == "a3") {echo " selected";} ?>>A3</option>
                <option value="a4"<?php if ($pagesize == "a4") {echo " selected";} ?>>A4</option>
                <option value="a5"<?php if ($pagesize == "a5") {echo " selected";} ?>>A5</option>
                <option value="letter"<?php if (!$pagesize || $pagesize == "letter") {echo " selected";} ?>>
                  <?php echo uiTextSnippet('letter'); ?>
                </option>
                <option value="legal<?php if ($pagesize == "legal") {echo " selected";} ?>">
                  <?php echo uiTextSnippet('legal'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td>
              <span><?php echo uiTextSnippet('orient'); ?>:&nbsp;</span>
            </td>
            <td>
              <select name="orient">
                <option value=p selected><?php echo uiTextSnippet('portrait'); ?></option>
                <option value=l><?php echo uiTextSnippet('landscape'); ?></option>
              </select>
            </td>
          </tr>
          <tr>
            <td>
              <span><?php echo uiTextSnippet('tmargin'); ?>:&nbsp;</span>
            </td>
            <td>
              <input name='topmrg' type='text' value='0.5' size='5'/>
            </td>
          </tr>
          <tr>
            <td>
              <span><?php echo uiTextSnippet('bmargin'); ?>:&nbsp;</span>
            </td>
            <td>
              <input name='botmrg' type='text' value='0.5' size='5'/>
            </td>
          </tr>
          <tr>
            <td>
              <span><?php echo uiTextSnippet('lmargin'); ?>:&nbsp;</span>
            </td>
            <td>
              <input name='lftmrg' type='text' value='0.5' size='5'/>
            </td>
          </tr>
          <tr>
            <td>
              <span><?php echo uiTextSnippet('rmargin'); ?>:&nbsp;</span>
            </td>
            <td>
              <input name='rtmrg' type='text' value='0.5' size='5'/>
            </td>
          </tr>
        </table>
      </div>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <input type='submit' onclick="this.form.target = '_blank'" value="<?php echo uiTextSnippet('createch'); ?>">
    </footer>
  <?php endFormElement(); ?>
</div>