<?php
include("begin.php");
include("adminlib.php");

include("checklogin.php");

if ($assignedtree) {
  $wherestr = "WHERE gedcom = \"$assignedtree\"";
  $tree = $assignedtree;
} else {
  $wherestr = "";
}
$treequery = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";
initMediaTypes();
header("Content-type:text/html; charset=" . $session_charset);
?>
<div id='finddiv'>
    <form name='find2' onsubmit="getNewMedia(this,1); return false;">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('addmedia'); ?></h4>
    </header>
    <div class='modal-body'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('mediatype'); ?>:</td>
          <td><?php echo uiTextSnippet('tree'); ?>:</td>
          <td colspan='2'><span><?php echo uiTextSnippet('searchfor'); ?>: </span></td>
        </tr>
        <tr>
          <td>
            <select name='mediatypeID'
                    onChange="toggleHeadstoneCriteria(document.find2,this.options[this.selectedIndex].value); getNewMedia(document.find2,0);">
              <option value=''><?php echo uiTextSnippet('all'); ?></option>
              <?php
              foreach ($mediatypes as $mediatype) {
                $msgID = $mediatype['ID'];
                echo "  <option value=\"$msgID\">" . $mediatype['display'] . "</option>\n";
              }
              ?>
            </select>
          </td>
          <td>
            <select name='searchtree' onchange="getNewMedia(document.find2, 0)">
              <?php
              if (!$assignedtree) {
                echo "  <option value=''>" . uiTextSnippet('alltrees') . "</option>\n";
              }
              $treeresult = tng_query($treequery) or die (uiTextSnippet('cannotexecutequery') . ": $treequery");
              while ($treerow = tng_fetch_assoc($treeresult)) {
                echo "  <option value=\"{$treerow['gedcom']}\"";
                if ($treerow['gedcom'] == $tree) {
                  echo " selected";
                }
                echo ">{$treerow['treename']}</option>\n";
              }
              tng_free_result($treeresult);
              ?>
            </select>
          </td>
          <td><input id='searchstring' name='searchstring' type='text' value="<?php echo $searchstring; ?>">
          </td>
          <td>
            <input name='searchbutton' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
            <span id='spinner1' style='display: none'><img src='img/spinner.gif'></span>
          </td>
        </tr>
      </table>
      <table class='table table-sm'>
        <tr id='hsstatrow' style='display: none'>
          <td><?php echo uiTextSnippet('status'); ?>:</td>
          <td><?php echo uiTextSnippet('cemetery'); ?>:</td>
        </tr>
        <tr id='cemrow' style='display: none'>
          <td>
            <select name='hsstat' onchange="getNewMedia(document.find2, 0)">
              <option value=''>&nbsp;</option>
              <option value="<?php echo uiTextSnippet('notyetlocated'); ?>"><?php echo uiTextSnippet('notyetlocated'); ?></option>
              <option value="<?php echo uiTextSnippet('located'); ?>"><?php echo uiTextSnippet('located'); ?></option>
              <option value="<?php echo uiTextSnippet('unmarked'); ?>"><?php echo uiTextSnippet('unmarked'); ?></option>
              <option value="<?php echo uiTextSnippet('missing'); ?>"><?php echo uiTextSnippet('missing'); ?></option>
              <option value="<?php echo uiTextSnippet('cremated'); ?>"><?php echo uiTextSnippet('cremated'); ?></option>
            </select>
          </td>
          <td>
            <select name='cemeteryID' onchange="getNewMedia(document.find2, 0)" style='width: 380px'>
              <option selected></option>
              <?php
              $query = "SELECT cemname, cemeteryID, city, county, state, country FROM $cemeteries_table ORDER BY country, state, county, city, cemname";
              $cemresult = tng_query($query);
              while ($cemrow = tng_fetch_assoc($cemresult)) {
                $cemetery = "{$cemrow['country']}, {$cemrow['state']}, {$cemrow['county']}, {$cemrow['city']}, {$cemrow['cemname']}";
                echo "  <option value=\"{$cemrow['cemeteryID']}\"";
                if ($cemeteryID == $cemrow['cemeteryID']) {
                  echo " selected";
                }
                echo ">$cemetery</option>\n";
              }
              ?>
            </select>
          </td>
        </tr>
      </table>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'></footer>
  </form>
    <div id='newmedia' style='width: 620px; height: 430px; overflow: auto'></div>
</div>