<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if ($ID) {
  $query = "SELECT mostwanted.title AS title, mostwanted.personID AS personID, mostwanted.description AS description, mostwanted.mediaID AS mediaID, mwtype, thumbpath, usecollfolder, media.description AS mtitle, media.notes AS mdesc, mediatypeID FROM mostwanted LEFT JOIN media ON mostwanted.mediaID = media.mediaID LEFT JOIN people ON mostwanted.personID = people.personID WHERE mostwanted.ID = '$ID'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  tng_free_result($result);
  $row['title'] = preg_replace('/\"/', '&#34;', $row['title']);
  $row['description'] = preg_replace('/\"/', '&#34;', $row['description']);
} else {
  $row['title'] = '';
  $row['description'] = '';
}
$helplang = findhelp('mostwanted_help.php');
if ($row['mwtype']) {
  $mwtype = $row['mwtype'];
}
$typemsg = $mwtype == 'person' ? uiTextSnippet('mysperson') : uiTextSnippet('mysphoto');

header('Content-type:text/html; charset=' . $session_charset);
?>
<div id='more'>
  <form name='editmostwanted' action='' onsubmit="return updateMostWanted(this);">
    <header class='modal-header'>
      <h4><?php echo uiTextSnippet('mostwanted') . ': ' . $typemsg; ?> |
        <a href='#' onclick="return openHelp('<?php echo $helplang; ?>/mostwanted_help.php');"><?php echo uiTextSnippet('help'); ?></a>
      </h4>
    </header>
    <div class='modal-body'>
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('title'); ?>:</td>
          <td>
            <input name='title' type='text' maxlength='128' value="<?php echo $row['title']; ?>">
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('description'); ?>:</td>
          <td>
            <textarea name='description' rows='7'><?php echo $row['description']; ?></textarea>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('person'); ?>:</td>
          <td>
            <table>
              <tr>
                <td>
                  <input id='personID' name='personID' type='text' size='22' maxlength='22' value="<?php echo $row['personID']; ?>">
                  &nbsp;<?php echo uiTextSnippet('or'); ?>&nbsp;
                </td>
                <td>
                  <a href='#' title="<?php echo uiTextSnippet('find'); ?>" onclick="return findItem('I', 'personID', '', '<?php echo $assignedbranch; ?>');">
                    <img class='icon-sm' src='svg/magnifying-glass.svg'>
                  </a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <br>
      <input type='button' value="<?php echo uiTextSnippet('selphoto'); ?>" onclick="return openMostWantedMediaFind();"/>
      <div id='mwphoto'>
        <table class='table table-sm'>
          <tr>
            <td id='mwthumb'
                style="width:<?php echo($thumbmaxw + 6); ?>px;height:<?php echo($thumbmaxh + 6); ?>px;text-align: center;">
              <?php
              initMediaTypes();
              $lmediatypeID = $row['mediatypeID'];
              $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$lmediatypeID] : $mediapath;

              if ($row['thumbpath'] && file_exists("$rootpath$usefolder/" . $row['thumbpath'])) {
                $photoinfo = getimagesize("$rootpath$usefolder/" . $row['thumbpath']);
                if ($photoinfo[1] < 50) {
                  $photohtouse = $photoinfo[1];
                  $photowtouse = $photoinfo[0];
                } else {
                  $photohtouse = 50;
                  $photowtouse = intval(50 * $photoinfo[0] / $photoinfo[1]);
                }
                echo "<img src=\"$usefolder/" . str_replace('%2F', '/', rawurlencode($row['thumbpath'])) . "\" width=\"$photowtouse\" height=\"$photohtouse\" id=\"img_$ID\" alt=\"{$row['mtitle']}\">";
              } else {
                echo '&nbsp;';
              }
              $row['mdesc'] = xmlcharacters($row['mdesc']);
              $truncated = substr($row['mdesc'], 0, 90);
              $truncated = strlen($row['mdesc']) > 90 ? substr($truncated, 0, strrpos($truncated, ' ')) . '&hellip;' : $row['mdesc'];
              ?>
            </td>
            <td id="mwdetails"><?php echo '<u>' . xmlcharacters($row['mtitle']) . '</u><br>' . $truncated; ?>
              &nbsp;</td>
          </tr>
        </table>
      </div>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <input name='ID' type='hidden' value="<?php echo $ID; ?>">
      <input id='mediaID' name='mediaID' type='hidden' value="<?php echo $row['mediaID']; ?>">
      <input id='orgmediaID' name='orgmediaID' type='hidden' value="<?php echo $row['mediaID']; ?>">
      <input id='mwtype' name='mwtype' type='hidden' value="<?php echo $mwtype; ?>">
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    </footer>
  </form>
</div>