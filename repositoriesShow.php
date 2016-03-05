<?php
include("tng_begin.php");

include("functions.php");

function doRepoSearch( $instance, $pagenav ) {
    global $reposearch;
    global $tree;

    $str = "<span>\n";
    $str .= buildFormElement("repositoriesShow", "get", "RepoSearch$instance");
    $str .= "<input name='reposearch' type='text' value=\"$reposearch\" /> \n";
  $str .= "<input type='submit' value=\"" . uiTextSnippet('search') . "\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $str .= $pagenav;
    if( $reposearch )
    {$str .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='repositoriesShow.php'>" . uiTextSnippet('browseallrepos') . "</a>";}
    $str .= "<input name='tree' type='hidden' value=\"$tree\" />\n";
    $str .= "</form></span>\n";
    
    return $str;
}

$max_browserepo_pages = 5;
if( $offset ) {
    $offsetplus = $offset + 1;
    $newoffset = "$offset, ";
}
else {
    $offsetplus = 1;
    $newoffset = "";
    $page = 1;
}

$reposearch = trim($reposearch);
if( $tree ) {
    $wherestr = "WHERE $repositories_table.gedcom = \"$tree\"";
    if( $reposearch ) {$wherestr .= " AND reponame LIKE \"%$reposearch%\"";}
    $join = "INNER JOIN";
}
else {
    if( $reposearch )
    {$wherestr = "WHERE reponame LIKE \"%$reposearch%\"";}
    else
    {$wherestr = "";}
    $join = "LEFT JOIN";
}

$query = "SELECT repoID, reponame, $repositories_table.gedcom as gedcom, treename FROM $repositories_table $join $trees_table on $repositories_table.gedcom = $trees_table.gedcom $wherestr ORDER BY reponame LIMIT $newoffset" . $maxsearchresults;
$result = tng_query($query);

$numrows = tng_num_rows( $result );

if( $numrows == $maxsearchresults || $offsetplus > 1 ) {
    if( $tree )
    {$query = "SELECT count(repoID) as scount FROM $repositories_table LEFT JOIN $trees_table on $repositories_table.gedcom = $trees_table.gedcom $wherestr";}
    else
    {$query = "SELECT count(repoID) as scount FROM $repositories_table $wherestr";}
    $result2 = tng_query($query);
    $row = tng_fetch_assoc( $result2 );
    $totrows = $row['scount'];
}
else
    {$totrows = $numrows;}

$numrowsplus = $numrows + $offset;

$treestr = $tree ? " (" . uiTextSnippet('tree') . ": $tree)" : "";
$logstring = "<a href=\"repositoriesShow.php?tree=$tree&amp;offset=$offset&amp;reposearch=$reposearch\">" . xmlcharacters(uiTextSnippet('repositories') . $treestr) . "</a>";
writelog($logstring);
preparebookmark($logstring);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('repositories'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/building.svg'><?php echo uiTextSnippet('repositories'); ?></h2>
    <br clear='left'>
    <?php
    echo treeDropdown(array('startform' => true, 'endform' => true, 'action' => 'repositoriesShow', 'method' => 'get', 'name' => 'form1', 'id' => 'form1'));

    if ($totrows) {
      echo "<p><span>" . uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows</span></p>";
    }
    $pagenav = buildSearchResultPagination( $totrows, "repositoriesShow.php?reposearch=$reposearch&amp;offset", $maxsearchresults, $max_browserepo_pages );
    if ($pagenav || $reposearch) {
      echo doRepoSearch( 1, $pagenav );
      echo "<br>\n";
    }
    ?>
    <table class="table table-sm table-striped">
      <tr>
        <th></th>
        <th><?php echo uiTextSnippet('repoid'); ?></th>
        <th><?php echo uiTextSnippet('name'); ?></th>
        <?php if( $numtrees > 1 ) { ?>
          <th><?php echo uiTextSnippet('tree'); ?></th>
        <?php } ?>
      </tr>
      <?php
      $i = $offsetplus;
      while ($row = tng_fetch_assoc($result)) {
        echo "<tr><td><span>$i</span></td>\n";
        echo "<td><span><a href=\"repositoriesShowItem.php?repoID={$row['repoID']}&amp;tree={$row['gedcom']}\">{$row['repoID']}</a>&nbsp;</span></td>";
        echo "<td><span><a href=\"repositoriesShowItem.php?repoID={$row['repoID']}&amp;tree={$row['gedcom']}\">{$row['reponame']}</a>&nbsp;</span></td>";
        if( $numtrees > 1 )
          {echo "<td><span>{$row['treename']}&nbsp;</span></td>";}
        echo "</tr>\n";
        $i++;
      }
      tng_free_result($result);
      ?>
    </table>
    <?php
    if( $pagenav || $reposearch ) {
      echo doRepoSearch( 2, $pagenav ) . "<br>\n";
    }
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
