<?php
require 'begin.php';
require 'adminlib.php';
if (!$mediaID) {
  die("no args");
}

require 'checklogin.php';

initMediaTypes();

$query = "SELECT * FROM $media_table WHERE mediaID = \"$mediaID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['firstname'] = preg_replace("/\"/", "&#34;", $row['firstname']);

if (!$allowMediaEdit && !$allowMediaAdd) {
  $message = uiTextSnippet('norights');
  header("Location: ajx_login.php?message=" . urlencode($message));
  exit;
}
$query = "SELECT $medialinks_table.medialinkID as mlinkID, $medialinks_table.personID as personID, eventID, people.lastname as lastname, people.lnprefix as lnprefix, people.firstname as firstname, people.prefix as prefix, people.suffix as suffix, people.nameorder as nameorder, altdescription, altnotes, people.branch as branch, familyID, people.personID as personID2, wifepeople.personID as wpersonID, wifepeople.firstname as wfirstname, wifepeople.lnprefix as wlnprefix, wifepeople.lastname as wlastname, wifepeople.prefix as wprefix, wifepeople.suffix as wsuffix, wifepeople.nameorder as wnameorder, husbpeople.personID as hpersonID, husbpeople.firstname as hfirstname, husbpeople.lnprefix as hlnprefix, husbpeople.lastname as hlastname, husbpeople.prefix as hprefix, husbpeople.suffix as hsuffix, husbpeople.nameorder as hnameorder, sourceID, sources.title, repositories.repoID as repoID, reponame, defphoto, linktype, dontshow, people.living, people.private, $families_table.living as fliving, $families_table.private as fprivate
    FROM $medialinks_table
    LEFT JOIN $people_table AS people ON $medialinks_table.personID = people.personID
    LEFT JOIN $families_table ON $medialinks_table.personID = $families_table.familyID
    LEFT JOIN $sources_table AS sources ON $medialinks_table.personID = sources.sourceID
    LEFT JOIN $repositories_table AS repositories ON $medialinks_table.personID = repositories.repoID
    LEFT JOIN $people_table AS husbpeople ON $families_table.husband = husbpeople.personID
    LEFT JOIN $people_table AS wifepeople ON $families_table.wife = wifepeople.personID
    WHERE mediaID = \"$mediaID\" ORDER BY $medialinks_table.medialinkID DESC";
$result2 = tng_query($query);

header("Content-type:text/html; charset=" . $session_charset);
?>
<table>
  <tr>
    <td>
      <h4><?php echo uiTextSnippet('medialinks'); ?></h4>
      <form action="" name='form1' id='form1'>
        <?php include 'micro_medialinks.php'; ?>
      </form>
    </td>
  </tr>
</table>