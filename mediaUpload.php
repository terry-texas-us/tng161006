<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowMediaEdit) {
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

$flags['styles'] .= "<!-- blueimp Gallery styles -->\n";
$flags['styles'] .= "<link rel=\"stylesheet\" href=\"//blueimp.github.io/Gallery/css/blueimp-gallery.min.css\">\n";

$flags['styles'] = "<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->\n";
$flags['styles'] .= "<link rel=\"stylesheet\" href=\"jquery.fileupload/jquery.fileupload.css\">\n";
$flags['styles'] .= "<link rel=\"stylesheet\" href=\"jquery.fileupload/jquery.fileupload-ui.css\">\n";

$flags['styles'] .= "<!-- CSS adjustments for browsers with JavaScript disabled -->\n";
$flags['styles'] .= "<noscript><link rel=\"stylesheet\" href=\"css/jquery.fileupload-noscript.css\"></noscript>\n";
$flags['styles'] .= "<noscript><link rel=\"stylesheet\" href=\"css/jquery.fileupload-ui-noscript.css\"></noscript>\n";

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('sortmedia'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="media-upload">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('media-upload', $message);
    $navList = new navList('');
    $navList->appendItem([true, "mediaBrowse.php", uiTextSnippet('browse'), "findmedia"]);
    $navList->appendItem([$allowMediaAdd, "mediaAdd.php", uiTextSnippet('add'), "addmedia"]);
    $navList->appendItem([$allowMediaEdit, "mediaSort.php", uiTextSnippet('text_sort'), "sortmedia"]);
    $navList->appendItem([$allowMediaEdit && !$assignedtree, "mediaThumbnails.php", uiTextSnippet('thumbnails'), "thumbs"]);
    $navList->appendItem([$allowMediaAdd && !$assignedtree, "mediaImport.php", uiTextSnippet('import'), "import"]);
    //    $navList->appendItem([$allowMediaAdd && !$assignedtree, "mediaUpload.php", uiTextSnippet('upload'), "upload"]);
    echo $navList->build("upload");
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <h4><?php echo uiTextSnippet('mediaupl'); ?></h4>

          <form id="fileupload" action="mediaUploadFormAction.php" method='post' enctype="multipart/form-data">
            <div class='row'>
              <div class='col-sm-3'>
                <span><?php echo uiTextSnippet('mediatype'); ?>: </span>
                <select class='form-control' name="mediatypeID" id="mediatypeID" onchange="changeCollection(this);">
                  <?php
                  foreach ($mediatypes as $mediatype) {
                    $msgID = $mediatype['ID'];
                    echo "  <option value=\"$msgID\">" . $mediatype['display'] . "</option>\n";
                  }
                  ?>
                </select>
              </div>
              <div class='col-sm-3'>
                <span>&nbsp;<?php echo uiTextSnippet('tree'); ?>: </span>
                <?php
                if ($assignedtree) {
                  if ($row['gedcom']) {
                    $treeresult = tng_query($treequery) or die(uiTextSnippet('cannotexecutequery') . ": $treequery");
                    $treerow = tng_fetch_assoc($treeresult);
                    echo $treerow['treename'];
                    tng_free_result($treeresult);
                  } else {
                    echo uiTextSnippet('alltrees');
                  }
                  echo "<input name='tree' type='hidden' value=\"{$row['gedcom']}\">";
                } else {
                  echo "<select class=\"form-control\" name=\"tree\">";
                  echo "  <option value=''>" . uiTextSnippet('alltrees') . "</option>\n";
                  if ($row['gedcom']) {
                    $tree = $row['gedcom'];
                  }

                  $treeresult = tng_query($treequery) or die(uiTextSnippet('cannotexecutequery') . ": $treequery");
                  while ($treerow = tng_fetch_assoc($treeresult)) {
                    echo "  <option value=\"{$treerow['gedcom']}\"";
                    if ($treerow['gedcom'] == $row['gedcom']) {
                      echo " selected";
                    }
                    echo ">{$treerow['treename']}</option>\n";
                  }
                  echo "</select>&nbsp;&nbsp;\n";
                  tng_free_result($treeresult);
                }
                $mediatypeID = $mediatypes[0]['ID'];
                $folder = $mediatypes_assoc[$mediatypeID];
                ?>
              </div>
              <div class='col-sm-3'>
                <label for='folder'>
                  <span><?php echo uiTextSnippet('folder'); ?>: </span><span id="folderlabel"><?php echo $folder; ?></span>
                  <input id="folder" name='folder' type='text'>
                </label>
              </div>          
              <div class='col-sm-3'>
                <input name='folderselect' type='button' value="<?php echo uiTextSnippet('select') . "..."; ?>" onclick="FilePicker('folder', $('#mediatypeID').val(), 1);">
              </div>          
            </div>
            <noscript><input name='redirect' type='hidden' value="$https://blueimp.github.io/jQuery-File-Upload/"></noscript>
            <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
            <div class="row fileupload-buttonbar">
              <div class="col-lg-7">
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-success fileinput-button">
                  <!--<i class="glyphicon glyphicon-plus"></i>-->
                  <span><?php echo uiTextSnippet('addfiles'); ?></span>
                  <input name="files[]" type="file" multiple>
                </span>
                <button type='submit' class="btn btn-primary start">
                  <!--<i class="glyphicon glyphicon-upload"></i>-->
                  <span><?php echo uiTextSnippet('startupl'); ?></span>
                </button>
                <button type="reset" class="btn btn-warning cancel">
                  <!--<i class="glyphicon glyphicon-ban-circle"></i>-->
                  <span><?php echo uiTextSnippet('cancelupl'); ?></span>
                </button>
                <button type='button' class="btn btn-danger delete">
                  <!--<i class="glyphicon glyphicon-trash"></i>-->
                  <span><?php echo uiTextSnippet('delete'); ?></span>
                </button>
                <input class='toggle' type='checkbox'>
                <!-- The global file processing state -->
                <span class="fileupload-process"></span>
              </div>
              <!-- The global progress state -->
              <div class="col-lg-5 fileupload-progress fade">
                <!-- The global progress bar -->
                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                  <div class="progress-bar progress-bar-success" style="width:0;"></div>
                </div>
                <!-- The extended global progress information -->
                <div class="progress-extended">&nbsp;</div>
              </div>
            </div>
            <div class='row' id="uploadarea">
              <div class='col-lg-12'>
                <!-- The table listing the files available for upload/download -->
                <table role="presentation" class="table table-striped">
                  <tbody class="files"></tbody>
                </table>
              </div>
            </div>
          </form>
          <br>

          <form action="mediaSortFormAction.php" method="get" name="find" id="linkerform" onsubmit="return validateForm();">
            <div class='row'>
              <div class='col-sm-3'>
                <?php echo uiTextSnippet('tree'); ?>
                <select class='form-control' name="tree1">
                  <?php
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
              <div class='col-sm-3'>
                <?php echo uiTextSnippet('linktype'); ?>
                <select class='form-control' name="linktype1" onchange="toggleEventLink(this.selectedIndex);">
                  <option value='I'><?php echo uiTextSnippet('person'); ?></option>
                  <option value='F'><?php echo uiTextSnippet('family'); ?></option>
                  <option value='S'><?php echo uiTextSnippet('source'); ?></option>
                  <option value='R'><?php echo uiTextSnippet('repository'); ?></option>
                  <option value='L'><?php echo uiTextSnippet('place'); ?></option>
                </select>
              </div>
              <div class='col-sm-6'>
                <?php echo uiTextSnippet('id'); ?>
                <input id='newlink1' name='newlink1' type='text' value="<?php echo $personID; ?>" 
                       onblur="toggleEventRow(document.find.eventlink1.checked);">
                <a href="#" onclick="return findItem(document.find.linktype1.options[document.find.linktype1.selectedIndex].value, 'newlink1', null, document.find.tree1.options[document.find.tree1.selectedIndex].value, '<?php echo $assignedbranch; ?>');" title="<?php echo uiTextSnippet('find'); ?>">
                  <img class='icon-sm' src='svg/magnifying-glass.svg'>
                </a>
                <input class='toggle' type='button' value="<?php echo uiTextSnippet('selectall'); ?>">
                <input id='linker' type='submit' value="<?php echo uiTextSnippet('linksel'); ?>"> &nbsp;
                <span id='linkermsg'></span>
                <span id='eventlink1'>
                  <input name='eventlink1' type='checkbox' value='1' 
                         onclick="return toggleEventRow(this.checked);"/> <?php echo uiTextSnippet('eventlink'); ?>
                </span><br>
                <select id='eventrow1' name='event1' style="display: none">
                  <option value=''></option>
                </select>
              </div>
            </div>
          </form>
        </td>
      </tr>

    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src='js/mediafind.js'></script>
  <script src='js/mediautils.js'></script>
  <script src='js/selectutils.js'></script>
  <script src='js/datevalidation.js'></script>
  <script>
    var preferEuro = <?php echo($tngconfig['preferEuro'] ? $tngconfig['preferEuro'] : "false"); ?>;
    var preferDateFormat = '<?php echo $preferDateFormat; ?>';
    var findopen;
    var album = '';
    var media = '';
    var type = "media";

    var findform = "find";
    var resheremsg = <?php echo "'<span>" . uiTextSnippet('reshere') . "</span>'"; ?>; // the snippet was undefined and i do not know what it should be
    var tng_thumbmaxw = <?php echo($thumbmaxw ? $thumbmaxw : "80"); ?>;
    var tng_thumbmaxh = <?php echo($thumbmaxh ? $thumbmaxh : "80"); ?>;
    var links_url = "ajx_medialinks.php";
    var findform;
    var remove_text = <?php echo "'" . uiTextSnippet('removelink') . "'" ?>;

    function enableSave(savebuttonid) {
      $('#q' + savebuttonid).removeAttr('disabled');
      $('#q' + savebuttonid + ' span').show();
      $('#ch' + savebuttonid).hide();
    }

    function validateForm() {
      var rval = true;

      if (document.find.newlink1.value.length === 0) {
        alert("<?php echo uiTextSnippet('enterid'); ?>");
        rval = false;
      }
      return rval;
    }

    function getTree(treeobj) {
      if (treeobj.options.length)
        return treeobj.options[treeobj.selectedIndex].value;
      else {
        alert(textSnippet('selecttree'));
        return false;
      }
    }

    function confirmDelete(event) {
      if (confirm('<?php echo uiTextSnippet('confdeletemedia'); ?>'))
        return true;
      else {
        event.preventDefault();
        event.stopPropagation();
        return false;
      }
    }

    var mediafolders = new Array();
    <?php
    foreach ($mediatypes as $mediatype) {
      $ID = $mediatype['ID'];
      echo "mediafolders['$ID'] = '{$mediatypes_assoc[$ID]}';\n";
    }
    ?>

    function changeCollection(coll) {
      var mediatype = coll.options[coll.selectedIndex].value;
      $('#folderlabel').html(mediafolders[mediatype]);
      $('#folder').val("");
    }

    $(document).ready(function () {
      $('#linker').click(function (e) {
        e.preventDefault();
        if ($('#newlink1').val()) {
          var medialist = "";

          $('.mediacheck:checked').each(function () {
            medialist += (medialist ? "," + this.id : this.id);
          });
          if (medialist) {
            var linkermsg = $('#linkermsg');
            linkermsg.html('&nbsp;<img src="img/spinner.gif">');

            var params = $('#linkerform').serialize();
            params += "&medialist=" + medialist + "&action=masslink";
            $.ajax({
              url: 'ajx_updateorder.php',
              data: params,
              dataType: 'html',
              success: function (req) {
                linkermsg.html('<span class="green">' + req + '</span>');
                linkermsg.effect("highlight", {}, 2500);
              },
              error: function (req) {
                linkermsg.html("An error has occurred. Please try again.");
              }
            });
          }
        }
        return false;
      });
    });
  </script>

  <!-- The template to display files available for upload -->
  <script id="template-upload" type="text/x-tmpl">
    {% for (var i=0, file; file=o.files[i]; i++) { %}
      <tr class="template-upload fade">
        <td>
          <span  class="preview"></span>
        </td>
        <td></td>
        <td></td>
        <td>
          <p class="name">{%=file.name%}</p>
          <strong class="error text-danger"></strong>
        </td>
        <td>
          <p class="size">Processing...</p>
        </td>
        <td>
          <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>  
        </td>
        <td>
          {% if (!i && !o.options.autoUpload) { %}
            <button class="btn btn-primary start" disabled>
              <!-- <i class="glyphicon glyphicon-upload"></i> -->
              <span><?php echo uiTextSnippet('startupl'); ?></span>
            </button>
          {% } %}
          {% if (!i) { %}
            <button class="btn btn-warning cancel">
              <!-- <i class="glyphicon glyphicon-ban-circle"></i> -->
              <span><?php echo uiTextSnippet('cancelupl'); ?></span>
            </button>
          {% } %}
        </td>
      </tr>
    {% } %}
  </script>
  <!-- The template to display files available for download -->
  <script id="template-download" type="text/x-tmpl">
    {% for (var i=0, file; file=o.files[i]; i++) { %}
      <tr class="template-download fade">
        <td>
          <span class="preview">
            {% if (file.thumbnailUrl) { %}
              <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
            {% } %}
          </span>
        </td>
        <td>
          {% if (file.error) { %}
            <div><span class="label label-danger">Error</span> {%=file.error%}</div>
          {% } %}
        </td>
        <td>
          <?php echo uiTextSnippet('title') . "<hr/>\n" . uiTextSnippet('description'); ?><br><br>
          <button class="savebutton" id="q{%=file.mediaID%}" disabled="disabled">
            <span style="display:none"><?php echo uiTextSnippet('save'); ?></span><img src="img/tng_check.gif" alt="" id="ch{%=file.mediaID%}">
          </button> &nbsp;
          <span id="spin{%=file.mediaID%}" style="visibility:hidden"><img src="img/spinner.gif"></span>
        </td>          
        <td class="name">
          <form id="f{%=file.mediaID%}">
            <input class="uploadfield" id="t{%=file.mediaID%}" name="title" type='text' value="{%=file.name%}" onkeypress="enableSave('{%=file.mediaID%}');" onpaste="enableSave('{%=file.mediaID%}');"/><br>
            <textarea id="d{%=file.mediaID%}" name='description' rows="3" class="uploadfield" onkeypress="enableSave('{%=file.mediaID%}');" onpaste="enableSave('{%=file.mediaID%}');"></textarea>
            <input id="mediaID" name="mediaID" type='hidden' value="{%=file.mediaID%}">
            <table class="uploadmore">
              <tr>
                <td><?php echo uiTextSnippet('photoowner'); ?>:</td>
                <td>
                  <input id="o{%=file.mediaID%}" name="owner" type='text' value='' size='40' onkeypress="enableSave('{%=file.mediaID%}');" onpaste="enableSave('{%=file.mediaID%}');">
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('datetaken'); ?>:</td>
                <td>
                  <input id="k{%=file.mediaID%}" name="datetaken" type='text' value='' size='40' onblur="checkDate(this);" onkeypress="enableSave('{%=file.mediaID%}');" onpaste="enableSave('{%=file.mediaID%}');">
                </td>
              </tr>
            </table>
          </form>  
        </td>
        <td>
          <button class="linksbutton btn btn-secondary" id="l{%=file.mediaID%}">
            <span><?php echo uiTextSnippet('medialinks'); ?></span>
          </button><br><br>
          <span>&nbsp;{%=file.dims%}</span><br>
          <span class="size">{%=o.formatFileSize(file.size)%}</span>
          <span>&nbsp;<a href="mediaEdit.php?mediaID={%=file.mediaID%}" target="_blank"><?php echo uiTextSnippet('edit'); ?></a>
        </td>
        <td style="width:10%;"></td>
        <td>
          {% if (file.deleteUrl) { %}
            <button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
              <!-- <i class="glyphicon glyphicon-trash"></i> -->
              <span><?php echo uiTextSnippet('delete'); ?></span>
            </button>
            <input class='toggle' name='delete' type='checkbox' value='1'>
          {% } else { %}
            <button class="btn btn-warning cancel">
              <!-- <i class="glyphicon glyphicon-ban-circle"></i> -->
              <span><?php echo uiTextSnippet('cancelupl'); ?></span>
            </button>
          {% } %}
        </td>
      </tr>
    {% } %}
  </script>
  
  <script src="//blueimp.github.io/JavaScript-Templates/js/tmpl.min.js"></script> <!-- to render the upload/download listings -->
  <script src="//blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js"></script> <!-- for the preview images and image resizing functionality -->
  <script src="//blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script> <!-- for image resizing functionality -->
  <script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script> <!-- for the responsive demo navigation (not required) -->
  <script src="//blueimp.github.io/Gallery/js/jquery.blueimp-gallery.min.js"></script>
  <script src="jquery.fileupload/jquery.iframe-transport.js"></script> <!-- required for browsers without support for XHR file uploads -->
  <script src="jquery.fileupload/jquery.fileupload.js"></script>
  <script src="jquery.fileupload/jquery.fileupload-process.js"></script>
  <script src="jquery.fileupload/jquery.fileupload-image.js"></script> <!-- image preview & resize plugin -->
  <script src="jquery.fileupload/jquery.fileupload-audio.js"></script> <!-- audio preview plugin -->
  <script src="jquery.fileupload/jquery.fileupload-video.js"></script> <!-- video preview plugin -->
  <script src="jquery.fileupload/jquery.fileupload-validate.js"></script> <!-- validation plugin -->
  <script src="jquery.fileupload/jquery.fileupload-ui.js"></script> <!-- user interface plugin -->
  <script src="js/main.js"></script> <!-- main application script (set upload handler url here!) -->
  
  <!-- The XDomainRequest Transport is included for cross-domain file deletion for IE8+ -->
  <!--[if gte IE 8]>
  <script src="js/cors/jquery.xdr-transport.js"></script><![endif]-->
</body>
</html>