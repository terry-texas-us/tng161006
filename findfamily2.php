<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

if ($session_charset != 'UTF-8') {
  $myhusbname = tng_utf8_decode($myhusbname);
  $mywifename = tng_utf8_decode($mywifename);
}

$allwhere = '1';
$joinon = '';
if ($assignedbranch) {
  $allwhere .= " AND $families_table.branch LIKE \"%$assignedbranch%\"";
}

$allwhere2 = '';

if ($mywifename) {
  $terms = explode(' ', $mywifename);
  foreach ($terms as $term) {
    if ($allwhere2) {
      $allwhere2 .= ' AND ';
    }
    $allwhere2 .= "CONCAT_WS(' ',wifepeople.firstname,TRIM(CONCAT_WS(' ',wifepeople.lnprefix,wifepeople.lastname))) LIKE \"%$term%\"";
  }
}

if ($myhusbname) {
  $terms = explode(' ', $myhusbname);
  foreach ($terms as $term) {
    if ($allwhere2) {
      $allwhere2 .= ' AND ';
    }
    $allwhere2 .= "CONCAT_WS(' ',husbpeople.firstname,TRIM(CONCAT_WS(' ',husbpeople.lnprefix,husbpeople.lastname))) LIKE \"%$term%\"";
  }
} else {
  $joinonhusb = '';
}

if ($allwhere2) {
  $allwhere2 = "AND $allwhere2";
}

$joinonwife = "LEFT JOIN $people_table AS wifepeople ON $families_table.wife = wifepeople.personID";
$joinonhusb = "LEFT JOIN $people_table AS husbpeople ON $families_table.husband = husbpeople.personID";
$query = "SELECT familyID, wifepeople.personID AS wpersonID, wifepeople.firstname AS wfirstname, wifepeople.lnprefix AS wlnprefix, wifepeople.lastname AS wlastname, wifepeople.suffix AS wsuffix, wifepeople.nameorder AS wnameorder, wifepeople.living AS wliving, wifepeople.private AS wprivate, wifepeople.branch AS wbranch, husbpeople.personID AS hpersonID, husbpeople.firstname AS hfirstname, husbpeople.lnprefix AS hlnprefix, husbpeople.lastname AS hlastname, husbpeople.suffix AS hsuffix, husbpeople.nameorder AS hnameorder, husbpeople.living AS hliving, husbpeople.private AS hprivate, husbpeople.branch AS hbranch FROM $families_table $joinonwife $joinonhusb WHERE $allwhere $allwhere2 ORDER BY hlastname, hlnprefix, hfirstname LIMIT 250";
$result = tng_query($query);

header('Content-type:text/html; charset=' . $session_charset);
?>

<div id='findfamilyresdiv'>
  <table class='table table-sm'>
    <tr>
      <td>
        <span><?php echo uiTextSnippet('searchresults'); ?></span><br>
        <span>(<?php echo uiTextSnippet('clicktoselect'); ?>)</span><br>
      </td>
      <td></td>
      <td>
        <form action=''>
          <input type='button' value="<?php echo uiTextSnippet('find'); ?>" onClick="reopenFindForm();">
        </form>
      </td>
    </tr>
  </table>
  <br>
  <table class='table table-sm'>
    <?php
    while ($row = tng_fetch_assoc($result)) {
      $thisfamily = '';
      if ($row['hpersonID']) {
        $person['firstname'] = $row['hfirstname'];
        $person['lnprefix'] = $row['hlnprefix'];
        $person['lastname'] = $row['hlastname'];
        $person['suffix'] = $row['hsuffix'];
        $person['nameorder'] = $row['hnameorder'];
        $person['living'] = $row['hliving'];
        $person['private'] = $row['hprivate'];
        $person['branch'] = $row['hbranch'];
        $person['allow_living'] = determineLivingRights($person);
        $thisfamily .= getName($person);
      }
      if ($row['wpersonID']) {
        if ($thisfamily) {
          $thisfamily .= '<br>';
        }
        $person['firstname'] = $row['wfirstname'];
        $person['lnprefix'] = $row['wlnprefix'];
        $person['lastname'] = $row['wlastname'];
        $person['suffix'] = $row['wsuffix'];
        $person['nameorder'] = $row['wnameorder'];
        $person['living'] = $row['wliving'];
        $person['private'] = $row['wprivate'];
        $person['branch'] = $row['wbranch'];
        $person['allow_living'] = determineLivingRights($person);
        $thisfamily .= getName($person);
      }
      echo "<tr><td><span><a href='#' onClick=\"return returnName('{$row['familyID']}','','text','{$row['familyID']}');\">{$row['familyID']}</a></span></td><td><a href='#' onclick=\"return returnName('{$row['familyID']}','','text','{$row['familyID']}');\">$thisfamily</a></td></tr>\n";
    }
    tng_free_result($result);
    ?>
  </table>
</div>
