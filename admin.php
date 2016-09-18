<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 2;
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

$genmsg = $mediamsg = '';
if ($allowAdd) {
  $genmsg .= uiTextSnippet('add') . ' | ';
  $mediamsg = $genmsg;
} elseif ($allowMediaAdd) {
  $mediamsg = uiTextSnippet('add') . ' | ';
}
$genmsg .= uiTextSnippet('find2') . ' | ';
$mediamsg . uiTextSnippet('find2') . ' | ';
if ($allowEdit) {
  $genmsg .= uiTextSnippet('edit') . ' | ';
  $mediamsg .= uiTextSnippet('edit') . ' | ';
} elseif ($allowMediaEdit) {
  $mediamsg .= uiTextSnippet('edit') . ' | ';
}
if ($allowDelete) {
  $genmsg .= uiTextSnippet('delete') . ' | ';
  $mediamsg .= uiTextSnippet('delete') . ' | ';
} elseif ($allowMediaDelete) {
  $mediamsg .= uiTextSnippet('delete') . ' | ';
}
$sourcesmsg = $peoplemsg = $familiesmsg = $treesmsg = $cemeteriesmsg = $timelinemsg = $placesmsg = $genmsg;
$mediamsg .= uiTextSnippet('text_sort') . ' | ';
if ($allowEdit) {
  $peoplemsg .= uiTextSnippet('reviewsh') . ' | ';
  $familiesmsg .= uiTextSnippet('reviewsh') . ' | ';
}
if ($allowEdit && $allowDelete) {
  $peoplemsg .= uiTextSnippet('merge') . ' | ';
  $placesmsg .= uiTextSnippet('merge') . ' | ';
  $sourcesmsg .= uiTextSnippet('merge') . ' | ';
}
$treesmsg = substr($treesmsg, 0, -3);
$peoplemsg = substr($peoplemsg, 0, -3);
$familiesmsg = substr($familiesmsg, 0, -3);
$sourcesmsg = substr($sourcesmsg, 0, -3);
$cemeteriesmsg = substr($cemeteriesmsg, 0, -3);
$placesmsg = substr($placesmsg, 0, -3);
$timelinemsg = substr($timelinemsg, 0, -3);

header('Content-type: text/html; charset=' . $session_charset);
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
        $query = 'SELECT languageID, display, folder FROM languages ORDER BY display';
        $result = tng_query($query);

        if ($result && tng_num_rows($result)) {
          echo " &nbsp;<select name=\"newlanguage\" onChange=\"document.language.submit();\">\n";

          while ($row = tng_fetch_assoc($result)) {
            echo "<option value=\"{$row['languageID']}\"";
            if ($languagesPath . $row['folder'] == $mylanguage) {
              echo ' selected';
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
      if ($allowEdit || $allowAdd || $allowDelete) {
        echo adminMenuItem('peopleBrowse.php', uiTextSnippet('people'), $peoplemsg, 'svg/person.svg');
        echo adminMenuItem('familiesBrowse.php', uiTextSnippet('families'), $familiesmsg, 'svg/people.svg');
        echo adminMenuItem('sourcesBrowse.php', uiTextSnippet('sources'), $sourcesmsg, 'svg/archive.svg');
        echo adminMenuItem('repositoriesBrowse.php', uiTextSnippet('repositories'), $sourcesmsg, 'svg/building.svg');
      }
      if ($allowEdit || $allowAdd || $allowDelete || $allowMediaAdd || $allowMediaEdit || $allowMediaDelete) {
        echo adminMenuItem('mediaBrowse.php', uiTextSnippet('media'), $mediamsg, 'svg/media-mixed.svg');
        echo adminMenuItem('albumsBrowse.php', uiTextSnippet('albums'), $mediamsg, 'svg/album.svg');
      }
      if ($allowEdit || $allowAdd || $allowDelete) {
        echo adminMenuItem('cemeteriesBrowse.php', uiTextSnippet('cemeteries'), $cemeteriesmsg, 'svg/headstone.svg');
        echo adminMenuItem('placesBrowse.php', uiTextSnippet('places'), $placesmsg, 'svg/location.svg');
        echo adminMenuItem('timelineeventsBrowse.php', uiTextSnippet('tlevents'), $timelinemsg, 'img/tlevents_icon.gif');
      }
      if ($allowEdit && $allowAdd && $allowDelete) {
        echo adminMenuItem('admin_misc.php', uiTextSnippet('misc'), uiTextSnippet('miscitems'), 'img/misc_icon.gif');
      }

      if ($allowEdit && $allowAdd && $allowDelete && !$assignedbranch) {
        echo adminMenuItem('dataImportGedcom.php', uiTextSnippet('datamaint'), uiTextSnippet('importgedcom2'), 'img/datamaint_icon.gif');
      }
      if ($allowEdit && $allowAdd && $allowDelete) {
        echo adminMenuItem('admin_setup.php', uiTextSnippet('setup'), uiTextSnippet('setupitems'), 'svg/cog.svg');
        echo adminMenuItem('usersBrowse.php', uiTextSnippet('users'), uiTextSnippet('usersitems'), 'svg/users.svg');
        echo adminMenuItem('treesBrowse.php', uiTextSnippet('trees'), $treesmsg, 'svg/tree.svg');

        if (!$assignedbranch) {
          echo adminMenuItem('branchesBrowse.php', uiTextSnippet('branches'), $treesmsg, 'svg/flow-branch.svg');
        }
        echo adminMenuItem('eventtypesBrowse.php', uiTextSnippet('customeventtypes'), uiTextSnippet('custeventitems'), 'svg/graduation-cap.svg');
        echo adminMenuItem('reportsBrowse.php', uiTextSnippet('reports'), uiTextSnippet('reportsitems'), 'svg/print.svg');
        echo adminMenuItem('languagesBrowse.php', uiTextSnippet('languages'), uiTextSnippet('languages'), 'svg/language.svg');
        echo adminMenuItem('admin_utilities.php', uiTextSnippet('backuprestore'), uiTextSnippet('backupitems'), 'svg/tools.svg');
      }
      ?>
    </div> <!-- .row -->
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
</body>
</html>