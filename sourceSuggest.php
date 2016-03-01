<?php
include("tng_begin.php");
require 'suggest.php';
require 'sources.php';

$_SESSION['tng_email'] = generatePassword(1);
$_SESSION['tng_comments'] = generatePassword(1);
$_SESSION['tng_yourname'] = generatePassword(1);

$righttree = checktree($tree);
$preemail = getCurrentUserEmail($currentuser, $users_table);

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
    echoResponseMessage($message, $sowner, $ssendemail);
    ?>
    <form action='sourceSuggestFormAction.php' method='post' name='suggest' id='suggest' onsubmit="return validateForm();">
      <div class='form-container'>
        <h4><?php echo uiTextSnippet('suggestchange'); ?></h4>
        <input name="sourceID" type='hidden' value="<?php echo $ID; ?>">
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
        <input name='enttype' type='hidden' value="S">
        <input name='ID' type='hidden' value="<?php echo $ID; ?>">
        <input name='tree' type='hidden' value="<?php echo $tree; ?>">
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
