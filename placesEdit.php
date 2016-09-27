<?php
/**
 * Name history: admin_editplace.php
 */

require 'begin.php';
require $subroot . 'mapconfig.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

$tng_search_places = $_SESSION['tng_search_places'];

if (is_numeric($ID)) {
  $wherestr = "ID = \"$ID\"";
} else {
  $wherestr = "place = \"$ID\"";
}
$query = "SELECT * FROM places WHERE $wherestr";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$orgplace = $row['place'];
$ID = $row['ID'];
$row['place'] = preg_replace('/\"/', '&#34;', $row['place']);

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('modifyplace'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
  
<body<?php echo ((bool) $map['key'] === true && (bool) $map['startoff'] === false) ? " onload=\"divbox('mapcontainer');\"" : ""; ?>>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('places-modifyplace', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'placesBrowse.php', uiTextSnippet('browse'), 'findplace']);
    $navList->appendItem([$allowAdd, 'placesAdd.php', uiTextSnippet('add'), 'addplace']);
    $navList->appendItem([$allowEdit && $allowDelete, 'placesMerge.php', uiTextSnippet('merge'), 'merge']);
    $navList->appendItem([$allowEdit, 'admin_geocodeform.php', uiTextSnippet('geocode'), 'geo']);
    // $navList->appendItem([$allowEdit, '#', uiTextSnippet('edit'), 'edit']);
    echo $navList->build('edit');
    ?>
    <br>
    <a href="placesearch.php?psearch=<?php echo urlencode($orgplace); ?>" title='<?php echo uiTextSnippet('preview') ?>'>
      <img class='icon-sm' src='svg/eye.svg'>
    </a>
    <a href="admin_newmedia.php?personID=<?php echo $row['place']; ?>&amp;linktype=L"><?php echo uiTextSnippet('addmedia'); ?></a>
    <form action='placesEditFormAction.php' method='post' name='form1' id='form1' onSubmit="return validateForm();">
      <h2><?php echo $row['place']; ?></h2>

      <div class='form-group row'>
        <label class='col-form-label col-sm-2' for='place'><?php echo uiTextSnippet('place'); ?></label>
        <div class='col-sm-6'>
          <input class='form-control' id='place' name='place' type='text' value="<?php echo $row['place']; ?>">
        </div>
      </div>
      <?php
      if (determineLDSRights()) {
        echo "<div class='row'><div class='offset-sm-2 col-sm-6'><input name='temple' type='checkbox' value='1'";
        if ($row['temple']) {
          echo ' checked';
        }
        echo '> ' . uiTextSnippet('istemple') . "</div></div>\n";
      }
      if ($map['key']) { 
      ?>
        <hr>
        <div class='form-group row'>
          <label class='col-form-label col-lg-2'><?php echo uiTextSnippet('mapof'); ?></label>
          <div class='col-lg-6'>
            <?php echo buildGoogleMapCardHtml($map, $row['place']); ?>
          </div>
        </div>
      <?php } ?>
      <div class='row'>
        <label class='col-form-label offset-sm-2 col-sm-2' for='latitude'><?php echo uiTextSnippet('latitude'); ?></label>
        <div class='col-sm-3'>
          <input class='form-control' id='latbox' name='latitude' type='text' value="<?php echo $row['latitude']; ?>">
        </div>
        <label class='col-form-label col-sm-2' for='longitude'><?php echo uiTextSnippet('longitude'); ?></label>
        <div class='col-sm-3'>
          <input class='form-control' id='lonbox' name='longitude' type='text' value="<?php echo $row['longitude']; ?>">
        </div>
      </div>
      <?php if ($map['key']) { ?>
        <div class='row'>
          <label class='col-form-label offset-sm-2 col-sm-2' for='zoom'><?php echo uiTextSnippet('zoom'); ?></label>
          <div class='col-sm-3'>
            <input class='form-control' id='zoombox' name='zoom' type='text' value="<?php echo $row['zoom']; ?>">
          </div>
          <label class='col-form-label col-sm-2' for='placelevel'><?php echo uiTextSnippet('placelevel'); ?></label>
          <div class='col-sm-3'>
            <select class='form-control' name='placelevel'>
              <option value=''></option>
              <?php
              for ($i = 1; $i < 7; $i++) {
                echo "<option value='$i'";
                if ($i === (int) $row['placelevel']) {
                  echo ' selected';
                }
                echo '>' . uiTextSnippet('level' . $i) . "</option>\n";
              }
              ?>
            </select>
          </div>
        </div>
      <?php } ?>

      <?php if ($map['key']) { ?>
        <hr>
        <div class='row'>
          <div class='col-sm-2'><?php echo uiTextSnippet('cemeteries'); ?></div>
          <div class='col-sm-10'>
            <table class='table table-sm' id='cemeteries'>
              <thead class='thead-default'>
                <tr>
                  <th><?php echo uiTextSnippet('action'); ?></th>
                  <th><?php echo uiTextSnippet('place'); ?></th>
                </tr>
              </thead>
              <tbody id="cemeteriestblbody">
              <?php

              $query = "SELECT cemeteryID, cemname, city, county, state, country FROM cemeteries WHERE place = \"{$row['place']}\" ORDER BY cemname";
              $cemresult = tng_query($query);
              while ($cemrow = tng_fetch_assoc($cemresult)) {
                $location = $cemrow['cemname'];
                if ($cemrow['city']) {
                  if ($location) {
                    $location .= ', ';
                  }
                  $location .= $cemrow['city'];
                }
                if ($cemrow['county']) {
                  if ($location) {
                    $location .= ', ';
                  }
                  $location .= $cemrow['county'];
                }
                if ($cemrow['state']) {
                  if ($location) {
                    $location .= ', ';
                  }
                  $location .= $cemrow['state'];
                }
                if ($cemrow['country']) {
                  if ($location) {
                    $location .= ', ';
                  }
                  $location .= $cemrow['country'];
                }
                $actionstr = '';
                if ($allowDelete) {
                  $actionstr .= "<a href='#' onclick=\"return deleteCemLink('{$cemrow['cemeteryID']}');\" title='" . uiTextSnippet('delete') . "'>\n";
                  $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
                  $actionstr .= '</a>';
                }
                if ($allowEdit) {
                  $actionstr .= "<a href='#' onclick=\"return copyGeoInfo('{$cemrow['cemeteryID']}');\"><img class='icon-sm' src='svg/globe.svg' id=\"geo{$cemrow['cemeteryID']}\" title=\"" . uiTextSnippet('geocopy') . '" alt="' . uiTextSnippet('geocopy') . '"></a>';
                }
                echo "<tr id=\"row_{$cemrow['cemeteryID']}\">\n";
                echo "<td>$actionstr</td>\n";
                echo "<td>$location</td>\n";
                echo "</tr>\n";
              }
              ?>
              </tbody>
            </table>
            <input class='btn btn-outline-primary' id='linkcem' type='button' value="<?php echo uiTextSnippet('linkcem'); ?>">
            <img class='icon-sm-inline' src='svg/globe.svg' title="<?php echo uiTextSnippet('geocopy'); ?>">
          </div>
        </div>
      <?php } ?>
      <hr>
      <div class='form-group row'>
        <label class='col-form-label col-sm-2' for='notes'><?php echo uiTextSnippet('notes'); ?></label>
        <div class='col-sm-10'>
          <textarea class='form-control' name='notes' rows='5'><?php echo $row['notes']; ?></textarea>
        </div>
      </div>
      <?php if (!$assignedbranch) { ?>
        <input name='propagate' type='checkbox' value='1' checked> <?php echo uiTextSnippet('propagate'); ?>:
        <br>
      <?php 
      }
      echo uiTextSnippet('onsave') . ':<br>';
      echo "<input name='newscreen' type='radio' value='return'> " . uiTextSnippet('savereturn') . "<br>\n";
      if ($cw) {
        echo "<input name='newscreen' type='radio' value='close' checked> " . uiTextSnippet('closewindow') . "\n";
      } else {
        echo "<input name='newscreen' type='radio' value='none' checked> " . uiTextSnippet('saveback') . "\n";
      }
      ?>
      <br>
      <input name='ID' type='hidden' value="<?php echo "$ID"; ?>">
      <input name='orgplace' type='hidden' value="<?php echo $row['place']; ?>">
      <br>
      <input class='btn btn-primary' name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<?php if ($map['key']) { ?>
  <script src='https://maps.googleapis.com/maps/api/js?key=AIzaSyAlWTL2QZDQv9BWXBvCwdAuhq1Lak8jSwU&amp;<?php echo uiTextSnippet('localize'); ?>'></script>
<?php } ?>
<script src="js/admin.js"></script>
<script>
  'use strict';
  function validateForm() {
    var rval = true;
    if (document.form1.place.value.length === 0) {
      alert(textSnippet('enterplace'));
      rval = false;
    }
    return rval;
  }

  function deleteCemLink(cemeteryID) {
    if (confirm(textSnippet('confdelcemlink'))) {
      deleteIt('cemlink', cemeteryID);
    }
  }

  function copyGeoInfo(cemeteryID) {
    var latitude = document.form1.latitude.value;
    var longitude = document.form1.longitude.value;
    var zoom = document.form1.zoom.value;
    var geo = $('#geo' + cemeteryID);
    geo.attr('src', 'img/spinner.gif');
    geo.css('height', '16px');
    geo.css('width', '16px');

    var params = {
      cemeteryID: cemeteryID,
      latitude: latitude,
      longitude: longitude,
      zoom: zoom,
      action: 'geocopy'
    };
    $.ajax({
      url: 'ajx_updateorder.php',
      data: params,
      dataType: 'json',
      success: function (vars) {
        //add new table row
        if (vars.result === 1) {
          geo.attr('src', 'img/tng_check.gif');
          geo.css('height', '18px');
          geo.css('width', '18px');
        } else
          alert("Sorry, an error occurred.");
      }
    });
    return false;
  }

  var tnglitbox;

  $('#linkcem').on('click', function () {
      'use strict';
      tnglitbox = new ModalDialog('cemeteriesSelectUnlinked.modal.php');
  });
  
  function insertCell(row, index, classname, content) {
    var cell = row.insertCell(index);
    cell.className = classname;
    cell.innerHTML = content ? content : content + '&nbsp;';
    return cell;
  }

  function addCemLink(cemeteryID) {
    'use strict';
    var place = '<?php echo urlencode($row['place']); ?>';
    var params = {cemeteryID: cemeteryID, place: place, action: 'addcemlink'};
    $.ajax({
      url: 'ajx_updateorder.php',
      data: params,
      dataType: 'json',
      success: function (vars) {
        //add new table row
        var cemtbl = document.getElementById('cemeteries');
        var newtr = cemtbl.insertRow(cemtbl.rows.length);
        newtr.id = "row_" + cemeteryID;
        var actionstr = '<a href="#" onclick="return deleteCemLink(\'' + cemeteryID + '\');" title="' + textSnippet('delete') + '">\n';
        actionstr += '<img class="icon-sm" src="svg/trash.svg">\n';
        actionstr += '</a>';
        actionstr += '<a href="#" onclick="return copyGeoInfo(\'' + cemeteryID + '\');\"><img class="icon-sm" id="geo' + cemeteryID + '" src="svg/globe.svg" title="' + textSnippet('geocopy') + '" alt="' + textSnippet('geocopy') + '"></a>';
        insertCell(newtr, 0, "nw", actionstr);
        insertCell(newtr, 1, "nw", vars.location);
        tnglitbox.remove();
        var tds = $('tr#row_' + cemeteryID + ' td');
        $.each(tds, function (index, item) {
          item.effect('highlight', {}, 1400);
        });
      }
    });
    return false;
  }
</script>
<?php
if ($map['key']) {
  include 'googlemaplib2.php';
}
?>
</body>
</html>