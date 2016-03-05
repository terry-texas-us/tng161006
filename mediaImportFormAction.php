<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");
require("adminlog.php");

if (!$allow_media_add || $assignedtree) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

$totalImported = 0;

function importFrom($tngpath, $orgpath, $needsubdirs) {
  global $rootpath;
  global $media_table;
  global $mediatypeID;
  global $tree;
  global $time_offset;
  global $thumbprefix;
  global $thumbsuffix;
  global $totalImported;
  $subdirs = array();

  if ($orgpath) {
    $path = $tngpath . "/" . $orgpath;
    $orgpath .= "/";
  } else {
    $path = $tngpath;
  }
  @chdir("$rootpath$path") or die("Unable to open $rootpath$path. Please check your Root Path (General Settings).");
  if ($handle = @opendir('.')) {
    while ($filename = readdir($handle)) {
      if (is_file($filename)) {
        if (($thumbprefix && strpos($filename, $thumbprefix) !== 0) || ($thumbsuffix && substr($filename, -strlen($thumbsuffix)) != $thumbsuffix)) {
          //$cleanfile = $session_charset == "UTF-8" ? utf8_encode($filename) : $filename;
          echo "Inserting $path/$filename ... ";
          //insert ignore into database
          $fileparts = pathinfo($filename);
          $form = strtoupper($fileparts["extension"]);
          $newdate = date("Y-m-d H:i:s", time() + (3600 * $time_offset));
          $query = "INSERT IGNORE INTO $media_table (mediatypeID,mediakey,gedcom,path,thumbpath,description,notes,width,height,datetaken,placetaken,owner,changedate,form,alwayson,map,abspath,status,cemeteryID,showmap,linktocem,latitude,longitude,zoom,bodytext,usenl,newwindow,usecollfolder) VALUES (\"$mediatypeID\",\"$path/$filename\",\"$tree\",\"$orgpath$filename\",\"\",\"$orgpath$filename\",\"\",\"\",\"\",\"\",\"\",\"\",\"$newdate\",\"$form\",\"0\",\"\",\"0\",\"\",\"\",\"0\",\"0\",\"\",\"\",\"0\",\"\",\"0\",\"0\",\"1\")";
          tng_query($query);
          $success = tng_affected_rows();
          //$success = 1;
          if ($success) {
            echo "success<br>\n";
            $totalImported++;
          } else {
            echo "<strong>failed (duplicate)</strong><br>\n";
          }
        }
      } elseif ($needsubdirs && is_dir($filename) && $filename != '..' && $filename != '.') {
        array_push($subdirs, $filename);
      }
    }
    closedir($handle);
  }

  return $subdirs;
}
adminwritelog(uiTextSnippet('media') . " &gt;&gt; " . uiTextSnippet('import') . " ($mediatypeID): $tree");

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('mediaimport'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="media-import">
  <section class='container'>
    <?php
    $tngpath = $mediatypes_assoc[$mediatypeID];

    echo $adminHeaderSection->build('media-import', $message);
    $navList = new navList('');
    $navList->appendItem([true, "mediaBrowse.php", uiTextSnippet('search'), "findmedia"]);
    $navList->appendItem([$allow_media_add, "admin_newmedia.php", uiTextSnippet('addnew'), "addmedia"]);
    $navList->appendItem([$allow_media_edit, "admin_ordermediaform.php", uiTextSnippet('text_sort'), "sortmedia"]);
    $navList->appendItem([$allow_media_edit && !$assignedtree, "mediaThumbnails.php", uiTextSnippet('thumbnails'), "thumbs"]);
    $navList->appendItem([$allow_media_add && !$assignedtree, "mediaImport.php", uiTextSnippet('import'), "import"]);
    $navList->appendItem([$allow_media_add && !$assignedtree, "mediaUpload.php", uiTextSnippet('upload'), "upload"]);
    echo $navList->build("import");
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <?php
          $subdirs = importFrom($tngpath, '', 1);
          foreach ($subdirs as $subdir) {
            chdir("$rootpath$tngpath/$subdir");
            importFrom($tngpath, $subdir, 0);
          }
          if ($totalImported) {
            $query = "UPDATE $mediatypes_table SET disabled=\"0\" where mediatypeID=\"$mediatypeID\"";
            $result = @tng_query($query);
          }
          ?>
        </td>
      </tr>

    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
</body>
</html>