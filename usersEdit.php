<?php
require 'begin.php';
require 'adminlib.php';

$adminLogin = 1;
require 'checklogin.php';
require 'version.php';

if (!$allowEdit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$query = "SELECT *, DATE_FORMAT(lastlogin,\"%d %b %Y %H:%i:%s\") AS lastlogin, DATE_FORMAT(dt_registered,\"%d %b %Y %H:%i:%s\") AS dt_registered_fmt, DATE_FORMAT(dt_activated,\"%d %b %Y %H:%i:%s\") AS dt_activated FROM $users_table WHERE userID = \"$userID\"";
$result = tng_query($query);
$row = tng_fetch_assoc($result);
tng_free_result($result);
$row['description'] = preg_replace("/\"/", "&#34;", $row['description']);
$row['realname'] = preg_replace("/\"/", "&#34;", $row['realname']);
$row['phone'] = preg_replace("/\"/", "&#34;", $row['phone']);
$row['email'] = preg_replace("/\"/", "&#34;", $row['email']);
$row['website'] = preg_replace("/\"/", "&#34;", $row['website']);
$row['address'] = preg_replace("/\"/", "&#34;", $row['address']);
$row['city'] = preg_replace("/\"/", "&#34;", $row['city']);
$row['state'] = preg_replace("/\"/", "&#34;", $row['state']);
$row['country'] = preg_replace("/\"/", "&#34;", $row['country']);
$row['notes'] = preg_replace("/\"/", "&#34;", $row['notes']);

$revquery = "SELECT count(userID) AS ucount FROM $users_table WHERE allow_living = \"-1\"";
$revresult = tng_query($revquery) or die(uiTextSnippet('cannotexecutequery') . ": $revquery");
$revrow = tng_fetch_assoc($revresult);
$revstar = $revrow['ucount'] ? " *" : "";
tng_free_result($revresult);

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('modifyuser'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('users-edit', $message);
    $navList = new navList('');
    $navList->appendItem([true, "usersBrowse.php", uiTextSnippet('search'), "finduser"]);
    $navList->appendItem([$allowAdd, "usersAdd.php", uiTextSnippet('add'), "adduser"]);
    $navList->appendItem([$allowEdit, "usersReview.php", uiTextSnippet('review') . $revstar, "review"]);
    $navList->appendItem([true, "usersSendMail.php", uiTextSnippet('email'), "mail"]);
    //    $navList->appendItem([true, "#", uiTextSnippet('edit'), "edit"]);
    echo $navList->build("edit");
    ?>
    <form id='users-edit' name='form1' action='usersEditFormAction.php' method='post'>
      <div class='row'>
        <div class='col-md-6'>
          <?php $label = uiTextSnippet('description'); ?>
          <label class='sr-only' for='description'><?php echo $label; ?></label>
          <input class='form-control' name='description' type='text' maxlength='50' value="<?php echo $row['description']; ?>" placeholder='<?php echo $label; ?>' required>

          <?php $label = uiTextSnippet('username'); ?>
          <label class='sr-only' for='username'><?php echo $label; ?></label>
          <input class='form-control' name='username' type='text' maxlength='100' value="<?php echo $row['username']; ?>" placeholder='<?php echo $label; ?>' required>
          <span id='checkmsg' role='alert'></span>

          <?php $label = uiTextSnippet('password'); ?>
          <label class='sr-only' for='password'><?php echo $label; ?></label>
          <input class='form-control' name='password' type='password' maxlength='100' value="<?php echo $row['password']; ?>" placeholder='<?php echo $label; ?>' required>
          <?php $label = uiTextSnippet('realname'); ?>
          <label class='sr-only' for='realname'><?php echo $label; ?></label>
          <input class='form-control' name='realname' type='text' maxlength='50' value="<?php echo $row['realname']; ?>" placeholder='<?php echo $label; ?>'>

          <?php $label = uiTextSnippet('phone'); ?>
          <label class='sr-only' for='phone'><?php echo $label; ?></label>
          <input class='form-control' name='phone' type='text' maxlength='30' value="<?php echo $row['phone']; ?>" placeholder='<?php echo $label; ?>'>
          <?php $label = uiTextSnippet('email'); ?>
          <label class='sr-only' for='email'><?php echo $label; ?></label>
          <input class='form-control' name='email' type='email' maxlength='100' value="<?php echo $row['email']; ?>" placeholder='<?php echo $label; ?>'> 
          <div id='emailmsg'></div>
          <div class='checkbox'>
            <label>
              <input name='no_email' type='checkbox' value='1'<?php if ($row['no_email']) {echo " checked";} ?>> <?php echo uiTextSnippet('no_email'); ?>
            </label>
          </div>
        </div>
        <div class='col-md-6'>
          <?php $label = uiTextSnippet('website'); ?>
          <label class='sr-only' for='website'><?php echo $label; ?></label>
          <input class='form-control' name='website' type='url' maxlength='128' value="<?php echo $row['website']; ?>" placeholder='<?php echo $label; ?>'>
          <?php $label = uiTextSnippet('address'); ?>
          <label class='sr-only' for='address'><?php echo $label; ?></label>
          <input class='form-control' name='address' type='text' maxlength='100' value="<?php echo $row['address']; ?>" placeholder='<?php echo $label; ?>'>
          <?php $label = uiTextSnippet('city'); ?>
          <label class='sr-only' for='city'><?php echo $label; ?></label>
          <input class='form-control' name='city' type='text' maxlength='64' value="<?php echo $row['city']; ?>" placeholder='<?php echo $label; ?>'>
          <?php $label = uiTextSnippet('stateprov'); ?>
          <label class='sr-only' for='state'><?php echo $label; ?></label>
          <input class='form-control' name='state' type='text' maxlength='64' value="<?php echo $row['state']; ?>" placeholder='<?php echo $label; ?>'>
          <?php $label = uiTextSnippet('zip'); ?>
          <label class='sr-only' for='zip'><?php echo $label; ?></label>
          <input class='form-control' name='zip' type='text' maxlength='10' value="<?php echo $row['zip']; ?>" placeholder='<?php echo $label; ?>'>
          <?php $label = uiTextSnippet('country'); ?>
          <label class='sr-only' for='country'><?php echo $label; ?></label>
          <input class='form-control' name='country' type='text' maxlength='64' value="<?php echo $row['country']; ?>" placeholder='<?php echo $label; ?>'>
        </div>
      </div>
      <div class='row'>
        <div class='col-md-12'><?php echo uiTextSnippet('notes'); ?></div>
      </div>
      <div class='row'>
        <div class='col-md-12'>
          <textarea class='form-control' name='notes' rows='4'><?php echo $row['notes']; ?></textarea>
        </div>
      </div>
      <div class='row'>
        <div class='col-sm-6'>
          <input class='form-control' id='personID' name='personID' type='text' maxlength='22' value="<?php echo $row['personID']; ?>">
          &nbsp;<?php echo uiTextSnippet('or'); ?>&nbsp;
          <a id='findPerson' href="#" title="<?php echo uiTextSnippet('find'); ?>" data-assigned-branch='<?php echo $assignedbranch; ?>'>
            <img class='icon-sm-inline' src='svg/magnifying-glass.svg' alt="<?php echo uiTextSnippet('find'); ?>">
          </a>
        </div>
      </div>
      <?php if ($row['dt_registered']) { ?>
        <div class='row'>
          <div class='col-sm-3'>
            <?php echo uiTextSnippet('dtregistered'); ?>:
          </div>
          <div class='col-sm-3'>
            <?php echo $row['dt_registered_fmt']; ?>
          </div>
          <div class='col-sm-3'><?php echo uiTextSnippet('dtactivated'); ?>:</div>
          <div class='col-sm-3'><?php echo $row['dt_activated']; ?></div>
        </div>
      <?php } ?>
      <div class='row'>
        <div class='col-sm-6 checkbox'>
          <label>
            <input name='disabled' type='checkbox' value='1'<?php if ($row['disabled']) {echo " checked";} ?>> <?php echo uiTextSnippet('disabled'); ?>
          </label>
        </div>
        <div class='col-sm-3'>
          <?php echo uiTextSnippet('lastlogin'); ?>:
        </div>
        <div class='col-sm-3'>
          <?php echo $row['lastlogin']; ?>
        </div>
      </div>
      <hr>
      
      <div class='row'>
        <div class='col-md-6'>
          <p><strong><?php echo uiTextSnippet('roles'); ?>:</strong></p>

          <p>
            <input name='role' type='radio' value="guest"<?php if ($row['role'] == "guest") {echo " checked";} ?> data-role='guest'>
            <?php echo uiTextSnippet('usrguest') . "<br><em class='small indent'>" . uiTextSnippet('usrguestd') . " " . uiTextSnippet('noadmin') . "</em>"; ?>
          </p>
          <p>
            <input name='role' type='radio' value="subm"<?php if ($row['role'] == "subm") {echo " checked";} ?> data-role='subm'>
            <?php echo uiTextSnippet('usrsubm') . "<br><em class='small indent'>" . uiTextSnippet('usrsubmd') . " " . uiTextSnippet('noadmin') . "</em>"; ?>
          </p>
          <p>
            <input name='role' type='radio' value="contrib"<?php if ($row['role'] == "contrib") {echo " checked";} ?> data-role='contrib'>
            <?php echo uiTextSnippet('usrcontrib') . "<br><em class='small indent'>" . uiTextSnippet('usrcontribd') . "</em>"; ?>
          </p>
          <p>
            <input name='role' type='radio' value="editor"<?php if ($row['role'] == "editor") {echo " checked";} ?> data-role='editor'>
            <?php echo uiTextSnippet('usreditor') . "<br><em class='small indent'>" . uiTextSnippet('usreditord') . "</em>"; ?>
          </p>
          <p>
            <input name='role' type='radio' value="mcontrib"<?php if ($row['role'] == "mcontrib") {echo " checked";} ?> data-role='mcontrib'>
            <?php echo uiTextSnippet('usrmcontrib') . "<br><em class='small indent'>" . uiTextSnippet('usrmcontribd') . "</em>"; ?>
          </p>
          <p>
            <input name='role' type='radio' value="meditor"<?php if ($row['role'] == "meditor") {echo " checked";} ?> data-role='meditor'>
            <?php echo uiTextSnippet('usrmeditor') . "<br><em class='small indent'>" . uiTextSnippet('usrmeditord') . "</em>"; ?>
          </p>
          <p>
            <input name='role' type='radio' value="custom"<?php if (!$row['role'] || $row['role'] == "custom") {echo " checked";} ?> data-role='custom'>
            <?php echo uiTextSnippet('usrcustom'); ?>
          </p>
          <p>
            <input name='role' type='radio' value="admin"<?php if ($row['role'] == "admin") {echo " checked";} ?> data-role='admin'>
            <?php echo uiTextSnippet('usradmin') . "<br><em class='small indent'>" . uiTextSnippet('usradmind') . "</em>"; ?>
          </p>
        </div>
        <div class='col-md-6'>
          <p><strong><?php echo uiTextSnippet('rights'); ?></strong></p>
          <p>
            <input class='rights' name='form_allow_add' type='radio' value='1'<?php if ($row['allow_add'] == 1) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_add'); ?>
            <br>
            <input class='rights' name='form_allow_add' type='radio' value="3"<?php if ($row['allow_add'] == 3) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_media_add'); ?>
            <br>
            <input class='rights' name='form_allow_add' type='radio' value='0'<?php if (!$row['allow_add']) {echo " checked";} ?>> <?php echo uiTextSnippet('no_add'); ?>
            <br>
          </p>
          <p>
            <input class='rights' name='form_allow_edit' type='radio' value='1'<?php if ($row['allow_edit'] == 1) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_edit'); ?>
            <br>
            <input class='rights' name='form_allow_edit' type='radio' value="3"<?php if ($row['allow_edit'] == 3) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_media_edit'); ?>
            <br>
            <input class='rights' name='form_allow_edit' type='radio' value="2"<?php if ($row['tentative_edit']) {echo " checked";} ?>> <?php echo uiTextSnippet('tentative_edit'); ?>
            <br>
            <input class='rights' name='form_allow_edit' type='radio' value='0'<?php if (!$row['allow_edit'] && !$row['tentative_edit']) {echo " checked";} ?>> <?php echo uiTextSnippet('no_edit'); ?>
            <br>
          </p>
          <p>
            <input class='rights' name='form_allow_delete' type='radio' value='1'<?php if ($row['allow_delete'] == 1) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_delete'); ?>
            <br>
            <input class='rights' name='form_allow_delete' type='radio' value="3"<?php if ($row['allow_delete'] == 3) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_media_delete'); ?>
            <br>
            <input class='rights' name='form_allow_delete' type='radio' value='0'<?php if (!$row['allow_delete']) {echo " checked";} ?>> <?php echo uiTextSnippet('no_delete'); ?>
            <br>
          </p>
          <hr>
          <p>
            <input name='form_allow_living' type='checkbox' value='1'<?php if ($row['allow_living'] > 0) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_living'); ?>
            <br>
            <input name='form_allow_private' type='checkbox' value='1'<?php if ($row['allow_private'] > 0) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_private'); ?>
            <br>
            <input name='form_allow_ged' type='checkbox' value='1'<?php if ($row['allow_ged']) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_ged'); ?>
            <br>
            <input name='form_allow_pdf' type='checkbox' value='1'<?php if ($row['allow_pdf']) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_pdf'); ?>
            <br>
            <input name='form_allow_lds' type='checkbox' value='1'<?php if ($row['allow_lds']) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_lds'); ?>
            <br>
            <input name='form_allow_profile' type='checkbox' value='1'<?php if ($row['allow_profile']) {echo " checked";} ?>> <?php echo uiTextSnippet('allow_profile'); ?>
          </p>
        </div>
      </div> 
      <hr>
      <?php
      echo "<strong>" . uiTextSnippet('accesslimits') . "</strong><br>\n";
      $adminaccess = $row['gedcom'] || $row['branch'] ? 0 : 1;
      ?>
      <input name='administrator' type='radio' value='1' <?php if ($adminaccess) {echo "checked";} ?> data-admin-access='allow'> <?php echo uiTextSnippet('allow_admin'); ?>
      <br>
      <input name='administrator' type='radio' value='0' <?php if (!$adminaccess) {echo "checked";} ?> data-admin-access='restrict'> <?php echo uiTextSnippet('limitedrights'); ?>
      <br>
      <div id="restrictions" <?php if ($adminaccess) {echo "style='visibility: hidden;'";} ?>>
        <div class='row'>
          <div class='col-sm-3'>
            <span><?php echo uiTextSnippet('branch'); ?>*:</span>
          </div>
          <div class='col-sm-3'>
            <?php
            $query = "SELECT branch, gedcom, description FROM $branches_table ORDER BY description";
            $branchresult = tng_query($query);

            echo "<select id='branch' name='branch' size='$selectnum'>\n";
            echo "  <option value=''>" . uiTextSnippet('allbranches') . "</option>\n";
            while ($branch = tng_fetch_assoc($branchresult)) {
              echo "  <option value=\"{$branch['branch']}\"";
              if ($row['branch'] == $branch['branch']) {
                echo " selected";
              }
              echo ">{$branch['description']}\n";
              echo "</option>\n";
            }
            echo "</select>\n";
            ?>
          </div>
        </div>
      </div>
      <?php if ($row['allow_living'] == -1) { // inactive user account
        echo "<input name='notify' type='checkbox' value='1' checked onClick=\"replaceText();\"> " . uiTextSnippet('notify') . "<br>\n";
        $owner = $sitename ? $sitename : $dbowner;
        echo "<textarea class='form-control' name='welcome' rows='4'>" . uiTextSnippet('hello') . " {$row['realname']},\r\n\r\n" . uiTextSnippet('activated');
        if (!$tngconfig['omitpwd']) {
          echo " " . uiTextSnippet('infois') . ":\r\n\r\n" . uiTextSnippet('username') . ": {$row['username']}\r\n" . uiTextSnippet('password') . ": {$row['password']}\r\n";
        }
        echo "\r\n$owner\r\n$tngdomain</textarea><br><br>\n";
      } else {
        echo "<input name='notify' type='hidden' value='0'>\n";
      }
      ?>
      <input name='userID' type='hidden' value="<?php echo "$userID"; ?>">
      <input name='orguser' type='hidden' value="<?php echo $row['username']; ?>"/>
      <input name='orgemail' type='hidden' value="<?php echo $row['email']; ?>"/>
      <input name='newuser' type='hidden' value="<?php echo "$newuser"; ?>">
      <input name='orgpwd' type='hidden' value="<?php echo $row['password']; ?>">
      <br>
      <button class='btn btn-primary btn-block' name='submit' type='submit'><?php echo uiTextSnippet('savechanges'); ?></button>
    </form>
    <hr>
    <p>*<?php echo uiTextSnippet('branchmsg'); ?></p>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
<script src="js/selectutils.js"></script>
<script src="js/users.js"></script>
<script>
    var orgrealname = "<?php echo $row['realname']; ?>";
    var orgusername = "<?php echo $row['username']; ?>";
    var orgpassword = "<?php echo $row['password']; ?>";

    <?php require "branchlibjs.php"; ?>

    $('#gedcom').on('change', function () {
      var tree = getTree(this);
      if (!tree) {
        tree = 'none';
      }
      <?php echo $swapbranches; ?>
     });
</script>
</body>
</html>
