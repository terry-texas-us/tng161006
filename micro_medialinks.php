<?php
$linkrows = "";
$usetree = $row['gedcom'];
if ($result2) {
  $oldlinks = 0;
  while ($plink = tng_fetch_assoc($result2)) {
    $oldlinks++;
    if (!$usetree) {
      $usetree = $plink['gedcom'];
    }
    $rights = determineLivingPrivateRights($plink);
    $plink['allow_living'] = $rights['living'];
    $plink['allow_private'] = $rights['private'];
    if ($plink['personID2'] != null) {
      $type = "person";
      $entityID = $plink['personID'];
      $id = " ($entityID)";
      $name = getName($plink);
      $linktype = 'I';
    } elseif ($plink['familyID'] != null) {
      $type = "family";
      $husb['firstname'] = $plink['hfirstname'];
      $husb['lnprefix'] = $plink['hlnprefix'];
      $husb['lastname'] = $plink['hlastname'];
      $husb['prefix'] = $plink['hprefix'];
      $husb['suffix'] = $plink['hsuffix'];
      $husb['nameorder'] = $plink['hnameorder'];
      $husb['allow_living'] = $husb['allow_private'] = 1;
      $name = getName($husb);

      $wife['firstname'] = $plink['wfirstname'];
      $wife['lnprefix'] = $plink['wlnprefix'];
      $wife['lastname'] = $plink['wlastname'];
      $wife['prefix'] = $plink['wprefix'];
      $wife['suffix'] = $plink['wsuffix'];
      $wife['nameorder'] = $plink['wnameorder'];
      $wife['allow_living'] = $wife['allow_private'] = 1;
      $wifename = getName($wife);

      if ($wifename) {
        if ($name) {
          $name .= ", ";
        }
        $name .= $wifename;
      }
      $entityID = $plink['familyID'];
      $id = " ($entityID)";
      $linktype = 'F';
    } elseif ($plink['sourceID'] != null) {
      $type = "source";
      $entityID = $plink['sourceID'];
      $id = " ($entityID)";
      $name = truncateIt($plink['title'], 100);
      $linktype = 'S';
    } elseif ($plink['repoID'] != null) {
      $type = "repository";
      $entityID = $plink['repoID'];
      $id = " ($entityID)";
      $name = truncateIt($plink['reponame'], 100);
      $linktype = 'R';
    } else { //place
      $type = "place";
      $entityID = $name = $plink['personID'];
      $id = "";
      $linktype = 'L';
    }

    $dchecked = $plink['defphoto'] ? " checked" : "";
    $schecked = $plink['dontshow'] ? "" : " checked";
    $alttext = $plink['altdescription'] || $plink['altnotes'] ? uiTextSnippet('yes') : "&nbsp;";

    include("eventmicro.php");

    $linkrows .= "<tr id=\"alink_{$plink['mlinkID']}\"><td>";
    $linkrows .= "<a href='#' onclick=\"return editMedia2EntityLink({$plink['mlinkID']});\" title='" . uiTextSnippet('edit') . "'>\n";
    $linkrows .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
    $linkrows .= "</a>";
    $linkrows .= "<a href='#' onclick=\"return deleteMedia2EntityLink({$plink['mlinkID']});\" title='" . uiTextSnippet('removelink') . "'>\n";
    $linkrows .= "<img class='icon-sm' src='svg/link.svg'>\n";
    $linkrows .= "</a>";
    $linkrows .= "</td>\n";
    $linkrows .= "<td>" . uiTextSnippet($type) . "</td>\n";
    $linkrows .= "<td>$name$id (<a href=\"admin_ordermedia.php?tree1={$plink['gedcom']}&linktype1=$linktype&mediatypeID=$mediatypeID&newlink1=$entityID&event1=$eventID\">" . uiTextSnippet('text_sort') . "</a>)&nbsp;</td>\n";
    $linkrows .= "<td>{$plink['treename']}</td>\n";
    $linkrows .= "<td id=\"event_{$plink['mlinkID']}\">$eventstr&nbsp;</td>\n";
    $linkrows .= "<td id=\"alt_{$plink['mlinkID']}\">$alttext</td>\n";
    $linkrows .= "<td id=\"def_{$plink['mlinkID']}\"><input id=\"defc{$plink['mlinkID']}\" name=\"defc{$plink['mlinkID']}\" type='checkbox' onclick=\"toggleDefault(this,'{$plink['gedcom']}','$entityID');\" value='1'$dchecked\"/></td>\n";
    $linkrows .= "<td id=\"show_{$plink['mlinkID']}\"><input id=\"show{$plink['mlinkID']}\" name=\"show{$plink['mlinkID']}\" type='checkbox' onclick=\"toggleShow(this);\" value='1'$schecked\"/></td></tr>\n";
  }
  tng_free_result($result2);
}
?>
<div id="links" style="margin:0;padding-top:12px">
  <table>
    <tr>
      <td><?php echo uiTextSnippet('tree'); ?></td>
      <td><?php echo uiTextSnippet('linktype'); ?></td>
      <td colspan='2'><?php echo uiTextSnippet('id'); ?></td>
    </tr>
    <tr>
      <td>
        <select name="tree1" id="microtree">
          <?php
          for ($j = 1; $j <= $treenum; $j++) {
            echo "  <option value=\"{$trees[$j]}\"";
            if ($trees[$j] == $usetree) {
              echo " selected";
            }
            echo ">$treename[$j]</option>\n";
          }
          ?>
        </select>
      </td>
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
        <input id='newlink' name='newlink1' type='text' value=''
                 onkeypress="return newlinkEnter(findform, this, event);">
      </td>
      <!--<td><input type='submit' value="<?php echo uiTextSnippet('add'); ?>"> <?php echo uiTextSnippet('text_or'); ?>
      <input name='find1' type='button' value="<?php echo uiTextSnippet('find'); ?>" onClick="findopen=true;openFind(document.find.linktype1.options[document.find.linktype1.selectedIndex].value);$('newlines').innerHTML=resheremsg;"></td>-->
      <td><input type='button' value="<?php echo uiTextSnippet('add'); ?>"
                 onclick="return addMedia2EntityLink(findform);"> &nbsp;<?php echo uiTextSnippet('text_or'); ?>
        &nbsp;</td>
      <td>
        <a href='#' onclick="return findItem(findform.linktype1.options[findform.linktype1.selectedIndex].value, 'newlink', null, findform.tree1.options[findform.tree1.selectedIndex].value, '<?php echo $assignedbranch; ?>', 'm_<?php echo $mediaID; ?>');"
           title="<?php echo uiTextSnippet('find'); ?>">
          <img class='icon-sm' src='svg/magnifying-glass.svg'>
        </a>
      </td>
    </tr>
  </table>
  <div id="alink_error" style="display:none;" class="red"></div>

  <p>&nbsp;<strong><?php echo uiTextSnippet('existlinks'); ?>
      :</strong> <?php echo uiTextSnippet('eloptions'); ?></p>
  <table id="linktable">
    <tbody>
    <tr>
      <td><?php echo uiTextSnippet('action'); ?></td>
      <td><?php echo uiTextSnippet('linktype'); ?></td>
      <td><?php echo uiTextSnippet('name') . ", " . uiTextSnippet('id'); ?></td>
      <td><?php echo uiTextSnippet('tree'); ?></td>
      <td><?php echo uiTextSnippet('event'); ?></td>
      <td><?php echo uiTextSnippet('alttd'); ?></td>
      <td><?php echo uiTextSnippet('defphoto'); ?></td>
      <td><?php echo uiTextSnippet('show'); ?></td>
    </tr>
    <?php
    echo $linkrows;
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
