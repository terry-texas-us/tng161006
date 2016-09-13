<?php
require 'tng_begin.php';

$logstring = "<a href='surnames.php'>" . xmlcharacters(uiTextSnippet('surnamelist')) . '</a>';
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('surnamelist'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <div class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/person.svg'><?php echo uiTextSnippet('surnamelist'); ?></h2>
    <br class='clearleft'>
    <?php
    $linkstr = '';
    $linkstr2col1 = '';
    $linkstr2col2 = '';
    $linkstr3col1 = '';
    $linkstr3col2 = '';
    $collen = 10;
    $cols = 3;
    $nosurname = urlencode(uiTextSnippet('nosurname'));
    $text['top30'] = preg_replace('/xxx/', '30', $text['top30']);

    $wherestr = '';

    $allwhere = getLivingPrivateRestrictions($people_table, false, false);

    if ($allwhere) {
      $wherestr .= $wherestr ? " AND $allwhere" : "WHERE $allwhere";
      $wherestr2 .= " AND $allwhere";
    }

    $query = "SELECT ucase(left(lastname,1)) AS firstchar, ucase( $binary left(lastname,1) ) AS binfirstchar, count( ucase( left( lastname,1) ) ) AS lncount FROM $people_table $wherestr GROUP BY binfirstchar ORDER by binfirstchar";
    $result = tng_query($query);
    if ($result) {
      $initialchar = 1;

      while ($surname = tng_fetch_assoc($result)) {
        if ($initialchar != 1) {
          $linkstr .= ' ';
        }
        if ($session_charset == 'UTF-8' && function_exists(mb_substr)) {
          $firstchar = mb_substr($surname['firstchar'], 0, 1, 'UTF-8');
        } else {
          $firstchar = substr($surname['firstchar'], 0, 1);
        }
        $firstchar = strtoupper($firstchar);
        if ($firstchar == '') {
          $linkstr .= "<a href=\"search.php?mylastname=$nosurname&amp;lnqualify=equals&amp;mybool=AND$treestr\">" . uiTextSnippet('nosurname') . '</a> ';
        } else {
          $urlfirstchar = $firstchar;

          $countstr = uiTextSnippet('surnamesstarting') . ': ' . $firstchar . ' (' . number_format($surname['lncount']) . ' ' . uiTextSnippet('totalnames') . ')';
          $linkstr .= "<a href=\"surnames-oneletter.php?firstchar=$urlfirstchar$treestr\" title=\"$countstr\">{$firstchar}</a>";
        }
        $initialchar++;
      }
      tng_free_result($result);
    }
    $surnamestr = $lnprefixes ? "TRIM(CONCAT_WS(' ',lnprefix,lastname) )" : 'lastname';
    if ($tngconfig['ucsurnames']) {
      $surnamestr = "ucase($surnamestr)";
    }
    $wherestr .= $wherestr ? " AND lastname != \"\"" : "WHERE lastname != \"\"";
    $query = "SELECT ucase( $binary $surnamestr ) AS lastname, $surnamestr AS lowername, count( ucase($binary lastname ) ) AS lncount FROM $people_table $wherestr GROUP BY lowername ORDER by lncount DESC, lastname LIMIT 30";
    $result = tng_query($query);
    $maxcount = 0;
    if ($result) {
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
        $linkstr2col[$col] .= "<td>$count.</td>\n";

        $chartstr = $col ? '' : "<td class=\"bar-holder\"><div style=\"width:{$thiswidth}%;\" class=\"bar rightround\" title=\"{$surname['lowername']} ($tally_fmt)\"><a href=\"search.php?mylastname=$surname2&amp;lnqualify=equals&amp;mybool=AND$treestr\"></a></div></td>";
        
        $linkstr2col[$col] .= "<td><a href=\"search.php?mylastname=$surname2&amp;lnqualify=equals&amp;mybool=AND$treestr\">{$surname['lowername']}</a> ($tally_fmt)</td>$chartstr\n";
        $linkstr2col[$col] .= '</tr>';
        
        $count++;
      }
      tng_free_result($result);
    }
    ?>

    <!--<div class="panel panel-default">-->
      <!--<div class="panel-heading">-->
        <h4><?php echo uiTextSnippet('surnamesstarting'); ?></h4>
      <!--</div>-->
      <p class="firstchars"><?php echo $linkstr; ?></p>
      <?php echo "<a href='surnames-all.php'>" . uiTextSnippet('showallsurnames') . '</a> (' . uiTextSnippet('sortedalpha') . ')'; ?>
    <!--</div>-->

    <br>
    <!--<div class="panel panel-primary">-->
      <!--<div class="panel-heading">-->
        <h4><?php echo uiTextSnippet('top30') . ' (' . uiTextSnippet('totalnames') . '):'; ?></h4>
      <!--</div>-->
      <!--<div class="panel-body">-->
        <div class="row">
          <?php
          for ($i = 0; $i < $cols; $i++) {
            ?>
            <div class="col-lg-4">
              <table class="table-histogram">
                <?php
                echo $linkstr2col[$i];
                ?>
              </table>
            </div>
          <?php } ?>
        </div>
      <!--</div>-->
    <!--</div>-->
    <div class="row">
      <?php
      beginFormElement('surnames100', 'get');
        echo uiTextSnippet('showtop');
        ?>
        <input name='topnum' type='text' value='100' size='4' maxlength='4'/> <?php echo uiTextSnippet('byoccurrence'); ?>
        <input type='submit' value="<?php echo uiTextSnippet('go'); ?>"/>
      <?php endFormElement(); ?>
    </div>
    <?php
    echo $publicFooterSection->build();
    echo scriptsManager::buildScriptElements($flags, 'public');
    ?>
  </div> <!-- container -->
</body>
</html>
