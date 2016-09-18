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

$sortstr = preg_replace('/xxx/', uiTextSnippet($mediatypeID), uiTextSnippet('sortmedia'));

switch ($linktype) {
  case 'I':
    $query = "SELECT lastname, lnprefix, firstname, prefix, suffix, nameorder, branch FROM $people_table WHERE personID = '$personID'";
    $result2 = tng_query($query);
    $person = tng_fetch_assoc($result2);
    $person['allow_living'] = 1;
    $namestr = "$personID: " . getName($person);
    tng_free_result($result2);
    $test_url = 'peopleShowPerson.php?';
    $testID = 'personID';
    break;
  case 'F':
    $query = "SELECT branch FROM $families_table WHERE familyID = '$personID'";
    $result2 = tng_query($query);
    $person = tng_fetch_assoc($result2);
    $namestr = uiTextSnippet('family') . ": $personID";
    tng_free_result($result2);
    $test_url = 'familiesShowFamily.php?';
    $testID = 'familyID';
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
    $test_url = 'sourcesShowSource.php?';
    $testID = 'sourceID';
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
    $test_url = 'repositoriesShowItem.php?';
    $testID = 'repoID';
    break;
  case 'L':
    $namestr = $personID;
    $person['branch'] = '';
    $test_url = 'placesearch.php?';
    $testID = 'psearch';
    break;
}

if (!checkbranch($person['branch'])) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}

adminwritelog("<a href=\"ordermedia.php?personID=$personID\">$sortstr: $personID</a>");

$photo = '';

$query = "SELECT alwayson, thumbpath, media.mediaID AS mediaID, usecollfolder, mediatypeID, medialinkID FROM (media, medialinks)
    WHERE personID = '$personID' AND media.mediaID = medialinks.mediaID AND defphoto = '1'";
$result = tng_query($query);
if ($result) {
  $row = tng_fetch_assoc($result);
}
$thismediatypeID = $row['mediatypeID'];
tng_free_result($result);

$query = "SELECT * FROM (medialinks, media) WHERE medialinks.personID = '$personID' AND media.mediaID = medialinks.mediaID AND eventID = \"$eventID\" AND mediatypeID = \"$mediatypeID\" ORDER BY ordernum";
$result = tng_query($query);

$numrows = tng_num_rows($result);

if (!$numrows) {
  $message = uiTextSnippet('noresults');
  header("Location: mediaSort.php?personID=$personID&message=" . urlencode($message));
  exit;
}
header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet($sortstr));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body onLoad="startMediaSort()">
  <?php
  echo $adminHeaderSection->build('media-text_sort', $message);
  $navList = new navList('');
  $navList->appendItem([true, 'mediaBrowse.php', uiTextSnippet('browse'), 'findmedia']);
  $navList->appendItem([$allowMediaAdd, 'mediaAdd.php', uiTextSnippet('add'), 'addmedia']);
  $navList->appendItem([$allowMediaEdit, 'mediaSort.php', uiTextSnippet('text_sort'), 'sortmedia']);
  $navList->appendItem([$allowMediaEdit, 'mediaThumbnails.php', uiTextSnippet('thumbnails'), 'thumbs']);
  $navList->appendItem([$allowMediaAdd, 'mediaImport.php', uiTextSnippet('import'), 'import']);
  $navList->appendItem([$allowMediaAdd, 'mediaUpload.php', uiTextSnippet('upload'), 'upload']);
  echo $navList->build('sortmedia');
  ?>
  <br>
  <a href="<?php echo $test_url; ?><?php echo $testID; ?>=<?php echo $personID; ?>" title='<?php echo uiTextSnippet('preview') ?>'>
    <img class='icon-sm' src='svg/eye.svg'>
  </a>
  <table class='table table-sm'>
    <tr>
      <td>
        <h4><?php echo "<div id='thumbholder' style='float: left'>$photo</div><strong>$sortstr<br>$namestr</strong>"; ?></h4>
        <br clear="left">
        <?php
        echo "<p class='small' id='removedefault'";
        if (!$photo) {
          echo " style='display: none'";
        }
        echo "><a href='#' onclick=\"return removeDefault();\">" . uiTextSnippet('removedef') . "</a></p>\n";
        ?>
        <table class="table" id="ordertbl">
          <tr>
            <th style="width:102px"><?php echo uiTextSnippet('text_sort'); ?></th>
            <th style="width:<?php echo($thumbmaxw + 10); ?>px"><?php echo uiTextSnippet('thumb'); ?></th>
            <th><?php echo uiTextSnippet('description'); ?></th>
            <th style="width:49px;"><?php echo uiTextSnippet('show'); ?></th>
            <th style="width:155px"><?php echo uiTextSnippet('datetaken'); ?></th>
          </tr>
        </table>

        <form name='form1'>
          <div id="orderdivs">
            <?php
            $result = tng_query($query);
            $count = 1;
            while ($row = tng_fetch_assoc($result)) {
              $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
              $truncated = substr($row['notes'], 0, 90);
              $truncated = strlen($row['notes']) > 90 ? substr($truncated, 0, strrpos($truncated, ' ')) . '&hellip;' : $row['notes'];
              echo "<div class=\"sortrow\" id=\"orderdivs_{$row['medialinkID']}\" style=\"clear:both;position:relative\" onmouseover=\"$('#md_{$row['medialinkID']}').css('visibility','visible');\" onmouseout=\"$('#md_{$row['medialinkID']}').css('visibility','hidden');\">";
              echo "<table width='100%'><tr>\n";
              echo "<td class='dragarea'>";
                echo "<img src='img/admArrowUp.gif' alt=''>" . uiTextSnippet('drag') . "\n";
                echo "<img src='img/admArrowDown.gif' alt=''>\n";
              echo "</td>\n";

              echo '<td class="small" style="width: 35px; text-align: center">';
              echo "<div style=\"padding-bottom:5px\"><a href='#' onclick=\"return moveItemInList('{$row['medialinkID']}',1);\" title=\"" .
                      uiTextSnippet('movetop') . "\"><img src=\"img/admArrowUp.gif\" alt=''><br>" . uiTextSnippet('top') . "</a></div>\n";
              echo "<input class='movefields' id=\"move{$row['medialinkID']}\" name=\"move{$row['medialinkID']}\" style='width: 30px' value=\"$count\" onkeypress=\"handleMediaEnter('{$row['medialinkID']}',$('#move{$row['medialinkID']}').val(),event);\" />\n";
              echo "<a href='#' onclick=\"return moveItemInList('{$row['medialinkID']}',$('#move{$row['medialinkID']}').val());\" title=\"" .
                      uiTextSnippet('movetop') . '">' . uiTextSnippet('go') . "</a>\n";
              echo "</td>\n";

              echo '<td style="width:' . ($thumbmaxw + 6) . 'px; text-align: center;">';
              if ($row['thumbpath'] && file_exists("$rootpath$usefolder/" . $row['thumbpath'])) {
                $size = getimagesize("$rootpath$usefolder/" . $row['thumbpath']);
                echo "<a href=\"mediaEdit.php?mediaID={$row['mediaID']}\"><img src=\"$usefolder/" . str_replace('%2F', '/', rawurlencode($row['thumbpath'])) . "\" $size[3] alt=\"{$row['description']}\"></a>";
              } else {
                echo '&nbsp;';
              }
              echo "</td>\n";
              $checked = $row['defphoto'] ? ' checked' : '';
              echo "<td><a href=\"mediaEdit.php?mediaID={$row['mediaID']}\">{$row['description']}</a><br>$truncated<br>\n";
              echo "<span id=\"md_{$row['medialinkID']}\" class=\"small\" style=\"color: gray; visibility: hidden\">\n";
              echo "<input name='rthumbs' type='radio' value=\"r{$row['mediaID']}\"$checked onclick=\"makeDefault(this);\">" . uiTextSnippet('makedefault') . "\n";
              echo ' &nbsp;|&nbsp; ';
              echo "<a href='#' onclick=\"return removeFromSort('media','{$row['medialinkID']}');\">" . uiTextSnippet('remove') . '</a>';
              echo "</span>&nbsp;</td>\n";
              echo '<td style="width: 45px; text-align: center">';
              $checked = $row['dontshow'] ? '' : ' checked';
              echo "<input name=\"show{$row['medialinkID']}\" type='checkbox' onclick=\"toggleShow(this);\" value='1'$checked/>&nbsp;</td>\n";
              echo "<td style=\"width:150px;\">{$row['datetaken']}&nbsp;</td>\n";
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
  
  $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$thismediatypeID] : $mediapath;

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
  }
  ?>
  <script>
    var entity = "<?php echo $personID; ?>";
    var album = '';
    var orderaction = "order";
  </script>
  <script src='js/selectutils.js'></script>
  <script src='js/mediautils.js'></script>
  <script src='js/admin.js'></script>
</body>
</html>
