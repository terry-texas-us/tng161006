<?php
include("begin.php");
include("genlib.php");
include("getlang.php");

include("checklogin.php");

$newroot = preg_replace('/\//', '', $rootpath);
$newroot = preg_replace('/ /', '', $newroot);
$newroot = preg_replace('/\./', '', $newroot);
$ref = "tngbookmarks_$newroot";

$bookmarks = explode("|", $_COOKIE[$ref]);
$bookmarkstr = $_SESSION['tnglastpage'];
foreach ($bookmarks as $bookmark) {
  if ($bookmark && stripslashes($bookmark) != stripslashes($_SESSION['tnglastpage'])) {
    $bookmarkstr .= "|" . $bookmark;
  }
}
setcookie($ref, stripslashes($bookmarkstr), time() + 31536000, "/");

header("Content-type:text/html; charset=" . $session_charset);
?>
<div id='bkmkdiv'>
  <form>
    <header class='modal-header'>
      <h4>
        <img class='icon-md' src='svg/bookmark.svg' alt=''><?php echo uiTextSnippet('bookmarked'); ?>
      </h4>
    </header>
    <div class='modal-body'>
    </div> <!-- .modal-body -->
    <footer class='modal-footer'>
      <input type='button' onclick="tnglitbox.remove(); return false;" value="<?php echo uiTextSnippet('closewindow'); ?>">
      <input type='button' onclick="window.location.href='bookmarks.php';" value="<?php echo uiTextSnippet('mngbookmarks'); ?>">
    </footer>
  </form>
</div>