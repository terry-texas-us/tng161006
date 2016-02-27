<?php
include("begin.php");
include($subroot . "mapconfig.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

$orgtree = $tree;
$exptime = 0;

$searchstring_noquotes = stripslashes(preg_replace("/\"/", "&#34;", $searchstring));
$searchstring = addslashes($searchstring);

if ($newsearch) {
  setcookie("tng_search_notes_post[search]", $searchstring_noquotes, $exptime);
  setcookie("tng_search_notes_post[tree]", $tree, $exptime);
  setcookie("tng_search_notes_post[tngpage]", 1, $exptime);
  setcookie("tng_search_notes_post[offset]", 0, $exptime);
  setcookie("tng_search_notes_post[private]", $private, $exptime);
} else {
  if (!$searchstring) {
    $searchstring_noquotes = $_COOKIE['tng_search_notes_post']['search'];
    $searchstring = preg_replace("/&#34;/", "\\\"", $searchstring_noquotes);
  }
  if (!$private) {
    $private = $_COOKIE['tng_search_notes_post']['private'];
  }
  if (!$tree) {
    $tree = $_COOKIE['tng_search_notes_post']['tree'];
  }
  if (!isset($offset)) {
    $tngpage = $_COOKIE['tng_search_notes_post']['tngpage'];
    $offset = $_COOKIE['tng_search_notes_post']['offset'];
  } else {
    setcookie("tng_search_notes_post[tngpage]", $tngpage, $exptime);
    setcookie("tng_search_notes_post[offset]", $offset, $exptime);
  }
}

if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $tngpage = 1;
}

if ($assignedtree) {
  $tree = $assignedtree;
  $wherestr = "WHERE gedcom = \"$assignedtree\"";
} else {
  $wherestr = "";
}
$treequery = "SELECT gedcom, treename FROM $trees_table $wherestr ORDER BY treename";

$wherestr = "WHERE $xnotes_table.ID = $notelinks_table.xnoteID";

if ($tree) {
  $wherestr .= " AND $xnotes_table.gedcom = \"$tree\"";
}

if ($private) {
  $wherestr .= " AND $notelinks_table.secret != 0";
}

if ($searchstring) {
  $wherestr .= $wherestr ? " AND" : "WHERE";
  $wherestr .= " ($xnotes_table.note LIKE '%" . $searchstring . "%')";
}

$query = "SELECT $xnotes_table.ID as ID, $xnotes_table.note as note, $xnotes_table.gedcom as gedcom
    FROM ($xnotes_table, $notelinks_table)" . $wherestr . " ORDER BY note LIMIT $newoffset" . $maxsearchresults;

$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count($xnotes_table.ID) as scount FROM ($xnotes_table, $notelinks_table) " . $wherestr;
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['scount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('notes'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body id="misc-notes">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('misc-notes', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_misc.php", uiTextSnippet('menu'), "misc"]);
    $navList->appendItem([true, "admin_notelist.php", uiTextSnippet('notes'), "notes"]);
    $navList->appendItem([true, "admin_whatsnewmsg.php", uiTextSnippet('whatsnew'), "whatsnew"]);
    $navList->appendItem([true, "admin_mostwanted.php", uiTextSnippet('mostwanted'), "mostwanted"]);
    echo $navList->build("notes");
    ?>

    <table class='table table-sm'>
      <tr>
        <td>
          <div>

            <form action="admin_notelist.php" name='form1' id='form1'>
              <table>
                <tr>
                  <td><?php echo uiTextSnippet('searchfor'); ?>:</td>
                  <td>
                    <select name='tree'>
                      <?php
                      if (!$assignedtree) {
                        echo "  <option value=''>" . uiTextSnippet('alltrees') . "</option>\n";
                      }
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
                    <input class='longfield' name='searchstring' type='text' value="<?php echo $searchstring_noquotes; ?>">
                  </td>
                  <td>
                    <input name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
                    <input name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>"
                           onClick="resetForm();">
                  </td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td><input name='private' type='checkbox' value='yes'<?php if ($private == "yes") {echo " checked";} ?>> <?php echo uiTextSnippet('private'); ?>
                  </td>
                </tr>
              </table>

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
              if ($allow_delete) {
                ?>
                <p>
                  <input name='selectall' type='button' value="<?php echo uiTextSnippet('selectall'); ?>" 
                         onClick="toggleAll(1);">
                  <input name='clearall' type='button' value="<?php echo uiTextSnippet('clearall'); ?>"
                         onClick="toggleAll(0);">
                  <input name='xnoteaction' type='submit' value="<?php echo uiTextSnippet('deleteselected'); ?>"
                         onClick="return confirm('<?php echo uiTextSnippet('confdeleterecs'); ?>');">
                </p>
                <?php
              }
              ?>
              <table class="table table-sm table-striped">
                <tr>
                  <th><?php echo uiTextSnippet('action'); ?></th>
                  <?php
                  if ($allow_delete) {
                    ?>
                    <th><?php echo uiTextSnippet('select'); ?></th>
                    <?php
                  }
                  ?>
                  <th><?php echo uiTextSnippet('note'); ?></th>
                  <?php
                  if (!$tree) {
                    ?>
                    <th><?php echo uiTextSnippet('tree'); ?></th>
                    <?php
                  }
                  ?>
                  <th><?php echo uiTextSnippet('linkedto'); ?></th>
                </tr>
                <?php
                if ($numrows) {
                $actionstr = "";
                if ($allow_edit) {
                  $actionstr .= "<a href=\"admin_editnote2.php?ID=xxx\" title='" . uiTextSnippet('edit') . "'>\n";
                  $actionstr .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
                  $actionstr .= "</a>\n";
                }
                if ($allow_delete) {
                  $actionstr .= "<a href='#' onClick=\"return confirmDelete('xxx');\" title='" . uiTextSnippet('delete') . "'>\n";
                  $actionstr .= "<img class='icon-sm' src='svg/trash.svg'>\n";
                  $actionstr .= "</a>\n";
                }

                while ($row = tng_fetch_assoc($result)) {
                  $newactionstr = preg_replace("/xxx/", $row['ID'], $actionstr);
                  echo "<tr id=\"row_{$row['ID']}\"><td><div class=\"action-btns2\">$newactionstr</div></td>\n";
                  if ($allow_delete) {
                    echo "<td><input name=\"del{$row['ID']}\" type='checkbox' value='1'></td>";
                  }

                  $query = "SELECT $notelinks_table.ID, $notelinks_table.persfamID as personID, $notelinks_table.gedcom, secret
          FROM $notelinks_table
          WHERE $notelinks_table.xnoteID = \"{$row['ID']}\" ";

                  $nresult = tng_query($query);
                  $notelinktext = "";
                  while ($nrow = tng_fetch_assoc($nresult)) {
                    $treetext = "";
                    if (!$tree) {
                      $query = "SELECT treename FROM " . $trees_table . " WHERE gedcom = \"{$nrow['gedcom']}\"";
                      $result2 = tng_query($query);
                      $row2 = tng_fetch_assoc($result2);
                      $treetext = "<td>" . $row2['treename'] . "</td>";
                      tng_free_result($result2);
                    }

                    if (!$notelinktext) {
                      $query = "SELECT * FROM $people_table WHERE personID = \"{$nrow['personID']}\" AND gedcom = \"{$nrow['gedcom']}\"";
                      $result2 = tng_query($query);
                      if (tng_num_rows($result2) == 1) {
                        $row2 = tng_fetch_assoc($result2);
                        $nrights = determineLivingPrivateRights($row2);
                        $row2['allow_living'] = $nrights['living'];
                        $row2['allow_private'] = $nrights['private'];
                        $notelinktext .= "<li><a href=\"getperson.php?personID={$row2['personID']}&amp;tree={$row2['gedcom']}\" target='_blank'>" . getNameRev($row2) . " ({$row2['personID']})</a></li>\n";
                        tng_free_result($result2);
                      }
                    }

                    if (!$notelinktext) {
                      $query = "SELECT * FROM $families_table WHERE familyID = \"{$nrow['personID']}\" AND gedcom = \"{$nrow['gedcom']}\"";
                      $result2 = tng_query($query);
                      if (tng_num_rows($result2) == 1) {
                        $row2 = tng_fetch_assoc($result2);
                        $nrights = determineLivingPrivateRights($row2);
                        $row2['allow_living'] = $nrights['living'];
                        $row2['allow_private'] = $nrights['private'];
                        $notelinktext .= "<li><a href=\"familygroup.php?familyID={$row2['familyID']}&tree={$nrow['gedcom']}\" target='_blank'>" . uiTextSnippet('family') . " {$row2['familyID']}</a></li>\n";
                        tng_free_result($result2);
                      }
                    }

                    if (!$notelinktext) {
                      $query = "SELECT * FROM $sources_table WHERE sourceID = \"{$nrow['personID']}\" AND gedcom = \"{$nrow['gedcom']}\"";
                      $result2 = tng_query($query);
                      if (tng_num_rows($result2) == 1) {
                        $row2 = tng_fetch_assoc($result2);
                        $notelinktext .= "<li><a href=\"showsource.php?sourceID={$row2['sourceID']}&tree={$row2['gedcom']}\" target='_blank'>" . uiTextSnippet('source') . " $sourcetext ({$row2['sourceID']})</a></li>\n";
                        tng_free_result($result2);
                      }
                    }

                    if (!$notelinktext) {
                      $query = "SELECT * FROM $repositories_table WHERE repoID = \"{$nrow['personID']}\" AND gedcom = \"{$nrow['gedcom']}\"";
                      $result2 = tng_query($query);
                      if (tng_num_rows($result2) == 1) {
                        $row2 = tng_fetch_assoc($result2);
                        $notelinktext .= "<li><a href=\"showrepo.php?repoID={$row2['repoID']}&tree={$row2['gedcom']}\" target='_blank'>" . uiTextSnippet('repository') . " $sourcetext ({$row2['repoID']})</a></li>\n";
                        tng_free_result($result2);
                      }
                    }
                  }
                  tng_free_result($nresult);

                  if (($allow_edit && !$assignedtree) || !$row['secret']) {
                    $notetext = cleanIt($row['note']);
                    $notetext = truncateIt($notetext, 500);
                    if (!$notetext) {
                      $notetext = "&nbsp;";
                    }
                  } else {
                    $notetext = uiTextSnippet('private');
                  }
                  echo "<td>$notetext</td>\n";
                  echo $treetext;
                  echo "<td>\n<ul>\n$notelinktext\n</ul>\n</td></tr>\n";
                }
                ?>
              </table>
            <?php
            echo buildSearchResultPagination($totrows, "admin_notelist.php?searchstring=$searchstring_noquotes&amp;offset", $maxsearchresults, 5);
            }
            else {
              echo "</table>\n" . uiTextSnippet('norecords');
            }
            tng_free_result($result);
            ?>
            </form>

          </div>
        </td>
      </tr>
    </table>
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

    function confirmDelete(ID) {
      if (confirm(textSnippet('confdeletenote')))
        deleteIt('note', ID);
      return false;
    }

    function resetForm() {
      document.form1.searchstring.value = '';
      document.form1.tree.selectedIndex = 0;
    }
  </script>
  <script src="js/admin.js"></script>
</body>
</html>