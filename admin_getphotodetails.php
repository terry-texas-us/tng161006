<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

$query = "SELECT thumbpath,  usecollfolder, description, notes, mediatypeID FROM media WHERE mediaID = \"$mediaID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

header('Content-type:text/html; charset=' . $session_charset);
?>

<table style="padding-top:6px">
  <tr>
    <td id="mwthumb" style="width:<?php echo($thumbmaxw + 6); ?>px;height:<?php echo($thumbmaxh + 6); ?>px;text-align: center;">
      <?php
      initMediaTypes();
      $lmediatypeID = $row['mediatypeID'];
      $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$lmediatypeID] : $mediapath;

      if ($row['thumbpath'] && file_exists("$rootpath$usefolder/" . $row['thumbpath'])) {
        $photoinfo = getimagesize("$rootpath$usefolder/" . $row['thumbpath']);
        if ($photoinfo[1] < 50) {
          $photohtouse = $photoinfo[1];
          $photowtouse = $photoinfo[0];
        } else {
          $photohtouse = 50;
          $photowtouse = intval(50 * $photoinfo[0] / $photoinfo[1]);
        }
        echo "<img src=\"$usefolder/" . str_replace('%2F', '/', rawurlencode($row['thumbpath'])) . "\" width=\"$photowtouse\" height=\"$photohtouse\" id=\"img_$ID\" alt=\"{$row['mtitle']}\">";
      } else {
        echo '&nbsp;';
      }
      $row['notes'] = xmlcharacters($row['notes']);
      $truncated = substr($row['notes'], 0, 90);
      $truncated = strlen($row['notes']) > 90 ? substr($truncated, 0, strrpos($truncated, ' ')) . '&hellip;' : $row['notes'];
      ?>
    </td>
    <td id="mwdetails"><?php echo '<u>' . xmlcharacters($row['description']) . '</u><br>' . $truncated; ?>
      &nbsp;</td>
  </tr>
</table>