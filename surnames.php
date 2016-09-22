<?php
require 'tng_begin.php';

$logstring = "<a href='surnames.php'>" . xmlcharacters(uiTextSnippet('surnames')) . '</a>';
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();
$sectionTitle = str_replace('xxx', '30', uiTextSnippet('top30'));
$tooltip['showall'] = uiTextSnippet('showallsurnames') . ' (' . uiTextSnippet('sortedalpha') . ')';

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('surnames'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='surnames'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/person.svg'><?php echo uiTextSnippet('surnames'); ?></h2>
    <br class='clearleft'>
    <hr>
    <form class='form-inline' action='surnamesTop.php' method='get'>
      <div class='form-group'>
        <label for='topnum'><?php echo uiTextSnippet('showtop'); ?></label>
        <input class='form-control' name='topnum' type='text' value='100' size='4' maxlength='4'><span class='verbose-md'><?php echo uiTextSnippet('byoccurrence'); ?></span>
        <input class='btn btn-outline-secondary' type='submit' value="<?php echo uiTextSnippet('go'); ?>">
      </div>
      <button class='btn btn-outline-secondary' type='button' title='<?php echo $tooltip['showall']; ?>'><a href='surnamesAll.php'><?php echo uiTextSnippet('showall') . '</a>'; ?></button>
    </form>
    <br>
    <?php
    $collen = 15;
    $cols = 2;
    $nosurname = urlencode(uiTextSnippet('nosurname'));

    $wherestr = "WHERE lastname != ''";

    $livingPrivateCondition = getLivingPrivateRestrictions('people', false, false);

    if ($livingPrivateCondition) {
      $wherestr .= " AND $livingPrivateCondition";
    }
    $surnamestr = $lnprefixes ? "TRIM(CONCAT_WS(' ', lnprefix,lastname))" : 'lastname';
    if ($tngconfig['ucsurnames']) {
      $surnamestr = "UCASE($surnamestr)";
    }
    $query = "SELECT UCASE($binary $surnamestr) AS lastname, $surnamestr AS lowername, COUNT(UCASE($binary lastname)) AS lncount FROM people $wherestr GROUP BY lowername ORDER by lncount DESC, lastname LIMIT 30";
    $result = tng_query($query);
    if ($result) {
      $maxcount = 0;
      $count = 1;
      $col = -1;
      while ($surname = tng_fetch_assoc($result)) {
        $surname2 = urlencode($surname['lastname']);
        if (!$maxcount) {
          $maxcount = $surname['lncount'];
        }
        $tally = $surname['lncount'];
        $tally_fmt = number_format($tally);
        $thiswidth = floor($tally / $maxcount * 100);
        if (($count - 1) % $collen == 0) {
          $col++;
        }
        $linkstr2col[$col] .= "<tr>\n";
        $linkstr2col[$col] .= "<td>$count.</td>";
        $linkstr2col[$col] .= "<td style='width:40%'><a href='search.php?mylastname=$surname2&amp;lnqualify=equals'>{$surname['lowername']}</a> ($tally_fmt)</td>\n";
        
        $linkstr2col[$col] .= "<td class='bar-holder'>";
        $linkstr2col[$col] .= "<div style='width:{$thiswidth}%;' class='bar rightround' title=\"{$surname['lowername']} ($tally_fmt)\">";
        $linkstr2col[$col] .= "<a href=\"search.php?mylastname=$surname2&amp;lnqualify=equals\"></a>";
        $linkstr2col[$col] .= "</div>";
        $linkstr2col[$col] .= "</td>";
        
        $linkstr2col[$col] .= "</tr>\n";
        
        $count++;
      }
      tng_free_result($result);
      ?>
      <div class='card'>
        <div class='card-header'>
          <h5><?php echo $sectionTitle . ' (' . uiTextSnippet('totalnames') . '):'; ?></h5>
        </div>
        <div class='card-block'>
          <div class='card-text'>
            <div class='row'>
            <?php
            for ($i = 0; $i < $cols; $i++) {
              echo "<div class='col-md-6'>\n";
              echo "<table class='table-histogram'>\n";
              echo $linkstr2col[$i];
              echo "</table>\n";
              echo "</div>\n";
            }
            ?>
            </div>
          </div>
        </div>
      </div>
      <?php
    }
    echo $publicFooterSection->build();
    echo scriptsManager::buildScriptElements($flags, 'public');
    ?>
  </section> <!-- .container -->
</body>
</html>
