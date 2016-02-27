<?php
include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if ($assignedtree || !$allow_edit) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
$query = "SELECT *, DATE_FORMAT(lastlogin,\"%d %b %Y %H:%i:%s\") as lastlogin, DATE_FORMAT(dt_registered,\"%d %b %Y %H:%i:%s\") as dt_registered_fmt, DATE_FORMAT(dt_activated,\"%d %b %Y %H:%i:%s\") as dt_activated FROM $users_table WHERE userID = \"$userID\"";
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

$revquery = "SELECT count(userID) as ucount FROM $users_table WHERE allow_living = \"-1\"";
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

<body id="users-modifyuser">
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('users-modifyuser', $message);
    $navList = new navList('');
    $navList->appendItem([true, "admin_users.php", uiTextSnippet('search'), "finduser"]);
    $navList->appendItem([$allow_add, "admin_newuser.php", uiTextSnippet('addnew'), "adduser"]);
    $navList->appendItem([$allow_edit, "admin_reviewusers.php", uiTextSnippet('review') . $revstar, "review"]);
    $navList->appendItem([true, "admin_mailusers.php", uiTextSnippet('email'), "mail"]);
    $navList->appendItem([true, "#", uiTextSnippet('edit'), "edit"]);
    echo $navList->build("edit");
    ?>
    <table class="table table-sm">
      <tr>
        <td>
          <form action="admin_updateuser.php" method='post' name="form1" onSubmit="return validateForm();">
            <table class='table table-sm'>
              <tr>
                <td><?php echo uiTextSnippet('description'); ?>:</td>
                <td><input name='description' type='text' size='50' maxlength='50' value="<?php echo $row['description']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('username'); ?>:</td>
                <td>
                  <input name='username' type='text' maxlength='100' value="<?php echo $row['username']; ?>">
                  <input type='button' value="<?php echo uiTextSnippet('check'); ?>"
                         onclick="checkNewUser(document.form1.username, document.form1.orguser);">
                  <span id="checkmsg"></span>
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('password'); ?>:</td>
                <td><input name='password' type='password' maxlength='100' value="<?php echo $row['password']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('realname'); ?>:</td>
                <td><input name='realname' type='text' size='50' maxlength='50' value="<?php echo $row['realname']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('phone'); ?>:</td>
                <td><input name='phone' type='text' size='30' maxlength='30' value="<?php echo $row['phone']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('email'); ?>:</td>
                <td>
                  <input name='email' type='text' size='50' maxlength='100' value="<?php echo $row['email']; ?>" 
                         onblur="checkIfUnique(this);"> 
                  <span id='emailmsg'></span>
                </td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>
                  <input name='no_email' type='checkbox' value='1'<?php if ($row['no_email']) {echo " checked";} ?>> <?php echo uiTextSnippet('no_email'); ?>
                </td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('website'); ?>:</td>
                <td><input name='website' type='text' size='50' maxlength='128' value="<?php echo $row['website']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('address'); ?>:</td>
                <td><input name='address' type='text' size='50' maxlength='100' value="<?php echo $row['address']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('city'); ?>:</td>
                <td><input name='city' type='text' size='50' maxlength='64' value="<?php echo $row['city']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('stateprov'); ?>:</td>
                <td><input name='state' type='text' size='50' maxlength='64' value="<?php echo $row['state']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('zip'); ?>:</td>
                <td><input name='zip' type='text' maxlength='10' value="<?php echo $row['zip']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('cap_country'); ?>:</td>
                <td><input name='country' type='text' size='50' maxlength='64' value="<?php echo $row['country']; ?>"></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('notes'); ?>:</td>
                <td><textarea cols="50" rows="4" name="notes"><?php echo $row['notes']; ?></textarea></td>
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
                      echo "	<option value=\"{$treerow['gedcom']}\"";
                      if ($treerow['gedcom'] == $row['mygedcom']) {
                        echo " selected";
                      }
                      echo ">{$treerow['treename']}</option>\n";
                    }
                    ?>
                  </select>
                  <input id='personID' name='personID' type='text' maxlength='22' value="<?php echo $row['personID']; ?>">
                  &nbsp;<?php echo uiTextSnippet('text_or'); ?>&nbsp;
                  <a href="#" onclick="return findItem('I', 'personID', '', document.form1.mynewgedcom.options[document.form1.mynewgedcom.selectedIndex].value, '<?php echo $assignedbranch; ?>');"
                     title="<?php echo uiTextSnippet('find'); ?>">
                    <img class='icon-sm-inline' src="svg/magnifying-glass.svg" alt="<?php echo uiTextSnippet('find'); ?>">
                  </a>
                </td>
              </tr>
              <?php if ($row['dt_registered']) { ?>
                <tr>
                  <td>
                    <?php echo uiTextSnippet('dtregistered'); ?>:
                  </td>
                  <td>
                    <?php echo $row['dt_registered_fmt']; ?>
                  </td>
                </tr>
              <?php } ?>
              <tr>
                <td><?php echo uiTextSnippet('dtactivated'); ?>:</td>
                <td><?php echo $row['dt_activated']; ?></td>
              </tr>
              <tr>
                <td><?php echo uiTextSnippet('lastlogin'); ?>:</td>
                <td><?php echo $row['lastlogin']; ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>
                  <input name='disabled' type='checkbox' value='1'<?php if ($row['disabled']) {echo " checked";} ?>> <?php echo uiTextSnippet('disabled'); ?>
                </td>
              </tr>
            </table>
            <br><br>

            <div id="rolesandrights">
              <table class='table table-sm'>
                <tr>
                  <td>
                    <p><strong><?php echo uiTextSnippet('roles'); ?>:</strong></p>

                    <p><input name='role' type='radio' value="guest"<?php if ($row['role'] == "guest") {echo " checked";} ?>
                              onclick="assignRightsFromRole('guest');"/> <?php echo uiTextSnippet('usrguest') . "<br><em class=\"small indent\">" . uiTextSnippet('usrguestd') . " " . uiTextSnippet('noadmin') . "</em>"; ?>
                    </p>
                    <p><input name='role' type='radio' value="subm"<?php if ($row['role'] == "subm") {echo " checked";} ?>
                              onclick="assignRightsFromRole('subm');"/> <?php echo uiTextSnippet('usrsubm') . "<br><em class=\"small indent\">" . uiTextSnippet('usrsubmd') . " " . uiTextSnippet('noadmin') . "</em>"; ?>
                    </p>
                    <p><input name='role' type='radio' value="contrib"<?php if ($row['role'] == "contrib") {echo " checked";} ?>
                              onclick="assignRightsFromRole('contrib');"/> <?php echo uiTextSnippet('usrcontrib') . "<br><em class=\"small indent\">" . uiTextSnippet('usrcontribd') . "</em>"; ?>
                    </p>
                    <p><input name='role' type='radio' value="editor"<?php if ($row['role'] == "editor") {echo " checked";} ?>
                              onclick="assignRightsFromRole('editor');"/> <?php echo uiTextSnippet('usreditor') . "<br><em class=\"small indent\">" . uiTextSnippet('usreditord') . "</em>"; ?>
                    </p>
                    <p><input name='role' type='radio' value="mcontrib"<?php if ($row['role'] == "mcontrib") {echo " checked";} ?>
                              onclick="assignRightsFromRole('mcontrib');"/> <?php echo uiTextSnippet('usrmcontrib') . "<br><em class=\"small indent\">" . uiTextSnippet('usrmcontribd') . "</em>"; ?>
                    </p>
                    <p><input name='role' type='radio' value="meditor"<?php if ($row['role'] == "meditor") {echo " checked";} ?>
                              onclick="assignRightsFromRole('meditor');"/> <?php echo uiTextSnippet('usrmeditor') . "<br><em class=\"small indent\">" . uiTextSnippet('usrmeditord') . "</em>"; ?>
                    </p>
                    <p><input name='role' type='radio' value="custom"<?php if (!$row['role'] || $row['role'] == "custom") {echo " checked";} ?>
                              onclick="assignRightsFromRole('custom');"/> <?php echo uiTextSnippet('usrcustom'); ?>
                    </p>
                    <p><input name='role' type='radio' value="admin"<?php if ($row['role'] == "admin") {echo " checked";} ?>
                              onclick="assignRightsFromRole('admin');"/> <?php echo uiTextSnippet('usradmin') . "<br><em class=\"small indent\">" . uiTextSnippet('usradmind') . "</em>"; ?>
                    </p>
                  </td>
                  <td>
                    <p><strong><?php echo uiTextSnippet('rights'); ?></strong></p>

                    <p>
                      <input class='rights' name='form_allow_add' type='radio' value='1'<?php if ($row['allow_add'] == 1) {echo " checked";} ?>
                             onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('allow_add'); ?>
                      <br>
                      <input class='rights' name='form_allow_add' type='radio' value="3"<?php if ($row['allow_add'] == 3) {echo " checked";} ?>
                             onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('allow_media_add'); ?>
                      <br>
                      <input class='rights' name='form_allow_add' type='radio' value='0'<?php if (!$row['allow_add']) {echo " checked";} ?>
                             onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('no_add'); ?>
                      <br>
                    </p>

                    <p>
                      <input class='rights' name='form_allow_edit' type='radio' value='1'<?php if ($row['allow_edit'] == 1) {echo " checked";} ?>
                             onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('allow_edit'); ?>
                      <br>
                      <input class='rights' name='form_allow_edit' type='radio' value="3"<?php if ($row['allow_edit'] == 3) {echo " checked";} ?>
                             onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('allow_media_edit'); ?>
                      <br>
                      <input class='rights' name='form_allow_edit' type='radio' value="2"<?php if ($row['tentative_edit']) {echo " checked";} ?>
                             onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('tentative_edit'); ?>
                      <br>
                      <input class='rights' name='form_allow_edit' type='radio' value='0'<?php if (!$row['allow_edit'] && !$row['tentative_edit']) {echo " checked";} ?>
                             onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('no_edit'); ?>
                      <br>
                    </p>

                    <p>
                      <input class='rights' name='form_allow_delete' type='radio' value='1'<?php if ($row['allow_delete'] == 1) {echo " checked";} ?>
                             onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('allow_delete'); ?>
                      <br>
                      <input class='rights' name='form_allow_delete' type='radio' value="3"<?php if ($row['allow_delete'] == 3) {echo " checked";} ?>
                             onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('allow_media_delete'); ?>
                      <br>
                      <input class='rights' name='form_allow_delete' type='radio' value='0'<?php if (!$row['allow_delete']) {echo " checked";} ?>
                             onclick="document.form1.role[6].checked = 'checked';"/> <?php echo uiTextSnippet('no_delete'); ?>
                      <br>
                    </p>

                    <br>
                    <hr/>
                    <br>
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
                  </td>
                </tr>
              </table>
              <br><br>

              <?php
              echo "<strong>" . uiTextSnippet('accesslimits') . "</strong><br>\n";
              $adminaccess = $row['gedcom'] || $row['branch'] ? 0 : 1;
              ?>
              <input name='administrator' type='radio' value='1' <?php if ($adminaccess) {echo "checked";} ?>
                     onClick="handleAdmin('allow');"> <?php echo uiTextSnippet('allow_admin'); ?><br>
              <input name='administrator' type='radio' value='0' <?php if (!$adminaccess) {echo "checked";} ?>
                     onClick="handleAdmin('restrict');"> <?php echo uiTextSnippet('limitedrights'); ?><br>
              <div id="restrictions" <?php if ($adminaccess) {
                echo "style='visibility: hidden;'";
              } ?>>
                <table class='table table-sm'>
                  <tr>
                    <td>
                      <?php echo uiTextSnippet('tree'); ?>*:
                    </td>
                    <td>
                      <select id='gedcom' name='gedcom'>
                        <option value=''></option>
                        <?php
                        $query = "SELECT gedcom, treename FROM $trees_table ORDER BY treename";
                        $treeresult = tng_query($query);

                        while ($treerow = tng_fetch_assoc($treeresult)) {
                          echo "	<option value=\"{$treerow['gedcom']}\"";
                          if ($row['gedcom'] == $treerow['gedcom']) {
                            echo " selected";
                          }
                          echo ">{$treerow['treename']}</option>\n";
                        }
                        ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php echo uiTextSnippet('branch'); ?>**:
                    </td>
                    <td>
                      <?php
                      $query = "SELECT branch, gedcom, description FROM $branches_table WHERE gedcom = \"{$row['gedcom']}\" ORDER BY description";
                      $branchresult = tng_query($query);

                      echo "<select id='branch' name=\"branch\" size=\"$selectnum\">\n";
                      echo "	<option value=''>" . uiTextSnippet('allbranches') . "</option>\n";
                      while ($branch = tng_fetch_assoc($branchresult)) {
                        echo "	<option value=\"{$branch['branch']}\"";
                        if ($row['branch'] == $branch['branch']) {
                          echo " selected";
                        }
                        echo ">{$branch['description']}</option>\n";
                      }
                      echo "</select>\n";
                      ?>
                    </td>
                  </tr>
                </table>
              </div>
              <br>
              <?php
              if ($row['allow_living'] == -1) { //account is inactive
                echo "<input name='notify' type='checkbox' value='1' checked onClick=\"replaceText();\"> " . uiTextSnippet('notify') . "<br>\n";
                $owner = $sitename ? $sitename : $dbowner;
                echo "<textarea name='welcome' rows='5' cols='50'>" . uiTextSnippet('hello') . " {$row['realname']},\r\n\r\n" . uiTextSnippet('activated');
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
              <input name='submit' type='submit' value="<?php echo uiTextSnippet('savechanges'); ?>">
            </div>
          </form>
          <p style="font-size: 8pt;">
            <?php
            echo "*" . uiTextSnippet('treemsg') . "<br>\n";
            echo "**" . uiTextSnippet('branchmsg') . "<br>\n";
            ?>
          </p>
        </td>
      </tr>
    </table>
    <?php echo $adminFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'admin'); ?>
  <script src="js/selectutils.js"></script>
  <script src="js/users.js"></script>
  <script>
    <?php include("branchlibjs.php"); ?>

    $('#gedcom').on('change', function () {
      var tree = getTree(this);
      if (!tree) {
        tree = 'none';
      }
      <?php echo $swapbranches; ?>
     });
    
    function validateForm() {
      var rval = true;
      if (document.form1.username.value.length === 0) {
        alert(textSnippet('enterusername'));
        document.form1.username.focus();
        rval = false;
      } else if (document.form1.password.value.length === 0) {
        alert(textSnippet('enterpassword'));
        document.form1.password.focus();
        rval = false;
      } else if (form.email.value.length !== 0 && !checkEmail(form.email.value)) {
        alert(textSnippet('enteremail'));
        form.email.focus();
        rval = false;
      } else if (document.form1.administrator[1].checked && document.form1.gedcom.selectedIndex < 1) {
        alert(textSnippet('selecttree'));
        document.form1.gedcom.focus();
        rval = false;
      }
      return rval;
    }

    var orgrealname = "<?php echo $row['realname']; ?>";
    var orgusername = "<?php echo $row['username']; ?>";
    var orgpassword = "<?php echo $row['password']; ?>";
  </script>
</body>
</html>
