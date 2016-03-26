<?php
require 'tng_begin.php';
require 'suggest.php';
require 'families.php';

$_SESSION['tng_email'] = generatePassword(1);
$_SESSION['tng_comments'] = generatePassword(1);
$_SESSION['tng_yourname'] = generatePassword(1);

$righttree = checktree($tree);
$preemail = getCurrentUserEmail($currentuser, $users_table);

$result = getFamilyData($tree, $ID);
$row = tng_fetch_assoc($result);
tng_free_result($result);

$hname = $wname = "";
$rights = determineLivingPrivateRights($row, $righttree);
$row['allow_living'] = $rights['living'];
$row['allow_private'] = $rights['private'];

if ($row['husband']) {
  $result = getPersonSimple($tree, $row['husband']);
  $prow = tng_fetch_assoc($result);
  tng_free_result($result);
  $prights = determineLivingPrivateRights($prow, $righttree);
  $prow['allow_living'] = $prights['living'];
  $prow['allow_private'] = $prights['private'];
  $hname = getName($prow);
}
if ($row['wife']) {
  $result = getPersonSimple($tree, $row['wife']);
  $prow = tng_fetch_assoc($result);
  tng_free_result($result);
  $prights = determineLivingPrivateRights($prow, $righttree);
  $prow['allow_living'] = $prights['living'];
  $prow['allow_private'] = $prights['private'];
  $wname = getName($prow);
}

$plus = $hname && $wname ? " + " : "";
$name = uiTextSnippet('family') . ": $hname$plus$wname ($ID)";

$years = $years = $row['marrdate'] && $row['allow_living'] && $row['allow_private'] ? uiTextSnippet('marrabbr') . " " . displayDate($row['marrdate']) : "";

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

    echo buildFamilyMenu('suggest', $ID);
    echo "<br style='clear: both;'>\n";
    echoResponseMessage($message, $sowner, $ssendemail);
    ?>
    <form action='familySuggestFormAction.php.php' method='post' name='suggest' id='suggest' data-email-control='#email' data-confirm-email-control='#confirm-email'>
      <div class='form-container'>
        <h4><?php echo uiTextSnippet('suggestchange'); ?></h4>
        <input name="familyID" type='hidden' value="<?php echo $ID; ?>"/>
        <div class='form-group yourname'>
          <label><?php echo uiTextSnippet('yourname'); ?>:</label>
          <input class='form-control' name="<?php echo $_SESSION['tng_yourname']; ?>" type='text' required>
        </div>  
        <div class='form-group'>
          <label><?php echo uiTextSnippet('email'); ?>:</label>
          <input class='form-control' id='email' name="<?php echo $_SESSION['tng_email']; ?>" type='email' value="<?php echo $preemail; ?>">
          <input class='form-control' id='confirm-email' name='em2' type='email' value="<?php echo $preemail; ?>" placeholder="<?php echo uiTextSnippet('emailagain'); ?>" required>
          <input name='mailme' type='checkbox' value='1'><?php echo uiTextSnippet('mailme'); ?>
        </div>
        <hr>
        <?php echo uiTextSnippet('proposedchanges'); ?>
        <textarea class='form-control' name="<?php echo $_SESSION['tng_comments']; ?>" rows='4' required></textarea>
        <input name='enttype' type='hidden' value='F'>
        <input name='ID' type='hidden' value="<?php echo $ID; ?>"/>
        <input name='tree' type='hidden' value="<?php echo $tree; ?>"/>
        <br>
        <button class="btn btn-primary btn-block" type="submit"><?php echo uiTextSnippet('submitsugg'); ?></button>
      </div>
    </form>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
<script src='js/suggest.js'></script>
</body>
</html>
