<?php
require 'tng_begin.php';

require 'functions.php';

function doBranchSearch($instance, $pagenav) {
  global $branchsearch;

  $str = "<span>\n";
  $str .= buildFormElement("branchesShow", "GET", "BranchSearch$instance");
  $str .= "<input name='branchsearch' type='text' value=\"$branchsearch\"> \n";
  $str .= "<input type='submit' value=\"" . uiTextSnippet('search') . "\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  $str .= $pagenav;
  if ($branchsearch) {
    $str .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='branchesShow.php'>" . uiTextSnippet('browsealltrees') . "</a>";
  }
  $str .= "</form></span>\n";

  return $str;
}

$max_browsebranch_pages = 5;
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $page = 1;
}

if ($branchsearch) {
  $wherestr = " AND (branch LIKE \"%$branchsearch%\" OR b.description LIKE \"%$branchsearch%\")";
} else {
  $wherestr = "";
}

$query = "SELECT b.branch, b.gedcom, b.description, treename, personID
    FROM ($branches_table as b, $trees_table as t)
    WHERE b.gedcom = t.gedcom $wherestr
    ORDER BY b.description LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows($result);

if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $query = "SELECT count(branch) as branchcount FROM $branches_table";
  $result2 = tng_query($query);
  $countrow = tng_fetch_assoc($result2);
  $totrows = $countrow['branchcount'];
} else {
  $totrows = $numrows;
}

$numrowsplus = $numrows + $offset;

$treequery = "SELECT count(gedcom) as treecount FROM $trees_table";
$treeresult = tng_query($treequery);
$treerow = tng_fetch_assoc($treeresult);
$numtrees = $treerow['treecount'];
tng_free_result($treeresult);

$logstring = "<a href=\"branchesShow.php?tree=$tree&amp;offset=$offset&amp;branchsearch=$branchsearch\">" . xmlcharacters(uiTextSnippet('branches')) . "</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('branches'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/flow-branch.svg'><?php echo uiTextSnippet('branches'); ?></h2>
    <br clear='left'>
    <?php
    if ($totrows) {
      echo "<p><span>" . uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows</span></p>";
    }
    $pagenav = buildSearchResultPagination($totrows, "branchesShow.php?branchsearch=$branchsearch&amp;offset", $maxsearchresults, $max_browsebranch_pages);
    if ($pagenav || $branchsearch) {
      echo doBranchSearch(1, $pagenav);
    }
    ?>
      <table class='table table-sm'>
        <thead>
          <tr>
            <th></th>
            <th><?php echo uiTextSnippet('description'); ?></th>
            <?php if ($numtrees > 1) { ?>
              <th><?php echo uiTextSnippet('treename'); ?></th>
            <?php } ?>
            <th><?php echo uiTextSnippet('startingind'); ?></th>
            <th><?php echo uiTextSnippet('individuals'); ?></th>
            <th><?php echo uiTextSnippet('families'); ?></th>
          </tr>
        </thead>
        <?php
        $i = $offsetplus;
        $peoplewhere = getLivingPrivateRestrictions($people_table, false, false);
        if ($peoplewhere) {
          $peoplewhere = "AND " . $peoplewhere;
        }
        $familywhere = getLivingPrivateRestrictions($families_table, false, false);
        if ($familywhere) {
          $familywhere = "AND " . $familywhere;
        }

        while ($row = tng_fetch_assoc($result)) {
          $query = "SELECT count(familyID) as fcount FROM $families_table WHERE branch LIKE \"%{$row['branch']}%\" $familywhere";
          $famresult = tng_query($query);
          $famrow = tng_fetch_assoc($famresult);
          tng_free_result($famresult);

          $query = "SELECT count(personID) as pcount FROM $people_table WHERE branch LIKE \"%{$row['branch']}%\" $peoplewhere";
          $indresult = tng_query($query);
          $indrow = tng_fetch_assoc($indresult);
          tng_free_result($indresult);

          $presult = getPersonSimple($row['gedcom'], $row['personID']);
          $prow = tng_fetch_assoc($presult);
          tng_free_result($presult);
          $prights = determineLivingPrivateRights($prow);
          $prow['allow_living'] = $prights['living'];
          $prow['allow_private'] = $prights['private'];
          $namestr = getName($prow);

          echo "<tr>\n";
          echo "<td>$i</td>\n";
          echo "<td>{$row['description']}</td>\n";
          if ($numtrees > 1) {
            echo "<td><a href=\"showtree.php?tree={$row['gedcom']}\">{$row['treename']}</a></td>\n";
          }
          echo "<td><a href=\"peopleShowPerson.php?personID={$row['personID']}&amp;tree={$row['gedcom']}\">$namestr</a></td>\n";
          echo "<td align='right'><a href=\"search.php?tree={$row['gedcom']}&amp;branch={$row['branch']}\">" . number_format($indrow['pcount']) . "</a></td>\n";
          echo "<td align='right'><a href=\"famsearch.php?tree={$row['gedcom']}&amp;branch={$row['branch']}\">" . number_format($famrow['fcount']) . "</a></td>\n";
          echo "</tr>\n";
          $i++;
        }
        tng_free_result($result);
        ?>
      </table>

    <?php
    if ($pagenav || $treesearch) {
      echo doBranchSearch(2, $pagenav);
    }
    echo $publicFooterSection->build();
    ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
