<?php
include("tng_begin.php");

require 'personlib.php';

$result = getPersonDataPlusDates($tree, $personID);
if ($result) {
  $row = tng_fetch_assoc($result);
  $righttree = checktree($tree);
  $rightbranch = checkbranch($row['branch']);
  $rights = determineLivingPrivateRights($row, $righttree, $rightbranch);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  $name = getName($row);
  tng_free_result($result);
}

$treeResult = getTreeSimple($tree);
$treerow = tng_fetch_assoc($treeResult);
$disallowgedcreate = $row['disallowgedcreate'];
tng_free_result($treeResult);

if ($disallowgedcreate && (!$allow_ged || !$rightbranch)) {
  exit;
}
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('creategedfor') . ": " . uiTextSnippet('gedstartfrom') . " $name");
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();

    $photostr = showSmallPhoto($personID, $name, $rights['both'], 0, false, $row['sex']);
    echo tng_DrawHeading($photostr, $name, getYears($row));

    echo buildPersonMenu("gedcom", $personID);
    echo "<br>\n";

    echo "<h4>" . uiTextSnippet('creategedfor') . "</h4>\n";

    if ($currentuser) {
      beginFormElement('gedcom', 'GET', 'gedform');
    } else {
      beginFormElement("gedcom", "GET\" onSubmit=\"return validateForm();", "gedform");
    }
    ?>
    <input name='personID' type='hidden' value="<?php echo $personID; ?>">
    <input name='tree' type='hidden' value="<?php echo $tree; ?>">
    <table class="table table-sm">
      <tr>
        <td>
          <span><?php echo uiTextSnippet('gedstartfrom'); ?>:&nbsp; </span>
        </td>
        <td>
          <span><?php echo $name; ?></span>
        </td>
      </tr>
      <?php if (!$currentuser) { ?>
        <tr>
          <td><?php echo uiTextSnippet('email'); ?>:</td>
          <td><input name='email' type='text'></td>
        </tr>
      <?php } ?>
      <tr>
        <td><?php echo uiTextSnippet('producegedfrom'); ?>:</td>
        <td>
          <select name='type'>
            <option value="<?php echo uiTextSnippet('ancestors'); ?>" selected><?php echo uiTextSnippet('ancestors'); ?></option>
            <option value="<?php echo uiTextSnippet('descendants'); ?>"><?php echo uiTextSnippet('descendants'); ?></option>
          </select>
        </td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('numgens'); ?>:</td>
        <td>
          <select name="maxgcgen">
            <?php
            if ($maxgedcom < 1) {
              $maxgedcom = 1;
            }
            for ($i = 1; $i <= $maxgedcom; $i++) {
              echo "<option value=\"$i\">$i</option>\n";
            }
            ?>
          </select>
        </td>
      </tr>
      <?php if ($rights['lds']) { ?>
        <tr>
          <td></td>
          <td>
            <input name='lds' type='checkbox' value='yes'> <?php echo uiTextSnippet('includelds'); ?>
          </td>
        </tr>
      <?php } ?>
    </table>
    <?php
    if ($currentuser) {
      echo "<input name='email' type='hidden' value=\"$currentuserdesc\">";
    }
    ?>
    <br>
    <input type='submit' value="<?php echo uiTextSnippet('buildged'); ?>">
    <?php endFormElement(); ?>
    <br>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
  <script>
    'use strict';
    function validateForm() {
      if (document.gedform.email.value === "") {
      alert(textSnippet('enteremail'));
      return false;
      }
      else return true;
    }
  </script>
</body>
</html>
