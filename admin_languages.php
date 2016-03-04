<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if ($assignedtree) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

$tng_search_langs = $_SESSION['tng_search_langs'] = 1;
if ($newsearch) {
  $exptime = 0;
  setcookie("tng_search_langs_post[search]", $searchstring, $exptime);
  setcookie("tng_search_langs_post[tngpage]", 1, $exptime);
  setcookie("tng_search_langs_post[offset]", 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = stripslashes($_COOKIE['tng_search_langs_post']['search']);
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_langs_post']['tngpage'];
    $offset = $_COOKIE['tng_search_langs_post']['offset'];
  } else {
    $exptime = 0;
    setcookie("tng_search_langs_post[tngpage]", $tngpage, $exptime);
    setcookie("tng_search_langs_post[offset]", $offset, $exptime);
  }
}

if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $tngpage = 1;
}

$wherestr = $searchstring ? "WHERE display LIKE \"%$searchstring%\" OR folder LIKE \"%$searchstring%\"" : "";
$query = "SELECT languageID, display, folder, charset FROM $languages_table $wherestr ORDER BY display LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(languageID) as lcount FROM $languages_table $wherestr";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['lcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('languages'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="admin-languages">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('languages', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_languages.php", uiTextSnippet('search'), "findlang"]);
    $navList->appendItem([$allow_add, "admin_newlanguage.php", uiTextSnippet('addnew'), "addlanguage"]);
    echo $navList->build("findlang");
    ?>
    <div>
      <form name='form1' action='admin_languages.php'>
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
          $actionstr = "";
          if ($allow_edit) {
            $actionstr .= "<a href=\"admin_editlanguage.php?languageID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
            $actionstr .= "</a>\n";
          }
          if ($allow_delete) {
            $actionstr .= "<a href='#' onclick=\"if(confirm('" . uiTextSnippet('conflangdelete') . "' )){deleteIt('language',xxx);} return false;\" title='" . uiTextSnippet('delete') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
            $actionstr .= "</a>\n";
          }
          while ($row = tng_fetch_assoc($result)) {
            $newactionstr = preg_replace("/xxx/", $row['languageID'], $actionstr);
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
