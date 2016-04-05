<?php
require 'begin.php';
require $subroot . 'mapconfig.php';
require 'adminlib.php';

if ($link) {
  $adminLogin = 1;
  include 'checklogin.php';
  include 'version.php';

  if ($assignedtree || !$allowEdit) {
    $message = uiTextSnippet('norights');
    header("Location: admin_login.php?message=" . urlencode($message));
    exit;
  }
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('modifymapsettings'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="setup-configuration-mapconfigsettings">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('setup-configuration-mapconfigsettings', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_setup.php", uiTextSnippet('configuration'), "configuration"]);
    $navList->appendItem([true, "admin_diagnostics.php", uiTextSnippet('diagnostics'), "diagnostics"]);
    $navList->appendItem([true, "admin_setup.php?sub=tablecreation", uiTextSnippet('tablecreation'), "tablecreation"]);
    $navList->appendItem([true, "#", uiTextSnippet('mapconfigsettings'), "map"]);
    echo $navList->build("map");
    ?>
    <form action="admin_updatemapconfig.php" method='post' name='form1'>
      <table>
        <tr>
          <td><?php echo uiTextSnippet('mapkey'); ?>:</td>
          <td>
            <select name="mapkey">
              <option value='1'<?php if ($map['key']) {echo " selected";} ?>>
                <?php echo uiTextSnippet('yes'); ?>
              </option>
              <option value='0'<?php if (!$map['key']) {echo " selected";} ?>>
                <?php echo uiTextSnippet('no'); ?>
              </option>
            </select>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('maptype'); ?>:</td>
          <td>
            <select name="maptype">
              <option value="TERRAIN"<?php if ($map['displaytype'] == "TERRAIN") {echo " selected";} ?>>
                <?php echo uiTextSnippet('mapterrain'); ?>
              </option>
              <option value="ROADMAP"<?php if ($map['displaytype'] == "ROADMAP") {echo " selected";} ?>>
                <?php echo uiTextSnippet('maproadmap'); ?>
              </option>
              <option value="HYBRID"<?php if ($map['displaytype'] == "HYBRID") {echo " selected";} ?>>
                <?php echo uiTextSnippet('maphybrid'); ?>
              </option>
              <option value="SATELLITE"<?php if ($map['displaytype'] == "SATELLITE") {echo " selected";} ?>>
                <?php echo uiTextSnippet('mapsatellite'); ?>
              </option>
            </select>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('mapstlat'); ?>:</td>
          <td>
            <input name='mapstlat' type='text' value="<?php echo $map['stlat']; ?>"
                   onblur="this.value = validateLatLong(this.value)">
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('mapstlong'); ?>:</td>
          <td>
            <input name='mapstlong' type='text' value="<?php echo $map['stlong']; ?>"
                   onblur="this.value = validateLatLong(this.value)">
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('mapstzm'); ?>:</td>
          <td>
            <select name="mapstzoom">
              <?php
              for ($i = 0; $i <= 17; $i++) {
                echo "<option value=\"$i\"";
                if ($map['stzoom'] == $i) {
                  echo " selected";
                }
                echo ">$i</option>\n";
              }
              ?>
            </select>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('mapfoundzm'); ?>:</td>
          <td>
            <select name="mapfoundzoom">
              <?php
              for ($i = 0; $i <= 17; $i++) {
                echo "<option value=\"$i\"";
                if ($map['foundzoom'] == $i) {
                  echo " selected";
                }
                echo ">$i</option>\n";
              }
              ?>
            </select>
          </td>
        </tr>

        <tr>
          <td colspan='2'><span><br><?php echo uiTextSnippet('mapdimsind'); ?>:</span></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('mapwidth'); ?>:</td>
          <td>
            <input name='mapindw' type='text' value="<?php echo $map['indw']; ?>"
                   onblur="this.value = validateWidth(this.value)">
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('mapheight'); ?>:</td>
          <td>
            <input name='mapindh' type='text' value="<?php echo $map['indh']; ?>"
                   onblur="this.value = validateHeight(this.value)">
          </td>
        </tr>

        <tr>
          <td colspan='2'><span><br><?php echo uiTextSnippet('mapdimshst'); ?>:</span></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('mapwidth'); ?>:</td>
          <td>
            <input name='maphstw' type='text' value="<?php echo $map['hstw']; ?>"
                   onblur="this.value = validateWidth(this.value)">
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('mapheight'); ?>:</td>
          <td>
            <input name='maphsth' type='text' value="<?php echo $map['hsth']; ?>"
                   onblur="this.value = validateHeight(this.value)">
          </td>
        </tr>

        <tr>
          <td colspan='2'><span><br><?php echo uiTextSnippet('mapdimsadm'); ?>:</span></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('mapwidth'); ?>:</td>
          <td>
            <input name='mapadmw' type='text' value="<?php echo $map['admw']; ?>"
                   onblur="this.value = validateWidth(this.value)">
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('mapheight'); ?>:</td>
          <td>
            <input name='mapadmh' type='text' value="<?php echo $map['admh']; ?>"
                   onblur="this.value = validateHeight(this.value)">
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('startoff'); ?>:</td>
          <td>
            <select name="startoff">
              <option value='1'<?php if ($map['startoff']) {echo " selected";} ?>>
                <?php echo uiTextSnippet('yes'); ?>
              </option>
              <option value='0'<?php if (!$map['startoff']) {echo " selected";} ?>>
                <?php echo uiTextSnippet('no'); ?>
              </option>
            </select>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('pstartoff'); ?>:</td>
          <td>
            <select name="pstartoff">
              <option value='1'<?php if ($map['pstartoff']) {echo " selected";} ?>>
                <?php echo uiTextSnippet('yes'); ?>
              </option>
              <option value='0'<?php if (!$map['pstartoff']) {echo " selected";} ?>>
                <?php echo uiTextSnippet('no'); ?>
              </option>
            </select>
          </td>
        </tr>
        <tr>
          <td colspan='2'><br></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('conslpins'); ?>:</td>
          <td>
            <select name="showallpins">
              <option value='0'<?php if (!$map['showallpins']) {echo " selected";} ?>>
                <?php echo uiTextSnippet('yes'); ?>
              </option>
              <option value='1'<?php if ($map['showallpins']) {echo " selected";} ?>>
                <?php echo uiTextSnippet('no'); ?>
              </option>
            </select>
          </td>
        </tr>
      </table>
      <br>
      <input name='pinplacelevel0' type='hidden' value="<?php echo $pinplacelevel0; ?>">
      <input name='pinplacelevel1' type='hidden' value="<?php echo $pinplacelevel1; ?>">
      <input name='pinplacelevel2' type='hidden' value="<?php echo $pinplacelevel2; ?>">
      <input name='pinplacelevel3' type='hidden' value="<?php echo $pinplacelevel3; ?>">
      <input name='pinplacelevel4' type='hidden' value="<?php echo $pinplacelevel4; ?>">
      <input name='pinplacelevel5' type='hidden' value="<?php echo $pinplacelevel5; ?>">
      <input name='pinplacelevel6' type='hidden' value="<?php echo $pinplacelevel6; ?>">
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script>
    function validateWidth(width) {
      if (width.indexOf('%') >= 0)
        return Math.min(parseInt(width), 100) + '%';
      else
        return parseInt(width) + 'px';
    }

    function validateHeight(height) {
      return parseInt(height) + 'px';
    }

    function validateLatLong(coord) {
      var c;
      var keep = "1234567890.-";     // Characters stripped out
      var i;
      var returnString = "";
      for (i = 0; i < coord.length; i++) {  // Search through string and append to unfiltered values to returnString.
        c = coord.charAt(i);
        if (keep.indexOf(c) !== -1)
          returnString += c;
        else
          break;
      }
      return returnString;
    }
  </script>
</body>
</html>

