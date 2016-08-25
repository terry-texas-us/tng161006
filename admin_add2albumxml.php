<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';

initMediaTypes();

function getAlbumNav($total, $perpage, $pagenavpages) {
  global $page;
  global $totalpages;
  global $albumID;
  global $searchstring;
  global $mediatypeID;
  global $hsstat;
  global $cemeteryID;

  if (!$page) {
    $page = 1;
  }
  if (!$perpage) {
    $perpage = 50;
  }

  if ($total <= $perpage) {
    return "";
  }

  $totalpages = ceil($total / $perpage);
  if ($page > $totalpages) {
    $page = $totalpages;
  }

  if ($page > 1) {
    $prevpage = $page - 1;
    $navoffset = (($prevpage * $perpage) - $perpage);
    $prevlink = " <a href='#' onclick=\"return getMoreMedia('$searchstring', '$mediatypeID', '$hsstat', '$cemeteryID', '$navoffset', '$prevpage', '$albumID');\" title=\"" . uiTextSnippet('prev') . "\">&laquo;" . uiTextSnippet('prev') . "</a> ";
  }
  if ($page < $totalpages) {
    $nextpage = $page + 1;
    $navoffset = (($nextpage * $perpage) - $perpage);
    $nextlink = "<a href='#' onclick=\"return getMoreMedia('$searchstring', '$mediatypeID', '$hsstat', '$cemeteryID', '$navoffset', '$nextpage', '$albumID');\" title=\"" . uiTextSnippet('next') . "\">" . uiTextSnippet('next') . "&raquo;</a>";
  }
  while ($curpage++ < $totalpages) {
    $navoffset = (($curpage - 1) * $perpage);
    if (($curpage <= $page - $pagenavpages || $curpage >= $page + $pagenavpages) && $pagenavpages) {
      if ($curpage == 1) {
        $firstlink = " <a href='#' onclick=\"return getMoreMedia('$searchstring', '$mediatypeID', '$hsstat', '$cemeteryID', '$navoffset', '$curpage', '$albumID');\" title=\"" . uiTextSnippet('firstpage') . "\">&laquo;1</a> ... ";
      }
      if ($curpage == $totalpages) {
        $lastlink = "... <a href='#' onclick=\"return getMoreMedia('$searchstring', '$mediatypeID', '$hsstat', '$cemeteryID', '$navoffset', '$curpage', '$albumID');\" title=\"" . uiTextSnippet('lastpage') . "\">$totalpages&raquo;</a>";
      }
    } else {
      if ($curpage == $page) {
        $pagenav .= " [$curpage] ";
      } else {
        $pagenav .= " <a href='#' onclick=\"return getMoreMedia('$searchstring', '$mediatypeID', '$hsstat', '$cemeteryID', '$navoffset', '$curpage', '$albumID');\">$curpage</a> ";
      }
    }
  }
  $pagenav = "<span>$prevlink $firstlink $pagenav $lastlink $nextlink</span>";

  return $pagenav;
}

$wherestr = $searchstring ? "($media_table.mediaID LIKE \"%$searchstring%\" OR description LIKE \"%$searchstring%\" OR path LIKE \"%$searchstring%\" OR notes LIKE \"%$searchstring%\" OR owner LIKE \"%$searchstring%\" OR bodytext LIKE \"%$searchstring%\")" : "";
if ($searchtree) {
  $wherestr .= $wherestr ? " AND (gedcom = \"\" OR gedcom = \"$searchtree\")" : "(gedcom = \"\" OR gedcom = \"$searchtree\")";
}
if ($mediatypeID) {
  $wherestr .= $wherestr ? " AND mediatypeID = \"$mediatypeID\"" : "mediatypeID = \"$mediatypeID\"";
}
if ($fileext) {
  $wherestr .= $wherestr ? " AND form = \"$fileext\"" : "form = \"$fileext\"";
}
if ($hsstat) {
  $wherestr .= $wherestr ? " AND status = \"$hsstat\"" : "status = \"$hsstat\"";
}
if ($cemeteryID) {
  $wherestr .= $wherestr ? " AND cemeteryID = \"$cemeteryID\"" : "cemeteryID = \"$cemeteryID\"";
}
if ($wherestr) {
  $wherestr = "WHERE $wherestr";
}

if (isset($offset) && $offset != 0) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offset = 0;
  $offsetplus = 1;
  $newoffset = "";
  $page = 1;
}

$query = "SELECT $media_table.mediaID as mediaID, $medialinkID description, notes, thumbpath, mediatypeID, usecollfolder, datetaken FROM $media_table $join $wherestr ORDER BY description LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);
if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count($media_table.mediaID) as mcount FROM $media_table $join $wherestr";
  $result2 = tng_query($query);
  $row = tng_fetch_assoc($result2);
  $totrows = $row['mcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}

if ($albumID) {
  $query2 = "SELECT mediaID FROM $albumlinks_table WHERE albumID = \"$albumID\"";
  $result2 = tng_query($query2) or die(uiTextSnippet('cannotexecutequery') . ": $query2");
  $alreadygot = [];
  while ($row2 = tng_fetch_assoc($result2)) {
    $alreadygot[] = $row2['mediaID'];
  }
  tng_free_result($result2);
} else {
  $alreadygot[] = [];
}

header("Content-type:text/html; charset=" . $session_charset);

$numrowsplus = $numrows + $offset;
if (!$numrowsplus) {
  $offsetplus = 0;
}
echo "<p>" . uiTextSnippet('matches') . ": $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows";
$pagenav = getAlbumNav($totrows, $maxsearchresults, 5);
echo " &nbsp; <span>$pagenav</span></p>";
?>
  <table>
    <tr>
      <td width="50"><?php echo uiTextSnippet('select'); ?></td>
      <td><?php echo uiTextSnippet('thumb'); ?></td>
      <td><?php echo uiTextSnippet('description'); ?></td>
      <td><?php echo uiTextSnippet('date'); ?></td>
      <td><?php echo uiTextSnippet('mediatype'); ?></td>
    </tr>
    <?php
    while ($row = tng_fetch_assoc($result)) {
      $mtypeID = $row['mediatypeID'];
      $usefolder = $row['usecollfolder'] ? $mediatypes_assoc[$mtypeID] : $mediapath;
      echo "<tr id=\"addrow_{$row['mediaID']}\"><td>";
      echo "<div id=\"add_{$row['mediaID']}\"";
      $gotit = in_array($row['mediaID'], $alreadygot);
      if ($gotit) {
        echo " style=\"display:none\"";
      }
      if ($albumID) {
        echo "><a href='#' onclick=\"return addToAlbum('{$row['mediaID']}');\">" . uiTextSnippet('add') . "</a></div>";
      } else {
        echo "><a href='#' onclick=\"return selectMedia('{$row['mediaID']}');\">" . uiTextSnippet('select') . "</a></div>";
      }
      echo "<div id=\"added_{$row['mediaID']}\"";
      if (!$gotit) {
        echo " style='display: none'>";
      } else {
        echo "><img class='icon-sm' src='svg/eye.svg' alt=''>";
      }
      echo "</div>";
      echo "&nbsp;</td>";
      echo "<td style=\"text-align:center\" id=\"thumbcell_{$row['mediaID']}\">";
      if ($row['thumbpath'] && file_exists("$rootpath$usefolder/" . $row['thumbpath'])) {
        $size = getimagesize("$rootpath$usefolder/" . $row['thumbpath']);
        echo "<a href=\"admin_editmedia.php?mediaID={$row['mediaID']}\" target='_blank'>\n";
          echo "<img src=\"$usefolder/" . str_replace("%2F", "/", rawurlencode($row['thumbpath'])) . "\" $size[3]>\n";
        echo "</a>\n";
      } else {
        echo "&nbsp;";
      }
      echo "</td>\n";
      $truncated = substr($row['notes'], 0, 90);
      $truncated = strlen($row['notes']) > 90 ? substr($truncated, 0, strrpos($truncated, ' ')) . '&hellip;' : $row['notes'];
      echo "<td id=\"desc_{$row['mediaID']}\"><a href=\"admin_editmedia.php?mediaID={$row['mediaID']}\">{$row['description']}</a><br>$truncated &nbsp;</td>";
      echo "<td style=\"width:100px;\" id=\"date_{$row['mediaID']}\">{$row['datetaken']}&nbsp;</td>\n";
      echo "<td><span id=\"mtype_{$row['mediaID']}\">" . uiTextSnippet($mtypeID) . "&nbsp;</span></td>\n";
      echo "</tr>\n";
    }
    ?>
  </table>
<?php
echo "<p>" . uiTextSnippet('matches') . ": $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows";
echo " &nbsp; <span>$pagenav</span></p>";
