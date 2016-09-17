<?php
require 'tng_begin.php';

$flnqualify = $_SESSION['tng_search_flnqualify'];
$myflastname = $_SESSION['tng_search_flastname'];
$ffnqualify = $_SESSION['tng_search_ffnqualify'];
$myffirstname = $_SESSION['tng_search_ffirstname'];
$mlnqualify = $_SESSION['tng_search_mlnqualify'];
$mymlastname = $_SESSION['tng_search_mlastname'];
$mfnqualify = $_SESSION['tng_search_mfnqualify'];
$mymfirstname = $_SESSION['tng_search_mfirstname'];
$fidqualify = $_SESSION['tng_search_fidqualify'];
$myfamilyid = $_SESSION['tng_search_familyid'];
$mpqualify = $_SESSION['tng_search_mpqualify'];
$mymarrplace = $_SESSION['tng_search_marrplace'];
$myqualify = $_SESSION['tng_search_myqualify'];
$mymarryear = $_SESSION['tng_search_marryear'];
$dvpqualify = $_SESSION['tng_search_dvpqualify'];
$mydivplace = $_SESSION['tng_search_divhplace'];
$dvyqualify = $_SESSION['tng_search_dvyqualify'];
$mydivyear = $_SESSION['tng_search_divyear'];
$mybool = $_SESSION['tng_search_fbool'];
$nr = $_SESSION['tng_nr'];

$dontdo = ['MARR', 'DIV'];

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

function buildSelectInputGroup($label, $formName, $selectName, $value, $options, $selected) {
  $out = "<label for='" . $formName . "'>" . uiTextSnippet($label) . "</label>\n";
  $out .= "<div class='input-group'>\n";
  $out .= "<input class='form-control search-qualify-combo' name='" . $formName . "' type='text' value='" . $value . "' placeholder='" . uiTextSnippet($label) . "'>\n";
  $out .= "<span class='input-group-select'>\n";
  $out .= "<select class='form-control' name='" . $selectName . "'>\n";
  foreach ($options as $option) {
    $out .= "<option value='$option'" . ($selected == $option ? ' selected' : '') . '>' . uiTextSnippet($option) . "</option>\n";
  }
  $out .= "</select>\n";
  $out .= "</span>\n";
  $out .= "</div>\n";
  
  return $out;
}

$idOptions = ['contains', 'equals', 'startswith', 'endswith'];
$placeOptions = ['contains', 'equals', 'startswith', 'endswith', 'exists', 'dnexist'];
$fnOptions = ['contains', 'equals', 'startswith', 'endswith', 'exists', 'dnexist', 'soundexof'];
$lnOptions = ['contains', 'equals', 'startswith', 'endswith', 'exists', 'dnexist', 'soundexof', 'metaphoneof'];

$yearOptions = ['equals', 'plusminus2', 'plusminus5', 'plusminus10', 'lessthan', 'greaterthan', 'lessthanequal', 'greaterthanequal', 'exists', 'dnexist'];

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('searchfams'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='search-families'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/magnifying-glass.svg'><?php echo uiTextSnippet('searchfams'); ?></h2>
    <?php
    if ($msg) {
      echo "<b id='errormsg' class='msgerror h4'>" . stripslashes(strip_tags($msg)) . '</b>';
    }
    ?>
    <form action="famsearch.php" name="famsearch" onsubmit="return makeURL();">
      <div id='searchform'>
        <div class='father-name'>
          <fieldset>
            <legend><?php echo uiTextSnippet('fathername'); ?></legend>
            <div class='row'>
              <div class='col-sm-6'>
                <?php echo buildSelectInputGroup('lastname', 'myflastname', 'flnqualify', $myflastname, $lnOptions, $flnqualify); ?>
              </div>
              <div class='col-sm-6'>
                <?php echo buildSelectInputGroup('firstname', 'myffirstname', 'ffnqualify', $myffirstname, $fnOptions, $ffnqualify); ?>
              </div>
          </fieldset>
        </div>
        <div class='mother-name'>
          <fieldset>
            <legend><?php echo uiTextSnippet('mothername'); ?></legend>
            <div class='row'>
              <div class='col-sm-6'>
                <?php echo buildSelectInputGroup('lastname', 'mymlastname', 'mlnqualify', $mymlastname, $lnOptions, $mlnqualify); ?>
              </div>
              <div class='col-sm-6'>
                <?php echo buildSelectInputGroup('firstname', 'mymfirstname', 'mfnqualify', $mymfirstname, $fnOptions, $mfnqualify); ?>
              </div>
            </div>
          </fieldset>
        </div>
        <div class='row'>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('familyid', 'myfamilyid', 'fidqualify', $myfamilyid, $idOptions, $fidqualify); ?>
          </div>
        </div>
          <br>
          <hr>
          <br>
        <div class='row'>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('marrplace', 'mymarrplace', 'mpqualify', $mymarrplace, $placeOptions, $mpqualify); ?>
          </div>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('marrdatetr', 'mymarryear', 'myqualify', $mymarryear, $yearOptions, $myqualify); ?>

          </div>
        </div>
        <div class='row'>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('divplace', 'mydivplace', 'dvpqualify', $mydivplace, $placeOptions, $dvpqualify); ?>
          </div>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('divdatetr', 'mydivyear', 'dvyqualify', $mydivyear, $yearOptions, $dvyqualify); ?>
          </div>
        </div>

        <input name='offset' type='hidden' value='0'>

        <section class='custom-events'>
          <div class='h4'><?php echo uiTextSnippet('customeventtypes'); ?>
            <span id="expand">
              <a href="#" onclick="return toggleSection(1);">
                <img class='icon-sm pull-xs-right' src='svg/expand.svg' alt=""></a>
            </span>
            <span id="contract" style="display: none;">
              <a href="#" onclick="return toggleSection(0);">
                <img class='icon-sm  pull-xs-right' src="svg/collapse.svg" alt=""></a>
            </span>
          </div>
          <div id='otherevents' style='display: none'>
            <?php
            $query = "SELECT eventtypeID, tag, display FROM eventtypes WHERE keep=\"1\" AND type=\"F\" ORDER BY display";
            $result = tng_query($query);
            $eventtypes = [];
            while ($row = tng_fetch_assoc($result)) {
              if (!in_array($row['tag'], $dontdo)) {
                $row['displaymsg'] = getEventDisplay($row['display']);
                $displaymsg = strtoupper($row['displaymsg']) . '_' . $row['eventtypeID'];
                $eventtypes[$displaymsg] = $row;
              }
            }
            tng_free_result($result);
            ksort($eventtypes);

            foreach ($eventtypes as $row) {
              echo "<div class='custom-events'>\n";
                echo "<div class='row'>\n";
                  echo "<div class='col-sm-12'><h6>{$row['displaymsg']}</h6>\n";
                echo "</div>\n";
                echo "<div class='row'>\n";
                  echo "<div class='offset-sm-1 col-sm-2'>" . uiTextSnippet('fact') . ":</div>\n";
                  echo "<div class='col-sm-3'>\n";
                    echo "<select class='form-control' name=\"cfq{$row['eventtypeID']}\">\n";
                      foreach ($item_array as $item) {
                        echo "<option value='$item[1]'";
                        echo ">$item[0]</option>\n";
                      }
                    echo "</select>\n";
                  echo "</div>\n";
                  echo "<div class='col-sm-6'>\n";
                    echo "<input class='form-control' name=\"cef{$row['eventtypeID']}\" type='text' value='' />\n";
                  echo "</div>\n";
                echo "</div>\n";
                echo "<div class='row'>\n";
                  echo "<div class='offset-sm-1 col-sm-2'>" . uiTextSnippet('place') . ":</div>\n";
                  echo "<div class='col-sm-3'>\n";
                    echo "<select class='form-control' name=\"cpq{$row['eventtypeID']}\">\n";
                      foreach ($item_array as $item) {
                        echo "<option value='$item[1]'";
                        echo ">$item[0]</option>\n";
                      }
                    echo "</select>\n";
                  echo "</div>\n";
                  echo "<div class='col-sm-6'>\n";
                    echo "<input class='form-control' name=\"cep{$row['eventtypeID']}\" type='text' value='' />\n";
                  echo "</div>\n";
                echo "</div>\n";
                echo "<div class='row'>\n";
                  echo "<div class='offset-sm-1 col-sm-2'>" . uiTextSnippet('year') . ":</div>\n";
                  echo "<div class='col-sm-3'>\n";
                    echo "<select class='form-control' name=\"cyq{$row['eventtypeID']}\">\n";
                      $item2_array = [[uiTextSnippet('equals'), ''], [uiTextSnippet('plusminus2'), 'plusminus2'], [uiTextSnippet('plusminus5'), 'plusminus5'], [uiTextSnippet('plusminus10'), 'plusminus10'], [uiTextSnippet('lessthan'), 'lessthan'], [uiTextSnippet('greaterthan'), 'greaterthan'], [uiTextSnippet('lessthanequal'), 'lessthanequal'], [uiTextSnippet('greaterthanequal'), 'greaterthanequal'], [uiTextSnippet('exists'), 'exists'], [uiTextSnippet('dnexist'), 'dnexist']];
                      foreach ($item2_array as $item) {
                        echo "<option value='$item[1]'";
                        echo ">$item[0]</option>\n";
                      }
                    echo "</select>\n";
                  echo "</div>\n";
                  echo "<div class='col-sm-6'>\n";
                    echo "<input class='form-control' name=\"cey$row[eventtypeID]\" type='text' value='' />\n";
                  echo "</div>\n";
                echo "</div>\n";
              echo "</div> <!-- .custom-event -->\n";
            }
            ?>
          </div>
          <div class="row secondsearch">
            <div class='offset-sm-6 col-sm-3'>
              <button class='btn btn-outline-primary' onclick="return makeURL();"><?php echo uiTextSnippet('search'); ?></button>
            </div>
            <div class='col-sm-3'>
              <button class='btn btn-outline-warning' onclick="resetValues();"><?php echo uiTextSnippet('resetall'); ?></button>
            </div>
          </div>
        </section> <!-- .custom-events -->
      </div>
      <br>
      <hr>
      <footer class='row'>
        <div class='col-sm-2'><?php echo uiTextSnippet('numresults'); ?>:</div>
        <div class='col-sm-2'>
          <select class='form-control' name="nr">
            <?php
            $item3_array = [[25, 25], [50, 50], [100, 100], [200, 200]];
            foreach ($item3_array as $item) {
              echo "<option value='$item[1]'";
              if ($nr == $item[1]) {
                echo ' selected';
              }
              echo ">$item[0]</option>\n";
            }
            ?>
          </select>
        </div>
        <div class='col-sm-2'><?php echo uiTextSnippet('joinwith'); ?>:</div>
        <div class='col-sm-2'>
          <select class='form-control' name='mybool'>
            <?php
            $item3_array = [[uiTextSnippet('cap_and'), 'AND'], [uiTextSnippet('cap_or'), 'OR']];
            foreach ($item3_array as $item) {
              echo "<option value='$item[1]'";
              if ($mybool == $item[1]) {
                echo ' selected';
              }
              echo ">$item[0]</option>\n";
            }
            ?>
          </select>
        </div>
        <div class='col-sm-2'>
          <button class='btn btn-outline-primary' id='searchbtn' type='submit'><?php echo uiTextSnippet('search'); ?></button>
        </div>
        <div class='col-sm-2'>
          <button class='btn btn-outline-warning' id='resetbtn' type='button' 
                  onclick="resetValues();"><?php echo uiTextSnippet('tng_reset'); ?></button>
        </div>
      </footer>
    </form>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script>
    function resetValues() {
      document.famsearch.flnqualify.selectedIndex = 0;
      document.famsearch.ffnqualify.selectedIndex = 0;
      document.famsearch.mlnqualify.selectedIndex = 0;
      document.famsearch.mfnqualify.selectedIndex = 0;
      document.famsearch.mpqualify.selectedIndex = 0;
      document.famsearch.myqualify.selectedIndex = 0;
      document.famsearch.dvpqualify.selectedIndex = 0;
      document.famsearch.dvyqualify.selectedIndex = 0;
      document.famsearch.mybool.selectedIndex = 0;
      document.famsearch.fidqualify.selectedIndex = 0;

      document.famsearch.myflastname.value = '';
      document.famsearch.myffirstname.value = '';
      document.famsearch.mymlastname.value = '';
      document.famsearch.mymfirstname.value = '';
      document.famsearch.mymarrplace.value = '';
      document.famsearch.mymarryear.value = '';
      document.famsearch.mydivplace.value = '';
      document.famsearch.mydivyear.value = '';
      document.famsearch.myfamilyid.value = '';
      $('errormsg').style.display = "none";
    }

    function toggleSection(flag) {
      if (flag) {
        $('#otherevents').fadeIn(200);
        $('#contract').show();
        $('#expand').hide();
      } else {
        $('#otherevents').fadeOut(200);
        $('#expand').show();
        $('#contract').hide();
      }
      return false;
    }

    function makeURL() {
      var URL;
      var thisform = document.famsearch;
      var thisfield;
      var found = 0;

      URL = "mybool=" + thisform.mybool[thisform.mybool.selectedIndex].value;
      URL = URL + "&nr=" + thisform.nr[thisform.nr.selectedIndex].value;
      <?php
      $qualifiers = ['fln', 'ffn', 'mln', 'mfn', 'fid', 'mp', 'my', 'dvp', 'dvy'];
      $criteria = ['flastname', 'ffirstname', 'mlastname', 'mfirstname', 'familyid', 'marrplace', 'marryear', 'divplace', 'divyear'];

      $qcount = 0;
      $found = 0;
      foreach ($criteria as $criterion) {
      ?>
      if (thisform.my<?php echo $criterion; ?>.value !== '' || thisform.<?php echo $qualifiers[$qcount]; ?>qualify.value === 'exists' || thisform.<?php echo $qualifiers[$qcount]; ?>qualify.value === 'dnexist') {
        URL = URL + "&my<?php echo $criterion; ?>=" + thisform.my<?php echo $criterion; ?>.value;
        URL = URL + "&<?php echo $qualifiers[$qcount]; ?>qualify=" + thisform.<?php echo $qualifiers[$qcount]; ?>qualify[thisform.<?php echo $qualifiers[$qcount]; ?>qualify.selectedIndex].value;
        found++;
      }
      <?php
      $qcount++;
      }

      $query = "SELECT eventtypeID, tag FROM eventtypes WHERE keep=\"1\" AND type=\"F\"";
      $etresult = tng_query($query);
      while ($row = tng_fetch_assoc($etresult)) {
      if (!in_array($row[tag], $dontdo)) {
      ?>
      if (thisform.cef<?php echo $row['eventtypeID']; ?>.value !== '' || thisform.cfq<?php echo $row['eventtypeID']; ?>.value === 'exists' || thisform.cfq<?php echo $row['eventtypeID']; ?>.value === 'dnexist') {
        URL = URL + "&cef<?php echo $row['eventtypeID']; ?>=" + thisform.cef<?php echo $row['eventtypeID']; ?>.value;
        URL = URL + "&cfq<?php echo $row['eventtypeID']; ?>=" + thisform.cfq<?php echo $row['eventtypeID']; ?>[thisform.cfq<?php echo $row['eventtypeID']; ?>.selectedIndex].value;
      }
      if (thisform.cep<?php echo $row['eventtypeID']; ?>.value !== '' || thisform.cpq<?php echo $row['eventtypeID']; ?>.value === 'exists' || thisform.cpq<?php echo $row['eventtypeID']; ?>.value === 'dnexist') {
        URL = URL + "&cep<?php echo $row['eventtypeID']; ?>=" + thisform.cep<?php echo $row['eventtypeID']; ?>.value;
        URL = URL + "&cpq<?php echo $row['eventtypeID']; ?>=" + thisform.cpq<?php echo $row['eventtypeID']; ?>[thisform.cpq<?php echo $row['eventtypeID']; ?>.selectedIndex].value;
      }
      if (thisform.cey<?php echo $row['eventtypeID']; ?>.value !== '' || thisform.cyq<?php echo $row['eventtypeID']; ?>.value === 'exists' || thisform.cyq<?php echo $row['eventtypeID']; ?>.value === 'dnexist') {
        URL = URL + "&cey<?php echo $row['eventtypeID']; ?>=" + thisform.cey<?php echo $row['eventtypeID']; ?>.value;
        URL = URL + "&cyq<?php echo $row['eventtypeID']; ?>=" + thisform.cyq<?php echo $row['eventtypeID']; ?>[thisform.cyq<?php echo $row['eventtypeID']; ?>.selectedIndex].value;
      }
      <?php
      }
      }
      tng_free_result($etresult);
      ?>
      window.location.href = "famsearch.php?" + URL;

      return false;
    }
  </script>
</body>
</html>
