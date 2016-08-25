<?php
require 'tng_begin.php';

require $subroot . 'pedconfig.php';
require 'personlib.php';

$rightbranch = checkbranch($row['branch']);

function drawBox($person, $box) {
  global $pedigree;
  global $more;
  global $boxheight;
  global $boxwidth;

  if ($box['lineoutof']) {
    $bgcolor = $pedigree['boxcolor'];
  } else {
    if ($box['lineinto'] == 2)  //spouse
    {
      $bgcolor = getColor(1);
    } else {
      $bgcolor = $pedigree['emptycolor'];
    }
  }

  //begin, entire square
  echo "<td><table width=\"100%\"><tr>";

  //box consists of three cells
  //left margin
  drawEmpty($box['topleft'], $box['middleleft'], $box['bottomleft']);

  //main area
  echo "<td>";

  //top border
  if ($box['lineinto']) {
    if ($box['topleft']) {
      echo "<img src=\"img/black.gif\" width=\"{$pedigree['halfwidth']}\" height=\"1\">";
    } else {
      echo "<img src=\"img/spacer.gif\" width=\"{$pedigree['halfwidth']}\" height=\"1\">";
    }
    if ($box['lineinto'] == 1 || $box['topleft'] || $box['topright']) {
      echo "<img src=\"img/black.gif\" width=\"1\" height=\"1\">";
    }
    //line break after
    if ($box['topright']) {
      echo "<img src=\"img/black.gif\" width=\"{$pedigree['halfwidth']}\" height=\"1\"><br>";
    } else {
      echo "<img src=\"img/spacer.gif\" width=\"{$pedigree['halfwidth']}\" height=\"1\"><br>";
    }
    if ($box['lineinto'] == 1) {
      echo "<img src=\"img/black.gif\" width=\"1\" height=\"20\">";
    } else {
      echo "<img src=\"img/spacer.gif\" width=\"1\" height=\"20\">";
    }
  } else {
    echo "<img src=\"img/spacer.gif\" width=\"1\" height=\"21\">";
  }

  //name section
  //outer table with border
  echo "<div class=\"popup trackerbox\" style=\"width:{$boxwidth}px; height:{$boxheight}px; background-color:$bgcolor; padding: {$pedigree['cellpad']}px; overflow:hidden\">\n";

  //inner table
  echo "<table style=\"margin:0\">\n<tr><td>";
  $name = getName($person);
  $nameinfo = "<a href=\"peopleShowPerson.php?personID={$person['personID']}\">$name</a>";
  if ($person['personID'] && $pedigree['inclphotos']) {
    $constoffset = 0;
    $photohtouse = $pedigree['puboxheight'] - $constoffset - ($pedigree['cellpad'] * 2) - 2; // take cellpadding into account
    echo showSmallPhoto($person['personID'], $name, $person['allow_living'] && $person['allow_private'], $photohtouse, false, $person['sex']);
  }
  if ($person['allow_living'] && $person['allow_private']) {
    if ($person['birth'] || $person['death']) {
      if (!$person['birth']) {
        $person['birth'] = "";
      }
      if (!$person['death']) {
        $person['death'] = "";
      }
      $nameinfo .= "<br>" . getYears($person);
    }
  }
  echo "</td>\n<td width=\"100%\">$nameinfo";
  //end inner table
  echo "</td>\n</tr></table>\n";

  //end outer table with border
  echo "</div>";

  //bottom border
  if ($more && $box['lineoutof']) {
    echo "<img src=\"img/black.gif\" width=\"1\" height=\"20\"><br>";
  } else {
    echo "<img src=\"img/spacer.gif\" width=\"1\" height=\"20\"><br>";
  }

  if ($more) {
    if ($box['bottomleft']) {
      echo "<img src=\"img/black.gif\" width=\"{$pedigree['halfwidth']}\" height=\"1\">";
    } else {
      echo "<img src=\"img/spacer.gif\" width=\"{$pedigree['halfwidth']}\" height=\"1\">";
    }
    if ($box['bottomleft'] || $box['bottomright'] || $box['lineoutof']) {
      echo "<img src=\"img/black.gif\" width=\"1\" height=\"1\">";
    }
    if ($box['bottomright']) {
      echo "<img src=\"img/black.gif\" width=\"{$pedigree['halfwidth']}\" height=\"1\">";
    } else {
      echo "<img src=\"img/spacer.gif\" width=\"{$pedigree['halfwidth']}\" height=\"1\">";
    }
  }

  //end main area
  echo "</td>";

  //right margin
  drawEmpty($box['topright'], $box['middleright'], $box['bottomright']);

  //end, entire square
  echo "</tr></table></td>";
}

function drawEmpty($top, $middle, $bottom) {
  global $pedigree, $more;

  echo "<td>";
  if ($top) {
    echo "<img src=\"img/black.gif\" width=\"5\" height=\"1\"><br>";
    echo "<img src=\"img/spacer.gif\" width=\"1\" height=\"20\">";
  } else {
    echo "<img src=\"img/spacer.gif\" width=\"1\" height=\"21\">";
  }

  echo "<table width=\"5\" height=\"{$pedigree['puboxheight']}\"><tr><td>\n";
  if ($middle) {
    echo "<img src=\"img/black.gif\" width=\"5\" height=\"1\">";
  }
  echo "</td></tr></table>";

  if ($bottom && $more) {
    echo "<img src=\"img/spacer.gif\" width=\"1\" height=\"20\"><br>";
    echo "<img src=\"img/black.gif\" width=\"5\" height=\"1\">";
  } else {
    echo "<img src=\"img/spacer.gif\" width=\"1\" height=\"21\">";
  }
  echo "</td>";
}

function doNextPerson($row, $items, $nextperson, $box) {
  global $childcount;
  global $totkids;

  $nextnextfamily = $items[0];
  if ($row['personID'] == $nextperson && $nextnextfamily) {
    $result3 = null;
    if ($row['sex'] == 'M') {
      $result3 = getParentDataCrossPlusDates($nextnextfamily, 'husband', $row['personID'], 'wife');
    } else {
      if ($row['sex'] == 'F') {
        $result3 = getParentDataCrossPlusDates($nextnextfamily, 'wife', $row['personID'], 'husband');
      }
    }
    if ($result3) {
      $spouserow = tng_fetch_assoc($result3);
      $srights = determineLivingPrivateRights($spouserow);
      $spouserow['allow_living'] = $srights['living'];
      $spouserow['allow_private'] = $srights['private'];
      tng_free_result($result3);
    } else {
      $spouserow = [];
    }

    $childcount++;
    if ($box['lineinto']) {
      $box['topright'] = $childcount == $totkids ? 0 : 1;
      $box['topleft'] = $childcount != $totkids ? 1 : 0;
      $box['bottomright'] = $childcount > $totkids / 2 ? 0 : 1;
      $box['bottomleft'] = $childcount > ($totkids + 1) / 2 ? 0 : 1;
    } else {
      $box['bottomleft'] = $box['bottomright'] = 0;
    }
    $box['lineinto'] = 2;
    $box['lineoutof'] = 0;
    $box['middleleft'] = 1;
    $box['middleright'] = 0;

    $spouserow['birthdatetr'] = $spouserow['altbirthdatetr'] = "0000-00-00"; //this is to suppress the age calculation on the chart
    drawBox($spouserow, $box);  //yes, that's intentional
  }
}

function getBox($childcount, $totkids, $thisisit, $gotnext) {
  global $more;

  $box = [];

  $box['lineoutof'] = $thisisit;
  $thisside = ($childcount < (($totkids / 2) + .5)) && $gotnext ? 1 : 0;
  $thatside = ($childcount > (($totkids / 2) + .5)) && (!$gotnext || $box['lineoutof']) ? 1 : 0;
  $middle = $childcount == ($totkids / 2) + .5;
  //echo "this=$thisside, that=$thatside, mid=$middle, cc=$childcount, tk=$totkids, gn=$gotnext, ";
  $box['topright'] = ($childcount == $totkids) || (($childcount == $totkids - 1) && $thisisit && !$thisside && $more) ? 0 : 1;
  $box['topleft'] = $childcount != 1 ? 1 : 0;
  if ($thisside) {
    if ($box['lineoutof']) {
      $box['bottomright'] = 1;
      $box['bottomleft'] = 0;
    } else {
      $box['bottomright'] = 1;
      $box['bottomleft'] = 1;
    }
  } elseif ($thatside) {
    if ($box['lineoutof']) {
      $box['bottomright'] = 0;
      $box['bottomleft'] = 1;
    } else {
      $box['bottomright'] = 1;
      $box['bottomleft'] = 1;
    }
  } elseif ($middle) {
    $box['bottomright'] = $thisisit || $gotnext ? 0 : 1;
    $box['bottomleft'] = $thisisit || !$gotnext ? 0 : 1;
  }
  $box[lineinto] = 1;

  return $box;
}

function getColor($shifts) {
  global $pedigree;

  $shiftval = $shifts * $pedigree['colorshift'];
  $R = $pedigree['baseR'] + $shiftval;
  $G = $pedigree['baseG'] + $shiftval;
  $B = $pedigree['baseB'] + $shiftval;
  if ($R > 255) {
    $R = 255;
  }
  if ($R < 0) {
    $R = 0;
  }
  if ($G > 255) {
    $G = 255;
  }
  if ($G < 0) {
    $G = 0;
  }
  if ($B > 255) {
    $B = 255;
  }
  if ($B < 0) {
    $B = 0;
  }
  $R = str_pad(dechex($R), 2, "0", STR_PAD_LEFT);
  $G = str_pad(dechex($G), 2, "0", STR_PAD_LEFT);
  $B = str_pad(dechex($B), 2, "0", STR_PAD_LEFT);
  return "#$R$G$B";
}

$pedigree['baseR'] = hexdec(substr($pedigree['boxcolor'], 1, 2));
$pedigree['baseG'] = hexdec(substr($pedigree['boxcolor'], 3, 2));
$pedigree['baseB'] = hexdec(substr($pedigree['boxcolor'], 5, 2));

if ($pedigree['colorshift'] > 0) {
  $extreme = $pedigree['baseR'] < $pedigree['baseG'] ? $pedigree['baseR'] : $pedigree['baseG'];
  $extreme = $extreme < $pedigree['baseB'] ? $extreme : $pedigree['baseB'];
} elseif ($pedigree['colorshift'] < 0) {
  $extreme = $pedigree['baseR'] > $pedigree['baseG'] ? $pedigree['baseR'] : $pedigree['baseG'];
  $extreme = $extreme > $pedigree['baseB'] ? $extreme : $pedigree['baseB'];
}

$boxheight = $pedigree['puboxheight'] + 12;
$boxwidth = $pedigree['puboxwidth'];

$pedigree['colorshift'] = 33;
$pedigree['cellpad'] = 5;
$pedigree['puboxheight'] += 24;

$pedigree['halfwidth'] = floor($pedigree['puboxwidth'] / 2) + 6;

$items = explode(",", $trail);
$personID = $nextperson = array_shift($items);
if ($nextperson) {
  $result = getPersonFullPlusDates($nextperson);
  if ($result) {
    $row = tng_fetch_assoc($result);

    $rights = determineLivingPrivateRights($row, $rightbranch);
    $row['allow_living'] = $rights['living'];
    $row['allow_private'] = $rights['private'];

    $descname = getName($row);
    $logname = $tngconfig['nnpriv'] && $row['private'] ? uiTextSnippet('private') : ($nonames && $row['living'] ? uiTextSnippet('living') : $descname);
  }

  $treeResult = getTreeSimple();
  $treerow = tng_fetch_assoc($treeResult);
  $disallowgedcreate = $treerow['disallowgedcreate'];
  tng_free_result($treeResult);

  writelog("<a href=\"desctracker.php?trail=$trail\">" . uiTextSnippet('descendfor') . " $logname ($personID)</a>");
  preparebookmark("<a href=\"desctracker.php?trail=$trail\">" . uiTextSnippet('descendfor') . " $descname ($personID)</a>");
}

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle($descname);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<?php
echo "<body id='public'>\n";
echo $publicHeaderSection->build();

$photostr = showSmallPhoto($personID, $descname, $rights['both'], 0, false, $row['sex']);
echo tng_DrawHeading($photostr, $descname, getYears($row));
$row['deathdatetr'] = $row['burialdatetr'] = "0000-00-00"; //this is to suppress the age calculation on the chart

if (!$pedigree['maxdesc']) {
  $pedigree['maxdesc'] = 12;
}
if (!$pedigree['initdescgens']) {
  $pedigree['initdescgens'] = 8;
}
if (!$generations) {
  $generations = $pedigree['initdescgens'] > 8 ? 8 : $pedigree['initdescgens'];
} else {
  if ($generations > $pedigree['maxdesc']) {
    $generations = $pedigree['maxdesc'];
  } else {
    $generations = intval($generations);
  }
}

$innermenu = "<a href=\"descend.php?personID=$personID&amp;display=standard&amp;generations=$generations\">" .
        uiTextSnippet('pedstandard') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
$innermenu .= "<a href=\"descend.php?personID=$personID&amp;display=compact&amp;generations=$generations\">" .
        uiTextSnippet('pedcompact') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
$innermenu .= "<a href=\"descendtext.php?personID=$personID&amp;generations=$generations\">" .
        uiTextSnippet('pedtextonly') . "</a> &nbsp;&nbsp; | &nbsp;&nbsp; \n";
$innermenu .= "<a href=\"register.php?personID=$personID&amp;generations=$generations\">" . uiTextSnippet('regformat') . "</a>\n";

beginFormElement("descend", "GET", "form1", "form1");
echo buildPersonMenu("descend", $personID);
echo "<div class='pub-innermenu small'>\n";
  echo $innermenu;
echo "</div>\n";
echo "<br>\n";
endFormElement();
?>
  <br clear="left">

  <div style="width: 100%; overflow: auto">
    <table>
      <tr>
        <td>
          <?php
          $more = count($items);
          if ($nextperson) {
            echo "<table>\n<tr>\n";
            $box = [];
            $box['lineinto'] = 0;
            $box['lineoutof'] = 1;
            $box['topleft'] = $box['topright'] = 0;
            $childcount = $totkids = 1;
            $box['bottomright'] = $more ? 1 : 0;
            $box['bottomleft'] = 0;
            $box['middleright'] = 1;
            $box['middleleft'] = 0;
            drawBox($row, $box);
            doNextPerson($row, $items, $nextperson, $box);
            echo "</tr>\n</table>\n";
            if ($more) {
              echo "<img src=\"img/black.gif\" width=\"1\" height=\"20\"><br>";
            }
          }
          while ($more) {
            $lineinfo = [];
            $linelength = 0;
            $gotnext = 0;
            $nextfamily = array_shift($items);
            $nextperson = array_shift($items);
            $more = count($items);

            //get kids
            $result2 = getChildrenDataPlusDates($nextfamily);
            if ($result2) {
              echo "<table>\n<tr>\n";
              $totkids = tng_num_rows($result2);
              if ($more) {
                $totkids++;
              }
              $childcount = 0;
              while ($row = tng_fetch_assoc($result2)) {
                $childcount++;

                $rights = determineLivingPrivateRights($row);
                $row['allow_living'] = $rights['living'];
                $row['allow_private'] = $rights['private'];

                if ($row['personID'] == $nextperson) {
                  $gotnext = 1;
                  $firsthalf = $childcount < ($totkids / 2) ? 1 : 0;
                  $thisisit = 1;
                } else {
                  $thisisit = 0;
                }

                $row['birthdatetr'] = $row['altbirthdatetr'] = "0000-00-00"; //this is to suppress the age calculation on the chart
                $box = getBox($childcount, $totkids, $thisisit, $gotnext);
                $box['middleleft'] = 0;
                $box['middleright'] = $thisisit && $more ? 1 : 0;
                //echo "tl=$box[topleft], tr=$box[topright], ml=$box[middleleft], mr=$box[middleright], bl=$box[bottomleft], br=$box[bottomright], cc=$childcount, tk=$totkids, gn=$gotnext";
                drawBox($row, $box);
                doNextPerson($row, $items, $nextperson, $box);
              }
              echo "</tr>\n</table>";
            }
            if ($more) {
              echo "<img src=\"img/black.gif\" width=\"1\" height=\"20\"><br>";
            }
            tng_free_result($result2);
          }
          ?>
        </td>
      </tr>
    </table>
  </div>
<?php
echo $publicFooterSection->build();
echo scriptsManager::buildScriptElements($flags, 'public');
?>
</body>
</html>