<?php
set_time_limit(0);
require 'tng_begin.php';

$firstchar = mb_substr($firstchar, 0, 1, $charset);
$decodedfirstchar = stripslashes(urldecode($firstchar));

$logstring = "<a href=\"surnames-oneletter.php?firstchar=$firstchar\">" . xmlcharacters(uiTextSnippet('surnamelist') . ': ' . uiTextSnippet('beginswith') . " $decodedfirstchar$treestr") . '</a>';
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('surnamelist') . ': ' . uiTextSnippet('beginswith') . " $decodedfirstchar");
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <?php echo $publicHeaderSection->build(); ?>
  <h2><img class='icon-md' src='svg/person.svg'><?php echo uiTextSnippet('surnamelist') . ': ' . uiTextSnippet('beginswith') . " $decodedfirstchar"; ?></h2>
  <br class='clearleft'>

  <div class="titlebox">
    <div>
      <h4><?php echo uiTextSnippet('allbeginningwith') . " $decodedfirstchar, " . uiTextSnippet('sortedalpha') . ' (' . uiTextSnippet('totalnames') . '):'; ?></h4>
      <p class="small">
        <?php echo uiTextSnippet('showmatchingsurnames') . "&nbsp;&nbsp;&nbsp;<a href='surnames.php'>" . uiTextSnippet('mainsurnamepage') . "</a> &nbsp;|&nbsp; <a href='surnames-all.php'>" . uiTextSnippet('showallsurnames') . '</a>'; ?>
      </p>
    </div>
    <table class="sntable">
      <tr>
        <td class="sncol">
          <?php
          $wherestr = '';

          $more = getLivingPrivateRestrictions($people_table, false, false);
          if ($more) {
            $wherestr .= ' AND ' . $more;
          }

          $surnamestr = $lnprefixes ? "TRIM(CONCAT_WS(' ',lnprefix,lastname) )" : 'lastname';
          if ($tngconfig['ucsurnames']) {
            $surnamestr = "ucase($surnamestr)";
          }
          $firstchar = $firstchar == '"' ? '\\"' : $firstchar;
          $query = "SELECT ucase( $binary $surnamestr ) AS lastname, $surnamestr AS lowername, ucase($binary lastname) AS binlast, count( ucase($binary lastname) ) AS lncount FROM $people_table WHERE ucase($binary TRIM(lastname)) LIKE \"$firstchar%\" $wherestr GROUP BY lowername ORDER by binlast";
          $result = tng_query($query);
          $topnum = tng_num_rows($result);
          if ($result) {
            $snnum = 1;
            if (!isset($numcols) || $numcols > 5) {
              $numcols = 5;
            }
            $num_in_col = ceil($topnum / $numcols);

            $num_in_col_ctr = 0;
            while ($surname = tng_fetch_assoc($result)) {
              $surname2 = urlencode($surname['lastname']);
              $name = $surname['lastname'] ? "<a href=\"search.php?mylastname=$surname2&amp;lnqualify=equals&amp;mybool=AND$treestr\">{$surname['lowername']}</a>" : uiTextSnippet('nosurname');
              echo "$snnum. $name ({$surname['lncount']})<br>\n";
              $snnum++;
              $num_in_col_ctr++;
              if ($num_in_col_ctr == $num_in_col) {
                echo "</td>\n<td class=\"table-dblgutter\"></td>\n<td class=\"sncol\">";
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
<?php
echo $publicFooterSection->build();
echo scriptsManager::buildScriptElements($flags, 'public');
?>
</body>
</html>