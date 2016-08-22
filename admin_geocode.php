<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

require 'geocodelib.php';
require 'adminlog.php';

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('places'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('places-geocode', $message);
    $navList = new navList('');
    $navList->appendItem([true, "placesBrowse.php", uiTextSnippet('browse'), "findplace"]);
    $navList->appendItem([$allowAdd, "placesAdd.php", uiTextSnippet('add'), "addplace"]);
    $navList->appendItem([$allowEdit && $allowDelete, "placesMerge.php", uiTextSnippet('merge'), "merge"]);
    $navList->appendItem([$allowEdit, "admin_geocodeform.php", uiTextSnippet('geocode'), "geo"]);
    echo $navList->build("geo");
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <h4><?php echo uiTextSnippet('geocoding'); ?></h4><br>
          <div>
            <?php
            $limitstr = $limit ? "LIMIT $limit" : "";

            $query = "SELECT ID, place FROM $places_table WHERE (latitude = \"\" OR latitude IS NULL) AND (longitude = \"\" OR longitude IS NULL) AND temple != \"1\" AND geoignore != \"1\" ORDER BY place $limitstr";
            $result = tng_query($query);

            $delay = 0;
            $count = 0;

            adminwritelog("<a href=\"admin_geocode.php\">" . uiTextSnippet('geoexpl') . " ($limit)</a>");

            while ($row = tng_fetch_assoc($result)) {
              $count++;
              $address = trim($row["place"]);
              if ($address) {
                $id = $row["ID"];
                $display = $address;
                $display = preg_replace("/</", "&lt;", $display);
                $display = preg_replace("/>/", "&gt;", $display);
                echo "<br>\n$count. $display ... &nbsp; ";
                echo geocode($address, $multiples, $id);
              } else {
                echo "<br>\n$count. " . uiTextSnippet('blankplace') . " &nbsp; <strong>" . uiTextSnippet('nogeocode') . "</strong>";
              }
            }
            tng_free_result($result);
            ?>
          </div>
          <p><a href="admin_geocodeform.php"><?php echo uiTextSnippet('backgeo'); ?></a></p>
        </td>
      </tr>
    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/admin.js"></script>
</body>
</html>
