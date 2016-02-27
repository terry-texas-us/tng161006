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
        <?php if ((!$requirelogin || !$treerestrict || !$assignedtree) && $numtrees > 1) { ?>
          <div class='row'>
            <?php echo treeSelect($result); ?>
          </div>
        <?php } ?>
        <div class='row'>
          <div class='col-sm-3'>
            <select class='form-control' name='lnqualify'>
              <?php
              $item_array = [
                array(uiTextSnippet('contains'), "contains"), 
                array(uiTextSnippet('equals'), "equals"),
                array(uiTextSnippet('startswith'), "startswith"),
                array(uiTextSnippet('endswith'), "endswith"),
                array(uiTextSnippet('exists'), "exists"),
                array(uiTextSnippet('dnexist'), "dnexist"),
                array(uiTextSnippet('soundexof'), "soundexof"),
                array(uiTextSnippet('metaphoneof'), "metaphoneof")
              ];

              foreach ($item_array as $item) {
                echo "<option value='$item[1]'";
                if ($lnqualify == $item[1]) {
                  echo " selected";
                }
                echo ">$item[0]</option>\n";
              }
              ?>
            </select>
          </div>
          <div class='col-sm-9'>
            <input class='btn btn-secondary' name='mylastname' type='text' value="<?php echo $mylastname; ?>" placeholder="<?php echo uiTextSnippet('lastname'); ?>">
          </div>
        </div>
        <div class='row'>
          <div class='col-sm-3'>
            <select class='form-control' name='fnqualify'>
              <?php
              $item_array = array(
                array(uiTextSnippet('contains'), "contains"),
                array(uiTextSnippet('equals'), "equals"),
                array(uiTextSnippet('startswith'), "startswith"),
                array(uiTextSnippet('endswith'), "endswith"),
                array(uiTextSnippet('exists'), "exists"),
                array(uiTextSnippet('dnexist'), "dnexist"),
                array(uiTextSnippet('soundexof'), "soundexof"));

              foreach ($item_array as $item) {
                echo "<option value='$item[1]'";
                if ($fnqualify == $item[1]) {
                  echo " selected";
                }
                echo ">$item[0]</option>\n";
              }
              ?>
            </select>
          </div>
          <div class='col-sm-9'>
            <input class='btn btn-secondary' name='myfirstname' type='text' value="<?php echo $myfirstname; ?>" placeholder="<?php echo uiTextSnippet('firstname'); ?>">
          </div>
        </div>
        <table class='table table-sm'>
          <tr>
            <td><?php echo uiTextSnippet('personid'); ?>:</td>
            <td>
              <select name='idqualify'>
                <?php
                $item_array = array(array(uiTextSnippet('equals'), "equals"), array(uiTextSnippet('contains'), "contains"), array(uiTextSnippet('startswith'), "startswith"), array(uiTextSnippet('endswith'), "endswith"));
                foreach ($item_array as $item) {
                  echo "<option value='$item[1]'";
                  if ($idqualify == $item[1]) {
                    echo " selected";
                  }
                  echo ">$item[0]</option>\n";
                }
                ?>
              </select>
              <input name='mypersonid' type='text' value="<?php echo $mypersonid; ?>"/>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('gender'); ?>:</td>
            <td>
              <select name='gequalify'>
                <option value="equals"><?php echo uiTextSnippet('equals'); ?></option>
              </select>
              <select name="mygender">
                <option value=''>&nbsp;</option>
                <option value='M'<?php if ($mygender == 'M') {
                  echo " selected";
                } ?>><?php echo uiTextSnippet('male'); ?></option>
                <option value='F'<?php if ($mygender == 'F') {
                  echo " selected";
                } ?>><?php echo uiTextSnippet('female'); ?></option>
                <option value='U'<?php if ($mygender == 'U') {
                  echo " selected";
                } ?>><?php echo uiTextSnippet('unknown'); ?></option>
                <option value='N'<?php if ($mygender == 'N') {
                  echo " selected";
                } ?>><?php echo uiTextSnippet('none'); ?></option>
              </select>
            </td>
          </tr>
          <tr>
            <td colspan='2'>&nbsp;</td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('birthplace'); ?>:</td>
            <td>
              <select name="bpqualify">
                <?php
                $item_array = array(array(uiTextSnippet('contains'), "contains"), array(uiTextSnippet('equals'), "equals"), array(uiTextSnippet('startswith'), "startswith"), array(uiTextSnippet('endswith'), "endswith"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"));
                foreach ($item_array as $item) {
                  echo "<option value='$item[1]'";
                  if ($bpqualify == $item[1]) {
                    echo " selected";
                  }
                  echo ">$item[0]</option>\n";
                }
                ?>
              </select>
              <input name='mybirthplace' type='text' value="<?php echo $mybirthplace; ?>"/>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('birthdatetr'); ?>:</td>
            <td>
              <select name="byqualify">
                <?php
                $item2_array = array(array(uiTextSnippet('equals'), ""), array(uiTextSnippet('plusminus2'), "pm2"), array(uiTextSnippet('plusminus5'), "pm5"), array(uiTextSnippet('plusminus10'), "pm10"), array(uiTextSnippet('lessthan'), "lt"), array(uiTextSnippet('greaterthan'), "gt"), array(uiTextSnippet('lessthanequal'), "lte"), array(uiTextSnippet('greaterthanequal'), "gte"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"));
                foreach ($item2_array as $item) {
                  echo "<option value='$item[1]'";
                  if ($byqualify == $item[1]) {
                    echo " selected";
                  }
                  echo ">$item[0]</option>\n";
                }
                ?>
              </select>
              <input name='mybirthyear' type='text' value="<?php echo $mybirthyear; ?>"/>
            </td>
          </tr>
          <tr<?php if ($tngconfig['hidechr']) {
            echo " style=\"display:none\"";
          } ?>>
            <td><?php echo uiTextSnippet('altbirthplace'); ?>:</td>
            <td>
              <select name="cpqualify">
                <?php
                foreach ($item_array as $item) {
                  echo "<option value='$item[1]'";
                  if ($cpqualify == $item[1]) {
                    echo " selected";
                  }
                  echo ">$item[0]</option>\n";
                }
                ?>
              </select>
              <input name='myaltbirthplace' type='text' value="<?php echo $myaltbirthplace; ?>"/>
            </td>
          </tr>
          <tr<?php if ($tngconfig['hidechr']) {
            echo " style=\"display:none\"";
          } ?>>
            <td><?php echo uiTextSnippet('altbirthdatetr'); ?>:</td>
            <td>
              <select name="cyqualify">
                <?php
                $item2_array = array(array(uiTextSnippet('equals'), ""), array(uiTextSnippet('plusminus2'), "pm2"), array(uiTextSnippet('plusminus5'), "pm5"), array(uiTextSnippet('plusminus10'), "pm10"), array(uiTextSnippet('lessthan'), "lt"), array(uiTextSnippet('greaterthan'), "gt"), array(uiTextSnippet('lessthanequal'), "lte"), array(uiTextSnippet('greaterthanequal'), "gte"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"));
                foreach ($item2_array as $item) {
                  echo "<option value='$item[1]'";
                  if ($cyqualify == $item[1]) {
                    echo " selected";
                  }
                  echo ">$item[0]</option>\n";
                }
                ?>
              </select>
              <input name='myaltbirthyear' type='text' value="<?php echo $myaltbirthyear; ?>"/>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('deathplace'); ?>:</td>
            <td>
              <select name="dpqualify">
                <?php
                foreach ($item_array as $item) {
                  echo "<option value='$item[1]'";
                  if ($dpqualify == $item[1]) {
                    echo " selected";
                  }
                  echo ">$item[0]</option>\n";
                }
                ?>
              </select>
              <input name='mydeathplace' type='text' value="<?php echo $mydeathplace; ?>"/>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('deathdatetr'); ?>:</td>
            <td>
              <select name="dyqualify">
                <?php
                $item2_array = array(array(uiTextSnippet('equals'), ""), array(uiTextSnippet('plusminus2'), "pm2"), array(uiTextSnippet('plusminus5'), "pm5"), array(uiTextSnippet('plusminus10'), "pm10"), array(uiTextSnippet('lessthan'), "lt"), array(uiTextSnippet('greaterthan'), "gt"), array(uiTextSnippet('lessthanequal'), "lte"), array(uiTextSnippet('greaterthanequal'), "gte"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"));
                foreach ($item2_array as $item) {
                  echo "<option value='$item[1]'";
                  if ($dyqualify == $item[1]) {
                    echo " selected";
                  }
                  echo ">$item[0]</option>\n";
                }
                ?>
              </select>
              <input name='mydeathyear' type='text' value="<?php echo $mydeathyear; ?>"/>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('burialplace'); ?>:</td>
            <td>
              <select name="brpqualify">
                <?php
                foreach ($item_array as $item) {
                  echo "<option value='$item[1]'";
                  if ($brpqualify == $item[1]) {
                    echo " selected";
                  }
                  echo ">$item[0]</option>\n";
                }
                ?>
              </select>
              <input name='myburialplace' type='text' value="<?php echo $myburialplace; ?>"/>
            </td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('burialdatetr'); ?>:</td>
            <td>
              <select name="bryqualify">
                <?php
                $item2_array = array(array(uiTextSnippet('equals'), ""), array(uiTextSnippet('plusminus2'), "pm2"), array(uiTextSnippet('plusminus5'), "pm5"), array(uiTextSnippet('plusminus10'), "pm10"), array(uiTextSnippet('lessthan'), "lt"), array(uiTextSnippet('greaterthan'), "gt"), array(uiTextSnippet('lessthanequal'), "lte"), array(uiTextSnippet('greaterthanequal'), "gte"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"));
                foreach ($item2_array as $item) {
                  echo "<option value='$item[1]'";
                  if ($bryqualify == $item[1]) {
                    echo " selected";
                  }
                  echo ">$item[0]</option>\n";
                }
                ?>
              </select>
              <input name='myburialyear' type='text' value="<?php echo $myburialyear; ?>"/>
            </td>
          </tr>
          <tr>
            <td colspan='2'>&nbsp;</td>
          </tr>
          <tr>
            <td><?php echo uiTextSnippet('spousesurname'); ?>*:</td>
            <td>
              <select name="spqualify">
                <?php
                $item_array = array(array(uiTextSnippet('contains'), "contains"), array(uiTextSnippet('equals'), "equals"), array(uiTextSnippet('startswith'), "startswith"), array(uiTextSnippet('endswith'), "endswith"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"), array(uiTextSnippet('soundexof'), "soundexof"), array(uiTextSnippet('metaphoneof'), "metaphoneof"));
                foreach ($item_array as $item) {
                  echo "<option value='$item[1]'";
                  if ($spqualify == $item[1]) {
                    echo " selected";
                  }
                  echo ">$item[0]</option>\n";
                }
                ?>
              </select>
              <input name='mysplname' type='text' value="<?php echo $mysplname; ?>"/>
            </td>
          </tr>
        </table>
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
          <table style="display:none" id="otherevents">
            <tr>
              <td colspan='3'>&nbsp;</td>
            </tr>
            <tr>
              <td><?php echo uiTextSnippet('nickname'); ?>:</td>
              <td>
                <select name="nnqualify">
                  <?php
                  foreach ($item_array as $item) {
                    echo "<option value='$item[1]'";
                    if ($nnqualify == $item[1]) {
                      echo " selected";
                    }
                    echo ">$item[0]</option>\n";
                  }
                  ?>
                </select>
              </td>
              <td><input name='mynickname' type='text' value="<?php echo $mynickname; ?>"/></td>
            </tr>
            <tr>
              <td><?php echo uiTextSnippet('title'); ?>:</td>
              <td>
                <select name="tqualify">
                  <?php
                  foreach ($item_array as $item) {
                    echo "<option value='$item[1]'";
                    if ($tqualify == $item[1]) {
                      echo " selected";
                    }
                    echo ">$item[0]</option>\n";
                  }
                  ?>
                </select>
              </td>
              <td><input name='mytitle' type='text' value="<?php echo $mytitle; ?>"/></td>
            </tr>
            <tr>
              <td><?php echo uiTextSnippet('prefix'); ?>:</td>
              <td>
                <select name="pfqualify">
                  <?php
                  foreach ($item_array as $item) {
                    echo "<option value='$item[1]'";
                    if ($pfqualify == $item[1]) {
                      echo " selected";
                    }
                    echo ">$item[0]</option>\n";
                  }
                  ?>
                </select>
              </td>
              <td><input name='myprefix' type='text' value="<?php echo $myprefix; ?>"/></td>
            </tr>
            <tr>
              <td><?php echo uiTextSnippet('suffix'); ?>:</td>
              <td>
                <select name="sfqualify">
                  <?php
                  foreach ($item_array as $item) {
                    echo "<option value='$item[1]'";
                    if ($sfqualify == $item[1]) {
                      echo " selected";
                    }
                    echo ">$item[0]</option>\n";
                  }
                  ?>
                </select>
              </td>
              <td><input name='mysuffix' type='text' value="<?php echo $mysuffix; ?>"/></td>
            </tr>
            <tr>
              <td colspan='3'>&nbsp;</td>
            </tr>
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
              echo "<tr>\n";
                echo "<td colspan='3'>{$row['displaymsg']}</td>\n";
              echo "</tr>\n";
              echo "<tr>\n";
                echo "<td>" . uiTextSnippet('fact') . ":</td>\n";
              echo "<td>\n";
              echo "<select name=\"cfq{$row['eventtypeID']}\">\n";
              foreach ($item_array as $item) {
                echo "<option value='$item[1]'";
                echo ">$item[0]</option>\n";
              }
              echo "</select>\n";
              echo "</td>\n";
              echo "<td><input name=\"cef{$row['eventtypeID']}\" type='text' value='' /></td>\n";
              echo "</tr>\n";

              echo "<tr>\n";
              echo "<td>" . uiTextSnippet('place') . ":</td>\n";
              echo "<td>\n";
              echo "<select name=\"cpq{$row['eventtypeID']}\">\n";
              foreach ($item_array as $item) {
                echo "<option value='$item[1]'";
                echo ">$item[0]</option>\n";
              }
              echo "</select>\n";
              echo "</td>\n";
              echo "<td><input name=\"cep{$row['eventtypeID']}\" type='text' value='' /></td>\n";
              echo "</tr>\n";

              echo "<tr>\n";
              echo "<td>" . uiTextSnippet('year') . ":</td>\n";
              echo "<td>\n";
              echo "<select name=\"cyq$row[eventtypeID]\">\n";

              $item2_array = array(array(uiTextSnippet('equals'), ""), array(uiTextSnippet('plusminus2'), "pm2"), array(uiTextSnippet('plusminus5'), "pm5"), array(uiTextSnippet('plusminus10'), "pm10"), array(uiTextSnippet('lessthan'), "lt"), array(uiTextSnippet('greaterthan'), "gt"), array(uiTextSnippet('lessthanequal'), "lte"), array(uiTextSnippet('greaterthanequal'), "gte"), array(uiTextSnippet('exists'), "exists"), array(uiTextSnippet('dnexist'), "dnexist"));
              foreach ($item2_array as $item) {
                echo "<option value='$item[1]'";
                echo ">$item[0]</option>\n";
              }
              echo "</select>\n";
              echo "</td>\n";
              echo "<td><input name=\"cey{$row['eventtypeID']}\" type='text' value='' /></td>\n";
              echo "</tr>\n";
            }
            ?>
            <tr>
              <td colspan='3'><br>
                <input type='button' value="<?php echo uiTextSnippet('search'); ?>" onclick="return makeURL();"/> 
                <input type='button' value="<?php echo uiTextSnippet('resetall'); ?>" onclick="resetValues();"/>
              </td>
            </tr>
          </table>
        </section> <!-- .custom-events -->
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
