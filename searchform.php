<?php
include("tng_begin.php");

$query = "SELECT gedcom, treename FROM $trees_table ORDER BY treename";
$result = tng_query($query);
$numtrees = tng_num_rows($result);

if ($_SESSION['tng_search_tree']) {
  $tree = $_SESSION['tng_search_tree'];
}
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
$showdeath = $_SESSION['tng_search_showdeath'];
$showspouse = $_SESSION['tng_search_showspouse'];
$mygender = $_SESSION['tng_search_gender'];
$mysplname = $_SESSION['tng_search_mysplname'];
$spqualify = $_SESSION['tng_search_spqualify'];
$nr = $_SESSION['tng_nr'];

$dontdo = array("ADDR", "BIRT", "CHR", "DEAT", "BURI", "NICK", "TITL", "NSFX", "NPFX");

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

function buildSelectInputGroup($label, $formName, $selectName, $value, $options, $selected) {
  $out = "<label for='" . $formName . "'>" . uiTextSnippet($label) . "</label>\n";
  $out .= "<div class='input-group' style='width: 100%;'>\n";
    $out .= "<input class='form-control' name='" . $formName . "' type='text' value='" . $value . "' placeholder='" . uiTextSnippet($label) . "'>\n";
    $out .= "<span class='input-group-select'>\n";
      $out .= "<select class='form-control' name='" . $selectName . "'>\n";
        foreach ($options as $option) {
          $out .= "<option value='$option'" . ($selected == $option ? ' selected' : '') . ">" . uiTextSnippet($option) . "</option>\n";
        }
      $out .= "</select>\n";
    $out .= "</span>\n";
  $out .= "</div>\n";
  return $out;
}
$idOptions = ['contains', 'equals', 'startswith', 'endswith'];
$bpOptions = ['contains', 'equals', 'startswith', 'endswith', 'exists', 'dnexist'];
$fnOptions = ['contains', 'equals', 'startswith', 'endswith', 'exists', 'dnexist', 'soundexof'];
$lnOptions = ['contains', 'equals', 'startswith', 'endswith', 'exists', 'dnexist', 'soundexof', 'metaphoneof'];

$yearOptions = ['equals', 'plusminus2', 'plusminus5', 'plusminus10', 'lessthan', 'greaterthan', 'lessthanequal', 'greaterthanequal', 'exists', 'dnexist'];

header("Content-type: text/html; charset=" . $session_charset);
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
      echo "<b class='msgerror h4' id='errormsg'>" . stripslashes(strip_tags($msg)) . "</b>";
    }
    beginFormElement("search", "", "search", "", "return makeURL();");
    ?>
      <div class="searchform">
        <div class='row'>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('personid', 'mypersonid', 'idqualify', $mypersonid, $idOptions, $idqualify); ?>
          </div>
          <div class='col-sm-6'>
            <?php if ((!$requirelogin || !$treerestrict || !$assignedtree) && $numtrees > 1) { ?>
              <?php echo treeSelect($result); ?>
            <?php } ?>
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

        <div class='row'>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('birthplace', 'mybirthplace', 'bpqualify', $mybirthplace, $bpOptions, $bpqualify); ?>
          </div>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('birthdatetr', 'mybirthyear', 'byqualify', $mybirthyear, $yearOptions, $byqualify); ?>
          </div>
        </div>

        <div class='row'<?php if ($tngconfig['hidechr']) {echo " style='display: none'";} ?>>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('altbirthplace', 'myaltbirthplace', 'cpqualify', $myaltbirthplace, $bpOptions, $cpqualify); ?>
          </div>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('altbirthdatetr', 'myaltbirthyear', 'cyqualify', $myaltbirthyear, $yearOptions, $cyqualify); ?>
          </div>
        </div>

        <div class='row'>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('deathplace', 'mydeathplace', 'dpqualify', $mydeathplace, $bpOptions, $dpqualify); ?>
          </div>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('deathdatetr', 'mydeathyear', 'dyqualify', $mydeathyear, $yearOptions, $dyqualify); ?>
          </div>
        </div>

        <div class='row'>
          <div class='col-sm-6'>
            <?php echo buildSelectInputGroup('burialplace', 'myburialplace', 'brpqualify', $myburialplace, $bpOptions, $brpqualify); ?>
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
              <option value='M'<?php if ($mygender == 'M') {echo " selected";} ?>><?php echo uiTextSnippet('male'); ?></option>
              <option value='F'<?php if ($mygender == 'F') {echo " selected";} ?>><?php echo uiTextSnippet('female'); ?></option>
              <option value='U'<?php if ($mygender == 'U') {echo " selected";} ?>><?php echo uiTextSnippet('unknown'); ?></option>
              <option value='N'<?php if ($mygender == 'N') {echo " selected";} ?>><?php echo uiTextSnippet('none'); ?></option>
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
          
          <section style="display: none" id="otherevents">
            <div class='row'>
              <div class='col-sm-6'>
                <?php echo buildSelectInputGroup('nickname', 'mynickname', 'nnqualify', $mynickname, $bpOptions, $nnqualify); ?>
              </div>
              <div class='col-sm-6'>
                <?php echo buildSelectInputGroup('title', 'mytitle', 'tqualify', $mytitle, $bpOptions, $tqualify); ?>
              </div>
            </div>          
            <div class='row'>
              <div class='col-sm-6'>
                <?php echo buildSelectInputGroup('prefix', 'myprefix', 'pfqualify', $myprefix, $bpOptions, $pfqualify); ?>
              </div>
              <div class='col-sm-6'>
                <?php echo buildSelectInputGroup('suffix', 'mysuffix', 'sfqualify', $mysuffix, $bpOptions, $sfqualify); ?>
              </div>
            </div>              
            <br>
            <?php
            $eventtypes = array();
            $query = "SELECT eventtypeID, tag, display FROM $eventtypes_table WHERE keep=\"1\" AND type=\"I\" ORDER BY display";
            $result = tng_query($query);
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
              echo "{$row['displaymsg']}\n";
              
              echo "<div class='row'>\n";
                echo "<div class='col-md-4'>\n";
                  $formName = 'cef' . $row['eventtypeID'];
                  $selectName = 'cfq' . $row['eventtypeID'];
                  echo buildSelectInputGroup('fact', $formName, $selectName, '', $bpOptions, '');
                echo "</div>\n";
                echo "<div class='col-md-4'>\n";
                  $formName = 'cep' . $row['eventtypeID'];
                  $selectName = 'cpq' . $row['eventtypeID'];
                  echo buildSelectInputGroup('place', $formName, $selectName, '', $bpOptions, '');
                echo "</div>\n";
                echo "<div class='col-md-4'>\n";
                  $formName = 'cey' . $row['eventtypeID'];
                  $selectName = 'cyq' . $row['eventtypeID'];
                  echo buildSelectInputGroup('year', $formName, $selectName, '', $yearOptions, '');
                echo "</div>\n";
              echo "</div>\n";
            }
            ?>
            <button class='btn btn-primary-outline' type='button' onclick="return makeURL();"><?php echo uiTextSnippet('search'); ?></button> 
            <button class='btn btn-warning-outline' type='button' onclick="resetValues();"><?php echo uiTextSnippet('resetall'); ?></button>
          </section>
        </section>  <!-- .custom-events --> 
      </div>
      <div class="searchsidebar">
        <table>
          <tr>
            <td><?php echo uiTextSnippet('joinwith'); ?>:</td>
            <td>
              <select name="mybool">
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
            </td>
            <td></td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('numresults'); ?>:</td>
            <td>
              <select name="nr">
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
            </td>
            <td></td>
          </tr>
        </table>
        <p>
          <input name='showdeath' type='checkbox' value='yes'<?php if ($showdeath == "yes") {echo " checked";} ?> /> <?php echo uiTextSnippet('showdeath'); ?>
          <br>
          <input name='showspouse' type='checkbox' value='yes'<?php if ($showspouse == "yes") {echo " checked"; } ?> /> <?php echo uiTextSnippet('showspouse'); ?>
          <br>
          <br>
          <input id='searchbtn' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
          <input id='resetbtn' type='button' value="<?php echo uiTextSnippet('tng_reset'); ?>" 
                 onclick="resetValues();"/>
        </p>
      </div>
    <?php endFormElement(); ?>
      <br clear='all'>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script>
    var searchByTree = <?php echo ((!$requirelogin || !$treerestrict || !$assignedtree) && $numtrees > 1) ? "true" : "false"; ?>;
    function resetValues() {
      if (searchByTree) {
        document.search.tree.selectedIndex = 0;
      }
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

      document.search.mylastname.value = "";
      document.search.myfirstname.value = "";
      document.search.mynickname.value = "";
      document.search.myprefix.value = "";
      document.search.mysuffix.value = "";
      document.search.mytitle.value = "";
      document.search.mybirthplace.value = "";
      document.search.mybirthyear.value = "";
      document.search.myaltbirthplace.value = "";
      document.search.myaltbirthyear.value = "";
      document.search.mydeathplace.value = "";
      document.search.mydeathyear.value = "";
      document.search.myburialplace.value = "";
      document.search.myburialyear.value = "";
      document.search.mygender.selectedIndex = 0;
      document.search.mysplname.value = "";
      document.search.mypersonid.value = "";

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

      if (thisform.mysplname.value !== "" && (thisform.mygender.selectedIndex < 1 || thisform.mygender.selectedIndex > 2)) {
        alert(textSnippet('spousemore'));
        return false;
      }

      if (thisform.mysplname.value !== "" && thisform.mybool.selectedIndex > 0) {
        alert(textSnippet('joinor'));
        return false;
      }

      URL = "mybool=" + thisform.mybool[thisform.mybool.selectedIndex].value;
      URL = URL + "&nr=" + thisform.nr[thisform.nr.selectedIndex].value;
      <?php if ((!$requirelogin || !$treerestrict || !$assignedtree) && $numtrees > 1) { ?>
      URL = URL + "&tree=" + thisform.tree[thisform.tree.selectedIndex].value;
      <?php } ?>

      if (thisform.showdeath.checked)
        URL = URL + "&showdeath=yes";
      if (thisform.showspouse.checked)
        URL = URL + "&showspouse=yes";

      <?php
      $qualifiers = array("ln", "fn", "id", "bp", "by", "cp", "cy", "dp", "dy", "brp", "bry", "nn", "t", "pf", "sf", "sp", "ge");
      $criteria = array("lastname", "firstname", "personid", "birthplace", "birthyear", "altbirthplace", "altbirthyear", "deathplace", "deathyear", "burialplace", "burialyear", "nickname", "title", "prefix", "suffix", "splname", "gender");

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
      $query = "SELECT eventtypeID, tag FROM $eventtypes_table WHERE keep=\"1\" AND type=\"I\"";
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
      window.location.href = "search.php?" + URL;

      return false;
    }
  </script>
</body>
</html>
