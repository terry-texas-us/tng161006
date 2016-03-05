<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");

if (!$allow_edit || ($assignedtree && $assignedtree != $tree)) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$query = "SELECT personID, firstname, lastname, lnprefix, prefix, suffix, branch, gedcom, nameorder, living, private FROM $people_table WHERE gedcom = \"$tree\" and branch LIKE \"%$branch%\" ORDER BY lastname, firstname";
$brresult = tng_query($query);
$numresults = tng_num_rows($brresult);
$names = "";
$counter = $fcounter = 0;

while ($row = tng_fetch_assoc($brresult)) {
  $rights = determineLivingPrivateRights($row, true, true);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];

  $names .= "<a href=\"peopleEdit.php?personID={$row['personID']}&amp;tree={$row['gedcom']}&amp;cw=1\" target='_blank'>" . getName($row) . " ({$row['personID']})</a><br>\n";
  $counter++;
}
tng_free_result($brresult);

$query = "SELECT familyID, husband, wife, gedcom, branch, living, private FROM $families_table WHERE gedcom = \"$tree\" AND branch LIKE \"%$branch%\" ORDER BY familyID";
$brresult = tng_query($query);
$numfresults = tng_num_rows($brresult);

if ($numresults) {
  $names .= "<br>\n";
}
while ($row = tng_fetch_assoc($brresult)) {
  $rights = determineLivingPrivateRights($row, true, true);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];

  $names .= "<a href=\"familiesEdit.php?familyID={$row['familyID']}&amp;tree={$row['gedcom']}&amp;cw=1\" target='_blank'>" . getFamilyName($row) . "</a><br>\n";
  $fcounter++;
}
tng_free_result($brresult);

if (!$names) {
  $names = "<p>" . uiTextSnippet('norecords') . "</p>";
}
header("Content-type:text/html; charset=" . $session_charset);
?>
<header class='modal-header'>
  <h4><?php echo uiTextSnippet('branchid') . ': ' . $branch ?></h4>
  <p><?php echo uiTextSnippet('description') . ': ' . $description; ?></p>
  <p><?php echo uiTextSnippet('treename') . ': ' . $tree; ?></p>
  <p><?php echo uiTextSnippet('existlabels') . ': ' . $counter . ' ' . uiTextSnippet('people') . ', ' . $fcounter . ' ' . uiTextSnippet('families'); ?></p>
</header>
<div class='modal-body'>
  <?php echo $names; ?>
</div>
<footer class='modal-footer'></footer>
