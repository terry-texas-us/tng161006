<?php
require 'begin.php';
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
if (!$rootpath) {
  $rootpath = dirname(__FILE__);
  $rootpath .= '/';
  if (preg_match('/WIN/i', PHP_OS)) {
    $rootpath = str_replace('\\', '/', $rootpath);
  }
}
if (!$lineendingdisplay) {
  if ($lineending == "\r\n") {
    $lineendingdisplay = "\\r\\n";
  } elseif ($lineending == "\r") {
    $lineendingdisplay = "\\r";
  } elseif ($lineending == "\n") {
    $lineendingdisplay = "\\n";
  }
}
if (!$tngconfig['maxdesc']) {
  $tngconfig['maxdesc'] = $maxdesc;
}
$tngconfig['doctype'] = preg_replace('/\"/', '&#34;', $tngconfig['doctype']);
$sitename = preg_replace('/\"/', '&#34;', $sitename);
$site_desc = preg_replace('/\"/', '&#34;', $site_desc);
$dbowner = preg_replace('/\"/', '&#34;', $dbowner);

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('modifysettings'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('setup-configuration-configsettings', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'admin_setup.php', uiTextSnippet('configuration'), 'configuration']);
    $navList->appendItem([true, 'admin_diagnostics.php', uiTextSnippet('diagnostics'), 'diagnostics']);
    $navList->appendItem([true, 'admin_setup.php?sub=tablecreation', uiTextSnippet('tablecreation'), 'tablecreation']);
    $navList->appendItem([true, '#', uiTextSnippet('configsettings'), 'gen']);
    echo $navList->build('gen');
    ?>
    <div class='small'>
      <a href='#' onClick="toggleAll('on');"><?php echo uiTextSnippet('expandall'); ?></a>
      <a href='#' onClick="toggleAll('off');"><?php echo uiTextSnippet('collapseall'); ?></a>
    </div>
    <form action="admin_updateconfig.php" method='post' name='form1'>

      <?php echo displayToggle('plus0', 0, 'db', uiTextSnippet('dbsection'), ''); ?>

      <div id="db" style="display:none">
        <table>
          <tr>
            <td colspan='2'>&nbsp;</td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('dbhost'); ?>:</td>
            <td><input name='new_database_host' type='text' value="<?php echo $database_host; ?>"></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('dbname'); ?>:</td>
            <td><input name='new_database_name' type='text' value="<?php echo $database_name; ?>"></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('dbusername'); ?>:</td>
            <td><input name='new_database_username' type='text' value="<?php echo $database_username; ?>"></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('dbpassword'); ?>:</td>
            <td>
              <input name='new_database_password' type='text' value="<?php echo $database_password; ?>">
            </td>
          </tr>
          <?php
          $query = "SELECT count(userID) AS ucount FROM users";
          $uresult = tng_query($query);
          if ($uresult) {
            $urow = tng_fetch_assoc($uresult);
            tng_free_result($uresult);
          } else {
            $urow['ucount'] = 0;
          }
          ?>
          <tr>
            <td colspan='2'>&nbsp;</td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('maintmode'); ?>:</td>
            <td>
              <select name="maint"<?php if (!$urow['ucount']) {echo ' disabled';} ?>>
                <option value=''<?php if (!$tngconfig['maint']) {echo ' selected';} ?>><?php echo uiTextSnippet('off'); ?></option>
                <option value='1'<?php if ($tngconfig['maint']) {echo ' selected';} ?>><?php echo uiTextSnippet('on'); ?></option>
              </select>
            </td>
          </tr>
        </table>
      </div>
      <?php echo displayToggle('plus1', 0, 'tables', uiTextSnippet('tablesection'), ''); ?>

      <div class='table table-sm' id='tables' style='display: none'>
        <table>
          <tr>
            <td>
              <table class='table table-sm'>
                <tr>
                  <td><?php echo uiTextSnippet('people'); ?>:</td>
                  <td><input name='people_table' type='text' value="<?php echo $people_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('families'); ?>:</td>
                  <td><input name='families_table' type='text' value="<?php echo $families_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('children'); ?>:</td>
                  <td><input name='children_table' type='text' value="<?php echo $children_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('albums'); ?>:</td>
                  <td><input name='albums_table' type='text' value="<?php echo $albums_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('album2entitiestable'); ?>:</td>
                  <td><input name='album2entities_table' type='text' value="<?php echo $album2entities_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('albumlinkstable'); ?>:</td>
                  <td><input name='albumlinks_table' type='text' value="<?php echo $albumlinks_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('media'); ?>:</td>
                  <td><input name='media_table' type='text' value="<?php echo $media_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('medialinkstable'); ?>:</td>
                  <td><input name='medialinks_table' type='text' value="<?php echo $medialinks_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('mediatypes'); ?>:</td>
                  <td><input name='mediatypes_table' type='text' value="<?php echo $mediatypes_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('addresstable'); ?>:</td>
                  <td><input name='address_table' type='text' value="<?php echo $address_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('languages'); ?>:</td>
                  <td><input name='languages_table' type='text' value="<?php echo $languagesTable; ?>"></td>
                </tr>
              </table>
            </td>
            <td>&nbsp;</td>
            <td>
              <table class='table table-sm'>
                <tr>
                  <td><?php echo uiTextSnippet('cemeteries'); ?>:</td>
                  <td><input name='cemeteries_table' type='text' value="<?php echo $cemeteries_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('sources'); ?>:</td>
                  <td><input name='sources_table' type='text' value="<?php echo $sources_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('citations'); ?>:</td>
                  <td><input name='citations_table' type='text' value="<?php echo $citations_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('repositories'); ?>:</td>
                  <td><input name='repositories_table' type='text' value="<?php echo $repositories_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('events'); ?>:</td>
                  <td><input name='events_table' type='text' value="<?php echo $events_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('eventtypes'); ?>:</td>
                  <td><input name='eventtypes_table' type='text' value="<?php echo $eventtypes_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('reports'); ?>:</td>
                  <td><input name='reports_table' type='text' value="<?php echo $reports_table; ?>"></td>
                </tr>
              </table>
            </td>
            <td>&nbsp;</td>
            <td>
              <table class='table table-sm'>
                <tr>
                  <td><?php echo uiTextSnippet('saveimporttable'); ?>:</td>
                  <td><input name='saveimport_table' type='text' value="<?php echo $saveimport_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('branches'); ?>:</td>
                  <td><input name='branches_table' type='text' value="<?php echo $branches_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('brlinkstable'); ?>:</td>
                  <td><input name='branchlinks_table' type='text' value="<?php echo $branchlinks_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('associations'); ?>:</td>
                  <td><input name='assoc_table' type='text' value="<?php echo $assoc_table; ?>"></td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('mostwanted'); ?>:</td>
                  <td><input name='mostwanted_table' type='text' value="<?php echo $mostwanted_table; ?>"></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </div>
      <?php echo displayToggle('plus2', 0, 'folders', uiTextSnippet('foldersection'), ''); ?>
      <div id="folders" style="display:none">
        <table>
          <tr>
            <td colspan='2'>&nbsp;</td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('rootpath'); ?>:</td>
            <td><input class='verylongfield' name='newrootpath' type='text' value="<?php echo $rootpath; ?>"></td>
          </tr>
<!-- [ts]
          <tr>
            <td><?php echo uiTextSnippet('subroot'); ?>*:</td>
            <td>
              <input class='verylongfield' name='newsubroot' type='text' value="<?php echo $tngconfig['subroot']; ?>">
            </td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>*<?php echo uiTextSnippet('srexpl'); ?></td>

          </tr>
-->
          <tr>
            <td><?php echo uiTextSnippet('photopath'); ?>:</td>
            <td>
              <input name='photopath' type='text' value="<?php echo $photopath; ?>">
              <input type='button' value="<?php echo uiTextSnippet('makefolder'); ?>"
                     onclick="makeFolder('photos', document.form1.photopath.value);"> <span
                      id="msg_photos"></span></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('documentpath'); ?>:</td>
            <td>
              <input name='documentpath' type='text' value="<?php echo $documentpath; ?>">
              <input type='button' value="<?php echo uiTextSnippet('makefolder'); ?>"
                     onClick="makeFolder('documents', document.form1.documentpath.value);"> <span
                      id="msg_documents"></span></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('historypath'); ?>:</td>
            <td><input name='historypath' type='text' value="<?php echo $historypath; ?>">
              <input type='button' value="<?php echo uiTextSnippet('makefolder'); ?>"
                     onClick="makeFolder('histories', document.form1.historypath.value);"> <span
                      id="msg_histories"></span></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('headstonepath'); ?>:</td>
            <td><input name='headstonepath' type='text' value="<?php echo $headstonepath; ?>">
              <input type='button' value="<?php echo uiTextSnippet('makefolder'); ?>"
                     onClick="makeFolder('headstones', document.form1.headstonepath.value);">
              <span id="msg_headstones"></span></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('mediapath'); ?>:</td>
            <td><input name='mediapath' type='text' value="<?php echo $mediapath; ?>">
              <input type='button' value="<?php echo uiTextSnippet('makefolder'); ?>"
                     onClick="makeFolder('media', document.form1.mediapath.value);"> <span
                      id="msg_media"></span></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('gendex'); ?>:</td>
            <td>
              <input name='gendexfile' type='text' value="<?php echo $gendexfile; ?>">
              <input type='button' value="<?php echo uiTextSnippet('makefolder'); ?>"
                     onClick="makeFolder('gendex', document.form1.gendexfile.value);"> <span
                      id="msg_gendex"></span></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('backuppath'); ?>:</td>
            <td><input name='backuppath' type='text' value="<?php echo $backuppath; ?>">
              <input type='button' value="<?php echo uiTextSnippet('makefolder'); ?>"
                     onClick="makeFolder('backups', document.form1.backuppath.value);"> <span
                      id="msg_backups"></span></td>
          </tr>
        </table>
      </div>

      <?php echo displayToggle('plus3', 0, 'site', uiTextSnippet('sitesection'), ''); ?>
      <div id="site" style="display:none">
        <table>
          <tr>
            <td colspan='2'>&nbsp;</td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('homepage'); ?>:</td>
            <td><input name='homepage' type='text' value="<?php echo $homepage; ?>" size='40'></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('tngdomain'); ?>:</td>
            <td><input name='tngdomain' type='text' value="<?php echo $tngdomain; ?>" size='40'>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('sitename'); ?>:</td>
            <td><input name='sitename' type='text' value="<?php echo $sitename; ?>" size='40'></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('site_desc'); ?>:</td>
            <td><textarea name="site_desc" rows='2' cols="65"><?php echo $site_desc; ?></textarea>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('doctype'); ?>:</td>
            <td><input name='doctype' type='text' value="<?php echo $tngconfig['doctype']; ?>" size='70'></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('siteowner'); ?>:</td>
            <td><input name='dbowner' type='text' value="<?php echo $dbowner; ?>" size='40'></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('targetframe'); ?>:</td>
            <td><input name='target' type='text' value="<?php echo $target; ?>"></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('custommeta'); ?>:</td>
            <td><input name='custommeta' type='text' value="<?php echo $custommeta; ?>">
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('tabstyle'); ?>:</td>
            <td>
              <select name="tng_tabs">
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('iconloc'); ?>:</td>
            <td>
              <select name="tng_menu">
                <option value='0'<?php if (!$tngconfig['menu']) {echo ' selected';} ?>><?php echo uiTextSnippet('topright'); ?></option>
                <option value='1'<?php if ($tngconfig['menu'] == 1) {echo ' selected';} ?>><?php echo uiTextSnippet('topleft'); ?></option>
                <option value='2'<?php if ($tngconfig['menu'] == 2) {echo ' selected';} ?>><?php echo uiTextSnippet('nodisplay'); ?></option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('showhome'); ?>:</td>
            <td>
              <select name="showhome">
                <option value='0'<?php if (!$tngconfig['showhome']) {echo ' selected';} ?>><?php echo uiTextSnippet('yes'); ?></option>
                <option value='1'<?php if ($tngconfig['showhome']) {echo ' selected';} ?>><?php echo uiTextSnippet('no'); ?></option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('showsearch'); ?>:</td>
            <td>
              <select name='showsearch'>
                <option value='0'<?php if (!$tngconfig['showsearch']) {echo ' selected';} ?>><?php echo uiTextSnippet('yes'); ?></option>
                <option value='1'<?php if ($tngconfig['showsearch']) {echo ' selected';} ?>><?php echo uiTextSnippet('no'); ?></option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('searchchoice'); ?>:</td>
            <td>
              <select name='searchchoice'>
                <option value='0'<?php if (!$tngconfig['searchchoice']) {echo ' selected';} ?>><?php echo uiTextSnippet('quicksearch'); ?></option>
                <option value='1'<?php if ($tngconfig['searchchoice']) {echo ' selected';} ?>><?php echo uiTextSnippet('advsearch'); ?></option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('showlogin'); ?>:</td>
            <td>
              <select name='showlogin'>
                <option value='0'<?php if (!$tngconfig['showlogin']) {echo ' selected';} ?>><?php echo uiTextSnippet('yes'); ?></option>
                <option value='1'<?php if ($tngconfig['showlogin']) {echo ' selected';} ?>><?php echo uiTextSnippet('no'); ?></option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('showshare'); ?>:</td>
            <td>
              <select name='showshare'>
                <option value='1'<?php if ($tngconfig['showshare']) {echo ' selected';} ?>><?php echo uiTextSnippet('yes'); ?></option>
                <option value='0'<?php if (!$tngconfig['showshare']) {echo ' selected';} ?>><?php echo uiTextSnippet('no'); ?></option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('showprint'); ?>:</td>
            <td>
              <select name='showprint'>
                <option value='0'<?php if (!$tngconfig['showprint']) {echo ' selected';} ?>><?php echo uiTextSnippet('yes'); ?></option>
                <option value='1'<?php if ($tngconfig['showprint']) {echo ' selected';} ?>><?php echo uiTextSnippet('no'); ?></option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('showbmarks'); ?>:</td>
            <td>
              <select name='showbmarks'>
                <option value='0'<?php if (!$tngconfig['showbmarks']) {echo ' selected';} ?>><?php echo uiTextSnippet('yes'); ?></option>
                <option value='1'<?php if ($tngconfig['showbmarks']) {echo ' selected';} ?>><?php echo uiTextSnippet('no'); ?></option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('hidechr'); ?>:</td>
            <td>
              <select name='hidechr'>
                <option value='0'<?php if (!$tngconfig['hidechr']) {echo ' selected';} ?>><?php echo uiTextSnippet('no'); ?></option>
                <option value='1'<?php if ($tngconfig['hidechr']) {echo ' selected';} ?>><?php echo uiTextSnippet('yes'); ?></option>
              </select>
            </td>
          </tr>
          
        </table>
      </div>
        <?php echo displayToggle('plus4', 0, 'media', uiTextSnippet('media'), ''); ?>
      <div id="media" style="display:none">
        <table>
          <tr>
            <td colspan='2'>&nbsp;</td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('photosext'); ?>:</td>
            <td>
              <select name="photosext">
                <option value="jpg"<?php if ($photosext == 'jpg') {echo ' selected';} ?>>.jpg</option>
                <option value="gif"<?php if ($photosext == 'gif') {echo ' selected';} ?>>.gif</option>
                <option value="png"<?php if ($photosext == 'png') {echo ' selected';} ?>>.png</option>
                <option value="bmp"<?php if ($photosext == 'bmp') {echo ' selected';} ?>>.bmp</option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('showextended'); ?>:</td>
            <td>
              <select name='showextended'>
                <option value='1'<?php if ($showextended) {echo ' selected';} ?>><?php echo uiTextSnippet('yes'); ?></option>
                <option value='0'<?php if (!$showextended) {echo ' selected';} ?>><?php echo uiTextSnippet('no'); ?></option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('imgmaxh'); ?>:</td>
            <td><input name='imgmaxh' type='text' value="<?php echo $tngconfig['imgmaxh']; ?>"></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('imgmaxw'); ?>:</td>
            <td><input name='imgmaxw' type='text' value="<?php echo $tngconfig['imgmaxw']; ?>"></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('thumbprefix'); ?>:</td>
            <td><input name='thumbprefix' type='text' value="<?php echo $thumbprefix; ?>">
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('thumbsuffix'); ?>:</td>
            <td>
              <input name='thumbsuffix' type='text' value="<?php echo $thumbsuffix; ?>">
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('thumbmaxh'); ?>:</td>
            <td>
              <input name='thumbmaxh' type='text' value="<?php echo $thumbmaxh; ?>">
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('thumbmaxw'); ?>:</td>
            <td><input name='thumbmaxw' type='text' value="<?php echo $thumbmaxw; ?>">
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('usedefthumbs'); ?>:</td>
            <td>
              <select name='tng_usedefthumbs'>
                <option value='0'<?php if (!$tngconfig['usedefthumbs']) {echo ' selected';} ?>><?php echo uiTextSnippet('no'); ?></option>
                <option value='1'<?php if ($tngconfig['usedefthumbs']) {echo ' selected';} ?>><?php echo uiTextSnippet('yes'); ?></option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('thumbcols'); ?>:</td>
            <td>
              <input name='tng_thumbcols' type='text' value="<?php echo $tngconfig['thumbcols']; ?>">
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('maxnoteprev'); ?>:</td>
            <td><input name='tng_maxnoteprev' type='text' value="<?php echo $tngconfig['maxnoteprev']; ?>"></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('ssenabled'); ?>:</td>
            <td>
              <select name='tng_ssdisabled'>
                <option value='0'<?php if (!$tngconfig['ssdisabled']) {echo ' selected';} ?>><?php echo uiTextSnippet('yes'); ?></option>
                <option value='1'<?php if ($tngconfig['ssdisabled']) {echo ' selected';} ?>><?php echo uiTextSnippet('no'); ?></option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('ssrepeat'); ?>:</td>
            <td>
              <select name='tng_ssrepeat'>
                <option value='1'<?php if ($tngconfig['ssrepeat']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
                <option value='0'<?php if (!$tngconfig['ssrepeat']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('imgviewer'); ?>:</td>
            <td>
              <select name='tng_imgviewer'>
                <option value='0'<?php if (!$tngconfig['imgviewer']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('ldson'); ?>
                </option>
                <option value="documents"<?php if ($tngconfig['imgviewer'] == 'documents') {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('docsonly'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('imgvheight'); ?>:</td>
            <td>
              <select name='tng_imgvheight'>
                <option value='0'<?php if ($tngconfig['imgvheight'] == 1) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('flex'); ?>
                </option>
                <option value='640'<?php if ($tngconfig['imgvheight'] == '640') {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('setheight'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('hidemedia'); ?>:</td>
            <td>
              <select name="hidemedia">
                <option value='1'<?php if ($tngconfig['hidemedia']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
                <option value='0'<?php if (!$tngconfig['hidemedia']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
              </select>
            </td>
          </tr>
        </table>
      </div>
      <?php echo displayToggle('plus5', 0, 'lang', uiTextSnippet('language'), ''); ?>
      <div id="lang" style="display:none">
        <table>
          <tr>
            <td colspan='2'>&nbsp;</td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('langfolder'); ?>:</td>
            <td>
              <select name="language">
                <?php
                chdir($rootpath . $endrootpath . $languagesPath);
                if ($handle = opendir('.')) {
                  $dirs = [];
                  while ($filename = readdir($handle)) {
                    if (is_dir($filename) && $filename != '..' && $filename != '.') {
                      array_push($dirs, $filename);
                    }
                  }
                  natcasesort($dirs);
                  $found_current = 0;
                  foreach ($dirs as $dir) {
                    echo "<option value=\"$dir\"";
                    if ($dir == $language) {
                      echo ' selected';
                      $found_current = 1;
                    }
                    echo ">$dir</option>\n";
                  }
                  if (!$found_current) {
                    echo "<option value=\"$language\" selected>$language</option>\n";
                  }
                  closedir($handle);
                }
                ?>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('charset'); ?>:</td>
            <td><input name='charset' type='text' value="<?php echo $charset; ?>"></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('chooselang'); ?>:</td>
            <td>
              <select name='chooselang'>
                <option value='1'<?php if ($chooselang) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('allow'); ?>
                </option>
                <option value='0'<?php if (!$chooselang) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('disallow'); ?>
                </option>
              </select>
            </td>
          </tr>
        </table>
      </div>
      <?php echo displayToggle('plus6', 0, 'priv', uiTextSnippet('privsection'), ''); ?>
      <div id="priv" style="display:none">
        <table>
          <tr>
            <td colspan='2'>&nbsp;</td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('requirelogin'); ?>:</td>
            <td>
              <select name='requirelogin'
                      onchange="flipTreeRestrict(this.options[this.selectedIndex].value);">
                <option value='1'<?php if ($requirelogin) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
                <option value='0'<?php if (!$requirelogin) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('ldsdefault'); ?>:</td>
            <td>
              <select name='ldsdefault'>
                <option value='0'<?php if (!$ldsdefault) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('ldson'); ?>
                </option>
                <option value='1'<?php if ($ldsdefault == 1) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('ldsoff'); ?>
                </option>
                <option value='2'<?php if ($ldsdefault == 2) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('ldspermit'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('livedefault'); ?>:</td>
            <td>
              <select name='livedefault'>
                <option value='2'<?php if ($livedefault == 2) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('ldson'); ?>
                </option>
                <option value='1'<?php if ($livedefault == 1) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('ldsoff'); ?>
                </option>
                <option value='0'<?php if (!$livedefault) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('ldspermit'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('shownames'); ?>:</td>
            <td>
              <select name='nonames'>
                <option value='0'<?php if (!$nonames) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
                <option value='1'<?php if ($nonames == 1) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
                <option value='2'<?php if ($nonames == 2) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('initials'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('shownamespr'); ?>:</td>
            <td>
              <select name='nnpriv'>
                <option value='0'<?php if (!$tngconfig['nnpriv']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
                <option value='1'<?php if ($tngconfig['nnpriv'] == 1) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
                <option value='2'<?php if ($tngconfig['nnpriv'] == 2) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('initials'); ?>
                </option>
              </select>
            </td>
          </tr>
        </table>
      </div>
      <?php echo displayToggle('plus7', 0, 'names', uiTextSnippet('namesection'), ''); ?>
      <div id="names" style="display:none">
        <table>
          <tr>
            <td colspan='2'>&nbsp;</td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('nameorder'); ?>:</td>
            <td>
              <select name='nameorder'>
                <option value=''></option>
                <option value='1'<?php if ($nameorder == 1) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('western'); ?>
                </option>
                <option value='2'<?php if ($nameorder == 2) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('oriental'); ?>
                </option>
                <option value='3'<?php if ($nameorder == 3) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('lnfirst'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('ucsurnames'); ?>:</td>
            <td>
              <select name='ucsurnames'>
                <option value='0'<?php if (!$tngconfig['ucsurnames']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
                <option value='1'<?php if ($tngconfig['ucsurnames']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('lnprefixes'); ?>:</td>
            <td>
              <select name='lnprefixes'>
                <option value='0'<?php if (!$lnprefixes) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('lntogether'); ?>
                </option>
                <option value='1'<?php if ($lnprefixes) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('lnapart'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td colspan='2'><?php echo uiTextSnippet('detectpfx'); ?>:</td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('lnpfxnum'); ?>:</td>
            <td><input name='lnpfxnum' type='text' value="<?php echo $lnpfxnum; ?>" size='5' /></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('specpfx'); ?>*:</td>
            <td>
              <input name='specpfx' type='text' value="<?php echo stripslashes($specpfx); ?>" /></td>
          </tr>
          <tr>
            <td colspan='2'>*<?php echo uiTextSnippet('commas'); ?></td>
          </tr>
        </table>
      </div>
      <?php echo displayToggle('plus8', 0, 'cemeteries', uiTextSnippet('cemeteries'), ''); ?>
      <div id="cemeteries" style="display:none">
        <table>
          <tr>
            <td colspan='2'>&nbsp;</td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('cemrows'); ?>:</td>
            <td><input type='text' value="<?php echo $tngconfig['cemrows']; ?>" name="cemrows" size='5'></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('cemblanks'); ?>:</td>
            <td>
              <select name='cemblanks'>
                <option value='0'<?php if (!$tngconfig['cemblanks']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
                <option value='1'<?php if ($tngconfig['cemblanks']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
              </select>
            </td>
          </tr>
        </table>
      </div>
      <?php echo displayToggle('plus9', 0, 'mailreg', uiTextSnippet('mailreg'), ''); ?>
      <div id="mailreg" style="display:none">
        <table>
          <tr>
            <td><?php echo uiTextSnippet('emailaddress'); ?>:</td>
            <td><input name='emailaddr' type='text' value="<?php echo $emailaddr; ?>" size='40'>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('fromadmin'); ?>:</td>
            <td>
              <select name='fromadmin'>
                <option value='0'<?php if (!$tngconfig['fromadmin']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
                <option value='1'<?php if ($tngconfig['fromadmin']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('allowreg'); ?>:</td>
            <td>
              <select name='disallowreg' onchange="toggleAllowReg();">
                <option value='0'<?php if (!$tngconfig['disallowreg']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
                <option value='1'<?php if ($tngconfig['disallowreg']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('revmail'); ?>:</td>
            <td>
              <select name='revmail'>
                <option value='0'<?php if (!$tngconfig['revmail']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
                <option value='1'<?php if ($tngconfig['revmail']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('autoapp'); ?>:</td>
            <td>
              <select id='autoapp' name='autoapp' onchange="toggleAutoApprove();">
                <option value='0'<?php if (!$tngconfig['autoapp']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
                <option value='1'<?php if ($tngconfig['autoapp']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('ackemail'); ?>:</td>
            <td>
              <select id='ackemail' name='ackemail'<?php if ($tngconfig['autoapp']) {echo ' disabled';} ?>>
                <option value='0'<?php if (!$tngconfig['ackemail']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
                <option value='1'<?php if ($tngconfig['ackemail']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('inclpwd'); ?>:</td>
            <td>
              <select id='omitpwd' name='omitpwd'>
                <option value='0'<?php if (!$tngconfig['omitpwd']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
                <option value='1'<?php if ($tngconfig['omitpwd']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('usesmtp'); ?>:</td>
            <td>
              <select id='usesmtp' name='usesmtp' onchange="$('#smtpstuff').toggle(200);">
                <option value='0'<?php if (!$tngconfig['usesmtp']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
                <option value='1'<?php if ($tngconfig['usesmtp']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
              </select>
            </td>
          </tr>
        </table>
        <table id="smtpstuff" style="margin-left: 5px;<?php if (!$tngconfig['usesmtp']) {echo ' display: none';} ?>">
          <tr>
            <td><?php echo uiTextSnippet('mailhost'); ?>:</td>
            <td><input name='mailhost' type='text' value="<?php echo $tngconfig['mailhost']; ?>" size='40'></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('mailuser'); ?>:</td>
            <td><input name='mailuser' type='text' value="<?php echo $tngconfig['mailuser']; ?>" size='40'></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('mailpass'); ?>:</td>
            <td><input name='mailpass' type='text' value="<?php echo $tngconfig['mailpass']; ?>" size='40'></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('mailport'); ?>:</td>
            <td><input name='mailport' type='text' value="<?php echo $tngconfig['mailport']; ?>" size='40'></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('mailenc'); ?>:</td>
            <td>
              <select name="mailenc" id="mailenc">
                <option value=''<?php if (!$tngconfig['mailenc']) {echo ' selected';} ?>></option>
                <option value="ssl"<?php if ($tngconfig['mailenc'] == 'ssl') {echo ' selected';} ?>>ssl</option>
                <option value="tls"<?php if ($tngconfig['mailenc'] == 'tls') {echo ' selected';} ?>>tls</option>
              </select>
            </td>
          </tr>
        </table>
      </div>
      <?php echo displayToggle('plus10', 0, 'misc', uiTextSnippet('miscsection'), ''); ?>
      <div id="misc" style="display:none">
        <table>
          <tr>
            <td><?php echo uiTextSnippet('maxsearchresults'); ?>:</td>
            <td>
              <input name='maxsearchresults' type='text' value="<?php echo $maxsearchresults; ?>">
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('indstart'); ?>:</td>
            <td>
              <select name="tng_istart">
                <option value='0'<?php if (!$tngconfig['istart']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('allinfo'); ?>
                </option>
                <option value='1'<?php if ($tngconfig['istart']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('persinfo'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('shownotes'); ?>:</td>
            <td>
              <select name="notestogether">
                <option value='0'<?php if (!$notestogether) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('notesapart'); ?>
                </option>
                <option value='1'<?php if ($notestogether == 1) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('notestogether'); ?>
                </option>
                <option value='2'<?php if ($notestogether == 2) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('notestogether2'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('scrollcite'); ?>:</td>
            <td>
              <select name="scrollcite">
                <option value='1'<?php if ($tngconfig['scrollcite']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
                <option value='0'<?php if (!$tngconfig['scrollcite']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('time_offset'); ?>:</td>
            <td>
              <input name='time_offset' type='text' value="<?php echo $timeOffset; ?>" size='5'>
                <?php echo uiTextSnippet('servertime') . ' <strong>' . date('D h:i a') . '</strong> ';
                $new_U = date('U') + $timeOffset * 3600;
                echo uiTextSnippet('sitetime') . ' <strong>' . date('D h:i a', $new_U) . '</strong>'; ?>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('edit_timeout'); ?>:</td>
            <td>
              <input name='edit_timeout' type='text' value="<?php echo $tngconfig['edit_timeout']; ?>" size='5'>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('maxgedcom'); ?>:</td>
            <td>
              <input name='maxgedcom' type='text' value="<?php echo $maxgedcom; ?>" size='5'>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('change_cutoff'); ?>:</td>
            <td>
              <input name='change_cutoff' type='text' value="<?php echo $change_cutoff; ?>" size='5'>
                <?php echo uiTextSnippet('nocutoff'); ?>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('change_limit'); ?>:</td>
            <td>
              <input name='change_limit' type='text' value="<?php echo $change_limit; ?>" size='5'>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('datefmt'); ?>:</td>
            <td>
              <select name="prefereuro">
                <option value="false"<?php if ($tngconfig['preferEuro'] == 'false') {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('monthfirst'); ?>
                </option>
                <option value="true"<?php if ($tngconfig['preferEuro'] == 'true') {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('dayfirst'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('calstart'); ?>:</td>
            <td>
              <select name="calstart">
                <option value='0'<?php if (!isset($tngconfig['calstart']) || $tngconfig['calstart'] == '0') {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('sunday'); ?>
                </option>
                <option value='1'<?php if ($tngconfig['calstart'] == '1') {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('monday'); ?>
                </option>
                <option value='2'<?php if ($tngconfig['calstart'] == '2') {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('tuesday'); ?>
                </option>
                <option value='3'<?php if ($tngconfig['calstart'] == '3') {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('wednesday'); ?>
                </option>
                <option value='4'<?php if ($tngconfig['calstart'] == '4') {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('thursday'); ?>
                </option>
                <option value='5'<?php if ($tngconfig['calstart'] == '5') {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('friday'); ?>
                </option>
                <option value='6'<?php if ($tngconfig['calstart'] == '6') {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('saturday'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('pardata'); ?>:</td>
            <td>
              <select name='pardata'>
                <option value='0'<?php if (!$tngconfig['pardata']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('palldata'); ?></option>
                <option value='1'<?php if ($tngconfig['pardata'] == 1) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('pstdonly'); ?>
                </option>
                <option value='2'<?php if ($tngconfig['pardata'] == 2) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('pnoevents'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('lineending'); ?>:</td>
            <td><input type='text' value="<?php echo $lineendingdisplay; ?>"
                                   name="lineending" size='5'></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('encrtype'); ?>:</td>
            <td>
              <select name="password_type">
                <?php
                $encrtypes = PasswordTypeList();
                foreach ($encrtypes as $encrtype) {
                  $display = $encrtype != 'none' ? $encrtype : uiTextSnippet('none');
                  echo "<option value=\"$encrtype\"";
                  if ($encrtype == $tngconfig['password_type']) {
                    echo ' selected';
                  }
                  echo ">$display</option>\n";
                }
                ?>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('autogeo'); ?>:</td>
            <td>
              <select name='autogeo'>
                <option value='0'<?php if (!$tngconfig['autogeo']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
                <option value='1'<?php if ($tngconfig['autogeo']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('reuseids'); ?>:</td>
            <td>
              <select name='oldids'>
                <option value=''<?php if (!$tngconfig['oldids']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
                <option value='1'<?php if ($tngconfig['oldids']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('lastimport'); ?>:</td>
            <td>
              <select name='lastimport'>
                <option value=''<?php if (!$tngconfig['lastimport']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('no'); ?>
                </option>
                <option value='1'<?php if ($tngconfig['lastimport']) {echo ' selected';} ?>>
                  <?php echo uiTextSnippet('yes'); ?>
                </option>
              </select>
            </td>
          </tr>
        </table>
      </div>

      <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
      <input name='safety' type='hidden' value='1'>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/admin.js"></script>
  <script>
    function toggleAll(display) {
      toggleSection('db', 'plus0', display);
      toggleSection('tables', 'plus1', display);
      toggleSection('folders', 'plus2', display);
      toggleSection('site', 'plus3', display);
      toggleSection('media', 'plus4', display);
      toggleSection('lang', 'plus5', display);
      toggleSection('priv', 'plus6', display);
      toggleSection('names', 'plus7', display);
      toggleSection('cemeteries', 'plus8', display);
      toggleSection('mailreg', 'plus9', display);
      toggleSection('misc', 'plus10', display);
      return false;
    }

    function flipTreeRestrict(requirelogin) {
      if (parseInt(requirelogin)) {
        $('#treerestrict').show();
        $('#trdisabled').hide();
      } else {
        $('#treerestrict').hide();
        $('#trdisabled').show();
      }
    }

    function toggleAllowReg() {
      if (document.form1.disallowreg.selectedIndex === 1) {   //off
        $('#autoapp').attr('disabled', 'disabled');
        $('#autotree').attr('disabled', 'disabled');
        $('#ackemail').attr('disabled', 'disabled');
        $('#omitpwd').attr('disabled', 'disabled');
      } else {
        $('#autoapp').attr('disabled', '');
        $('#autotree').attr('disabled', '');
        if (document.form1.autoapp.selectedIndex === 0) {
          $('#ackemail').attr('disabled', '');
        }
        $('#omitpwd').attr('disabled', '');
      }
    }

    function toggleAutoApprove() {
      if (document.form1.autoapp.selectedIndex === 1) {   //off
        $('#ackemail').attr('disabled', 'disabled');
      } else {
        $('#ackemail').attr('disabled', '');
      }
    }

    function flipPlaces(select) {
      if (select.selectedIndex) {
        //change to NO
        $('#merge').show();
        $('#mergeexpl').show();
        $('#convert').hide();
        $('#convertexpl').hide();
        $('#placetree').hide();
      } else {
        //change to YES
        $('#convert').show();
        $('#convertexpl').show();
        $('#placetree').show();
        $('#merge').hide();
        $('#mergeexpl').hide();
      }
    }

    function convertPlaces(command) {
      var options = {action: command};
      if (command === "convert")
        options.placetree = $('#placetree').val();
      $('#' + command + 'expl').html('<img src="img/spinner.gif" style="border:0;vertical-align:middle;">');

      $.ajax({
        url: 'ajx_placeconvert.php',
        data: options,
        type: 'GET',
        dataType: 'html',
        success: function (req) {
          $('#' + command + 'expl').html(req);
        }
      });

      return false;
    }
  </script>
</body>
</html>
