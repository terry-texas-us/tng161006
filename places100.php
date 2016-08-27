<?php
require 'tng_begin.php';

$topnum = preg_replace("/[^0-9]/", '', $topnum);

$logstring = "<a href='places100.php?topnum=$topnum'>" . xmlcharacters(uiTextSnippet('placelist') . " &mdash; " . uiTextSnippet('top') . " $topnum") . "</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('placelist') . ": " . uiTextSnippet('top') . " $topnum");
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/location.svg'><? echo uiTextSnippet('placelist') . ": " . uiTextSnippet('top') . " $topnum"; ?></h2>
    <br class='clearleft'>
    <?php
    beginFormElement("places100", "get");
    ?>
      <div class="card">
        <?php echo uiTextSnippet('showtop'); ?>&nbsp;
        <input name='topnum' type='text' value="<?php echo $topnum; ?>" size="4" maxlength="4"/> <?php echo uiTextSnippet('byoccurrence'); ?>&nbsp;
        <input type='submit' value="<?php echo uiTextSnippet('go'); ?>"/>
      </div>
      <?php
      endFormElement();
      beginFormElement("places-oneletter", "get");
      ?>
      <div class="card">
        <?php
        echo uiTextSnippet('placescont') . ": <input name='psearch' type='text' />\n";
        echo "<input name='stretch' type='hidden' value='1' />\n";
        echo "<input name='pgo' type='submit' value='" . uiTextSnippet('go') . "' />\n";
        if (!$decodedfirstchar) {
          $decodedfirstchar = uiTextSnippet('top') . " $topnum";
        }
        ?>
        <?php echo "<a href='placesMain.php'>" . uiTextSnippet('mainplacepage') . "</a> &nbsp;|&nbsp; <a href='places-all.php'>" . uiTextSnippet('showallplaces') . "</a>"; ?>
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
            $wherestr = "";
            if ($psearch) {
              $wherestr .= " AND trim(substring_index(place,',',-$offset)) = \"$psearch\"";
            }
            $offsetorg = $offset;
            $offset = $offset ? $offset + 1 : 1;
            $offsetplus = $offset + 1;

            $topnum = $topnum ? $topnum : 100;
            $query = "SELECT distinct trim(substring_index(place,',',-$offset)) AS myplace, count(place) AS placecount FROM $places_table WHERE trim(substring_index(place,',',-$offset)) != \"\" $wherestr GROUP BY myplace ORDER by placecount DESC, myplace LIMIT $topnum";

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

                $query = "SELECT count(place) AS placecount FROM $places_table WHERE place = \"" . addslashes($place['myplace']) . "\" $wherestr";
                $result2 = tng_query($query);
                $countrow = tng_fetch_assoc($result2);
                $specificcount = $countrow['placecount'];
                tng_free_result($result2);

                $searchlink = $specificcount ? " <a href='placesearch.php?psearch=$place2'>"
                        . "<img class='icon-xs-inline' src='svg/magnifying-glass.svg' alt=''></a>" : "";
                if ($place[placecount] > 1 || !$specificcount) {
                  $name = "<a href=\"places-oneletter.php?offset=$offset&amp;psearch=$place2\">{$place['myplace']}</a>";
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