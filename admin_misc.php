<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if ($assignedtree) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('misc'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <?php
  echo $adminHeaderSection->build('misc', $message);
  $navList = new navList('');
  $navList->appendItem([true, "admin_misc.php", uiTextSnippet('menu'), "misc"]);
  $navList->appendItem([true, "admin_notelist.php", uiTextSnippet('notes'), "notes"]);
  $navList->appendItem([true, "admin_whatsnewmsg.php", uiTextSnippet('whatsnew'), "whatsnew"]);
  $navList->appendItem([true, "admin_mostwanted.php", uiTextSnippet('mostwanted'), "mostwanted"]);
  echo $navList->build("misc");
  ?>
  <div>
    <h4><img src="img/tng_expand.gif" width="15" height="15"> <a
              href="admin_notelist.php"><?php echo uiTextSnippet('notes'); ?></a></h4>
    <blockquote><?php echo uiTextSnippet('noteblurb'); ?></blockquote>
    <h4><img src="img/tng_expand.gif" width="15" height="15"> <a
              href="admin_whatsnewmsg.php"><?php echo uiTextSnippet('whatsnew'); ?></a></h4>
    <blockquote><?php echo uiTextSnippet('whatsnewblurb'); ?></blockquote>
    <h4><img src="img/tng_expand.gif" width="15" height="15"> <a
              href="admin_mostwanted.php"><?php echo uiTextSnippet('mostwanted'); ?></a></h4>
    <blockquote><?php echo uiTextSnippet('mwblurb'); ?></blockquote>
  </div>
  <?php echo "<div align=\"right\">$tng_title, v.$tng_version</div>"; ?>
</body>
</html>
