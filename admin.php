<?php
require 'begin.php';
require 'adminlib.php';

$admin_login = 2;
require 'checklogin.php';
require 'version.php';

function adminMenuItem($destination, $label, $message, $icon) {
  $menu = "<div class='col-sm-6 col-md-4 col-lg-3'>\n";
  $menu .= "<a class='admincell' href='$destination'>\n";
    $menu .= "<img class='icon-lg icon-admin' src='{$icon}' alt='{$label}'>\n";
    $menu .= "<h5>$label</h5>\n";
    $menu .= "<span class='hidden-md-down'>$message</span>\n";
  $menu .= "</a>\n";
  $menu .= "</div>\n";

  return $menu;
}
$genmsg = $mediamsg = "";
if ($allow_add) {
  $genmsg .= uiTextSnippet('add') . " | ";
  $mediamsg = $genmsg;
} elseif ($allow_media_add) {
  $mediamsg = uiTextSnippet('add') . " | ";
}
$genmsg .= uiTextSnippet('find2') . " | ";
$mediamsg . uiTextSnippet('find2') . " | ";
if ($allow_edit) {
  $genmsg .= uiTextSnippet('edit') . " | ";
  $mediamsg .= uiTextSnippet('edit') . " | ";
} elseif ($allow_media_edit) {
  $mediamsg .= uiTextSnippet('edit') . " | ";
}
if ($allow_delete) {
  $genmsg .= uiTextSnippet('delete') . " | ";
  $mediamsg .= uiTextSnippet('delete') . " | ";
} elseif ($allow_media_delete) {
  $mediamsg .= uiTextSnippet('delete') . " | ";
}
$sourcesmsg = $peoplemsg = $familiesmsg = $treesmsg = $cemeteriesmsg = $timelinemsg = $placesmsg = $genmsg;
$mediamsg .= uiTextSnippet('text_sort') . " | ";
if ($allow_edit) {
  $peoplemsg .= uiTextSnippet('reviewsh') . " | ";
  $familiesmsg .= uiTextSnippet('reviewsh') . " | ";
}
if ($allow_edit && $allow_delete) {
  $peoplemsg .= uiTextSnippet('merge') . " | ";
  $placesmsg .= uiTextSnippet('merge') . " | ";
  $sourcesmsg .= uiTextSnippet('merge') . " | ";
}
$treesmsg = substr($treesmsg, 0, -3);
$peoplemsg = substr($peoplemsg, 0, -3);
$familiesmsg = substr($familiesmsg, 0, -3);
$sourcesmsg = substr($sourcesmsg, 0, -3);
$cemeteriesmsg = substr($cemeteriesmsg, 0, -3);
$placesmsg = substr($placesmsg, 0, -3);
$timelinemsg = substr($timelinemsg, 0, -3);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('administration'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php echo $adminHeaderSection->build(); ?>
    <form action="admin_savelanguage.php" target="_parent" name="language">
      <?php
      if ($link && $chooselang) {
        $query = "SELECT languageID, display, folder FROM $languages_table ORDER BY display";
        $result = tng_query($query);

        if ($result && tng_num_rows($result)) {
          echo " &nbsp;<select name=\"newlanguage\" onChange=\"document.language.submit();\">\n";

          while ($row = tng_fetch_assoc($result)) {
            echo "<option value=\"{$row['languageID']}\"";
            if ($languages_path . $row['folder'] == $mylanguage) {
              echo " selected";
            }
            echo ">{$row['display']}</option>\n";
          }
          echo "</select>\n";
          tng_free_result($result);
        }
      }
      ?>
    </form>
    <div class="row">
      <?php
      if ($allow_edit || $allow_add || $allow_delete) {
        echo adminMenuItem("peopleBrowse.php", uiTextSnippet('people'), $peoplemsg, "svg/person.svg");
        echo adminMenuItem("familiesBrowse.php", uiTextSnippet('families'), $familiesmsg, "svg/people.svg");
        echo adminMenuItem("admin_sources.php", uiTextSnippet('sources'), $sourcesmsg, "svg/archive.svg");
        echo adminMenuItem("repositoriesBrowse.php", uiTextSnippet('repositories'), $sourcesmsg, "svg/building.svg");
      }
      if ($allow_edit || $allow_add || $allow_delete || $allow_media_add || $allow_media_edit || $allow_media_delete) {
        echo adminMenuItem("mediaBrowse.php", uiTextSnippet('media'), $mediamsg, "svg/media-mixed.svg");
        echo adminMenuItem("admin_albums.php", uiTextSnippet('albums'), $mediamsg, "svg/album.svg");
      }
      if ($allow_edit || $allow_add || $allow_delete) {
        echo adminMenuItem("admin_cemeteries.php", uiTextSnippet('cemeteries'), $cemeteriesmsg, "svg/headstone.svg");
        echo adminMenuItem("admin_places.php", uiTextSnippet('places'), $placesmsg, "svg/location.svg");
        echo adminMenuItem("admin_timelineevents.php", uiTextSnippet('tlevents'), $timelinemsg, "img/tlevents_icon.gif");
      }
      if ($allow_edit && $allow_add && $allow_delete && !$assignedtree) {
        echo adminMenuItem("admin_misc.php", uiTextSnippet('misc'), uiTextSnippet('miscitems'), "img/misc_icon.gif");
      }

      if ($allow_edit && $allow_add && $allow_delete && !$assignedbranch) {
        echo adminMenuItem("admin_dataimport.php", uiTextSnippet('datamaint'), uiTextSnippet('importgedcom2'), "img/datamaint_icon.gif");
      }
      if ($allow_edit && $allow_add && $allow_delete && !$assignedtree) {
        echo adminMenuItem("admin_setup.php", uiTextSnippet('setup'), uiTextSnippet('setupitems'), "svg/cog.svg");
        echo adminMenuItem("usersBrowse.php", uiTextSnippet('users'), uiTextSnippet('usersitems'), "svg/users.svg");
        echo adminMenuItem("treesBrowse.php", uiTextSnippet('trees'), $treesmsg, "svg/tree.svg");

        if (!$assignedbranch) {
          echo adminMenuItem("admin_branches.php", uiTextSnippet('branches'), $treesmsg, "svg/flow-branch.svg");
        }
        echo adminMenuItem("admin_eventtypes.php", uiTextSnippet('customeventtypes'), uiTextSnippet('custeventitems'), "svg/graduation-cap.svg");
        echo adminMenuItem("admin_reports.php", uiTextSnippet('reports'), uiTextSnippet('reportsitems'), "svg/print.svg");
        echo adminMenuItem("admin_languages.php", uiTextSnippet('languages'), uiTextSnippet('languages'), "svg/language.svg");
        echo adminMenuItem("admin_utilities.php", uiTextSnippet('backuprestore'), uiTextSnippet('backupitems'), "svg/tools.svg");
      }
      ?>
    </div> <!-- .row -->
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
</body>
</html>