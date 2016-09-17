<?php
require 'begin.php';
require 'adminlib.php';
if (!$mediaID) {
  die('no args');
}

require 'checklogin.php';

initMediaTypes();

$query = "SELECT * FROM $media_table WHERE mediaID = \"$mediaID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['firstname'] = preg_replace('/\"/', '&#34;', $row['firstname']);

if (!$allowMediaEdit && !$allowMediaAdd) {
  $message = uiTextSnippet('norights');
  header('Location: ajx_login.php?message=' . urlencode($message));
  exit;
}
$query = "SELECT $medialinks_table.medialinkID AS mlinkID, $medialinks_table.personID AS personID, eventID, people.lastname AS lastname, people.lnprefix AS lnprefix, people.firstname AS firstname, people.prefix AS prefix, people.suffix AS suffix, people.nameorder AS nameorder, altdescription, altnotes, people.branch AS branch, familyID, people.personID AS personID2, wifepeople.personID AS wpersonID, wifepeople.firstname AS wfirstname, wifepeople.lnprefix AS wlnprefix, wifepeople.lastname AS wlastname, wifepeople.prefix AS wprefix, wifepeople.suffix AS wsuffix, wifepeople.nameorder AS wnameorder, husbpeople.personID AS hpersonID, husbpeople.firstname AS hfirstname, husbpeople.lnprefix AS hlnprefix, husbpeople.lastname AS hlastname, husbpeople.prefix AS hprefix, husbpeople.suffix AS hsuffix, husbpeople.nameorder AS hnameorder, sourceID, sources.title, repositories.repoID AS repoID, reponame, defphoto, linktype, dontshow, people.living, people.private, $families_table.living AS fliving, $families_table.private AS fprivate FROM $medialinks_table LEFT JOIN $people_table AS people ON $medialinks_table.personID = people.personID LEFT JOIN $families_table ON $medialinks_table.personID = $families_table.familyID LEFT JOIN $sources_table AS sources ON $medialinks_table.personID = sources.sourceID LEFT JOIN $repositories_table AS repositories ON $medialinks_table.personID = repositories.repoID LEFT JOIN $people_table AS husbpeople ON $families_table.husband = husbpeople.personID LEFT JOIN $people_table AS wifepeople ON $families_table.wife = wifepeople.personID WHERE mediaID = '$mediaID' ORDER BY $medialinks_table.medialinkID DESC";
$result2 = tng_query($query);

header('Content-type:text/html; charset=' . $session_charset);
?>
<table>
  <tr>
    <td>
      <h4><?php echo uiTextSnippet('medialinks'); ?></h4>
      <form action="" name='form1' id='form1'>
        <?php require 'micro_medialinks.php'; ?>
      </form>
    </td>
  </tr>
</table>