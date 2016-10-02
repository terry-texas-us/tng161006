<?php
require 'begin.php';
require $subroot . 'mapconfig.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

$exptime = 0;

$searchstring_noquotes = stripslashes(preg_replace('/\"/', '&#34;', $searchstring));
$searchstring = addslashes($searchstring);

if ($newsearch) {
  setcookie('tng_search_notes_post[search]', $searchstring_noquotes, $exptime);
  setcookie('tng_search_notes_post[tngpage]', 1, $exptime);
  setcookie('tng_search_notes_post[offset]', 0, $exptime);
  setcookie('tng_search_notes_post[private]', $private, $exptime);
} else {
  if (!$searchstring) {
    $searchstring_noquotes = $_COOKIE['tng_search_notes_post']['search'];
    $searchstring = preg_replace('/&#34;/', '\\\"', $searchstring_noquotes);
  }
  if (!$private) {
    $private = $_COOKIE['tng_search_notes_post']['private'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_notes_post']['tngpage'];
    $offset = $_COOKIE['tng_search_notes_post']['offset'];
  } else {
    setcookie('tng_search_notes_post[tngpage]', $tngpage, $exptime);
    setcookie('tng_search_notes_post[offset]', $offset, $exptime);
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
$wherestr = 'WHERE xnotes.ID = notelinks.xnoteID';

if ($private) {
  $wherestr .= ' AND notelinks.secret != 0';
}
if ($searchstring) {
  $wherestr .= $wherestr ? ' AND' : 'WHERE';
  $wherestr .= " (xnotes.note LIKE '%" . $searchstring . "%')";
}
$query = 'SELECT xnotes.ID AS ID, xnotes.note AS note FROM (xnotes, notelinks)' . $wherestr . " ORDER BY note LIMIT $newoffset" . $maxsearchresults;

$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = 'SELECT count(xnotes.ID) AS scount FROM (xnotes, notelinks) ' . $wherestr;
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['scount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('notes'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
<body id="misc-notes">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('misc-notes', $message);
    ?>
    <br>
    <div>
      <form name='form1' id='form1' action="notesBrowse.php">
        <div class='row'>
          <div class='col-sm-2'><?php echo uiTextSnippet('searchfor'); ?>: </div>
          <div class='col-sm-4'>
            <input class='form-control' name='searchstring' type='text' value="<?php echo $searchstring_noquotes; ?>">
          </div>
          <div class='col-sm-6'>
            <input class='btn btn-outline-primary' name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
            <input class='btn btn-outline-secondary' name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>"
                   onClick="resetForm();">
          </div>
        </div>
        <div class='row'>
          <div class='offset-sm-2 col-sm-6'>
          <td><input name='private' type='checkbox' value='yes'<?php if ($private == 'yes') {echo ' checked';} ?>> <?php echo uiTextSnippet('private'); ?>
          </div>
        </div>

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
      <form action="admin_deleteselected.php" method='post' name="form2">
        <?php
        if ($allowDelete) {
          ?>
          <p>
            <button class='btn btn-sm btn-outline-secondary' id='selectall-notes' name='selectall' type='button'><?php echo uiTextSnippet('selectall'); ?></button> 
            <button class='btn btn-sm btn-outline-secondary' id='clearall-notes' name='clearall' type='button'><?php echo uiTextSnippet('clearall'); ?></button>
            <input class='btn btn-sm btn-outline-warning' id='deleteselected-notes' name='xnoteaction' type='submit' value="<?php echo uiTextSnippet('deleteselected'); ?>">
          </p>
          <?php
        }
        ?>
        <table class="table table-sm">
          <thead>
            <tr>
              <th><?php echo uiTextSnippet('action'); ?></th>
              <?php if ($allowDelete) { ?>
                <th><?php echo uiTextSnippet('select'); ?></th>
              <?php } ?>
              <th><?php echo uiTextSnippet('note'); ?></th>
              <th width='20%'><?php echo uiTextSnippet('linkedto'); ?></th>
            </tr>
          </thead>
          <?php
          if ($numrows) {
          $actionstr = '';
          if ($allowEdit) {
            $actionstr .= "<a href=\"notesEdit.php?ID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
            $actionstr .= "</a>\n";
          }
          if ($allowDelete) {
            $actionstr .= "<a href='#' onClick=\"return confirmDelete('xxx');\" title='" . uiTextSnippet('delete') . "'>\n";
            $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
            $actionstr .= "</a>\n";
          }

          while ($row = tng_fetch_assoc($result)) {
            $newactionstr = preg_replace('/xxx/', $row['ID'], $actionstr);
            echo "<tr id=\"row_{$row['ID']}\"><td><div class=\"action-btns2\">$newactionstr</div></td>\n";
            if ($allowDelete) {
              echo "<td><input class='selected' name=\"del{$row['ID']}\" type='checkbox' value='1'></td>";
            }
            $query = "SELECT notelinks.ID, notelinks.persfamID AS personID, secret FROM notelinks WHERE notelinks.xnoteID = '{$row['ID']}' ";

            $nresult = tng_query($query);
            $linkedTo = '';
            while ($nrow = tng_fetch_assoc($nresult)) {
              if (!$linkedTo) {
                $query = "SELECT * FROM people WHERE personID = \"{$nrow['personID']}\"";
                $result2 = tng_query($query);
                if (tng_num_rows($result2) == 1) {
                  $row2 = tng_fetch_assoc($result2);
                  $nrights = determineLivingPrivateRights($row2);
                  $row2['allow_living'] = $nrights['living'];
                  $row2['allow_private'] = $nrights['private'];
                  $linkedTo .= "<li><a href=\"peopleShowPerson.php?personID={$row2['personID']}\" target='_blank'>" . getNameRev($row2) . "</a></li>\n";
                  tng_free_result($result2);
                }
              }
              if (!$linkedTo) {
                $query = "SELECT * FROM families WHERE familyID = \"{$nrow['personID']}\"";
                $result2 = tng_query($query);
                if (tng_num_rows($result2) == 1) {
                  $row2 = tng_fetch_assoc($result2);
                  $nrights = determineLivingPrivateRights($row2);
                  $row2['allow_living'] = $nrights['living'];
                  $row2['allow_private'] = $nrights['private'];
                  $linkedTo .= "<li><a href=\"familiesShowFamily.php?familyID={$row2['familyID']}\" target='_blank'>" . uiTextSnippet('family') . " ({$row2['familyID']})</a></li>\n";
                  tng_free_result($result2);
                }
              }
              if (!$linkedTo) {
                $query = "SELECT * FROM sources WHERE sourceID = \"{$nrow['personID']}\"";
                $result2 = tng_query($query);
                if (tng_num_rows($result2) == 1) {
                  $row2 = tng_fetch_assoc($result2);
                  $linkedTo .= "<li><a href=\"sourcesShowSource.php?sourceID={$row2['sourceID']}\" target='_blank'>" . uiTextSnippet('source') . " $sourcetext ({$row2['sourceID']})</a></li>\n";
                  tng_free_result($result2);
                }
              }
              if (!$linkedTo) {
                $query = "SELECT * FROM repositories WHERE repoID = \"{$nrow['personID']}\"";
                $result2 = tng_query($query);
                if (tng_num_rows($result2) == 1) {
                  $row2 = tng_fetch_assoc($result2);
                  $linkedTo .= "<li><a href=\"repositoriesShowItem.php?repoID={$row2['repoID']}\" target='_blank'>" . uiTextSnippet('repository') . " $sourcetext ({$row2['repoID']})</a></li>\n";
                  tng_free_result($result2);
                }
              }
            }
            tng_free_result($nresult);

            if (($allowEdit) || !$row['secret']) {
              $notetext = cleanIt($row['note']);
              $notetext = truncateIt($notetext, 500);
              if (!$notetext) {
                $notetext = '&nbsp;';
              }
            } else {
              $notetext = uiTextSnippet('private');
            }
            echo "<td>$notetext</td>\n";
            echo $treetext;
            echo "<td>\n<ul>\n$linkedTo\n</ul>\n</td></tr>\n";
          }
          ?>
        </table>
      <?php
      echo buildSearchResultPagination($totrows, "notesBrowse.php?searchstring=$searchstring_noquotes&amp;offset", $maxsearchresults, 5);
      }
      else {
        echo "</table>\n" . uiTextSnippet('norecords');
      }
      tng_free_result($result);
      ?>
      </form>

    </div>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script>
    function validateForm() {
      var rval = true;
      if (document.form1.searchstring.value.length === 0) {
        alert(textSnippet('entersearchvalue'));
        rval = false;
      }
      return rval;
    }

    $('#selectall-notes').on('click', function () {
        $('.selected').prop('checked', true);
    });

    $('#clearall-notes').on('click', function () {
        $('.selected').prop('checked', false);
    });

    $('#deleteselected-notes').on('click', function () {
        return confirm(textSnippet('confdeleterecs'));
    });
  
    function confirmDelete(ID) {
      if (confirm(textSnippet('confdeletenote')))
        deleteIt('note', ID);
      return false;
    }

    function resetForm() {
      document.form1.searchstring.value = '';
    }
  </script>
  <script src="js/admin.js"></script>
</body>
</html>
