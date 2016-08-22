<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

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
      if ($mylanguage == $languagesPath . $lang) {
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
  $eventtypedesc = uiTextSnippet($eventID) ? uiTextSnippet($eventID) : uiTextSnippet('notes');
} else {
  $eventtypedesc = uiTextSnippet('general');
}
tng_free_result($eventtypes);

$helplang = findhelp("citations_help.php");

header("Content-type:text/html; charset=" . $session_charset);

$xnotestr = $noteID ? " OR persfamID = \"$noteID\"" : "";
$query = "SELECT citationID, $citations_table.sourceID as sourceID, description, title, shorttitle
    FROM $citations_table LEFT JOIN $sources_table on $citations_table.sourceID = $sources_table.sourceID AND $sources_table.gedcom = $citations_table.gedcom
    WHERE ((persfamID = \"$persfamID\" AND eventID = \"$eventID\")$xnotestr) ORDER BY ordernum, citationID";
$citresult = tng_query($query);
$citationcount = tng_num_rows($citresult);
?>

<div id='citations'<?php if (!$citationcount) {echo " style=\"display:none\"";} ?>>
  <form name='citeform'>
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('citations') . ": $eventtypedesc"; ?></h4>
      <p>
        <a href="#" onclick="return openHelp('<?php echo $helplang; ?>/citations_help.php');"><?php echo uiTextSnippet('help'); ?></a>
      </p>
    </header>
    <div class='modal-body'>
      <p>
        <?php if ($allowAdd) { ?>
          <input type='button' value="  <?php echo uiTextSnippet('addnew'); ?>  "
                 onclick="document.citeform2.reset();gotoSection('citations', 'addcitation');">
        <?php } ?>
        <input type='button' value="  <?php echo uiTextSnippet('finish'); ?>  " onclick="if (subpage) {
              gotoSection('citationslist', 'notelist');
            } else {
              tnglitbox.remove();
            }">
      </p>
      <table id='citationstbl' class='table table-sm' <?php if (!$citationcount) {echo " style='display: none'";} ?>>
        <tbody id='citationstblbody'>
          <tr>
            <th><?php echo uiTextSnippet('text_sort'); ?></th>
            <th><?php echo uiTextSnippet('action'); ?></th>
            <th><?php echo uiTextSnippet('title'); ?></th>
          </tr>
        </tbody>
      </table>
      <div id='cites'>
        <?php
        if ($citresult && $citationcount) {
          while ($citation = tng_fetch_assoc($citresult)) {
            $sourcetitle = $citation['title'] ? $citation['title'] : $citation['shorttitle'];
            $citationsrc = $citation['sourceID'] ? "[{$citation['sourceID']}] $sourcetitle" : $citation['description'];
            $citationsrc = cleanIt($citationsrc);
            $truncated = truncateIt($citationsrc, 75);
            $actionstr = '';
            if ($allowEdit) {
              $actionstr .= "<a href='#' onclick=\"return editCitation({$citation['citationID']});\" title='" . uiTextSnippet('edit') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
              $actionstr .= "</a>";
            }
            if ($allowDelete) {
              $actionstr .= "<a href='#' onclick=\"return deleteCitation({$citation['citationID']},'$persfamID','$eventID');\" title='" . uiTextSnippet('delete') . "'>\n";
              $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
              $actionstr .= "</a>";
            }
            echo "<div class=\"sortrow\" id=\"citations_{$citation['citationID']}\">";
              echo "<table class='table table-sm'>";
                echo "<tr id=\"row_{$citation['citationID']}\">";
                  echo "<td class=\"dragarea\">\n";
                  echo "<img src='img/admArrowUp.gif' alt=''><br>\n";
                  echo "<img src='img/admArrowDown.gif' alt=''>\n";
                  echo "</td>\n";
                  echo "<td>$actionstr</td>";
                  echo "<td>$truncated</td>";
                echo "</tr>\n";
              echo "</table>\n";
            echo "</div>\n";
          }
          tng_free_result($citresult);
        }
        ?>
      </div>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'></footer>
  </form>
</div>
<div id='addcitation' <?php if ($citationcount) {echo " style='display: none'";} ?>>
  <form action='' name='citeform2' onSubmit="return addCitation(this);">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('addnewcite'); ?></h4>
      <p>
        <a href='#' onclick="return openHelp('<?php echo $helplang; ?>/citations_help.php#add', 'newwindow', 'height=500,width=700,resizable=yes,scrollbars=yes'); newwindow.focus();"><?php echo uiTextSnippet('help'); ?></a>
      </p>
    </header>
    <div class='modal-body'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('sourceid'); ?>:</td>
          <td>
            <input id='sourceID' name='sourceID' type='text'>
            &nbsp;<?php echo uiTextSnippet('or'); ?> &nbsp;
            <input type='button' value="<?php echo uiTextSnippet('find'); ?>"
                   onclick="return initFilter('addcitation', 'findsource', 'sourceID', 'sourceTitle');"/>
            <input type='button' value="<?php echo uiTextSnippet('create'); ?>"
                   onclick="return initNewItem('source', document.newsourceform.sourceID, 'sourceID', 'sourceTitle', 'addcitation', 'newsource');"/>
            <?php
            if (isset($_SESSION['lastcite'])) {
              $parts = explode("|", $_SESSION['lastcite']);
              if ($parts[0] == '') {
                echo "<input type='button' value=\"" . uiTextSnippet('copylast') . "\" onclick=\"return copylast(document.citeform2,'{$parts[1]}');\">";
                echo "&nbsp; <img src=\"img/spinner.gif\" id=\"lastspinner\" style=\"vertical-align:-3px; display:none\">";
              }
            }
            ?>
          </td>
        </tr>
        <tr>
          <td></td>
          <td id='sourceTitle'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('page'); ?>:</td>
          <td><input id='citepage' name='citepage' type='text'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('reliability'); ?>:</td>
          <td>
            <select name="quay" id="quay">
              <option value=''></option>
              <option value='0'>0</option>
              <option value='1'>1</option>
              <option value="2">2</option>
              <option value="3">3</option>
            </select> (<?php echo uiTextSnippet('relyexplain'); ?>)
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('citedate'); ?>:</td>
          <td><input id='citedate' name='citedate' type='text' onBlur="checkDate(this);"></td>
        </tr>
        <tr>
          <td colspan='2'>
            <fieldset class='form-group'>
              <label for='citetext'><?php echo uiTextSnippet('actualtext'); ?></label>
              <textarea class='form-control' id='citetext' name='citetext' rows='4'></textarea>
            </fieldset>
          </td>
        </tr>
        <tr>
          <td colspan='2'>
            <fieldset class='form-group'>
              <label for='citenote'><?php echo uiTextSnippet('notes'); ?></label>
              <textarea class='form-control' id='citenote' name='citenote' rows='4'></textarea>
            </fieldset>
          </td>
        </tr>
      </table>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <input name='persfamID' type='hidden' value="<?php echo $persfamID; ?>"/>
      <input name='eventID' type='hidden' value="<?php echo $eventID; ?>"/>
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
      <p>
        <a href='#' onclick="return gotoSection('addcitation', 'citations');"><?php echo uiTextSnippet('cancel'); ?></a>
      </p>
    </footer>
  </form>
</div>
<div id='editcitation' style='display: none;'></div>

<?php $applyfilter = "applyFilter({form:'findsourceform1', fieldId:'mytitle', type:'S', destdiv:'sourceresults'});"; ?>
<div id='findsource' style='display: none;'>
  <form action="" method='post' name="findsourceform1" id="findsourceform1" onsubmit="return <?php echo $applyfilter; ?>">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('findsourceid'); ?><br>
        <span>(<?php echo uiTextSnippet('entersourcepart'); ?>)</span>
      </h4>
    </header>
    <div class='modal-body'>
      <table>
        <tr>
          <td><?php echo uiTextSnippet('title'); ?>:</td>
          <td>
            <input id='mytitle' name='mytitle' type='text'
                   onkeyup="filterChanged(event, {form: 'findsourceform1', fieldId: 'mytitle', type: 'S', destdiv: 'sourceresults'});"/>
          </td>
          <td>
            <input type='submit' value="<?php echo uiTextSnippet('search'); ?>"> 
            <input type='button' value="<?php echo uiTextSnippet('cancel'); ?>"
                   onclick="gotoSection('findsource', prevsection);">
          </td>
        </tr>
        <tr>
          <td colspan='3'>
            <input name='filter' type='radio' value='s'
                   onclick="<?php echo $applyfilter; ?>"/> <?php echo uiTextSnippet('startswith'); ?> &nbsp;&nbsp;
            <input name='filter' type='radio' value='c' checked
                   onclick="<?php echo $applyfilter; ?>"/> <?php echo uiTextSnippet('contains'); ?>
          </td>
        </tr>
      </table>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'></footer>
  </form>
  <p>
    <strong><?php echo uiTextSnippet('searchresults'); ?></strong>(<?php echo uiTextSnippet('clicktoselect'); ?>)
  </p>
  <div id='sourceresults' style='width: 605px; height: 380px; overflow: auto'></div>
</div>

<div id='newsource' style='display: none;'>
  <form id='newsourceform' name='newsourceform' action='' method='post' onsubmit="return saveSource(this);">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('addnewsource'); ?></h4>
      <p>
        <a href="#" onclick="return openHelp('<?php echo $helplang; ?>/sources_help.php#add', 'newwindow', 'height=500,width=700,resizable=yes,scrollbars=yes'); newwindow.focus();"><?php echo uiTextSnippet('help'); ?></a>
      </p>
    </header>
    <div class='modal-body'>
      <span><strong><?php echo uiTextSnippet('prefixsourceid'); ?></strong></span><br>
      <?php echo uiTextSnippet('sourceid'); ?>:
      <div class='row'>
        <div class='col-md-6'>
          <div class='input-group'>
            <span class='input-group-btn'>
              <button class='btn btn-secondary' id='generate' type='button' onclick="generateID('source', document.newsourceform.sourceIDnew);"><?php echo uiTextSnippet('generate'); ?></button>
            </span>
            <input class='form-control' id='sourceIDnew' name='sourceID' type='text'
                   onBlur="this.value = this.value.toUpperCase()" data-check-result=''>
            <span class='input-group-btn'>
              <button class='btn btn-secondary' id='check' type='button' onclick="checkID(document.newsourceform.sourceIDnew.value, 'source', 'checkmsg');"><?php echo uiTextSnippet('check'); ?></button>
            </span>
          </div>
        </div>
        <div id='checkmsg'></div>
      </div>
      <?php require 'micro_newsource.php'; ?>
      <p><strong><?php echo uiTextSnippet('sevslater'); ?></strong></p>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
      <p>
        <a href="#" onclick="gotoSection('newsource', prevsection);"><?php echo uiTextSnippet('cancel'); ?></a>
      </p>
    </footer>
  </form>
</div>