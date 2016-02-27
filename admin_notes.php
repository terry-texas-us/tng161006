<?php
include("begin.php");
include("adminlib.php");

include("checklogin.php");

$query = "SELECT $eventtypes_table.eventtypeID, tag, display FROM $events_table 
    LEFT JOIN  $eventtypes_table on $eventtypes_table.eventtypeID = $events_table.eventtypeID 
    WHERE eventID=\"$eventID\"";
$eventtypes = tng_query($query);
$eventtype = tng_fetch_assoc($eventtypes);

if ($eventtype['display']) {
  $dispvalues = explode("|", $eventtype['display']);
  $numvalues = count($dispvalues);
  if ($numvalues > 1) {
    $displayval = "";
    for ($i = 0; $i < $numvalues; $i += 2) {
      $lang = $dispvalues[$i];
      if ($mylanguage == $languages_path . $lang) {
        $eventtypedesc = $dispvalues[$i + 1];
        break;
      }
    }
  } else {
    $eventtypedesc = $eventtype['display'];
  }
} elseif ($eventtype['tag']) {
  $eventtypedesc = $eventtype['tag'];
} elseif ($eventID) {
  $eventtypedesc = uiTextSnippet($eventID);
} else {
  $eventtypedesc = uiTextSnippet('general');
}
tng_free_result($eventtypes);

$helplang = findhelp("notes_help.php");

header("Content-type:text/html; charset=" . $session_charset);

$query = "SELECT $notelinks_table.ID as ID, $xnotes_table.note as note, noteID, secret FROM ($notelinks_table, $xnotes_table)
    WHERE $notelinks_table.xnoteID = $xnotes_table.ID AND $notelinks_table.gedcom = $xnotes_table.gedcom
        AND persfamID=\"$persfamID\" AND $notelinks_table.gedcom =\"$tree\" AND eventID = \"$eventID\" ORDER BY ordernum, ID";
$notelinks = tng_query($query);
$notecount = tng_num_rows($notelinks);
?>
<div id='notelist'<?php if (!$notecount) {echo " style='display: none'";} ?>>
  <form name='form1'>
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('notes') . ": $eventtypedesc"; ?></h4>
      <p>
        <a href='#' onclick="return openHelp('<?php echo $helplang; ?>/notes_help.php');"><?php echo uiTextSnippet('help'); ?></a>
      </p>
    </header>
    <div class='modal-body'>
      <p>
        <?php if ($allow_add) { ?>
          <input type='button' value="  <?php echo uiTextSnippet('addnew'); ?>  "
                 onclick="document.form2.reset(); gotoSection('notelist', 'addnote');" />
        <?php } ?>
        <input type='button' value="  <?php echo uiTextSnippet('finish'); ?>  " onclick="tnglitbox.remove();" />
      </p>
      <table class='table table-sm' id='notestbl' <?php if (!$notecount) {echo " style='display: none'";} ?>>
        <thead>
          <tr>
            <th><?php echo uiTextSnippet('text_sort'); ?></th>
            <th><?php echo uiTextSnippet('action'); ?></th>
            <th><?php echo uiTextSnippet('note'); ?></th>
          </tr>
        </thead>
      </table>
      <div id='notes' style='width: 460px;'>
        <?php
        if ($notelinks && $notecount) {

          while ($note = tng_fetch_assoc($notelinks)) {
            $citquery = "SELECT citationID FROM $citations_table WHERE gedcom = \"$tree\" AND ";
            if ($note['noteID']) {
              $citquery .= "((persfamID = \"$persfamID\" AND eventID = \"N{$note['ID']}\") OR persfamID = \"{$note['noteID']}\")";
            } else {
              $citquery .= "persfamID = \"$persfamID\" AND eventID = \"N{$note['ID']}\"";
            }
            $citresult = tng_query($citquery) or die(uiTextSnippet('cannotexecutequery') . ": $citquery");
            $iconColor = tng_num_rows($citresult) ? "icon-info" : "icon-muted";
            tng_free_result($citresult);

            $note['note'] = cleanIt($note['note']);
            $truncated = truncateIt($note['note'], 75);
            $actionstr = '';
            if ($allow_edit) {
              $actionstr .= "<a href='#' onclick=\"return editNote({$note['ID']});\" title='" . uiTextSnippet('edit') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
              $actionstr .= "</a>";
            }
            if ($allow_delete) {
              $actionstr .= "<a href='#' onclick=\"return deleteNote({$note['ID']},'$persfamID','$tree','$eventID');\" title='" . uiTextSnippet('delete') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
              $actionstr .= "</a>";
            }
            $citesLink = "<a id=\"citesiconN{$note['ID']}\" href='#' onclick=\"return showCitationsInside('N{$note['ID']}','{$note['noteID']}', '$persfamID');\" title='" . uiTextSnippet('citations') . "'>\n";
            $citesLink .= "<img class='icon-sm icon-citations $iconColor' data-src='svg/archive.svg'>\n";
            $citesLink .= "</a>";
            echo "<div class=\"sortrow\" id=\"notes_{$note['ID']}\">";
              echo "<table class='table table-sm'>";
                echo "<tr id=\"row_{$note['ID']}\">";
                  echo "<td class='dragarea'>\n";
                    echo "<img src='img/admArrowUp.gif' alt=''><br>\n";
                    echo "<img src='img/admArrowDown.gif' alt=''>\n";
                  echo "</td>";
                  echo "<td>$actionstr$citesLink</td>";
                  echo "<td>$truncated</td>";
                echo "</tr>\n";
              echo "</table>\n";
            echo "</div>\n";
          }
          tng_free_result($notelinks);
        }
        ?>
      </div>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'></footer>
  </form>
</div>
<div id='addnote'<?php if ($notecount) {echo " style='display: none'";} ?>>
  <form name='form2' action='' onSubmit="return addNote(this);">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('addnewnote'); ?> |
        <a href="#"
           onclick="return openHelp('<?php echo $helplang; ?>/notes_help.php');"><?php echo uiTextSnippet('help'); ?></a>
      </h4>
    </header>
    <div class='modal-body'>
      <?php echo uiTextSnippet('note'); ?>:
      <textarea class='form-control' name='note' wrap='soft'></textarea>
      <label>
        <?php echo uiTextSnippet('private'); ?>
        <input class='form-control' name='private' type='checkbox' value='1'>
      </label>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <input name='persfamID' type='hidden' value="<?php echo $persfamID; ?>" />
      <input name='tree' type='hidden' value="<?php echo $tree; ?>" />
      <input name='eventID' type='hidden' value="<?php echo $eventID; ?>" />
      
      <button class='btn btn-primary-outline' name='submit' type='submit'><?php echo uiTextSnippet('save'); ?></button>
      <button class='btn' name='cancel' type='button' onclick="gotoSection('addnote', 'notelist');"><?php echo uiTextSnippet('cancel'); ?></button>
    </footer>
  </form>
</div>
<div id='editnote' style='display: none;'></div>
<div id='citationslist' style='display: none;'></div>
<script src="js/citations.js"></script>