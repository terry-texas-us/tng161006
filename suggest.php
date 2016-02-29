<?php
include("tng_begin.php");

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
if ($enttype == 'I') {
  $typestr = "person";
  $result = getPersonDataPlusDates($tree, $ID);
  if ($result) {
    $row = tng_fetch_assoc($result);
    $rights = determineLivingPrivateRights($row, $righttree);
    $row['allow_living'] = $rights['living'];
    $row['allow_private'] = $rights['private'];
    $name = getName($row) . " ($ID)";
    tng_free_result($result);
  }
  $treeResult = getTreeSimple($tree);
  $treerow = tng_fetch_assoc($treeResult);
  $disallowgedcreate = $treerow['disallowgedcreate'];
  tng_free_result($treeResult);

  $years = getYears($row);
} elseif ($enttype == 'F') {
  $typestr = "family";
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
} elseif ($enttype == 'S') {
  $typestr = "source";
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
} elseif ($enttype == 'R') {
  $typestr = "repo";
  $query = "SELECT reponame FROM $repositories_table WHERE repoID = \"$ID\" AND gedcom = \"$tree\"";
  $result = tng_query($query);
  $row = tng_fetch_assoc($result);
  tng_free_result($result);

  $row['living'] = 0;
  $row['allow_living'] = $row['allow_private'] = 1;

  $name = uiTextSnippet('repository') . ": {$row['reponame']} ($ID)";
} elseif ($enttype == 'L') {
  $typestr = "place";
  $row['living'] = 0;
  $row['allow_living'] = $row['allow_private'] = 1;
  $name = $ID;
} else {
  $typestr = "";
}


scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();
header("Content-type: text/html; charset=" . $session_charset);

$headTitle = ($enttype) ? uiTextSnippet('suggestchange') . ": $name" : uiTextSnippet('contactus');
$headSection->setTitle($headTitle);
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php
    if ($enttype) {
      $comments = uiTextSnippet('proposedchanges');
      echo $publicHeaderSection->build();

      $photostr = showSmallPhoto($ID, $name, $row['allow_living'] && $row['allow_private'], 0, false, $row['sex']);
      echo tng_DrawHeading($photostr, $name, $years);

      echo tng_menu($enttype, "suggest", $ID);
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
    if ($message) {
      $newmessage = uiTextSnippet($message);
      if ($message == "mailnotsent") {
        $newmessage = preg_replace("/xxx/", $sowner, $newmessage);
        $newmessage = preg_replace("/yyy/", $ssendemail, $newmessage);
      }
      echo "<p><strong><font color=\"red\">$newmessage</font></strong></p>\n";
    }

    if ($enttype) {
      echo "<h4><b>$headTitle</b></h4>\n";
    }
    ?>

    <?php
    beginFormElement("tngsendmail", "post", "suggest", "suggest", "return validateForm();");
    if ($typestr) {
      ?>
      <input name="<?php echo $typestr; ?>ID" type='hidden' value="<?php echo $ID; ?>"/>
      <?php
    }
    ?>
      <table class='table table-sm'>
        <tr>
          <td width="20%"><?php echo uiTextSnippet('yourname'); ?>:</td>
          <td width="80%">
            <input class='longfield' name="<?php echo $_SESSION['tng_yourname']; ?>" type='text'>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('email'); ?>:</td>
          <td>
            <input class='longfield' name="<?php echo $_SESSION['tng_email']; ?>" type='text' value="<?php echo $preemail; ?>"> 
            <input name='mailme' type='checkbox' value='1'/><?php echo uiTextSnippet('mailme'); ?>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('emailagain'); ?>:</td>
          <td>
            <input class='longfield' name='em2' type='text' value="<?php echo $preemail; ?>"/>
          </td>
        </tr>
        <?php
        if ($page) { ?>
          <tr>
            <td><?php echo uiTextSnippet('subject'); ?>:</td>
            <td><?php echo stripslashes($page); ?></td>
          </tr>
        <?php } ?>
        <tr>
          <td><?php echo $comments; ?>:</td>
          <td>
            <textarea cols="60" rows="10" name="<?php echo $_SESSION['tng_comments']; ?>"></textarea>
          </td>
        </tr>
      </table>
      <input name='enttype' type='hidden' value="<?php echo $enttype; ?>"/>
      <input name='ID' type='hidden' value="<?php echo $ID; ?>"/>
      <input name='tree' type='hidden' value="<?php echo $tree; ?>"/>
      <input name='page' type='hidden' value="<?php echo $page; ?>"/>
      <br>
      <input type='submit' value="<?php echo $buttontext; ?>"/>
    <?php endFormElement(); ?>
      <br>
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
