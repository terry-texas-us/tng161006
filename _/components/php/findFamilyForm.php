
<form id='form1' name='form1' action='familiesBrowse.php'>
  <section class='card'>
    <div class='card-block'>
      <div class='row form-group'>
        <label for='searchstring' class='sr-only'><?php echo uiTextSnippet('searchfor'); ?></label>
        <input class='btn btn-secondary' name='searchstring' type='text' value="<?php echo $searchstring_noquotes; ?>" placeholder="<?php echo uiTextSnippet('searchfor'); ?>" autofocus>
        <input class='btn btn-secondary-outline' name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
        <input class='btn btn-warning-outline' name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>" onClick="resetFamiliesSearch();">
      </div>
    </div>
    <div class='card-block'>
      <div class='row form-group'>
        <?php include '_/components/php/treeSelectControl.php'; ?>
        <select name="spousename">
          <option value='husband'<?php if ($spousename == 'husband') {echo " selected";} ?>>
            <?php echo uiTextSnippet('husbname'); ?></option>
          <option value='wife'<?php if ($spousename == 'wife') {echo " selected";} ?>>
            <?php echo uiTextSnippet('wifename'); ?></option>
          <option value="none"<?php if ($spousename == "none") {echo " selected";} ?>>
            <?php echo uiTextSnippet('noname'); ?></option>
        </select>
        <label class='checkbox-inline'>
          <input name='living' type='checkbox' value='yes'<?php if ($living == "yes") {echo " checked";} ?>>
          <?php echo uiTextSnippet('livingonly'); ?>
        </label>
        <label class='checkbox-inline'>
          <input name='exactmatch' type='checkbox' value='yes'<?php if ($exactmatch == "yes") {echo " checked";} ?>>
          <?php echo uiTextSnippet('exactmatch'); ?>
        </label>
      </div>
    </div>
  </section> <!-- .card -->
  <input name='findfamily' type='hidden' value='1'>
  <input name='newsearch' type='hidden' value='1'>
</form>

