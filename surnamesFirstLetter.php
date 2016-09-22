<?php
/**
 * Name history: surnames-oneletter.php
 */

set_time_limit(0);
require 'tng_begin.php';

$firstchar = mb_substr($firstchar, 0, 1, $charset);
$decodedfirstchar = stripslashes(urldecode($firstchar));

$logstring = "<a href=\"surnamesFirstLetter.php?firstchar=$firstchar\">" . xmlcharacters(uiTextSnippet('surnames') . ': ' . uiTextSnippet('beginswith') . " $decodedfirstchar") . '</a>';
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('surnames') . ': ' . uiTextSnippet('beginswith') . " $decodedfirstchar");
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/person.svg'><?php echo uiTextSnippet('surnames') . ': ' . uiTextSnippet('beginswith') . " $decodedfirstchar"; ?></h2>
    <br class='clearleft'>

    <nav class='breadcrumb'>
      <a class='breadcrumb-item' href='surnames.php'><?php echo uiTextSnippet('mainsurnamepage'); ?></a>
      <a class='breadcrumb-item' href='surnamesAll.php'><?php echo uiTextSnippet('allsurnames'); ?></a>
      <span class='breadcrumb-item active'><?php echo uiTextSnippet('surnames-firstletter') . ':' . " $decodedfirstchar"; ?></span>
    </nav>
    <div class='card'>
      <div class='card-header'>
        <?php echo uiTextSnippet('allbeginningwith') . " $decodedfirstchar, " . uiTextSnippet('sortedalpha') . ' (' . uiTextSnippet('totalnames') . '):'; ?>
        <p class="small">
          <?php echo uiTextSnippet('showmatchingsurnames'); ?>
        </p>
      </div>
      <div class='card-block'>
      <div class='card-text'>
        <div class='row'>
          <div class='col-md-3'>
            <?php
            $wherestr = '';

            $livingPrivateCondition = getLivingPrivateRestrictions('people', false, false);
            if ($livingPrivateCondition) {
              $wherestr .= ' AND ' . $livingPrivateCondition;
            }

            $surnamestr = $lnprefixes ? "TRIM(CONCAT_WS(' ',lnprefix,lastname) )" : 'lastname';
            if ($tngconfig['ucsurnames']) {
              $surnamestr = "ucase($surnamestr)";
            }
            $firstchar = $firstchar == '"' ? '\\"' : $firstchar;
            $query = "SELECT ucase( $binary $surnamestr ) AS lastname, $surnamestr AS lowername, ucase($binary lastname) AS binlast, count( ucase($binary lastname) ) AS lncount FROM people WHERE ucase($binary TRIM(lastname)) LIKE \"$firstchar%\" $wherestr GROUP BY lowername ORDER by binlast";
            $result = tng_query($query);
            $topnum = tng_num_rows($result);
            if ($result) {
              $snnum = 1;
              if (!isset($numcols) || $numcols > 4) {
                $numcols = 4;
              }
              $num_in_col = ceil($topnum / $numcols);

              $num_in_col_ctr = 0;
              while ($surname = tng_fetch_assoc($result)) {
                $surname2 = urlencode($surname['lastname']);
                $name = $surname['lastname'] ? "<a href=\"search.php?mylastname=$surname2&amp;lnqualify=equals\">{$surname['lowername']}</a>" : uiTextSnippet('nosurname');
                echo "$snnum. $name ({$surname['lncount']})<br>\n";
                $snnum++;
                $num_in_col_ctr++;
                if ($num_in_col_ctr == $num_in_col) {
                  echo "</div>\n";
                  echo "<div class='col-md-3'>\n";
                  $num_in_col_ctr = 0;
                }
              }
              tng_free_result($result);
            }
            ?>
          </div>
        </div>
      </div>
      </div>
    </div>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>