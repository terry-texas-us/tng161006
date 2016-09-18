<?php
require 'begin.php';
require 'adminlib.php';
require 'getlang.php';

require 'checklogin.php';

if ($session_charset != 'UTF-8') {
  $myfirstname = tng_utf8_decode($myfirstname);
  $mylastname = tng_utf8_decode($mylastname);
}
$allwhere = '1';
if ($personID) {
  $allwhere .= " AND personID = \"$personID\"";
}
if ($myfirstname) {
  $allwhere .= ' AND firstname LIKE "%' . trim($myfirstname) . '%"';
}
if ($mylastname) {
  if ($lnprefixes) {
    $allwhere .= " AND CONCAT_WS(' ',lnprefix,lastname) LIKE \"%" . trim($mylastname) . '%"';
  } else {
    $allwhere .= ' AND lastname LIKE "%' . trim($mylastname) . '%"';
  }
}
if ($livedefault < 2 && (!$allow_living_db) && $nonames == 1) {
  $allwhere .= ' AND ';
  if ($allow_living_db) {
    if ($assignedbranch) {
      $allwhere .= "(living != 1 OR branch LIKE \"%$assignedbranch%\")";
    } else {
      $allwhere .= 'living != 1';
    }
  } else {
    $allwhere .= 'living != 1 AND private != 1';
  }
}
$query = "SELECT personID, lastname, firstname, lnprefix, birthdate, altbirthdate, deathdate, burialdate, prefix, suffix, nameorder, living, private, branch FROM people WHERE $allwhere ORDER BY lastname, lnprefix, firstname LIMIT 250";
$result = tng_query($query);

header('Content-type:text/html; charset=' . $session_charset);
?>
<div id='findpersonresdiv'>
  <table class='table table-sm'>
    <tr>
      <td>
        <h4><?php echo uiTextSnippet('searchresults'); ?></h4>
        <span>(<?php echo uiTextSnippet('clicktoselect'); ?>)</span>
      </td>
      <td></td>
      <td>
        <form action=''>
          <input type='button' value="<?php echo uiTextSnippet('find'); ?>" onclick="reopenFindForm()">
        </form>
      </td>
    </tr>
  </table>
  <table class='table table-sm'>
    <?php
    while ($row = tng_fetch_assoc($result)) {
      $birthdate = $deathdate = '';
      $row['allow_living'] = determineLivingRights($row);

      if ($row['allow_living']) {
        if ($row['birthdate']) {
          $birthdate = uiTextSnippet('birthabbr') . " {$row['birthdate']}";
        } else {
          if ($row['altbirthdate']) {
            $birthdate = uiTextSnippet('chrabbr') . " {$row['altbirthdate']}";
          }
        }
        if ($row['deathdate']) {
          $deathdate = uiTextSnippet('deathabbr') . " {$row['deathdate']}";
        } else {
          if ($row['burialdate']) {
            $deathdate = uiTextSnippet('burialabbr') . " {$row['burial']}";
          }
        }
        if (!$birthdate && $deathdate) {
          $birthdate = uiTextSnippet('nobirthinfo');
        }
      }
      $name = getName($row);
      if ($fieldtype == 'select') {
        $namestr = addslashes($name) . "| - {$row['personID']}<br>$birthdate";
      } elseif ($textchange) {
        $birthdatestr = displayDate($birthdate);
        $namestr = addslashes(preg_replace('/\"/', '&#34;', getName($row) . ($birthdatestr ? ' (' . displayDate($birthdate) . ')' : '') . " - $row[personID]"));
        $nameplusid = $textchange;
      } elseif ($nameplusid == 1) {
        $namestr = addslashes("$name");
      } elseif ($nameplusid) {
        $namestr = addslashes("$name - {$row['personID']}");
      } else {
        $namestr = addslashes("$name");
      }
      $jsnamestr = str_replace('&#34;', '&lsquo;', $namestr);
      $jsnamestr = str_replace("\\\"", '&lsquo;', $namestr);
      echo "<tr>\n";
        echo "<td><span><a href='#' onClick=\"return returnName('{$row['personID']}','$jsnamestr','$fieldtype','$nameplusid');\">{$row['personID']}</a></span></td>\n";
        echo "<td><a href='#' onClick=\"return returnName('{$row['personID']}','$jsnamestr','$fieldtype','$nameplusid');\">$name</a><br>$birthdate $deathdate</td>\n";
      echo "</tr>\n";
    }
    tng_free_result($result);
    ?>
  </table>
</div>