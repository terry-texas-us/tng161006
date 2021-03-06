<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

$tng_search_langs = $_SESSION['tng_search_langs'] = 1;
if ($newsearch) {
  $exptime = 0;
  setcookie('tng_search_langs_post[search]', $searchstring, $exptime);
  setcookie('tng_search_langs_post[tngpage]', 1, $exptime);
  setcookie('tng_search_langs_post[offset]', 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = stripslashes($_COOKIE['tng_search_langs_post']['search']);
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_langs_post']['tngpage'];
    $offset = $_COOKIE['tng_search_langs_post']['offset'];
  } else {
    $exptime = 0;
    setcookie('tng_search_langs_post[tngpage]', $tngpage, $exptime);
    setcookie('tng_search_langs_post[offset]', $offset, $exptime);
  }
}
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = '';
  $tngpage = 1;
}

$wherestr = $searchstring ? "WHERE display LIKE \"%$searchstring%\" OR folder LIKE \"%$searchstring%\"" : '';
$query = "SELECT languageID, display, folder, charset FROM languages $wherestr ORDER BY display LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(languageID) AS lcount FROM languages $wherestr";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['lcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('languages'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
<body id="admin-languages">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('languages', $message);
    $navList = new navList('');
    //    $navList->appendItem([true, 'languagesBrowse.php', uiTextSnippet('browse'), 'findlang']);
    $navList->appendItem([$allowAdd, 'languagesAdd.php', uiTextSnippet('add'), 'addlanguage']);
    echo $navList->build('findlang');
    ?>
    <div>
      <form name='form1' action='languagesBrowse.php'>
        <?php echo uiTextSnippet('searchfor'); ?>: <input class='longfield' name='searchstring' type='text' value="<?php echo $searchstring; ?>">
        <input name='findlang' type='hidden' value='1'>
        <input name='newsearch' type='hidden' value='1'>
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

      <table class="table table-sm table-striped">
        <thead>
          <tr>
            <th><?php echo uiTextSnippet('action'); ?></th>
            <th><?php echo uiTextSnippet('display'); ?></th>
            <th><?php echo uiTextSnippet('folder'); ?></th>
            <th><?php echo uiTextSnippet('charset'); ?></th>
          </tr>
        </thead>
        <?php
        if ($numrows) {
          $actionstr = '';
          if ($allowEdit) {
            $actionstr .= "<a href=\"languagesEdit.php?languageID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
            $actionstr .= "</a>\n";
          }
          if ($allowDelete) {
            $actionstr .= "<a href='#' onclick=\"if(confirm('" . uiTextSnippet('conflangdelete') . "' )){deleteIt('language',xxx);} return false;\" title='" . uiTextSnippet('delete') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
            $actionstr .= "</a>\n";
          }
          while ($row = tng_fetch_assoc($result)) {
            $newactionstr = preg_replace('/xxx/', $row['languageID'], $actionstr);
            echo "<tr id=\"row_{$row['languageID']}\"><td>\n";
            echo "<div class='action-btns2'>\n$newactionstr</div>\n";
            echo "</td>\n";
            echo "<td>{$row['display']}</td>\n";
            echo "<td>{$row['folder']}</td>\n";
            echo "<td>{$row['charset']}</td></tr>\n";
          }
        }
        ?>
      </table>
      <?php
      if ($numrows) {
        echo buildSearchResultPagination($totrows, "languages.php?searchstring=$searchstring&amp;offset", $maxsearchresults, 5);
      } else {
        echo "<div class='alert alert-warning'>" . uiTextSnippet('norecords') . "</div>\n";
      }
      tng_free_result($result);
      ?>
    </div>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/admin.js"></script>
</body>
</html>
