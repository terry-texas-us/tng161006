<?php
require 'begin.php';
require 'genlib.php';
if (!is_numeric($mediaID)) {
  header('Location: thispagedoesnotexist.html');
  exit;
}
require 'getlang.php';

require 'log.php';
require 'functions.php';
require 'personlib.php';

initMediaTypes();

if ($medialinkID) {
  //look up media & medialinks joined
  //get info for linked person/family/source/repo
  $query = "SELECT mediatypeID, personID, linktype, eventID, ordernum FROM (media, medialinks) WHERE medialinkID = '$medialinkID' AND media.mediaID = medialinks.mediaID";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $personID = $row['personID'];
  $ordernum = $row['ordernum'];
  $mediatypeID = $row['mediatypeID'];
  $linktype = $row['linktype'];
  if ($linktype == 'P') {
    $linktype = 'I';
  }
  $eventID = $row['eventID'];
} else {
  if ($albumlinkID) {
    $query = "SELECT albumname, description, ordernum, albums.albumID AS albumID FROM (albums, albumlinks)
      WHERE albumlinkID = \"$albumlinkID\" AND albumlinks.albumID = albums.albumID";
    $result = tng_query($query);
    $row = tng_fetch_assoc($result);
    $ordernum = $row['ordernum'];
    $albumID = $row['albumID'];
    $albumname = $row['albumname'];
    $albdesc = $row['description'];
    tng_free_result($result);
  }
  $query = "SELECT mediatypeID FROM media WHERE mediaID = '$mediaID'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $mediatypeID = $row['mediatypeID'];
}
if (!tng_num_rows($result)) {
  tng_free_result($result);
  header('Location: thispagedoesnotexist.html');
  exit;
}
require 'checklogin.php';
require 'showmedialib.php';

$mediaperpage = 1;
$max_showmedia_pages = 5;

$info = getMediaInfo($mediatypeID, $mediaID, $personID, $albumID, $albumlinkID, $cemeteryID, $eventID);
$ordernum = $info['ordernum'];
$mediaID = $info['mediaID'];
$medianotes = $info['medianotes'];
$mediadescription = $info['mediadescription'];
$page = $info['page'];
$result = $info['result'];
$imgrow = $info['imgrow'];

$numitems = tng_num_rows($result);

if ($personID && !$albumlinkID) {
  if ($linktype == 'L') {
    $row['allow_living'] = 1;
    $rightbranch = 1;
  } else {
    if ($linktype == 'F') {
      $query = "SELECT familyID, husband, wife, living, marrdate, branch FROM families WHERE familyID = '$personID'";
    } elseif ($linktype == 'S') {
      $query = "SELECT title FROM sources WHERE sourceID = '$personID'";
    } elseif ($linktype == 'R') {
      $query = "SELECT reponame FROM repositories WHERE repoID = '$personID'";
    } elseif ($linktype == 'I') {
      $query = "SELECT lastname, firstname, prefix, suffix, title, lnprefix, living, private, branch, birthdate, birthdatetr, altbirthdate, altbirthdatetr, deathdate, deathdatetr, burialdate, burialdatetr, sex, IF(birthdatetr !='0000-00-00', YEAR(birthdatetr), YEAR(altbirthdatetr)) AS birth, IF(deathdatetr !='0000-00-00', YEAR(deathdatetr), YEAR(burialdatetr)) AS death FROM people, trees WHERE personID = '$personID'";
    }
    $result2 = tng_query($query);
    if ($result2) {
      $row = tng_fetch_assoc($result2);
      if ($linktype == 'S' || $linktype == 'R') {
        $row['allow_living'] = 1;
        $row['allow_private'] = 1;
        $rightbranch = 1;
      } else {
        $rightbranch = checkbranch($row['branch']);
        $rights = determineLivingPrivateRights($row, $rightbranch);
        $row['allow_living'] = $rights['living'];
        $row['allow_private'] = $rights['private'];
      }
      tng_free_result($result2);
    }
  }
}

$livinginfo = findLivingPrivate($mediaID);
$noneliving = $livinginfo['noneliving'] && $livinginfo['noneprivate'];

$showPhotoInfo = $imgrow['alwayson'] || $noneliving;
$nonamesloc = $livinginfo['private'] ? $tngconfig['nnpriv'] : $nonames;

if ($noneliving || !$nonamesloc || $imgrow['alwayson']) {
  $description = preg_replace('/\"/', '&#34;', $mediadescription);
  $notes = nl2br(getXrefNotes($medianotes));
  $mapnote = $info['gotmap'] ? '<p>' . uiTextSnippet('mediamaptext') . "</p>\n" : '';
} else {
  $description = $notes = ($livinginfo['private'] ? uiTextSnippet('private') : uiTextSnippet('living'));
  $mapnote = '';
}
$logdesc = $nonamesloc && !$noneliving && !$imgrow['alwayson'] ? ($livinginfo['private'] ? uiTextSnippet('private') : uiTextSnippet('living')) : $description;
$mediatypeIDstr = uiTextSnippet($mediatypeID) ? uiTextSnippet($mediatypeID) : $mediatypes_display[$mediatypeID];

if (!$personID) {
  writelog("<a href=\"showmedia.php?mediaID=$mediaID&amp;tnggallery=$tnggallery\">$mediatypeIDstr: $logdesc ($mediaID)</a>");
  preparebookmark("<a href=\"showmedia.php?mediaID=$mediaID&amp;tnggallery=$tnggallery\">$mediatypeIDstr: $description ($mediaID)</a>");
} elseif ($albumlinkID) {
  writelog("<a href=\"showmedia.php?mediaID=$mediaID&amp;albumlinkID=$albumlinkID&amp;tnggallery=$tnggallery\">" . uiTextSnippet('albums') . ": $logdesc ($mediaID)</a>");
  preparebookmark("<a href=\"showmedia.php?mediaID=$mediaID&amp;albumlinkID=$albumlinkID&amp;tnggallery=$tnggallery\">" . uiTextSnippet('albums') . ": $description ($mediaID)</a>");
} else {
  writelog("<a href=\"showmedia.php?mediaID=$mediaID&amp;medialinkID=$medialinkID\">$mediatypeIDstr: $logdesc ($mediaID)</a>");
  preparebookmark("<a href=\"showmedia.php?mediaID=$mediaID&amp;medialinkID=$medialinkID\">$mediatypeIDstr: $description ($mediaID)</a>");
}

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle($mediatypeIDstr . ': ' . $description);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<?php
echo "<body id='public'>\n";
  echo "<section class='container'>\n";
    echo $publicHeaderSection->build();

    $usefolder = $imgrow['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
    $size = getimagesize("$rootpath$usefolder/" . $imgrow['path'], $info);
    $adjheight = $size['1'] - 1;

    if ($personID) {
      if ($linktype == 'I') {
        $namestr = getName($row);
        $years = getYears($row);
        $type = 'person';
      } elseif ($linktype == 'F') {
        $namestr = uiTextSnippet('family') . ': ' . getFamilyName($row);
        $years = $row['marrdate'] && $row['allow_living'] && $row['allow_private'] ? uiTextSnippet('marrabbr') . ' ' . displayDate($row['marrdate']) : '';
        $type = 'family';
      } elseif ($linktype == 'S') {
        $namestr = $row['title'];
        $type = 'source';
      } elseif ($linktype == 'R') {
        $namestr = $row['reponame'];
        $type = 'repo';
      } else {
        $namestr = $personID;
        $type = 'place';
      }
      $mediastr = showSmallPhoto($personID, $namestr, $row['allow_living'] && $row['allow_private'], 0, false, $row['sex']);
      echo tng_DrawHeading($mediastr, $namestr, $years);

      echo tng_menu($linktype, $type, $personID);
      echo "<br>\n";
    } else {
      if ($albumlinkID) {
        $mediastr = "<img class='icon-md' src='svg/album.svg'>\n";
        echo tng_DrawHeading($mediastr, $albumname, $albdesc);
      } else {
        $titlemsg = uiTextSnippet($mediatypeID) ? uiTextSnippet($mediatypeID) : $mediatypes_display[$mediatypeID];
        $icon = $mediatypes_icons[$mediatypeID];
        if ($mediatypes_icons[$mediatypeID]) {
          $icon = "<img class='icon-md' src='{$mediatypes_icons[$mediatypeID]}' alt=''>";
        } else {
          $icon = "<span class='icon-md icon-{$mediatypeID}'></span>";
        }
        echo "<h1>$icon$titlemsg</h1>\n";
      }
    }
    $pagenav = getMediaNavigation($mediaID, $personID, $albumlinkID, $result, true);

    if ($page < $totalpages) {
      $nextpage = $page + 1;
    } else {
      $nextpage = 1;
    }
    $nextmediaID = get_item_id($result, $nextpage - 1, 'mediaID');
    $nextmedialinkID = get_item_id($result, $nextpage - 1, 'medialinkID');
    $nextalbumlinkID = get_item_id($result, $nextpage - 1, 'albumlinkID');

    tng_free_result($result);

    echo "<div style='margin-top: 2.5em'>$pagenav</div>";

    if ($noneliving || $imgrow['alwayson']) {
      $show_on_top = false;
      if ((isset($mediatypes_like['histories']) && !in_array($mediatypeID, $mediatypes_like['histories'])) || !$imgrow['bodytext']) {
        echo $mapnote;
        showMediaSource($imgrow);
        echo '<br>';
        $show_on_top = true;
      }

      echo "<h4>$description</h4>\n";
      if ($notes) {
        echo "<p>$notes</p>\n";
      } else {
        echo '<br>';
      }
      if (!$show_on_top) {
        showMediaSource($imgrow);
      }
      if ($mediatypeID == 'headstones' && ($imgrow['status'] || $imgrow['plot'])) {
        echo '<p>';
        if ($imgrow['status']) {
          $status = $imgrow['status'];
          if ($status && uiTextSnippet($status)) {
            $imgrow['status'] = uiTextSnippet($status);
          }
          echo '<b>' . uiTextSnippet('status') . ":</b> {$imgrow['status']}";
        }
        if ($imgrow['plot']) {
          if ($imgrow['status']) {
            echo '<br>';
          }
          echo '<b>' . uiTextSnippet('plot') . ':</b> ' . nl2br($imgrow['plot']);
        }
        echo '</p>';
      } elseif (!$tngconfig['imgviewer'] || in_array($mediatypeID, $mediatypes_like[$tngconfig['imgviewer']])) {
        echo "<br>\n";
      } else {
        echo "<br>\n";
      }
      $medialinktext = getMediaLinkText($mediaID, $ioffset);
      $albumlinktext = getAlbumLinkText($mediaID);
      echo showTable($imgrow, $medialinktext, $albumlinktext);

      //do cemetery name here for headstones
      //do map here for headstones
      if ($imgrow['cemeteryID']) {
        doCemPlusMap($imgrow);
      }
      echo "<br><div>$pagenav</div><br>\n";
    } else {
      ?>
      <div style="border:1px solid black;padding:5px;width:<?php echo $size['0']; ?>px;height:<?php echo $adjheight; ?>px">
        <strong><span><?php echo $livinginfo['private'] ? uiTextSnippet('private') : uiTextSnippet('living'); ?></span></strong>
      </div>
      <?php
    }
    echo "<br>\n";
    echo $publicFooterSection->build();
  echo "</section> <!-- .container -->\n";
  echo scriptsManager::buildScriptElements($flags, 'public');

  ?>
  <script>
    <?php if ($imgviewer && !in_array($imgrow['mediatypeID'], $mediatypes_like[$imgviewer])) { ?>
      $(document).ready(adjustWidth);
      function adjustWidth() {
        if ($('#imgdiv').length && $('#theimage').width() > document.getElementById('imgdiv').clientWidth) {
          $('#imgdiv').width($('#theimage').width() + 'px');
        }
      }
    <?php } ?>
  </script>
  <?php

  $imgviewer = $tngconfig['imgviewer'];
  if (!$imgviewer || in_array($imgrow['mediatypeID'], $mediatypes_like[$imgviewer])) {
  ?>
  <script>
  <?php include 'js/img_utils.js'; ?>
  </script>
  <?php } ?>
</body>
</html>