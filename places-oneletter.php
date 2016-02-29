<?php
include("tng_begin.php");

$psearch = trim($psearch);
$decodedfirstchar = $firstchar ? stripslashes(urldecode($firstchar)) : stripslashes($psearch);

if ($tree && !$tngconfig['places1tree']) {
  $treestr = "tree=$tree&amp;";
  $treestr2 = "tree=$tree";
  $places_all_url = "places-all.php?";
  $places_url = "placesMain.php?";
  $logstring = "<a href=\"places-oneletter.php?firstchar=$firstchar&amp;psearch=$psearch&amp;tree=$tree\">" . uiTextSnippet('placelist') . ": $decodedfirstchar (" . uiTextSnippet('tree') . ": $tree)</a>";
  $wherestr = " gedcom = \"$tree\"";
  $wherestr2 = " AND gedcom = \"$tree\"";
} else {
  $treestr = $treestr2 = "";
  $places_all_url = "places-all.php";
  $places_url = "placesMain.php";
  $logstring = "<a href=\"places-oneletter.php?firstchar=$firstchar&amp;psearch=$psearch\">" . uiTextSnippet('placelist') . ": $decodedfirstchar</a>";
  $wherestr = $wherestr2 = "";
}
$offsetorg = $offset;
$offset = $offset ? $offset + 1 : 1;

if ($firstchar) {
  if ($wherestr) {
    $wherestr .= " AND ";
  }
  $wherestr .= "trim(substring_index(place,',',-$offset)) LIKE \"$firstchar%\"";
}
if ($psearch) {
  if ($wherestr) {
    $wherestr .= " AND ";
  }
  $psearchslashed = addslashes($psearch);
  $wherestr .= $offsetorg ? "trim(substring_index(place,',',-$offsetorg)) = \"$psearchslashed\"" : "place LIKE \"%$psearch%\"";
}

if ($wherestr) {
  $wherestr = "WHERE $wherestr";
}
//if doing a locality search, link directly to placesearch
if ($stretch) {
  $query = "SELECT distinct place as myplace, place as wholeplace, count( place ) as placecount, gedcom "
          . "FROM $places_table $wherestr GROUP BY myplace ORDER by myplace";
  $places_oneletter_url = "placesearch.php?";
} else {
  $query = "SELECT distinct trim(substring_index(place,',',-$offset)) as myplace, trim(place) as wholeplace, count(place) as placecount, gedcom "
          . "FROM $places_table $wherestr GROUP BY myplace ORDER by myplace";
  $places_oneletter_url = "places-oneletter.php?";
}
$result = tng_query($query);
if (tng_num_rows($result) == 1) {
  $row = tng_fetch_assoc($result);
  if ($row['myplace'] == $psearch) {
    header("Location: placesearch.php?{$treestr}psearch=$psearch&oper=eq");
  } else {
    $result = tng_query($query);
  }
}
writelog($logstring);
preparebookmark($logstring);

$displaychar = $decodedfirstchar ? $decodedfirstchar : uiTextSnippet('all');

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('placelist') . ": $displaychar");
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/location.svg'><?php echo uiTextSnippet('placelist') . ": $displaychar"; ?></h2>
    <br class='clearleft'>
    <?php
    $hiddenfields[] = array('name' => 'firstchar', 'value' => $firstchar);
    $hiddenfields[] = array('name' => 'psearch', 'value' => $psearch);
    $hiddenfields[] = array('name' => 'offset', 'value' => $offsetorg);
    if ($tree && !$tngconfig['places1tree']) {
      echo treeDropdown(array('startform' => true, 'endform' => true, 'action' => 'places-oneletter', 'method' => 'get', 'name' => 'form1', 'id' => 'form1', 'hidden' => $hiddenfields));
    }
    beginFormElement("places-oneletter", "get");
    ?>
      <div class="card">
        <div class='card-header'>
          <?php
          echo uiTextSnippet('placescont') . ": <input name='psearch' type='text' />\n";
          if ($tree && !$tngconfig['places1tree']) {
            echo "<input name='tree' type='hidden' value=\"$tree\" />\n";
          }
          echo "<input name='stretch' type='hidden' value='1'>\n";
          echo "<input name='pgo' type='submit' value=\"" . uiTextSnippet('go') . "\" />\n";
          ?>
        </div>
        <br><br><?php echo "<a href=\"$places_url" . "{$treestr2}\">" . uiTextSnippet('mainplacepage') . "</a> &nbsp;|&nbsp; <a href=\"$places_all_url" . "{$treestr2}\">" . uiTextSnippet('showallplaces') . "</a>"; ?>
      </div>
    <?php endFormElement(); ?>
    <br>
    <div class="card">
      <div class='card-header'>
        <h4><?php echo uiTextSnippet('placelist') . ": $decodedfirstchar, " . uiTextSnippet('sortedalpha') . " (" . uiTextSnippet('numoccurrences') . "):"; ?></h4>
        <p class="small"><?php echo uiTextSnippet('showmatchingplaces'); ?></p>
      </div>
      <table class="table table-sm">
        <tr>
          <td class="plcol">
            <?php
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
                $olplace = $place2 = urlencode($place['myplace']);
                if ($place2) {
                  $commaOnEnd = false;
                  $poffset = $stretch ? "" : "offset=$offset&amp;";
                  if (substr($place['wholeplace'], 0, 1) == ',' && trim(substr($place['wholeplace'], 1)) == $place['myplace']) {
                    $place3 = addslashes($place['wholeplace']);
                    $commaOnEnd = true;
                    $place2 = urlencode($place['wholeplace']);
                    $placetitle = $place['wholeplace'];
                  } else {
                    $place3 = addslashes($place['myplace']);
                    $placetitle = $place['myplace'];
                  }

                  $query = "SELECT count(place) as placecount "
                          . "FROM $places_table WHERE place = \"$place3\" $wherestr2";
                  $result2 = tng_query($query);
                  $countrow = tng_fetch_assoc($result2);
                  $specificcount = $countrow['placecount'];
                  tng_free_result($result2);

                  $searchlink = $specificcount ? " <a href=\"placesearch.php?{$treestr}psearch=$place2\" title=\"" .
                          uiTextSnippet('findplaces') . "\"><img class='icon-xs-inline' src='svg/magnifying-glass.svg' alt=\"" . uiTextSnippet('findplaces') . "\"></a>" : "";
                  if ($place['placecount'] > 1 || ($place['myplace'] != $place['wholeplace'] && !$commaOnEnd)) {
                    $name = "<a href=\"$places_oneletter_url" . $poffset;
                    if ($tree && !$tngconfig['places1tree']) {
                      $name .= "tree={$place['gedcom']}&amp;";
                    }
                    $name .= "psearch=$olplace\">";
                    $name .= $place['myplace'];
                    $name .= "</a>";

                    echo "$snnum. $name ({$place['placecount']})$searchlink<br>\n";
                  } else {
                    echo "$snnum. $placetitle$searchlink<br>\n";
                  }
                  $snnum++;
                  $num_in_col_ctr++;
                  if ($num_in_col_ctr == $num_in_col) {
                    echo "</td>\n<td class=\"table-dblgutter\"></td>\n<td class=\"plcol\">";
                    $num_in_col_ctr = 0;
                  }
                }
              }
              tng_free_result($result);
            }
            ?>
          </td>
        </tr>
      </table>
    </div>
    <br>
  <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
