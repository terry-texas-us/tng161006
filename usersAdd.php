<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowAdd) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$query = "SELECT count(userID) as ucount FROM $users_table";
$result = tng_query($query);
if ($result) {
  $row = tng_fetch_assoc($result);
} else {
  $row['ucount'] = 0;
}
$revquery = "SELECT count(userID) as ucount FROM $users_table WHERE allow_living = \"-1\"";
$revresult = tng_query($revquery) or die(uiTextSnippet('cannotexecutequery') . ": $revquery");
$revrow = tng_fetch_assoc($revresult);
$revstar = $revrow['ucount'] ? " *" : "";
tng_free_result($revresult);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('addnewuser'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('users-add', $message);
    $navList = new navList('');
    $navList->appendItem([true, "usersBrowse.php", uiTextSnippet('search'), "finduser"]);
    //    $navList->appendItem([$allowAdd, "usersAdd.php", uiTextSnippet('add'), "adduser"]);
    $navList->appendItem([$allowEdit, "usersReview.php", uiTextSnippet('review') . $revstar, "review"]);
    $navList->appendItem([true, "usersSendMail.php", uiTextSnippet('email'), "mail"]);
    echo $navList->build("adduser");
    ?>
    <form id='users-add' name='form1' action="usersAddFormAction.php" method='post'>
      <div class='row'>
        <div class='col-md-6'>
          <?php $label = uiTextSnippet('description'); ?>
          <label class='sr-only' for='description'><?php echo $label; ?></label>
          <input class='form-control' name='description' type='text' maxlength='50' placeholder='<?php echo $label; ?>' required>

          <?php $label = uiTextSnippet('username'); ?>
          <label class='sr-only' for='username'><?php echo $label; ?></label>
          <input class='form-control' name='username' type='text' maxlength="100" placeholder='<?php echo $label; ?>' required>
          <span id='checkmsg'></span>

          <?php $label = uiTextSnippet('password'); ?>
          <label class='sr-only' for='password'><?php echo $label; ?></label>
          <input class='form-control' name='password' type='password' maxlength="100" placeholder='<?php echo $label; ?>' required>
          <?php $label = uiTextSnippet('realname'); ?>
          <label class='sr-only' for='realname'><?php echo $label; ?></label>
          <input class='form-control' name='realname' type='text' maxlength='50' placeholder='<?php echo $label; ?>'>

          <?php $label = uiTextSnippet('phone'); ?>
          <label class='sr-only' for='phone'><?php echo $label; ?></label>
          <input class='form-control' name='phone' type='text' maxlength='30' placeholder='<?php echo $label; ?>'>
          <?php $label = uiTextSnippet('email'); ?>
          <label class='sr-only' for='email'><?php echo $label; ?></label>
          <input class='form-control' name='email' type='email' maxlength='100' placeholder='<?php echo $label; ?>'>
          <div id='emailmsg'></div>
          <div class='checkbox'>
            <label>
              <input name='no_email' type='checkbox' value='1'> <?php echo uiTextSnippet('no_email'); ?>
            </label>
          </div>
        </div>  
        <div class='col-md-6'>
          <?php $label = uiTextSnippet('website'); ?>
          <label class='sr-only' for='website'><?php echo $label; ?></label>
          <input class='form-control' name='website' type='url' maxlength='128' placeholder='<?php echo $label; ?>'>
          <?php $label = uiTextSnippet('address'); ?>
          <label class='sr-only' for='address'><?php echo $label; ?></label>
          <input class='form-control' name='address' type='text' maxlength='100' placeholder='<?php echo $label; ?>'>
          <?php $label = uiTextSnippet('city'); ?>
          <label class='sr-only' for='city'><?php echo $label; ?></label>
          <input class='form-control' name='city' type='text' maxlength='64' placeholder='<?php echo $label; ?>'>
          <?php $label = uiTextSnippet('stateprov'); ?>
          <label class='sr-only' for='state'><?php echo $label; ?></label>
          <input class='form-control' name='state' type='text' maxlength='64' placeholder='<?php echo $label; ?>'>
          <?php $label = uiTextSnippet('zip'); ?>
          <label class='sr-only' for='zip'><?php echo $label; ?></label>
          <input class='form-control' name='zip' type='text' maxlength='10' placeholder='<?php echo $label; ?>'>
          <?php $label = uiTextSnippet('country'); ?>
          <label class='sr-only' for='country'><?php echo $label; ?></label>
          <input class='form-control' name='country' type='text' maxlength='64' placeholder='<?php echo $label; ?>'>
        </div>
      </div>
      <div class='row'>
        <div class='col-md-12'><?php echo uiTextSnippet('notes'); ?></div>
      </div>
      <div class='row'>
        <div class='col-md-12'>
          <textarea class='form-control' name='notes' rows='4'></textarea>
        </div>
      </div>
      <div class='row'>
        <div class='col-sm-6'>
          <input class='form-control' id='personID' name='personID' type='text' maxlength='22'>
          &nbsp;<?php echo uiTextSnippet('or'); ?>&nbsp;
          <a id='findPerson' href="#" title="<?php echo uiTextSnippet('find'); ?>" data-assigned-branch='<?php echo $assignedbranch; ?>'>
            <img class='icon-sm-inline' src='svg/magnifying-glass.svg' alt="<?php echo uiTextSnippet('find'); ?>">
          </a>
        </div>
      </div>
      <div class='row'>
        <div class='col-sm-6 checkbox'>
          <label>
            <input name='disabled' type='checkbox' value='1'> <?php echo uiTextSnippet('disabled'); ?>
          </label>
        </div>
      </div>
      <hr>

      <div class='row'>
        <div class='col-md-6'>
          <p><strong><?php echo uiTextSnippet('roles'); ?>:</strong></p>

          <?php if ($row['ucount']) { ?>
            <p>
              <input name='role' type='radio' value='guest' checked data-role='guest'>
              <?php echo uiTextSnippet('usrguest') . "<br><em class='small indent'>" . uiTextSnippet('usrguestd') . " " . uiTextSnippet('noadmin') . "</em>"; ?>
            </p>
            <p>
              <input name='role' type='radio' value='subm'  data-role='subm'>
              <?php echo uiTextSnippet('usrsubm') . "<br><em class='small indent'>" . uiTextSnippet('usrsubmd') . " " . uiTextSnippet('noadmin') . "</em>"; ?>
            </p>
            <p>
              <input name='role' type='radio' value='contrib' data-role='contrib'>
              <?php echo uiTextSnippet('usrcontrib') . "<br><em class='small indent'>" . uiTextSnippet('usrcontribd') . "</em>"; ?>
            </p>
            <p>
              <input name='role' type='radio' value='editor'  data-role='editor'>
              <?php echo uiTextSnippet('usreditor') . "<br><em class='small indent'>" . uiTextSnippet('usreditord') . "</em>"; ?>
            </p>
            <p>
              <input name='role' type='radio' value='mcontrib' data-role='mcontrib'>
              <?php echo uiTextSnippet('usrmcontrib') . "<br><em class='small indent'>" . uiTextSnippet('usrmcontribd') . "</em>"; ?>
            </p>
            <p>
              <input name='role' type='radio' value='meditor'  data-role='meditor'>
              <?php echo uiTextSnippet('usrmeditor') . "<br><em class='small indent'>" . uiTextSnippet('usrmeditord') . "</em>"; ?>
            </p>
            <p>
              <input name='role' type='radio' value='custom'  data-role='custom'>
              <?php echo uiTextSnippet('usrcustom'); ?>
            </p>
          <?php } ?>
          <p>
            <input name='role' type='radio' value="admin"<?php if (!$row['ucount']) {echo " checked";} ?> data-role='admin'>
            <?php echo uiTextSnippet('usradmin') . "<br><em class='small indent'>" . uiTextSnippet('usradmind') . "</em>"; ?>
          </p>
        </div>
        <div class='col-md-6'>
          <p><strong><?php echo uiTextSnippet('rights'); ?></strong></p>
          <p>
            <input class='rights' name='form_allow_add' type='radio' value='1'<?php if (!$row['ucount']) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_add'); ?>
            <br>
            <?php if ($row['ucount']) { ?>
              <input class='rights' name='form_allow_add' type='radio' value="3"> <?php echo uiTextSnippet('allow_media_add'); ?>
              <br>
              <input class='rights' name='form_allow_add' type='radio' value='0' checked> <?php echo uiTextSnippet('no_add'); ?>
              <br>
            <?php } ?>
          </p>
          <p>
            <input class='rights' name='form_allow_edit' type='radio' value='1'<?php if (!$row['ucount']) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_edit'); ?>
            <br>
            <?php if ($row['ucount']) { ?>
              <input class='rights' name='form_allow_edit' type='radio' value='3'> <?php echo uiTextSnippet('allow_media_edit'); ?>
              <br>
              <input class='rights' name='form_allow_edit' type='radio' value='2'> <?php echo uiTextSnippet('tentative_edit'); ?>
              <br>
              <input class='rights' name='form_allow_edit' type='radio' value='0' checked> <?php echo uiTextSnippet('no_edit'); ?>
              <br>
            <?php } ?>
          </p>
          <p>
            <input class='rights' name='form_allow_delete' type='radio' value='1'<?php if (!$row['ucount']) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_delete'); ?>
            <br>
            <?php if ($row['ucount']) { ?>
              <input class='rights' name='form_allow_delete' type='radio' value='3'> <?php echo uiTextSnippet('allow_media_delete'); ?>
              <br>
              <input class='rights' name='form_allow_delete' type='radio' value='0' checked> <?php echo uiTextSnippet('no_delete'); ?>
              <br>
            <?php } ?>
          </p>
          <hr>
          <p>
            <input name='form_allow_living' type='checkbox' value='1'<?php if (!$row['ucount']) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_living'); ?>
            <br>
            <input name='form_allow_private' type='checkbox' value='1'<?php if (!$row['ucount']) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_private'); ?>
            <br>
            <input name='form_allow_ged' type='checkbox' value='1'<?php if (!$row['ucount']) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_ged'); ?>
            <br>
            <input name='form_allow_pdf' type='checkbox' value='1'<?php if (!$row['ucount']) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_pdf'); ?>
            <br>
            <input name='form_allow_lds' type='checkbox' value='1'<?php if (!$row['ucount']) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_lds'); ?>
            <br>
            <input name='form_allow_profile' type='checkbox' value='1'<?php if (!$row['ucount']) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_profile'); ?>
          </p>
        </div>
      </div>
      <hr>
      <?php
      if ($row['ucount']) {
        echo "<strong>" . uiTextSnippet('accesslimits') . "</strong><br>\n";
        ?>
        <input name='administrator' type='radio' value='1' data-admin-access='allow'> <?php echo uiTextSnippet('allow_admin'); ?>
        <br>
        <input name='administrator' type='radio' value='0' checked data-admin-access='restrict'> <?php echo uiTextSnippet('limitedrights'); ?>
        <br>
        <div id='restrictions'>
          <div class='row'>
            <div class='col-sm-3'>
            </div>
            <div class='col-sm-3'>
            </div>
            <div class='col-sm-3'>
              <span><?php echo uiTextSnippet('branch'); ?>*:</span>
            </div>
            <div class='col-sm-3'>
              <?php
              $query = "SELECT branch, description FROM $branches_table ORDER BY description";
              $branchresult = tng_query($query);

              echo "<select id='branch' name=\"branch\">\n";
              echo "  <option value='' selected>" . uiTextSnippet('allbranches') . "</option>\n";
              echo "</select>\n";
              ?>
            </div>
          </div>
        </div>
        <?php
      } else {
        echo "<b>" . uiTextSnippet('firstuser') . "</b>\n";
        echo "<input name='branch' type='hidden' value=''>";
      }
      ?>
      <br>
      <input name='notify' type='checkbox' value='1'>
      <?php echo uiTextSnippet('notify'); ?>
      <br>
      <textarea class='form-control' name='welcome' rows='4' style="display: none">
        <?php
        echo uiTextSnippet('hello') . " xxx,\r\n\r\n" . uiTextSnippet('activated') . " " . uiTextSnippet('infois') . ":\r\n\r\n" .
             uiTextSnippet('username') . ": yyy\r\n" . uiTextSnippet('password') . ": zzz\r\n\r\n$dbowner\r\n$tngdomain"; 
        ?>
      </textarea>
      <br>
      <button class='btn btn-primary btn-block' name='submit' type='submit'><?php echo uiTextSnippet('save'); ?></button>
    </form>
    <hr>
    <p>*<?php echo uiTextSnippet('branchmsg'); ?></p>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/selectutils.js"></script>
<script src="js/users.js"></script>
<script>
  var orgrealname = "xxx";
  var orgusername = "yyy";
  var orgpassword = "zzz";

  <?php include("branchlibjs.php"); ?>
  <?php if ($row['ucount']) { ?>
    var tree = getTree();
    if (tree) {
      <?php echo $swapbranches; ?>
    }
  <?php } ?>
      
  $('#gedcom').on('change', function () {
    var tree = getTree();
    if (!tree) {
      tree = 'none';
    }
    <?php echo $swapbranches; ?>
  });
</script>
</body>
</html>
