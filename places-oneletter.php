<?php
require 'tng_begin.php';

$psearch = trim($psearch);
$decodedfirstchar = $firstchar ? stripslashes(urldecode($firstchar)) : stripslashes($psearch);

$logstring = "<a href=\"places-oneletter.php?firstchar=$firstchar&amp;psearch=$psearch\">" . uiTextSnippet('placelist') . ": $decodedfirstchar</a>";

$offsetorg = $offset;
$offset = $offset ? $offset + 1 : 1;

$wherestr = '';
if ($firstchar) {
  $wherestr .= "trim(substring_index(place,',',-$offset)) LIKE \"$firstchar%\"";
}
if ($psearch) {
  if ($wherestr) {
    $wherestr .= ' AND ';
  }
  $psearchslashed = addslashes($psearch);
  $wherestr .= $offsetorg ? "trim(substring_index(place,',',-$offsetorg)) = \"$psearchslashed\"" : "place LIKE \"%$psearch%\"";
}
if ($wherestr) {
  $wherestr = "WHERE $wherestr";
}
//if doing a locality search, link directly to placesearch
if ($stretch) {
  $query = "SELECT distinct place AS myplace, place AS wholeplace, count( place ) AS placecount FROM places $wherestr GROUP BY myplace ORDER by myplace";
  $places_oneletter_url = 'placesearch.php?';
} else {
  $query = "SELECT distinct trim(substring_index(place,',',-$offset)) AS myplace, trim(place) AS wholeplace, count(place) AS placecount FROM places $wherestr GROUP BY myplace ORDER by myplace";
  $places_oneletter_url = 'places-oneletter.php?';
}
$result = tng_query($query);
if (tng_num_rows($result) == 1) {
  $row = tng_fetch_assoc($result);
  if ($row['myplace'] == $psearch) {
    header("Location: placesearch.php?psearch=$psearch&oper=eq");
  } else {
    $result = tng_query($query);
  }
}
writelog($logstring);
preparebookmark($logstring);

$displaychar = $decodedfirstchar ? $decodedfirstchar : uiTextSnippet('all');

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
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
    $hiddenfields[] = ['name' => 'firstchar', 'value' => $firstchar];
    $hiddenfields[] = ['name' => 'psearch', 'value' => $psearch];
    $hiddenfields[] = ['name' => 'offset', 'value' => $offsetorg];
    beginFormElement('places-oneletter', 'get');
    ?>
      <div class="card">
        <div class='card-header'>
          <?php
          echo uiTextSnippet('placescont') . ": <input name='psearch' type='text' />\n";
          echo "<input name='stretch' type='hidden' value='1'>\n";
          echo "<input name='pgo' type='submit' value=\"" . uiTextSnippet('go') . "\" />\n";
          ?>
        </div>
        <br><br><?php echo '<a href="placesMain.php">' . uiTextSnippet('mainplacepage') . '</a> &nbsp;|&nbsp; <a href="places-all.php">' . uiTextSnippet('showallplaces') . '</a>'; ?>
      </div>
    <?php endFormElement(); ?>
    <br>
    <div class="card">
      <div class='card-header'>
        <h4><?php echo uiTextSnippet('placelist') . ": $decodedfirstchar, " . uiTextSnippet('sortedalpha') . ' (' . uiTextSnippet('numoccurrences') . '):'; ?></h4>
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
                  $poffset = $stretch ? '' : "offset=$offset&amp;";
                  if (substr($place['wholeplace'], 0, 1) == ',' && trim(substr($place['wholeplace'], 1)) == $place['myplace']) {
                    $place3 = addslashes($place['wholeplace']);
                    $commaOnEnd = true;
                    $place2 = urlencode($place['wholeplace']);
                    $placetitle = $place['wholeplace'];
                  } else {
                    $place3 = addslashes($place['myplace']);
                    $placetitle = $place['myplace'];
                  }
                  $query = "SELECT count(place) AS placecount FROM places WHERE place = \"$place3\"";
                  $result2 = tng_query($query);
                  $countrow = tng_fetch_assoc($result2);
                  $specificcount = $countrow['placecount'];
                  tng_free_result($result2);

                  $searchlink = $specificcount ? " <a href=\"placesearch.php?psearch=$place2\" title=\"" . uiTextSnippet('findplaces') . '">'
                      . "<img class='icon-xs-inline' src='svg/magnifying-glass.svg' alt=\"" . uiTextSnippet('findplaces') . '"></a>' : '';
                  if ($place['placecount'] > 1 || ($place['myplace'] != $place['wholeplace'] && !$commaOnEnd)) {
                    $name = "<a href=\"$places_oneletter_url" . $poffset;
                    $name .= "psearch=$olplace\">";
                    $name .= $place['myplace'];
                    $name .= '</a>';

                    echo "$snnum. $name ({$place['placecount']})$searchlink<br>\n";
                  } else {
                    echo "$snnum. $placetitle$searchlink<br>\n";
                  }
                  $snnum++;
                  $num_in_col_ctr++;
                  if ($num_in_col_ctr == $num_in_col) {
                    echo "</td>\n";
                    echo "<td class='table-dblgutter'></td>\n";
                    echo "<td class='plcol'>";
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
