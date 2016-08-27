<?php
require 'begin.php'; // [ts] args expected path, searchstring, folders [optional]
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require $subroot . 'importconfig.php';

initMediaTypes();

$img = "";
if ($path == "gedcom") {
  $tngpath = $gedpath;
} elseif ($mediatypes_assoc[$path]) {
  $tngpath = $mediatypes_assoc[$path];
} else {
  $tngpath = "templates/" . $path . "/img";
  $img = "img/";
}
$pagetotal = 50;

if (!isset($subdir)) {
  $subdir = '';
}
$ImageFileTypes = ["GIF", "JPG", "PNG"];

header("Content-type:text/html; charset=" . $session_charset);

frmFiles();

function frmFiles() {
  global $ImageFileTypes;
  global $subdir;
  global $img;
  global $page;
  global $rootpath;
  global $path;
  global $tngpath;
  global $pagetotal;
  global $searchstring;
  global $allowDelete;
  global $tngconfig;
  global $folders;

  // [ts]  $datefmt = $tngconfig['preferEuro'] == "true" ? "d/m/Y/* h:i:s A*/" : "m/d/Y h:i:s A";
  $datefmt = $tngconfig['preferEuro'] == "true" ? "d/m/Y" : "m/d/Y";
  ?>
  <div id='filepicker'>
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('selectfile'); ?></h4>
      <span><?php echo "<strong>" . uiTextSnippet('folder') . ":</strong> $tngpath" . stripslashes($subdir); ?></span>
      <?php
      $nCurrentPage = $page ? $page : 0;

      $lRecCount = lCountFiles();
      $nPages = intval(( $lRecCount - 0.5 ) / $pagetotal) + 1;
      $lStartRec = $nCurrentPage * $pagetotal;

      frmFilesHdFt($nCurrentPage, $nPages);
      ?>
    </header>
    <div class='modal-body'>
      <table class='table table-sm'>
        <thead>
          <tr>
            <th><?php echo uiTextSnippet('action'); ?></th>
            <th><?php echo uiTextSnippet('filename'); ?></th>
            <th><?php echo uiTextSnippet('date'); ?></th>
            <th><?php echo uiTextSnippet('size'); ?></th>
            <th><?php echo uiTextSnippet('dimensions'); ?></th>
          </tr>
        </thead>
        <?php
        $nImageNr = 0;
        $nImageShowed = 0;

        $savedir = getcwd();
        chdir("$rootpath$tngpath/" . stripslashes($subdir));
        if ($handle = opendir('.')) {
          $fentries = [];
          $dentries = [];
          while ($file = readdir($handle)) {
            if (!$searchstring || strpos(strtoupper($file), strtoupper($searchstring)) === 0) {
              if (is_file($file)) {
                if (!$folders) {
                  array_push($fentries, $file);
                }
              } else {
                array_push($dentries, $file);
              }
            }
          }
          natcasesort($fentries);
          natcasesort($dentries);
          $entries = array_merge($dentries, $fentries);
          foreach ($entries as $file) {
            $filename = $file;
            if (is_file($filename) && $filename != "index.html") {
              $fileparts = pathinfo($filename);
              $file_ext = strtoupper($fileparts["extension"]);
              if ($nImageNr >= $lStartRec && $nImageShowed < $pagetotal) {
                echo "<tr id=\"row_$nImageNr\">\n";
                echo "<td>\n";
                echo "<div class='action-btns'>\n";
                echo "<a href=\"javascript:ReturnFile('$img$subdir" . addslashes($file) . "')\" title='" . uiTextSnippet('select') . "'>\n";
                echo "<img class='icon-sm' src='svg/new-message.svg'>\n";
                echo "</a>";
                if ($allowDelete) {
                  echo "<a href='#' onclick=\"return deleteIt('file','$nImageNr','$tngpath/$subdir" . addslashes($file) . "');\" title='" . uiTextSnippet('delete') . "'>\n";
                  echo "<img class='icon-sm' src='svg/trash.svg'>\n";
                  echo "</a>\n";
                }
                echo "<a href=\"javascript:ShowFile('$tngpath/$subdir" . addslashes($file) . "')\" title=\"" . uiTextSnippet('preview') . "\">\n";
                echo "<img class='icon-sm' src='svg/eye.svg'>\n";
                echo "</a>\n";
                echo "</div>\n";
                echo "</td>\n";
                echo "<td>$file</td>\n";
                echo "<td>" . date($datefmt, filemtime($file)) . "</td>\n";
                echo "<td>" . displaySize(filesize($file)) . "</td>\n";
                  
                if (in_array($file_ext, $ImageFileTypes)) {
                  $size = getimagesize($filename);
                } else {
                  $size = "";
                }
                if ($size) {
                  $imagesize1 = $size[0];
                  $imagesize2 = $size[1];
                  $imagesize = "$imagesize1 x $imagesize2";
                } else {
                  $imagesize = "";
                }
                echo "<td>$imagesize</td>\n";
                echo "</tr>\n";
                $nImageShowed++;
              }
              $nImageNr++;
            }
            elseif (is_dir($filename)) {
              if ($filename != '.' && ($filename != '..' || $subdir != '')) {
                //if( ( ( $subdir != '' ) && ( $filename != '.' ) ) || ( ( $subdir == '' ) && ( $filename != '.' ) && ( $filename != '..' ) ) ) {
                if ($nImageNr >= $lStartRec && $nImageShowed < $pagetotal) {
                  if ($filename != '..') {
                    $newsubdir = $subdir . $filename . '/';
                  } else {
                    $dirbreakdown = explode('/', $subdir);
                    array_pop($dirbreakdown);
                    array_pop($dirbreakdown);
                    $newsubdir = implode('/', $dirbreakdown) . '/';
                    if ($newsubdir == '/') {
                      $newsubdir = '';
                    }
                  }
                  ?>
                  <tr>
                    <td>
                      <?php
                      if ($folders) {
                        echo "<a href=\"javascript:ReturnFile('$img$subdir" . addslashes($file) . "')\" title=\"" . uiTextSnippet('select') . "\">" . uiTextSnippet('select') . "</a> | ";
                      }
                      ?>
                      <span><a href="#" onclick="return moreFilepicker({subdir: '<?php echo addslashes($newsubdir); ?>', path: '<?php echo $path; ?>', folders: '<?php echo $folders; ?>'});"><?php echo uiTextSnippet('open'); ?></a></span>
                    </td>
                    <td>
                      <span><?php echo "<b>" . uiTextSnippet('folder') . ":</b> $filename"; ?></span>
                    </td>
                    <td><?php echo date($datefmt, filemtime($file)); ?></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <?php
                  $nImageShowed++;
                }
                $nImageNr++;
              }
            }
          }
          closedir($handle);
        }
        chdir($savedir);
        ?>
      </table>
    </div>
    <footer class='modal-footer'></footer>
  </div>
<?php
}

function lCountFiles() {
  global $subdir;
  global $rootpath;
  global $tngpath;
  global $searchstring;

  $nFileCount = 0;
  $savedir = getcwd();
  chdir("$rootpath$tngpath/" . stripslashes($subdir));
  if ($handle = opendir('.')) {
    while ($file = readdir($handle)) {
      if (!$searchstring || strpos($file, $searchstring) === 0) {
        $filename = $file;
        if (is_file($filename)) {
          $fileparts = pathinfo($filename);
          $file_ext = strtoupper($fileparts["extension"]);
          $nFileCount++;
        } elseif (is_dir($filename)) {
          if (($subdir != '') || ($filename != '..')) {
            $nFileCount++;
          }
        }
      }
    }
    closedir($handle);
  }
  chdir($savedir);

  return $nFileCount;
}

function frmFilesHdFt($nCurrentPage, $nPages) {
  global $subdir;
  global $path;
  if ($nPages > 1) {
  ?>
    <span style='display: block; float: right;'>
      <a href='#' onclick="return moreFilepicker({subdir: '<?php echo addslashes($subdir); ?>', path: '<?php echo $path; ?>', page: 0});">
        <img src='img/first_button.gif' width='15' height='15'>
      </a>
      <?php if ($nCurrentPage != 0) { ?>
        <a href='#' onclick="return moreFilepicker({subdir: '<?php echo addslashes($subdir); ?>', path: '<?php echo $path; ?>', page: <?php echo ($nCurrentPage - 1); ?>});">
          <img src='img/prev_button.gif' width='15' height='15'>
        </a>
      <?php
      }
      $nCPage = $nCurrentPage + 1;
      echo uiTextSnippet('page') . " $nCPage " . uiTextSnippet('of') . " $nPages ";
      if ($nCurrentPage + 1 != $nPages) {
      ?>
        <a href='#' onclick="return moreFilepicker({subdir: '<?php echo addslashes($subdir); ?>', path: '<?php echo $path; ?>', page: <?php echo ($nCurrentPage + 1); ?>});">
          <img src='img/next_button.gif' width='15' height='15'>
        </a>
      <?php } ?>
      <a href='#' onclick="return moreFilepicker({subdir: '<?php echo addslashes($subdir); ?>', path: '<?php echo $path; ?>', page: <?php echo ($nPages - 1); ?>});">
        <img src='img/last_button.gif' width="15" height='15'>
      </a>
    </span>
  <?php
  }
}

function displaySize($file_size) {
  if ($file_size >= 1073741824) {
    $file_size = round($file_size / 1073741824 * 100) / 100 . "g";
  } elseif ($file_size >= 1048576) {
    $file_size = round($file_size / 1048576 * 100) / 100 . "m";
  } elseif ($file_size >= 1024) {
    $file_size = round($file_size / 1024 * 100) / 100 . "k";
  } else {
    $file_size = $file_size . " bytes";
  }
  return $file_size;
}
