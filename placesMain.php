<?php
require 'tng_begin.php';

if ($tree && !$tngconfig['places1tree']) {
  $treestr = "tree=$tree&amp;";
  $treestr2 = "tree=$tree";
  $places_all_url = "places-all.php?";
  $places_url = "placesMain.php?";
  $logstring = "<a href=\"placesMain.php?$treestr2\">" . uiTextSnippet('placelist') . " (" . uiTextSnippet('tree') . ": $tree)</a>";
  $wherestr = "AND gedcom = \"$tree\"";
} else {
  $treestr = $treestr2 = "";
  $places_all_url = "places-all.php";
  $places_url = "placesMain.php";
  $logstring = "<a href='placesMain.php'>" . uiTextSnippet('placelist') . "</a>";
  $wherestr = "";
}
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('placelist'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/location.svg'><?php echo uiTextSnippet('placelist'); ?></h2>
    <br class='clearleft'>
    <?php
    if (!$tngconfig['places1tree']) {
      echo treeDropdown(array('startform' => true, 'endform' => true, 'action' => 'places', 'method' => 'get', 'name' => 'form1', 'id' => 'form1'));
    }
    $linkstr = "";
    $linkstr2col1 = "";
    $linkstr2col2 = "";
    $linkstr3col1 = "";
    $linkstr3col2 = "";
    $collen = 10;
    $cols = 3;

    $offsetorg = $offset;
    $offset = $offset ? $offset + 1 : 1;

    $query = "SELECT ucase(left(trim(substring_index(place,',',-$offset)),1)) as firstchar, count(ucase(left(trim(substring_index(place,',',-$offset)),1))) as placecount "
            . "FROM $places_table WHERE trim(substring_index(place,',',-$offset)) != \"\" $wherestr GROUP BY firstchar ORDER by firstchar";
    $result = tng_query($query);
    if ($result) {
      $initialchar = 1;

      while ($place = tng_fetch_assoc($result)) {
        if ($initialchar != 1) {
          $linkstr .= " ";
        }
        if ($place['firstchar'] != "") {
          $urlfirstchar = urlencode($place['firstchar']);
          $countstr = uiTextSnippet('placesstarting') . ": " . $place['firstchar'] . " (" . number_format($place['placecount']) . " " . uiTextSnippet('totalnames') . ")";
          $linkstr .= "<a href=\"places-oneletter.php?firstchar=$urlfirstchar&amp;{$treestr}offset=$offsetorg&amp;psearch=$psearch\" title=\"$countstr\">$place[firstchar]</a> ";
        }
        $initialchar++;
      }
      tng_free_result($result);
    }

    $query = "SELECT trim(substring_index(place,',',-$offset)) as myplace, count(place) as placecount "
            . "FROM $places_table WHERE trim(substring_index(place,',',-$offset)) != \"\" $wherestr GROUP BY myplace ORDER by placecount DESC LIMIT 30";
    $result = tng_query($query);
    $maxcount = 0;
    if ($result) {
      $count = 1;
      $col = -1;
      while ($place = tng_fetch_assoc($result)) {
        $place2 = urlencode($place['myplace']);
        if ($place2 != "") {
          if (!$maxcount) {
            $maxcount = $place['placecount'];
          }
          $tally = $place['placecount'];
          $tally_fmt = number_format($tally);
          $thiswidth = floor($tally / $maxcount * 100);
          $query = "SELECT count(place) as placecount FROM $places_table WHERE place = \"" . addslashes($place['myplace']) . "\" $wherestr";
          $result2 = tng_query($query);
          $countrow = tng_fetch_assoc($result2);
          $specificcount = $countrow['placecount'];
          tng_free_result($result2);

          $searchlink = $specificcount ? " <a href='placesearch.php?{$treestr}psearch=$place2'><img class='icon-xs-inline' src='svg/magnifying-glass.svg' alt=''></a>" : "";
          $name = $place['placecount'] > 1 || !$specificcount ? "<a href=\"places-oneletter.php?offset=$offset&amp;{$treestr}psearch=$place2\">" . str_replace(array("<", ">"), array("&lt;", "&gt;"), $place['myplace']) . "</a> ($tally_fmt)" : $place['myplace'];
          if (($count - 1) % $collen == 0) {
            $col++;
          }
          $chartstr = $col ? "" : "<td width=\"400\"><div style=\"width:{$thiswidth}%;\" class=\"bar rightround\"><a href=\"places-oneletter.php?offset=$offset&amp;{$treestr}psearch=$place2\" title=\"{$place['myplace']} ($tally_fmt)\"></a></div></td>";
          $linkstr2col[$col] .= "<tr><td align=\"right\">$count.</td><td>$name$searchlink</td>$chartstr</tr>\n";
          $count++;
        }
      }
      tng_free_result($result);
    }
    ?>
    <div class='card'>
      <div class='card-block'>
        <h4 class='card-header'>
          <?php echo uiTextSnippet('placesstarting'); ?>
        </h4>
        <p class="firstchars"><?php echo $linkstr; ?></p>
        <?php
        beginFormElement("places-oneletter", "get");
        echo uiTextSnippet('placescont') . ": <input name='psearch' type='text' />\n";
        if ($tree && !$tngconfig['places1tree']) {
          echo "<input name='tree' type='hidden' value=\"$tree\" />\n";
        }
        echo "<input name='stretch' type='hidden' value='1'>\n";
        echo "<input name='pgo' type='submit' value=\"" . uiTextSnippet('go') . "\" />\n";
        endFormElement();
        ?>
        <br>
        <?php echo "<a href=\"$places_all_url" . "$treestr2\">" . uiTextSnippet('showallplaces') . "</a> (" . uiTextSnippet('sortedalpha') . ")"; ?>
        </div>
    </div>
    <br>

    <div class="card">
      <div class='card-block'>
        <h4 class="card-header">
          <?php
          echo str_replace('{xxx}', '30', uiTextSnippet('top{xxx}places')) . " (" . uiTextSnippet('totalplaces') . "):"; 
          ?>
        </h4>
        <table class='table table-sm'>
          <tr>
            <?php
            for ($i = 0; $i < $cols; $i++) {
              if ($i) {
                echo "<td class=\"table-gutter\">&nbsp;</td>\n";
              }
              ?>
              <td>
                <table class="table-histogram">
                  <?php
                  echo $linkstr2col[$i];
                  ?>
                </table>
              </td>
              <?php
            }
            ?>
          </tr>
        </table>
        <div>
          <?php
          beginFormElement("places100", "get");
          echo uiTextSnippet('showtop');
          echo "<input name='topnum' type='text' value='100' size='4' maxlength='4'> " . uiTextSnippet('byoccurrence') . "\n";
          if ($tree && !$tngconfig['places1tree']) {
            echo "<input name='tree' type='hidden' value='$tree'>\n";
          }
          echo "<input type=\"submit\" value=\"" . uiTextSnippet('go') . "\" />\n";
          endFormElement();
          ?>
        </div>
      </div>
    </div>
    <br>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>