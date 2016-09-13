<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if ($place) {
  setcookie("tng_merge_places_post[place]", $place, 0);
  setcookie("tng_merge_places_post[place2]", $place2, 0);

  $pwherestr = "place LIKE \"%$place%\"";
  if ($place2) {
    $pwherestr = "($pwherestr OR place LIKE \"%$place2%\")";
  }
  $query = "SELECT ID, place, longitude, latitude, gedcom FROM $places_table
    WHERE ";
  $query .= $pwherestr . ' ORDER BY place, gedcom, ID';
  $result = tng_query($query);

  $numrows = tng_num_rows($result);
  if (!$numrows) {
    $message = uiTextSnippet('noresults');
  }
} else {
  $numrows = 0;
  if ($_COOKIE['tng_merge_places_post']['place']) {
    $place = stripslashes($_COOKIE['tng_merge_places_post']['place']);
    $place2 = stripslashes($_COOKIE['tng_merge_places_post']['place2']);
  } else {
    $place = $_COOKIE['tng_search_places_post']['search'];
  }
}
header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('mergeplaces'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('places-mergeplaces', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'placesBrowse.php', uiTextSnippet('browse'), 'findplace']);
    $navList->appendItem([$allowAdd, 'placesAdd.php', uiTextSnippet('add'), 'addplace']);
    $navList->appendItem([$allowEdit && $allowDelete, 'placesMerge.php', uiTextSnippet('merge'), 'merge']);
    $navList->appendItem([$allowEdit, 'admin_geocodeform.php', uiTextSnippet('geocode'), 'geo']);
    echo $navList->build('merge');
    ?>

    <h4>1. <?php echo uiTextSnippet('findmerge'); ?></h4>

    <form action="placesMerge.php" method='post' name="form1" onSubmit="return validateForm1();">
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('searchfor'); ?>:</td>
          <td><input name='place' type='text' size='50' value="<?php echo stripslashes($place); ?>">
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('or'); ?>:</td>
          <td><input name='place2' type='text' size='50' value="<?php echo stripslashes($place2); ?>">
          </td>
        </tr>
      </table>
      <br>
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('text_continue'); ?>">
    </form>
    <?php
    if ($place && $numrows) {
      ?>
      <br><br>

      <h4>2. <?php echo uiTextSnippet('selectplacemerge'); ?></h4>

      <form name='form2' action='' method='post' onSubmit="return validateForm2(this);">
        <p>
          <input type='submit' value="<?php echo uiTextSnippet('mergeplaces'); ?>"> 
          <img src="img/spinner.gif" id="placespin" style='display: none'>
          <span class='msgapproved' id='successmsg1'></span></p>
        <table class='table table-sm'>
          <thead>
            <tr>
              <th><span><?php echo uiTextSnippet('mcol1'); ?></span></th>
              <th><span><?php echo uiTextSnippet('mcol2'); ?></span></th>
              <th><span><?php echo uiTextSnippet('place'); ?></span></th>
              <th><span><?php echo uiTextSnippet('latitude'); ?></span></th>
              <th><span><?php echo uiTextSnippet('longitude'); ?></span></th>
            </tr>
          </thead>
          <?php
          while ($row = tng_fetch_assoc($result)) {
            echo "<tr class=\"mergerows\" id=\"row_{$row['ID']}\">\n";
            echo "<td><input class='mc' name=\"mc{$row['ID']}\" type='checkbox' onclick=\"handleCheck({$row['ID']});\" value=\"{$row['ID']}\"></td>\n";
            echo "<td><input id=\"r{$row['ID']}\" name='keep' type='radio' onclick=\"handleRadio({$row['ID']});\" value=\"{$row['ID']}\"></td>\n";
            $display = $row['place'];
            $display = preg_replace('/</', '&lt;', $display);
            $display = preg_replace('/>/', '&gt;', $display);
            echo "<td>$display&nbsp;</td>\n";
            echo "<td id=\"lat_{$row['ID']}\">{$row['latitude']}&nbsp;</td>\n";
            echo "<td id=\"long_{$row['ID']}\">{$row['longitude']}&nbsp;</td>\n";
            echo "</tr>\n";
          }
          tng_free_result($result);
          ?>
        </table>
        <br>
        <input type='submit' value="<?php echo uiTextSnippet('mergeplaces'); ?>">
        <span id="successmsg2" class="msgapproved"></span>
      </form>
      <?php
    }
    ?>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/mergeplaces.js"></script>
</body>
</html>
