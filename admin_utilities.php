<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

function getfiletime($filename) {
  global $fileflag, $timeOffset;

  $filemodtime = '';
  if ($fileflag) {
    $filemod = filemtime($filename) + (3600 * $timeOffset);
    $filemodtime = date('F j, Y h:i:s A', $filemod);
  }
  return $filemodtime;
}

function getfilesize($filename) {
  global $fileflag;

  $filesize = '';
  if ($fileflag) {
    $filesize = ceil(filesize($filename) / 1000) . ' Kb';
  }
  return $filesize;
}

function doRow($table_name, $display_name) {
  global $rootpath;
  global $backuppath;
  global $fileflag;

  $fileflag = $table_name && file_exists("$rootpath$backuppath/$table_name.bak");

  echo "<tr>\n";
  echo "<td>\n";
  echo "<div class='action-btns'>\n";
  echo "<a href='#' onclick=\"return startOptimize('$table_name');\" title=\"" . uiTextSnippet('optimize') . "\">\n";
  echo "<img class='icon-sm' src='svg/oil-can.svg'>\n";
  echo '</a>';
  echo "<a href='#' onclick=\"return startBackup('$table_name');\" title=\"" . uiTextSnippet('backup') . "\">\n";
  echo "<img class='icon-sm' src='svg/upload.svg'>\n";
  echo '</a>';
  echo "<a id=\"rst_$table_name\" href='#' onclick=\"if( confirm('" . uiTextSnippet('surerestore') . "') ) {startRestore('$table_name') ;} return false;\" title=\"" . uiTextSnippet('restore') . '"';
  echo $fileflag ? '>' : " style='visibility: hidden'>";
  echo "<img class='icon-sm' src='svg/download.svg'>\n";
  echo '</a>';
  echo "</div>\n";
  echo '</td>';
  echo "<td><input class='tablechecks' name=\"$table_name\" type='checkbox' value='1' style=\"margin: 0; padding: 0;\"></td>\n";
  echo "<td>$display_name &nbsp;</td>\n";
  echo "<td><span id=\"time_$table_name\">" . getfiletime("$rootpath$backuppath/$table_name.bak") . "</span>&nbsp;</td>\n";
  echo "<td align=\"right\"><span id=\"size_$table_name\">" . getfilesize("$rootpath$backuppath/$table_name.bak") . "</span>&nbsp;</td>\n";
  echo "<td><span id=\"msg_$table_name\"></span>&nbsp;</td>\n";
  echo "</tr>\n";
}

if (!$sub) {
  $sub = 'tables';
}
header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('backuprestore'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('backuprestore-' . ($sub == 'tables' ? 'backuprestoretables' : 'backupstruct'), $message);
    $navList = new navList('');
    $navList->appendItem([true, 'admin_utilities.php?sub=tables', uiTextSnippet('tables'), 'tables']);
    $navList->appendItem([true, 'admin_utilities.php?sub=structure', uiTextSnippet('tablestruct'), 'structure']);
    $navList->appendItem([true, 'admin_renumbermenu.php', uiTextSnippet('renumber'), 'renumber']);
    echo $navList->build($sub);
    if ($sub == 'tables') {
      ?>
      <p><i><?php echo uiTextSnippet('brinstructions'); ?></i></p>

      <h4><?php echo uiTextSnippet('backuprestoretables'); ?></h4>
      <div>
        <form action="" name='form1' id='form1' onsubmit="return startUtility(document.form1.withsel);">
          <p>
            <input name='table' type='hidden' value='all'>
            <input name='selectall' type='button' value="<?php echo uiTextSnippet('selectall'); ?>" 
                   onclick="toggleAll(1);">
            <input name='clearall' type='button' value="<?php echo uiTextSnippet('clearall'); ?>"
                   onclick="toggleAll(0);">&nbsp;&nbsp;
            <?php echo uiTextSnippet('wsel'); ?>
            <select name="withsel">
              <option value=''></option>
              <option value="backupall"><?php echo uiTextSnippet('backup'); ?></option>
              <option value="optimizeall"><?php echo uiTextSnippet('optimize'); ?></option>
              <option value="restoreall"><?php echo uiTextSnippet('restore'); ?></option>
              <option value="delete"><?php echo uiTextSnippet('delete'); ?></option>
            </select>
            <input name='go' type='submit' value="<?php echo uiTextSnippet('go'); ?>">
          </p>

          <table class="table table-sm table-striped">
            <tr>
              <th><?php echo uiTextSnippet('action'); ?></th>
              <th><?php echo uiTextSnippet('select'); ?></th>
              <th><?php echo uiTextSnippet('table'); ?></th>
              <th><?php echo uiTextSnippet('lastbackup'); ?></th>
              <th><?php echo uiTextSnippet('backupfilesize'); ?></th>
              <th style='width: 200px'><?php echo uiTextSnippet('msg'); ?></th>
            </tr>
            <?php
            doRow($address_table, uiTextSnippet('addresstable'));
            doRow($albums_table, uiTextSnippet('albums'));
            doRow($album2entities_table, uiTextSnippet('album2entitiestable'));
            doRow($albumlinks_table, uiTextSnippet('albumlinkstable'));
            doRow($assoc_table, uiTextSnippet('associations'));
            doRow($branches_table, uiTextSnippet('branches'));
            doRow($branchlinks_table, uiTextSnippet('brlinkstable'));
            doRow($cemeteries_table, uiTextSnippet('cemeteries'));
            doRow($children_table, uiTextSnippet('children'));
            doRow('countries', uiTextSnippet('countriestable'));
            doRow('places', uiTextSnippet('places'));
            doRow($events_table, uiTextSnippet('events'));
            doRow($eventtypes_table, uiTextSnippet('eventtypes'));
            doRow($families_table, uiTextSnippet('families'));
            doRow($languagesTable, uiTextSnippet('languages'));
            doRow($media_table, uiTextSnippet('mediatable'));
            doRow($medialinks_table, uiTextSnippet('medialinkstable'));
            doRow($mediatypes_table, uiTextSnippet('mediatypes'));
            doRow($mostwanted_table, uiTextSnippet('mostwanted'));
            doRow('notelinks', uiTextSnippet('notelinkstable'));
            doRow('xnotes', uiTextSnippet('notes'));
            doRow($people_table, uiTextSnippet('people'));
            doRow($reports_table, uiTextSnippet('reports'));
            doRow($sources_table, uiTextSnippet('sources'));
            doRow($repositories_table, uiTextSnippet('repositories'));
            doRow($citations_table, uiTextSnippet('citations'));
            doRow($saveimport_table, uiTextSnippet('saveimporttable'));
            doRow('states', uiTextSnippet('statestable'));
            doRow('temp_events', uiTextSnippet('temptable'));
            doRow('timelineevents', uiTextSnippet('tleventstable'));
            doRow('trees', uiTextSnippet('trees'));
            doRow('users', uiTextSnippet('users'));
            ?>
          </table>
        </form>
      </div>
    <?php } elseif ($sub == 'structure') { ?>
      <p><i><?php echo uiTextSnippet('brinstructions2'); ?></i></p>

      <h4><?php echo uiTextSnippet('backupstruct'); ?></h4>
      <div>
        <table class="table table-sm table-striped">
          <tr>
            <th><?php echo uiTextSnippet('action'); ?></th>
            <th><?php echo uiTextSnippet('lastbackup'); ?></th>
            <th><?php echo uiTextSnippet('backupfilesize'); ?></th>
          </tr>
          <tr>
            <td>
              <div class="action-btns2">
                <a href="admin_backup.php?table=struct" title="<?php echo uiTextSnippet('backup'); ?>">
                  <img class='icon-sm' src='svg/upload.svg'>
                </a>
                <?php
                if (file_exists("$rootpath$backuppath/tng_tablestructure.bak")) {
                  $fileflag = 1;
                  ?>
                  <a id='restore-table-structure' href="admin_restore.php?table=struct" title="<?php echo uiTextSnippet('restore'); ?>">
                    <img class='icon-sm' src='svg/download.svg'>
                  </a>
                  <?php
                } else {
                  $fileflag = 0;
                }
                ?>
              </div>
            </td>
            <?php
            if ($fileflag) {
              echo '<td>' . getfiletime("$rootpath$backuppath/tng_tablestructure.bak") . "</td>\n";
              echo "<td align='right'><span>" . getfilesize("$rootpath$backuppath/tng_tablestructure.bak") . "</span></td>\n";
            } else {
              echo "<td></td>\n";
              echo "<td align='right'><span>&nbsp;</span></td>\n";
            }
            ?>
          </tr>
        </table>
      </div>
    <?php } ?>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script>
  function toggleAll(flag) {
    for (var i = 0; i < document.form1.elements.length; i++) {
      if (document.form1.elements[i].type === "checkbox") {
        if (flag)
          document.form1.elements[i].checked = true;
        else
          document.form1.elements[i].checked = false;
      }
    }
  }

  function startUtility(sel) {
    if (sel.selectedIndex < 1)
      return false;
    var checks = $('.tablechecks');
    var totalchecked = 0;
    checks.each(function (index, item) {
      if (item.checked) {
        totalchecked = 1;
      }
    });
    if (totalchecked) {
      var selval = sel.options[sel.selectedIndex].value;
      var form = document.form1;
      switch (selval) {
        case "backupall":
          form.action = 'admin_backup.php';
          form.submit();
          break;
        case "optimizeall":
          form.action = 'admin_optimize.php';
          form.submit();
          break;
        case "restoreall":
          if (confirm(textSnippet('surerestore'))) {
            form.action = 'admin_restore.php';
            form.submit();
          }
          break;
        case "delete":
          if (confirm(textSnippet('suredelbk'))) {
            form.table.value = 'del';
            form.action = 'admin_backup.php?table=del';
            form.submit();
          }
          break;
      }
    } else {
      alert(textSnippet('seltable'));
      sel.selectedIndex = 0;
    }
    return false;
  }

  function startBackup(table) {
    var params = {table: table};
    $('#msg_' + table).html('<img src="img/spinner.gif">');
    $.ajax({
      url: 'admin_backup.php',
      data: params,
      dataType: 'html',
      success: function (req) {
        var pairs = req.split('&');
        var table = pairs[0];
        var timestamp = pairs[1];
        var size = pairs[2];
        var message = pairs[3];
        $('#msg_' + table).html(message);
        $('#msg_' + table).effect('highlight', {}, 500);
        $('#time_' + table).html(timestamp);
        $('#time_' + table).effect('highlight', {}, 500);
        $('#size_' + table).html(size);
        $('#size_' + table).effect('highlight', {}, 500);
        $('#rst_' + table).css('visibility', 'visible');
      }
    });
    return false;
  }

  function startOptimize(table) {
    var params = {table: table};
    $('#msg_' + table).html('<img src="img/spinner.gif">');
    $.ajax({
      url: 'admin_optimize.php',
      data: params,
      dataType: 'html',
      success: function (req) {
        var pairs = req.split('&');
        var table = pairs[0];
        var message = pairs[1];
        $('#msg_' + table).html(message);
        $('#msg_' + table).effect('highlight', {}, 500);
      }
    });
    return false;
  }

  function startRestore(table) {
    var params = {table: table};
    $('#msg_' + table).html('<img src="img/spinner.gif">');
    $.ajax({
      url: 'admin_restore.php',
      data: params,
      dataType: 'html',
      success: function (req) {
        var pairs = req.split('&');
        var table = pairs[0];
        var message = pairs[1];
        $('#msg_' + table).html(message);
        $('#msg_' + table).effect('highlight', {}, 500);
      }
    });
    return false;
  }
  $('#restore-table-structure').on('click', function() {
      return confirm(textSnippet('surerestorets'));
  });
</script>
</body>
</html>
