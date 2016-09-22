<div class='row'>
  <div class='col-md-3'>
      <?php
      $wherestr = '';

      $livingPrivateCondition = getLivingPrivateRestrictions('people', false, false);
      if ($livingPrivateCondition) {
        $wherestr .= $wherestr ? ' AND ' . $livingPrivateCondition : "WHERE $livingPrivateCondition";
      }
      $topnum = $topnum ? $topnum : 100;
      $surnamestr = $lnprefixes ? "TRIM(CONCAT_WS(' ',lnprefix,lastname) )" : 'lastname';
      if ($tngconfig['ucsurnames']) {
        $surnamestr = "ucase($surnamestr)";
      }
      $wherestr .= $wherestr ? ' AND lastname != ""' : 'WHERE lastname != ""';
      $query = "SELECT ucase( $binary $surnamestr ) AS lastname, $surnamestr AS lowername, count( ucase($binary lastname) ) AS lncount FROM people $wherestr GROUP BY lowername ORDER by lncount DESC, lastname LIMIT $topnum";

      $result = tng_query($query);
      $topnum = tng_num_rows($result);
      if ($result) {
        $counter = 1;
        if (!isset($numcols) || $numcols > 4) {
          $numcols = 4;
        }
        $num_in_col = ceil($topnum / $numcols);

        $num_in_col_ctr = 0;
        $nosurname = urlencode(uiTextSnippet('nosurname'));
        while ($surname = tng_fetch_assoc($result)) {
          $surname2 = urlencode($surname['lastname']);
          
          if ($surname[lastname]) {
            $name = "<a href=\"search.php?mylastname=$surname2&amp;lnqualify=equals\">{$surname['lowername']}</a>";
          } else {
            $name = "<a href=\"search.php?mylastname=$nosurname&amp;lnqualify=equals\">" . uiTextSnippet('nosurname') . '</a>';
          }
          echo "$counter. $name ({$surname['lncount']})<br>\n";
          $counter++;
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
