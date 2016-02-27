<?php
include("tng_begin.php");

include($subroot . "pedconfig.php");

$relatepersonID = $_SESSION['relatepersonID'];
$relatetreeID = $_SESSION['relatetreeID'];

$result = getPersonDataPlusDates($tree, $primaryID);
if ($result) {
  $row = tng_fetch_assoc($result);
  $righttree = checktree($tree);
  $rightbranch = checkbranch($row['branch']);
  $rights = determineLivingPrivateRights($row, $righttree, $rightbranch);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  if ($rights['both']) {
    $birthdate = "";
    if ($row['birthdate']) {
      $birthdate = uiTextSnippet('birthabbr') . " " . displayDate($row['birthdate']);
    } else {
      if ($row['altbirthdate']) {
        $birthdate = uiTextSnippet('chrabbr') . " " . displayDate($row['altbirthdate']);
      }
    }
    if ($birthdate) {
      $birthdate = "($birthdate)";
    }
    $namestrplus = " $birthdate - $primaryID";
  } else {
    $namestrplus = " - $primaryID";
  }
  $namestr = getName($row);

  $treeResult = getTreeSimple($tree);
  $treerow = tng_fetch_assoc($treeResult);
  $disallowgedcreate = $treerow['disallowgedcreate'];
  tng_free_result($treeResult);

  tng_free_result($result);
}

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('relcalc'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<?php
echo "<body id='public'>\n";
  echo "<section class='container'>\n";
    echo $publicHeaderSection->build();

    $photostr = showSmallPhoto($primaryID, $namestr, $rights['both'], 0, false, $row['sex']);
    echo tng_DrawHeading($photostr, $namestr, getYears($row));

    $innermenu = "&nbsp; \n";

    echo tng_menu('I', "relate", $primaryID, $innermenu);

    $namestr .= $namestrplus;

    beginFormElement("relationship", "get", "form1", "form1");

    $maxupgen = $pedigree['maxupgen'] ? $pedigree['maxupgen'] : 15;
    $newstr = preg_replace("/xxx/", $maxupgen, uiTextSnippet('findrelinstr'));
    ?>
      <h4><?php echo uiTextSnippet('findrel'); ?></h4>
      <p><?php echo $newstr; ?></p>
      <table>
        <tr>
          <td>
            <table>
              <tr>
                <td><strong><?php echo uiTextSnippet('person1'); ?> </strong></td>
                <td>
                  <div id="name1"><?php echo $namestr; ?></div>
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('changeto'); ?> </td>
                <td>
                  <input id='altprimarypersonID' name='altprimarypersonID' type='text' size='10'>
                  <input id='findFirstPerson' name='find1' type='button' value="<?php echo uiTextSnippet('find'); ?>">
                </td>
              </tr>
              <tr>
                <td colspan='2'>&nbsp;</td>
              </tr>
              <tr>
                <td><strong><?php echo uiTextSnippet('person2'); ?> </strong></td>
                <td>
                  <?php
                  if ($relatepersonID && $relatetreeID == $tree) {
                    $query = "SELECT firstname, lastname, lnprefix, prefix, suffix, nameorder, living, private, branch, birthdate, altbirthdate FROM $people_table WHERE personID = \"$relatepersonID\" AND gedcom = \"$tree\"";
                    $result2 = tng_query($query);
                    if ($result2) {
                      $row2 = tng_fetch_assoc($result2);
                      $rights2 = determineLivingPrivateRights($row2, $righttree);
                      $row2['allow_living'] = $rights2['living'];
                      $row2['allow_private'] = $rights2['private'];
                      if ($row2['allow_living']) {
                        $birthdate = $row2['birthdate'] ? $row2['birthdate'] : $row2['altbirthdate'];
                        $birthdate = " ($birthdate)";
                      } else {
                        $birthdate = "";
                      }
                      $namestr2 = getName($row2) . "$birthdate - $relatepersonID";
                      tng_free_result($result2);
                    }
                  }
                  echo "<div id=\"name2\">$namestr2</div><input name='savedpersonID' type='hidden' value=\"$relatepersonID\" /></td></tr>\n";
                  echo "<tr><td>" . uiTextSnippet('changeto') . " </td><td>";
                  ?>
                  <input id='secondpersonID' name='secondpersonID' type='text' size='10'>
                  <input id='findSecondPerson' name='find2' type='button' value="<?php echo uiTextSnippet('find'); ?>">
                </td>
              </tr>
            </table>
          </td>
          <td>
            <div class="searchsidebar">
              <table>
                <tr>
                  <td><?php echo uiTextSnippet('maxrels'); ?>:</td>
                  <td>
                    <select name="maxrels">
                      <?php
                      $initrels = $pedigree['initrels'] ? $pedigree['initrels'] : 1;
                      $maxrels = $pedigree['maxrels'] ? $pedigree['maxrels'] : 15;
                      $dorels = $dorels ? $dorels : $initrels;
                      for ($i = 1; $i <= $maxrels; $i++) {
                        echo "<option value=\"$i\"";
                        if ($i == $dorels) {
                          echo " selected";
                        }
                        echo ">$i</option>\n";
                      }
                      ?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td colspan='2'>&nbsp;</td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('dospouses'); ?>:&nbsp;</td>
                  <td>
                    <select name="disallowspouses">
                      <?php
                      $dospouses = $dospouses ? $dospouses : 1;
                      echo "<option value=\"0\"";
                      if ($dospouses) {
                        echo " selected";
                      }
                      echo ">" . uiTextSnippet('yes') . "</option>\n";
                      echo "<option value='1'";
                      if (!$dospouses) {
                        echo " selected";
                      }
                      echo ">" . uiTextSnippet('no') . "</option>\n";
                      ?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td colspan='2'>&nbsp;</td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('gencheck'); ?>:</td>
                  <td>
                    <select name="generations">
                      <?php
                      $dogens = $dogens ? $dogens : $pedigree['maxupgen'];
                      $maxgens = $pedigree['maxupgen'] ? $pedigree['maxupgen'] : 15;
                      for ($i = 1; $i <= $maxgens; $i++) {
                        echo "<option value=\"$i\"";
                        if ($i == $dogens) {
                          echo " selected";
                        }
                        echo ">$i</option>\n";
                      }
                      ?>
                    </select>
                  </td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
      </table>
      <br>
      <input name='tree' type='hidden' value="<?php echo $tree; ?>">
      <input id='primarypersonID' name='primarypersonID' type='hidden' value="<?php echo $primaryID; ?>">
      <input id='calcbtn' type='submit' value="<?php echo uiTextSnippet('calculate'); ?>">
      <br><br>
    <?php
    endFormElement();
    echo $publicFooterSection->build();
  echo "</section> <!-- .container -->";
  echo scriptsManager::buildScriptElements($flags, 'public');
  ?>
  <script>
    $('#calcbtn').on('click', function() {
      <?php if (!$relatepersonID) { ?>
        if (form1.secondpersonID.value.length === 0 ) {
          alert(textSnippet('select2inds')); 
          return false;
        }
      <?php } ?>
    });

    $('#findFirstPerson').on('click', function() {
      findItem('I', 'altprimarypersonID', 'name1', '<?php echo $tree; ?>');
    });

    $('#findSecondPerson').on('click', function() {
      findItem('I', 'secondpersonID', 'name2', '<?php echo $tree; ?>');
    });
  </script>
  <script src='js/selectutils.js'></script>
</body>
</html>
