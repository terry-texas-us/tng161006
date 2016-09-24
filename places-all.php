<?php
require 'tng_begin.php';

$logstring = "<a href='places-all.php'>" . uiTextSnippet('allplaces') . '</a>';
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('placelist') . ': ' . uiTextSnippet('allplaces'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/location.svg'><?php echo uiTextSnippet('places') . ': ' . uiTextSnippet('allplaces'); ?></h2>
    <br class='clearleft'>
    <?php
    $offset = 1;

    $query = "SELECT distinct ucase(left(trim(substring_index(place,',',-$offset)),1)) AS firstchar FROM places GROUP BY firstchar ORDER by firstchar";
    $result = tng_query($query);
    if ($result) {
      $initialchar = 1;

      while ($place = tng_fetch_assoc($result)) {
        if ($initialchar != 1) {
        }
        if ($place['firstchar'] != '' && $place['firstchar'] != '_') {
          $firstchars[$initialchar] = $place['firstchar'];
          $initialchar++;
        }
      }
      tng_free_result($result);
    }
    ?>
    <nav class='breadcrumb'>
      <a class='breadcrumb-item' href='places.php'><?php echo uiTextSnippet('mainplacepage'); ?></a>
      <span class='breadcrumb-item active'><?php echo uiTextSnippet('allplaces'); ?></span>
    </nav>
    
    <div class='card'>
      <div class='card-header'>
        <?php echo uiTextSnippet('placesstarting'); ?>
      </div>
      <div class='card-block'>
        <div class='card-text'>
          <form class='form-inline' action='places-containing.php' method='get'>
            <label for='psearch'><?php echo uiTextSnippet('placescont') . ': '; ?></label>
            <input class='form-control' name='psearch' type='text'>
            <input class='form-control' name='pgo' type='submit' value="<?php echo uiTextSnippet('go'); ?>">
            <input name='stretch' type='hidden' value='1'>
          </form>
        </div>
      </div>
    </div>
    <?php for ($scount = 1; $scount < $initialchar; $scount++) { ?>
      <div class='card'>
        <?php
        $urlfirstchar = addslashes($firstchars[$scount]);
        if ($urlfirstchar) {
          echo "<div class='card-title'>\n";
            echo "<a id=\"char$scount\">{$firstchars[$scount]}</a>\n";
          echo "</div>\n";
          ?>
          <?php
          $query = "SELECT trim(substring_index(place,',',-$offset)) AS myplace, count(place) AS placecount FROM places WHERE trim(substring_index(place,',',-$offset)) LIKE \"$urlfirstchar%\" GROUP BY myplace ORDER by myplace";
          $result = tng_query($query);
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
              $place2 = urlencode($place['myplace']);
              $commaOnEnd = false;
              $poffset = $stretch ? '' : "offset=$offset&amp;";
              if (substr($place['wholeplace'], 0, 1) == ',' && trim(substr($place[wholeplace], 1)) == $place['myplace']) {
                $place3 = addslashes($place['wholeplace']);
                $commaOnEnd = true;
                $place2 = urlencode($place['wholeplace']);
                $placetitle = $place['wholeplace'];
              } else {
                $place3 = addslashes($place['myplace']);
                $placetitle = $place['myplace'];
              }

              $query = "SELECT count(place) AS placecount FROM places WHERE place = '$place3'";
              $result2 = tng_query($query);
              $countrow = tng_fetch_assoc($result2);
              $specificcount = $countrow['placecount'];
              tng_free_result($result2);

              $searchlink = $specificcount ? " <a href=\"placesearch.php?psearch=$place2\" title=\"" .
                      uiTextSnippet('findplaces') . "\"><img class='icon-xs-inline' src='svg/magnifying-glass.svg' alt=\"uiTextSnippet(findplaces)\"></a>" : '';
              if ($place['placecount'] > 1 || ($place['myplace'] != $place['wholeplace'] && !$commaOnEnd)) {
                $name = '<a href="places-containing.php?' . $poffset;
                $name .= "psearch=$place2\">" . str_replace(['<', '>'], ['&lt;', '&gt;'], $place['myplace']) . '</a>';
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
        <?php } ?>
      </div>
    <?php } ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>