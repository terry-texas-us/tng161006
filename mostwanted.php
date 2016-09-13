<?php
require 'tng_begin.php';

require 'functions.php';

$logstring = "<a href='mostwanted.php'>" . xmlcharacters(uiTextSnippet('mostwanted')) . '</a>';
writelog($logstring);
preparebookmark($logstring);

$gotImageJpeg = function_exists(imageJpeg);

function showDivs($type) {
  global $people_table;
  global $media_table;
  global $mostwanted_table;
  global $mediatypes_assoc;
  global $mediapath;
  global $rootpath;
  global $gotImageJpeg;
  global $maxmediafilesize;

  $mediatext = "<table class='table'>\n";

  $query = "SELECT DISTINCT $mostwanted_table.ID AS mwID, mwtype, thumbpath, abspath, form, usecollfolder, mediatypeID, path, $media_table.description AS mtitle, $mostwanted_table.personID, $mostwanted_table.mediaID, $mostwanted_table.description AS mwdesc, $mostwanted_table.title AS mwtitle, lastname, firstname, lnprefix, suffix, prefix, $people_table.title AS title, living, private, nameorder, branch FROM $mostwanted_table "
      . "LEFT JOIN $media_table ON $mostwanted_table.mediaID = $media_table.mediaID "
      . "LEFT JOIN $people_table ON $mostwanted_table.personID = $people_table.personID "
      . "WHERE mwtype = '$type' ORDER BY ordernum";
  $result = tng_query($query);

  while ($row = tng_fetch_assoc($result)) {
    $mediatypeID = $row['mediatypeID'];
    $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;
    $row['allow_living'] = 1;
    $imgsrc = $row['mediaID'] ? getSmallPhoto($row) : '';

    $mediatext .= "<tr><td>\n";
    $href = getMediaHREF($row, 0);
    if ($imgsrc) {
      //$mediatext .= "<div class=\"mwimage\">\n<div class=\"media-img\"><div class=\"media-prev\" id=\"prev{$row['mediaID']}\" style=\"display:none;left:$thumbmaxw" . "px\"></div></div>\n";
      $mediatext .= "<div class=\"mwimage\">\n<div class=\"media-img\"><div class=\"media-prev\" id=\"prev{$row['mediaID']}\" style=\"display:none;\"></div></div>\n";
      $mediatext .= "<a href=\"$href\"";
      if ($gotImageJpeg && isPhoto($row) && filesize("$rootpath$usefolder/" . $row['path']) < $maxmediafilesize) {
        $mediatext .= " class=\"media-preview\" id=\"img-{$row['mediaID']}-0-" . urlencode("$usefolder/{$row['path']}") . "\"";
      }
      $mediatext .= ">$imgsrc</a>\n";
      $mediatext .= "</div>\n";
    }
    $mediatext .= "<span><strong>{$row['mwtitle']}</strong></span><br><br>";
    $mediatext .= "<div style=\"margin:0;\">{$row['mwdesc']}</div>";

    $mediatext .= "<div class=\"mwperson\">\n";
    if ($type == 'person') {
      if ($row['personID']) {
        $mediatext .= "<a href=\"personSuggest.php?&amp;ID={$row['personID']}\">" . uiTextSnippet('tellus') . '</a>';

        $rights = determineLivingPrivateRights($row);
        $row['allow_living'] = $rights['living'];
        $row['allow_private'] = $rights['private'];

        $name = getName($row);
        $mediatext .= ' &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp; ' . uiTextSnippet('moreinfo') . " <a href=\"peopleShowPerson.php?personID={$row['personID']}\">$name</a>";
      } else {
        $mediatext .= "<a href=\"contactUs.php?page=" . uiTextSnippet('mostwanted') . ":+{$row['mwtitle']}\">" . uiTextSnippet('tellus') . '</a>';
      }
    }
    if ($type == 'photo' && $row['mediaID']) {
      $mediatext .= "<a href=\"contactUs.php?page=" . uiTextSnippet('mostwanted') . ":+{$row['mtitle']}\">" . uiTextSnippet('tellus') . '</a>';
      $mediatext .= ' &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp; ' . uiTextSnippet('moreinfo') . " <a href=\"$href\">{$row['mtitle']}</a> &nbsp;&nbsp;&nbsp;";
    }
    $mediatext .= "</div>\n";
    $mediatext .= "</td></tr>\n";
  }
  tng_num_rows($result);
  tng_free_result($result);

  $mediatext .= "</table>\n";

  return $mediatext;
}

$flags = '';

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('mostwanted'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>

<?php $flags['imgprev'] = true; ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/person-unknown.svg'><?php echo uiTextSnippet('mostwanted'); ?></h2>
    <br clear='left'>
    <?php

    echo "<div class=\"titlebox mwblock\">\n";
    echo '<h4>' . uiTextSnippet('mysperson') . '</h4>';
    echo showDivs('person');
    echo "</div>\n";

    echo "<br>\n";

    echo "<div class=\"titlebox mwblock\">\n";
    echo '<h4>' . uiTextSnippet('mysphoto') . '</h4>';
    echo showDivs('photo');
    echo "</div>\n";
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>