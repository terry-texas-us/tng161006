<?php
/**
 * Name history: surnames-all.php
 */

set_time_limit(0);
require 'tng_begin.php';

$logstring = "<a href='surnamesAll.php'>" . uiTextSnippet('surnames') . ': ' . uiTextSnippet('allsurnames') . '</a>';
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('surnames') . ' - ' . uiTextSnippet('allsurnames'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/person.svg'><?php echo uiTextSnippet('surnames') . ': ' . uiTextSnippet('all'); ?></h2>
    <br class='clearleft'>
    <?php
    $wherestr = '';
    $wherestr2 = '';

    $livingPrivateCondition = getLivingPrivateRestrictions('people', false, false);

    if ($livingPrivateCondition) {
      $wherestr .= "WHERE $livingPrivateCondition";
      $wherestr2 .= " AND $livingPrivateCondition";
    }
    $linkstr = '';
    $nosurname = urlencode(uiTextSnippet('nosurname'));
    $query = "SELECT ucase(left(lastname,1)) AS firstchar, ucase( $binary left(lastname,1) ) AS binfirstchar FROM people $wherestr GROUP BY binfirstchar ORDER by binfirstchar";
    $result = tng_query($query);
    if ($result) {
      $initialchar = 1;

      while ($surname = tng_fetch_assoc($result)) {
        if ($initialchar != 1) {
          $linkstr .= ' ';
        }
        if ($surname['firstchar'] == '') {
          $surname['firstchar'] = uiTextSnippet('nosurname');
          $linkstr .= "<a href=\"search.php?mylastname=$nosurname&amp;lnqualify=equals\">" . uiTextSnippet('nosurname') . '</a> ';
        } else {
          if ($surname['firstchar'] != '_') {
//            $linkstr .= "<a href=\"#char$initialchar\">{$surname['firstchar']}</a>";
            $linkstr .= "<a href=\"surnamesFirstLetter.php?firstchar={$surname['firstchar']}\">{$surname['firstchar']}</a>";
            $firstchars[$initialchar] = $surname['firstchar'];
            $initialchar++;
          }
        }
      }
      tng_free_result($result);
    }
    ?>
    <nav class='breadcrumb'>
      <a class='breadcrumb-item' href='surnames.php'><?php echo uiTextSnippet('mainsurnamepage'); ?></a>
      <span class='breadcrumb-item active'><?php echo uiTextSnippet('allsurnames'); ?></span>
    </nav>
    
    <div class='card'>
      <div class='card-header'>
        <h5><?php echo uiTextSnippet('surnamesstarting'); ?></h5>
      </div>
      <div class='card-block'>
        <div class='card-text'>
          <?php echo $linkstr; ?>
        </div>
      </div>
    </div>
    
    <?php
    for ($scount = 1; $scount < $initialchar; $scount++) {
      echo "<a id=\"char$scount\"></a>\n";
      $urlfirstchar = addslashes($firstchars[$scount]);
      $panelClass = 'panel-collapse collapse' . (($scount === 1) ? ' in' : '');
      ?>
      <div class='card'>
        <div class='card-header text-md-center'>
          <?php echo $firstchars[$scount]; ?>
        </div>
        <div class='card-block'>
          <div class='card-text'>

            <div class='row'>
              <div class='col-md-3'>
                <?php
                $surnamestr = $lnprefixes ? "TRIM(CONCAT_WS(' ',lnprefix,lastname) )" : 'lastname';
                if ($tngconfig['ucsurnames']) {
                  $surnamestr = "ucase($surnamestr)";
                }
                $query = "SELECT ucase( $binary $surnamestr ) AS lastname, $surnamestr AS lowername, ucase($binary lastname) AS binlast, count( ucase($binary lastname) ) AS lncount FROM people WHERE ucase($binary TRIM(lastname)) LIKE \"$urlfirstchar%\" $wherestr2 GROUP BY lowername ORDER by binlast";
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
                    if ($surname['lastname']) {
                      $name = "<a href=\"search.php?mylastname=$surname2&amp;lnqualify=equals\">{$surname['lowername']}</a>";
                    } else {
                      $name = "<a href=\"search.php?mylastname=$nosurname&amp;lnqualify=equals\">" . uiTextSnippet('nosurname') . '</a>';
                    }
                    echo "$snnum. $name ({$surname['lncount']})<br>\n";
                    $snnum++;
                    $num_in_col_ctr++;
                    if ($num_in_col_ctr == $num_in_col) {
                      echo "</div>\n";
                      echo "<div class='col-md-3'>";
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
      </div> <!-- .card -->
    <?php
    }
    echo $publicFooterSection->build();
    ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>