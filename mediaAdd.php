<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowMediaAdd) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

if ($assignedtree) {
  $wherestr = "WHERE gedcom = \"$assignedtree\"";
  $tree = $assignedtree;
} else {
  $wherestr = "";
}
$treequery = "SELECT gedcom, treename FROM $treesTable $wherestr ORDER BY treename";
$treeresult = tng_query($treequery) or die(uiTextSnippet('cannotexecutequery') . ": $treequery");
$treenum = 0;
while ($treerow = tng_fetch_assoc($treeresult)) {
  $treenum++;
  $trees[$treenum] = $treerow['gedcom'];
  $treename[$treenum] = $treerow['treename'];
}
tng_free_result($treeresult);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('addnewmedia'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="media-addnewmedia">
  <section class='container'>
    <?php
    $lastcoll = isset($_COOKIE['lastcoll']) ? $_COOKIE['lastcoll'] : "";
    $standardtypes = array();
    $moptions = "";
    $likearray = "var like = new Array();\n";
    foreach ($mediatypes as $mediatype) {
      if (!$mediatype['type']) {
        $standardtypes[] = "\"" . $mediatype['ID'] . "\"";
      }
      $msgID = $mediatype['ID'];
      $moptions .= "  <option value=\"$msgID\"";
      if ($lastcoll == $msgID) {
        $moptions .= " selected";
      }
      $moptions .= ">" . $mediatype['display'] . "</option>\n";
      $likearray .= "like['$msgID'] = '{$mediatype['liketype']}';\n";
    }
    $sttypestr = implode(",", $standardtypes);
    ?>

    <?php
    echo $adminHeaderSection->build('media-addnewmedia', $message);
    $navList = new navList('');
    $navList->appendItem([true, "mediaBrowse.php", uiTextSnippet('browse'), "findmedia"]);
    //    $navList->appendItem([$allowMediaAdd, "mediaAdd.php", uiTextSnippet('add'), "addmedia"]);
    $navList->appendItem([$allowMediaEdit, "mediaSort.php", uiTextSnippet('text_sort'), "sortmedia"]);
    $navList->appendItem([$allowMediaEdit && !$assignedtree, "mediaThumbnails.php", uiTextSnippet('thumbnails'), "thumbs"]);
    $navList->appendItem([$allowMediaAdd && !$assignedtree, "mediaImport.php", uiTextSnippet('import'), "import"]);
    $navList->appendItem([$allowMediaAdd && !$assignedtree, "mediaUpload.php", uiTextSnippet('upload'), "upload"]);
    echo $navList->build("addmedia");
    ?>
    <div class='small'>
      <a href='#' onClick="toggleAll('on');"><?php echo uiTextSnippet('expandall'); ?></a>
      <a href='#' onClick="toggleAll('off');"><?php echo uiTextSnippet('collapseall'); ?></a>
    </div>
    <form action="mediaAddFormAction.php" method='post' name='form1' id='form1' ENCTYPE="multipart/form-data"
          onSubmit="return validateForm();">
      <input name='link_personID' type='hidden' value="<?php echo $personID; ?>">
      <input name='link_tree' type='hidden' value="<?php echo $tree; ?>">
      <input name='link_linktype' type='hidden' value="<?php echo $linktype; ?>">
     
      <?php echo displayToggle("plus0", 1, "mediafile", uiTextSnippet('imagefile'), uiTextSnippet('uplsel')); ?>

      <div id="mediafile">
        <br>
        <?php echo uiTextSnippet('mediatype'); ?>:
        <select name="mediatypeID" onChange="switchOnType(this.options[this.selectedIndex].value)">
          <?php echo $moptions; ?>
        </select>
        <?php if (!$assignedtree && $allowAdd && $allowEdit && $allowDelete) { ?>
          <input name='addnewmediatype' type='button' value="<?php echo uiTextSnippet('addnewcoll'); ?>"
                 onclick="tnglitbox = new ModalDialog('admin_newcollection.php?field=mediatypeID');">
          <input id='editmediatype' name='editmediatype' type='button' value="<?php echo uiTextSnippet('edit'); ?>" style="display: none"
                 onclick="editMediatype(document.form1.mediatypeID);">
          <input id='delmediatype' name='delmediatype' type='button' value="<?php echo uiTextSnippet('delete'); ?>" style="display: none"
                 onclick="confirmDeleteMediatype(document.form1.mediatypeID);">
        <?php } ?>
        <br>
        <input name='abspath' type='checkbox' value='1'
               onClick="toggleMediaURL();"><span> <?php echo uiTextSnippet('abspath'); ?></span>
        <br>
        <span><strong><br><?php echo uiTextSnippet('imagefile'); ?></strong></span>
        <div class='row' id='imgrow'>
          <div class='col-sm-12'><?php echo uiTextSnippet('imagefiletoupload'); ?>*:
            <input name='newfile' type='file' onchange="populatePath(document.form1.newfile, document.form1.path);">
          </div>
        </div>
        <div class='row' id='pathrow'>
          <div class='col-sm-12'><?php echo uiTextSnippet('pathwithinphotos'); ?>**:
            <input id='path' name='path' type='text' size='60'>
            <input id='path_org' type="hidden">
            <input id='path_last' type="hidden"> 
            <input name='photoselect' type='button' value="<?php echo uiTextSnippet('select') . "..."; ?>"
                   onclick="javascript:FilePicker('path', document.form1.mediatypeID.options[document.form1.mediatypeID.selectedIndex].value);">
          </div>
        </div>
        <div class='row' id="abspathrow" style="display:none">
          <div class='col-sm-12'><span><?php echo uiTextSnippet('mediaurl'); ?>:</span>
            <input name='mediaurl' type='text' size='60'>
          </div>
        </div>
        <!-- history section -->
        <div class='row' id='bodytextrow'>
          <div class='col-sm-12'>
            <span><?php echo uiTextSnippet('bodytext'); ?>:</span>
            <br>
            <textarea class='form-control' id='bodytext' name='bodytext' wrap='soft'></textarea>
          </div>
        </div>
        <?php if (function_exists("imageJpeg")) { ?>
          <div class='row'>
            <div class='col-sm-12'>
              <span><strong><br><?php echo uiTextSnippet('thumbnailfile'); ?></strong></span>
              <span><br>
                <input name='thumbcreate' type='radio' value='specify' checked
                       onClick="document.form1.newthumb.style.visibility = 'visible'; document.form1.thumbselect.style.visibility = 'visible';"> <?php echo uiTextSnippet('specifyimg'); ?>
                &nbsp;
                <input name='thumbcreate' type='radio' value='auto'
                       onClick="document.form1.newthumb.style.visibility = 'hidden'; document.form1.thumbselect.style.visibility = 'hidden'; prepopulateThumb(); document.form1.abspath.checked = false;"> <?php echo uiTextSnippet('autoimg'); ?>
              </span>
            </div>
          </div>
        <?php } else { ?>
          <div class='row'>
            <div class='col-sm-12'>
              <strong><br><?php echo uiTextSnippet('thumbnailfile'); ?></strong>
            </div>
          </div>
        <?php } ?>
        <div class='row'>
          <div class='col-sm-12'>
            <?php echo uiTextSnippet('imagefiletoupload'); ?>*:
            <input name='newthumb' type="file"
                   onChange="populatePath(document.form1.newthumb, document.form1.thumbpath);">
          </div>
        </div>
        <div class='row'>
          <div class='col-sm-12'>
            <?php echo uiTextSnippet('pathwithinphotos'); ?>**:
            <input id='thumbpath' name='thumbpath' type='text' size='60'>
            <input id='thumbpath_org' type='hidden'>
            <input id='thumbpath_last' type='hidden'>
            <input name='thumbselect' type='button' value="<?php echo uiTextSnippet('select') . "..."; ?>"
                   onClick="javascript:FilePicker('thumbpath', document.form1.mediatypeID.options[document.form1.mediatypeID.selectedIndex].value);">
          </div>
        </div>
        <div class='row'>
          <div class='col-sm-12'>
            <span><strong><br><?php echo uiTextSnippet('put_in'); ?></strong></span>
            <span><br>
              <input name='usecollfolder' type='radio' value='0'> <?php echo uiTextSnippet('usemedia'); ?> &nbsp;
              <input name='usecollfolder' type='radio' value='1' checked> <?php echo uiTextSnippet('usecollect'); ?>
            </span>
          </div>
        </div>
        <div class='row' id='vidrow1'>
          <div class='col-sm-12'>
            <span><?php echo uiTextSnippet('width'); ?>:</span>
            <input name='width' type='text' size='40'>
          </div>
        </div>
        <div class='row' id='vidrow2'>
          <div class='col-sm-12'>
            <span><?php echo uiTextSnippet('height'); ?>:</span>
            <input name='height' type='text' size='40'><span> (<?php echo uiTextSnippet('controller'); ?>)</span>
          </div>
        </div>
        <p class="small">
          <?php
          echo "*" . uiTextSnippet('leaveblankphoto') . "<br>\n";
          echo "**" . uiTextSnippet('requiredphoto') . "\n";
          ?>
        </p>
      </div>

      <?php echo displayToggle("plus1", 1, "details", uiTextSnippet('newmediainfo'), uiTextSnippet('minfosubt')); ?>

      <div id="details">
        <br>
        <div class='row'>
          <div class='col-sm-12'>
            <?php echo uiTextSnippet('title'); ?>:
            <textarea class='form-control' name='description' wrap='soft'></textarea>
          </div>
        </div>
        <div class='row'>
          <div class='col-sm-12'>
            <?php echo uiTextSnippet('description'); ?>:
            <textarea class='form-control' name='notes' wrap='soft'></textarea>
          </div>
        </div>
        <div class='row'>
          <div class='col-sm-12'>
            <?php echo uiTextSnippet('photoowner'); ?>:
            <input name='owner' type='text'>
          </div>
        </div>
        <div class='row'>
          <div class='col-sm-12'>
            <?php echo uiTextSnippet('datetaken'); ?>:
            <input name='datetaken' type='text' size='40' onblur="checkDate(this);">
          </div>
        </div>
        <div class='row'>
          <div class='col-sm-12'>
            <?php echo uiTextSnippet('tree'); ?>:
            <select name='tree'>
              <?php
              echo "  <option value=''>" . uiTextSnippet('alltrees') . "</option>\n";
              $treeresult = tng_query($treequery) or die(uiTextSnippet('cannotexecutequery') . ": $treequery");
              while ($treerow = tng_fetch_assoc($treeresult)) {
                echo "  <option value=\"{$treerow['gedcom']}\"";
                if ($treerow['gedcom'] == $tree) {
                  echo " selected";
                }
                echo ">{$treerow['treename']}</option>\n";
              }
              tng_free_result($treeresult);
              ?>
            </select>
          </div>
        </div>

        <!-- headstone section -->
        <div class='row' id='cemrow'>
          <div class='col-sm-12'>
            <?php echo uiTextSnippet('cemetery'); ?>:
            <div id="cemchoice">
              <a href="#" onclick="return toggleCemSelect();"><?php echo uiTextSnippet('select'); ?></a>
            </div>
            <div id="cemselect" style="display: none">
              <select name='cemeteryID'>
                <option selected></option>
                <?php
                $query = "SELECT cemname, cemeteryID, city, county, state, country FROM $cemeteries_table ORDER BY country, state, county, city, cemname";
                $cemresult = tng_query($query);
                while ($cemrow = tng_fetch_assoc($cemresult)) {
                  $cemetery = "{$cemrow['country']}, {$cemrow['state']}, {$cemrow['county']}, {$cemrow['city']}, {$cemrow['cemname']}";
                  echo " <option value=\"{$cemrow['cemeteryID']}\">$cemetery</option>\n";
                }
                ?>
              </select>
            </div>
          </div>
        </div>
        <div class='row' id='hsplotrow'>
          <div class='col-sm-12'>
            <?php echo uiTextSnippet('plot'); ?>:
            <textarea class='form-control' name='plot' wrap='soft'></textarea>
          </div>
        </div>
        <div class='row' id='hsstatrow'>
          <div class='col-sm12'><?php echo uiTextSnippet('status'); ?>:
            <select name='status'>
              <option value=''>&nbsp;</option>
              <option value="notyetlocated"><?php echo uiTextSnippet('notyetlocated'); ?></option>
              <option value="located"><?php echo uiTextSnippet('located'); ?></option>
              <option value="unmarked"><?php echo uiTextSnippet('unmarked'); ?></option>
              <option value="missing"><?php echo uiTextSnippet('missing'); ?></option>
              <option value="cremated"><?php echo uiTextSnippet('cremated'); ?></option>
            </select>
          </div>
        </div>
        <br>
        <input name='alwayson' type='checkbox' value='1'> <?php echo uiTextSnippet('alwayson'); ?>

        <!-- history section -->
        <div class='row' id='newwinrow'>
          <div class='col-sm-12'>
            <input name='newwindow' type='checkbox' value='1'> <?php echo uiTextSnippet('newwin'); ?>
          </div>
        </div>

        <!-- headstone section -->
        <div class='row' id='linktocemrow'>
          <div class='col-sm-12'>
            <input name='linktocem' type='checkbox' value='1'> <?php echo uiTextSnippet('linktocem'); ?>
          </div>
        </div>
        <div class='row' id='maprow'>
          <div class='col-sm-12'>
            <input name='showmap' type='checkbox' value='1'> <?php echo uiTextSnippet('showmap'); ?>
          </div>
        </div>
      </div>
      <p><strong><?php echo uiTextSnippet('medlater'); ?></strong></p>
      <input name='usenl' type='hidden' value='0'/>
      <input name='cw' type='hidden' value="<?php echo "$cw"; ?>">
      <input name='numlinks' type='hidden' value='1'>
      <input name='submitbtn' type='submit' value="<?php echo uiTextSnippet('savecont'); ?>">
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src='js/mediautils.js'></script>
<script src='js/admin.js'></script>
<script src='js/datevalidation.js'></script>
<script>
  var tree = "<?php echo $tree; ?>";
  var tnglitbox;
  var trees = new Array();
  var treename = new Array();
<?php
for ($i = 1; $i <= $treenum; $i++) {
  echo "trees[$i] = \"$trees[$i]\";\n";
  echo "treename[$i] = \"$treename[$i]\";\n";
}
?>
  var thumbPrefix = "<?php echo $thumbprefix; ?>";
  var thumbSuffix = "<?php echo $thumbsuffix; ?>";
  var treemsg = '<?php echo uiTextSnippet('tree'); ?>';
  var manage = 0;
<?php echo $likearray; ?>
  var linkcount = 1;
  var stmediatypes = new Array(<?php echo $sttypestr; ?>);
  var allow_edit = <?php echo($allowEdit ? "1" : "0"); ?>;
  var allow_delete = <?php echo($allowDelete ? "1" : "0"); ?>;

  function validateForm() {
    var rval = true;

    var frm = document.form1;
   
    var selectedType = frm.mediatypeID.options[frm.mediatypeID.selectedIndex].value;
    if (frm.path.value.length === 0 && frm.mediaurl.value.length === 0 && like[selectedType] !== "histories" && frm.mediatypeID.options[frm.mediatypeID.selectedIndex].value !== "headstones") {
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

  var gsControlName = "";

  function toggleAll(display) {
    toggleSection('mediafile', 'plus0', display);
    toggleSection('details', 'plus1', display);
    return false;
  }
</script>
<script>
  var preferEuro = <?php echo($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : "false"); ?>;
  var preferDateFormat = '<?php echo $preferDateFormat; ?>';
  switchOnType(document.form1.mediatypeID.options[document.form1.mediatypeID.selectedIndex].value);
</script>
<script src="js/nicedit.js"></script>
<script>
  bkLib.onDomLoaded(function () {
    new nicEditor({fullPanel: true}).panelInstance('bodytext');
  });
</script>
</body>
</html>