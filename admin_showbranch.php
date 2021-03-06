<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header('Location: admin_login.php?message=' . urlencode($message));
  exit;
}
$query = 'SELECT treename FROM trees';
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

$query = "SELECT description FROM branches WHERE branch = '$branch'";
$result = tng_query($query);
$brow = tng_fetch_assoc($result);
tng_free_result($result);

$query = "SELECT personID, firstname, lastname, lnprefix, prefix, suffix, branch, nameorder, living, private FROM people WHERE branch LIKE \"%$branch%\" ORDER BY lastname, firstname";
$brresult = tng_query($query);

header('Content-type: text/html; charset=' . $sessionCharset);
$headSection->setTitle(uiTextSnippet('labelbranches'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $sessionCharset); ?>
<body id="branches-labelbranches">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('branches-labelbranches', $message);
    $navList = new navList('');
    $navList->appendItem([true, 'branchesBrowse.php', uiTextSnippet('browse'), 'findbranch']);
    $navList->appendItem([$allowAdd, 'branchesAdd.php', uiTextSnippet('add'), 'addbranch']);
    $navList->appendItem([$allowEdit, '#', uiTextSnippet('labelbranches'), 'label']);
    echo $navList->build('label');
    ?>
    <table class='table table-sm'>
      <tr>
        <td>
          <table>
            <tr>
              <td><strong><?php echo uiTextSnippet('tree'); ?>:</strong></td>
              <td><?php echo $row['treename']; ?></td>
            </tr>
            <tr>
              <td><strong><?php echo uiTextSnippet('branch'); ?>:</strong></td>
              <td><?php echo $brow['description']; ?></td>
            </tr>
            <tr>
              <td colspan='2'>
                <span><br>
                  <?php
                  echo "<p><a href=\"admin_branchmenu.php?branch=$branch\">" . uiTextSnippet('labelbranches') . "</a></p>\n";
                  while ($row = tng_fetch_assoc($brresult)) {
                    $rights = determineLivingPrivateRights($row, true);
                    $row['allow_living'] = $rights['living'];
                    $row['allow_private'] = $rights['private'];

                    echo "<a href=\"peopleEdit.php?personID={$row['personID']}&amp;cw=1\" target='_blank'>" . getNameRev($row) . " ({$row['personID']})</a><br>\n";
                  }
                  tng_free_result($brresult);
                  ?>
                </span>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
</body>
</html>