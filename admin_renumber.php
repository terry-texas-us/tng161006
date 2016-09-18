<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

require 'prefixes.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
//can only start if in maintenance mode

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('backuprestore'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>

<?php
$headline = uiTextSnippet('backuprestore') . ' &gt;&gt; ' . uiTextSnippet('renumber');
$navList = new navList('');
$navList->appendItem([true, 'admin_utilities.php?sub=tables', uiTextSnippet('tables'), 'tables']);
$navList->appendItem([true, 'admin_utilities.php?sub=structure', uiTextSnippet('tablestruct'), 'structure']);
$navList->appendItem([true, 'admin_renumbermenu.php', uiTextSnippet('renumber'), 'renumber']);
echo $navList->build('renumber');
?>
<div>
  <div>

    <h4><?php echo uiTextSnippet('renumber'); ?></h4>

    <?php
    $nextnum = isset($start) ? $start : 1;
    if (!isset($digits)) {
      $digits = 0;
    }
    if (!isset($type)) {
      $type = 'person';
    }
    $count = 0;

    eval("\$prefix = \$$type" . 'prefix;');
    eval("\$suffix = \$$type" . 'suffix;');

    //choose to do people, families, sources or repos
    if ($type == 'person') {
      $table = $people_table;
      $id = 'personID';
    } elseif ($type == 'family') {
      $table = $families_table;
      $id = 'familyID';
    } elseif ($type == 'source') {
      $table = 'sources';
      $id = 'sourceID';
    } elseif ($type == 'repo') {
      $table = 'repositories';
      $id = 'repoID';
    }

    //get all people after start number, sorted on ID (not including prefix)
    if ($prefix) {
      $prefixlen = strlen($prefix) + 1;

      $query = "SELECT ID, $id, (0+SUBSTRING($id,$prefixlen)) AS num FROM $table WHERE (0+SUBSTRING($id,$prefixlen)) >= $nextnum ORDER BY num";
    } else {
      $query = "SELECT ID, $id, (0+SUBSTRING_INDEX($id,'$suffix',1)) AS num FROM $table WHERE (0+SUBSTRING_INDEX($id,'$suffix',1)) >= $nextnum ORDER BY num";
    }

    $result = tng_query($query);

    //do this only for person type:
    if ($type == 'person') {
      //search media table for all media records with an image map
      $query = "SELECT mediaID, map FROM media WHERE map != ''";
      $result1 = tng_query($query);
      $keys = [];
      $maps = [];
      while ($row = tng_fetch_assoc($result1)) {
        //put all in an array with mediaID as the key
        $maps[$row['mediaID']] = ['map' => $row['map'], 'newmap' => ''];
        $pattern = "/personID=(I\d+)&[amp;]*tree=$tree/";
        //loop over all of them and pull out person IDs
        preg_match_all($pattern, $row['map'], $matches, PREG_SET_ORDER);
        //build an index with the personID as the key and the mediaID as the value
        foreach ($matches as $match) {
          $fullmatch = $match[0];
          $specmatch = $match[1];
          $key = $specmatch;
          $keys[$key][] = ['mediaID' => $row['mediaID'], 'found' => $fullmatch];
          /* this block used for testing
            if(isset($keys[$key])) {
            foreach($keys[$key] as $tkey) {
            $mediaID = $tkey['mediaID'];
            $map = $maps[$mediaID]['map'];
            $offset = strpos($map, $tkey['found']);

            if($offset) {
            $offset += 9;
            $oldlen = strlen($key);
            $newmap = substr_replace($map, "newID", $offset, $oldlen);
            $maps[$mediaID]['map'] = $maps[$mediaID]['newmap'] = $newmap;
            echo "newmap = \n$newmap\n";
            }
            }
            }
           */
        }
      }
      tng_free_result($result1);
    }

    while ($row = tng_fetch_assoc($result)) {
      if ($row['num'] < $nextnum) {
        break;
      }
      if ($row['num'] >= $nextnum) {
        $newID = $digits ? ($prefix . str_pad($nextnum, $digits, '0', STR_PAD_LEFT) . $suffix) : ($prefix . $nextnum . $suffix);

        $query = "SELECT ID FROM $table WHERE $id = '$newID'";
        $result1 = tng_query($query);
        if (!tng_num_rows($result1)) {
          //if(tng_num_rows($result1)) die("Problem: destination ID ($newID) already exists. Operation aborted.");
          //change ID in people to match next #
          $query = "UPDATE $table SET $id=\"$newID\" WHERE ID=\"{$row['ID']}\"";
          $result2 = tng_query($query);

          if ($type == 'person') {
            $old = $row['personID'];

            $query = "UPDATE $families_table SET husband = '$newID' WHERE husband = '$old'";
            $result2 = tng_query($query);

            $query = "UPDATE $families_table SET wife = '$newID' WHERE wife = '$old'";
            $result2 = tng_query($query);

            $query = "UPDATE $children_table SET personID = '$newID' WHERE personID = '$old'";
            $result2 = tng_query($query);

            $query = "UPDATE associations SET personID = '$newID' WHERE personID = '$old'";
            $result2 = tng_query($query);

            $query = "UPDATE associations SET passocID = '$newID' WHERE passocID = '$old'";
            $result2 = tng_query($query);

            $query = "UPDATE temp_events SET personID = '$newID' WHERE personID = '$old'";
            $result2 = tng_query($query);

            $query = "UPDATE mostwanted SET personID = '$newID' WHERE personID = '$old'";
            $result2 = tng_query($query);

            $query = "UPDATE users SET personID = '$newID' WHERE personID = '$old'";
            $result2 = tng_query($query);

            if (isset($keys[$old])) {
              foreach ($keys[$old] as $key) {
                $mediaID = $key['mediaID'];
                $map = $maps[$mediaID]['map'];
                $offset = strpos($map, $key['found']);

                if ($offset) {
                  $offset += 9; //length of personID=
                  $oldlen = strlen($old);
                  $newmap = substr_replace($map, $newID, $offset, $oldlen);
                  $maps[$mediaID]['map'] = $maps[$mediaID]['newmap'] = $newmap;

                  $query = "UPDATE media SET map=\"" . addslashes($newmap) . "\" WHERE mediaID=\"$mediaID\"";
                  $result2 = tng_query($query);
                }
              }
            }
          }

          if ($type == 'family') {
            $query = "UPDATE $children_table SET familyID = '$newID' WHERE familyID=\"{$row['familyID']}\"";
            $result2 = tng_query($query);

            $query = "UPDATE $people_table SET famc = '$newID' WHERE famc=\"{$row['familyID']}\"";
            $result2 = tng_query($query);

            $query = "UPDATE temp_events SET familyID = '$newID' WHERE familyID=\"{$row['familyID']}\"";
            $result2 = tng_query($query);
          }

          $query = "UPDATE events SET persfamID = '$newID' WHERE persfamID=\"" . $row[$id] . '"';
          $result2 = tng_query($query);

          $query = "UPDATE medialinks SET personID = '$newID' WHERE personID=\"" . $row[$id] . '"';
          $result2 = tng_query($query);

          if ($type == 'person' || $type == 'family') {
            $query = "UPDATE branchlinks SET persfamID = '$newID' WHERE persfamID=\"" . $row[$id] . '"';
            $result2 = tng_query_noerror($query);
            $success = tng_affected_rows();
            if (!$success) {
              $query = "DELETE FROM branchlinks WHERE persfamID=\"" . $row[$id] . '"';
              $result2 = tng_query_noerror($query);
            }
          }

          $query = "UPDATE albumplinks SET entityID = '$newID' WHERE entityID=\"" . $row[$id] . '"';
          $result2 = tng_query($query);

          if ($type == 'source') {
            $query = "UPDATE citations SET sourceID = '$newID' WHERE sourceID=\"" . $row[$id] . '"';
            $result2 = tng_query($query);
          } else {
            $query = "UPDATE citations SET persfamID = '$newID' WHERE persfamID=\"" . $row[$id] . '"';
            $result2 = tng_query($query);
          }

          $query = "UPDATE notelinks SET persfamID = '$newID' WHERE persfamID=\"" . $row[$id] . '"';
          $result2 = tng_query($query);

          //echo "$row['personID'] -&gt; $newID<br>";
          $count++;
          if ($count % 10 == 0) {
            echo "<strong>$count</strong> ";
          }
        }
        tng_free_result($result1);
      }
      $nextnum++;
    }
    tng_free_result($result);

    echo '<p>' . uiTextSnippet('finreseq') . ": $count " . uiTextSnippet('recsreseq') . "</p>\n";
    echo "</div></div>\n";
    echo "<div align=\"right\"><span>$tng_title, v.$tng_version</span></div>";
    ?>
</body>
</html>
