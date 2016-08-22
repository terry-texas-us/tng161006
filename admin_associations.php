<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

$query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix, branch, living, private, gedcom FROM $people_table WHERE personID = '$personID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);

$rightbranch = checkbranch($row['branch']);
$rights = determineLivingPrivateRights($row, $rightbranch);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];

$namestr = getName($row);
tng_free_result($result);

$helplang = findhelp("assoc_help.php");
header("Content-type:text/html; charset=" . $session_charset);

$query = "SELECT assocID, passocID, relationship, reltype FROM $assoc_table WHERE personID = '$personID'";
$assocresult = tng_query($query);
$assoccount = tng_num_rows($assocresult);
?>
<div id='associations'<?php if (!$assoccount) {echo " style='display: none'";} ?>>
  <form name='assocform'>
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('associations') . " : $namestr"; ?></h4>
      <p>
        <a href='#' onclick="return openHelp('<?php echo $helplang; ?>/assoc_help.php');"><?php echo uiTextSnippet('help'); ?></a>
      </p>
    </header>
    <div class='modal-body'>
      <p>
        <?php if ($allowAdd) { ?>
        <button class='btn btn-secondary' id='addnew' type='button'><?php echo uiTextSnippet('add'); ?></button>
        <?php } ?>
      </p>
      <table class='table table-sm' id='associationstbl' <?php if (!$assoccount) {echo " style='display: none'";} ?>>
        <thead>
          <tr>
            <th><?php echo uiTextSnippet('action'); ?></th>
            <th><?php echo uiTextSnippet('description'); ?></th>
          </tr>
        </thead>
        <tbody id='associationstblbody'>
          <?php
          if ($assocresult && $assoccount) {
            while ($assoc = tng_fetch_assoc($assocresult)) {
              //run query for name or family
              $assoc['allow_living'] = 1;
              if ($assoc['reltype'] == 'I') {
                $query = "SELECT firstname, lastname, lnprefix, nameorder, prefix, suffix, living, private, branch FROM $people_table WHERE personID=\"{$assoc['passocID']}\"";
                $nameresult = tng_query($query);
                $row = tng_fetch_assoc($nameresult);
                $rights = determineLivingPrivateRights($row);
                $row['allow_living'] = $rights['living'];
                $row['allow_private'] = $rights['private'];
                $assocname = getName($row) . " ({$assoc['passocID']})";
                tng_free_result($nameresult);
              } else {
                $query = "SELECT husband, wife, gedcom, familyID, living, private FROM $families_table WHERE familyID=\"{$assoc['passocID']}\"";
                $nameresult = tng_query($query);
                $row = tng_fetch_assoc($nameresult);
                $rights = determineLivingPrivateRights($row);
                $row['allow_living'] = $rights['living'];
                $row['allow_private'] = $rights['private'];
                $assocname = getFamilyName($row);
                tng_free_result($nameresult);
              }
              $assocname .= ": " . $assoc['relationship'];
              $assocname = cleanIt($assocname);
              $truncated = truncateIt($assocname, 75);
              $actionstr = '';
              if ($allowEdit) {
                $actionstr .= "<a href='#' onclick=\"return editAssociation({$assoc['assocID']});\" title='" . uiTextSnippet('edit') . "'>\n";
                $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
                $actionstr .= "</a>";
              }
              if ($allowDelete) {
                $actionstr .= "<a href='#' onclick=\"return deleteAssociation({$assoc['assocID']},'$personID');\" title='" . uiTextSnippet('delete') . "'>\n";
                $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
                $actionstr .= "</a>";
              }
              echo "<tr id=\"row_{$assoc['assocID']}\">\n";
                echo "<td>$actionstr</td>\n";
                echo "<td>$truncated</td>\n";
              echo "</tr>\n";
            }
            tng_free_result($assocresult);
          }
          ?>
        </tbody>
      </table>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <button class='btn btn-primary' id='finish' type='button'><?php echo uiTextSnippet('finish'); ?></button>
    </footer>
  </form>
</div>

<div id='addassociation' <?php if ($assoccount) {echo "style='display: none'";} ?>>
  <form name='newassocform1' action='' onSubmit="return addAssociation(this);">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('addnewassoc'); ?></h4>
      <p>
        <a id='help' href='#'><?php echo uiTextSnippet('help'); ?></a>
      </p>
    </header>
    <div class='modal-body'>
      <table class='table table-sm'>
        <tr>
          <td colspan='2'>
            <input name='reltype' type='radio' value='I' checked 
                   onclick="activateAssocType('I');"/> <?php echo uiTextSnippet('person'); ?>&nbsp;
            <input name='reltype' type='radio' value='F'
                   onclick="activateAssocType('F');"/> <?php echo uiTextSnippet('family'); ?>
          </td>
        </tr>
        <tr>
          <td>
            <span id='person_label'><?php echo uiTextSnippet('personid'); ?></span>
            <span id='family_label' style='display: none'><?php echo uiTextSnippet('familyid'); ?></span>:
          </td>
          <td>
            <input id='passocID' name='passocID' type='text'>
            &nbsp;<?php echo uiTextSnippet('or'); ?>&nbsp;
            <a id='find' href='#' title="<?php echo uiTextSnippet('find'); ?>">
              <img class='icon-sm-inline' src='svg/magnifying-glass.svg'>
            </a>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('relationship'); ?>:</td>
          <td><input name='relationship' type='text'></td>
        </tr>
        <tr>
          <td colspan='2'><input name='revassoc' type='checkbox' value='1'> <?php echo uiTextSnippet('revassoc'); ?>:
          </td>
        </tr>
      </table>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <input name='personID' type='hidden' value="<?php echo $personID; ?>">
      <input name='orgreltype' type='hidden' value="<?php echo $orgreltype; ?>">
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
      <input name='cancel' type='button' value="<?php echo uiTextSnippet('cancel'); ?>"
             onclick="gotoSection('addassociation', 'associations');">
    </footer>
  </form>
  <br>
</div>
<div id='editassociation' style='display: none;'></div>
<script src="js/associations.js"></script>
<script>
    var helpLang = '<?php echo $helplang; ?>';
    var assignedBranch = '<?php echo $assignedbranch; ?>';
    
    $('#help').on('click', function () {
        return openHelp(helpLang + '/assoc_help.php#add', 'newwindow', 'height=500, width=700, resizable=yes, scrollbars=yes');
        newwindow.focus();
    });
  
    $('#addnew').on('click', function () {
        document.newassocform1.reset();
        assocType = 'I';
        gotoSection('associations', 'addassociation');
    });
    
    $('#finish').on('click', function () {
        tnglitbox.remove();
    });
    
    $('#find').on('click', function () {
        return findItem(assocType, 'passocID', null, assignedBranch);
    });
</script>