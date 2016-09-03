<?php
require 'begin.php';
require 'genlib.php';
require 'getlang.php';

require 'functions.php';
require 'personlib.php';
require 'checklogin.php';
require 'showmedialib.php';

initMediaTypes();

require 'showmediaxmllib.php';

if ($page < $totalpages) {
  $nextpage = $page + 1;
} else {
  $nextpage = 1;
}
$nextmediaID = get_item_id($result, $nextpage - 1, "mediaID");
$nextmedialinkID = get_item_id($result, $nextpage - 1, "medialinkID");
$nextalbumlinkID = get_item_id($result, $nextpage - 1, "albumlinkID");
header("Content-type:text/html; charset=" . $session_charset);
echo "mediaID=$nextmediaID&medialinkID=$nextmedialinkID&albumlinkID=$nextalbumlinkID";

tng_free_result($result);

echo "<p class=\"topmargin\">$pagenav</p>";
echo "<h4>" . truncateIt($description, 100) . "</h4>\n";

if ($noneliving || $imgrow['alwayson']) {
  showMediaSource($imgrow, true);
} else {
  ?>
  <div class='livingbox'><?php echo uiTextSnippet('living'); ?></div>
  <?php
}
?>