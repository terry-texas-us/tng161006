<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

if (!$allowEdit) {
  exit;
}

require 'adminlog.php';

header('Content-type:text/html; charset=' . $session_charset);
if ($action == 'convert') {
  $query = "UPDATE places SET gedcom = \"$placetree\" WHERE gedcom = \"\"";
  $result = tng_query($query) or die('Cannot complete this operation because place records were never merged.');
  $logmsg = "All places assigned to tree: $placetree";
} else {
  if ($action == 'merge') {
    //stop using trees, blank out the gedcom field for every place
    $query = "SELECT * FROM places WHERE gedcom != \"\"";
    $result = tng_query($query);

    while ($row = tng_fetch_assoc($result)) {
      $query = "SELECT * FROM places WHERE place = \"" . addslashes($row['place']) . '" AND gedcom = ""';
      $result2 = tng_query($query);

      if (tng_num_rows($result2)) {
        $row2 = tng_fetch_assoc($result2);
        //merge, then delete one and update the other
        if ($row['longitude'] || $row['latitude'] || $row['zoom'] || $row['placelevel'] || $row['temple'] || $row['notes']) {
          $newlongitude = $row['longitude'] && !$row2['longitude'] ? $row['longitude'] : $row2['longitude'];
          $newlatitude = $row['latitude'] && !$row2['latitude'] ? $row['latitude'] : $row2['latitude'];
          $newzoom = $row['zoom'] && !$row2['zoom'] ? $row['zoom'] : $row2['zoom'];
          $newplacelevel = $row['placelevel'] && !$row2['placelevel'] ? $row['placelevel'] : $row2['placelevel'];
          $newtemple = $row['temple'] && !$row2['temple'] ? $row['temple'] : $row2['temple'];
          $newnotes = $row['notes'] && !$row2['notes'] ? $row['notes'] : $row2['notes'];

          $query = "UPDATE places SET longitude = \"$newlongitude\", latitude = \"$newlatitude\", zoom = \"$newzoom\", placelevel = \"$newplacelevel\", temple = \"$newtemple\", notes = \"" . addslashes($newnotes) . '" WHERE ID = "' . addslashes($row2['ID']) . '"';
          $result3 = tng_query($query);
        }
        $query = "DELETE FROM places WHERE ID = \"" . addslashes($row['ID']) . '"';
        $result3 = tng_query($query);
      } else {
        $query = "UPDATE places SET gedcom = \"\" WHERE ID = \"" . addslashes($row['ID']) . '"';
        $result3 = tng_query($query);
      }
      tng_free_result($result2);
    }
    tng_free_result($result);

    $logmsg = uiTextSnippet('treesgone');
  }
}

adminwritelog($logmsg);
echo $logmsg;