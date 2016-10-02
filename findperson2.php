<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

if ($sessionCharset != 'UTF-8') {
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
$query = "SELECT personID, lastname, firstname, lnprefix, birthdate, altbirthdate, deathdate, burialdate, prefix, suffix, nameorder, living, private, branch FROM people WHERE $allwhere ORDER BY lastname, lnprefix, firstname LIMIT 250";
$result = tng_query($query);

header('Content-type:text/html; charset=' . $sessionCharset);
?>
<div id='findpersonresdiv'>
  <table class='table table-sm'>
    <tr>
      <td>
        <h4><?php echo uiTextSnippet('searchresults'); ?></h4>
        <span>(<?php echo uiTextSnippet('clicktoselect'); ?>)</span><br>
      </td>
      <td></td>
      <td>
        <form action=''>
          <input type='button' value="<?php echo uiTextSnippet('find'); ?>" onclick="reopenFindForm()">
        </form>
      </td>
    </tr>
  </table>
  <br>
  <table class='table table-sm'>
    <?php
    while ($row = tng_fetch_assoc($result)) {
      if ($row['birthdate']) {
        $birthdate = uiTextSnippet('birthabbr') . ' ' . $row['birthdate'];
      } else {
        if ($row['altbirthdate']) {
          $birthdate = uiTextSnippet('chrabbr') . ' ' . $row['altbirthdate'];
        } else {
          $birthdate = '';
        }
      }
      if ($row['deathdate']) {
        $deathdate = uiTextSnippet('deathabbr') . ' ' . $row['deathdate'];
      } else {
        if ($row['burialdate']) {
          $deathdate = uiTextSnippet('burialabbr') . ' ' . $row['burial'];
        } else {
          $deathdate = '';
        }
      }
      if (!$birthdate && $deathdate) {
        $birthdate = uiTextSnippet('nobirthinfo');
      }
      $row['allow_living'] = determineLivingRights($row);
      $name = getName($row);
      if ($type == 'select') {
        $namestr = addslashes($name) . "| - {$row['personID']}<br>$birthdate";
      } elseif ($nameplusid == 1) {
        $namestr = addslashes("$name");
      } elseif ($nameplusid) {
        $namestr = addslashes("$name - {$row['personID']}");
      } else {
        $namestr = addslashes("$name");
      }
      $jsnamestr = str_replace('&#34;', '&quot;', $namestr);
      $jsnamestr = str_replace('"', '&quot;', $namestr);
      echo "<tr>\n";
        echo "<td><span><a href='#' onClick=\"return returnName('{$row['personID']}','$jsnamestr','$type','$nameplusid');\">{$row['personID']}</a></span></td>\n";
        echo "<td><a href='#' onClick=\"return returnName('{$row['personID']}','$jsnamestr','$type','$nameplusid');\">$name</a><br>$birthdate $deathdate</td>\n";
      echo "</tr>\n";
    }
    tng_free_result($result);
    ?>
  </table>
</div>
