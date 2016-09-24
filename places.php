<?php
require 'tng_begin.php';

$logstring = "<a href='places.php'>" . uiTextSnippet('placelist') . '</a>';
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();
$tooltip['showall'] = uiTextSnippet('showallplaces') . ' (' . uiTextSnippet('sortedalpha') . ')';

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('places'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='places'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/location.svg'><?php echo uiTextSnippet('places'); ?></h2>
    <br class='clearleft'>
    <hr>
    
    <form class='form-inline' action='places-top.php' method='get'>
      <label for='topnum'><?php echo uiTextSnippet('showtop'); ?></label>
      <input class='form-control' name='topnum' type='text' value='100' size='4' maxlength='4'><span class='verbose-md'><?php echo uiTextSnippet('byoccurrence'); ?></span>
      <input class='btn btn-outline-secondary' type='submit' value="<?php echo uiTextSnippet('go'); ?>">
      <button class='btn btn-outline-secondary' type='button' title='<?php echo $tooltip['showall']; ?>'><a href='places-all.php'><?php echo uiTextSnippet('showall'); ?></a></button>
    </form>
    
    <?php
    $collen = 10;
    $cols = 2;

    $offsetorg = $offset;
    $offset = $offset ? $offset + 1 : 1;

    $query = "SELECT TRIM(SUBSTRING_INDEX(place, ',', -$offset)) AS myplace, COUNT(place) AS placecount FROM places WHERE TRIM(SUBSTRING_INDEX(place, ',', -$offset)) != '' GROUP BY myplace ORDER by placecount DESC LIMIT 30";
    $result = tng_query($query);
    $maxcount = 0;
    if ($result) {
      $count = 1;
      $col = -1;
      while ($place = tng_fetch_assoc($result)) {
        $place2 = urlencode($place['myplace']);
        if ($place2 != '') {
          if (!$maxcount) {
            $maxcount = $place['placecount'];
          }
          $tally = $place['placecount'];
          $tally_fmt = number_format($tally);
          $thiswidth = floor($tally / $maxcount * 100);
          $query = 'SELECT count(place) AS placecount FROM places WHERE place = "' . addslashes($place['myplace']) . '"';
          $result2 = tng_query($query);
          $countrow = tng_fetch_assoc($result2);
          $specificcount = $countrow['placecount'];
          tng_free_result($result2);

          $searchlink = $specificcount ? " <a href='placesearch.php?psearch=$place2'><img class='icon-xs-inline' src='svg/magnifying-glass.svg' alt=''></a>" : '';
          $name = $place['placecount'] > 1 || !$specificcount ? "<a href=\"places-containing.php?offset=$offset&amp;psearch=$place2\">" . str_replace(['<', '>'], ['&lt;', '&gt;'], $place['myplace']) . "</a> ($tally_fmt)" : $place['myplace'];
          if (($count - 1) % $collen == 0) {
            $col++;
          }
          $linkstr2col[$col] .= "<tr>\n";
          $linkstr2col[$col] .= "<td>$count.</td>";
          $linkstr2col[$col] .= "<td style='width:40%'>$name$searchlink</td>";
          
          $linkstr2col[$col] .= "<td class='bar-holder'>";
          $linkstr2col[$col] .= "<div class='bar rightround' style='width:{$thiswidth}%;'>";
          $linkstr2col[$col] .= "<a href=\"places-containing.php?offset=$offset&amp;psearch=$place2\" title=\"{$place['myplace']} ($tally_fmt)\"></a>";
          $linkstr2col[$col] .= "</div></td>";

          $linkstr2col[$col] .= "</tr>\n";

          $count++;
        }
      }
      tng_free_result($result);
    }
    ?>
    <form class='form-inline' action='places-containing.php' method='get'>
      <label for='psearch'><?php echo uiTextSnippet('placescont') . ': '; ?></label>
      <input class='form-control' name='psearch' type='text'>
      <input class='btn btn-outline-secondary' name='pgo' type='submit' value="<?php echo uiTextSnippet('go'); ?>">
      <input name='stretch' type='hidden' value='1'>
    </form>
    
    <div class='card'>
      <div class='card-header'>
        <?php
        echo str_replace('{xxx}', '30', uiTextSnippet('top{xxx}places')) . ' (' . uiTextSnippet('totalplaces') . '):'; 
        ?>
      </div>
      <div class='card-block'>
        <div class='row'>
          <?php
          for ($i = 0; $i < $cols; $i++) {
            echo "<div class='col-md-6'>\n";
            echo "<table class='table-histogram'>\n";
            echo $linkstr2col[$i];
            echo "</table>\n";
            echo "</div>";
          }
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