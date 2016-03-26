<?php
set_time_limit(0);
require 'tng_begin.php';

$treestr = $tree ? " (" . uiTextSnippet('tree') . ": $tree)" : "";
$logstring = "<a href=\"surnames-all.php?tree=$tree\">" . uiTextSnippet('surnamelist') . ": " . uiTextSnippet('allsurnames') . "$treestr</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('surnamelist') . " - " . uiTextSnippet('allsurnames'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/person.svg'><?php echo uiTextSnippet('surnamelist'); ?></h2>
    <br class='clearleft'>
    <?php
    echo treeDropdown(array('startform' => true, 'endform' => true, 'action' => 'surnames-all', 'method' => 'get', 'name' => 'form1', 'id' => 'form1'));

    if ($tree) {
      $wherestr = "WHERE gedcom = \"$tree\"";
      $wherestr2 = "AND gedcom = \"$tree\"";
    } else {
      $wherestr = "";
      $wherestr2 = "";
    }
    $treestr = $orgtree ? "&amp;tree=$tree" : "";

    $allwhere = getLivingPrivateRestrictions($people_table, false, false);

    if ($allwhere) {
      $wherestr .= $wherestr ? " AND $allwhere" : "WHERE $allwhere";
      $wherestr2 .= " AND $allwhere";
    }
    $linkstr = "";
    $nosurname = urlencode(uiTextSnippet('nosurname'));
    $query = "SELECT ucase(left(lastname,1)) as firstchar, ucase( $binary left(lastname,1) ) as binfirstchar FROM $people_table $wherestr GROUP BY binfirstchar ORDER by binfirstchar";
    $result = tng_query($query);
    if ($result) {
      $initialchar = 1;

      while ($surname = tng_fetch_assoc($result)) {
        if ($initialchar != 1) {
          $linkstr .= " ";
        }
        if ($surname['firstchar'] == "") {
          $surname['firstchar'] = uiTextSnippet('nosurname');
          $linkstr .= "<a href=\"search.php?mylastname=$nosurname&amp;lnqualify=equals&amp;mybool=AND$treestr\">" . uiTextSnippet('nosurname') . "</a> ";
        } else {
          if ($surname['firstchar'] != "_") {
            $linkstr .= "<a href=\"#char$initialchar\">{$surname['firstchar']}</a>";
            $firstchars[$initialchar] = $surname['firstchar'];
            $initialchar++;
          }
        }
      }
      tng_free_result($result);
    }
    ?>

      <div class="titlebox">
        <h4><?php echo uiTextSnippet('surnamesstarting'); ?></h4>
        <p class="firstchars"><?php echo $linkstr; ?></p>
        <br><?php echo "<a href='surnames.php'>" . uiTextSnippet('mainsurnamepage') . "</a>"; ?>
      </div>

      <br>
    <?php
    for ($scount = 1; $scount < $initialchar; $scount++) {
      echo "<a id=\"char$scount\"></a>\n";
      $urlfirstchar = addslashes($firstchars[$scount]);
      ?>
      <div class="titlebox">
        <h2><?php echo $firstchars[$scount]; ?></h2>
        <table class="sntable">
          <tr>
            <td class="sncol">
              <?php
              $surnamestr = $lnprefixes ? "TRIM(CONCAT_WS(' ',lnprefix,lastname) )" : "lastname";
              if ($tngconfig['ucsurnames']) {
                $surnamestr = "ucase($surnamestr)";
              }
              $query = "SELECT ucase( $binary $surnamestr ) as lastname, $surnamestr as lowername, ucase($binary lastname) as binlast, count( ucase($binary lastname) ) as lncount FROM $people_table WHERE ucase($binary TRIM(lastname)) LIKE \"$urlfirstchar%\" $wherestr2 GROUP BY lowername ORDER by binlast";
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
                  $name = $surname['lastname'] ? "<a href=\"search.php?mylastname=$surname2&amp;lnqualify=equals&amp;mybool=AND$treestr\">{$surname['lowername']}</a>" : "<a href=\"search.php?mylastname=$nosurname&amp;lnqualify=equals&amp;mybool=AND$treestr\">" . uiTextSnippet('nosurname') . "</a>";
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
    <?php
    }
    echo $publicFooterSection->build();
    ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>