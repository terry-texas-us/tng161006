<?php
require 'tng_begin.php';
require 'suggest.php';

$_SESSION['tng_email'] = generatePassword(1);
$_SESSION['tng_comments'] = generatePassword(1);
$_SESSION['tng_yourname'] = generatePassword(1);

$preemail = getCurrentUserEmail($currentuser, $users_table);

if ($enttype == 'I') {
  $typestr = 'person';
  $result = getPersonDataPlusDates($ID);
  if ($result) {
    $row = tng_fetch_assoc($result);
    $rights = determineLivingPrivateRights($row);
    $row['allow_living'] = $rights['living'];
    $row['allow_private'] = $rights['private'];
    $name = getName($row) . " ($ID)";
    tng_free_result($result);
  }
  $years = getYears($row);
} elseif ($enttype == 'F') {
  $typestr = 'family';
  $result = getFamilyData($ID);
  $row = tng_fetch_assoc($result);
  tng_free_result($result);

  $hname = $wname = '';
  $rights = determineLivingPrivateRights($row);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];

  if ($row['husband']) {
    $result = getPersonSimple($row['husband']);
    $prow = tng_fetch_assoc($result);
    tng_free_result($result);
    $prights = determineLivingPrivateRights($prow);
    $prow['allow_living'] = $prights['living'];
    $prow['allow_private'] = $prights['private'];
    $hname = getName($prow);
  }
  if ($row['wife']) {
    $result = getPersonSimple($row['wife']);
    $prow = tng_fetch_assoc($result);
    tng_free_result($result);
    $prights = determineLivingPrivateRights($prow);
    $prow['allow_living'] = $prights['living'];
    $prow['allow_private'] = $prights['private'];
    $wname = getName($prow);
  }
  $plus = $hname && $wname ? ' + ' : '';
  $name = uiTextSnippet('family') . ": $hname$plus$wname ($ID)";

  $years = $years = $row['marrdate'] && $row['allow_living'] && $row['allow_private'] ? uiTextSnippet('marrabbr') . ' ' . displayDate($row['marrdate']) : '';
} elseif ($enttype == 'S') {
  $typestr = 'source';
  $query = "SELECT title FROM $sources_table WHERE sourceID = '$ID'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  tng_free_result($result);

  $query = "SELECT count(personID) AS ccount FROM $citations_table, $people_table
      WHERE $citations_table.sourceID = '$ID' AND $citations_table.persfamID = $people_table.personID AND living = '1'";
  $sresult = tng_query($query);
  $srow = tng_fetch_assoc($sresult);
  $row['living'] = $srow['ccount'] ? 1 : 0;

  $rights = determineLivingPrivateRights($row);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  tng_free_result($sresult);

  $name = uiTextSnippet('source') . ": {$row['title']} ($ID)";
  $years = '';
} elseif ($enttype == 'R') {
  $typestr = 'repo';
  $query = "SELECT reponame FROM $repositories_table WHERE repoID = '$ID'";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  tng_free_result($result);

  $row['living'] = 0;
  $row['allow_living'] = $row['allow_private'] = 1;

  $name = uiTextSnippet('repository') . ": {$row['reponame']} ($ID)";
} elseif ($enttype == 'L') {
  $typestr = 'place';
  $row['living'] = 0;
  $row['allow_living'] = $row['allow_private'] = 1;
  $name = $ID;
} else {
  $typestr = '';
}
scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();
header('Content-type: text/html; charset=' . $session_charset);

$headTitle = ($enttype) ? uiTextSnippet('suggestchange') . ": $name" : uiTextSnippet('contactus');
$headSection->setTitle($headTitle);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body class='form-suggest'>
  <section class='container'>
    <?php
    if ($enttype) {
      $comments = uiTextSnippet('proposedchanges');
      echo $publicHeaderSection->build();

      $photostr = showSmallPhoto($ID, $name, $row['allow_living'] && $row['allow_private'], 0, false, $row['sex']);
      echo tng_DrawHeading($photostr, $name, $years);

      echo tng_menu($enttype, 'suggest', $ID);
      echo "<br>\n";
      $buttontext = uiTextSnippet('submitsugg');
    } else {
      $comments = uiTextSnippet('yourcomments');
      echo $publicHeaderSection->build();
      ?>
      <h2><img class='icon-md' src='svg/mail.svg'><?php echo $headTitle; ?></h2>
      <br clear='left'>
      <?php
      $buttontext = uiTextSnippet('sendmsg');
    }
    echoResponseMessage($message, $sowner, $ssendemail);
    if ($enttype) {
      echo "<h4><b>$headTitle</b></h4>\n";
    }
    ?>
      <form action='mixedSuggestFormAction.php' method='post' name='suggest' id='suggest' data-email-control='#email' data-confirm-email-control='#confirm-email'>
      <div class='form-container'>
        <?php if ($typestr) { ?>
          <input name="<?php echo $typestr; ?>ID" type='hidden' value="<?php echo $ID; ?>"/>
        <?php } ?>
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
        <?php if ($page) { ?>
          <label><?php echo uiTextSnippet('subject'); ?>:</label>
          <div><?php echo stripslashes($page); ?></div>
        <?php } ?>
        <hr>
        <?php echo $comments; ?>:
        <textarea class='form-control' name="<?php echo $_SESSION['tng_comments']; ?>" rows='4' required></textarea>
        <input name='enttype' type='hidden' value="<?php echo $enttype; ?>">
        <input name='ID' type='hidden' value="<?php echo $ID; ?>">
        <input name='page' type='hidden' value="<?php echo $page; ?>">
        <br>
        <button class="btn btn-primary btn-block" type="submit"><?php echo $buttontext; ?></button>
      </div>
    </form>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
<script src='js/suggest.js'></script>
</body>
</html>
