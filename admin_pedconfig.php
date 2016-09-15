<?php
require 'begin.php';
require $subroot . 'pedconfig.php';
require 'adminlib.php';

if ($link) {
  $adminLogin = 1;
  include 'checklogin.php';
  include 'version.php';

  if (!$allowEdit) {
    $message = uiTextSnippet('norights');
    header('Location: admin_login.php?message=' . urlencode($message));
    exit;
  }
}
header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('modifypedsettings'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<style>input[type='color'] {width: 100%;}</style>

<body id="setup-configuration-pedconfigsettings">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('setup-configuration-pedconfigsettings', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'admin_setup.php', uiTextSnippet('configuration'), 'configuration']);
    $navList->appendItem([true, 'admin_diagnostics.php', uiTextSnippet('diagnostics'), 'diagnostics']);
    $navList->appendItem([true, 'admin_setup.php?sub=tablecreation', uiTextSnippet('tablecreation'), 'tablecreation']);
    $navList->appendItem([true, '#', uiTextSnippet('pedconfigsettings'), 'ped']);
    echo $navList->build('ped');
    ?>
    <div class='small'>
      <a href='#' onClick="toggleAll('on');"><?php echo uiTextSnippet('expandall'); ?></a>
      <a href='#' onClick="toggleAll('off');"><?php echo uiTextSnippet('collapseall'); ?></a>
    </div>
    <form action="admin_updatepedconfig.php" method='post' name='form1'>
      <table class='table table-sm'>
        <tr>
          <td>
            <?php echo displayToggle('plus0', 0, 'ped', uiTextSnippet('pedchart'), ''); ?>

            <div id="ped" style="display:none">
              <table>
                <tr>
                  <td colspan='2'>&nbsp;</td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('usepopups'); ?>:</td>
                  <td>
                    <select name="usepopups">
                      <option value='1'<?php if ($pedigree['usepopups'] == 1) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('pedstandard'); ?>
                      </option>
                      <option value='0'<?php if (!$pedigree['usepopups']) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('pedbox'); ?>
                      </option>
                      <option value="-1"<?php if ($pedigree['usepopups'] == -1) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('pedtextonly'); ?>
                      </option>
                      <option value='2'<?php if ($pedigree['usepopups'] == 2) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('pedcompact'); ?>
                      </option>
                      <option value='3'<?php if ($pedigree['usepopups'] == 3) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('ahnentafel'); ?></option>
                      <option value='4'<?php if ($pedigree['usepopups'] == 4) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('pedvertical'); ?>
                      </option>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('maxpedgens'); ?>:</td>
                  <td><input name='maxgen' type='text' value="<?php echo $pedigree['maxgen']; ?>" size='5'></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('initgens'); ?>:</td>
                  <td colspan='4'><input type='text' value="<?php echo $pedigree['initpedgens']; ?>"
                                         name="initpedgens" size='5'></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('popupspouses'); ?>:</td>
                  <td>
                    <input name='popupspouses' type='radio' value='1' <?php if ($pedigree['popupspouses']) {echo 'checked';} ?>> <?php echo uiTextSnippet('yes'); ?> 
                    <input name='popupspouses' type='radio' value='0' <?php if (!$pedigree['popupspouses']) {echo 'checked';} ?>> <?php echo uiTextSnippet('no'); ?>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('popupkids'); ?>:</td>
                  <td>
                    <input name='popupkids' type='radio' value='1' <?php if ($pedigree['popupkids']) {echo 'checked';} ?>> <?php echo uiTextSnippet('yes'); ?> 
                    <input name='popupkids' type='radio' value='0' <?php if (!$pedigree['popupkids']) {echo 'checked';} ?>> <?php echo uiTextSnippet('no'); ?>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('popupchartlinks'); ?>:</td>
                  <td>
                    <input name='popupchartlinks' type='radio' value='1' <?php if ($pedigree['popupchartlinks']) {echo 'checked';} ?>> <?php echo uiTextSnippet('yes'); ?> 
                    <input name='popupchartlinks' type='radio' value='0' <?php if (!$pedigree['popupchartlinks']) {echo 'checked';} ?>> <?php echo uiTextSnippet('no'); ?>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('hideempty'); ?>:</td>
                  <td>
                    <input name='hideempty' type='radio' value='1' <?php if ($pedigree['hideempty']) {echo 'checked';} ?>> <?php echo uiTextSnippet('yes'); ?>
                    <input name='hideempty' type='radio' value='0' <?php if (!$pedigree['hideempty']) {echo 'checked';} ?>> <?php echo uiTextSnippet('no'); ?>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('boxwidth'); ?>:</td>
                  <td>
                    <input name='boxwidth' type='text' value="<?php echo $pedigree['boxwidth']; ?>" size='10'>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('boxheight'); ?>:</td>
                  <td><input name='boxheight' type='text' value="<?php echo $pedigree['boxheight']; ?>" size='10'></td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <?php echo displayToggle('plus1', 0, 'desc', uiTextSnippet('descchart'), ''); ?>

            <div id="desc" style="display:none">
              <table>
                <tr>
                  <td colspan='2'>&nbsp;</td>
                </tr>
                <tr>
                  <td><span><?php echo uiTextSnippet('usepopups'); ?>:</span></td>
                  <td>
                    <select name="defdesc">
                      <option value='2'<?php if ($pedigree['defdesc'] == 2) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('stdformat'); ?>
                      </option>
                      <option value='0'<?php if (!$pedigree['defdesc']) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('pedtextonly'); ?>
                      </option>
                      <option value='3'<?php if ($pedigree['defdesc'] == 3) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('pedcompact'); ?>
                      </option>
                      <option value='1'<?php if ($pedigree['defdesc'] == 1) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('regformat'); ?>
                      </option>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td><span><?php echo uiTextSnippet('maxpedgens'); ?>:</span></td>
                  <td colspan='4'><input type='text' value="<?php echo $pedigree['maxdesc']; ?>"
                                         name="maxdesc" size='5'></td>
                </tr>
                <tr>
                  <td><span><?php echo uiTextSnippet('initgens'); ?>:</span></td>
                  <td colspan='4'><input type='text' value="<?php echo $pedigree['initdescgens']; ?>"
                                         name="initdescgens" size='5'></td>
                </tr>
                <tr>
                  <td><span><?php echo uiTextSnippet('stdesc'); ?>:</span></td>
                  <td>
                    <select name="stdesc">
                      <option value='0'<?php if (!$pedigree['stdesc']) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('stexpand'); ?>
                      </option>
                      <option value='1'<?php if ($pedigree['stdesc'] == 1) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('stcollapse'); ?>
                      </option>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td><span><?php echo uiTextSnippet('regnotes'); ?>:</span></td>
                  <td>
                    <select name="regnotes">
                      <option value='0'<?php if (!$pedigree['regnotes']) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('no'); ?>
                      </option>
                      <option value='1'<?php if ($pedigree['regnotes']) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('yes'); ?>
                      </option>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td><span><?php echo uiTextSnippet('regnosp'); ?>:</span></td>
                  <td>
                    <select name="regnosp">
                      <option value='0'<?php if (!$pedigree['regnosp']) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('chshow'); ?>
                      </option>
                      <option value='1'<?php if ($pedigree['regnosp']) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('chifsp'); ?>
                      </option>
                    </select>
                  </td>
                </tr>
              </table>
            </div>

          </td>
        </tr>

        <tr>
          <td>
            <?php echo displayToggle('plus2', 0, 'rel', uiTextSnippet('relchart'), ''); ?>

            <div id="rel" style="display:none">
              <table>
                <tr>
                  <td colspan='2'>&nbsp;</td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('initrels'); ?>:</td>
                  <td><input name='initrels' type='text' value="<?php echo $pedigree['initrels']; ?>" size='5'></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('maxrels'); ?>:</td>
                  <td><input name='maxrels' type='text' value="<?php echo $pedigree['maxrels']; ?>" size='5'></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('maxpedgens'); ?>:</td>
                  <td><input name='maxupgen' type='text' value="<?php echo $pedigree['maxupgen']; ?>" size='5'></td>
                </tr>
              </table>
            </div>

          </td>
        </tr>

        <tr>
          <td>
            <?php echo displayToggle('plus3', 0, 'time', uiTextSnippet('timechart'), ''); ?>

            <div id="time" style="display:none">
              <table>
                <tr>
                  <td colspan='2'>&nbsp;</td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('tcwidth'); ?>:</td>
                  <td>
                    <input name='tcwidth' type='text' value="<?php echo $pedigree['tcwidth']; ?>" size='5'>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('simile'); ?>:</td>
                  <td>
                    <select name="simile"
                            onchange="new Effect.toggle('simileTable', 'appear', {duration: .2});">
                      <option value='0'<?php if (!$pedigree['simile']) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('no'); ?>
                      </option>
                      <option value='1'<?php if ($pedigree['simile']) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('yes'); ?>
                      </option>
                    </select>
                  </td>
                </tr>
              </table>
              <table<?php if (!$pedigree['simile']) {echo " style='display: none'";} ?> id="simileTable">
                <tr>
                  <td><?php echo uiTextSnippet('tcheight'); ?>:</td>
                  <td><input name='tcheight' type='text' value="<?php echo $pedigree['tcheight']; ?>" size='5'></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('ypct'); ?>:</td>
                  <td><input name='ypct' type='text' value="<?php echo $pedigree['ypct']; ?>" size='5'>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('ypixels'); ?>:</td>
                  <td><input name='ypixels' type='text' value="<?php echo $pedigree['ypixels']; ?>" size='5'></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('ymult'); ?>:</td>
                  <td><input name='ymult' type='text' value="<?php echo $pedigree['ymult']; ?>" size='5'>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('mpct'); ?>:</td>
                  <td><input name='mpct' type='text' value="<?php echo $pedigree['mpct']; ?>" size='5'>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('mpixels'); ?>:</td>
                  <td><input name='mpixels' type='text' value="<?php echo $pedigree['mpixels']; ?>" size='5'></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('inclevs'); ?>:</td>
                  <td>
                    <select name="tcevents">
                      <option value='0'<?php if (!$pedigree['tcevents']) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('allevs'); ?>
                      </option>
                      <option value='1'<?php if ($pedigree['tcevents']) {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('rangeevs'); ?>
                      </option>
                    </select>
                  </td>
                </tr>
              </table>
            </div>

          </td>
        </tr>

        <tr>
          <td>
            <?php echo displayToggle('plus4', 0, 'peddesc', uiTextSnippet('pedanddesc'), ''); ?>

            <div id="peddesc" style="display:none">
              <table>
                <tr>
                  <td colspan='3'>&nbsp;</td>
                </tr>
                <tr>
                  <td>
                    <table class='table table-sm'>
                      <tr>
                        <td><?php echo uiTextSnippet('leftindent'); ?>:</td>
                        <td><input type='text' value="<?php echo $pedigree['leftindent']; ?>"
                                   name="leftindent" size='10'></td>
                      </tr>
                      <tr>
                        <td><?php echo uiTextSnippet('boxnamesize'); ?>:</td>
                        <td><input type='text' value="<?php echo $pedigree['boxnamesize']; ?>"
                                   name="boxnamesize" size='10'></td>
                      </tr>
                      <tr>
                        <td><?php echo uiTextSnippet('boxdatessize'); ?>:</td>
                        <td><input type='text' value="<?php echo $pedigree['boxdatessize']; ?>"
                                   name="boxdatessize" size='10'></td>
                      </tr>
                      <tr>
                        <td><?php echo uiTextSnippet('boxcolor'); ?>:</td>
                        <td>
                            <input id='boxcolor' name='boxcolor' type='color' <?php echo "value='" . $pedigree['boxcolor'] . "'"; ?>>
                        </td>
                      </tr>
                      <tr>
                        <td><?php echo uiTextSnippet('emptycolor'); ?>:</td>
                        <td>
                            <input id='emptycolor' name='emptycolor' type='color' <?php echo "value='" . $pedigree['emptycolor'] . "'"; ?>>
                        </td>
                      </tr>
                      <tr>
                        <td><?php echo uiTextSnippet('bordercolor'); ?>:</td>
                        <td>
                            <input id='bordercolor' name='bordercolor' type='color' <?php echo "value='" . $pedigree['bordercolor'] . "'"; ?>>
                        </td>
                      </tr>
                      <tr>
                        <td><?php echo uiTextSnippet('defpgsize'); ?>:</td>
                        <td>
                          <select name="pagesize">
                            <option value="a3"<?php if ($pedigree['pagesize'] == 'a3') {echo ' selected';} ?>>A3</option>
                            <option value="a4"<?php if ($pedigree['pagesize'] == 'a4') {echo ' selected';} ?>>A4</option>
                            <option value="a5"<?php if ($pedigree['pagesize'] == 'a5') {echo ' selected';} ?>>A5</option>
                            <option value="letter"<?php if (!$pedigree['pagesize'] || $pedigree['pagesize'] == 'letter') {echo ' selected';} ?>>
                              <?php echo uiTextSnippet('letter'); ?>
                            </option>
                            <option value="legal"<?php if ($pedigree['pagesize'] == 'legal') {echo ' selected';} ?>>
                              <?php echo uiTextSnippet('legal'); ?>
                            </option>
                          </select>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <td width="20">&nbsp;</td>
                  <td>
                    <table class='table table-sm'>
                      <tr>
                        <td><span><?php echo uiTextSnippet('linewidth'); ?>:</span></td>
                        <td>
                          <input name='linewidth' type='text' value="<?php echo $pedigree['linewidth']; ?>" size='10'>
                        </td>
                      </tr>
                      <tr>
                        <td><span><?php echo uiTextSnippet('borderwidth'); ?>:</span></td>
                        <td>
                          <input name='borderwidth' type='text' value="<?php echo $pedigree['borderwidth']; ?>" size='10'>
                        </td>
                      </tr>
                      <tr>
                        <td><span><?php echo uiTextSnippet('popupcolor'); ?>:</span></td>
                        <td>
                            <input id='popupcolor' name='popupcolor' type='color' <?php echo "value='" . $pedigree['popupcolor'] . "'"; ?>>
                        </td>
                      </tr>
                      <tr>
                        <td><span><?php echo uiTextSnippet('popupinfosize'); ?>:</span></td>
                        <td>
                          <input name='popupinfosize' type='text' value="<?php echo $pedigree['popupinfosize']; ?>" size='10'>
                        </td>
                      </tr>
                      <tr>
                        <td><span><?php echo uiTextSnippet('popuptimer'); ?>:</span></td>
                        <td><input type='text' value="<?php echo $pedigree['popuptimer']; ?>"
                                   name="popuptimer" size='10'></td>
                      </tr>
                      <tr>
                        <td><span><?php echo uiTextSnippet('pedevent'); ?>:</span></td>
                        <td>
                          <select name="pedevent">
                            <option value="down"<?php if ($pedigree['event'] == 'down') {echo ' selected';} ?>>
                              <?php echo uiTextSnippet('mousedown'); ?>
                            </option>
                            <option value="over"<?php if ($pedigree['event'] == 'over') {echo ' selected';} ?>>
                              <?php echo uiTextSnippet('mouseover'); ?>
                            </option>
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td><span><?php echo uiTextSnippet('puboxwidth'); ?>:</span></td>
                        <td>
                          <input name='puboxwidth' type='text' value="<?php echo $pedigree['puboxwidth']; ?>" size='10'>
                        </td>
                      </tr>
                      <tr>
                        <td><span><?php echo uiTextSnippet('puboxheight'); ?>:</span></td>
                        <td><input type='text' value="<?php echo $pedigree['puboxheight']; ?>"
                                   name="puboxheight" size='10'></td>
                      </tr>
                      <tr>
                        <td><span><?php echo uiTextSnippet('puboxheightshift'); ?>:</span>
                        </td>
                        <td><input type='text' value="<?php echo $pedigree['puboxheightshift']; ?>"
                                   name="puboxheightshift" size='10'></td>
                      </tr>
                      <tr>
                        <td>
                          <?php echo uiTextSnippet('inclphotos'); ?>:
                        </td>
                        <td>
                          <input name='inclphotos' type='radio' value='1' <?php if ($pedigree['popupchartlinks']) {echo 'checked';} ?>> <?php echo uiTextSnippet('yes'); ?>
                          <input name='inclphotos' type='radio' value='0' <?php if (!$pedigree['inclphotos']) {echo 'checked';} ?>> <?php echo uiTextSnippet('no'); ?>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
          </td>
        </tr>
      </table>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script>
    function toggleAll(display) {
      toggleSection('ped', 'plus0', display);
      toggleSection('desc', 'plus1', display);
      toggleSection('rel', 'plus2', display);
      toggleSection('time', 'plus3', display);
      toggleSection('peddesc', 'plus4', display);
      return false;
    }
  </script>
  <script src="js/admin.js"></script>
</body>
</html>
