<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if (!$allow_edit || ($assignedtree && $assignedtree != $tree)) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
require("adminlog.php");

$shorttitle = addslashes($shorttitle);
$title = addslashes($title);
$author = addslashes($author);
$callnum = addslashes($callnum);
$publisher = addslashes($publisher);
$actualtext = addslashes($actualtext);

$newdate = date("Y-m-d H:i:s", time() + (3600 * $time_offset));

$query = "UPDATE $sources_table SET shorttitle=\"$shorttitle\",title=\"$title\",author=\"$author\",callnum=\"$callnum\",publisher=\"$publisher\",repoID=\"$repoID\",actualtext=\"$actualtext\",changedate=\"$newdate\",changedby=\"$currentuser\" WHERE sourceID=\"$sourceID\" AND gedcom = \"$tree\"";
$result = tng_query($query);

adminwritelog("<a href=\"admin_editsource.php?sourceID=$sourceID&amp;tree=$tree\">" . uiTextSnippet('modifysource') . ": $tree/$sourceID</a>");

if ($newscreen == "return") {
  header("Location: admin_editsource.php?sourceID=$sourceID&tree=$tree");
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
    header("Location: admin_sources.php?message=" . urlencode($message));
  }
}
