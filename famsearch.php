<?php
$order = "";
include("tng_begin.php");

include("searchlib.php");

set_time_limit(0);
$maxsearchresults = $nr ? $nr : ($_SESSION['tng_nr'] ? $_SESSION['tng_nr'] : $maxsearchresults);

$_SESSION['tng_search_ftree'] = $tree;
$_SESSION['tng_search_branch'] = $branch;
$_SESSION['tng_search_flnqualify'] = $flnqualify;
$myflastname = trim(stripslashes($myflastname));
$_SESSION['tng_search_flastname'] = cleanIt($myflastname);

$_SESSION['tng_search_ffnqualify'] = $ffnqualify;
$myffirstname = trim(stripslashes($myffirstname));
$_SESSION['tng_search_ffirstname'] = cleanIt($myffirstname);

$_SESSION['tng_search_mlnqualify'] = $mlnqualify;
$mymlastname = trim(stripslashes($mymlastname));
$_SESSION['tng_search_mlastname'] = cleanIt($mymlastname);

$_SESSION['tng_search_mfnqualify'] = $mfnqualify;
$mymfirstname = trim(stripslashes($mymfirstname));
$_SESSION['tng_search_mfirstname'] = cleanIt($mymfirstname);

$_SESSION['tng_search_fidqualify'] = $fidqualify;
$myfamilynid = trim(stripslashes($myfamilyid));
$_SESSION['tng_search_familyid'] = cleanIt($myfamilynid);

$_SESSION['tng_search_mpqualify'] = $mpqualify;
$mymarrplace = trim(stripslashes($mymarrplace));
$_SESSION['tng_search_marrplace'] = cleanIt($mymarrplace);

$_SESSION['tng_search_myqualify'] = $myqualify;
$mymarryear = trim(stripslashes($mymarryear));
$_SESSION['tng_search_marryear'] = cleanIt($mymarryear);

$_SESSION['tng_search_dvpqualify'] = $dvpqualify;
$mydivplace = trim(stripslashes($mydivplace));
$_SESSION['tng_search_divplace'] = cleanIt($mydivplace);

$_SESSION['tng_search_dvyqualify'] = $dvyqualify;
$mydivyear = trim(stripslashes($mydivyear));
$_SESSION['tng_search_divyear'] = cleanIt($mydivyear);

$_SESSION['tng_search_fbool'] = $mybool;
$_SESSION['tng_nr'] = $nr;
if ($order) {
  $_SESSION['tng_search_forder'] = $order;
} else {
  $order = isset($_SESSION['tng_search_forder']) ? $_SESSION['tng_search_forder'] : "fname";
}

$marrsort = "marr";
$divsort = "div";
$fnamesort = "fnameup";
$mnamesort = "mnameup";
$orderloc = strpos($_SERVER['QUERY_STRING'], "&order=");
$currargs = $orderloc > 0 ? substr($_SERVER['QUERY_STRING'], 0, $orderloc) : $_SERVER['QUERY_STRING'];
$mybooltext = $mybool == "AND" ? uiTextSnippet('cap_and') : uiTextSnippet('cap_or');

if ($order == "marr") {
  $orderstr = "marrdatetr, marrplace, father.lastname, father.firstname";
  $marrsort = "<a href=\"search.php?$currargs&order=marrup\">" . uiTextSnippet('married') . " <img src=\"img/tng_sort_desc.gif\" width=\"15\" height=\"8\"></a>";
} else {
  $marrsort = "<a href=\"search.php?$currargs&order=marr\">" . uiTextSnippet('married') . " <img src=\"img/tng_sort_asc.gif\" width=\"15\" height=\"8\"></a>";
  if ($order == "marrup") {
    $orderstr = "marrdatetr DESC, marrplace DESC, father.lastname, father.firstname";
  }
}

if ($order == "div") {
  $orderstr = "divdatetr, divplace, father.lastname, father.firstname, marrdatetr";
  $divsort = "<a href=\"search.php?$currargs&order=divup\">" . uiTextSnippet('divorced') . " <img src=\"img/tng_sort_desc.gif\" width=\"15\" height=\"8\"></a>";
} else {
  $divsort = "<a href=\"search.php?$currargs&order=div\">" . uiTextSnippet('divorced') . " <img src=\"img/tng_sort_asc.gif\" width=\"15\" height=\"8\"></a>";
  if ($order == "divup") {
    $orderstr = "divdatetr DESC, divplace DESC, father.lastname, father.firstname, marrdatetr";
  }
}

if ($order == "fname") {
  $orderstr = "father.lastname, father.firstname, marrdatetr";
  $fnamesort = "<a href=\"search.php?$currargs&order=fnameup\">" . uiTextSnippet('fathername') . " <img src=\"img/tng_sort_desc.gif\" width=\"15\" height=\"8\"></a>";
} else {
  $fnamesort = "<a href=\"search.php?$currargs&order=fname\">" . uiTextSnippet('fathername') . " <img src=\"img/tng_sort_asc.gif\" width=\"15\" height=\"8\"></a>";
  if ($order == "fnameup") {
    $orderstr = "father.lastname DESC, father.firstname DESC, marrdatetr";
  }
}

if ($order == "mname") {
  $orderstr = "mother.lastname, mother.firstname, marrdatetr";
  $mnamesort = "<a href=\"search.php?$currargs&order=mnameup\">" . uiTextSnippet('mothername') . " <img src=\"img/tng_sort_desc.gif\" width=\"15\" height=\"8\"></a>";
} else {
  $mnamesort = "<a href=\"search.php?$currargs&order=mname\">" . uiTextSnippet('mothername') . " <img src=\"img/tng_sort_asc.gif\" width=\"15\" height=\"8\"></a>";
  if ($order == "mnameup") {
    $orderstr = "mother.lastname DESC, mother.firstname DESC, marrdatetr";
  }
}

function buildCriteria($column, $colvar, $qualifyvar, $qualifier, $value, $textstr) {
  global $lnprefixes, $criteria_limit, $criteria_count;

  if ($qualifier == 'exists' || $qualifier == "dnexist") {
    $value = $usevalue = "";
  } else {
    $value = urldecode(trim($value));
    $usevalue = addslashes($value);
  }

  if ($column == "father.lastname" && $lnprefixes) {
    $column = "TRIM(CONCAT_WS(' ',father.lnprefix,father.lastname))";
  } elseif ($column == "mother.lastname") {
    $column = "TRIM(CONCAT_WS(' ',mother.lnprefix,mother.lastname))";
  }

  $criteria_count++;
  if ($criteria_count >= $criteria_limit) {
    die("sorry");
  }
  $criteria = "";
  $returnarray = buildColumn($qualifier, $column, $usevalue);
  $criteria .= $returnarray['criteria'];
  $qualifystr = $returnarray['qualifystr'];

  addtoQuery($textstr, $colvar, $criteria, $qualifyvar, $qualifier, $qualifystr, $value);
}

$querystring = "";
$allwhere = "";

if ($myflastname || $flnqualify == 'exists' || $flnqualify == "dnexist") {
  if ($myflastname == uiTextSnippet('nosurname')) {
    addtoQuery("lastname", "myflastname", "father.lastname = \"\"", "flnqualify", uiTextSnippet('equals'), uiTextSnippet('equals'), $myflastname);
  } else {
    buildCriteria("father.lastname", "myflastname", "flnqualify", $flnqualify, $myflastname, uiTextSnippet('lastname'));
  }
}
if ($myffirstname || $ffnqualify == 'exists' || $ffnqualify == "dnexist") {
  buildCriteria("father.firstname", "myffirstname", "ffnqualify", $ffnqualify, $myffirstname, uiTextSnippet('firstname'));
}

if ($mymlastname || $mlnqualify == 'exists' || $mlnqualify == "dnexist") {
  if ($mymlastname == uiTextSnippet('nosurname')) {
    addtoQuery("lastname", "mymlastname", "mother.lastname = \"\"", "mlnqualify", uiTextSnippet('equals'), uiTextSnippet('equals'), $mymlastname);
  } else {
    buildCriteria("mother.lastname", "mymlastname", "mlnqualify", $mlnqualify, $mymlastname, uiTextSnippet('lastname'));
  }
}
if ($mymfirstname || $mfnqualify == 'exists' || $mfnqualify == "dnexist") {
  buildCriteria("mother.firstname", "mymfirstname", "mfnqualify", $mfnqualify, $mymfirstname, uiTextSnippet('firstname'));
}

if ($myfamilyid) {
  $myfamilyid = strtoupper($myfamilyid);
  if ($fidqualify == "equals" && is_numeric($myfamilyid)) {
    $myfamilyid = $familyprefix . $myfamilyid . $familysuffix;
  }
  buildCriteria("familyID", "myfamilyid", "fidqualify", $fidqualify, $myfamilyid, uiTextSnippet('familyid'));
}
if ($mymarrplace || $mpqualify == 'exists' || $mpqualify == "dnexist") {
  buildCriteria("marrplace", "mymarrplace", "mpqualify", $mpqualify, $mymarrplace, uiTextSnippet('marrplace'));
}
if ($mymarryear || $myqualify == 'exists' || $myqualify == "dnexist") {
  buildYearCriteria("marrdatetr", "mymarryear", "myqualify", "", $myqualify, $mymarryear, uiTextSnippet('marrdatetr'));
}
if ($mydivplace || $dvpqualify == 'exists' || $dvpqualify == "dnexist") {
  buildCriteria("divplace", "mydivplace", "dvpqualify", $dvpqualify, $mydivplace, uiTextSnippet('divplace'));
}
if ($mydivyear || $dvyqualify == 'exists' || $dvyqualify == "dnexist") {
  buildYearCriteria("divdatetr", "mydivyear", "dvyqualify", "", $dvyqualify, $mydivyear, uiTextSnippet('divdatetr'));
}

$dontdo = array("MARR", "DIV");
$cejoin = doCustomEvents('F');

if ($tree) {
  if ($urlstring) {
    $urlstring .= "&amp;";
  }
  $urlstring .= "tree=$tree";

  if ($querystring) {
    $querystring .= " AND ";
  }

  $query = "SELECT treename FROM $trees_table WHERE gedcom = \"$tree\"";
  $treeresult = tng_query($query);
  $treerow = tng_fetch_assoc($treeresult);
  tng_free_result($treeresult);

  $querystring .= uiTextSnippet('tree') . " " . uiTextSnippet('equals') . " {$treerow['treename']}";

  if ($allwhere) {
    $allwhere = "($allwhere) AND";
  }
  $allwhere .= " f.gedcom=\"$tree\"";

  if ($branch) {
    $urlstring .= "&amp;branch=$branch";
    $querystring .= " " . uiTextSnippet('cap_and') . " ";

    $query = "SELECT description FROM $branches_table WHERE gedcom = \"$tree\" AND branch = \"$branch\"";
    $branchresult = tng_query($query);
    $branchrow = tng_fetch_assoc($branchresult);
    tng_free_result($branchresult);

    $querystring .= uiTextSnippet('branch') . " " . uiTextSnippet('equals') . " {$branchrow['description']}";

    $allwhere .= " AND f.branch like \"%$branch%\"";
  }
}

$treequery = "SELECT count(gedcom) as treecount FROM $trees_table";
$treeresult = tng_query($treequery);
$treerow = tng_fetch_assoc($treeresult);
$numtrees = $treerow['treecount'];
tng_free_result($treeresult);

$gotInput = $mymarrplace || $mydivplace || $mymarryear || $mydivyear || $ecount;
$more = getLivingPrivateRestrictions('F', false, $gotInput);

if ($more) {
  if ($allwhere) {
    $allwhere = $tree ? "$allwhere AND " : "($allwhere) AND ";
  }
  $allwhere .= $more;
}

if ($allwhere) {
  $allwhere = "WHERE " . $allwhere . " AND ";
  $querystring = uiTextSnippet('text_for') . " $querystring";
} else {
  $allwhere = "WHERE ";
}

$max_browsesearch_pages = 5;
if ($offset) {
  $offsetplus = $offset + 1;
  $newoffset = "$offset, ";
} else {
  $offsetplus = 1;
  $newoffset = "";
  $page = 1;
}

//left join to people table twice, once for husband and for wife
$query = "SELECT f.ID, familyID, husband, wife, marrdate, marrplace, divdate, divplace, f.gedcom as gedcom, f.living, f.private, f.branch, treename,
    father.lastname as flastname, father.lnprefix as flnprefix, father.firstname as ffirstname, father.living as fliving, father.private as fprivate, father.branch as fbranch,
    mother.lastname as mlastname, mother.lnprefix as mlnprefix, mother.firstname as mfirstname, mother.living as mliving, mother.private as fprivate, mother.branch as mbranch
    FROM ($families_table as f, $trees_table) $cejoin
    LEFT JOIN $people_table AS father ON f.gedcom=father.gedcom AND husband = father.personID
    LEFT JOIN $people_table AS mother ON f.gedcom=mother.gedcom AND wife = mother.personID
    $allwhere (f.gedcom = $trees_table.gedcom)
    ORDER BY $orderstr
    LIMIT $newoffset" . $maxsearchresults;
$query2 = "SELECT count(f.ID) as fcount
    FROM ($families_table as f, $trees_table) $cejoin
    LEFT JOIN $people_table AS father ON f.gedcom=father.gedcom AND husband = father.personID
    LEFT JOIN $people_table AS mother ON f.gedcom=mother.gedcom AND wife = mother.personID
    $allwhere (f.gedcom = $trees_table.gedcom)";

//echo $query;
$result = tng_query($query);
$numrows = tng_num_rows($result);

if ($numrows == $maxsearchresults || $offsetplus > 1) {
  $result2 = tng_query($query2) or die(uiTextSnippet('cannotexecutequery') . ": $query2");
  $countrow = tng_fetch_assoc($result2);
  $totrows = $countrow['fcount'];
  tng_free_result($result2);
} else {
  $totrows = $numrows;
}

if (!$numrows) {
  $msg = uiTextSnippet('noresults') . " $querystring. " . uiTextSnippet('tryagain') . ".";
  header("Location: famsearchform.php?msg=" . urlencode($msg));
  exit;
}

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('searchresults'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/magnifying-glass.svg'><?php echo uiTextSnippet('searchresults'); ?></h2>
    <?php
    $logstring = "<a href=\"search.php?" . $_SERVER['QUERY_STRING'] . "\">" . xmlcharacters(uiTextSnippet('searchresults') . " $querystring") . "</a>";
    writelog($logstring);
    preparebookmark($logstring);

    $numrowsplus = $numrows + $offset;

    echo "<p>" . uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " " . number_format($totrows) . " $querystring</p>";
    ?>
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th></th>
          <th><?php echo uiTextSnippet('familyid'); ?></th>
          <th><?php echo $fnamesort; ?></th>
          <th><?php echo $mnamesort; ?></th>
          <th colspan='2'><?php echo $marrsort; ?></th>
          <?php if ($mydivplace || $mydivyear) { ?>
            <th colspan='2'><?php echo $divsort; ?></th>
          <?php } ?>
          <?php if ($numtrees > 1) { ?>
            <th><?php echo uiTextSnippet('tree'); ?></th>
          <?php } ?>
        </tr>
      </thead>
      <?php
      $i = $offsetplus;
      //$chartlinkimg = getimagesize("img/Chart.gif");
      $chartlink = "<img src=\"img/Chart.gif\" class=\"chartimg\">";
      while ($row = tng_fetch_assoc($result)) {
        //assemble frow and mrow, override family living flag if allow_living for either of these is no
        $frow = array(
          "firstname" => $row['ffirstname'],
          "lnprefix" => $row['flnprefix'],
          "lastname" => $row['flastname'],
          "living" => $row['fliving'],
          "private" => $row['fprivate'],
          "branch" => $row['fbranch']
        );
        $rights = determineLivingPrivateRights($frow);
        $frow['allow_living'] = $rights['living'];
        $frow['allow_private'] = $rights['private'];

        $mrow = array(
          "firstname" => $row['mfirstname'],
          "lnprefix" => $row['mlnprefix'],
          "lastname" => $row['mlastname'],
          "living" => $row['mliving'],
          "branch" => $row['mbranch'],
          "private" => $row['mprivate']
        );
        $rights = determineLivingPrivateRights($mrow);
        $mrow['allow_living'] = $rights['living'];
        $mrow['allow_private'] = $rights['private'];

        $rights = determineLivingPrivateRights($row);
        if ($rights['both']) {
          $marrdate = $row['marrdate'] ? displayDate($row['marrdate']) : "";
          $marrplace = $row['marrplace'] ? $row['marrplace'] . " " . placeImage($row['marrplace']) : "";
          $divdate = $row['divdate'] ? displayDate($row['divdate']) : "";
          $divplace = $row['divplace'] ? $row['divplace'] . " " . placeImage($row['divplace']) : "";
        } else {
          $marrdate = $marrplace = $divdate = $divplace = $livingOK = "";
        }
        $fname = getNameRev($frow);
        $mname = getNameRev($mrow);

        $famidstr = "<a href=\"familygroup.php?familyID={$row['familyID']}&amp;tree={$row['gedcom']}\" class=\"fam\" id=\"f{$row['familyID']}_t{$row['gedcom']}\">{$row['familyID']} </a>";

        echo "<tr>";
        echo "<td>$i</td>\n";
        $i++;
        echo "<td>$famidstr";
          echo "<div class='person-img' id=\"mi{$row['gedcom']}_{$row['familyID']}\">\n";
            echo "<div class='person-prev' id=\"prev{$row['gedcom']}_{$row['familyID']}\"></div>\n";
          echo "</div>\n";
        echo "</td>";
        echo "<td>$fname</td><td>$mname</td>";
        echo "<td>$marrdate</td><td>$marrplace</td>";
        if ($mydivyear || $mydivplace) {
          echo "<td>$divdate </td><td>$divplace</td>";
        }
        if ($numtrees > 1) {
          echo "<td><a href=\"showtree.php?tree={$row['gedcom']}\">{$row['treename']}</a></td>";
        }
        echo "</tr>\n";
      }
      tng_free_result($result);
      ?>
    </table>
    <?php
    echo buildSearchResultPagination($totrows, "search.php?$urlstring&amp;mybool=$mybool&amp;nr=$maxsearchresults&amp;showspouse=$showspouse&amp;showdeath=$showdeath&amp;offset", $maxsearchresults, $max_browsesearch_pages);
    echo $publicFooterSection->build();
    ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script>
    var searchtimer;
    $(document).ready(function () {
      $('a.fam').each(function (index, item) {
        var matches = /f(\w*)_t(\w*)/.exec(item.id);
        var familyID = matches[1];
        var tree = matches[2];
        item.onmouseover = function () {
          searchtimer = setTimeout('showFamilyPreview(\'' + familyID + '\',\'' + tree + '\')', 1000);
        };
        item.onmouseout = function () {
          closeFamilyPreview(familyID, tree);
        };
        item.onclick = function () {
          closeFamilyPreview(familyID, tree);
        };
      });
      $('a.fl').each(function (index, item) {
        item.title = textSnippet('findplaces');
      });
    });

    function showFamilyPreview(familyID, tree) {
      var entitystr = tree + "_" + familyID;
      $('#prev' + entitystr).css('visibility', 'visible');
      if (!$('#prev' + entitystr).html()) {
        $('#prev' + entitystr).html('<div id="ld' + entitystr + '" class="person-inner"><img src="img/spinner.gif" style="border:0"> ' + textSnippet('loading') + '</div>');
        var params = {familyID: familyID, tree: tree};
        $.ajax({
          url: 'ajx_fampreview.php',
          data: params,
          dataType: 'html',
          success: function (req) {
            $('#ld' + entitystr).html(req);
          }
        });
      }
      return false;
    }

    function closeFamilyPreview(familyID, tree) {
      clearTimeout(searchtimer);
      var entitystr = tree + '_' + familyID;
      $('#prev' + entitystr).css('visibility', 'hidden');
    }
  </script>
</body>
</html>