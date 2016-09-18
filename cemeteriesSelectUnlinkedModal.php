<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

header('Content-type:text/html; charset=' . $session_charset);

$query = 'SELECT cemeteryID, cemname, city, county, state, country FROM cemeteries WHERE place = "" ORDER BY country, state, county, city, cemname';
$result = tng_query($query);
?>
<div id='cemdiv'>
  <?php if (tng_num_rows($result)) { ?>
    <form id='findcemetery' name='findcemetery' action='' onsubmit="return addCemLink(this.cemeteryID.options[this.cemeteryID.selectedIndex].value);">
      <header class='modal-header'>
        <h4><?php echo uiTextSnippet('choosecem'); ?></h4>
        <p><?php echo uiTextSnippet('cemsavail'); ?></p>
      </header>
      <div class='modal-body'>
        <fieldset class='form-group'>
          <select class='form-control' id='cemeteryID' name='cemeteryID'>
            <option value=''></option>
            <?php
            while ($cemrow = tng_fetch_assoc($result)) {
              $location = $cemrow['country'];
              if ($cemrow['state']) {
                if ($location) {
                  $location .= ', ';
                }
                $location .= $cemrow['state'];
              }
              if ($cemrow['county']) {
                if ($location) {
                  $location .= ', ';
                }
                $location .= $cemrow['county'];
              }
              if ($cemrow['city']) {
                if ($location) {
                  $location .= ', ';
                }
                $location .= $cemrow['city'];
              }
              if ($cemrow['cemname']) {
                if ($location) {
                  $location .= ', ';
                }
                $location .= $cemrow['cemname'];
              }
              echo "<option value=\"{$cemrow['cemeteryID']}\">$location</option>\n";
            }
            ?>
          </select>
        </fieldset>
      </div> <!-- .modal-body -->
      <footer class='modal-footer'>
        <button class='btn btn-primary' type='submit'><?php echo uiTextSnippet('go'); ?></button>
      </footer>
    </form>
  <?php } else { ?>
    <p><?php echo uiTextSnippet('nocemsavail'); ?></p>
  <?php
  }
  tng_free_result($result);
  ?>
</div>