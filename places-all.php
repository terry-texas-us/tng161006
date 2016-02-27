<?php
include("tng_begin.php");

if ($tree && !$tngconfig['places1tree']) {
  $treestr = "tree=$tree&amp;";
  $treestr2 = "tree=$tree";
  $places_url = "places.php?";
  $logstring = "<a href=\"places-all.php?$treestr2\">" . uiTextSnippet('allplaces') . " (" . uiTextSnippet('tree') . ": $tree)</a>";
  $wherestr = "WHERE gedcom = \"$tree\"";
  $wherestr2 = "AND gedcom = \"$tree\"";
} else {
  $treestr = $treestr2 = "";
  $places_url = "places.php";
  $logstring = "<a href='places-all.php'>" . uiTextSnippet('allplaces') . "</a>";
  $wherestr = "";
  $wherestr2 = "";
}
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('placelist') . ": " . uiTextSnippet('allplaces'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/location.svg'><?php echo uiTextSnippet('placelist') . ": " . uiTextSnippet('allplaces'); ?></h2>
    <br class='clearleft'>
    <?php
    if (!$tngconfig['places1tree']) {
      echo treeDropdown(array('startform' => true, 'endform' => true, 'action' => 'places-all', 'method' => 'get', 'name' => 'form1', 'id' => 'form1'));
    }
    $offset = 1;

    $linkstr = "";
    $query = "SELECT distinct ucase(left(trim(substring_index(place,',',-$offset)),1)) as firstchar FROM $places_table $wherestr GROUP BY firstchar ORDER by firstchar";
    $result = tng_query($query);
    if ($result) {
      $initialchar = 1;

      while ($place = tng_fetch_assoc($result)) {
        if ($initialchar != 1) {
          $linkstr .= " ";
        }
        if ($place['firstchar'] != "" && $place['firstchar'] != "_") {
          $linkstr .= "<a href=\"#char$initialchar\">{$place['firstchar']}</a> ";
          $firstchars[$initialchar] = $place['firstchar'];
          $initialchar++;
        }
      }
      tng_free_result($result);
    }
    ?>
    <div class="card">
      <div class='card-header'>
        <h4><?php echo uiTextSnippet('placesstarting'); ?></h4>
      </div>
      <div class='card-subtitle text-sm-center'>
        <p class="firstchars"><?php echo $linkstr; ?></p>
      </div>
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

      <br><?php echo "<a href=\"$places_url" . "$treestr2\">" . uiTextSnippet('mainplacepage') . "</a>"; ?>
    </div>
    <br>
    <p class="small"><?php echo uiTextSnippet('showmatchingplaces'); ?></p>
    <?php for ($scount = 1; $scount < $initialchar; $scount++) { ?>
      <div class='card'>
        <?php
        $urlfirstchar = addslashes($firstchars[$scount]);
        if ($urlfirstchar) {
          echo "<div class='card-header'>\n";
            echo "<a id=\"char$scount\">{$firstchars[$scount]}</a>\n";
          echo "</div>\n";
          ?>
          <table class="table table-sm">
            <tr>
              <td class="plcol">
                <?php
                $query = "SELECT trim(substring_index(place,',',-$offset)) as myplace, count(place) as placecount FROM $places_table WHERE trim(substring_index(place,',',-$offset)) LIKE \"$urlfirstchar%\" $wherestr2 GROUP BY myplace ORDER by myplace";
                $result = tng_query($query);
                $topnum = tng_num_rows($result);
                if ($result) {
                  $snnum = 1;
                  if (!isset($numcols)) {
                    $numcols = 3;
                  }
                  $num_in_col = ceil($topnum / $numcols);
                  if ($numcols > 3) {
                    $numcols = 3;
                    $num_in_col = ceil($topnum / 3);
                  }

                  $num_in_col_ctr = 0;
                  while ($place = tng_fetch_assoc($result)) {
                    $place2 = urlencode($place['myplace']);
                    $commaOnEnd = false;
                    $poffset = $stretch ? "" : "offset=$offset&amp;";
                    if (substr($place['wholeplace'], 0, 1) == ',' && trim(substr($place[wholeplace], 1)) == $place['myplace']) {
                      $place3 = addslashes($place['wholeplace']);
                      $commaOnEnd = true;
                      $place2 = urlencode($place['wholeplace']);
                      $placetitle = $place['wholeplace'];
                    } else {
                      $place3 = addslashes($place['myplace']);
                      $placetitle = $place['myplace'];
                    }

                    $query = "SELECT count(place) as placecount FROM $places_table WHERE place = \"$place3\" $wherestr2";
                    $result2 = tng_query($query);
                    $countrow = tng_fetch_assoc($result2);
                    $specificcount = $countrow['placecount'];
                    tng_free_result($result2);

                    $searchlink = $specificcount ? " <a href=\"placesearch.php?{$treestr}psearch=$place2\" title=\"" .
                            uiTextSnippet('findplaces') . "\"><img class='icon-xs-inline' src='svg/magnifying-glass.svg' alt=\"uiTextSnippet(findplaces)\"></a>" : "";
                    if ($place['placecount'] > 1 || ($place['myplace'] != $place['wholeplace'] && !$commaOnEnd)) {
                      $name = "<a href=\"places-oneletter.php?" . $poffset;
                      if ($tree && !$tngconfig['places1tree']) {
                        $name .= "tree={$place['gedcom']}&amp;";
                      }
                      $name .= "psearch=$place2\">" . str_replace(array("<", ">"), array("&lt;", "&gt;"), $place['myplace']) . "</a>";
                      echo "$snnum. $name ({$place['placecount']})$searchlink<br>\n";
                    } else {
                      echo "$snnum. $placetitle$searchlink<br>\n";
                    }
                    $snnum++;
                    $num_in_col_ctr++;
                    if ($num_in_col_ctr == $num_in_col) {
                      echo "</td>\n<td></td>\n<td class=\"plcol\">";
                      $num_in_col_ctr = 0;
                    }
                  }
                  tng_free_result($result);
                }
                ?>
              </td>
            </tr>
          </table>
        <?php } ?>
      </div>
    <?php } ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>