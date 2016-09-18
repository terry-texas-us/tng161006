<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

if (!$allowAdd) {
  $message = uiTextSnippet('norights');
  header('Location: login.php?message=' . urlencode($message));
  exit;
}
if ($child) {
  $newperson = $child;
} else {
  if ($husband) {
    $newperson = $husband;
  } else {
    if ($wife) {
      $newperson = $wife;
    } else {
      $newperson = '';
    }
  }
}
if ($newperson) {
  $query = "SELECT personID, firstname, lnprefix, lastname, prefix, suffix, nameorder, living, private, branch FROM people WHERE personID = '$newperson'";
  $result = tng_query($query);
  $newpersonrow = tng_fetch_assoc($result);
  tng_free_result($result);
}
if ($husband) {
  $husbstr = getName($newpersonrow) . " - $husband";
} else {
  if ($wife) {
    $wifestr = getName($newpersonrow) . " - $wife";
  }
}
if (!isset($husbstr)) {
  $husbstr = uiTextSnippet('clickfind');
}
if (!isset($wifestr)) {
  $wifestr = uiTextSnippet('clickfind');
}
header('Content-type:text/html; charset=' . $session_charset);

require_once 'eventlib.php';
?>
<form id='famform1' name='famform1' action='' method='post' onsubmit="return validateFamily(this, '<?php echo $slot; ?>');">
  <header class='modal-header'>
    <h4 class='togglehead'><?php echo uiTextSnippet('addnewfamily'); ?></h4>
  </header>
  <div class='modal-body'>
    <input name='lastperson' type='hidden' value="<?php echo $child; ?>">
    <fieldset class='form-group'>
      <span><strong><?php echo uiTextSnippet('prefixfamilyid'); ?></strong></span>
      <label for='familyID'><?php echo uiTextSnippet('familyid'); ?>: </label>
      <input id='familyID' name='familyID' type='text' value="<?php echo $newID; ?>" onblur="this.value = this.value.toUpperCase()">
      <input type='button' value="<?php echo uiTextSnippet('generate'); ?>" onClick="generateIDajax('family', 'familyID');">
      <input type='button' value="<?php echo uiTextSnippet('check'); ?>" onClick="checkIDajax($('#familyID').val(), 'family', 'checkmsg');">
      <span id='checkmsg'></span>
    </fieldset>

    <?php echo displayToggle('plus0', 1, 'spouses', uiTextSnippet('spouses'), ''); ?>
    <div id='spouses'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('husband'); ?>:</td>
          <td><input id='husbnameplusid' name='husbnameplusid' type='text' size='40' value="<?php echo "$husbstr"; ?>" readonly>
            <input id='husband' name='husband' type='hidden' value="<?php echo $husband; ?>">
            <input type='button' value="<?php echo uiTextSnippet('find'); ?>"
                   onclick="return findItem('I', 'husband', 'husbnameplusid', '<?php echo $assignedbranch; ?>');">
            <input type='button' value="<?php echo uiTextSnippet('create'); ?>"
                   onclick="return newPerson('M', 'spouse');">
            <input type='button' value="  <?php echo uiTextSnippet('edit'); ?>  "
                   onclick="editPerson(document.famform1.husband.value, 0, 'M');">
            <input type='button' value="<?php echo uiTextSnippet('remove'); ?>"
                   onclick="removeSpouse(document.famform1.husband, document.famform1.husbnameplusid);">
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('wife'); ?>:</td>
          <td>
            <input id='wifenameplusid' name='wifenameplusid' type='text' size='40' value="<?php echo "$wifestr"; ?>" readonly>
            <input id='wife' name='wife' type='hidden' value="<?php echo $wife; ?>">
            <input type='button' value="<?php echo uiTextSnippet('find'); ?>"
                   onclick="return findItem('I', 'wife', 'wifenameplusid', '<?php echo $assignedbranch; ?>');">
            <input type='button' value="<?php echo uiTextSnippet('create'); ?>"
                   onclick="return newPerson('F', 'spouse');">
            <input type='button' value="  <?php echo uiTextSnippet('edit'); ?>  "
                   onclick="editPerson(document.famform1.wife.value, 0, 'F');">
            <input type='button' value="<?php echo uiTextSnippet('remove'); ?>"
                   onclick="removeSpouse(document.famform1.wife, document.famform1.wifenameplusid);">
          </td>
        </tr>
      </table>
      <table class='table table-sm'>
        <tr>
          <td>
            <input name='living' type='checkbox' value='1' checked> <?php echo uiTextSnippet('living'); ?>&nbsp;
            <input name='private' type='checkbox' value='1'> <?php echo uiTextSnippet('private'); ?>
          </td>
          <td></td>
          <td><?php echo uiTextSnippet('branch') . ': '; ?></td>
          <td style="height: 2em">
            <?php
            $query = 'SELECT branch, description FROM branches ORDER BY description';
            $branchresult = tng_query($query);
            $numbranches = tng_num_rows($branchresult);
            $branchlist = explode(',', $row['branch']);

            $descriptions = [];
            $options = '';
            while ($branchrow = tng_fetch_assoc($branchresult)) {
              $options .= "  <option value=\"{$branchrow['branch']}\">{$branchrow['description']}</option>\n";
            }
            echo "<span id='fbranchlist'></span>";
            if (!$assignedbranch) {
              if ($numbranches > 8) {
                $select = uiTextSnippet('scrollbranch') . '<br>';
              }
              $select .= "<select name=\"branch[]\" id=\"fbranch\" multiple size=\"8\">\n";
              $select .= "  <option value=''";
              if ($row['branch'] == '') {
                $select .= ' selected';
              }
              $select .= '>' . uiTextSnippet('nobranch') . "</option>\n";

              $select .= "$options</select>\n";
              echo "<span>(<a href='#' onclick=\"showBranchEdit('fbranchedit'); quitBranchEdit('fbranchedit'); return false;\"><img src='img/ArrowDown.gif'>" . uiTextSnippet('edit') . '</a> )</span><br>';
              ?>
              <div id='fbranchedit' style='position: absolute; display: none;'
                   onmouseover="clearTimeout(branchtimer);"
                   onmouseout="closeBranchEdit('fbranch', 'fbranchedit', 'fbranchlist');">
                <?php echo $select; ?>
              </div>
            <?php
            } else {
              echo "<input name='branch' type='hidden' value=\"$assignedbranch\">";
            }
            ?>
          </td>
        </tr>
      </table>
    </div>
    <?php echo displayToggle('plus1', 1, 'events', uiTextSnippet('events'), ''); ?>
    <div id='events'>
      <p class='smallest'><?php echo uiTextSnippet('datenote'); ?></p>
      <table class='table table-sm'>
        <tr>
          <td></td>
          <td><?php echo uiTextSnippet('date'); ?></td>
          <td><?php echo uiTextSnippet('place'); ?></td>
          <td colspan='4'></td>
        </tr>
        <?php
        echo showEventRow('marrdate', 'marrplace', 'MARR', '');
        ?>
        <tr>
          <td><?php echo uiTextSnippet('marriagetype'); ?>:</td>
          <td colspan='6'>
            <input name='marrtype' type='text' value='' style='width: 494px' maxlength='50'>
          </td>
        </tr>
        <?php
        if (determineLDSRights()) {
          echo showEventRow('sealdate', 'sealplace', 'SLGS', '');
        }
        echo showEventRow('divdate', 'divplace', 'DIV', '');
        ?>
      </table>
    </div>
  </div> <!-- .modal-body -->
  <footer class='modal-footer'>
    <input name='newfamily' type='hidden' value='ajax'>
    <input name='submit2' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
  </footer>
</form>