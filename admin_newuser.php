<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if ($assignedtree || !$allow_add) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}

$query = "SELECT count(userID) as ucount FROM $users_table";
$result = @tng_query($query);
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
<body id="users-addnewuser">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('users-addnewuser', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_users.php", uiTextSnippet('search'), "finduser"]);
    $navList->appendItem([$allow_add, "admin_newuser.php", uiTextSnippet('addnew'), "adduser"]);
    $navList->appendItem([$allow_edit, "admin_reviewusers.php", uiTextSnippet('review') . $revstar, "review"]);
    $navList->appendItem([true, "admin_mailusers.php", uiTextSnippet('email'), "mail"]);
    echo $navList->build("adduser");
    ?>
    <form action="admin_adduser.php" method='post' name="form1" onSubmit="return validateForm(this);">
      <table class='table table-sm'>
        <tr>
          <td><?php echo uiTextSnippet('description'); ?>:</td>
          <td><input name='description' type='text' size='50' maxlength='50' required></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('username'); ?>:</td>
          <td><input name='username' type='text' maxlength="100"
                     onblur="checkNewUser(this, null);"><span id="checkmsg"></span>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('password'); ?>:</td>
          <td><input name='password' type='password' maxlength="100"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('realname'); ?>:</td>
          <td><input name='realname' type='text' size='50' maxlength='50'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('phone'); ?>:</td>
          <td><input name='phone' type='text' size='30' maxlength='30'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('email'); ?>:</td>
          <td><input name='email' type='text' size='50' maxlength='100' onblur="checkIfUnique(this);">
            <span id="emailmsg"></span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td><input name='no_email' type='checkbox' value='1'> <?php echo uiTextSnippet('no_email'); ?></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('website'); ?>:</td>
          <td><input name='website' type='text' size='50' maxlength='128' value="http://"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('address'); ?>:</td>
          <td><input name='address' type='text' size='50' maxlength='100'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('city'); ?>:</td>
          <td><input name='city' type='text' size='50' maxlength='64'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('stateprov'); ?>:</td>
          <td><input name='state' type='text' size='50' maxlength='64'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('zip'); ?>:</td>
          <td><input name='zip' type='text' maxlength="10"></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('cap_country'); ?>:</td>
          <td><input name='country' type='text' size='50' maxlength='64'></td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('notes'); ?>:</td>
          <td><textarea cols="50" rows="4" name="notes"></textarea></td>
        </tr>
        <tr>
          <td>
            <?php echo uiTextSnippet('tree'); ?> / <?php echo uiTextSnippet('personid'); ?>:
          </td>
          <td>
            <select name="mynewgedcom">
              <option value=''></option>
              <?php
              $query = "SELECT gedcom, treename FROM $trees_table ORDER BY treename";
              $treeresult = tng_query($query);

              while ($treerow = tng_fetch_assoc($treeresult)) {
                echo "  <option value=\"{$treerow['gedcom']}\">{$treerow['treename']}</option>\n";
              }
              ?>
            </select>
            <input id='personID' name='personID' type='text' maxlength='22'>
            &nbsp;<?php echo uiTextSnippet('text_or'); ?>&nbsp;
            <a href="#" onclick="return findItem('I', 'personID', '', document.form1.mynewgedcom.options[document.form1.mynewgedcom.selectedIndex].value, '<?php echo $assignedbranch; ?>');"
               title="<?php echo uiTextSnippet('find'); ?>">
              <img class='icon-sm-inline' src="svg/magnifying-glass.svg" alt="<?php echo uiTextSnippet('find'); ?>">
            </a>
          </td>
        </tr>
        <tr>
          <td></td>
          <td><input name='disabled' type='checkbox' value='1' /> <?php echo uiTextSnippet('disabled'); ?></td>
        </tr>
      </table>
      <br><br>
      <div>
        <table class='table table-sm'>
          <tr>
            <td>
              <p><strong><?php echo uiTextSnippet('roles'); ?>:</strong></p>

              <?php
              if ($row['ucount']) {
                ?>
                <p>
                  <input name='role' type='radio' value='guest' checked
                         onclick="assignRightsFromRole('guest');" />
                  <?php echo uiTextSnippet('usrguest') . "<br><em class='small indent'>" . uiTextSnippet('usrguestd') . " " . uiTextSnippet('noadmin') . "</em>"; ?>
                </p>
                <p>
                  <input name='role' type='radio' value='subm'
                         onclick="assignRightsFromRole('subm');" />
                  <?php echo uiTextSnippet('usrsubm') . "<br><em class='small indent'>" . uiTextSnippet('usrsubmd') . " " . uiTextSnippet('noadmin') . "</em>"; ?>
                </p>
                <p>
                  <input name='role' type='radio' value='contrib'
                         onclick="assignRightsFromRole('contrib');" />
                  <?php echo uiTextSnippet('usrcontrib') . "<br><em class='small indent'>" . uiTextSnippet('usrcontribd') . "</em>"; ?>
                </p>
                <p>
                  <input name='role' type='radio' value='editor' 
                         onclick="assignRightsFromRole('editor');" />
                  <?php echo uiTextSnippet('usreditor') . "<br><em class='small indent'>" . uiTextSnippet('usreditord') . "</em>"; ?>
                </p>
                <p>
                  <input name='role' type='radio' value='mcontrib'
                         onclick="assignRightsFromRole('mcontrib');" />
                  <?php echo uiTextSnippet('usrmcontrib') . "<br><em class='small indent'>" . uiTextSnippet('usrmcontribd') . "</em>"; ?>
                </p>
                <p>
                  <input name='role' type='radio' value='meditor'
                         onclick="assignRightsFromRole('meditor');" />
                  <?php echo uiTextSnippet('usrmeditor') . "<br><em class='small indent'>" . uiTextSnippet('usrmeditord') . "</em>"; ?>
                </p>
                <p>
                  <input name='role' type='radio' value='custom'
                         onclick="assignRightsFromRole('custom');" />
                  <?php echo uiTextSnippet('usrcustom'); ?>
                </p>
                <?php
              }
              ?>
              <p>
                <input name='role' type='radio' value="admin"<?php if (!$row['ucount']) {echo " checked";} ?>
                       onclick="assignRightsFromRole('admin');"/>
                <?php echo uiTextSnippet('usradmin') . "<br><em class='small indent'>" . uiTextSnippet('usradmind') . "</em>"; ?>
              </p>
            </td>
            <td>
              <p><strong><?php echo uiTextSnippet('rights'); ?></strong></p>
              <p>
                <input class='rights' name='form_allow_add' type='radio' value='1'<?php if (!$row['ucount']) {echo " checked";} ?>
                       onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('allow_add'); ?>
                <br>
                <?php
                if ($row['ucount']) {
                  ?>
                  <input class='rights' name='form_allow_add' type='radio' value="3"
                         onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('allow_media_add'); ?>
                  <br>
                  <input class='rights' name='form_allow_add' type='radio' value='0'
                         onclick="document.form1.role[6].checked = 'checked';"
                         checked> <?php echo uiTextSnippet('no_add'); ?><br>
                  <?php
                }
                ?>
              </p>

              <p>
                <input class='rights' name='form_allow_edit' type='radio' value='1'<?php if (!$row['ucount']) {echo " checked";} ?>
                       onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('allow_edit'); ?>
                <br>
                <?php
                if ($row['ucount']) {
                  ?>
                  <input class='rights' name='form_allow_edit' type='radio' value="3"
                         onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('allow_media_edit'); ?>
                  <br>
                  <input class='rights' name='form_allow_edit' type='radio' value="2"
                         onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('tentative_edit'); ?>
                  <br>
                  <input class='rights' name='form_allow_edit' type='radio' value='0'
                         onclick="document.form1.role[6].checked = 'checked';"
                         checked> <?php echo uiTextSnippet('no_edit'); ?><br>
                  <?php
                }
                ?>
              </p>

              <p>
                <input class='rights' name='form_allow_delete' type='radio' value='1'<?php if (!$row['ucount']) {echo " checked";} ?>
                       onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('allow_delete'); ?>
                <br>
                <?php
                if ($row['ucount']) {
                  ?>
                  <input class='rights' name='form_allow_delete' type='radio' value="3"
                         onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('allow_media_delete'); ?>
                  <br>
                  <input class='rights' name='form_allow_delete' type='radio' value='0'
                         onclick="document.form1.role[6].checked = 'checked';"
                         checked> <?php echo uiTextSnippet('no_delete'); ?><br>
                  <?php
                }
                ?>
              </p>

              <br>
              <hr/>
              <br>
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
            </td>
          </tr>
        </table>
      </div>
      <br><br>

      <?php
      if ($row['ucount']) {
        echo "<strong>" . uiTextSnippet('accesslimits') . "</strong><br>\n";
        ?>
        <input name='administrator' type='radio' value='1'
               onclick="handleAdmin('allow');"> <?php echo uiTextSnippet('allow_admin'); ?><br>
        <input name='administrator' type='radio' value='0' checked
               onclick="handleAdmin('restrict');"> <?php echo uiTextSnippet('limitedrights'); ?><br>
        <div id='restrictions'>
          <table class='table table-sm'>
            <tr>
              <td>
                <span><?php echo uiTextSnippet('tree'); ?>*:</span></td>
              <td>
                <select id='gedcom' name='gedcom'>
                  <option value=''></option>
                  <?php
                  $query = "SELECT gedcom, treename FROM $trees_table ORDER BY treename";
                  $treeresult = tng_query($query);

                  while ($treerow = tng_fetch_assoc($treeresult)) {
                    echo "  <option value=\"{$treerow['gedcom']}\">{$treerow['treename']}</option>\n";
                  }
                  ?>
                </select>
              </td>
            </tr>
            <tr>
              <td><span><?php echo uiTextSnippet('branch'); ?>**:</span></td>
              <td>
                <?php
                $query = "SELECT branch, gedcom, description FROM $branches_table WHERE gedcom = \"{$row['gedcom']}\" ORDER BY description";
                $branchresult = tng_query($query);

                echo "<select id='branch' name=\"branch\">\n";
                echo "  <option value='' selected>" . uiTextSnippet('allbranches') . "</option>\n";
                if ($assignedtree) {
                  while ($branch = tng_fetch_assoc($branchresult)) {
                    echo "  <option value=\"{$branch['branch']}\">{$branch['description']}</option>\n";
                  }
                }
                echo "</select>\n";
                ?>
              </td>
            </tr>
          </table>
        </div>
        <?php
      } else {
        echo "<b>" . uiTextSnippet('firstuser') . "</b>\n";
        echo "<input name='gedcom' type='hidden' value=''>\n";
        echo "<input name='branch' type='hidden' value=''>";
      }
      ?>
      <br>
      <input name='notify' type='checkbox' value='1'
             onClick="replaceText();">
      <?php echo uiTextSnippet('notify'); ?><br>
      <textarea name='welcome' rows='5' cols='50' style="display: none">
        <?php
        echo uiTextSnippet('hello') . " xxx,\r\n\r\n" . uiTextSnippet('activated') . " " . uiTextSnippet('infois') . ":\r\n\r\n" .
             uiTextSnippet('username') . ": yyy\r\n" . uiTextSnippet('password') . ": zzz\r\n\r\n$dbowner\r\n$tngdomain"; 
        ?>
      </textarea><br><br>
      <input name='submit' type='submit' value="<?php echo uiTextSnippet('save'); ?>">
    </form>
    <br>
    <p>
      <?php
      echo "*" . uiTextSnippet('treemsg') . "<br>\n";
      echo "**" . uiTextSnippet('branchmsg') . "<br>\n";
      ?>
    </p>
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

  function validateForm(form) {
    var rval = true;
    if (form.description.value.length === 0) {
      alert(textSnippet('enteruserdesc'));
      form.description.focus();
      rval = false;
    } else if (form.username.value.length === 0) {
      alert(textSnippet('enterusername'));
      form.username.focus();
      rval = false;
    } else if (form.password.value.length === 0) {
      alert(textSnippet('enterpassword'));
      form.password.focus();
      rval = false;
    } else if (form.email.value.length !== 0 && !checkEmail(form.email.value)) {
      alert(textSnippet('enteremail'));
      form.email.focus();
      rval = false;
    } else if (form.administrator[1].checked && form.gedcom.selectedIndex < 1) {
      alert(textSnippet('selecttree'));
      form.gedcom.focus();
      rval = false;
    }
    return rval;
  }
  </script>
</body>
</html>
