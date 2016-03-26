<?php
require 'begin.php';
require 'genlib.php';
require 'getlang.php';

require 'functions.php';

if ($medialinkID) {
  $query = "SELECT mediatypeID, personID, linktype, $medialinks_table.gedcom as gedcom, eventID, ordernum FROM ($media_table, $medialinks_table) WHERE medialinkID = \"$medialinkID\" AND $media_table.mediaID = $medialinks_table.mediaID";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $personID = $row['personID'];
  if (!$requirelogin || !$treerestrict || !$assignedtree) {
    $tree = $row['gedcom'];
  }
  $ordernum = $row['ordernum'];
  $mediatypeID = $row['mediatypeID'];
  $linktype = $row['linktype'];
  if ($linktype == "P") {
    $linktype = 'I';
  }
  $eventID = $row['eventID'];
} else {
  $query = "SELECT mediatypeID, gedcom FROM $media_table WHERE mediaID = \"$mediaID\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $mediatypeID = $row['mediatypeID'];
  if (!$requirelogin || !$treerestrict || !$assignedtree) {
    $tree = $row['gedcom'];
  }
}

if ($requirelogin && $treerestrict && $assignedtree && $row['gedcom'] && $row['gedcom'] != $assignedtree) {
  header("location: mediaShow.php?");
  exit;
}
if (!tng_num_rows($result)) {
  tng_free_result($result);
  header("Location: thispagedoesnotexist.html");
}
require 'checklogin.php';
require 'showmedialib.php';

$info = getMediaInfo($mediatypeID, $mediaID, $personID, $albumID, $albumlinkID, $cemeteryID, $eventID);
$imgrow = $info['imgrow'];

$flags['styles'] = "<link href=\"css/img_viewer.css\" rel=\"stylesheet\" type=\"text/css\">\n";

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle($imgrow['description']);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<?php  
echo "<body id='public'>\n";
  echo $publicHeaderSection->build();

  $usefolder = $imgrow['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
  if ($imgrow['abspath'] || substr($imgrow['path'], 0, 4) == "http" || substr($imgrow['path'], 0, 1) == "/") {
    $mediasrc = $imgrow['path'];
  } else {
    $mediasrc = "$usefolder/" . str_replace("%2F", "/", rawurlencode($imgrow['path']));
  }
  // get image info
  if (substr($imgrow['path'], 0, 4) == "http") {
    list($width, $height) = getimagesize($imgrow['path']);
  } else {
    list($width, $height) = getimagesize("$rootpath$usefolder/" . $imgrow['path']);
  }
  $maxw = $tngconfig['imgmaxw'];
  $maxh = $tngconfig['imgmaxh'];
  $orgwidth = $width;
  $orgheight = $height;

  if ($maxw && ($width > $maxw)) {
    $width = $maxw;
    $height = floor($width * $orgheight / $orgwidth);
  }
  if ($maxh && ($height > $maxh)) {
    $height = $maxh;
    $width = floor($height * $orgwidth / $orgheight);
  }
  $float = strpos($_SERVER['HTTP_USER_AGENT'], "MSIE 7") > 0 ? " style=\"float:left\"" : "";
  ?>
  <div id="imgviewer" <?php echo $float; ?>>
    <map name="imgMapViewer" id="imgMapViewer"><?php echo $imgrow['map']; ?></map>
    <?php
    // clean up the description
    $imgrow['description'] = str_replace("\r\n", "<br>", $imgrow['description']);
    $imgrow['description'] = str_replace("\n", "<br>", $imgrow['description']);

    // if running in standalone mode we need to display the title and notes info
    if (isset($_GET['sa'])) {
      $sa = 1;
      if (!empty($imgrow['description'])) {
        echo "<h4 id='img_desc'><strong>{$imgrow['description']}</strong></h4>";
      }
      if (!empty($imgrow['notes'])) {
        echo "<p id=\"img_notes\">{$imgrow['notes']}</p>";
      }
    } else {
      $sa = 0;
    }
    ?>
  </div>
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script src='js/img_viewer.js'></script>
  <script>
    var mediaSrc = '<?php echo $mediasrc ?>',
      width = '<?php echo $width ?>',
      height = '<?php echo $height ?>';
      sa = <?php echo isset($sa) ? $sa : 0 ?>,
      mediaID = '<?php echo $mediaID ?>',
      mediaLinkID = '<?php echo $medialinkID ?>';

    if (parent.document.getElementById(window.name)) {
      viewer = imageViewer('imgviewer', mediaSrc, width, height, sa, mediaID, mediaLinkID, "<?php echo urlencode($imgrow['description']) ?>");
    }
  </script>
</body>
</html>