<?php
require 'begin.php';
require $subroot . 'mapconfig.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

$maxnoteprev = 350;    //don't use the global value here because we always want to truncate

if ($newsearch) {
  $exptime = 0;
  $searchstring = stripslashes(trim($searchstring));
  setcookie('tng_search_media_post[search]', $searchstring, $exptime);
  setcookie('tng_search_media_post[mediatypeID]', $mediatypeID, $exptime);
  setcookie('tng_search_media_post[fileext]', $fileext, $exptime);
  setcookie('tng_search_media_post[unlinked]', $unlinked, $exptime);
  setcookie('tng_search_media_post[hsstat]', $hsstat, $exptime);
  setcookie('tng_search_media_post[cemeteryID]', $cemeteryID, $exptime);
  setcookie('tng_search_media_post[tngpage]', 1, $exptime);
  setcookie('tng_search_media_post[offset]', 0, $exptime);
} else {
  if (!$searchstring) {
    $searchstring = stripslashes($_COOKIE['tng_search_media_post']['search']);
  }
  if (!$mediatypeID) {
    $mediatypeID = $_COOKIE['tng_search_media_post']['mediatypeID'];
  }
  if (!$fileext) {
    $fileext = $_COOKIE['tng_search_media_post']['fileext'];
  }
  if (!$unlinked) {
    $unlinked = $_COOKIE['tng_search_media_post']['unlinked'];
  }
  if (!$hsstat) {
    $hsstat = $_COOKIE['tng_search_media_post']['hsstat'];
  }
  if (!$cemeteryID) {
    $cemeteryID = $_COOKIE['tng_search_media_post']['cemeteryID'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_media_post']['tngpage'];
    $offset = $_COOKIE['tng_search_media_post']['offset'];
  } else {
    $exptime = 0;
    setcookie('tng_search_media_post[tngpage]', $tngpage, $exptime);
    setcookie('tng_search_media_post[offset]', $offset, $exptime);
  }
}
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = '';
  $tngpage = 1;
}
$orgwherestr = '';

$originalstring = preg_replace('/\"/', '&#34;', $searchstring);
$searchstring = addslashes($searchstring);
$wherestr = $searchstring ? "(media.mediaID LIKE \"%$searchstring%\" OR description LIKE \"%$searchstring%\" OR path LIKE \"%$searchstring%\" OR notes LIKE \"%$searchstring%\" OR bodytext LIKE \"%$searchstring%\")" : '';

if ($mediatypeID) {
  $wherestr .= $wherestr ? " AND mediatypeID = \"$mediatypeID\"" : "mediatypeID = \"$mediatypeID\"";
}
if ($fileext) {
  $wherestr .= $wherestr ? " AND form = \"$fileext\"" : "form = \"$fileext\"";
}
if ($hsstat != 'all') {
  if ($hsstat) {
    $wherestr .= $wherestr ? " AND status = \"$hsstat\"" : "status = \"$hsstat\"";
  } else {
    $wherestr .= $wherestr ? " AND (status = \"$hsstat\" OR status IS NULL)" : "(status = \"$hsstat\" OR status IS NULL)";
  }
}
if ($cemeteryID) {
  $wherestr .= $wherestr ? " AND cemeteryID = '$cemeteryID'" : "cemeteryID = '$cemeteryID'";
}
if ($unlinked) {
  $join = 'LEFT JOIN medialinks ON media.mediaID = medialinks.mediaID';
  $medialinkID = 'medialinkID,';
  $wherestr .= $wherestr ? ' AND medialinkID is NULL' : 'medialinkID is NULL';
}
if ($wherestr) {
  $wherestr = "WHERE $wherestr";
}
$query = "SELECT media.mediaID AS mediaID, $medialinkID description, notes, thumbpath, mediatypeID, usecollfolder, latitude, longitude, zoom FROM media $join $wherestr ORDER BY description LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(media.mediaID) AS mcount FROM media $join $wherestr";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['mcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('media'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id='admin-media'>
  <section class='container'>
    <?php
    $standardtypes = [];
    foreach ($mediatypes as $mediatype) {
      if (!$mediatype['type']) {
        $standardtypes[] = '"' . $mediatype['ID'] . '"';
      }
    }
    $sttypestr = implode(',', $standardtypes);

    echo $adminHeaderSection->build('media', $message);
    $navList = new navList('');
    //    $navList->appendItem([true, 'mediaBrowse.php', uiTextSnippet('browse'), 'findmedia']);
    $navList->appendItem([$allowMediaAdd, 'mediaAdd.php', uiTextSnippet('add'), 'addmedia']);
    $navList->appendItem([$allowMediaEdit, 'mediaSort.php', uiTextSnippet('text_sort'), 'sortmedia']);
    $navList->appendItem([$allowMediaEdit, 'mediaThumbnails.php', uiTextSnippet('thumbnails'), 'thumbs']);
    $navList->appendItem([$allowMediaAdd, 'mediaImport.php', uiTextSnippet('import'), 'import']);
    $navList->appendItem([$allowMediaAdd, 'mediaUpload.php', uiTextSnippet('upload'), 'upload']);
    echo $navList->build('findmedia');
    ?>
    <div class='row'>
      <form action="mediaBrowse.php" name='form1' id='form1'>
        <div class='row'>
          <div class='col-md-6'>
            <?php
            $newwherestr = $wherestr;
            $wherestr = $orgwherestr;
            $wherestr = $newwherestr;
            ?>
          </div>
          <div class='col-md-6'>
            <input class='form-control' name='searchstring' type='text' value="<?php echo $originalstring; ?>" placeholder='<?php echo uiTextSnippet('searchfor'); ?>'>
            <button class='btn btn-outline-primary' name='submit' type='submit'><?php echo uiTextSnippet('search'); ?></button>
            <button class='btn btn-outline-warning' name='submit' type='submit' onClick="resetForm();"><?php echo uiTextSnippet('reset'); ?></button>
          </div>
        </div>
        <br>
        <div class='row'>
          <div class='col-md-6'>
            <br>
            <label class='form-control-label' for='fileext'><?php echo uiTextSnippet('fileext'); ?>:</label>
            <input class='form-control' name='fileext' type='text' value="<?php echo $fileext; ?>">
            <br>
            <input name='unlinked' type='checkbox' value='1'<?php if ($unlinked) {echo ' checked';} ?>> <?php echo uiTextSnippet('unlinked'); ?>
          </div>
          <div class='col-md-6'>
            <label for='mediatyeID'>
              <span><?php echo uiTextSnippet('mediatype'); ?>: </span>
              <select class='form-control' name="mediatypeID" onchange="toggleHeadstoneCriteria(this.options[this.selectedIndex].value)">
                <?php
                echo   "<option value=''>" . uiTextSnippet('all') . "</option>\n";
                foreach ($mediatypes as $mediatype) {
                  $msgID = $mediatype['ID'];
                  echo "<option value=\"$msgID\"";
                  if ($msgID == $mediatypeID) {
                    echo ' selected';
                  }
                  echo ">\n";
                  echo $mediatype['display'] . "</option>\n";
                }
                ?>
              </select>
            </label>
            <?php if ($allowAdd && $allowEdit && $allowDelete) { ?>
              <button class='btn btn-secondary' name='addnewmediatype' type='button' onclick="tnglitbox = new ModalDialog('admin_newcollection.php?field=mediatypeID');"><?php echo uiTextSnippet('addnewcoll'); ?></button>
              <button class='btn btn-secondary' id='editmediatype' name='editmediatype' type='button' style='display: none' onclick="editMediatype(document.form1.mediatypeID);"><?php echo uiTextSnippet('edit'); ?></button>
              <button class='btn btn-outline-danger' id='delmediatype' name='delmediatype' type='button' style='display: none' onclick="confirmDeleteMediatype(document.form1.mediatypeID);"><?php echo uiTextSnippet('delete'); ?></button>
            <?php } ?>
          </div>
        </div>
        <table class='table'>
          <tr id="hsstatrow">
            <td>
              <label for='hsstat'>
                <span><?php echo uiTextSnippet('status'); ?>:</span>
                <select class='form-control' name="hsstat">
                  <option value='all'<?php if ($hsstat == 'all') {echo ' selected';} ?>></option>
                  <option value=''<?php if (!$hsstat) {echo ' selected';} ?>>
                    <?php echo uiTextSnippet('nostatus'); ?>
                  </option>
                  <option value='notyetlocated'<?php if ($hsstat == 'notyetlocated') {echo ' selected';} ?>>
                    <?php echo uiTextSnippet('notyetlocated'); ?>
                  </option>
                  <option value='located'<?php if ($hsstat == 'located') {echo ' selected';} ?>>
                    <?php echo uiTextSnippet('located'); ?>
                  </option>
                  <option value='unmarked'<?php if ($hsstat == 'unmarked') {echo ' selected';} ?>>
                    <?php echo uiTextSnippet('unmarked'); ?>
                  </option>
                  <option value='missing'<?php if ($hsstat == 'missing') {echo ' selected';} ?>>
                    <?php echo uiTextSnippet('missing'); ?>
                  </option>
                  <option value='cremated'<?php if ($hsstat == 'cremated') {echo ' selected';} ?>>
                    <?php echo uiTextSnippet('cremated'); ?>
                  </option>
                </select>
              </label>
            </td>
          </tr>
          <tr id="cemrow">
            <td>
              <label for='cemeteryID'>
                <span><?php echo uiTextSnippet('cemetery'); ?>: </span>
                  <select class='form-control' name="cemeteryID">
                    <option selected></option>
                    <?php
                    $query = 'SELECT cemname, cemeteryID, city, county, state, country FROM cemeteries ORDER BY country, state, county, city, cemname';
                    $cemresult = tng_query($query);
                    while ($cemrow = tng_fetch_assoc($cemresult)) {
                      $cemetery = "{$cemrow['country']}, {$cemrow['state']}, {$cemrow['county']}, {$cemrow['city']}, {$cemrow['cemname']}";
                      echo "    <option value=\"{$cemrow['cemeteryID']}\"";
                      if ($cemeteryID == $cemrow['cemeteryID']) {
                        echo ' selected';
                      }
                      echo ">$cemetery</option>\n";
                    }
                    ?>
                  </select>
              </label>
            </td>
          </tr>
        </table>
        <input name='findmedia' type='hidden' value='1'>
        <input name='newsearch' type='hidden' value='1'>
      </form>
      <br>
      <?php
      $numrowsplus = $numrows + $offset;
      if (!$numrowsplus) {
        $offsetplus = 0;
      }
      echo displayListLocation($offsetplus, $numrowsplus, $totrows);
      ?>
      <form action="admin_updateselectedmedia.php" method='post' name="form2">
        <?php if ($allowMediaDelete || $allowMediaEdit) { ?>
          <div class='row'>
            <div class='col-md-6'>
              <button class='btn btn-secondary' name='selectall' type='button' onClick="toggleAll(1);"><?php echo uiTextSnippet('selectall'); ?></button>
              <button class='btn btn-secondary' name='clearall' type='button' onClick="toggleAll(0);"><?php echo uiTextSnippet('clearall'); ?></button>
            <?php if ($allowMediaDelete) { ?>
                <button class='btn btn-outline-danger' name='xphaction' type='submit' onClick="return confirm('<?php echo uiTextSnippet('confdeleterecs'); ?>');"><?php echo uiTextSnippet('deleteselected'); ?></button>
            <?php } ?>
            </div>
            <?php if ($allowMediaEdit) { ?>
              <div class='col-md-3'>
                <button class='btn btn-secondary' name='xphaction' type='submit'><?php echo uiTextSnippet('convto'); ?></button>
              </div>
              <div class='col-md-3'>
                <select class='form-control' name="newmediatype">
                  <?php
                  foreach ($mediatypes as $mediatype) {
                    $msgID = $mediatype['ID'];
                    if ($msgID != $mediatypeID) {
                      echo "  <option value=\"$msgID\">" . $mediatype['display'] . "</option>\n";
                    }
                  }
                ?> 
                </select>
              </div>
            </div>  
            <br>
            <?php
            $albumquery = 'SELECT albumID, albumname FROM albums ORDER BY albumname';
            $albumresult = tng_query($albumquery) or die(uiTextSnippet('cannotexecutequery') . ": $albumquery");
            $numalbums = tng_num_rows($albumresult);
            if ($numalbums) {
              echo "<div class='row'>\n";
              echo "<div class='offset-md-6 col-md-3'>\n";
              echo "<button class='btn btn-secondary' name='xphaction' type='submit'>" . uiTextSnippet('addtoalbum') . "</button>\n";
              echo "</div>\n";
              echo "<div class='col-md-3'>\n";
              echo "<select class='form-control' name='albumID'>\n";
              while ($albumrow = tng_fetch_assoc($albumresult)) {
                echo "<option value=\"{$albumrow['albumID']}\">{$albumrow['albumname']}</option>\n";
              }
              echo "</select>\n";
              echo "</div>\n";
              echo "</div>\n";
            }
            tng_free_result($albumresult);
          }
          ?>
        <?php } ?>
        <table class='table table-sm'>
          <thead>
            <tr>
              <th><?php echo uiTextSnippet('action'); ?></th>
              <?php if ($allowEdit || $allowMediaEdit || $allowDelete || $allowMediaDelete) { ?>
                <th><?php echo uiTextSnippet('select'); ?></th>
              <?php } ?>
              <th><?php echo uiTextSnippet('thumb'); ?></th>
              <th><?php echo uiTextSnippet('title') . ', ' . uiTextSnippet('description'); ?></th>
              <?php if ($map['key']) { ?>
                <th><?php echo uiTextSnippet('googleplace'); ?></th>
              <?php } ?>
              <?php if (!$mediatypeID) { ?>
                <th><?php echo uiTextSnippet('mediatype'); ?></th>
              <?php } ?>
              <th><?php echo uiTextSnippet('linkedto'); ?></th>
            </tr>
          </thead>
          <?php
          if ($numrows) {
          $actionstr = '';
          if ($allowMediaEdit) {
            $actionstr .= "<a href=\"mediaEdit.php?mediaID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
            $actionstr .= "</a>\n";
          }
          if ($allowMediaDelete) {
            $actionstr .= "<a href='#' onClick=\"return confirmDelete('xxx');\" title='" . uiTextSnippet('delete') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
            $actionstr .= '</a>';
          }
          $actionstr .= "<a href=\"showmedia.php?mediaID=xxx\" title='" . uiTextSnippet('preview') . "'>\n";
          $actionstr .= "<img class='icon-sm' src='svg/eye.svg'>\n";
          $actionstr .= "</a>\n";

          while ($row = tng_fetch_assoc($result)) {
            //$cleanfile = $session_charset == 'UTF-8' ? utf8_decode($row['thumbpath']) : $row['thumbpath'];
            $mtypeID = $row['mediatypeID'];
            $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mtypeID] : $mediapath;
            $newactionstr = preg_replace('/xxx/', $row['mediaID'], $actionstr);
            echo "<tr id=\"row_{$row['mediaID']}\"><td><div class=\"action-btns\">$newactionstr</div></td>\n";
            if ($allowEdit || $allowMediaEdit || $allowDelete || $allowMediaDelete) {
              echo "<td><input name=\"ph{$row['mediaID']}\" type='checkbox' value='1'></td>";
            }
            echo '<td>';
            if ($row['thumbpath'] && file_exists("$rootpath$usefolder/" . $row['thumbpath'])) {
              $photoinfo = getimagesize("$rootpath$usefolder/" . $row['thumbpath']);
              if ($photoinfo['1'] < 50) {
                $photohtouse = $photoinfo['1'];
                $photowtouse = $photoinfo['0'];
              } else {
                $photohtouse = 50;
                $photowtouse = intval(50 * $photoinfo['0'] / $photoinfo['1']);
              }
              echo '<span>';
              echo "<img src=\"$usefolder/" . str_replace('%2F', '/', rawurlencode($row['thumbpath'])) . "\" width=\"$photowtouse\" height=\"$photohtouse\"></span>\n";
            }
            echo "</td>\n";
            $description = $allowEdit || $allowMediaEdit ? "<a href=\"mediaEdit.php?mediaID={$row['mediaID']}\">{$row['description']}</a>" : $row['description'];
            echo "<td><span>$description<br>" . truncateIt(getXrefNotes($row['notes']), $maxnoteprev) . "</span></td>\n";
            if ($map['key']) {
              echo '<td><span>';
              $geo = '';
              if ($row['latitude']) {
                $geo .= uiTextSnippet('latitude') . ': ' . number_format($row['latitude'], 3);
              }
              if ($row['longitude']) {
                if ($geo) {
                  $geo .= '<br>';
                }
                $geo .= uiTextSnippet('longitude') . ': ' . number_format($row['longitude'], 3);
              }
              if ($row['zoom']) {
                if ($geo) {
                  $geo .= '<br>';
                }
                $geo .= uiTextSnippet('zoom') . ': ' . $row['zoom'];
              }
              echo "$geo</span></td>\n";
            }
            if (!$mediatypeID) {
              $label = uiTextSnippet($mtypeID) ? uiTextSnippet($mtypeID) : $mediatypes_display[$mtypeID];
              echo '<td>' . $label . "</td>\n";
            }
            $query = "SELECT people.personID AS personID2, familyID, husband, wife, people.lastname AS lastname, people.lnprefix AS lnprefix, people.firstname AS firstname, people.prefix AS prefix, people.suffix AS suffix, nameorder, medialinks.personID AS personID, sources.title, sources.sourceID, repositories.repoID, reponame, linktype FROM medialinks LEFT JOIN $people_table AS people ON medialinks.personID = people.personID LEFT JOIN families ON medialinks.personID = families.familyID LEFT JOIN sources ON medialinks.personID = sources.sourceID LEFT JOIN repositories ON (medialinks.personID = repositories.repoID) WHERE mediaID = '{$row['mediaID']}' ORDER BY lastname, lnprefix, firstname, personID LIMIT 10";
            $presult = tng_query($query);
            $medialinktext = '';
            while ($prow = tng_fetch_assoc($presult)) {
              $prights = determineLivingPrivateRights($prow);
              $prow['allow_living'] = $prights['living'];
              $prow['allow_private'] = $prights['private'];
              if ($prow['personID2'] != null) {
                $medialinktext .= '<li>' . getName($prow) . " ({$prow['personID2']})</li>\n";
              } elseif ($prow['sourceID'] != null) {
                $sourcetext = $prow['title'] ? uiTextSnippet('source') . ": {$prow['title']}" : uiTextSnippet('source') . ": {$prow['sourceID']}";
                $medialinktext .= "<li>$sourcetext ({$prow['sourceID']})</li>\n";
              } elseif ($prow['repoID'] != null) {
                $repotext = $prow['reponame'] ? uiTextSnippet('repository') . ": {$prow['reponame']}" : uiTextSnippet('repository') . ": {$prow['repoID']}";
                $medialinktext .= "<li>$repotext ({$prow['repoID']})</li>\n";
              } elseif ($prow['familyID'] != null) {
                $medialinktext .= '<li>' . uiTextSnippet('family') . ': ' . getFamilyName($prow) . "</li>\n";
              } else {
                $medialinktext .= "<li>{$prow['personID']}</li>";
              }
            }
            $medialinktext = $medialinktext ? "<ul>\n$medialinktext\n</ul>\n" : '';
            echo "<td>$medialinktext</td>\n";
            echo "</tr>\n";
          }
          ?>
        </table>
        <?php
        echo buildSearchResultPagination($totrows, "mediaBrowse.php?searchstring=$searchstring&amp;mediatypeID=$mediatypeID&amp;fileext=$fileext&amp;hsstat=$hsstat&amp;cemeteryID=$cemeteryID&amp;offset", $maxsearchresults, 5);
      }
      else {
        echo "</table>\n" . uiTextSnippet('norecords');
      }
      tng_free_result($result);
      ?>
      </form>
    </div> <!-- .row -->
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src='js/admin.js'></script>
<script src='js/mediautils.js'></script>
<script>
  var tnglitbox;
  var stmediatypes = new Array(<?php echo $sttypestr; ?>);
  var manage = 1;
  var allow_media_edit = <?php echo($allowMediaEdit ? '1' : '0'); ?>;
  var allow_media_delete = <?php echo($allowMediaDelete ? '1' : '0'); ?>;
  var allow_edit = <?php echo($allowEdit ? '1' : '0'); ?>;
  var allow_delete = <?php echo($allowDelete ? '1' : '0'); ?>;

  function toggleHeadstoneCriteria(mediatypeID) {
    var hsstatus = document.getElementById('hsstatrow');
    var cemrow = document.getElementById('cemrow');
    if (mediatypeID === 'headstones') {
      cemrow.style.display = '';
      hsstatus.style.display = '';
    } else {
      cemrow.style.display = 'none';
      document.form1.cemeteryID.selectedIndex = 0;
      hsstatus.style.display = 'none';
      document.form1.hsstat.selectedIndex = 0;
      if (mediatypeID && stmediatypes.indexOf(mediatypeID) === -1) {
        if ($('#editmediatype').length)
          $('#editmediatype').show();
        if ($('#delmediatype').length)
          $('#delmediatype').show();
      } else {
        if ($('#editmediatype').length)
          $('#editmediatype').hide();
        if ($('#delmediatype').length)
          $('#delmediatype').hide();
      }
    }
    return false;
  }

  function resetForm() {
    document.form1.searchstring.value = '';
    document.form1.mediatypeID.selectedIndex = 0;
    document.form1.fileext.value = '';
    document.form1.unlinked.checked = false;
    document.form1.hsstat.selectedIndex = 0;
    document.form1.cemeteryID.selectedIndex = 0;
  }

  function confirmDelete(mediaID) {
    if (confirm(textSnippet('confdeletemedia'))) {
      deleteIt('media', mediaID);
    }
    return false;
  }
</script>
<script>
  toggleHeadstoneCriteria('<?php echo $mediatypeID; ?>');
</script>
</body>
</html>