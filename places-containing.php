<?php
/**
 * Name history: places-oneletter.php
 */

require 'tng_begin.php';

$psearch = trim($psearch);
$decodedfirstchar = $firstchar ? stripslashes(urldecode($firstchar)) : stripslashes($psearch);

$logstring = "<a href=\"places-containing.php?firstchar=$firstchar&amp;psearch=$psearch\">" . uiTextSnippet('placelist') . ": $decodedfirstchar</a>";

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
  $places_oneletter_url = 'places-containing.php?';
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
$tooltip['showall'] = uiTextSnippet('showallplaces') . ' (' . uiTextSnippet('sortedalpha') . ')';

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('places-containing') . ": $displaychar");
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/location.svg'><?php echo uiTextSnippet('places-containing') . ": $displaychar"; ?></h2>
    <br class='clearleft'>
    
    <nav class='breadcrumb'>
      <a class='breadcrumb-item' href='places.php'><?php echo uiTextSnippet('places'); ?></a>
      <span class='breadcrumb-item active'><?php echo uiTextSnippet('places-containing'); ?></span>
    </nav>

    <?php
    $hiddenfields[] = ['name' => 'firstchar', 'value' => $firstchar];
    $hiddenfields[] = ['name' => 'psearch', 'value' => $psearch];
    $hiddenfields[] = ['name' => 'offset', 'value' => $offsetorg];
    ?>
    <form class='form-inline' action='places-containing.php' method='get'>
      <label for='psearch'><?php echo uiTextSnippet('placescont') . ': '; ?></label>
      <input class='form-control' name='psearch' type='text' value='<?php echo $displaychar; ?>'>
      <input class='btn btn-outline-secondary' name='pgo' type='submit' value="<?php echo uiTextSnippet('go'); ?>">
      <button class='btn btn-outline-secondary' type='button' title='<?php echo $tooltip['showall']; ?>'><a href='places-all.php'><?php echo uiTextSnippet('showall'); ?></a></button>
      <input name='stretch' type='hidden' value='1'>
    </form>
    
    <div class='card'>
      <div class='card-header'>
        <h5><?php echo uiTextSnippet('placelist') . ", " . uiTextSnippet('sortedalpha'); ?></h5>
      </div>
      <div class='card-block'>
        <div class='card-text'>
          <div class='row'>
          <div class='col-md-6'>
            <?php
            $topnum = tng_num_rows($result);
            if ($result) {
              $snnum = 1;
              if (!isset($numcols)) {
                $numcols = 2;
              }
              $num_in_col = ceil($topnum / $numcols);
              if ($numcols > 2) {
                $numcols = 2;
                $num_in_col = ceil($topnum / $numcols);
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
                    echo "</div>\n";
                    echo "<div class='col-md-6'>";
                    $num_in_col_ctr = 0;
                  }
                }
              }
              tng_free_result($result);
            }
            ?>
          </div>
          </div>
        </div>
      </div> <!-- .card-block -->
    </div>
    <br>
  <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
