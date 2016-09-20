<?php
/**
 * Name history: admin_editcitation.php
 */

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

$query = "SELECT citations.sourceID AS sourceID, description, page, quay, citedate, citetext, note, title FROM citations LEFT JOIN sources ON citations.sourceID = sources.sourceID WHERE citationID = '$citationID'";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['page'] = preg_replace('/\"/', '&#34;', $row['page']);
$row['citetext'] = preg_replace('/\"/', '&#34;', $row[citetext]);
$row['note'] = preg_replace('/\"/', '&#34;', $row[note]);

$helplang = findhelp('citations_help.php');

header('Content-type:text/html; charset=' . $session_charset);
?>
<form action='' name='citeform3' onsubmit="return updateCitation(this);">
  <header class='modal-header'>
    <h4><?php echo uiTextSnippet('modifycite'); ?></h4>
    <p>
      <a href='#' onclick="return openHelp('<?php echo $helplang; ?>/citations_help.php#add', 'newwindow', 'height=500,width=700,resizable=yes,scrollbars=yes'); newwindow.focus();"><?php echo uiTextSnippet('help'); ?></a>
    </p>
  </header>
  <div class='modal-body'>
    <table class='table table-sm'>
      <?php if ($row['sourceID']) { ?>
        <tr>
          <td><?php echo uiTextSnippet('source'); ?>:</td>
          <td>
            <input id='sourceID2' name='sourceID' type='text' value="<?php echo $row['sourceID']; ?>"> &nbsp;<?php echo uiTextSnippet('or'); ?> &nbsp;
            <input type='button' value="<?php echo uiTextSnippet('find'); ?>"
                   onclick="return initFilter('editcitation', 'findsource', 'sourceID2', 'sourceTitle2');">
            <input type='button' value="<?php echo uiTextSnippet('create'); ?>"
                   onclick="return initNewItem('source', document.newsourceform.sourceID, 'sourceID2', 'sourceTitle2', 'editcitation', 'newsource');">
          </td>
        </tr>
        <tr>
          <td></td>
          <td id='sourceTitle2'><?php echo $row['title']; ?></td>
        </tr>
      <?php
      } else {
        echo '<tr><td>' . uiTextSnippet('description') . ":</td><td>\n";
        echo "<input name='description' type='text' value=\"{$row['description']}\">\n";
        echo "<input name='sourceID' type='hidden' value=''></td>\n";
      }
      ?>
      <tr>
        <td><?php echo uiTextSnippet('page'); ?>:</td>
        <td><input name='citepage' type='text' value="<?php echo $row['page']; ?>"></td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('reliability'); ?>*:</td>
        <td>
          <select name="quay">
            <option value=''></option>
            <option value='0'<?php if ($row['quay'] == '0') {echo ' selected';} ?>>0</option>
            <option value='1'<?php if ($row['quay'] == '1') {echo ' selected';} ?>>1</option>
            <option value='2'<?php if ($row['quay'] == '2') {echo ' selected';} ?>>2</option>
            <option value='3'<?php if ($row['quay'] == '3') {echo ' selected';} ?>>3</option>
          </select> <span>(<?php echo uiTextSnippet('relyexplain'); ?>)</span>
        </td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('citedate'); ?>:</td>
        <td>
          <input name='citedate' type='text' value="<?php echo $row['citedate']; ?>" onBlur="checkDate(this);">
        </td>
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
    <input name='citationID' type='hidden' value="<?php echo $citationID; ?>">
    <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    <p>
      <a href='#' onclick="return gotoSection('editcitation', 'citations');"><?php echo uiTextSnippet('cancel'); ?></a>
    </p>
  </footer>
</form>