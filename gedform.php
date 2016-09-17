<?php
require 'tng_begin.php';

require 'personlib.php';

$result = getPersonDataPlusDates($personID);
if ($result) {
  $row = tng_fetch_assoc($result);
  $rightbranch = checkbranch($row['branch']);
  $rights = determineLivingPrivateRights($row, $rightbranch);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  $name = getName($row);
  tng_free_result($result);
}
if (!$allowGed || !$rightbranch) {
  exit;
}
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('creategedfor') . ': ' . uiTextSnippet('gedstartfrom') . " $name");
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body class='form-gedcom'>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();

    $photostr = showSmallPhoto($personID, $name, $rights['both'], 0, false, $row['sex']);
    echo tng_DrawHeading($photostr, $name, getYears($row));

    echo buildPersonMenu('gedcom', $personID);
    echo "<br>\n";

    $onSubmit = ($currentuser) ? '' : "onsubmit='return validateForm();'";
    ?>
    <form action="gedcom.php" method='get' name="gedform"<?php echo $onSubmit; ?>>
      <div class='form-container'>
        <div class='form-heading'>
          <h4><?php echo uiTextSnippet('creategedfor'); ?></h4>
        </div>
        <input name='personID' type='hidden' value="<?php echo $personID; ?>">
<!--
        <span><?php echo uiTextSnippet('gedstartfrom'); ?>: </span>
        <span><?php echo $name; ?></span>
-->
        <?php if (!$currentuser) { ?>
          <div class='form-group'>
            <label><?php echo uiTextSnippet('email'); ?>:</label>
            <input class='form-control' name='email' type='email' required>
          </div>
        <?php } ?>
        <div class='form-group'>
          <label><?php echo uiTextSnippet('producegedfrom'); ?>:</label>
          <select class='form-control' name='type'>
            <option value="<?php echo uiTextSnippet('ancestors'); ?>" selected><?php echo uiTextSnippet('ancestors'); ?></option>
            <option value="<?php echo uiTextSnippet('descendants'); ?>"><?php echo uiTextSnippet('descendants'); ?></option>
          </select>
        </div>
        <div class='form-group'>
          <label><?php echo uiTextSnippet('numgens'); ?>:</label>
          <select class='form-control' name="maxgcgen">
            <?php
            if ($maxgedcom < 1) {
              $maxgedcom = 1;
            }
            for ($i = 1; $i <= $maxgedcom; $i++) {
              echo "<option value=\"$i\">$i</option>\n";
            }
            ?>
          </select>
        </div>
        <?php if ($rights['lds']) { ?>
          <input name='lds' type='checkbox' value='yes'> <?php echo uiTextSnippet('includelds'); ?>
        <?php } ?>
        <?php
        if ($currentuser) {
          echo "<input name='email' type='hidden' value=\"$currentuserdesc\">";
        }
        ?>
        <hr>
        <button class="btn btn-primary btn-block" type="submit"><?php echo uiTextSnippet('buildged'); ?></button>
      </div>
    </form>
    <br>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
<?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
<script>
  'use strict';
  function validateForm() {
      if (document.gedform.email.value === '') {
          alert(textSnippet('enteremail'));
          return false;
      }
      return true;
  }
</script>
</body>
</html>
