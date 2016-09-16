<?php
require 'begin.php';
require $subroot . 'mapconfig.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowMediaEdit && (!$allowMediaAdd || !$added)) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
require 'showmedialib.php';

$query = "SELECT *, DATE_FORMAT(changedate,\"%d %b %Y %H:%i:%s\") AS changedate FROM $media_table WHERE mediaID = \"$mediaID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
$row['description'] = preg_replace('/\"/', '&#34;', $row['description']);
$row['notes'] = preg_replace('/\"/', '&#34;', $row['notes']);
$row['datetaken'] = preg_replace('/\"/', '&#34;', $row['datetaken']);
$row['placetaken'] = preg_replace('/\"/', '&#34;', $row['placetaken']);
$row['owner'] = preg_replace('/\"/', '&#34;', $row['owner']);
$row['map'] = preg_replace('/\"/', '&#34;', $row['map']);
$row['map'] = preg_replace('/>/', '&gt;', $row['map']);
$row['map'] = preg_replace('/</', '&lt;', $row['map']);

if ($row['usenl']) {
  $row['bodytext'] = nl2br($row['bodytext']);
}
if ($row['abspath']) {
  $row['path'] = preg_replace('/&/', '&amp;', $row['path']);
}
tng_free_result($result);

$mediatypeID = $row['mediatypeID'];
$path = stripslashes($path);
$thumbpath = stripslashes($thumbpath);
if ($row['form']) {
  $form = strtoupper($row['form']);
} else {
  preg_match('/\.(.+)$/', $row['path'], $matches);
  $form = strtoupper($matches[1]);
}
$treequery = "SELECT gedcom FROM $treesTable";
$treeresult = tng_query($treequery) or die(uiTextSnippet('cannotexecutequery') . ": $treequery");
$treenum = 0;

while ($treerow = tng_fetch_assoc($treeresult)) {
  $treenum++;
  $trees[$treenum] = $treerow['gedcom'];
  $treename[$treenum] = $treerow['treename'];
}
tng_free_result($treeresult);

$query = "SELECT $medialinks_table.medialinkID AS mlinkID, $medialinks_table.personID AS personID, eventID, people.lastname AS lastname, people.lnprefix AS lnprefix, people.firstname AS firstname, people.prefix AS prefix, people.suffix AS suffix, people.nameorder AS nameorder, altdescription, altnotes, people.branch AS branch, familyID, people.personID AS personID2, wifepeople.personID AS wpersonID, wifepeople.firstname AS wfirstname, wifepeople.lnprefix AS wlnprefix, wifepeople.lastname AS wlastname, wifepeople.prefix AS wprefix, wifepeople.suffix AS wsuffix, wifepeople.nameorder AS wnameorder, husbpeople.personID AS hpersonID, husbpeople.firstname AS hfirstname, husbpeople.lnprefix AS hlnprefix, husbpeople.lastname AS hlastname, husbpeople.prefix AS hprefix, husbpeople.suffix AS hsuffix, husbpeople.nameorder AS hnameorder, sourceID, sources.title, repositories.repoID AS repoID, reponame, defphoto, linktype, dontshow, people.living, people.private, $families_table.living AS fliving, $families_table.private AS fprivate FROM $medialinks_table LEFT JOIN $people_table AS people ON $medialinks_table.personID = people.personID LEFT JOIN $families_table ON $medialinks_table.personID = $families_table.familyID LEFT JOIN $sources_table AS sources ON $medialinks_table.personID = sources.sourceID LEFT JOIN $repositories_table AS repositories ON $medialinks_table.personID = repositories.repoID LEFT JOIN $people_table AS husbpeople ON $families_table.husband = husbpeople.personID LEFT JOIN $people_table AS wifepeople ON $families_table.wife = wifepeople.personID WHERE mediaID = '$mediaID' ORDER BY $medialinks_table.medialinkID DESC";
$result2 = tng_query($query);
$numlinks = tng_num_rows($result2);

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('modifymedia'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body <?php echo "$onload"; ?>>
  <section class='container'>
    <?php
    $standardtypes = [];
    $moptions = '';
    $likearray = "var like = new Array();\n";
    foreach ($mediatypes as $mediatype) {
      if (!$mediatype['type']) {
        $standardtypes[] = '"' . $mediatype['ID'] . '"';
      }
      $msgID = $mediatype['ID'];
      $moptions .= "  <option value=\"$msgID\"";
      if ($msgID == $mediatypeID) {
        $moptions .= ' selected';
      }
      $moptions .= '>' . $mediatype['display'] . "</option>\n";
      $likearray .= "like['$msgID'] = '{$mediatype['liketype']}';\n";
    }
    $sttypestr = implode(',', $standardtypes);

    $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mediatypeID] : $mediapath;

    if ($row['thumbpath'] && file_exists("$rootpath$usefolder/" . $row['thumbpath'])) {
      $photoinfo = getimagesize("$rootpath$usefolder/" . $row['thumbpath']);
      if ($photoinfo[1] < 50) {
        $photohtouse = $photoinfo[1];
        $photowtouse = $photoinfo[0];
      } else {
        $photohtouse = 50;
        $photowtouse = intval(50 * $photoinfo[0] / $photoinfo[1]);
      }
      $photo = "<img src=\"$usefolder/" . str_replace('%2F', '/', rawurlencode($row['thumbpath'])) . "\" width=\"$photowtouse\" height=\"$photohtouse\" style=\"border-color:#000000;margin-right:6px\"></span>\n";
    } else {
      $photo = '';
    }
    if ($row['path'] && ($form == 'JPG' || $form == 'JPEG' || $form == 'GIF' || $form == 'PNG')) {
      $size = getimagesize("$rootpath$usefolder/" . $row['path']);
      $isphoto = true;
    } else {
      $size = '';
      $isphoto = false;
    }
    if ($map['key']) {
    ?>
      <script src='https://maps.googleapis.com/maps/api/js?key=AIzaSyAlWTL2QZDQv9BWXBvCwdAuhq1Lak8jSwU&amp;<?php echo uiTextSnippet('localize'); ?>'></script>
    <?php }
    $onload = $onunload = '';
    if ($isphoto && !$row['abspath']) {
      $onload = 'init();';
    }
    $placeopen = 0;
    if ($map['key']) {
      include 'googlemaplib2.php';
      if (!$map['startoff']) {
        $onload .= "divbox('mapcontainer');";
        $placeopen = 1;
      }
    }
    if ($onload) {
      $onload = "onload=\"$onload\"";
    }
    ?>

    <?php
    echo $adminHeaderSection->build('media-existingmediainfo', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'mediaBrowse.php', uiTextSnippet('browse'), 'findmedia']);
    $navList->appendItem([$allowMediaAdd, 'mediaAdd.php', uiTextSnippet('add'), 'addmedia']);
    $navList->appendItem([$allowMediaEdit, 'mediaSort.php', uiTextSnippet('text_sort'), 'sortmedia']);
    $navList->appendItem([$allowMediaEdit, 'mediaThumbnails.php', uiTextSnippet('thumbnails'), 'thumbs']);
    $navList->appendItem([$allowMediaAdd, 'mediaImport.php', uiTextSnippet('import'), 'import']);
    $navList->appendItem([$allowMediaAdd, 'mediaUpload.php', uiTextSnippet('upload'), 'upload']);
    $navList->appendItem([$allowMediaEdit, '#', uiTextSnippet('edit'), 'edit']);
    echo $navList->build('edit');
    ?>
    <br>
    <a href="showmedia.php?mediaID=<?php echo $mediaID; ?>" title='<?php echo uiTextSnippet('preview') ?>'>
      <img class='icon-sm' src='svg/eye.svg'>
    </a>

    <div class='small'>
      <a href='#' onClick="toggleAll('on');"><?php echo uiTextSnippet('expandall'); ?></a>
      <a href='#' onClick="toggleAll('off');"><?php echo uiTextSnippet('collapseall'); ?></a>
    </div>
    <form action="mediaEditFormAction.php" method='post' name='form1' id='form1' ENCTYPE="multipart/form-data"
          onsubmit="return validateForm();">
      <table class="table table-sm">
        <tr>
          <td>
            <table class='table table-sm'>
              <tr>
                <td>
                  <div id="thumbholder" style="margin-right: 5px; <?php if (!$photo) {echo 'display: none';} ?>">
                    <?php echo $photo; ?>
                  </div>
                </td>
                <td>
                  <h2><?php echo $row['description']; ?></h2><br>
                  <?php echo $row['notes']; ?>
                  <p class="smallest"><?php echo uiTextSnippet('lastmodified') . ': ' . $row['changedate'] . ($row['changedby'] ? " ({$row['changedby']})" : ''); ?></p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
            <?php echo displayToggle('plus0', 1, 'mediafile', uiTextSnippet('imagefile'), uiTextSnippet('uplsel')); ?>
            <div id="mediafile">
              <br>
              <table class='table table-sm'>
                <tr>
                  <td><?php echo uiTextSnippet('mediatype'); ?>:</td>
                  <td>
                    <select name="mediatypeID" onchange="switchOnType(this.options[this.selectedIndex].value)">
                      <?php
                      foreach ($mediatypes as $mediatype) {
                        $msgID = $mediatype['ID'];
                        echo "  <option value=\"$msgID\"";
                        if ($msgID == $mediatypeID) {
                          echo ' selected';
                        }
                        echo '>' . $mediatype['display'] . "</option>\n";
                      }
                      ?>
                    </select>
                    <?php if ($allowAdd && $allowEdit && $allowDelete) { ?>
                      <input name='addnewmediatype' type='button' value="<?php echo uiTextSnippet('addnewcoll'); ?>"
                             onclick="tnglitbox = new ModalDialog('admin_newcollection.php?field=mediatypeID');">
                      <input id='editmediatype' name='editmediatype' type='button' value="<?php echo uiTextSnippet('edit'); ?>"
                             style="display:none"
                             onclick="editMediatype(document.form1.mediatypeID);">
                      <input id='delmediatype' name='delmediatype' type='button' value="<?php echo uiTextSnippet('delete'); ?>"
                             style="display:none"
                             onclick="confirmDeleteMediatype(document.form1.mediatypeID);">
                    <?php } ?>
                  </td>
                </tr>
                <tr>
                  <td colspan='2'>
                    <input name='abspath' type='checkbox' value='1'<?php if ($row[abspath]) {echo ' checked';} ?>
                           onClick="toggleMediaURL();"><span> <?php echo uiTextSnippet('abspath'); ?></span>
                  </td>
                </tr>
                <tr>
                  <td colspan='2'><strong><br><?php echo uiTextSnippet('imagefile'); ?></strong></td>
                </tr>
                <tr id="imgrow">
                  <td><?php echo uiTextSnippet('imagefiletoupload'); ?>*:</td>
                  <td>
                    <input name='newfile' type='file' onChange="populatePath(document.form1.newfile, document.form1.path);">
                  </td>
                </tr>
                <tr id="pathrow">
                  <td><?php echo uiTextSnippet('pathwithinphotos'); ?>**:</td>
                  <td>
                    <input id='path' name='path' type='text' value="<?php if (!$row['abspath']) {echo "$row[path]"; } ?>" size='60'>
                    <input id='path_org' name='path_org' type='hidden' value="<?php if (!$row['abspath']) {echo "$row[path]";} ?>">
                    <input id='path_last' name='path_last' type='hidden'>
                    <input name='photoselect' type='button' value="<?php echo uiTextSnippet('select') . '...'; ?>"
                           onclick="javascript:FilePicker('path', document.form1.mediatypeID.options[document.form1.mediatypeID.selectedIndex].value);">
                  </td>
                </tr>
                <tr id="abspathrow" style="display:none">
                  <td><?php echo uiTextSnippet('mediaurl'); ?>:</td>
                  <td>
                    <input name='mediaurl' type='text' value="<?php if ($row['abspath']) {echo "$row[path]";} ?>" size='60'>
                  </td>
                </tr>

                <!-- history section -->
                <tr id="bodytextrow">
                  <td><?php echo uiTextSnippet('bodytext'); ?>:</td>
                  <td>
                    <textarea wrap='soft' cols="100" rows="11" name="bodytext" id="bodytext"><?php echo $row['bodytext']; ?></textarea>
                  </td>
                </tr>
                <?php if (function_exists('imageJpeg')) { ?>
                  <tr>
                    <td><strong><br><?php echo uiTextSnippet('thumbnailfile'); ?></strong></td>
                    <td><br>
                      <input name='thumbcreate' type='radio' value='specify' checked
                             onClick="document.form1.newthumb.style.visibility = 'visible'; document.form1.thumbselect.style.visibility = 'visible';"> <?php echo uiTextSnippet('specifyimg'); ?>
                      &nbsp;
                      <input name='thumbcreate' type='radio' value='auto'
                             onClick="document.form1.newthumb.style.visibility = 'hidden'; document.form1.thumbselect.style.visibility = 'hidden'; prepopulateThumb(); document.form1.abspath.checked = false;"> <?php echo uiTextSnippet('autoimg'); ?>
                    </td>
                  </tr>
                <?php } else { ?>
                  <tr>
                    <td colspan='2'><strong><br><?php echo uiTextSnippet('thumbnailfile'); ?></strong></td>
                  </tr>
                <?php } ?>
                <tr>
                  <td><?php echo uiTextSnippet('imagefiletoupload'); ?>*:</td>
                  <td>
                    <input name='newthumb' type="file" onChange="populatePath(document.form1.newthumb, document.form1.thumbpath);">
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('pathwithinphotos'); ?>**:</td>
                  <td>
                    <input id='thumbpath' name='thumbpath' type='text' value="<?php echo $row['thumbpath']; ?>" size='60'>
                    <input id='thumbpath_org' name='thumbpath_org' type='hidden' value="<?php if (!$row['abspath']) {echo "$row[thumbpath]";} ?>">
                    <input id='thumbpath_last' name='thumbpath_last' type='hidden'>
                    <input name='thumbselect' type='button' value="<?php echo uiTextSnippet('select') . '...'; ?>"
                           onClick="javascript:FilePicker('thumbpath', document.form1.mediatypeID.options[document.form1.mediatypeID.selectedIndex].value);">
                  </td>
                </tr>
                <tr>
                  <td><strong><br><?php echo uiTextSnippet('put_in'); ?></strong></td>
                  <td><br>
                    <input name='usecollfolder' type='radio' value='0'<?php if (!$row['usecollfolder']) {echo ' checked';} ?>> <?php echo uiTextSnippet('usemedia'); ?>&nbsp;
                    <input name='usecollfolder' type='radio' value='1'<?php if ($row['usecollfolder']) {echo ' checked';} ?>> <?php echo uiTextSnippet('usecollect'); ?>
                  </td>
                </tr>
                <tr id="vidrow1">
                  <td><?php echo uiTextSnippet('width'); ?>:</td>
                  <td><input name='width' type='text' value="<?php echo $row['width']; ?>" size='40'></td>
                </tr>
                <tr id="vidrow2">
                  <td><?php echo uiTextSnippet('height'); ?>:</td>
                  <td>
                    <input name='height' type='text' value="<?php echo $row['height']; ?>" size='40'><span> (<?php echo uiTextSnippet('controller'); ?>)</span>
                  </td>
                </tr>
              </table>
              <p class="small">
                <?php
                echo '*' . uiTextSnippet('leaveblankphoto') . "<br>\n";
                echo '**' . uiTextSnippet('requiredphoto') . "<br>\n";
                ?>
              </p>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <?php echo displayToggle('plus1', 1, 'details', uiTextSnippet('newmediainfo'), uiTextSnippet('minfosubt')); ?>
            <div id="details">
              <br>
              <table class='table table-sm'>
                <tr>
                  <td><?php echo uiTextSnippet('title'); ?>:</td>
                  <td>
                    <textarea wrap='soft' cols="70" rows='3' name='description'><?php echo $row['description']; ?></textarea>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('description'); ?>:</td>
                  <td>
                    <textarea wrap='soft' cols="70" rows='5' name="notes"><?php echo $row['notes']; ?></textarea>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('photoowner'); ?>:</td>
                  <td>
                    <input name='owner' type='text' value="<?php echo $row['owner']; ?>" size='40'>
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('datetaken'); ?>:</td>
                  <td>
                    <input name='datetaken' type='text' value="<?php echo $row['datetaken']; ?>" size='40' onblur="checkDate(this);">
                  </td>
                </tr>
                
                <!-- headstone section -->
                <tr id="cemrow">
                  <td><?php echo uiTextSnippet('cemetery'); ?>:</td>
                  <td>
                    <div id="cemchoice"<?php if ($row['cemeteryID'] || $mediatypeID == 'headstones') {echo " style='display: none'";} ?>>
                      <a href="#" onclick="return toggleCemSelect();"><?php echo uiTextSnippet('select'); ?></a>
                    </div>
                    <div id="cemselect"<?php if (!$row['cemeteryID'] && $mediatypeID != 'headstones') {echo " style='display: none'";} ?>>
                      <select name="cemeteryID">
                        <option selected></option>
                        <?php
                        $query = "SELECT cemname, cemeteryID, city, county, state, country FROM $cemeteries_table ORDER BY country, state, county, city, cemname";
                        $cemresult = tng_query($query);
                        while ($cemrow = tng_fetch_assoc($cemresult)) {
                          $cemetery = "{$cemrow['country']}, {$cemrow['state']}, {$cemrow['county']}, {$cemrow['city']}, {$cemrow['cemname']}";
                          echo "    <option value=\"{$cemrow['cemeteryID']}\"";
                          if ($row['cemeteryID'] == $cemrow['cemeteryID']) {
                            echo ' selected';
                          }
                          echo ">$cemetery</option>\n";
                        }
                        ?>
                      </select>
                    </div>
                  </td>
                </tr>
                <tr id="hsplotrow">
                  <td><?php echo uiTextSnippet('plot'); ?>:</td>
                  <td>
                    <textarea wrap='soft' cols="70" rows='2' name="plot"><?php echo $row['plot']; ?></textarea>
                  </td>
                </tr>
                <tr id="hsstatrow">
                  <td><?php echo uiTextSnippet('status'); ?>:</td>
                  <td>
                    <select name="status">
                      <option value=''>&nbsp;</option>
                      <option value="notyetlocated"<?php if ($row['status'] == 'notyetlocated') {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('notyetlocated'); ?>
                      </option>
                      <option value="located"<?php if ($row['status'] == 'located') {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('located'); ?>
                      </option>
                      <option value="unmarked"<?php if ($row['status'] == 'unmarked') {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('unmarked'); ?>
                      </option>
                      <option value="missing"<?php if ($row['status'] == 'missing') {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('missing'); ?>
                      </option>
                      <option value="cremated"<?php if ($row['status'] == 'cremated') {echo ' selected';} ?>>
                        <?php echo uiTextSnippet('cremated'); ?>
                      </option>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td colspan='2'>
                    <input name='alwayson' type='checkbox' value='1'<?php if ($row['alwayson']) {echo ' checked';} ?>> <?php echo uiTextSnippet('alwayson'); ?>
                  </td>
                </tr>
                <!-- history section -->
                <tr id="newwinrow">
                  <td colspan='2'>
                    <input name='newwindow' type='checkbox' value='1'<?php if ($row['newwindow']) {echo ' checked';} ?>> <?php echo uiTextSnippet('newwin'); ?>
                  </td>
                </tr>
                <!-- headstone section -->
                <tr id="linktocemrow">
                  <td colspan='2'>
                    <input name='linktocem' type='checkbox' value='1'<?php if ($row['linktocem']) {echo ' checked';} ?>> <?php echo uiTextSnippet('linktocem'); ?>
                  </td>
                </tr>
                <tr id="maprow">
                  <td colspan='2'>
                    <input name='showmap' type='checkbox' value='1'<?php if ($row['showmap']) {echo ' checked';} ?>> <?php echo uiTextSnippet('showmap'); ?>
                  </td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
        <tr>
          <td id="linkstd">
            <?php echo displayToggle('plus2', 1, 'links', uiTextSnippet('medialinks') . " (<span id=\"linkcount\">$numlinks</span>)", uiTextSnippet('linkssubt')); ?>
            <?php require 'micro_medialinks.php'; ?>
          </td>
        </tr>
        <tr>
          <td>
            <?php echo displayToggle('plus3', $placeopen, 'placeinfo', uiTextSnippet('placetaken'), ''); ?>
            <div id="placeinfo"<?php if (!$placeopen) {echo " style='display: none'";} ?>>
              <table class='table table-sm'>
                <tr>
                  <td width="150"><?php echo uiTextSnippet('placetaken'); ?>:</td>
                  <td>
                    <input id='place' name='place' type='text' value="<?php echo $row['placetaken']; ?>" size='40' style="float:left">
                    <a class='dn2px' href="#" onclick="return openFindPlaceForm('place');" title="<?php echo uiTextSnippet('find'); ?>">
                      <img class='icon-sm' src='svg/magnifying-glass.svg'>
                    </a>
                  </td>
                </tr>
                <?php if ($map['key']) { ?>
                  <tr>
                    <td colspan='2'>
                      <div style="padding:0 10px 10px 0">
                        <?php include 'googlemapdrawthemap.php'; ?>
                      </div>
                    </td>
                  </tr>
                <?php } ?>
                <tr>
                  <td><?php echo uiTextSnippet('latitude'); ?>:</td>
                  <td>
                    <input id='latbox' name='latitude' type='text' value="<?php echo $row['latitude']; ?>">
                  </td>
                </tr>
                <tr>
                  <td><?php echo uiTextSnippet('longitude'); ?>:</td>
                  <td>
                    <input id='lonbox' name='longitude' type='text' value="<?php echo $row['longitude']; ?>">
                  </td>
                </tr>
                <?php if ($map['key']) { ?>
                  <tr>
                    <td><?php echo uiTextSnippet('zoom'); ?>:</td>
                    <td>
                      <input id='zoombox' name='zoom' type='text' value="<?php echo $row['zoom']; ?>">
                    </td>
                  </tr>
                <?php } ?>
              </table>
            </div>
          </td>
        </tr>
        <?php if ($isphoto && !$row['abspath']) { ?>
          <tr>
            <td>
              <?php echo displayToggle('plus4', 0, 'imagemapdiv', uiTextSnippet('imgmap'), uiTextSnippet('mapinstr2')); ?>
              <div id="imagemapdiv" style="display:none">
                <br>
                <p><?php echo uiTextSnippet('mapinstr3'); ?></p>
                <?php echo '<strong>' . uiTextSnippet('forrects') . ':</strong><br>' . uiTextSnippet('rectinstr'); ?>
                <br>
                <?php
                $width = $size[0];
                $height = $size[1];
                if ($width && $height) {
                  if ($tngconfig['imgmaxw'] && ($width > $tngconfig['imgmaxw'])) {
                    $width = $tngconfig['imgmaxw'];
                    $height = intval($width * $size[1] / $size[0]);
                  }
                  if ($tngconfig['imgmaxh'] && ($height > $tngconfig['imgmaxh'])) {
                    $height = $tngconfig['imgmaxh'];
                    $width = intval($height * $size[0] / $size[1]);
                  }
                }
                $widthstr = "width = \"$width\"";
                $heightstr = "height=\"$height\"";
                echo uiTextSnippet('imgdim') . ": $width " . uiTextSnippet('pixw') . " x $height " . uiTextSnippet('pixh');
                ?>
                <br>
                <div id="imgholder" style="position:relative">
                  <img id="myimg"
                       src="<?php echo "$usefolder/" . str_replace('%2F', '/', rawurlencode($row['path'])); ?>" <?php echo "$widthstr $heightstr"; ?>
                       alt="<?php echo uiTextSnippet('circleinstr'); ?>"
                       style="cursor:crosshair;">
                </div>
                <p><?php echo uiTextSnippet('imgmap'); ?>:
                  <br>
                  <textarea cols="80" rows='4' name="imagemap" id="imagemap"><?php echo $row['map']; ?></textarea>
                </p>
              </div>
            </td>
          </tr>
          <?php
        } //end abspath condition
        ?>
        <tr>
          <td>
            <p>
              <?php
              echo uiTextSnippet('onsave') . ':<br>';
              echo "<input name='newmedia' type='radio' value='return'> " . uiTextSnippet('savereturn') . "<br>\n";
              if ($cw) {
                echo "<input name='newmedia' type='radio' value='close' checked> " . uiTextSnippet('closewindow') . "\n";
              } else {
                echo "<input name='newmedia' type='radio' value='none' checked> " . uiTextSnippet('saveback') . "\n";
              }
              ?>
            </p>
            <input name='usenl' type='hidden' value='0'>
            <input name='mediatypeID_org' type='hidden' value="<?php echo "$mediatypeID"; ?>"/>
            <input name='mediaID' type='hidden' value="<?php echo "$mediaID"; ?>"/>
            <input name='mediakey_org' type='hidden' value="<?php echo $row['mediakey']; ?>"/>
            <input name='cw' type='hidden' value="<?php echo "$cw"; ?>">
            <input name='fsubmit' type='submit' value="<?php echo uiTextSnippet('save'); ?>"/>
          </td>
        </tr>
      </table>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/admin.js"></script>
<script>
  var tree = '',
    type = "media",
    treename = new Array(),
    seclitbox,
    tnglitbox;

  var media = '<?php echo $mediaID; ?>';
  var thumbPrefix = '<?php echo $thumbprefix; ?>';
  var thumbSuffix = '<?php echo $thumbsuffix; ?>';
  var treemsg = '<?php echo uiTextSnippet('tree'); ?>';
  var remove_text = '<?php echo uiTextSnippet('removelink'); ?>';
  var linkcount = <?php echo $numlinks; ?>;
  var manage = 0;
  var assignedbranch = '<?php echo $assignedbranch; ?>';

  <?php echo $likearray; ?>

  var stmediatypes = new Array(<?php echo $sttypestr; ?>);
  var allow_edit = <?php echo($allowEdit ? '1' : '0'); ?>;
  var allow_delete = <?php echo($allowDelete ? '1' : '0'); ?>;

  function validateForm() {
    var rval = true;

    var frm = document.form1;
    if (frm.path.value.length === 0 && frm.mediaurl.value.length === 0 && frm.bodytext.value.length === 0 && frm.mediatypeID.options[frm.mediatypeID.selectedIndex].value !== 'headstones') {
      alert(textSnippet('enterphotopath'));
      rval = false;
    } else if (frm.thumbpath.value.length === 0 && frm.thumbcreate[1].checked === true) {
      alert(textSnippet('enterthumbpath'));
      rval = false;
    } else if (frm.thumbpath.value.length > 0 && frm.path.value === frm.thumbpath.value) {
      alert(textSnippet('samepaths'));
      rval = false;
    } else {
      frm.path.value = frm.path.value.replace(/\\/g, "/");
      frm.thumbpath.value = frm.thumbpath.value.replace(/\\/g, "/");
    }
    if (rval && frm.newfile.value) {
      rval = false;
      var usecollfolder = frm.usecollfolder[0].checked ? 0 : 1;
      var mediatypeID = frm.mediatypeID.options[frm.mediatypeID.selectedIndex].value;
      var params = {path: frm.path.value, usecollfolder: usecollfolder, mediatypeID: mediatypeID};
      $.ajax({
        url: 'admin_checkfile.php',
        data: params,
        dataType: 'html',
        success: function (req) {
          if (req === "false" || confirm(textSnippet('fileexists')))
            document.form1.submit();
        }
      });
    }
    return rval;
  }

  function toggleAll(display) {
    toggleSection('mediafile', 'plus0', display);
    toggleSection('details', 'plus1', display);
    toggleSection('links', 'plus2', display);
    toggleSection('placeinfo', 'plus3', display);
    if ($('#imagemapdiv').length)
      toggleSection('imagemapdiv', 'plus4', display);
    return false;
  }
</script>
<script src='js/mediautils.js'></script>
<script src='js/mediafind.js'></script>
<script src='js/selectutils.js'></script>
<script src='js/datevalidation.js'></script>
<script>
  var preferEuro = <?php echo($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : 'false'); ?>;
  var preferDateFormat = '<?php echo $preferDateFormat; ?>';
  switchOnType(document.form1.mediatypeID.options[document.form1.mediatypeID.selectedIndex].value);
  toggleMediaURL();
  var findform = document.form1;
</script>
<script src="js/nicedit.js"></script>
<script>
  bkLib.onDomLoaded(function () {
    new nicEditor({fullPanel: true}).panelInstance('bodytext');
    <?php
    if ($isphoto && !$row['abspath']) {
      echo "init();\n";
    }
    if ($map['key'] && !$map['startoff']) {
      echo "divbox('mapcontainer');\n";
    }
    if ($added) {
      echo "toggleSection('mediafile','plus0');\n";
      echo "toggleSection('details','plus1');\n";
    }
    ?>
  });

  var box;
  var x1, y1;

  $(document).ready(function () {

    $('#myimg').mousedown(function (e) {
      e.preventDefault();
      if ($("#mlbox").length)
        var a = 1;//$("#mlbox").attr({ id: '' });
      else {
        $('.bselected').removeClass('bselected').addClass('bunselected');

        box = $('<div id="mlbox" class="mlbox bunselected">').hide();

        $('#imgholder').append(box);

        x1 = e.pageX;
        y1 = e.pageY;

        box.css({
          top: e.pageY - $(e.target).offset().top - 1, //offsets
          left: e.pageX - $(e.target).offset().left - 1 //offsets
        }).fadeIn();
      }
    });

    $('#myimg').mousemove(function (e) {

      e.preventDefault();
      $("#mlbox").css({
        width: Math.abs(e.pageX - x1 - 1), //offsets
        height: Math.abs(e.pageY - y1 - 1) //offsets
      }).fadeIn();
    });

    $('#myimg').mouseup(function () {
      findItem('I', 'imagemap', '', assignedbranch);
      $("#current").attr({id: ''});
    });
  });
</script>
</body>
</html>
