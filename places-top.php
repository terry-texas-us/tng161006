<?php
/**
 * Name history: places100.php
 */

require 'tng_begin.php';

$topnum = preg_replace('/[^0-9]/', '', $topnum);

$logstring = "<a href='places-top.php?topnum=$topnum'>" . xmlcharacters(uiTextSnippet('placelist') . ' &mdash; ' . uiTextSnippet('top') . " $topnum") . '</a>';
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();
$tooltip['showall'] = uiTextSnippet('showallplaces') . ' (' . uiTextSnippet('sortedalpha') . ')';

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('places') . ': ' . uiTextSnippet('top') . " $topnum");
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='places'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/location.svg'><? echo uiTextSnippet('placelist') . ': ' . uiTextSnippet('top') . " $topnum"; ?></h2>
    <br class='clearleft'>
    
    <nav class='breadcrumb'>
      <a class='breadcrumb-item' href='places.php'><?php echo uiTextSnippet('mainplacepage'); ?></a>
      <span class='breadcrumb-item active'><?php echo uiTextSnippet('topplaces'); ?></span>
    </nav>

    <form class='form-inline' action='places-top.php' method='get'>
      <div class='form-group'>
        <label for='topnum'><?php echo uiTextSnippet('showtop'); ?></label>
        <input class='form-control' name='topnum' type='text' value="<?php echo $topnum; ?>" size='4' maxlength='4'><span class='verbose-md'><?php echo uiTextSnippet('byoccurrence'); ?></span>
        <input class='btn btn-outline-secondary' type='submit' value="<?php echo uiTextSnippet('go'); ?>">
      </div>
    </form>
    
    <form class='form-inline' action='places-containing.php' method='get'>
      <label for='psearch'><?php echo uiTextSnippet('placescont') . ': '; ?></label>
      <input class='form-control' name='psearch' type='text'>
      <input class='form-control' name='pgo' type='submit' value='<?php echo uiTextSnippet('go'); ?>'>
      <button class='btn btn-outline-secondary' type='button' title='<?php echo $tooltip['showall']; ?>'><a href='places-all.php'><?php echo uiTextSnippet('showall'); ?></a></button>
      <input name='stretch' type='hidden' value='1'>
      <?php 
      if (!$decodedfirstchar) {
        $decodedfirstchar = uiTextSnippet('top') . " $topnum";
      }
      ?>
    </form>
    <br>
    <div class='card'>
      <div class='card-header'>
        <h5><?php echo uiTextSnippet('places') . ": $decodedfirstchar, " . uiTextSnippet('sortedalpha'); ?></h5>
      </div>
      <table class="table table-sm">
        <tr>
          <td class="plcol">
            <?php
            $wherestr = '';
            if ($psearch) {
              $wherestr .= " AND trim(substring_index(place,',',-$offset)) = \"$psearch\"";
            }
            $offsetorg = $offset;
            $offset = $offset ? $offset + 1 : 1;
            $offsetplus = $offset + 1;

            $topnum = $topnum ? $topnum : 100;
            $query = "SELECT distinct trim(substring_index(place,',',-$offset)) AS myplace, count(place) AS placecount FROM places WHERE trim(substring_index(place,',',-$offset)) != \"\" $wherestr GROUP BY myplace ORDER by placecount DESC, myplace LIMIT $topnum";

            $result = tng_query($query);
            $topnum = tng_num_rows($result);
            if ($result) {
              $counter = 1;
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

                $query = 'SELECT count(place) AS placecount FROM places WHERE place = "' . addslashes($place['myplace']) . "\" $wherestr";
                $result2 = tng_query($query);
                $countrow = tng_fetch_assoc($result2);
                $specificcount = $countrow['placecount'];
                tng_free_result($result2);

                $searchlink = $specificcount ? " <a href='placesearch.php?psearch=$place2'><img class='icon-xs-inline' src='svg/magnifying-glass.svg' alt=''></a>" : '';
                if ($place[placecount] > 1 || !$specificcount) {
                  $name = "<a href=\"places-containing.php?offset=$offset&amp;psearch=$place2\">{$place['myplace']}</a>";
                  echo "$counter. $name ({$place['placecount']}) $searchlink<br>\n";
                } else {
                  echo "$counter. {$place['myplace']} $searchlink<br>\n";
                }
                $counter++;
                $num_in_col_ctr++;
                if ($num_in_col_ctr == $num_in_col) {
                  echo "</td>\n<td></td>\n<td>";
                  $num_in_col_ctr = 0;
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