<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

function showDiv($type) {
  global $thumbmaxw;
  global $mostwanted_table;
  global $media_table;
  global $people_table;
  global $mediatypes_assoc;
  global $mediapath;
  global $allow_add;
  global $allow_delete;
  global $allow_edit;
  global $rootpath;

  if ($allow_add) {
    echo "<form action=\"\" style=\"margin:0;padding-bottom:5px\" method=\"post\" name=\"form$type\" id=\"form$type\">\n";
    echo "<input type='button' value=\"" . uiTextSnippet('addnew') . "\" onclick=\"return openMostWanted('$type','');\">\n";
    echo "</form>\n";
  }


  echo "<div id=\"order$type" . "divs\">\n";
  echo "<table id=\"order$type" . "tbl\" width=\"100%\" cellpadding=\"3\" cellspacing=\"1\">\n";
  echo "<tr>\n";
  echo "<td style='width: 55px'>" . uiTextSnippet('text_sort') . "</td>\n";
  echo "<td style='width: " . ($thumbmaxw + 10) . "px'>" . uiTextSnippet('thumb') . "</td>\n";
  echo "<td>" . uiTextSnippet('description') . "</td>\n";
  echo "</tr>\n";
  echo "</table>\n";


  $query = "SELECT DISTINCT $mostwanted_table.ID as mwID, mwtype, thumbpath, usecollfolder, mediatypeID, $media_table.description as mtitle, $mostwanted_table.description as mwdesc, $mostwanted_table.title as title FROM $mostwanted_table
    LEFT JOIN $media_table ON $mostwanted_table.mediaID = $media_table.mediaID
    LEFT JOIN $people_table ON $mostwanted_table.personID = $people_table.personID AND $mostwanted_table.gedcom = $people_table.gedcom
    WHERE mwtype = \"$type\" ORDER BY ordernum";
  $result = tng_query($query);
  //echo $query;

  while ($lrow = tng_fetch_assoc($result)) {
    $lmediatypeID = $lrow['mediatypeID'];
    $usefolder = $lrow['usecollfolder'] ? $mediatypes_assoc[$lmediatypeID] : $mediapath;

    $truncated = substr($lrow['mwdesc'], 0, 90);
    $truncated = strlen($lrow['mwdesc']) > 90 ? substr($truncated, 0, strrpos($truncated, ' ')) . '&hellip;' : $lrow['mwdesc'];
    echo "<div class='sortrow' id=\"order{$lrow['mwtype']}" . "divs_{$lrow['mwID']}\" style='clear: both' onmouseover=\"showEditDelete('{$lrow['mwID']}');\" onmouseout=\"hideEditDelete('{$lrow['mwID']}');\">";
    echo "<table width=\"100%\" cellpadding=\"5\" cellspacing=\"1\"><tr id=\"row_{$lrow['mwID']}\">\n";
    echo "<td class='dragarea'>";
      echo "<img src='img/admArrowUp.gif' alt=''>" . uiTextSnippet('drag') . "\n";
      echo "<img src='img/admArrowDown.gif' alt=''>\n";
    echo "</td>\n";
    echo "<td style=\"width:" . ($thumbmaxw + 6) . "px;text-align:center;\">";
    if ($lrow['thumbpath'] && file_exists("$rootpath$usefolder/" . $lrow['thumbpath'])) {
      $size = getimagesize("$rootpath$usefolder/" . $lrow['thumbpath']);
      echo "<img src=\"$usefolder/" . str_replace("%2F", "/", rawurlencode($lrow['thumbpath'])) . "\" $size[3]} id=\"img_{$lrow['mwID']}\" alt=\"{$lrow['mtitle']}\">";
    } else {
      echo "&nbsp;";
    }
    echo "</td>\n";
    echo "<td>";
    if ($allow_edit) {
      echo "<a href='#' onclick=\"return openMostWanted('{$lrow['mwtype']}','{$lrow['mwID']}');\" id=\"title_{$lrow['mwID']}\">{$lrow['title']}</a>";
    } else {
      echo "<u id=\"title_{$lrow['mwID']}\">{$lrow['title']}</u>";
    }
    echo "<br><span id=\"desc_{$lrow['mwID']}\">$truncated</span><br>";
    echo "<div id=\"del_{$lrow['mwID']}\" class=\"small\" style=\"color:gray;visibility:hidden\">";
    if ($allow_edit) {
      echo "<a href='#' onclick=\"return openMostWanted('{$lrow['mwtype']}','{$lrow['mwID']}');\">" . uiTextSnippet('edit') . "</a>";
      if ($allow_delete) {
        echo " | ";
      }
    }
    if ($allow_delete) {
      echo "<a href='#' onclick=\"return removeFromMostWanted('{$lrow['mwtype']}','{$lrow['mwID']}');\">" . uiTextSnippet('delete') . "</a>";
    }
    echo "</div>";
    echo "</td>\n";
    echo "</tr></table>";
    echo "</div>\n";
  }
  tng_num_rows($result);
  tng_free_result($result);
  echo "</div>\n";
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('mostwanted'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body onLoad="startMostWanted()">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('misc-mostwanted', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_misc.php", uiTextSnippet('menu'), "misc"]);
    $navList->appendItem([true, "admin_notelist.php", uiTextSnippet('notes'), "notes"]);
    $navList->appendItem([true, "admin_whatsnewmsg.php", uiTextSnippet('whatsnew'), "whatsnew"]);
    $navList->appendItem([true, "admin_mostwanted.php", uiTextSnippet('mostwanted'), "mostwanted"]);
    echo $navList->build("mostwanted");
    ?>
    <br>
    <a href="mostwanted.php" title='<?php echo uiTextSnippet('preview') ?>'>
      <img class='icon-sm' src='svg/eye.svg'>
    </a>
    <table class='table table-sm'>
      <tr>
        <td>
          <?php
          echo displayToggle("plus0", 1, "personarea", uiTextSnippet('mysperson'), "");
          echo "<div id=\"personarea\">\n<br>\n";
          showDiv('person');
          echo "<br></div>\n";

          echo "<br>\n";

          echo displayToggle("plus1", 1, "photoarea", uiTextSnippet('mysphoto'), "");
          echo "<div id=\"photoarea\">\n<br>\n";
          showDiv('photo');
          echo "</div>\n";
          ?>
        </td>
      </tr>

    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/admin.js"></script>
  <script src="js/mostwanted.js"></script>
  <script src="js/selectutils.js"></script>
  <script>
    var mwlitbox;
    var tnglitbox;
    var thumbwidth = <?php echo($thumbmaxw + 6); ?>;
    var tree = "<?php echo $assignedtree; ?>";
  </script>
</body>
</html>
