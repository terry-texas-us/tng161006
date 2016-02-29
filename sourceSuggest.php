<?php
include("tng_begin.php");

require 'sources.php';

$_SESSION['tng_email'] = generatePassword(1);
$_SESSION['tng_comments'] = generatePassword(1);
$_SESSION['tng_yourname'] = generatePassword(1);

$righttree = checktree($tree);

if ($currentuser) {
  $query = "SELECT email FROM $users_table WHERE username=\"$currentuser\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  $preemail = $row['email'];
  tng_free_result($result);
} else {
  $preemail = "";
}
$query = "SELECT title FROM $sources_table WHERE sourceID = \"$ID\" AND gedcom = \"$tree\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);

$query = "SELECT count(personID) as ccount FROM $citations_table, $people_table
  WHERE $citations_table.sourceID = '$ID' AND $citations_table.persfamID = $people_table.personID AND $citations_table.gedcom = $people_table.gedcom
  AND living = '1'";
$sresult = tng_query($query);
$srow = tng_fetch_assoc($sresult);
$row['living'] = $srow['ccount'] ? 1 : 0;

$rights = determineLivingPrivateRights($row, $righttree);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];
tng_free_result($sresult);

$name = uiTextSnippet('source') . ": {$row['title']} ($ID)";
$years = "";

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();
header("Content-type: text/html; charset=" . $session_charset);

$headTitle = uiTextSnippet('suggestchange') . ": $name";
$headSection->setTitle($headTitle);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body class='form-suggest'>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();

    $photostr = showSmallPhoto($ID, $name, $row['allow_living'] && $row['allow_private'], 0, false, $row['sex']);
    echo tng_DrawHeading($photostr, $name, $years);

    echo buildSourceMenu('suggest', $ID);
    echo "<br>\n";
    $buttontext = uiTextSnippet('submitsugg');
    if ($message) {
      $newmessage = uiTextSnippet($message);
      if ($message == "mailnotsent") {
        $newmessage = preg_replace("/xxx/", $sowner, $newmessage);
        $newmessage = preg_replace("/yyy/", $ssendemail, $newmessage);
      }
      echo "<p><strong><font color=\"red\">$newmessage</font></strong></p>\n";
    }
    ?>
    <form action="tngsendmail.php" method="post" name="suggest" id="suggest" onsubmit="return validateForm();">
      <div class='form-container'>
        <h4><?php echo uiTextSnippet('suggestchange'); ?></h4>
        <input name="sourceID" type='hidden' value="<?php echo $ID; ?>">
        <div class='form-group yourname'>
          <label><?php echo uiTextSnippet('yourname'); ?>:</label>
          <input class='form-control' name="<?php echo $_SESSION['tng_yourname']; ?>" type='text'>
        </div>  
        <div class='form-group'>
          <label><?php echo uiTextSnippet('email'); ?>:</label>
          <input class='form-control' id='email' name="<?php echo $_SESSION['tng_email']; ?>" type='email' value="<?php echo $preemail; ?>">
          <input class='form-control' id='confirm-email' name='em2' type='email' value="<?php echo $preemail; ?>" placeholder="<?php echo uiTextSnippet('emailagain'); ?>">
          <input name='mailme' type='checkbox' value='1'><?php echo uiTextSnippet('mailme'); ?>
        </div>
        <?php
        if ($page) { ?>
          <label><?php echo uiTextSnippet('subject'); ?>:</label>
          <div><?php echo stripslashes($page); ?></div>
        <?php } ?>
        <hr>
        <?php echo uiTextSnippet('proposedchanges'); ?>
        <textarea class='form-control' name="<?php echo $_SESSION['tng_comments']; ?>" rows='4'></textarea>
        <input name='enttype' type='hidden' value="S">
        <input name='ID' type='hidden' value="<?php echo $ID; ?>">
        <input name='tree' type='hidden' value="<?php echo $tree; ?>">
        <input name='page' type='hidden' value="<?php echo $page; ?>">
        <br>
        <button class="btn btn-primary btn-block" type="submit"><?php echo uiTextSnippet('submitsugg'); ?></button>
      </div>
    </form>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
<script>
  function validateForm() {
    if( document.suggest.<?php echo $_SESSION['tng_yourname'] ?>.value === '') {
      alert(textSnippet('entername'));
      return false;
    }
    var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,6})$/;
    var address = document.suggest.<?php echo $_SESSION['tng_email'] ?>.value;
    if(address.length === 0 || reg.test(address) === false){
      alert(textSnippet('enteremail'));
      return false;
    }
    else if( document.suggest.em2.value.length === 0 ) {
      alert(textSnippet('enteremail2'));
      return false;
    }
    else if( document.suggest.<?php echo $_SESSION['tng_email'] ?>.value !== document.suggest.em2.value ) {
      alert(textSnippet('emailsmatch'));
      return false;
    }
    if( document.suggest.<?php echo $_SESSION['tng_comments'] ?>.value === '') {
      alert(textSnippet('entercomments'));
      return false;
    }
    return true;
  }
</script>
</body>
</html>