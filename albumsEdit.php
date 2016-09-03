<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowMediaEdit && (!$allowMediaAdd || !$added)) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$tng_search_places = $_SESSION['tng_search_album'];

$query = "SELECT * FROM $albums_table WHERE albumID = \"$albumID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['description'] = preg_replace("/\"/", "&#34;", $row['description']);
$row['keywords'] = preg_replace("/\"/", "&#34;", $row['keywords']);

$query2 = "SELECT albumlinkID, thumbpath, $media_table.mediaID AS mediaID, usecollfolder, mediatypeID, notes, description, datetaken, placetaken, defphoto FROM ($media_table, $albumlinks_table)
    WHERE albumID = \"$albumID\" AND $media_table.mediaID = $albumlinks_table.mediaID order by ordernum, description";
$result2 = tng_query($query2) or die(uiTextSnippet('cannotexecutequery') . ": $query2");
$numrows = tng_num_rows($result2);

$query3 = "SELECT alinkID, entityID, eventID, people.lastname AS lastname, people.lnprefix AS lnprefix, people.firstname AS firstname, people.suffix AS suffix, people.nameorder AS nameorder, familyID, people.personID AS personID, wifepeople.personID AS wpersonID, wifepeople.firstname AS wfirstname, wifepeople.lnprefix AS wlnprefix, wifepeople.lastname AS wlastname, wifepeople.prefix AS wprefix, wifepeople.suffix AS wsuffix, wifepeople.nameorder AS wnameorder, husbpeople.personID AS hpersonID, husbpeople.firstname AS hfirstname, husbpeople.lnprefix AS hlnprefix, husbpeople.lastname AS hlastname, husbpeople.prefix AS hprefix, husbpeople.suffix AS hsuffix, husbpeople.nameorder AS hnameorder, sourceID, sources.title, repositories.repoID AS repoID, reponame, linktype FROM ($album2entities_table as ate) "
    . "LEFT JOIN $people_table AS people ON ate.entityID = people.personID "
    . "LEFT JOIN $families_table ON ate.entityID = $families_table.familyID "
    . "LEFT JOIN $sources_table AS sources ON ate.entityID = sources.sourceID "
    . "LEFT JOIN $repositories_table AS repositories ON ate.entityID = repositories.repoID "
    . "LEFT JOIN $people_table AS husbpeople ON $families_table.husband = husbpeople.personID "
    . "LEFT JOIN $people_table AS wifepeople ON $families_table.wife = wifepeople.personID "
    . "WHERE albumID = '$albumID' ORDER BY alinkID DESC";
$result3 = tng_query($query3) or die(uiTextSnippet('cannotexecutequery') . ": $query3");
$numlinks = tng_num_rows($result3);

if (!$thumbmaxw) {
  $thumbmaxw = 50;
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('modifyalbum'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body onload="startMediaSort()">
  <section class='container'>
    <?php
    $photo = "";

    $query = "SELECT alwayson, thumbpath, $media_table.mediaID AS mediaID, usecollfolder, mediatypeID, albumlinkID FROM ($media_table, $albumlinks_table)
      WHERE albumID = '$albumID' AND $media_table.mediaID = $albumlinks_table.mediaID AND defphoto = '1'";
    $defresult = tng_query($query);
    if ($defresult) {
      $drow = tng_fetch_assoc($defresult);
    }
    $thismediatypeID = $drow['mediatypeID'];
    $usefolder = $drow['usecollfolder'] ? $mediatypes_assoc[$thismediatypeID] : $mediapath;
    tng_free_result($defresult);

    $photoref = "$usefolder/" . $drow['thumbpath'];

    if ($drow['thumbpath'] && file_exists("$rootpath$photoref")) {
      $photoinfo = getimagesize("$rootpath$photoref");
      if ($photoinfo[1] <= $thumbmaxh) {
        $photohtouse = $photoinfo[1];
        $photowtouse = $photoinfo[0];
      } else {
        $photohtouse = $thumbmaxh;
        $photowtouse = intval($thumbmaxh * $photoinfo[0] / $photoinfo[1]);
      }
      $photo = "<img src=\"" . str_replace("%2F", "/", rawurlencode($photoref)) . "?" . time() . "\" alt='' width=\"$photowtouse\" height=\"$photohtouse\" style='margin-right: 10px; margin-bottom: 4px;'>";
    }
    ?>
    <?php
    echo $adminHeaderSection->build('albums-modifyalbum', $message);
    $navList = new navList('');
    $navList->appendItem([true, "albumsBrowse.php", uiTextSnippet('browse'), "findalbum"]);
    $navList->appendItem([$allowAdd, "albumsAdd.php", uiTextSnippet('add'), "addalbum"]);
    $navList->appendItem([$allowEdit, "albumsSort.php", uiTextSnippet('text_sort'), "sortalbums"]);
    $navList->appendItem([$allowEdit, "#", uiTextSnippet('edit'), "edit"]);
    echo $navList->build("edit");
    ?>
    <div class='small'>
      <a href='#' onClick="toggleAll('on');"><?php echo uiTextSnippet('expandall'); ?></a>
      <a href='#' onClick="toggleAll('off');"><?php echo uiTextSnippet('collapseall'); ?></a>
    </div>

    <form action="albumsEditFormAction.php" method='post' name='form1' id='form1' onSubmit="return validateForm();">
      <div>
        <div id="thumbholder" style="float: left; <?php if (!$photo) {echo "display: none";} ?>">
          <?php echo $photo; ?>
        </div>
        <span><?php echo $row['albumname'] . ": </span><br>" . $row['description']; ?>
      </div>
      <?php
      echo "<a class='small' id='removedefault' href='#' onclick=\"return removeDefault();\"";
      if (!$photo) {
        echo " style=\"visibility:hidden\"";
      }
      echo ">" . uiTextSnippet('removedef') . "</a>\n";
      ?>
      <section id='album-info'>
<!--        <?php echo displayToggle("plus0", 1, "details", uiTextSnippet('existingalbuminfo'), uiTextSnippet('infosubt')); ?>

        <div id="details">-->
          <div class='row'>
            <div class='col-sm-2'><?php echo uiTextSnippet('albumname'); ?>:</div>
            <div class='col-sm-10'>
              <input class='form-control' name='albumname' type='text' value="<?php echo $row['albumname']; ?>">
            </div>
          </div>
          <div class='row'>
            <div class='col-sm-2'><?php echo uiTextSnippet('description'); ?>:</div>
            <div class='col-sm-10'>
              <textarea class='form-control' name='description'><?php echo $row['description']; ?></textarea>
            </div>
          </div>
          <div class='row'>
            <div class='col-sm-2'><?php echo uiTextSnippet('keywords'); ?>:</div>
            <div class='col-sm-10'>
              <textarea class='form-control' name='keywords'><?php echo $row['keywords']; ?></textarea>
            </div>
          </div>
          <div class='row'>
            <div class='col-sm-2'><?php echo uiTextSnippet('active'); ?>:</div>
            <div class='col-sm-4'>
              <input class='form-control-inline' name='active' type='radio' value='1'<?php if ($row['active']) {echo " checked";} ?>> <?php echo uiTextSnippet('yes'); ?>
              <input class='form-control-inline' name='active' type='radio' value='0'<?php if (!$row['active']) {echo " checked";} ?>> <?php echo uiTextSnippet('no'); ?>
            </div>
            <div class='col-sm-6'>
              <div class='checkbox'>
              <label>
                <input name='alwayson' type='checkbox' value='1'<?php if ($row['alwayson']) {echo " checked";} ?>> 
                <?php echo uiTextSnippet('alwayson'); ?>
              </label>
              </div>
            </div>
          </div>
        <!--</div>-->
      </section> <!-- #album-info -->
      
      <table class='table table-sm'>
        
        <tr>
          <td>
            <?php echo displayToggle("plus1", 1, "addmedia", uiTextSnippet('albmedia') . " (<span id=\"mediacount\">$numrows</span>)", uiTextSnippet('mediasubt')); ?>

            <div id="addmedia">
              <p style="padding-top:12px">
                <input type='button' value="<?php echo uiTextSnippet('addmedia'); ?>"
                       onclick="return openAlbumMediaFind();"> <?php echo uiTextSnippet('selmedia') . " (<a href=\"admin_newmedia.php\" target='_blank'>" . uiTextSnippet('uploadfirst') . "</a>)"; ?>
              </p>

              <p>&nbsp;<strong><?php echo uiTextSnippet('inclmedia'); ?>
                  :</strong> <?php echo uiTextSnippet('emoptions'); ?></p>
              <table class="table" id="ordertbl">
                <tr>
                  <th style="width:102px"><?php echo uiTextSnippet('text_sort'); ?></th>
                  <th style="width:<?php echo($thumbmaxw + 10); ?>px"><?php echo uiTextSnippet('thumb'); ?></th>
                  <th><?php echo uiTextSnippet('description'); ?></th>
                  <th style="width:154px"><?php echo uiTextSnippet('date'); ?></th>
                  <th style="width:105px"><?php echo uiTextSnippet('mediatype'); ?></th>
                </tr>
              </table>

              <div id="orderdivs">
                <?php
                $count = 1;
                while ($lrow = tng_fetch_assoc($result2)) {
                  $lmediatypeID = $lrow['mediatypeID'];
                  $usefolder = $lrow['usecollfolder'] ? $mediatypes_assoc[$lmediatypeID] : $mediapath;

                  $truncated = substr($lrow['notes'], 0, 90);
                  $truncated = strlen($lrow['notes']) > 90 ? substr($truncated, 0, strrpos($truncated, ' ')) . '&hellip;' : $lrow['notes'];
                  echo "<div class=\"sortrow\" id=\"orderdivs_{$lrow['albumlinkID']}\" style=\"clear:both;position:relative\" onmouseover=\"$('#del_{$lrow['albumlinkID']}').css('visibility','visible');\" onmouseout=\"$('#del_{$lrow['albumlinkID']}').css('visibility','hidden');\">";
                  echo "<table width='100%'><tr>\n";
                  echo "<td class='dragarea'>";
                  echo "<img src='img/admArrowUp.gif' alt=''>" . uiTextSnippet('drag') . "\n";
                  echo "<img src='img/admArrowDown.gif' alt=''>\n";
                  echo "</td>\n";

                  echo "<td class=\"small\" style=\"width:35px;text-align:center\">";
                  echo "<div style=\"padding-bottom:5px\"><a href='#' onclick=\"return moveItemInList('{$lrow['albumlinkID']}',1);\" title=\"" .
                          uiTextSnippet('movetop') . "\"><img src=\"img/admArrowUp.gif\" alt=''><br>Top</a></div>\n";
                  echo "<input class='movefields' id=\"move{$lrow['albumlinkID']}\" name=\"move{$lrow['albumlinkID']}\" style='width: 30px' value=\"$count\" onkeypress=\"return handleMediaEnter('{$lrow['albumlinkID']}',$('#move{$lrow['albumlinkID']}').val(),event);\" />\n";
                  echo "<a href='#' onclick=\"return moveItemInList('{$lrow['albumlinkID']}',$('#move{$lrow['albumlinkID']}').val());\" title=\"" . uiTextSnippet('movetop') . "\">Go</a>\n";
                  echo "</td>\n";

                  echo "<td style=\"width:" . ($thumbmaxw + 6) . "px;text-align:center;\">";
                  if ($lrow['thumbpath'] && file_exists("$rootpath$usefolder/" . $lrow['thumbpath'])) {
                    $size = getimagesize("$rootpath$usefolder/" . $lrow['thumbpath']);
                    echo "<a href=\"admin_editmedia.php?mediaID={$lrow['mediaID']}\"><img src=\"$usefolder/" . str_replace("%2F", "/", rawurlencode($lrow['thumbpath'])) . "\" $size[3] alt=\"" . htmlentities($lrow['description'], ENT_QUOTES) . " \"></a>";
                    $foundthumb = true;
                  } else {
                    echo "&nbsp;";
                    $foundthumb = false;
                  }
                  echo "</td>\n";
                  $checked = $lrow['defphoto'] ? " checked" : "";
                  echo "<td><a href=\"admin_editmedia.php?mediaID={$lrow['mediaID']}\">{$lrow['description']}</a><br>" . strip_tags($truncated) . "<br>";
                  echo "<div id=\"del_{$lrow['albumlinkID']}\" class=\"small\" style=\"color:gray;visibility:hidden\">";
                  if ($foundthumb) {
                    echo "<input name='rthumbs' type='radio' value=\"r{$lrow['mediaID']}\"$checked onclick=\"makeDefault(this);\">" . uiTextSnippet('makedefault');
                    echo " &nbsp;|&nbsp; ";
                  }
                  echo "<a href='#' onclick=\"return removeFromAlbum('{$lrow['mediaID']}','{$lrow['albumlinkID']}');\">" . uiTextSnippet('remove') . "</a>";
                  echo "</div></td>\n";
                  echo "<td style=\"width:150px;\">{$lrow['datetaken']}&nbsp;</td>\n";
                  echo "<td style=\"width:100px;\">" . uiTextSnippet($lmediatypeID) . "&nbsp;</td>\n";
                  echo "</tr></table>";
                  echo "</div>\n";
                  $count++;
                }
                $numrows = tng_num_rows($result2);
                tng_free_result($result2);
                ?>
              </div>
              <div id="nomedia" style="margin-left:3px">
                <?php
                if (!$numrows) {
                  echo uiTextSnippet('nomedia');
                }
                ?>
              </div>
          </td>
        </tr>

        <tr>
          <td>
            <?php echo displayToggle("plus2", 1, "albumlinks", uiTextSnippet('albumlinks') . " (<span id=\"linkcount\">$numlinks</span>)", uiTextSnippet('linkssubt')); ?>

            <div id="albumlinks">
              <table style="padding-top:12px">
                <tr>
                  <td><?php echo uiTextSnippet('linktype'); ?></td>
                  <td colspan='2'><?php echo uiTextSnippet('id'); ?></td>
                </tr>
                <tr>
                  <td>
                    <select name="linktype1">
                      <option value='I'><?php echo uiTextSnippet('person'); ?></option>
                      <option value='F'><?php echo uiTextSnippet('family'); ?></option>
                      <option value='S'><?php echo uiTextSnippet('source'); ?></option>
                      <option value='R'><?php echo uiTextSnippet('repository'); ?></option>
                      <option value='L'><?php echo uiTextSnippet('place'); ?></option>
                    </select>
                  </td>
                  <td>
                    <input id='newlink1' name='newlink1' type='text' value=''
                             onkeypress="return newlinkEnter(document.form1, this, event);">
                  </td>
                  <!--<td>
                    <input type='submit' value="<?php echo uiTextSnippet('add'); ?>"> <?php echo uiTextSnippet('or'); ?>
                    <input name='find1' type='button' value="<?php echo uiTextSnippet('find'); ?>" onClick="findopen=true;openFind(document.find.linktype1.options['document.find.linktype1.selectedIndex'].value);$('newlines').innerHTML=resheremsg;">
                  </td>-->
                  <td>
                    <input type='button' value="<?php echo uiTextSnippet('add'); ?>"
                             onclick="return addMedia2EntityLink(document.form1);">
                    &nbsp;<?php echo uiTextSnippet('or'); ?>&nbsp;
                  </td>
                  <td>
                    <a href="#" title="<?php echo uiTextSnippet('find'); ?>" onclick="return findItem(findform.linktype1.options[findform.linktype1.selectedIndex].value, 'newlink1', null, '<?php echo $assignedbranch; ?>', 'a_<?php echo $albumID; ?>');">
                      <img class='icon-sm' src='svg/magnifying-glass.svg'>
                    </a>
                  </td>
                </tr>
              </table>
              <div id="alink_error" style="display:none;" class="red"></div>

              <p><strong><?php echo uiTextSnippet('existlinks'); ?>:</strong></p>
              <table class="table table-sm table-striped">
                <tbody id="linktable">
                <tr>
                  <th><?php echo uiTextSnippet('action'); ?></th>
                  <th><?php echo uiTextSnippet('linktype'); ?></th>
                  <th><?php echo uiTextSnippet('name') . ", " . uiTextSnippet('id'); ?></th>
                  <th><?php echo uiTextSnippet('event'); ?></th>
                </tr>
                <?php
                $oldlinks = 0;
                if ($result3) {
                  while ($plink = tng_fetch_assoc($result3)) {
                    $oldlinks++;
                    $plink['allow_living'] = 1;
                    if ($plink['personID'] != null) {
                      $type = "person";
                      $id = " (" . $plink['personID'] . ")";
                      $name = getName($plink);
                    } elseif ($plink['familyID'] != null) {
                      $type = "family";
                      $husb['firstname'] = $plink['hfirstname'];
                      $husb['lnprefix'] = $plink['hlnprefix'];
                      $husb['lastname'] = $plink['hlastname'];
                      $husb['prefix'] = $plink['hprefix'];
                      $husb['suffix'] = $plink['hsuffix'];
                      $husb['nameorder'] = $plink['hnameorder'];
                      $husb['allow_living'] = 1;
                      $wife['firstname'] = $plink['wfirstname'];
                      $wife['lnprefix'] = $plink['wlnprefix'];
                      $wife['lastname'] = $plink['wlastname'];
                      $wife['prefix'] = $plink['wprefix'];
                      $wife['suffix'] = $plink['wsuffix'];
                      $wife['nameorder'] = $plink['wnameorder'];
                      $wife['allow_living'] = 1;
                      $name = getName($husb);
                      $wifename = getName($wife);
                      if ($wifename) {
                        if ($name) {
                          $name .= ", ";
                        }
                        $name .= $wifename;
                      }
                      $id = " (" . $plink['familyID'] . ")";
                    } elseif ($plink['sourceID'] != null) {
                      $type = "source";
                      $id = " (" . $plink['sourceID'] . ")";
                      $name = substr($plink['title'], 0, 25);
                    } elseif ($plink['repoID'] != null) {
                      $type = "repository";
                      $id = " (" . $plink['repoID'] . ")";
                      $name = substr($plink['reponame'], 0, 25);
                    } else { //place
                      $type = "place";
                      $id = "";
                      $name = $plink['entityID'];
                    }

                    include 'eventmicro.php';

                    echo "<tr id=\"alink_{$plink['alinkID']}\"><td>\n";
                    if ($type != "place") {
                      echo "<a href='#' onclick=\"return editMedia2EntityLink({$plink['alinkID']});\" title='" . uiTextSnippet('edit') . "'>\n";
                      echo "<img class='icon-sm' src='svg/new-message.svg'>\n";
                      echo "</a>\n";
                    }
                    echo "<a href='#' onclick=\"return deleteMedia2EntityLink({$plink['alinkID']});\" title='" . uiTextSnippet('removelink') . "'>\n";
                    echo "<img class='icon-sm' src='svg/link.svg'>\n";
                    echo "</a>\n";
                    echo "</td>\n";
                    echo "<td>" . uiTextSnippet($type) . "</td>\n";
                    echo "<td>$name$id</td>\n";
                    echo "<td id=\"event_{$plink['alinkID']}\">$eventstr&nbsp;</td>\n";
                    echo "</tr>\n";
                  }
                  tng_free_result($result3);
                }
                ?>
                </tbody>
              </table>
              <div id="nolinks" style="margin-left:3px">
                <?php
                if (!$oldlinks) {
                  echo uiTextSnippet('nolinks');
                }
                ?>
              </div>
            </div>
          </td>
        </tr>

        <tr>
          <td>
            <p>
              <?php
              echo uiTextSnippet('onsave') . ":<br>";
              echo "<input name='newscreen' type='radio' value='return'> " . uiTextSnippet('savereturn') . "<br>\n";
              if ($cw) {
                echo "<input name='newscreen' type='radio' value='close' checked> " . uiTextSnippet('closewindow') . "\n";
              } else {
                echo "<input name='newscreen' type='radio' value='none' checked> " . uiTextSnippet('saveback') . "\n";
              }
              ?>
            </p>

            <input name='cw' type='hidden' value="<?php echo "$cw"; ?>">
            <input name='albumID' type='hidden' value="<?php echo "$albumID"; ?>">
            <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
          </td>
        </tr>
      </table>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src='js/mediafind.js'></script>
<script src='js/mediautils.js'></script>
<script src='js/selectutils.js'></script>
<script src='js/admin.js'></script>
<script>
  var tnglitbox;
  var album = "<?php echo $albumID; ?>";
  var entity = "";
  var tree = "";
  var type = "album";
  var thumbmaxw = parseInt("<?php echo $thumbmaxw; ?>");
  var remove_text = "<?php echo uiTextSnippet('remove'); ?>";
  var mediacount = <?php echo $numrows; ?>;
  var linkcount = <?php echo $numlinks; ?>;
  var findopen;
  var orderaction = "order";

  function toggleAll(display) {
    toggleSection('addmedia', 'plus1', display);
    toggleSection('albumlinks', 'plus2', display);
    return false;
  }
</script>
<script src='js/albums.js'></script>
<script>
  var findform = document.form1;
</script>
</body>
</html>