<?php
$order = '';
require 'tng_begin.php';

require 'searchlib.php';
require 'prefixes.php';

set_time_limit(0);
$maxsearchresults = $nr ? ($nr < 200 ? $nr : 200) : ($_SESSION['tng_nr'] ? $_SESSION['tng_nr'] : $maxsearchresults);
if (!isset($mybool)) {
  $mybool = 'AND';
}
$_SESSION['tng_search_branch'] = $branch;
$_SESSION['tng_search_lnqualify'] = $lnqualify;
$mylastname = trim(stripslashes($mylastname));
$_SESSION['tng_search_lastname'] = cleanIt($mylastname);

$_SESSION['tng_search_fnqualify'] = $fnqualify;
$myfirstname = trim(stripslashes($myfirstname));
$_SESSION['tng_search_firstname'] = cleanIt($myfirstname);

$_SESSION['tng_search_idqualify'] = $idqualify;
$mypersonid = trim(stripslashes($mypersonid));
$_SESSION['tng_search_personid'] = cleanIt($mypersonid);

$_SESSION['tng_search_bpqualify'] = $bpqualify;
$mybirthplace = trim(stripslashes($mybirthplace));
$_SESSION['tng_search_birthplace'] = cleanIt($mybirthplace);

$_SESSION['tng_search_byqualify'] = $byqualify;
$mybirthyear = trim(stripslashes($mybirthyear));
$_SESSION['tng_search_birthyear'] = cleanIt($mybirthyear);

$_SESSION['tng_search_cpqualify'] = $cpqualify;
$myaltbirthplace = trim(stripslashes($myaltbirthplace));
$_SESSION['tng_search_altbirthplace'] = cleanIt($myaltbirthplace);

$_SESSION['tng_search_cyqualify'] = $cyqualify;
$myaltbirthyear = trim(stripslashes($myaltbirthyear));
$_SESSION['tng_search_altbirthyear'] = cleanIt($myaltbirthyear);

$_SESSION['tng_search_dpqualify'] = $dpqualify;
$mydeathplace = trim(stripslashes($mydeathplace));
$_SESSION['tng_search_deathplace'] = cleanIt($mydeathplace);

$_SESSION['tng_search_dyqualify'] = $dyqualify;
$mydeathyear = trim(stripslashes($mydeathyear));
$_SESSION['tng_search_deathyear'] = cleanIt($mydeathyear);

$_SESSION['tng_search_brpqualify'] = $brpqualify;
$myburialplace = trim(stripslashes($myburialplace));
$_SESSION['tng_search_burialplace'] = cleanIt($myburialplace);

$_SESSION['tng_search_bryqualify'] = $bryqualify;
$myburialyear = trim(stripslashes($myburialyear));
$_SESSION['tng_search_burialyear'] = cleanIt($myburialyear);

$_SESSION['tng_search_bool'] = $mybool;
$_SESSION['tng_search_gender'] = $mygender;

$_SESSION['tng_search_showspouse'] = $showspouse;
$mysplname = trim(stripslashes($mysplname));
$_SESSION['tng_search_mysplname'] = cleanIt($mysplname);

$_SESSION['tng_search_spqualify'] = $spqualify;
$_SESSION['tng_nr'] = $nr;
if ($order) {
  $_SESSION['tng_search_order'] = $order;
} else {
  $order = isset($_SESSION['tng_search_order']) ? $_SESSION['tng_search_order'] : 'name';
}
$_SERVER['QUERY_STRING'] = str_replace(['&amp;', '&'], ['&', '&amp;'], $_SERVER['QUERY_STRING']);
$birthsort = 'birth';
$deathsort = 'death';
$namesort = 'nameup';
$orderloc = strpos($_SERVER['QUERY_STRING'], '&amp;order=');
$currargs = $orderloc > 0 ? substr($_SERVER['QUERY_STRING'], 0, $orderloc) : $_SERVER['QUERY_STRING'];
$mybooltext = $mybool == 'AND' ? uiTextSnippet('cap_and') : uiTextSnippet('cap_or');

$birthOrderHeader = $tngconfig['hidechr'] ? uiTextSnippet('born') : uiTextSnippet('bornchr');
if ($order == 'birth') {
  $orderbyClause = 'IF(p.birthdatetr, p.birthdatetr, p.altbirthdatetr), p.lastname, p.firstname';
  $birthsort = $tngprint ? $birthOrderHeader : "<a href=\"search.php?$currargs&amp;order=birthup\">$birthOrderHeader <img src='img/tng_sort_desc.gif' width='15' height='8' alt=''></a>";
} else {
  $birthsort = $tngprint ? $birthOrderHeader : "<a href=\"search.php?$currargs&amp;order=birth\">$birthOrderHeader <img src='img/tng_sort_asc.gif' width='15' height='8' alt=''></a>";
  if ($order == 'birthup') {
    $orderbyClause = 'IF(p.birthdatetr, p.birthdatetr, p.altbirthdatetr) DESC, p.lastname, p.firstname';
  }
}
$deathOrderHeader = uiTextSnippet('diedburied');
if ($order == 'death') {
  $orderbyClause = 'IF(p.deathdatetr, p.deathdatetr, p.burialdatetr), p.lastname, p.firstname, IF(p.birthdatetr, p.birthdatetr, p.altbirthdatetr)';
  $deathsort = $tngprint ? $deathOrderHeader : "<a href=\"search.php?$currargs&amp;order=deathup\">" . $deathOrderHeader . " <img src='img/tng_sort_desc.gif' width='15' height='8'></a>";
} else {
  $deathsort = $tngprint ? $deathOrderHeader : "<a href=\"search.php?$currargs&amp;order=death\">" . $deathOrderHeader . " <img src='img/tng_sort_asc.gif' width='15' height='8'></a>";
  if ($order == 'deathup') {
    $orderbyClause = 'IF(p.deathdatetr, p.deathdatetr, p.burialdatetr) DESC, p.lastname, p.firstname, IF(p.birthdatetr, p.birthdatetr, p.altbirthdatetr)';
  }
}
$nametitle = uiTextSnippet('name');
if ($order == 'name') {
  $orderbyClause = 'p.lastname, p.firstname, IF(p.birthdatetr, p.birthdatetr, p.altbirthdatetr)';
  $namesort = $tngprint ? $nametitle : "<a href=\"search.php?$currargs&amp;order=nameup\">$nametitle <img src='img/tng_sort_desc.gif' width='15' height='8' alt=''></a>";
} else {
  $namesort = $tngprint ? $nametitle : "<a href=\"search.php?$currargs&amp;order=name\">$nametitle <img src='img/tng_sort_asc.gif' width='15' height='8' alt=''></a>";
  if ($order == 'nameup') {
    $orderbyClause = 'p.lastname DESC, p.firstname DESC, IF(p.birthdatetr, p.birthdatetr, p.altbirthdatetr)';
  }
}

function buildCriteria($column, $colvar, $qualifyvar, $qualifier, $value, $textstr) {
  global $lnprefixes;
  global $criteria_limit;
  global $criteria_count;

  if ($qualifier == 'exists' || $qualifier == 'dnexist') {
    $value = $usevalue = '';
  } else {
    $value = urldecode(trim($value));
    $usevalue = addslashes($value);
  }
  if ($column == 'p.lastname' && $lnprefixes) {
    $column = "TRIM(CONCAT_WS(' ',p.lnprefix,p.lastname))";
  } elseif ($column == 'spouse.lastname') {
    $column = "TRIM(CONCAT_WS(' ',spouse.lnprefix,spouse.lastname))";
  }
  $criteria_count++;
  if ($criteria_count >= $criteria_limit) {
    die('sorry');
  }
  $criteria = '';
  $returnarray = buildColumn($qualifier, $column, $usevalue);
  $criteria .= $returnarray['criteria'];
  $qualifystr = $returnarray['qualifystr'];

  addtoQuery($textstr, $colvar, $criteria, $qualifyvar, $qualifier, $qualifystr, $value);
}

$querystring = '';
$allwhere = '';

if ($mylastname || $lnqualify == 'exists' || $lnqualify == 'dnexist') {
  if ($mylastname == uiTextSnippet('nosurname')) {
    addtoQuery(uiTextSnippet('lastname'), 'mylastname', 'lastname = ""', 'lnqualify', uiTextSnippet('equals'), uiTextSnippet('equals'), $mylastname);
  } else {
    buildCriteria('p.lastname', 'mylastname', 'lnqualify', $lnqualify, $mylastname, uiTextSnippet('lastname'));
  }
}
if ($myfirstname || $fnqualify == 'exists' || $fnqualify == 'dnexist') {
  buildCriteria('p.firstname', 'myfirstname', 'fnqualify', $fnqualify, $myfirstname, uiTextSnippet('firstname'));
}
if ($mysplname || $spqualify == 'exists' || $spqualify == 'dnexist') {
  buildCriteria('spouse.lastname', 'mysplname', 'spqualify', $spqualify, $mysplname, uiTextSnippet('spousesurname'));
}
if ($mypersonid) {
  $mypersonid = strtoupper($mypersonid);
  if ($idqualify == 'equals' && is_numeric($mypersonid)) {
    $mypersonid = $personprefix . $mypersonid . $personsuffix;
  }
  buildCriteria('p.personID', 'mypersonid', 'idqualify', $idqualify, $mypersonid, uiTextSnippet('personid'));
}
if ($mytitle || $tqualify == 'exists' || $tqualify == 'dnexist') {
  buildCriteria('p.title', 'mytitle', 'tqualify', $tqualify, $mytitle, uiTextSnippet('title'));
}
if ($myprefix || $pfqualify == 'exists' || $pfqualify == 'dnexist') {
  buildCriteria('p.prefix', 'myprefix', 'pfqualify', $pfqualify, $myprefix, uiTextSnippet('prefix'));
}
if ($mysuffix || $sfqualify == 'exists' || $sfqualify == 'dnexist') {
  buildCriteria('p.suffix', 'mysuffix', 'sfqualify', $sfqualify, $mysuffix, uiTextSnippet('suffix'));
}
if ($mynickname || $nnqualify == 'exists' || $nnqualify == 'dnexist') {
  buildCriteria('p.nickname', 'mynickname', 'nnqualify', $nnqualify, $mynickname, uiTextSnippet('nickname'));
}
if ($mybirthplace || $bpqualify == 'exists' || $bpqualify == 'dnexist') {
  buildCriteria('p.birthplace', 'mybirthplace', 'bpqualify', $bpqualify, $mybirthplace, uiTextSnippet('birthplace'));
}
if ($mybirthyear || $byqualify == 'exists' || $byqualify == 'dnexist') {
  buildYearCriteria('p.birthdatetr', 'mybirthyear', 'byqualify', 'p.altbirthdatetr', $byqualify, $mybirthyear, uiTextSnippet('birthdatetr'));
}
if ($myaltbirthplace || $cpqualify == 'exists' || $cpqualify == 'dnexist') {
  buildCriteria('p.altbirthplace', 'myaltbirthplace', 'cpqualify', $cpqualify, $myaltbirthplace, uiTextSnippet('altbirthplace'));
}
if ($myaltbirthyear || $cyqualify == 'exists' || $cyqualify == 'dnexist') {
  buildYearCriteria('p.altbirthdatetr', 'myaltbirthyear', 'cyqualify', '', $cyqualify, $myaltbirthyear, uiTextSnippet('altbirthdatetr'));
}
if ($mydeathplace || $dpqualify == 'exists' || $dpqualify == 'dnexist') {
  buildCriteria('p.deathplace', 'mydeathplace', 'dpqualify', $dpqualify, $mydeathplace, uiTextSnippet('deathplace'));
}
if ($mydeathyear || $dyqualify == 'exists' || $dyqualify == 'dnexist') {
  buildYearCriteria('p.deathdatetr', 'mydeathyear', 'dyqualify', 'p.burialdatetr', $dyqualify, $mydeathyear, uiTextSnippet('deathdatetr'));
}
if ($myburialplace || $brpqualify == 'exists' || $brpqualify == 'dnexist') {
  buildCriteria('p.burialplace', 'myburialplace', 'brpqualify', $brpqualify, $myburialplace, uiTextSnippet('burialplace'));
}
if ($myburialyear || $bryqualify == 'exists' || $bryqualify == 'dnexist') {
  buildYearCriteria('p.burialdatetr', 'myburialyear', 'bryqualify', '', $bryqualify, $myburialyear, uiTextSnippet('burialdatetr'));
}
if ($mygender) {
  if ($mygender == 'N') {
    $mygender = '';
  }
  buildCriteria('p.sex', 'mygender', 'gequalify', $gequalify, $mygender, uiTextSnippet('gender'));
}
$dontdo = ['ADDR', 'BIRT', 'CHR', 'DEAT', 'BURI', 'NICK', 'TITL', 'NSFX'];
$cejoin = doCustomEvents('I');

$gotInput = $mytitle || $myprefix || $mysuffix || $mynickname || $mybirthplace || $mydeathplace || $mybirthyear || $mydeathyear || $ecount;
$livingPrivateCondition = getLivingPrivateRestrictions('p', $myfirstname, $gotInput);

if ($livingPrivateCondition) {
  if ($allwhere) {
    $allwhere = "($allwhere) AND ";
  }
  $allwhere .= $livingPrivateCondition;
}
if ($allwhere) {
  $allwhere = 'WHERE ' . $allwhere;
  $querystring = uiTextSnippet('text_for') . " $querystring";
}
if ($orderbyClause) {
  $orderbyClause = "ORDER BY $orderbyClause";
}
$max_browsesearch_pages = 5;

$limitClause = 'LIMIT ';
if ($offset) {
  $offsetplus = $offset + 1;
  $limitClause .= "$offset, ";
} else {
  $offsetplus = 1;
  $page = 1;
}
$limitClause .= $maxsearchresults;

if (($mysplname && $mygender) || $spqualify == 'exists' || $spqualify == 'dnexist') {
  $spouseCondition = $mygender == 'F' ? 'p.personID = wife AND spouse.personID = husband' : 'p.personID = husband AND spouse.personID = wife';
  $query = 'SELECT p.ID, spouse.personID AS spersonID, p.personID, p.lastname, p.lnprefix, p.firstname, p.living, p.private, p.branch, p.nickname, p.suffix, p.prefix, p.nameorder, p.title, p.birthplace, p.birthdate, p.deathplace, p.deathdate, p.altbirthdate, p.altbirthplace, p.burialdate, p.burialplace '
      . "FROM ($people_table AS p, $families_table, $people_table AS spouse) $cejoin $allwhere AND $spouseCondition $orderbyClause $limitClause";
  $showspouse = 'yess';
  $query2 = "SELECT count(p.ID) AS pcount FROM ($people_table AS p, $families_table, $people_table AS spouse) $cejoin $allwhere AND $spouseCondition";
} else {
  if ($showspouse == 'yes') {
    $families_join = "LEFT JOIN $families_table AS families1 ON (p.personID = families1.husband) LEFT JOIN $families_table AS families2 ON (p.personID = families2.wife) ";
    $huswife = ', families1.wife as wife, families2.husband as husband';
  } else {
    $families_join = '';
    $huswife = '';
  }

  $query = "SELECT p.ID, p.personID, lastname, lnprefix, firstname, p.living, p.private, p.branch, nickname, prefix, suffix, nameorder, title, birthplace, birthdate, deathplace, deathdate, altbirthdate, altbirthplace, burialdate, burialplace $huswife FROM $people_table AS p $families_join $cejoin $allwhere $orderbyClause $limitClause";
  $query2 = "SELECT count(p.ID) AS pcount FROM $people_table AS p $families_join $cejoin $allwhere";
}
$result = tng_query($query);
$numrows = tng_num_rows($result);

if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $result2 = tng_query($query2) or die(uiTextSnippet('cannotexecutequery') . ": $query2");
  $countrow = tng_fetch_assoc($result2);
  $totrows = $countrow['pcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}
if (!$numrows) {
  $msg = uiTextSnippet('noresults') . " $querystring. " . uiTextSnippet('tryagain') . '.';
  header('Location: searchform.php?msg=' . urlencode($msg));
  exit;
} elseif ($numrows == 1) {
  $row = tng_fetch_assoc($result);
  tng_free_result($result);
  header('Location: peopleShowPerson.php?personID=' . $row['personID']);
  exit;
}
scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('searchresults'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/magnifying-glass.svg'><?php echo uiTextSnippet('searchresults'); ?></h2>
    <br>
    <?php
    $logstring = '<a href="search.php?' . $_SERVER['QUERY_STRING'] . '">' . xmlcharacters(uiTextSnippet('searchresults') . " $querystring") . '</a>';
    writelog($logstring);
    preparebookmark($logstring);

    $numrowsplus = $numrows + $offset;

    echo '<p>' . uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . ' ' . number_format($totrows) . " $querystring</p>";
    ?>
    <table class="table table-sm table-striped">
      <tr>
        <th></th>
        <th><?php echo $namesort; ?></th>
        <?php if ($myprefix) { ?>
          <th><?php echo uiTextSnippet('prefix'); ?></th>
        <?php } ?>
        <?php if ($mysuffix) { ?>
          <th><?php echo uiTextSnippet('suffix'); ?></th>
        <?php } ?>
        <?php if ($mytitle) { ?>
          <th><?php echo uiTextSnippet('title'); ?></th>
        <?php } ?>
        <?php if ($mynickname) { ?>
          <th><?php echo uiTextSnippet('nickname'); ?></th>
        <?php } ?>
        <th colspan='2'><?php echo $birthsort; ?></th>
        <th colspan='2'><?php echo $deathsort; ?></th>
        <?php if ($showspouse) { ?>
          <th><?php echo uiTextSnippet('spouse'); ?></th>
        <?php } ?>
      </tr>
      <?php
      $i = $offsetplus;
      
      while ($row = tng_fetch_assoc($result)) {
        $rights = determineLivingPrivateRights($row);
        $row['allow_living'] = $rights['living'];
        $row['allow_private'] = $rights['private'];
        if ($rights['both']) {
          if ($row['birthdate'] || ($row['birthplace'] && !$row['altbirthdate'])) {
            $birthdate = uiTextSnippet('birthabbr', ['html' => 'strong']) . ' ' . displayDate($row['birthdate']);
            $birthplace = $row['birthplace'] ? buildSilentPlaceLink($row['birthplace']) : '';
          } else {
            if ($row['altbirthdate'] || $row['altbirthplace']) {
              $birthdate = uiTextSnippet('chrabbr', ['html' => 'strong']) . ' ' . displayDate($row['altbirthdate']);
              $birthplace = $row['altbirthplace'] ? buildSilentPlaceLink($row['altbirthplace']) : '';
            } else {
              $birthdate = '';
              $birthplace = '';
            }
          }
          if ($row['deathdate'] || ($row['deathplace'] && !$row['burialdate'])) {
            $deathdate = uiTextSnippet('deathabbr', ['html' => 'strong']) . ' ' . displayDate($row['deathdate']);
            $deathplace = $row['deathplace'] ? buildSilentPlaceLink($row['deathplace']) : '';
          } else {
            if ($row['burialdate'] || $row['burialplace']) {
              $deathdate = uiTextSnippet('burialabbr', ['html' => 'strong']) . ' ' . displayDate($row['burialdate']);
              $deathplace = $row['burialplace'] ? $row['burialplace'] . ' ' . buildSilentPlaceLink($row['burialplace']) : '';
            } else {
              $deathdate = '';
              $deathplace = '';
            }
          }
          $prefix = $row['prefix'];
          $suffix = $row['suffix'];
          $title = $row['title'];
          $nickname = $row['nickname'];
        } else {
          $prefix = $suffix = $title = $nickname = $birthdate = $birthplace = $deathdate = $deathplace = '';
        }
        $personID = $row['personID'];
        echo '<tr>';
        $name = getNameRev($row);
        echo "<td>$i</td>\n";
        $i++;
        echo "<td>\n";
        echo "<a tabindex='0' class='btn btn-sm btn-outline-primary person-popover' role='button' data-toggle='popover' data-placement='bottom' data-person-id='$personID'>$name</a>\n";
        echo '</td>';

        if ($showspouse) {
          $spouse = '';
          if ($showspouse == 'yess') {
            $spouseID = $row['spersonID'];
          } else {
            $spouseID = $row['husband'] ? $row['husband'] : $row['wife'];
          }
          if ($spouseID) {
            $query = "SELECT lastname, lnprefix, firstname, prefix, suffix, nameorder, living, private, branch FROM $people_table WHERE personID = '$spouseID'";
            $spresult = tng_query($query);
            if ($spresult) {
              $sprow = tng_fetch_assoc($spresult);
              $sprights = determineLivingPrivateRights($sprow);
              $sprow['allow_living'] = $sprights['living'];
              $sprow['allow_private'] = $sprights['private'];
              $spouse = getName($sprow);
              tng_free_result($spresult);
            }
          }
          $spousestr = $spouse ? "<a href=\"peopleShowPerson.php?personID=$spouseID\">$spouse</a>" : '';
        } else {
          $spousestr = '';
        }
        if ($myprefix) {
          echo "<td>$prefix &nbsp;</td>";
        }
        if ($mysuffix) {
          echo "<td>$suffix &nbsp;</td>";
        }
        if ($mytitle) {
          echo "<td>$title &nbsp;</td>";
        }
        if ($mynickname) {
          echo "<td>$nickname &nbsp;</td>";
        }
        echo "<td colspan='2'>$birthdate<br>$birthplace</td>";
        echo "<td colspan='2'>$deathdate<br>$deathplace</td>";
        if ($showspouse) {
          echo "<td>$spousestr</td>";
        }
        echo "</tr>\n";
      }
      tng_free_result($result);
      ?>
    </table>
    <?php
    echo buildSearchResultPagination($totrows, "search.php?$urlstring&amp;mybool=$mybool&amp;nr=$maxsearchresults&amp;showspouse=$showspouse&amp;offset", $maxsearchresults, $max_browsesearch_pages);
    echo $publicFooterSection->build();
    ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script src="js/search.js"></script>
  <script>
   $(function () {
        $('[data-toggle="popover"]').popover();
    });
  </script>
</body>
</html>