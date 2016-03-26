<?php
require 'begin.php';
require $subroot . 'templateconfig.php';
require 'adminlib.php';

$templatespath = "templates";

if ($link) {
  $adminLogin = 1;
  include 'checklogin.php';
  include 'version.php';

  if ($assignedtree || !$allowEdit) {
    $message = uiTextSnippet('norights');
    header("Location: admin_login.php?message=" . urlencode($message));
    exit;
  }
}
$languageArray = array();
$query = "SELECT display, folder FROM $languagesTable ORDER BY display";
$result = tng_query($query);
$languageList = tng_num_rows($result) ? "<option value=''></option>\n" : "";
while ($row = tng_fetch_assoc($result)) {
  $key = $row['folder'];
  $languageList .= "<option value=\"$key\">{$row['display']}</option>\n";
  $languageArray[$key] = $row['display'];
}
tng_free_result($result);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('modifytemplatesettings'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="setup-configuration-templateconfigsettings">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('setup-configuration-templateconfigsettings', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_setup.php", uiTextSnippet('configuration'), "configuration"]);
    $navList->appendItem([true, "admin_diagnostics.php", uiTextSnippet('diagnostics'), "diagnostics"]);
    $navList->appendItem([true, "admin_setup.php?sub=tablecreation", uiTextSnippet('tablecreation'), "tablecreation"]);
    $navList->appendItem([true, "#", uiTextSnippet('templateconfigsettings'), "template"]);
    echo $navList->build("template");
    ?>
    <form action="admin_updatetemplateconfig.php" method='post' name="form1" ENCTYPE="multipart/form-data">
      <table class='table table-sm'>
        <tr>
          <td>
            <label for="form_templateswitching"><?php echo uiTextSnippet('templateswitching'); ?>:</label>
            <select name="form_templateswitching" id="form_templateswitching">
              <option value='0'<?php echo " selected"; ?>><?php echo uiTextSnippet('no'); ?></option>
            </select>
          </td>
        </tr>
        <tr>
          <td>
            <label for="form_templatenum"><b><?php echo uiTextSnippet('template'); ?>:</b></label>
            <?php
            chdir($rootpath . $endrootpath . $templatespath);
            $totaltemplates = 0;
            $sections = array();
            $entries = array();
            $folders = array();
            if ($handle = opendir('.')) {
              while ($filename = readdir($handle)) {
                if (is_dir($filename) && $filename != "." && $filename != "..") {
                  $i = substr($filename, 0, 8) == "template" && is_numeric(substr($filename, 8)) ? substr($filename, 8) : $filename;
                  $totaltemplates++;
                  $sections['t' . $i] = "";
                  $entries[] = $i;
                  $folders['t' . $i] = $filename;
                }
              }
              closedir($handle);
            }
            natcasesort($entries);
            ?>
            <select name="form_templatenum" id="form_templatenum"
                    onchange="switchTemplates($(this).val());">
              <option value=''></option>
              <?php
              foreach ($entries as $entry) {
                echo "<option value=\"$entry\"";
                if ("" == $entry) {
                  echo " selected";
                }
                $tprefix = is_numeric($entry) ? uiTextSnippet('template') . " " : "";
                echo ">$tprefix$entry</option>\n";
              }
              ?>
            </select>
            <button id="previewbtn"><span class="prevmsg"><?php echo uiTextSnippet('showprev'); ?></span><span
                      class="prevmsg" style="display:none"><?php echo uiTextSnippet('hideprev'); ?></span></button>
            <input name='submittop' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
            <br>

            <div style="display:none" id="previewscroll" class="scroller">
              <br>
              <div style="position:absolute">
                <?php
                foreach ($entries as $i) {
                  $newtemplatepfx = is_numeric($i) ? "template" : "";
                  echo "<div class=\"prevdiv\" id=\"prev$i\"><span class=\"prevnum\">$i:</span>";
                  if (file_exists("{$rootpath}{$endrootpath}templates/$newtemplatepfx$i/img/preview1sm.jpg")) {
                    echo "<img src=\"templates/$newtemplatepfx$i/img/preview1sm.jpg\" id=\"preview-$i\" class=\"temppreview\">";
                  }
                  if (file_exists("{$rootpath}{$endrootpath}templates/$newtemplatepfx$i/img/preview2sm.jpg")) {
                    echo "<img src=\"templates/$newtemplatepfx$i/img/preview2sm.jpg\" id=\"preview-$i\" class=\"temppreview\"> &nbsp;&nbsp;\n";
                  }
                  echo "</div>\n";
                }
                ?>
              </div>
            </div>

            <br><br>
            <?php
            $textareas = array('mainpara', 'searchpara', 'fhpara', 'fhlinkshis', 'fhlinkshers', 'mwpara', 'featurepara', 'respara', 'featurelinks', 'reslinks', 'headtitle', 'headsubtitle', 'latestnews', 'featurepara1', 'featurepara2', 'featurepara3', 'featurepara4', 'photocaption', 'newstext', 'featurespara', 'photocaptionl', 'photocaptionr');
            //needtrans: these fields can be duplicated in another language
            $needtrans = array('headline', 'maintitle', 'welcome', 'hisside', 'herside', 'headtitle1', 'headtitle2', 'headtitle3', 'momside', 'dadside', 'mainpara', 'featurepara', 'searchpara', 'fhpara', 'mwpara', 'respara', 'headtitle', 'headsubtitle', 'latestnews', 'featuretitle1', 'featuretitle2', 'featuretitle3', 'featuretitle4', 'featurepara1', 'featurepara2', 'featurepara3', 'featurepara4', 'photocaption', 'newstext', 'menutitle', 'phototitlel', 'photocaptionl', 'phototitler', 'photocaptionr', 'topsurnames', 'featurespara');
            foreach ($tmp as $key => $value) {
              $parts = explode("_", $key);
              $n = $parts[0];
              $label = $parts[1];
              $value = preg_replace("/\"/", "&#34;", $value);
              $sections[$n] .= "<tr id=\"$key\">\n";
              if (in_array($label, $textareas)) {
                $type = "textarea";
                $align = "";
              } else {
                $type = "text";
                $align = "";
              }
              $sections[$n] .= "<td$align>";
              $sections[$n] .= isset($parts[2]) ? "&nbsp;&nbsp;" . uiTextSnippet($label) . ":" : uiTextSnippet($label) . ":";
              $sections[$n] .= isset($parts[2]) ? "<br>&nbsp;&nbsp;&nbsp;&nbsp;(" . $languageArray[$parts[2]] . ")" : "";
              $sections[$n] .= "</td>\n";
              $sections[$n] .= "<td>";
              if ($type == "textarea") {
                $sections[$n] .= "<textarea name=\"form_$key\" id=\"form_$key\" rows=\"5\" cols=\"80\">$value</textarea>\n";
              } elseif ($label == "titlechoice") {
                $sections[$n] .= "<input id=\"form_{$key}_image\" name=\"form_$key\" type='radio' value=\"image\"";
                if ($value == "image") {
                  $sections[$n] .= " checked";
                }
                $sections[$n] .= "> <label for=\"form_{$key}_image\">" . uiTextSnippet('ttitleimage') . "</label> &nbsp;";
                $sections[$n] .= "<input id=\"form_{$key}_text\" name=\"form_$key\" type='radio' value=\"text\"";
                if ($value == "text") {
                  $sections[$n] .= " checked";
                }
                $sections[$n] .= "> <label for=\"form_{$key}_text\">" . uiTextSnippet('ttitletext') . "</label> &nbsp;";
              } else {
                $sections[$n] .= "<input class='longfield' id=\"form_$key\" name=\"form_$key\" type='text' value=\"$value\"/>\n";
                if (strpos($key, "img") !== false || strpos($key, "image") !== false || strpos($key, "thumb") !== false || strpos($key, "photol") !== false || strpos($key, "photor") !== false) {
                  $sections[$n] .= " <input type='button' onclick=\"return preview('templates/{$folders[$n]}/$value');\" value=\"" .
                          uiTextSnippet('preview') . "\" /> <input type='button' onclick=\"return showUploadBox('$key','{$folders[$n]}');\" value=\"" .
                          uiTextSnippet('change') . "\" />\n";
                  $size = getimagesize($rootpath . "templates/{$folders[$n]}/$value");
                  if ($size) {
                    $imagesize1 = $size[0];
                    $imagesize2 = $size[1];
                    $sections[$n] .= " &nbsp; $imagesize1 x $imagesize2 px\n";
                  }
                  $sections[$n] .= "<div id=\"div_$key\" style=\"display:none\"></div>";
                }
              }
              if ($languageList && !isset($parts[2]) && in_array($label, $needtrans)) {
                if ($type == "textarea") {
                  $sections[$n] .= "<br>";
                }
                $sections[$n] .= uiTextSnippet('createcopy') . ": \n<select id=\"lang_$key\">\n$languageList\n</select> <input type='button' value=\"" .
                        uiTextSnippet('go') . "\" onclick=\"return insertLangRow('$key','$type');\" />\n";
              }
              $sections[$n] .= "</td>\n</tr>\n";
            }
            //debugPrint($sections);
            foreach ($entries as $i) {
              $section = $sections['t' . $i];
              if ($section) {
                $dispstr = "" != $i ? " style=\"display:none\"" : "";
                echo "<div$dispstr class=\"tsection\" id=\"t$i\">\n<table class=\"tstable\">\n";
                $newtemplatepfx = is_numeric($i) ? "template" : "";
                $imagetext = "";
                if (file_exists("{$rootpath}templates/$newtemplatepfx$i/img/preview1.jpg")) {
                  $imagetext .= "<img src=\"templates/$newtemplatepfx$i/img/preview1.jpg\" id=\"preview1\" class=\"temppreview\"> ";
                }
                if (file_exists("{$rootpath}templates/$newtemplatepfx$i/img/preview2.jpg")) {
                  $imagetext .= " &nbsp; <img src=\"templates/$newtemplatepfx$i/img/preview2.jpg\" id=\"preview2\" class=\"temppreview\">\n";
                }
                if ($imagetext) {
                  echo "$imagetext<br>";
                }
                echo "<p><b>" . uiTextSnippet('folder') . ": templates/" . $folders['t' . $i] . "</b></p>";
                echo "$section</table>\n</div>\n";
              }
            }
            ?>
            <br>
            <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
          </td>
        </tr>
      </table>
    </form>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src='js/admin.js'></script>
  <script src='js/mediautils.js'></script>
  <script>
    function switchTemplates(newtemp) {
      $('div.tsection').each(function (index, item) {
        item.style.display = item.id === "t" + newtemp ? '' : 'none';
      });
    }

    function insertCell(row, index, content) {
      var cell = row.insertCell(index);
      cell.innerHTML = content ? content : content + '&nbsp;';
      if (!index)
        cell.vAlign = "top";
      return cell;
    }

    function insertLangRow(rowID, type) {
      var row;
      var language = $('#lang_' + rowID);
      var langVal = language.val();
      if (langVal && !$('#form_' + rowID + '_' + langVal).length) {
        row = document.getElementById(rowID);
        var langElem = language[0];
        var langDisplay = langElem.options[langElem.selectedIndex].innerHTML;
        var table = row.parentNode;
        var newtr = table.insertRow(row.rowIndex + 1);
        var label = "&nbsp;&nbsp;" + $('#' + rowID + ' :first-child').html();
        insertCell(newtr, 0, label + "<br>&nbsp;&nbsp;&nbsp;(" + langDisplay + ")");
        var inputstr = type === "textarea" ? "<textarea name=\"form_" + rowID + "_" + langVal + "\" id=\"form_" + rowID + "_" + langVal + "\" rows=\"3\" cols=\"80\"></textarea>" : "<input class='longfield' id=\"form_" + rowID + "_" + langVal + "\" name=\"form_" + rowID + "_" + langVal + "\" type='text'>";
        insertCell(newtr, 1, inputstr);
        insertCell(newtr, 2, "");
      }
      return false;
    }

    function showUploadBox(key, folder) {
      $('#div_' + key).html("<input type=\"file\" name=\"upload_" + key + "\" onchange=\"populateFileName(this,$('#form_" + key + "'));\"/> <?php echo uiTextSnippet('or'); ?> <input type='button' value=\"<?php echo uiTextSnippet('select'); ?>\" name=\"photoselect_" + key + "\" onclick=\"javascript:FilePicker('form_" + key + "','" + folder + "');\" />");
      $('#div_' + key).toggle();
      return false;
    }

    function populateFileName(source, dest) {
      var temp = source.value.replace(/\\/g, "/");
      var lastslash = temp.lastIndexOf("/") + 1;
      dest.val(lastslash > 0 ? 'img/' + source.value.slice(lastslash) : 'img/' + source.value);
    }

    function preview(sFileName) {
      window.open(escape(sFileName), "File", "width=400,height=250,status=no,resizable=yes,scrollbars=yes");
      return false;
    }

    $(document).ready(function () {
      $('#previewbtn').click(function (e) {
        e.preventDefault();
        $('#previewscroll').toggle();
        $('.prevmsg').toggle();
        return false;
      });
      $('.prevdiv').click(function (e) {
        e.preventDefault();
        var id = this.id.substring(4);
        $('#form_templatenum').val(id);
        switchTemplates(id);
        return false;
      });
    });
  </script>
</body>
</html>
