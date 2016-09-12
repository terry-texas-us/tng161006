
<form id='form1' name='form1' action='familiesBrowse.php'>
  <section class='card'>
    <div class='card-block'>
      <div class='row form-group'>
        <div class='col-sm-6'>
          <label for='searchstring' class='sr-only'><?php echo uiTextSnippet('searchfor'); ?></label>
          <input class='form-control' name='searchstring' type='text' value="<?php echo $searchstring_noquotes; ?>" placeholder="<?php echo uiTextSnippet('searchfor'); ?>" autofocus>
          <button class='btn btn-outline-primary' name='submit' type='submit'><?php echo uiTextSnippet('search'); ?></button>
          <button class='btn btn-outline-warning' name='submit' type='submit' onClick="resetFamiliesSearch();"><?php echo uiTextSnippet('reset'); ?></button>
        </div>
      </div>
    </div>
    <div class='card-block'>
      <div class='row form-group'>
        <select name="spousename">
          <option value='husband'<?php if ($spousename == 'husband') {echo " selected";} ?>>
            <?php echo uiTextSnippet('husbname'); ?></option>
          <option value='wife'<?php if ($spousename == 'wife') {echo " selected";} ?>>
            <?php echo uiTextSnippet('wifename'); ?></option>
          <option value="none"<?php if ($spousename == "none") {echo " selected";} ?>>
            <?php echo uiTextSnippet('noname'); ?></option>
        </select>
        <label class='form-check-inline'>
          <input class='form-check-input' name='living' type='checkbox' value='yes'<?php if ($living == "yes") {echo " checked";} ?>>
          <?php echo uiTextSnippet('livingonly'); ?>
        </label>
        <label class='form-check-inline'>
          <input class='form-check-input' name='exactmatch' type='checkbox' value='yes'<?php if ($exactmatch == "yes") {echo " checked";} ?>>
          <?php echo uiTextSnippet('exactmatch'); ?>
        </label>
      </div>
    </div>
  </section> <!-- .card -->
  <input name='findfamily' type='hidden' value='1'>
  <input name='newsearch' type='hidden' value='1'>
</form>

