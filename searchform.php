<?php
require 'tng_begin.php';

$lnqualify = $_SESSION['tng_search_lnqualify'];
$mylastname = $_SESSION['tng_search_lastname'];
$fnqualify = $_SESSION['tng_search_fnqualify'];
$myfirstname = $_SESSION['tng_search_firstname'];
$idqualify = $_SESSION['tng_search_idqualify'];
$mypersonid = $_SESSION['tng_search_personid'];
$bpqualify = $_SESSION['tng_search_bpqualify'];
$mybirthplace = $_SESSION['tng_search_birthplace'];
$byqualify = $_SESSION['tng_search_byqualify'];
$mybirthyear = $_SESSION['tng_search_birthyear'];
$cpqualify = $_SESSION['tng_search_cpqualify'];
$myaltbirthplace = $_SESSION['tng_search_altbirthplace'];
$cyqualify = $_SESSION['tng_search_cyqualify'];
$myaltbirthyear = $_SESSION['tng_search_altbirthyear'];
$dpqualify = $_SESSION['tng_search_dpqualify'];
$mydeathplace = $_SESSION['tng_search_deathplace'];
$dyqualify = $_SESSION['tng_search_dyqualify'];
$mydeathyear = $_SESSION['tng_search_deathyear'];
$brpqualify = $_SESSION['tng_search_brpqualify'];
$myburialplace = $_SESSION['tng_search_burialplace'];
$bryqualify = $_SESSION['tng_search_bryqualify'];
$myburialyear = $_SESSION['tng_search_burialyear'];
$mybool = $_SESSION['tng_search_bool'];
$showspouse = $_SESSION['tng_search_showspouse'];
$mygender = $_SESSION['tng_search_gender'];
$mysplname = $_SESSION['tng_search_mysplname'];
$spqualify = $_SESSION['tng_search_spqualify'];
$nr = $_SESSION['tng_nr'];

$dontdo = ['ADDR', 'BIRT', 'CHR', 'DEAT', 'BURI', 'NICK', 'TITL', 'NSFX', 'NPFX'];

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
$headSection->setTitle(uiTextSnippet('searchnames'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/magnifying-glass.svg'><?php echo uiTextSnippet('searchnames'); ?></h2>
    <br>
    <?php
    if ($msg) {
      echo "<b class='msgerror h4' id='errormsg'>" . stripslashes(strip_tags($msg)) . '</b>';
    }
    ?>
    <form action='search.php' name='search' onsubmit='return makeURL();'>
      <div class="searchform">
        <div class='row'>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('personid', 'mypersonid', 'idqualify', $mypersonid, $idOptions, $idqualify); ?>
          </div>
        </div>
        <br>
        <div class='row'>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('lastname', 'mylastname', 'lnqualify', $mylastname, $lnOptions, $lnqualify); ?>
          </div>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('firstname', 'myfirstname', 'fnqualify', $myfirstname, $fnOptions, $fnqualify); ?>
          </div>
        </div>
        <br>
        <div class='row'>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('birthplace', 'mybirthplace', 'bpqualify', $mybirthplace, $placeOptions, $bpqualify); ?>
          </div>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('birthdatetr', 'mybirthyear', 'byqualify', $mybirthyear, $yearOptions, $byqualify); ?>
          </div>
        </div>

        <div class='row'<?php if ($tngconfig['hidechr']) {echo " style='display: none'";} ?>>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('altbirthplace', 'myaltbirthplace', 'cpqualify', $myaltbirthplace, $placeOptions, $cpqualify); ?>
          </div>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('altbirthdatetr', 'myaltbirthyear', 'cyqualify', $myaltbirthyear, $yearOptions, $cyqualify); ?>
          </div>
        </div>

        <div class='row'>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('deathplace', 'mydeathplace', 'dpqualify', $mydeathplace, $placeOptions, $dpqualify); ?>
          </div>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('deathdatetr', 'mydeathyear', 'dyqualify', $mydeathyear, $yearOptions, $dyqualify); ?>
          </div>
        </div>

        <div class='row'>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('burialplace', 'myburialplace', 'brpqualify', $myburialplace, $placeOptions, $brpqualify); ?>
          </div>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('burialdatetr', 'myburialyear', 'bryqualify', $myburialyear, $yearOptions, $bryqualify); ?>
          </div>
        </div>
            
        <div class='row'>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('spousesurname', 'mysplname', 'spqualify', $mysplname, $lnOptions, $spqualify); ?>
          </div>
          <div class='col-sm-6'>
            <?php echo uiTextSnippet('gender'); ?>:
            <select class='form-control' name='gequalify'>
              <option value="equals"><?php echo uiTextSnippet('equals'); ?></option>
            </select>
            <select class='form-control' name="mygender">
              <option value=''>&nbsp;</option>
              <option value='M'<?php if ($mygender == 'M') {echo ' selected';} ?>><?php echo uiTextSnippet('male'); ?></option>
              <option value='F'<?php if ($mygender == 'F') {echo ' selected';} ?>><?php echo uiTextSnippet('female'); ?></option>
              <option value='U'<?php if ($mygender == 'U') {echo ' selected';} ?>><?php echo uiTextSnippet('unknown'); ?></option>
              <option value='N'<?php if ($mygender == 'N') {echo ' selected';} ?>><?php echo uiTextSnippet('none'); ?></option>
            </select>
          </div>
        </div>
        
        <p class="small"><em>*<?php echo uiTextSnippet('spousemore'); ?></em></p>
        <input name='offset' type='hidden' value='0'/>
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
          
          <section style='display: none' id="otherevents">
            <div class='row'>
              <div class='col-sm-6'>
                <?php echo buildSelectInputGroup('nickname', 'mynickname', 'nnqualify', $mynickname, $placeOptions, $nnqualify); ?>
              </div>
              <div class='col-sm-6'>
                <?php echo buildSelectInputGroup('title', 'mytitle', 'tqualify', $mytitle, $placeOptions, $tqualify); ?>
              </div>
            </div>          
            <div class='row'>
              <div class='col-sm-6'>
                <?php echo buildSelectInputGroup('prefix', 'myprefix', 'pfqualify', $myprefix, $placeOptions, $pfqualify); ?>
              </div>
              <div class='col-sm-6'>
                <?php echo buildSelectInputGroup('suffix', 'mysuffix', 'sfqualify', $mysuffix, $placeOptions, $sfqualify); ?>
              </div>
            </div>              
            <br>
            <?php
            $eventtypes = [];
            $query = "SELECT eventtypeID, tag, display FROM eventtypes WHERE keep=\"1\" AND type=\"I\" ORDER BY display";
            $result = tng_query($query);
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
              echo "{$row['displaymsg']}\n";
              
              echo "<div class='row'>\n";
                echo "<div class='col-md-4'>\n";
                  $formName = 'cef' . $row['eventtypeID'];
                  $selectName = 'cfq' . $row['eventtypeID'];
                  echo buildSelectInputGroup('fact', $formName, $selectName, '', $placeOptions, '');
                echo "</div>\n";
                echo "<div class='col-md-4'>\n";
                  $formName = 'cep' . $row['eventtypeID'];
                  $selectName = 'cpq' . $row['eventtypeID'];
                  echo buildSelectInputGroup('place', $formName, $selectName, '', $placeOptions, '');
                echo "</div>\n";
                echo "<div class='col-md-4'>\n";
                  $formName = 'cey' . $row['eventtypeID'];
                  $selectName = 'cyq' . $row['eventtypeID'];
                  echo buildSelectInputGroup('year', $formName, $selectName, '', $yearOptions, '');
                echo "</div>\n";
              echo "</div>\n";
            }
            ?>
            <button class='btn btn-outline-primary' type='button' onclick="return makeURL();"><?php echo uiTextSnippet('search'); ?></button> 
            <button class='btn btn-outline-warning' type='button' onclick="resetValues();"><?php echo uiTextSnippet('resetall'); ?></button>
          </section>
        </section>  <!-- .custom-events --> 
      </div>
      <div class='row'>
        <div class='col-sm-3'><?php echo uiTextSnippet('numresults'); ?>:</div>
        <div class='col-sm-3'>
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
        <div class='col-sm-6'><?php echo uiTextSnippet('joinwith'); ?>:<br>
          <!--<fieldset class="form-group">-->
            <label class='form-check-inline'>
              <?php
              echo "<input type='radio' name='mybool' id='joinsWithAND' value='AND'";
              echo $mybool == 'AND' ? ' checked> ' : '> ';
              echo uiTextSnippet('cap_and');
              ?>
            </label>
            <label class='form-check-inline'>
              <?php
              echo "<input type='radio' name='mybool' id='joinsWithOR' value='OR'";
              echo $mybool == 'OR' ? ' checked> ' : '> ';
              echo uiTextSnippet('cap_or');
              ?>
            </label>
          <!--</fieldset>-->
        </div>
      </div>
      <div class='row'>
        <div class='col-sm-12'>
          <input name='showspouse' type='checkbox' value='yes'<?php if ($showspouse == 'yes') {echo ' checked'; } ?> /> <?php echo uiTextSnippet('showspouse'); ?>
        </div>
      </div>
      <div class='row'>
        <div class='col-sm-2'>  
          <button class='btn btn-outline-primary' id='searchbtn' type='submit'><?php echo uiTextSnippet('search'); ?></button>
        </div>
        <div class='col-sm-2'>
          <button class='btn btn-outline-primary' id='resetbtn' type='button' onclick="resetValues();"><?php echo uiTextSnippet('tng_reset'); ?></button> 
        </div>
      </div>
    </form>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script>
    var searchByTree = "false";
    function resetValues() {
      document.search.lnqualify.selectedIndex = 0;
      document.search.fnqualify.selectedIndex = 0;
      document.search.nnqualify.selectedIndex = 0;
      document.search.tqualify.selectedIndex = 0;
      document.search.sfqualify.selectedIndex = 0;
      document.search.bpqualify.selectedIndex = 0;
      document.search.byqualify.selectedIndex = 0;
      document.search.cpqualify.selectedIndex = 0;
      document.search.cyqualify.selectedIndex = 0;
      document.search.dpqualify.selectedIndex = 0;
      document.search.dyqualify.selectedIndex = 0;
      document.search.brpqualify.selectedIndex = 0;
      document.search.bryqualify.selectedIndex = 0;
      document.search.spqualify.selectedIndex = 0;
      document.search.mybool.selectedIndex = 0;
      document.search.idqualify.selectedIndex = 0;

      document.search.mylastname.value = '';
      document.search.myfirstname.value = '';
      document.search.mynickname.value = '';
      document.search.myprefix.value = '';
      document.search.mysuffix.value = '';
      document.search.mytitle.value = '';
      document.search.mybirthplace.value = '';
      document.search.mybirthyear.value = '';
      document.search.myaltbirthplace.value = '';
      document.search.myaltbirthyear.value = '';
      document.search.mydeathplace.value = '';
      document.search.mydeathyear.value = '';
      document.search.myburialplace.value = '';
      document.search.myburialyear.value = '';
      document.search.mygender.selectedIndex = 0;
      document.search.mysplname.value = '';
      document.search.mypersonid.value = '';

      document.search.showdeath.checked = false;
      document.search.showspouse.checked = false;
      $('#errormsg').hide();
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
      var thisform = document.search;
      var thisfield;
      var found = 0;

      if (thisform.mysplname.value !== '' && (thisform.mygender.value === '')) {
        alert(textSnippet('spousemore'));
        return false;
      }

      if (thisform.mysplname.value !== '' && thisform.mybool.selectedIndex > 0) {
        alert(textSnippet('joinor'));
        return false;
      }

      URL = "mybool=" + thisform.mybool[thisform.mybool.selectedIndex].value;
      URL = URL + "&nr=" + thisform.nr[thisform.nr.selectedIndex].value;

      if (thisform.showspouse.checked)
        URL = URL + "&showspouse=yes";

      <?php
      $qualifiers = ['ln', 'fn', 'id', 'bp', 'by', 'cp', 'cy', 'dp', 'dy', 'brp', 'bry', 'nn', 't', 'pf', 'sf', 'sp', 'ge'];
      $criteria = ['lastname', 'firstname', 'personid', 'birthplace', 'birthyear', 'altbirthplace', 'altbirthyear', 'deathplace', 'deathyear', 'burialplace', 'burialyear', 'nickname', 'title', 'prefix', 'suffix', 'splname', 'gender'];

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

      $query = "SELECT eventtypeID, tag FROM eventtypes WHERE keep=\"1\" AND type=\"I\"";
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
      window.location.href = "search.php?" + URL;

      return false;
    }
  </script>
</body>
</html>
