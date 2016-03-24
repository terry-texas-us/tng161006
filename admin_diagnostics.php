<?php
//This page written and contributed by Bert Deelman. Thanks, Bert!
require 'begin.php';
include($subroot . "logconfig.php");
include($subroot . "importconfig.php");
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
include("version.php");

$file_uploads = (bool)ini_get("file_uploads");
$safe_mode = (bool)ini_get("safe_mode");

error_reporting(E_ERROR | E_PARSE); //  Disable error reporting for anything but critical errors

$red = "<img src=\"img/tng_close.gif\" width=\"18\" height=\"18\">";
$orange = "<img src=\"img/orange.gif\" width=\"18\" height=\"18\">";
$green = "<img src=\"img/tng_check.gif\" width=\"18\" height=\"18\">";

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('diagnostics'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="setup-diagnostics">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('setup-diagnostics', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_setup.php", uiTextSnippet('configuration'), "configuration"]);
    $navList->appendItem([true, "admin_diagnostics.php", uiTextSnippet('diagnostics'), "diagnostics"]);
    $navList->appendItem([true, "admin_setup.php?sub=tablecreation", uiTextSnippet('tablecreation'), "tablecreation"]);
    echo $navList->build("diagnostics");
    ?>

    <table class='table table-sm'>
      <tr>
        <td colspan='2'>
          <em><?php echo uiTextSnippet('sysinfo'); ?></em>
        </td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('phpver'); ?>
          :<br><em><?php echo uiTextSnippet('phpreq'); ?></em></td>
        <td>
          <?php
          if (phpversion() >= '5.0') {
            echo "<p>$green&nbsp;";
          } else {
            echo '&nbsp;<img src="tng_close.gif" width="12" height="12">&nbsp;';
          }
          echo 'PHP ' . phpversion();
          ?><br>
          <a href="admin_phpinfo.php"><?php echo uiTextSnippet('phpinf'); ?></a>
        </td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('gdlib'); ?>
          :<br><em><?php echo uiTextSnippet('gdreq'); ?></em></td>
        <td>
          <?php
          if (extension_loaded('gd')) {
            if (ImageTypes() & IMG_GIF) {
              echo "<p>$green&nbsp;" . uiTextSnippet('available') . "</p>";
            } else {
              echo "<p>$orange&nbsp;" . uiTextSnippet('availnogif') . "</p>";
            }
          } else {
            echo "<p>$red&nbsp;" . uiTextSnippet('notinst') . "</p>";
          }
          ?>
        </td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('safemode'); ?>:</td>
        <td>
          <?php
          if (!$safe_mode) {
            echo "<p>$green&nbsp;" . uiTextSnippet('off') . "</p>";
          } else {
            echo "<p>$orange&nbsp;" . uiTextSnippet('on') . "</p>";
          }
          ?>
        </td>
      </tr>
      <tr>
        <td>
          <?php echo uiTextSnippet('fileuploads'); ?>:<br><em><?php echo uiTextSnippet('fureq'); ?></em>
        </td>
        <td>
          <?php
          if ($file_uploads) {
            echo "<p>$green&nbsp;" . uiTextSnippet('perm') . "</p>";
          } else {
            echo "<p>$red&nbsp;" . uiTextSnippet('notperm') . "</p>";
          }
          ?>
        </td>
      </tr>
      <tr>
        <td>
          <?php echo uiTextSnippet('sqlver'); ?>:<br><em><?php echo uiTextSnippet('sqlreq'); ?></em>
        </td>
        <td>
          <?php
          $dbci = tng_get_client_info();
          if ($dbci >= '3.23') {
            echo "<p>$green&nbsp;";
          } else {
            if ($dbci >= '3.20.32') {
              echo "<p>$orange&nbsp;";
            } else {
              echo "<p>$red&nbsp;";
            }
          }
          echo 'MySQL ' . tng_get_client_info() . " " . uiTextSnippet('client') . "</p>";
          $dbsi = tng_get_server_info();
          if ($dbsi >= '3.23') {
            echo "<p>$green&nbsp;";
          } else {
            if ($dbsi >= '3.20.32') {
              echo "<p>$orange&nbsp;";
            } else {
              echo "<p>$red&nbsp;";
            }
          }
          echo 'MySQL ' . tng_get_server_info() . " " . uiTextSnippet('server') . "</p>";
          ?>
        </td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('wsrvr'); ?>:</td>
        <td>
          <?php
          echo "<p>$green&nbsp;";
          echo $_SERVER['SERVER_SOFTWARE'] . "</p>";
          ?>
        </td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('fperms'); ?>
          :<br><em><?php echo uiTextSnippet('fpreq'); ?></em></td>
        <td>
          <?php
          $myuserid = getmyuid();
          if (phpversion() >= '4.1.0') {
            $mygroupid = getmygid();
          } else {
            $mygroupid = getmyuid();
          }

          if (function_exists('posix_getuid')) {
            $posixmyuserid = posix_getuid();
            $posixuserinfo = posix_getpwuid($posixmyuserid);
            $posixname = $posixuserinfo['name'];
            $posixmygroupid = $posixuserinfo['gid'];
            $posixgroupinfo = posix_getgrgid($posixmygroupid);
            $posixgroup = $posixgroupinfo['name'];
          } else {
            $posixmyuserid = $myuserid;
            $posixname = get_current_user();
            $posixmygroupid = $mygroupid;
            $posixgroup = '';
          }

          if ($myuserid != $posixmyuserid) {
            $myuserid = $posixmyuserid;
          }
          if ($mygroupid != $posixmygroupid) {
            $mygroupid = $posixmygroupid;
          }

          $text = '';
          $ftext = '';
          // check files
          if (!(fileReadWrite($myuserid, $mygroupid, $subroot . 'config.php'))) {
            $text = "<p>$red&nbsp;" . uiTextSnippet('rofile') . " config.php</p>";
          }
          $uselog = $logname;
          if (!(fileReadWrite($myuserid, $mygroupid, $uselog))) {
            $ftext = "<p>$red&nbsp;" . uiTextSnippet('rofile') . " " . uiTextSnippet('publog') . " ($logname)</p>";
          }
          if (!(fileReadWrite($myuserid, $mygroupid, $adminlogfile))) {
            $ftext .= "<p>$red&nbsp;" . uiTextSnippet('rofile') . " " . uiTextSnippet('admlog') . " ($adminlogfile)</p>";
          }
          if (!(fileReadWrite($myuserid, $mygroupid, $subroot . 'importconfig.php'))) {
            $ftext .= "<p>$red&nbsp;" . uiTextSnippet('rofile') . " importconfig.php</p>";
          }
          if (!(fileReadWrite($myuserid, $mygroupid, $subroot . 'logconfig.php'))) {
            $ftext .= "<p>$red&nbsp;" . uiTextSnippet('rofile') . " logconfig.php</p>";
          }
          if (!(fileReadWrite($myuserid, $mygroupid, $subroot . 'pedconfig.php'))) {
            $ftext .= "<p>$red&nbsp;" . uiTextSnippet('rofile') . " pedconfig.php</p>";
          }
          if (!(fileReadWrite($myuserid, $mygroupid, $subroot . 'mapconfig.php'))) {
            $ftext .= "<p>$red&nbsp;" . uiTextSnippet('rofile') . " mapconfig.php</p>";
          }
          if (!(fileReadWrite($myuserid, $mygroupid, $subroot . 'templateconfig.php'))) {
            $ftext .= "<p>$red&nbsp;" . uiTextSnippet('rofile') . " templateconfig.php</p>";
          }

          // check folders
          if (!(dirExists($photopath))) {
            $ftext .= "<p>$red&nbsp;" . uiTextSnippet('folderdne') . " $photopath</p>";
          } else {
            if (!(dirReadWrite($myuserid, $mygroupid, $photopath))) {
              $ftext .= "<p>$orange&nbsp;" . uiTextSnippet('rofolder') . " $photopath ($rootpath$photopath)</p>";
            }
          }
          if (!(dirExists($headstonepath))) {
            $ftext .= "<p>$red&nbsp;" . uiTextSnippet('folderdne') . " $headstonepath</p>";
          } else {
            if (!(dirReadWrite($myuserid, $mygroupid, $headstonepath))) {
              $ftext .= "<p>$orange&nbsp;" . uiTextSnippet('rofolder') . " $headstonepath ($rootpath$headstonepath)</p>";
            }
          }
          if (!(dirExists($historypath))) {
            $ftext .= "<p>$orange&nbsp;" . uiTextSnippet('folderdne') . " $historypath ($rootpath$historypath)</p>";
          } else {
            if (!(dirReadWrite($myuserid, $mygroupid, $historypath))) {
              $ftext .= "<p>$orange&nbsp;" . uiTextSnippet('rofolder') . " $historypath ($rootpath$historypath)</p>";
            }
          }
          if (!(dirExists($backuppath))) {
            $ftext .= "<p>$orange&nbsp;" . uiTextSnippet('folderdne') . " $backuppath ($rootpath$backuppath)</p>";
          } else {
            if (!(dirReadWrite($myuserid, $mygroupid, $backuppath))) {
              $ftext .= "<p>$orange&nbsp;" . uiTextSnippet('rofolder') . " $backuppath ($rootpath$backuppath)</p>";
            }
          }
          if (!(dirExists($gedpath))) {
            $ftext .= "<p>$orange&nbsp;" . uiTextSnippet('folderdne') . " $gedpath ($rootpath$gedpath)</p>";
          } else {
            if (!(dirReadWrite($myuserid, $mygroupid, $gedpath))) {
              $ftext .= "<p>$orange&nbsp;" . uiTextSnippet('rofolder') . " $gedpath ($rootpath$gedpath)</p>";
            }
          }
          if ($ftext == '') {
            $ftext = "<p>$green&nbsp;" . uiTextSnippet('keyrw') . "</p>";
          }
          echo $ftext;

          if ($text == '') {
            echo "<p>$green&nbsp;" . uiTextSnippet('cfgrw') . "</p>";
          }
          echo $text;
          ?>
        </td>
      </tr>
      <tr>
        <td colspan='2'>
          <p><img src="img/tng_check.gif" width="18" height="18"
                  align="left">&nbsp;= <?php echo uiTextSnippet('acceptable'); ?></p>
          <p><img src="img/orange.gif" width="18" height="18"
                  align="left">&nbsp;= <?php echo uiTextSnippet('restricted'); ?></p>
          <p><img src="img/tng_close.gif" width="18" height="18"
                  align="left">&nbsp;= <?php echo uiTextSnippet('needchngs'); ?></p>
          <br><?php echo uiTextSnippet('yourbrowser') . $_SERVER['HTTP_USER_AGENT']; ?></td>
      </tr>
    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
</body>
</html>

<?php 
function fileReadWrite($myuserid, $mygroupid, $fileref) {
  $rval = false;

  $userid = fileowner($fileref);
  $groupid = filegroup($fileref);
  $perms = readPerms(fileperms($fileref));

  if ($myuserid == $userid) {
    if (substr($perms, 2, 1) == 'w') {
      $rval = true;
    } elseif ($mygroupid == $groupid) {
      if (substr($perms, 5, 1) == 'w') {
        $rval = true;
      } elseif (substr($perms, 8, 1) == 'w') {
        $rval = true;
      }
    }
  } elseif ($mygroupid == $groupid) {
    if (substr($perms, 5, 1) == 'w') {
      $rval = true;
    }
  } elseif (substr($perms, 8, 1) == 'w') {
    $rval = true;
  }

  return $rval;
}

function dirExists($dirref) {
  $rval = is_dir($dirref) ? true : false;
  return $rval;
}

function dirReadWrite($myuserid, $mygroupid, $dirref) {
  $rval = false;

  $userid = fileowner($dirref);
  $groupid = filegroup($dirref);
  $perms = readPerms(fileperms($dirref));

  if ($myuserid == $userid) {
    if (substr($perms, 2, 1) == 'w') {
      $rval = true;
    } elseif ($mygroupid == $groupid) {
      if (substr($perms, 5, 1) == 'w') {
        $rval = true;
      } elseif (substr($perms, 8, 1) == 'w') {
        $rval = true;
      }
    }
  } elseif ($mygroupid == $groupid) {
    if (substr($perms, 5, 1) == 'w') {
      $rval = true;
    }
  } elseif (substr($perms, 8, 1) == 'w') {
    $rval = true;
  }

  return $rval;
}

function readPerms($in_Perms) {
  $sP;

  if ($in_Perms & 0x1000) {
    $sP = 'p';
  } // FIFO pipe
  elseif ($in_Perms & 0x2000) {
    $sP = 'c';
  } // Character special
  elseif ($in_Perms & 0x4000) {
    $sP = 'd';
  } // Directory
  elseif ($in_Perms & 0x6000) {
    $sP = 'b';
  } // Block special
  elseif ($in_Perms & 0x8000) {
    $sP = '-';
  } // Regular
  elseif ($in_Perms & 0xA000) {
    $sP = 'l';
  } // Symbolic Link
  elseif ($in_Perms & 0xC000) {
    $sP = 's';
  } // Socket
  else {
    $sP = 'u';
  } // UNKNOWN
  // owner
  $sP .= (($in_Perms & 0x0100) ? 'r' : '-') . (($in_Perms & 0x0080) ? 'w' : '-') . (($in_Perms & 0x0040) ? (($in_Perms & 0x0800) ? 's' : 'x') : (($in_Perms & 0x0800) ? 'S' : '-'));
  // group
  $sP .= (($in_Perms & 0x0020) ? 'r' : '-') . (($in_Perms & 0x0010) ? 'w' : '-') . (($in_Perms & 0x0008) ? (($in_Perms & 0x0400) ? 's' : 'x') : (($in_Perms & 0x0400) ? 'S' : '-'));
  // world
  $sP .= (($in_Perms & 0x0004) ? 'r' : '-') . (($in_Perms & 0x0002) ? 'w' : '-') . (($in_Perms & 0x0001) ? (($in_Perms & 0x0200) ? 't' : 'x') : (($in_Perms & 0x0200) ? 'T' : '-'));
  return $sP;
}