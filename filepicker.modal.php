<?php
/**
 * Name history: admin_filepicker.php
 */

require 'begin.php'; // [ts] args expected path, searchstring, folders [optional]
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require $subroot . 'importconfig.php';

initMediaTypes();

$img = '';
if ($path == 'gedcom') {
  $tngpath = $gedpath;
} elseif ($mediatypes_assoc[$path]) {
  $tngpath = $mediatypes_assoc[$path];
} else {
  $tngpath = 'templates/' . $path . '/img';
  $img = 'img/';
}
$pagetotal = 20;

if (!isset($subdir)) {
  $subdir = '';
}
$ImageFileTypes = ['GIF', 'JPG', 'PNG'];

header('Content-type:text/html; charset=' . $session_charset);

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

  $datefmt = $tngconfig['preferEuro'] == 'true' ? 'd/m/Y' : 'm/d/Y';
  ?>
  <div id='filepicker'>
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('selectfile'); ?></h4>
      
      <span><?php echo "<img class='icon-sm-inline' src='svg/folder.svg'> $tngpath/" . stripslashes($subdir); ?></span>
      <?php
      $nCurrentPage = $page ? $page : 0;

      $lRecCount = lCountFiles();
      $nPages = intval(( $lRecCount - 0.5 ) / $pagetotal) + 1;
      $lStartRec = $nCurrentPage * $pagetotal;
      ?>
    </header>
    <div class='modal-body'>
      <table class='table table-sm table-hover'>
        <thead class='thead-default'>
          <tr>
            <th><?php echo uiTextSnippet('action'); ?></th>
            <th><?php echo uiTextSnippet('name'); ?></th>
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
            if (is_file($filename) && $filename != 'index.html') {
              $fileparts = pathinfo($filename);
              $file_ext = strtoupper($fileparts['extension']);
              if ($nImageNr >= $lStartRec && $nImageShowed < $pagetotal) {
                echo "<tr id=\"row_$nImageNr\">\n";
                echo "<td>\n";
                echo "<div class='action-btns'>\n";
                echo "<a href=\"javascript:ReturnFile('$img$subdir" . addslashes($file) . "')\" title='" . uiTextSnippet('select') . "'>\n";
                echo "<img class='icon-sm' src='svg/new-message.svg'>\n";
                echo '</a>';
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
                echo "<td><img class='icon-sm-inline' src='svg/folder-images.svg'> $file</td>\n";
                echo '<td>' . date($datefmt, filemtime($file)) . "</td>\n";
                echo '<td>' . displaySize(filesize($file)) . "</td>\n";
                  
                if (in_array($file_ext, $ImageFileTypes)) {
                  $size = getimagesize($filename);
                } else {
                  $size = '';
                }
                if ($size) {
                  $imagesize1 = $size[0];
                  $imagesize2 = $size[1];
                  $imagesize = "$imagesize1 x $imagesize2";
                } else {
                  $imagesize = '';
                }
                echo "<td>$imagesize</td>\n";
                echo "</tr>\n";
                $nImageShowed++;
              }
              $nImageNr++;
            } elseif (is_dir($filename)) {
              if ($filename != '.' && ($filename != '..' || $subdir != '')) {
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
                        echo "<a href=\"javascript:ReturnFile('$img$subdir" . addslashes($file) . "')\" title=\"" . uiTextSnippet('select') . '\">' . uiTextSnippet('select') . '</a> | ';
                      }
                      ?>
                    </td>
                    <td>
                      <span><a class='files-silent-folder-link' href="#" onclick="return moreFilepicker({subdir: '<?php echo addslashes($newsubdir); ?>', path: '<?php echo $path; ?>', folders: '<?php echo $folders; ?>'});"><img class='icon-sm-inline' src='svg/folder.svg'> <?php echo $filename; ?></a></span>
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
    <footer class='modal-footer'>
      <?php frmFilesHdFt($nCurrentPage, $nPages); ?>
    </footer>
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
          $file_ext = strtoupper($fileparts['extension']);
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
    $nCPage = $nCurrentPage + 1;
  ?>
    <nav aria-label='Navigation'>
      <ul class='pagination pagination-sm'>
        <?php if ($nCurrentPage != 0) { ?>
          <li class='page-item'>
            <a class='page-link' href='#' aria-label='Previous' onclick="return moreFilepicker({subdir: '<?php echo addslashes($subdir); ?>', path: '<?php echo $path; ?>', page: <?php echo ($nCurrentPage - 1); ?>});">
              <span aria-hidden='true'>&laquo;</span>
              <span class='sr-only'>Previous</span>
            </a>
          </li>
        <?php } ?>
        <li class='page-item active'><a class='page-link' href="#"><?php echo $nCPage; ?></a></li>
        <?php if ($nCurrentPage + 1 != $nPages) { ?>
          <li class="page-item">
            <a class='page-link' href='#' aria-label='Next' onclick="return moreFilepicker({subdir: '<?php echo addslashes($subdir); ?>', path: '<?php echo $path; ?>', page: <?php echo ($nCurrentPage + 1); ?>});">
              <span aria-hidden="true">&raquo;</span>
              <span class="sr-only">Next</span>
            </a>
          </li>
        <?php } ?>
      </ul>
    </nav>
  <?php
  }
}

function displaySize($file_size) {
  if ($file_size >= 1073741824) {
    $file_size = round($file_size / 1073741824 * 100) / 100 . 'g';
  } elseif ($file_size >= 1048576) {
    $file_size = round($file_size / 1048576 * 100) / 100 . 'm';
  } elseif ($file_size >= 1024) {
    $file_size = round($file_size / 1024 * 100) / 100 . 'k';
  } else {
    $file_size = $file_size . ' bytes';
  }
  return $file_size;
}
