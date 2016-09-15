<?php
require 'tng_begin.php';

require $subroot . 'pedconfig.php';
require 'personlib.php';

if (!$personID && !isset($needperson)) {
  die('no args');
}
if ($generations > $pedigree['maxgen']) {
  $generations = intval($pedigree['maxgen']);
} elseif (!$generations) {
  $generations = $pedigree['initpedgens'] >= 2 ? intval($pedigree['initpedgens']) : 2;
} else {
  $generations = intval($generations);
}

$result = getPersonFullPlusDates($personID);
if (!tng_num_rows($result)) {
  tng_free_result($result);
  header('Location: thispagedoesnotexist.html');
  exit;
}
$row = tng_fetch_assoc($result);
tng_free_result($result);
$rightbranch = checkbranch($row['branch']);
$rights = determineLivingPrivateRights($row, $rightbranch);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];
$row['name'] = getName($row);

$logname = $tngconfig['nnpriv'] && $row['private'] ? uiTextSnippet('private') : ($nonames && $row['living'] ? uiTextSnippet('living') : $row['name']);

function initChart() {
  global $gens;
  global $gedcom;
  global $generations;
  global $personID;

  $gedcom = tng_real_escape_string($_GET['tree']);
  $gens[1][1] = $personID;
  get_details($gens, 1, $generations);
  close_parents($gens);
  remove_margins($gens);
  do_chart($gens, true);
}

function get_details(&$gens, $generation, $max_generations) {
  global $chartBoxWidth;
  global $chartBoxVerticalSpacing;
  global $person_count;
  global $people_table;
  global $families_table;
  $delete_variables = ['firstname', 'lnprefix', 'lastname', 'title', 'prefix', 'suffix', 'nameorder', 'allow_living', 'allow_private'];
  foreach ($gens[$generation] as $num => $g) {
    if ($g) {
      $query = "SELECT personID, firstname, lnprefix, lastname, title, prefix, suffix, nameorder, sex, birthdate, birthdatetr, altbirthdate, altbirthdatetr, deathdate, deathdatetr, burialdate, burialdatetr, birthplace, altbirthplace, deathplace, burialplace, husband AS father, wife AS mother, {$people_table}.living, {$people_table}.private, {$people_table}.branch FROM {$people_table} "
      . "LEFT JOIN {$families_table} ON {$people_table}.famc={$families_table}.familyID "
      . "WHERE personID='{$g}'";
      $result = tng_query($query);
      if ($result && tng_num_rows($result)) {
        $result = tng_fetch_assoc($result);
        if (isset($result['personID'])) {
          $result['xpos'] = ($chartBoxWidth + $chartBoxVerticalSpacing) * (pow(2, $max_generations - $generation)) * ($num - 0.5);
          $result['spacer_xwidth'] = ($chartBoxWidth + $chartBoxVerticalSpacing) * (pow(2, $max_generations - $generation - 1));

          $rights = determineLivingPrivateRights($result);
          $result['display'] = $rights['both'];

          $result['allow_living'] = $rights['living'];
          $result['allow_private'] = $rights['private'];
          $result['name'] = getName($result);
          foreach ($delete_variables as $var) {
            unset($result[$var]);
          } //Save memory by deleting variables no longer needed
          $gens[$generation][$num] = $result;
          if ($generation < $max_generations) {
            $gens[$generation + 1][($num * 2) - 1] = $result ['father'];
            $gens[$generation + 1][$num * 2] = $result ['mother'];
          }
          $person_count++;
        }
      } else {
        echo uiTextSnippet('no_ancestors');
        die();
      }
    }
    if (!isset($gens[$generation + 1][$num * 2]) && ($generation < $max_generations)) {
      $gens[$generation + 1][$num * 2] = '';
      $gens[$generation + 1][($num * 2) - 1] = '';
    }
  }
  if ($generation < $max_generations) {
    get_details($gens, $generation + 1, $max_generations);
  }
}

function is_male($num) {
  return (boolean)($num % 2);
}

function move_one_person(&$gens, $gen_num, $num) {
  global $chartBoxWidth;
  global $chartBoxVerticalSpacing;

  $previous = get_previous_person($gens[$gen_num], $num);
  if ($previous) {
    $distance = $gens[$gen_num][$num]['xpos'] - ($gens[$gen_num][$previous]['xpos'] + $chartBoxWidth + $chartBoxVerticalSpacing);
  } else {
    $distance = $gens[$gen_num][$num]['xpos'];
  }
  // Now move everything on this row, plus their descendants
  if ($distance != 0) {
    if ($distance == 9999999999) {
      $distance = $gens[$gen_num][$gen_num]['xpos'];
    }
    for ($loop = $num; $loop <= pow(2, $gen_num - 1); $loop++) {
      if (isset($gens[$gen_num][$loop]['xpos'])) {
        move_left($gens, $gen_num, $loop, $distance);
        if (is_male($loop) && isset($gens[$gen_num][$loop + 1]['personID'])) {
          move_left($gens, $gen_num, $loop + 1, $distance);
        }
        $move_limit = move_descendant($gens, $gen_num, $loop);            // Move the descendants
        if ($move_limit !== null && $move_limit < $distance) {                // Test whether the descendant move had to be limited to avoid overlapping
          move_left($gens, $gen_num, $loop, $move_limit - $distance);     // If so, move the person back
          if (is_male($loop) && isset($gens[$gen_num][$loop + 1]['personID'])) {    // And his wife, if necessary
            move_left($gens, $gen_num, $loop + 1, $move_limit - $distance);
          }
          $distance = $move_limit;
        }
      }
    }
  }
}

function close_parents(&$gens) {
  global $chartBoxWidth, $chartBoxVerticalSpacing;
  $max_generations = count($gens);
  for ($gen_num = $max_generations; $gen_num > 1; $gen_num--) {
    $generation_exists = false;
    for ($num = count($gens[$gen_num]); $num >= 1; $num--) {
      $person = &$gens[$gen_num][$num];
      if (isset($person['personID'])) {
        $generation_exists = true;
        $child = &$gens[$gen_num - 1][ceil($num / 2)];
        $spouse = &$gens[$gen_num][$num + (is_male($num) ? 1 : -1)];
        if (!isset($spouse['personID'])) {
          move_left($gens, $gen_num, $num, $person['xpos'] - $child['xpos']);
          $child['spacer_xwidth'] = 0;
        } elseif (!is_male($num)) {
          // Move wife across
          // First, calculate the maximum distance she can be moved by going up the tree and calculating the maximum distance at every level
          $distance = 9999999999;
          $this_person = $num;
          for ($loop = $gen_num; $loop <= $max_generations; $loop++) {
            $left = get_previous_person($gens[$loop], $this_person);
            $right = get_next_person($gens[$loop], $this_person - 1);
            if ($left && $right) {
              $new_distance = $gens[$loop][$right]['xpos'] - $gens[$loop][$left]['xpos'] - $chartBoxWidth - $chartBoxVerticalSpacing;
            } elseif (!$left & $right) {
              $new_distance = $gens[$loop][$right]['xpos'];
            }
            if (isset($new_distance)) {
              if ($new_distance < $distance) {
                $distance = $new_distance;
              }
              unset($new_distance);
            }
            if ($distance == 0) { // If or we've already established we can't move this person, there's no point continuing
              break;
            }
            if ($loop < $max_generations) {
              $this_person = ($this_person * 2) - 1;
            }
          }
          move_left($gens, $gen_num, $num, $distance);
          $child['spacer_xwidth'] = $child['spacer_xwidth'] - $distance;
        } elseif (is_male($num)) {
          // Move wife and husband back to above the centre of the child
          $distance = $child['xpos'] - (($spouse['xpos'] + $person ['xpos']) / 2);
          move_left($gens, $gen_num, $num + 1, -$distance);
          move_left($gens, $gen_num, $num, -$distance);
        }
      }
    }
    if (!$generation_exists) {
      unset($gens[$gen_num]);
      $max_generations = count($gens);
    }
  }
}

function remove_margins(&$gens) {
  global $chartBoxVerticalSpacing;
  $left_most_xpos = 999999999;
  foreach ($gens as $gen_num => $generation) {
    $next = get_next_person($generation, 0);
    if (isset($generation[$next]['xpos']) && ($generation[$next]['xpos'] < $left_most_xpos)) {
      $left_most_xpos = $generation[$next]['xpos'];
    }
  }
  move_left($gens, 1, 1, $left_most_xpos - $chartBoxVerticalSpacing);
}

function get_previous_person($generation, $num) {
  for ($previous = $num - 1; $previous >= 1; $previous--) {
    if (isset($generation[$previous]['personID'])) {
      return $previous;
    }
  }
  return false;
}

function get_next_person($generation, $num) {
  for ($next = $num + 1; $next <= count($generation); $next++) {
    if (isset($generation[$next]['personID'])) {
      return $next;
    }
  }
  return false;
}

function move_left(&$gens, $gen_num, $num, $distance) {
  $gens[$gen_num][$num]['xpos'] = $gens[$gen_num][$num]['xpos'] - $distance;
  if (isset($gens [$gen_num + 1][($num * 2) - 1]['xpos'])) {
    move_left($gens, $gen_num + 1, ($num * 2) - 1, $distance);
  }
  if (isset($gens [$gen_num + 1][$num * 2]['xpos'])) {
    move_left($gens, $gen_num + 1, $num * 2, $distance);
  }
}

function move_descendant(&$gens, $gen_num, $num) {
  global $chartBoxWidth, $chartBoxVerticalSpacing;
  if ($gen_num == 1) {
    return;
  }
  $child = &$gens[$gen_num - 1][ceil($num / 2)];
  $father = $gens[$gen_num][(ceil($num / 2) * 2) - 1];
  $mother = $gens[$gen_num][ceil($num / 2) * 2];
  $orig_child_xpos = $child['xpos'];
  if ($father && $mother) {
    $child['xpos'] = ($father['xpos'] + $mother ['xpos']) / 2;
    $child['spacer_xwidth'] = $mother ['xpos'] - $father['xpos'];
  } elseif ($father) {
    $child['xpos'] = $father['xpos'];
    $child['spacer_xwidth'] = 0;
  } elseif ($mother) {
    if ($mother['xpos'] < $child['xpos']) {
      $child['xpos'] = $mother['xpos'];
    }
    $child['spacer_xwidth'] = 0;
  }
  $limit = 9999999999;
  $previous_child = get_previous_person($gens[$gen_num - 1], ceil($num / 2));
  if ($previous_child) {
    $min_xpos = $gens[$gen_num - 1][$previous_child]['xpos'] + $chartBoxWidth + $chartBoxVerticalSpacing;
    if ($child['xpos'] < $min_xpos) {
      $limit = $orig_child_xpos - $min_xpos;
      $child['xpos'] = $min_xpos;
    }
  }
  //Repeat whole process for children
  if ($gen_num > 2) {
    $new_limit = move_descendant($gens, $gen_num - 1, ceil($num / 2));
  } else {
    $new_limit = null;
  }
  if ($new_limit !== null && ($new_limit < $limit)) {
    $limit = $new_limit;
    $new_limit = null;
  }
  if ($limit == 9999999999) {
    return null;
  } else {
    return $limit;
  }
}

function do_chart($gens, $output = false) {
  global $chartBoxWidth;
  global $chartBoxHeight;
  global $chartBoxVerticalSpacing;
  global $containerheight;

  $rows = sizeof($gens);
  $ignore = isset($_GET['ignorestart']);
  foreach ($gens as $gen_num => $generation) {
    $row [$rows - $gen_num] = '';
    $ypos = ($rows - $gen_num) * ($chartBoxHeight + ($chartBoxVerticalSpacing * 2)) + $chartBoxVerticalSpacing;
    $spacer_ypos = (($rows - $gen_num) * ($chartBoxHeight + ($chartBoxVerticalSpacing * 2)));
    $line_ypos = (($rows - $gen_num) * ($chartBoxHeight + ($chartBoxVerticalSpacing * 2))) + $chartBoxHeight + $chartBoxVerticalSpacing;
    foreach ($generation as $num => $person) {
      if (isset($person['personID'])) {
        if (is_male($num)) {
          if (isset($gens[$gen_num][$num + 1]['personID'])) {
            $type = 'husband';
          } else {
            $type = 'single';
          }
        } else {
          if (isset($gens[$gen_num][$num - 1]['personID'])) {
            $type = 'wife';
          } else {
            $type = 'single';
          }
        }
        $person['xpos'] = round($person['xpos']);
        $bio = '';
        if ($person['birthdate'] || $person['birthplace']) {
          $bio .= trim(uiTextSnippet('born') . ': ' . displayDate($person['birthdate']));
          if ($person['birthdate'] && $person['birthplace']) {
            $bio .= ', ';
          }
          $bio .= $person['birthplace'];
        } elseif ($person['altbirthdate'] || $person['altbirthplace']) {
          $bio .= trim(uiTextSnippet('christened') . ': ' . displayDate($person['altbirthdate']));
          if ($person['altbirthdate'] && $person['altbirthplace']) {
            $bio .= ', ';
          }
          $bio .= $person['altbirthplace'];
        }
        if ($person['deathdate'] || $person['deathplace']) {
          $bio .= trim(($bio ? ' &#013;' : '') . uiTextSnippet('died') . ': ' . displayDate($person['deathdate']));
          if ($person['deathdate'] && $person['deathplace']) {
            $bio .= ', ';
          }
          $bio .= $person['deathplace'];
        } elseif ($person['burialdate'] || $person['burialplace']) {
          $bio .= trim(($bio ? ' &#013;' : '') . uiTextSnippet('buried') . ': ' . displayDate($person['burialdate']));
          if ($person['burialdate'] && $person['burialplace']) {
            $bio .= ', ';
          }
          $bio .= $person['burialplace'];
        }
        if ($spacer_ypos > 0 && (isset($gens[$gen_num + 1][($num * 2) - 1]['name']))) {
          $row [$rows - $gen_num] .= "\t\t<div class=\"ascender father\" style=\"left:" . ($person['xpos'] - (($person['spacer_xwidth'] - $chartBoxWidth) / 2)) . "px;top:{$spacer_ypos}px;width:" . ($person['spacer_xwidth'] / 2) . "px\"></div>\r\n";
        }
        if ($spacer_ypos > 0 && (isset($gens[$gen_num + 1][$num * 2]['name']))) {
          $row [$rows - $gen_num] .= "\t\t<div class=\"ascender mother\" style=\"left:" . ($person['xpos'] + ($chartBoxWidth) / 2) . 'px;top:' . ($ypos - $chartBoxVerticalSpacing) . 'px;width:' . ($person['spacer_xwidth'] / 2) . "px\"></div>\r\n";
        }
        if (!$ignore || $gen_num > 1) {
          $row [$rows - $gen_num] .= "\t\t<div class=\"box\" style=\"left:{$person['xpos']}px;top:{$ypos}px;width:{$chartBoxWidth}px\">\r\n\t\t\t<div class=\"inner\">\r\n\t\t\t\t<div>\r\n\t\t\t\t\t";
          $url = htmlentities("peopleShowPerson.php?personID={$person['personID']}");
          $row [$rows - $gen_num] .= '<a' . ($person['display'] ? " title=\"{$bio}\"" : '') . " href=\"{$url}\">{$person['name']}</a><br>" . getGenderIcon($person['sex'], -2);
          if ($person['display']) {
            if ($person['birthdatetr'] != '0000-00-00' || $person['altbirthdatetr'] != '0000-00-00' || $person['deathdatetr'] != '0000-00-00' || $person['burialdatetr'] != '0000-00-00') {
              $row [$rows - $gen_num] .= ' ' . (substr($person['birthdatetr'], 0, 4) != '0000' ? substr($person['birthdatetr'], 0, 4) : (substr($person['altbirthdatetr'], 0, 4) != '0000' ? substr($person['altbirthdatetr'], 0, 4) : '')) . '-' . (substr($person['deathdatetr'], 0, 4) != '0000' ? substr($person['deathdatetr'], 0, 4) : (substr($person['burialdatetr'], 0, 4) != '0000' ? substr($person['burialdatetr'], 0, 4) : ''));
            }
          }
          $row[$rows - $gen_num] .= "\r\n\t\t\t\t</div>\r\n\t\t\t</div>\r\n\t\t</div>\r\n";
        }
        if ($gen_num > 1) {
          $row[$rows - $gen_num] .= "\t\t<div class=\"descender_container\" style=\"left:{$person['xpos']}px;top:{$line_ypos}px;height:{$chartBoxVerticalSpacing}px\">\r\n";
          $row[$rows - $gen_num] .= "\t\t\t<div class=\"descender {$type}\"></div>\r\n";
          $row[$rows - $gen_num] .= "\t\t</div>\r\n";
        }
      }
    }
  }
  ksort($row);

  $html = '<style>
    #vcontainer {
    height:' . $containerheight . 'px;
    }
    #vcontainer div.ascender {
    height:' . $chartBoxVerticalSpacing . 'px;
    }
    #vcontainer div.descender_container {
    height:' . $chartBoxVerticalSpacing . 'px;
    width:' . $chartBoxWidth . 'px;
    }
    #vcontainer div.descender {
    height:' . $chartBoxVerticalSpacing . 'px;
    }
    #vcontainer div.single {
    margin-left: ' . ($chartBoxWidth / 2) . 'px;
    }
    #vcontainer div.box {
    height:' . $chartBoxHeight . 'px;
    padding-right:' . (int)($chartBoxVerticalSpacing / 2) . 'px;
    }
    #vcontainer div.box div.inner {
    font-size: 0.75rem;
    width:' . $chartBoxWidth . 'px;
    height:' . ($chartBoxHeight - 6) . 'px;
    }
    #vcontainer div.box div.inner div {
    width:' . $chartBoxWidth . 'px;
    height:' . ($chartBoxHeight - 6) . 'px;
    }
    </style>
    <div id="vcontainer">';
  $html .= implode($row, "\r\n");
  $html .= '  </div>';
  if ($output) {
    echo $html;
  }
}

writelog("<a href=\"verticalchart.php?personID=$personID&amp;generations=$gens&amp;display=$display\">" . xmlcharacters('' . uiTextSnippet('pedigreefor') . " $logname ($personID)") . "</a> $gens " . $gentext);
preparebookmark("<a href=\"verticalchart.php?personID=$personID&amp;generations=$gens&amp;display=$display\">" . xmlcharacters('' . uiTextSnippet('pedigreefor') . " $pedname ($personID)") . "</a> $gens " . $gentext);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('pedigreefor') . ' ' . $row['name']);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();

    $photostr = showSmallPhoto($personID, $row['name'], $rights['both'], 0, false, $row['sex']);
    echo tng_DrawHeading($photostr, $row['name'], getYears($row));

    $innermenu = uiTextSnippet('generations') . ': &nbsp;';
    $innermenu .= "<select name='generations' class='small' onchange=\"window.location.href='verticalchart.php?personID=$personID&amp;parentset=$parentset&amp;display=$display&amp;generations=' + this.options[this.selectedIndex].value\">\n";
    for ($i = 2; $i <= $pedigree['maxgen']; $i++) {
      $innermenu .= "<option value='$i'";
      if ($i == $generations) {
        $innermenu .= ' selected';
      }
      $innermenu .= ">$i</option>\n";
    }
    $innermenu .= "</select>\n";
    $innermenu .= "<a class='navigation-item' href='pedigree.php?personID=$personID&amp;parentset=$parentset&amp;display=standard&amp;generations=$generations' id='stdpedlnk'>" . uiTextSnippet('pedstandard') . "</a>\n";
    $innermenu .= "<a class='navigation-item' href='pedigree.php?personID=$personID&amp;parentset=$parentset&amp;display=compact&amp;generations=$generations' id='compedlnk'>" . uiTextSnippet('pedcompact') . "</a>\n";
    $innermenu .= "<a class='navigation-item' href='pedigree.php?personID=$personID&amp;parentset=$parentset&amp;display=box&amp;generations=$generations' id='boxpedlnk'>" . uiTextSnippet('pedbox') . "</a>\n";
    $innermenu .= "<a class='navigation-item' href='pedigreetext.php?personID=$personID&amp;parentset=$parentset&amp;generations=$generations'>" . uiTextSnippet('pedtextonly') . "</a>\n";
    $innermenu .= "<a class='navigation-item' href='ahnentafel.php?personID=$personID&amp;parentset=$parentset&amp;generations=$generations'>" . uiTextSnippet('ahnentafel') . "</a>\n";
    $innermenu .= "<a class='navigation-item' href='extrastree.php?personID=$personID&amp;parentset=$parentset&amp;showall=1&amp;generations=$generations'>" . uiTextSnippet('media') . "</a>\n";
    if ($gens <= 6 && $allow_pdf && $rightbranch) {
      $innermenu .= "<a class='navigation-item' href='#' onclick=\"tnglitbox = new ModalDialog('rpt_pdfform.php?pdftype=ped&amp;personID=$personID&amp;generations=$generations');return false;\">PDF</a>\n";
    }
    beginFormElement('pedigree', '', 'form1', 'form1');
    echo buildPersonMenu('pedigree', $personID);
    echo "<div class='pub-innermenu small'>\n";
      echo $innermenu;
    echo "</div>\n";
    echo "<br>\n";
    endFormElement();

    $chartBoxHeight = 60;
    $chartBoxWidth = 120;
    $chartBoxVerticalSpacing = 20;

    $containerheight = ($generations * ($chartBoxHeight + ($chartBoxVerticalSpacing * 2))) + $chartBoxVerticalSpacing;

    initChart();
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script>
    var tnglitbox;
  </script>
</body>
</html>
