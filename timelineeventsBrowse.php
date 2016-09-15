<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

$tng_search_tlevents = $_SESSION['tng_search_tlevents'] = 1;
if ($newsearch) {
  $exptime = 0;
  $searchstring = stripslashes(trim($searchstring));
  setcookie('tng_search_tlevents_post[search]', $searchstring, $exptime);
  setcookie('tng_search_tlevents_post[tngpage]', 1, $exptime);
  setcookie('tng_search_tlevents_post[offset]', 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = stripslashes($_COOKIE['tng_search_tlevents_post']['search']);
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_tlevents_post']['tngpage'];
    $offset = $_COOKIE['tng_search_tlevents_post']['offset'];
  } else {
    $exptime = 0;
    setcookie('tng_search_tlevents_post[tngpage]', $tngpage, $exptime);
    setcookie('tng_search_tlevents_post[offset]', $offset, $exptime);
  }
}
$searchstring_noquotes = preg_replace('/\"/', '&#34;', $searchstring);
$searchstring = addslashes($searchstring);

if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = '';
  $tngpage = 1;
}

$wherestr = $searchstring ? "WHERE evyear LIKE \"%$searchstring%\" OR evdetail LIKE \"%$searchstring%\"" : '';
$query = "SELECT tleventID, evyear, endyear, evtitle, evdetail FROM $tlevents_table $wherestr ORDER BY ABS(evyear), tleventID LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(tleventID) AS tlcount FROM $tlevents_table $wherestr";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['tlcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('tlevents'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="tlevents">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('tlevents', $message);
    $navList = new navList('');
    //    $navList->appendItem([true, 'timelineeventsBrowse.php', uiTextSnippet('browse'), 'findtimeline']);
    $navList->appendItem([$allowAdd, 'timelineeventsAdd.php', uiTextSnippet('add'), 'addtlevent']);
    echo $navList->build('findtimeline');
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <div>
            <form action="timelineeventsBrowse.php" name='form1'>
              <?php echo uiTextSnippet('searchfor'); ?>: <input class='longfield' name='searchstring' type='text' value="<?php echo $searchstring_noquotes; ?>">
              <input name="findtlevent" type='hidden' value='1'>
              <input name="newsearch" type='hidden' value='1'>
              <input name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
              <input name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>"
                     onClick="document.form1.searchstring.value = '';">
            </form>
            <br>

            <?php
            $numrowsplus = $numrows + $offset;
            if (!$numrowsplus) {
              $offsetplus = 0;
            }
            echo displayListLocation($offsetplus, $numrowsplus, $totrows);
            ?>
            <form action="admin_deleteselected.php" method='post' name="form2">
              <?php
              if ($allowDelete) {
                ?>
                <p>
                  <input name='selectall' type='button' value="<?php echo uiTextSnippet('selectall'); ?>" 
                         onClick="toggleAll(1);">
                  <input name='clearall' type='button' value="<?php echo uiTextSnippet('clearall'); ?>"
                         onClick="toggleAll(0);">
                  <input name='xtimeaction' type='submit' value="<?php echo uiTextSnippet('deleteselected'); ?>"
                         onClick="return confirm('<?php echo uiTextSnippet('confdeleterecs'); ?>');">
                </p>
                <?php
              }
              ?>
              <table class="table table-sm table-striped">
                <tr>
                  <th><?php echo uiTextSnippet('action'); ?></th>
                  <?php if ($allowDelete) { ?>
                    <th><?php echo uiTextSnippet('select'); ?></th>
                  <?php } ?>
                  <th><?php echo uiTextSnippet('evyear'); ?></th>
                  <th><?php echo uiTextSnippet('enddt'); ?></th>
                  <th><?php echo uiTextSnippet('evtitle'); ?></th>
                  <th><?php echo uiTextSnippet('evdetail'); ?></th>
                </tr>
                <?php
                if ($numrows) {
                $actionstr = '';
                if ($allowEdit) {
                  $actionstr .= "<a href=\"timelineeventsEdit.php?tleventID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
                  $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
                  $actionstr .= "</a>\n";
                }
                if ($allowDelete) {
                  $actionstr .= "<a href='#' onClick=\"if(confirm('" . uiTextSnippet('confdeletetlevent') . "' )){deleteIt('tlevent',xxx);} return false;\" title='" . uiTextSnippet('delete') . "'>\n";
                  $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
                  $actionstr .= "</a>\n";
                }
                while ($rowcount < $numrows && $row = tng_fetch_assoc($result)) {
                  $newactionstr = preg_replace('/xxx/', $row['tleventID'], $actionstr);
                  echo "<tr id=\"row_{$row['tleventID']}\"><td><div class=\"action-btns2\">$newactionstr</div></td>\n";
                  if ($allowDelete) {
                    echo "<td><input name=\"del{$row['tleventID']}\" type='checkbox' value='1'></td>";
                  }
                  echo "<td>{$row['evyear']}</td>\n";
                  echo "<td>{$row['endyear']}</td>";
                  echo "<td>{$row['evtitle']}</td>";
                  echo "<td>{$row['evdetail']}</td></tr>\n";
                }
                ?>
              </table>
              <?php
              echo buildSearchResultPagination($totrows, "timelineeventsBrowse.php?searchstring=$searchstring&amp;offset", $maxsearchresults, 5);
            } else {
              echo "<div class='alert alert-warning'>" . uiTextSnippet('norecords') . "</div>\n";
            }
            tng_free_result($result);
            ?>
            </form>

          </div>
        </td>
      </tr>
    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/admin.js"></script>
</body>
</html>
