<?php
require 'begin.php';
require 'adminlib.php';
if (!$personID) {
  die('no args');
}
require 'checklogin.php';

$query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix, branch, living, private FROM $people_table WHERE personID = '$personID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);

$rightbranch = checkbranch($row['branch']);
$rights = determineLivingPrivateRights($row, $rightbranch);

$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];

$namestr = getName($row);
tng_free_result($result);

header('Content-type: text/html; charset=' . $session_charset);

require_once 'eventlib.php';
?>
<section class='container'>
  <form id='ldsordinances' name='ldsordinanances' onSubmit='updateLDSOrdinances(this);'>
    <header class='modal-header'>
      <?php echo "<h4>$namestr ($personID)</h4><p>" . getYears($row) . "</p>\n"; ?>
    </header>
    <div class='modal-body'>
      <?php
      if ($rights['lds']) {
        echo buildEventRow('baptdate', 'baptplace', 'BAPL', $personID);
        echo buildEventRow('confdate', 'confplace', 'CONL', $personID);
        echo buildEventRow('initdate', 'initplace', 'INIT', $personID);
        echo buildEventRow('endldate', 'endlplace', 'ENDL', $personID);
        // parents
        echo "<div id='parents'>\n";
          $query = "SELECT personID, familyID, sealdate, sealplace, frel, mrel FROM $children_table WHERE personID = '$personID' ORDER BY parentorder";
          $parents = tng_query($query);
          $parentcount = tng_num_rows($parents);

          // fill a selection control with 0 or more parents
          while ($parent = tng_fetch_assoc($parents)) {
            $familyId =  $parent['familyID'];
            echo buildParentRow($parent, 'husband', 'father');
            echo buildParentRow($parent, 'wife', 'mother');

            $citquery = "SELECT citationID FROM citations WHERE persfamID = \"$personID" . '::' . "{$familyId}\"";
            $citresult = tng_query($citquery) or die(uiTextSnippet('cannotexecutequery') . ": $citquery");
            $iconColor = tng_num_rows($citresult) ? 'icon-info' : 'icon-muted';
            tng_free_result($citresult);

            echo "<div class='row'>\n";
              echo "<div class='col-md-2'>" . uiTextSnippet('SLGC') . ":</div>\n";
              echo "<div class='col-md-2'>\n";
                echo "<input class='form-control form-control-sm' id='parent-sealdate' name='sealpdate" . $familyId . "' type='text' value='" . $parent['sealdate'] . "' maxlength='50' placeholder='" . uiTextSnippet('date') . "'>\n";
              echo "</div>\n";
              echo "<div class='col-md-5'>\n";
                echo "<input class='form-control form-control-sm' id='sealpplace" . $familyId . "' name='sealpplace" . $familyId . "' type='text' value='" . $parent['sealplace'] . "' placeholder='" . uiTextSnippet('place') . "'>\n";
              echo "</div>\n";
              echo "<div class='col-md-3'>\n";
                echo "<a id='find-place-seal' href='#' title='" . uiTextSnippet('find') . "'>\n";
                  echo "<img class='icon-sm' src='svg/temple.svg'>\n";
                echo "</a>\n";
                echo "<a class='lds-seal-citations' id='citesiconSLGC$personID::" . $familyId . "' href='#' title='" . uiTextSnippet('citations') . "' data-person-id='$personID' data-family-id='$familyId'>\n";
                  echo "<img class='icon-sm icon-right icon-citations $iconColor' data-src='svg/archive.svg'>\n";
                echo "</a>\n";
              echo "</div>\n";
            echo "</div>\n";
          }
        echo "</div>\n";
        }
      ?>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <input name='submit2' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    </footer>
  </form>
</section>
<script src='js/people.js'></script>
