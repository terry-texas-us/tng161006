<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowEdit || ($assignedtree && $assignedtree != $tree)) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
require 'adminlog.php';

$shorttitle = addslashes($shorttitle);
$title = addslashes($title);
$author = addslashes($author);
$callnum = addslashes($callnum);
$publisher = addslashes($publisher);
$actualtext = addslashes($actualtext);

$newdate = date("Y-m-d H:i:s", time() + (3600 * $timeOffset));

$query = "UPDATE $sources_table SET shorttitle=\"$shorttitle\",title=\"$title\",author=\"$author\",callnum=\"$callnum\",publisher=\"$publisher\",repoID=\"$repoID\",actualtext=\"$actualtext\",changedate=\"$newdate\",changedby=\"$currentuser\" WHERE sourceID=\"$sourceID\" AND gedcom = \"$tree\"";
$result = tng_query($query);

adminwritelog("<a href=\"sourcesEdit.php?sourceID=$sourceID&amp;tree=$tree\">" . uiTextSnippet('modifysource') . ": $tree/$sourceID</a>");

if ($newscreen == "return") {
  header("Location: sourcesEdit.php?sourceID=$sourceID&tree=$tree");
} else {
  if ($newscreen == "close") {
  ?>
    <!DOCTYPE html>
    <html>
    <body>
      <script>
        top.close();
      </script>
    </body>
    </html>
  <?php
  } else {
    $message = uiTextSnippet('changestosource') . " $sourceID " . uiTextSnippet('succsaved') . '.';
    header("Location: sourcesBrowse.php?message=" . urlencode($message));
  }
}
