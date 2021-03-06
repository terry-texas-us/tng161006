<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

require 'adminlog.php';

initMediaTypes();

//mediatypeID and linktype should be passed in
$personID = ucfirst($newlink1);
$linktype = $linktype1;
$eventID = $event1;

$sortstr = preg_replace('/xxx/', uiTextSnippet('albums'), uiTextSnippet('sortmedia'));

switch ($linktype) {
  case 'I':
    $query = "SELECT lastname, lnprefix, firstname, prefix, suffix, nameorder, branch FROM people WHERE personID = '$personID'";
    $result2 = tng_query($query);
    $person = tng_fetch_assoc($result2);
    $person['allow_living'] = 1;
    $namestr = "$personID: " . getName($person);
    tng_free_result($result2);
    break;
  case 'F':
    $query = "SELECT branch FROM families WHERE familyID = '$personID'";
    $result2 = tng_query($query);
    $person = tng_fetch_assoc($result2);
    $namestr = uiTextSnippet('family') . ": $personID";
    tng_free_result($result2);
    break;
  case 'S':
    $query = "SELECT title FROM sources WHERE sourceID = '$personID'";
    $result2 = tng_query($query);
    $person = tng_fetch_assoc($result2);
    $namestr = uiTextSnippet('source') . ": $personID";
    if ($person['title']) {
      $namestr .= ', ' . $person['title'];
    }
    $person['branch'] = '';
    tng_free_result($result2);
    break;
  case 'R':
    $query = "SELECT reponame FROM repositories WHERE repoID = '$personID'";
    $result2 = tng_query($query);
    $person = tng_fetch_assoc($result2);
    $namestr = uiTextSnippet('repository') . ": $personID";
    if ($person['reponame']) {
      $namestr .= ', ' . $person['reponame'];
    }
    $person['branch'] = '';
    tng_free_result($result2);
    break;
  case 'L':
    $namestr = $personID;
    $person['branch'] = '';
    break;
}

if (!checkbranch($person['branch'])) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}

adminwritelog("<a href=\"ordermedia.php?personID=$personID\">$sortstr: $action</a>");

$photofound = 0;
$photo = '';

$query = "SELECT alwayson, thumbpath, media.mediaID AS mediaID, usecollfolder, mediatypeID, medialinkID FROM (media, medialinks)
    WHERE personID = \"$personID\" AND media.mediaID = medialinks.mediaID AND defphoto = '1'";
$result = tng_query($query);
if ($result) {
  $row = tng_fetch_assoc($result);
}
$thismediatypeID = $row['mediatypeID'];
$usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$thismediatypeID] : $mediapath;
tng_free_result($result);

if ($row['thumbpath']) {
  $photoref = "$usefolder/" . $row['thumbpath'];
} else {
  $photoref = "$photopath/$personID.$photosext";
}

if (file_exists("$rootpath$photoref")) {
  $photoinfo = getimagesize("$rootpath$photoref");
  if ($photoinfo[1] <= $thumbmaxh) {
    $photohtouse = $photoinfo[1];
    $photowtouse = $photoinfo[0];
  } else {
    $photohtouse = $thumbmaxh;
    $photowtouse = intval($thumbmaxh * $photoinfo[0] / $photoinfo[1]);
  }
  $photo = '<img src="' . str_replace('%2F', '/', rawurlencode($photoref)) . '?' . time() . "\" alt='' width=\"$photowtouse\" height=\"$photohtouse\" style=\"margin-right:10px\">";
  $photofound = 1;
}

$query = "SELECT * FROM (albumplinks, albums) WHERE albumplinks.entityID = '$personID' AND albums.albumID = albumplinks.albumID ORDER BY ordernum";
$result = tng_query($query);

$numrows = tng_num_rows($result);

if (!$numrows) {
  $message = uiTextSnippet('noresults');
  header("Location: albumsSort.php?personID=$personID&message=" . urlencode($message));
  exit;
}
header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet($sortstr));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
<body onload="startMediaSort()">
  <?php
  echo $adminHeaderSection->build('albums-text_sort', $message);
  $navList = new navList('');
  $navList->appendItem([true, 'albumsBrowse.php', uiTextSnippet('browse'), 'findalbum']);
  $navList->appendItem([$allowAdd, 'albumsAdd.php', uiTextSnippet('add'), 'addalbum']);
  $navList->appendItem([$allowEdit, 'albumsSort.php', uiTextSnippet('text_sort'), 'sortalbums']);
  echo $navList->build('sortalbums');
  ?>
  <table class='table table-sm'>
    <tr>
      <td>
        <h4><?php echo "<div id='thumbholder' style='float: left'>$photo</div><strong>$sortstr<br>$namestr</strong>"; ?></h4>
        <br clear="left">
        <br>
        <table class="table" id="ordertbl">
          <tr>
            <th style="width:102px"><?php echo uiTextSnippet('text_sort'); ?></th>
            <th style="width:<?php echo($thumbmaxw + 10); ?>px"><?php echo uiTextSnippet('thumb'); ?></th>
            <th><?php echo uiTextSnippet('description'); ?></th>
          </tr>
        </table>

        <form name='form1'>
          <div id="orderdivs">
            <?php
            $result = tng_query($query);
            $count = 1;
            while ($row = tng_fetch_assoc($result)) {
              $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
              $truncated = substr($row['description'], 0, 90);
              $truncated = strlen($row['description']) > 90 ? substr($truncated, 0, strrpos($truncated, ' ')) . '&hellip;' : $row['description'];
              echo "<div class=\"sortrow\" id=\"orderdivs_{$row['alinkID']}\" style=\"clear:both;position:relative\" onmouseover=\"$('#md_{$row['albumID']}').css('visibility','visible');\" onmouseout=\"$('#md_{$row['albumID']}').css('visibility','hidden');\">";
              echo "<table width='100%'><tr>\n";
              echo "<td class='dragarea'>";
                echo "<img src='img/admArrowUp.gif' alt=''>" . uiTextSnippet('drag') . "\n";
                echo "<img src='img/admArrowDown.gif' alt=''>\n";
              echo "</td>\n";
              echo '<td class="small" style="width: 35px; text-align:center">';
              echo "<div style=\"padding-bottom:5px\"><a href='#' onclick=\"return moveItemInList('{$row['alinkID']}',1);\" title=\"" .
                      uiTextSnippet('movetop') . "\"><img src=\"img/admArrowUp.gif\" alt=''><br>" . uiTextSnippet('top') . "</a></div>\n";
              echo "<input class='movefields' id=\"move{$row['alinkID']}\" name=\"move{$row['alinkID']}\" style='width: 30px' value=\"$count\" onkeypress=\"handleMediaEnter('{$row['alinkID']}',$('#move{$row['alinkID']}').val(),event);\" />\n";
              echo "<a href='#' onclick=\"return moveItemInList('{$row['alinkID']}',$('#move{$row['alinkID']}').val());\" title=\"" .
                      uiTextSnippet('movetop') . '">' . uiTextSnippet('go') . "</a>\n";
              echo "</td>\n";

              echo '<td style="width: ' . ($thumbmaxw + 6) . 'px; text-align: center;">';

              $query2 = "SELECT thumbpath, usecollfolder, mediatypeID FROM (albumlinks, media) WHERE albumID=\"{$row['albumID']}\" AND defphoto = \"1\" AND albumlinks.mediaID = media.mediaID";
              $result2 = tng_query($query2) or die(uiTextSnippet('cannotexecutequery') . ": $query2");
              $trow = tng_fetch_assoc($result2);
              $tmediatypeID = $trow['mediatypeID'];
              $tusefolder = $trow['usecollfolder'] ? $mediatypes_assoc[$tmediatypeID] : $mediapath;
              tng_free_result($result2);

              if ($trow['thumbpath'] && file_exists("$rootpath$tusefolder/" . $trow['thumbpath'])) {
                $size = getimagesize("$rootpath$tusefolder/" . $trow['thumbpath']);
                echo "<a href=\"albumsEdit.php?albumID={$row['albumID']}\"><img src=\"$tusefolder/" . str_replace('%2F', '/', rawurlencode($trow['thumbpath'])) . "\" $size[3] alt=\"{$row['albumname']}\"></a>";
              } else {
                echo '&nbsp;';
              }
              echo "</td>\n";
              $checked = $row['defphoto'] ? ' checked' : '';
              echo "<td><a href=\"editalbum.php?albumID={$row['albumID']}\">{$row['albumname']}</a><br>$truncated<br>";
              echo "<span id=\"md_{$row['albumID']}\" class=\"small\" style=\"visibility: hidden\"><a href='#' onclick=\"return removeFromSort('album','{$row['alinkID']}');\">" .
                      uiTextSnippet('remove') . "</a></span></td>\n";
              echo '</tr></table>';
              echo "</div>\n";
              $count++;
            }
            tng_free_result($result);
            ?>
          </div>
        </form>

      </td>
    </tr>

  </table>
  <?php
  echo $adminFooterSection->build();
  echo scriptsManager::buildScriptElements($flags, 'admin');
  ?>
  <script>
    var entity = "<?php echo $personID; ?>";
    var album = "<?php echo $albumID; ?>";
    var orderaction = "alborder";
  </script>
  <script src='js/albums.js'></script>
  <script src='js/selectutils.js'></script>
  <script src='js/mediautils.js'></script>
</body>
</html>
