<?php
include("tng_begin.php");

$query = "SELECT gedcom, treename FROM $trees_table ORDER BY treename";
$result = tng_query($query);
$numtrees = tng_num_rows($result);

if ($_SESSION['tng_search_ftree']) {
  $tree = $_SESSION['tng_search_ftree'];
}
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

$dontdo = array("MARR", "DIV");

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
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
      echo "<b id='errormsg' class='msgerror h4'>" . stripslashes(strip_tags($msg)) . "</b>";
    }
    ?>
    <form action="famsearch.php" name="famsearch" onsubmit="return makeURL();">
      <div id='searchform'>
        <?php if ((!$requirelogin || !$treerestrict || !$assignedtree) && $numtrees > 1) { ?>
          <div class='row'>
            <div class='col-md-offset-8 col-md-4'>
              <?php echo treeSelect($result); ?>
            </div>
          </div>
        <?php } ?>
        <div class='father-name'>
          <fieldset>
            <legend><?php echo uiTextSnippet('fathername'); ?></legend>
            <div class='row'>
              <label class='form-control-label col-sm-2' for='myflastname'><?php echo uiTextSnippet('lastname'); ?>:</label>
              <div class='col-sm-3'>
                <select class='form-control' name='flnqualify'>
                  <?php
                  $item_array = array(array(uiTextSnippet('contains'), "contains"), array(uiTextSnippet('equals'), "equals"), array(uiTextSnippet('startswith'), "startswith"), array(uiTextSnippet('endswith'), "endswith"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"), array(uiTextSnippet('soundexof'), "soundexof"), array(uiTextSnippet('metaphoneof'), "metaphoneof"));
                  foreach ($item_array as $item) {
                    echo "<option value='$item[1]'";
                    if ($flnqualify == $item[1]) {
                      echo " selected";
                    }
                    echo ">$item[0]</option>\n";
                  }
                  ?>
                </select>
              </div>
              <div class='col-sm-6'>
                <input class='form-control' name='myflastname' type='text' value="<?php echo $myflastname; ?>">
              </div>
            </div>        
            <div class='row'>
              <label class='form-control-label col-sm-2' for='myffirstname'><?php echo uiTextSnippet('firstname'); ?>:</label>
              <div class='col-sm-3'>
                <select class='form-control' name="ffnqualify">
                  <?php
                  $item_array = array(array(uiTextSnippet('contains'), "contains"), array(uiTextSnippet('equals'), "equals"), array(uiTextSnippet('startswith'), "startswith"), array(uiTextSnippet('endswith'), "endswith"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"), array(uiTextSnippet('soundexof'), "soundexof"));
                  foreach ($item_array as $item) {
                    echo "<option value='$item[1]'";
                    if ($ffnqualify == $item[1]) {
                      echo " selected";
                    }
                    echo ">$item[0]</option>\n";
                  }
                  ?>
                </select>
              </div>
              <div class='col-sm-6'>
                <input class='form-control' name='myffirstname' type='text' value="<?php echo $myffirstname; ?>">
              </div>
            </div>
          </fieldset>
        </div>
        <div class='mother-name'>
          <fieldset>
            <legend><?php echo uiTextSnippet('mothername'); ?></legend>
            <div class='row'>
              <label class='form-control-label col-sm-2' for='mymlastname'><?php echo uiTextSnippet('lastname'); ?>:</label>
              <div class='col-sm-3'>
                <select class='form-control' name='mlnqualify'>
                  <?php
                  $item_array = array(array(uiTextSnippet('contains'), "contains"), array(uiTextSnippet('equals'), "equals"), array(uiTextSnippet('startswith'), "startswith"), array(uiTextSnippet('endswith'), "endswith"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"), array(uiTextSnippet('soundexof'), "soundexof"), array(uiTextSnippet('metaphoneof'), "metaphoneof"));
                  foreach ($item_array as $item) {
                    echo "<option value='$item[1]'";
                    if ($mlnqualify == $item[1]) {
                      echo " selected";
                    }
                    echo ">$item[0]</option>\n";
                  }
                  ?>
                </select>
              </div>
              <div class='col-sm-6'>
                <input class='form-control' name='mymlastname' type='text' value="<?php echo $mymlastname; ?>">
              </div>
            </div>
            <div class='row'>
              <div class='form-control-label col-sm-2'><?php echo uiTextSnippet('firstname'); ?>:</div>
              <div class='col-sm-3'>
                <select class='form-control' name='mfnqualify'>
                  <?php
                  $item_array = array(array(uiTextSnippet('contains'), "contains"), array(uiTextSnippet('equals'), "equals"), array(uiTextSnippet('startswith'), "startswith"), array(uiTextSnippet('endswith'), "endswith"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"), array(uiTextSnippet('soundexof'), "soundexof"));
                  foreach ($item_array as $item) {
                    echo "<option value='$item[1]'";
                    if ($mfnqualify == $item[1]) {
                      echo " selected";
                    }
                    echo ">$item[0]</option>\n";
                  }
                  ?>
                </select>
              </div>
              <div class='col-sm-6'>
                <input class='form-control' name='mymfirstname' type='text' value="<?php echo $mymfirstname; ?>">
              </div>
            </div>
          </fieldset>
        </div>
        <div class='form-group row'>
          <label class='form-control-label col-sm-2' for='myfamilyid'><?php echo uiTextSnippet('familyid'); ?>:</label>
          <div class='col-sm-3'>
            <select class='form-control' name='fidqualify' title='<?php echo $fidqualify; ?>'>
              <?php
              $item_array = array(array(uiTextSnippet('equals'), "equals"), array(uiTextSnippet('contains'), "contains"), array(uiTextSnippet('startswith'), "startswith"), array(uiTextSnippet('endswith'), "endswith"));
              foreach ($item_array as $item) {
                echo "<option value='$item[1]'";
                if ($fidqualify == $item[1]) {
                  echo " selected";
                }
                echo ">$item[0]</option>\n";
              }
              ?>
            </select>
          </div>
          <div class='col-sm-6'>
            <input class='form-control' name='myfamilyid' type='text' value="<?php echo $myfamilyid; ?>">
          </div>
        </div>
        <div class='row'>
          <label class='form-control-label col-sm-2' for='mymarrplace'><?php echo uiTextSnippet('marrplace'); ?>:</label>
          <div class='col-sm-3'>
            <select class='form-control' name='mpqualify'>
              <?php
              $item_array = array(array(uiTextSnippet('contains'), "contains"), array(uiTextSnippet('equals'), "equals"), array(uiTextSnippet('startswith'), "startswith"), array(uiTextSnippet('endswith'), "endswith"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"));
              foreach ($item_array as $item) {
                echo "<option value='$item[1]'";
                if ($mpqualify == $item[1]) {
                  echo " selected";
                }
                echo ">$item[0]</option>\n";
              }
              ?>
            </select>
          </div>
          <div class='col-sm-6'>
            <input class='form-control' name='mymarrplace' type='text' value='<?php echo $mymarrplace; ?>'>
          </div>
        </div>
        <div class='row'>
          <label class='form-control-label col-sm-2' for='mymarryear'><?php echo uiTextSnippet('marrdatetr'); ?>:</label>
          <div class='col-sm-3'>
            <select class='form-control' name='myqualify'>
              <?php
              $item2_array = array(array(uiTextSnippet('equals'), ""), array(uiTextSnippet('plusminus2'), "plusminus2"), array(uiTextSnippet('plusminus5'), "plusminus5"), array(uiTextSnippet('plusminus10'), "plusminus10"), array(uiTextSnippet('lessthan'), "lessthan"), array(uiTextSnippet('greaterthan'), "greaterthan"), array(uiTextSnippet('lessthanequal'), "lessthanequal"), array(uiTextSnippet('greaterthanequal'), "greaterthanequal"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"));
              foreach ($item2_array as $item) {
                echo "<option value='$item[1]'";
                if ($myqualify == $item[1]) {
                  echo " selected";
                }
                echo ">$item[0]</option>\n";
              }
              ?>
            </select>
          </div>
          <div class='col-sm-6'>
            <input class='form-control' name='mymarryear' type='text' value='<?php echo $mymarryear; ?>'>
          </div>
        </div>
        <div class='row'>
          <label class='form-control-label col-sm-2' for='mydivplace'><?php echo uiTextSnippet('divplace'); ?>:</label>
          <div class='col-sm-3'>
            <select class='form-control' name='dvpqualify'>
              <?php
              $item_array = array(array(uiTextSnippet('contains'), "contains"), array(uiTextSnippet('equals'), "equals"), array(uiTextSnippet('startswith'), "startswith"), array(uiTextSnippet('endswith'), "endswith"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"));
              foreach ($item_array as $item) {
                echo "<option value='$item[1]'";
                if ($dvpqualify == $item[1]) {
                  echo " selected";
                }
                echo ">$item[0]</option>\n";
              }
              ?>
            </select>
          </div>
          <div class='col-sm-6'>
            <input class='form-control' name='mydivplace' type='text' value='<?php echo $mydivplace; ?>'>
          </div>
        </div>
        <div class='row'>
          <label class='form-control-label col-sm-2' for='mydivyear'><?php echo uiTextSnippet('divdatetr'); ?>:</label>
          <div class='col-sm-3'>
            <select class='form-control' name='dvyqualify'>
              <?php
              $item2_array = array(array(uiTextSnippet('equals'), ""), array(uiTextSnippet('plusminus2'), "plusminus2"), array(uiTextSnippet('plusminus5'), "plusminus5"), array(uiTextSnippet('plusminus10'), "plusminus10"), array(uiTextSnippet('lessthan'), "lessthan"), array(uiTextSnippet('greaterthan'), "greaterthan"), array(uiTextSnippet('lessthanequal'), "lessthanequal"), array(uiTextSnippet('greaterthanequal'), "greaterthanequal"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"));
              foreach ($item2_array as $item) {
                echo "<option value='$item[1]'";
                if ($dvyqualify == $item[1]) {
                  echo " selected";
                }
                echo ">$item[0]</option>\n";
              }
              ?>
            </select>
          </div>
          <div class='col-sm-6'>
            <input class='form-control' name='mydivyear' type='text' value='<?php echo $mydivyear; ?>'>
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
            $query = "SELECT eventtypeID, tag, display FROM $eventtypes_table WHERE keep=\"1\" AND type=\"F\" ORDER BY display";
            $result = tng_query($query);
            $eventtypes = array();
            while ($row = tng_fetch_assoc($result)) {
              if (!in_array($row['tag'], $dontdo)) {
                $row['displaymsg'] = getEventDisplay($row['display']);
                $displaymsg = strtoupper($row['displaymsg']) . "_" . $row['eventtypeID'];
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
                  echo "<div class='col-sm-offset-1 col-sm-2'>" . uiTextSnippet('fact') . ":</div>\n";
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
                  echo "<div class='col-sm-offset-1 col-sm-2'>" . uiTextSnippet('place') . ":</div>\n";
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
                  echo "<div class='col-sm-offset-1 col-sm-2'>" . uiTextSnippet('year') . ":</div>\n";
                  echo "<div class='col-sm-3'>\n";
                    echo "<select class='form-control' name=\"cyq{$row['eventtypeID']}\">\n";
                      $item2_array = array(array(uiTextSnippet('equals'), ""), array(uiTextSnippet('plusminus2'), "plusminus2"), array(uiTextSnippet('plusminus5'), "plusminus5"), array(uiTextSnippet('plusminus10'), "plusminus10"), array(uiTextSnippet('lessthan'), "lessthan"), array(uiTextSnippet('greaterthan'), "greaterthan"), array(uiTextSnippet('lessthanequal'), "lessthanequal"), array(uiTextSnippet('greaterthanequal'), "greaterthanequal"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"));
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
            <div class='col-sm-offset-6 col-sm-3'>
              <button class='btn btn-primary-outline' onclick="return makeURL();"><?php echo uiTextSnippet('search'); ?></button>
            </div>
            <div class='col-sm-3'>
              <button class='btn btn-warning-outline' onclick="resetValues();"><?php echo uiTextSnippet('resetall'); ?></button>
            </div>
          </div>
        </section> <!-- .custom-events -->
      </div>
      <div class="searchsidebar">
        <div class='row'>
          <div class='col-sm-3'><?php echo uiTextSnippet('joinwith'); ?>:</div>
          <div class='col-sm-3'>
            <select class='form-control' name='mybool'>
              <?php
              $item3_array = array(array(uiTextSnippet('cap_and'), "AND"), array(uiTextSnippet('cap_or'), "OR"));
              foreach ($item3_array as $item) {
                echo "<option value='$item[1]'";
                if ($mybool == $item[1]) {
                  echo " selected";
                }
                echo ">$item[0]</option>\n";
              }
              ?>
            </select>
          </div>
        </div>
        <div class='row'>
          <div class='col-sm-3'><?php echo uiTextSnippet('numresults'); ?>:</div>
          <div class='col-sm-3'>
            <select class='form-control' name="nr">
              <?php
              $item3_array = array(array(50, 50), array(100, 100), array(150, 150), array(200, 200));
              foreach ($item3_array as $item) {
                echo "<option value='$item[1]'";
                if ($nr == $item[1]) {
                  echo " selected";
                }
                echo ">$item[0]</option>\n";
              }
              ?>
            </select>
          </div>
        </div>
      </div>
      <footer class='row'>
        <div class='col-sm-offset-6 col-sm-3'>
          <button class='btn btn-primary-outline' id='searchbtn' type='submit'><?php echo uiTextSnippet('search'); ?></button>
        </div>
        <div class='col-sm-3'>
          <button class='btn btn-warning-outline' id='resetbtn' type='button' 
                  onclick="resetValues();"><?php echo uiTextSnippet('tng_reset'); ?></button>
        </div>
      </footer>
    </form>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script>
    function resetValues() {
      <?php if ((!$requirelogin || !$treerestrict || !$assignedtree) && $numtrees > 1) {
      echo "  document.famsearch.tree.selectedIndex = 0;";
    } ?>
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

      document.famsearch.myflastname.value = "";
      document.famsearch.myffirstname.value = "";
      document.famsearch.mymlastname.value = "";
      document.famsearch.mymfirstname.value = "";
      document.famsearch.mymarrplace.value = "";
      document.famsearch.mymarryear.value = "";
      document.famsearch.mydivplace.value = "";
      document.famsearch.mydivyear.value = "";
      document.famsearch.myfamilyid.value = "";
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
      <?php if ((!$requirelogin || !$treerestrict || !$assignedtree) && $numtrees > 1) { ?>
      URL = URL + "&tree=" + thisform.tree[thisform.tree.selectedIndex].value;
      <?php
      }
      $qualifiers = array("fln", "ffn", "mln", "mfn", "fid", "mp", "my", "dvp", "dvy");
      $criteria = array("flastname", "ffirstname", "mlastname", "mfirstname", "familyid", "marrplace", "marryear", "divplace", "divyear");

      $qcount = 0;
      $found = 0;
      foreach ($criteria as $criterion) {
      ?>
      if (thisform.my<?php echo $criterion; ?>.value !== "" || thisform.<?php echo $qualifiers[$qcount]; ?>qualify.value === 'exists' || thisform.<?php echo $qualifiers[$qcount]; ?>qualify.value === "dnexist") {
        URL = URL + "&my<?php echo $criterion; ?>=" + thisform.my<?php echo $criterion; ?>.value;
        URL = URL + "&<?php echo $qualifiers[$qcount]; ?>qualify=" + thisform.<?php echo $qualifiers[$qcount]; ?>qualify[thisform.<?php echo $qualifiers[$qcount]; ?>qualify.selectedIndex].value;
        found++;
      }
      <?php
      $qcount++;
      }

      //get eventtypeIDs from $eventtypes_table
      $query = "SELECT eventtypeID, tag FROM $eventtypes_table WHERE keep=\"1\" AND type=\"F\"";
      $etresult = tng_query($query);
      while ($row = tng_fetch_assoc($etresult)) {
      if (!in_array($row[tag], $dontdo)) {
      ?>
      if (thisform.cef<?php echo $row['eventtypeID']; ?>.value !== "" || thisform.cfq<?php echo $row['eventtypeID']; ?>.value === 'exists' || thisform.cfq<?php echo $row['eventtypeID']; ?>.value === "dnexist") {
        URL = URL + "&cef<?php echo $row['eventtypeID']; ?>=" + thisform.cef<?php echo $row['eventtypeID']; ?>.value;
        URL = URL + "&cfq<?php echo $row['eventtypeID']; ?>=" + thisform.cfq<?php echo $row['eventtypeID']; ?>[thisform.cfq<?php echo $row['eventtypeID']; ?>.selectedIndex].value;
      }
      if (thisform.cep<?php echo $row['eventtypeID']; ?>.value !== "" || thisform.cpq<?php echo $row['eventtypeID']; ?>.value === 'exists' || thisform.cpq<?php echo $row['eventtypeID']; ?>.value === "dnexist") {
        URL = URL + "&cep<?php echo $row['eventtypeID']; ?>=" + thisform.cep<?php echo $row['eventtypeID']; ?>.value;
        URL = URL + "&cpq<?php echo $row['eventtypeID']; ?>=" + thisform.cpq<?php echo $row['eventtypeID']; ?>[thisform.cpq<?php echo $row['eventtypeID']; ?>.selectedIndex].value;
      }
      if (thisform.cey<?php echo $row['eventtypeID']; ?>.value !== "" || thisform.cyq<?php echo $row['eventtypeID']; ?>.value === 'exists' || thisform.cyq<?php echo $row['eventtypeID']; ?>.value === "dnexist") {
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
