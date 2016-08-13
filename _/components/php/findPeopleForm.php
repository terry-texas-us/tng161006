 <!-- [ts] testing different approaches to bootstrap-forms -->
 
<form name='form1' action='peopleBrowse.php'>
  <section class='card'>
    <div class='card-block'>
      <div class='row form-group'>
        <div class='col-md-6'>
          <?php require '_/components/php/treeSelectControl.php'; ?>
        </div>
        <div class='input-group col-md-6'>
          <input class="form-control" id='searchstring' name='searchstring' type='text' value="<?php echo $searchstring_noquotes; ?>">
          <span class='input-group-addon'>
            <input name='exactmatch' type='checkbox' value='yes' title='<?php echo uiTextSnippet('exactmatch'); ?>' <?php if ($exactmatch == "yes") {echo "checked";} ?>>
          </span>
        </div>
      </div>
      <div class='row form-group'>
        <label class='checkbox-inline'>
          <input name='living' type='checkbox' value='yes'<?php if ($living == "yes") {echo " checked";} ?>>
          <?php echo uiTextSnippet('livingonly'); ?>
        </label>
        <label class='checkbox-inline'>
          <input name='nokids' type='checkbox' value='yes'<?php if ($nokids == "yes") {echo " checked";} ?>>
          <?php echo uiTextSnippet('nokids'); ?>
        </label>
        <label class='checkbox-inline'>
          <input name='noparents' type='checkbox' value='yes'<?php if ($noparents == "yes") {echo " checked";} ?>>
          <?php echo uiTextSnippet('noparents'); ?>
        </label>
        <label class='checkbox-inline'>
          <input name='nospouse' type='checkbox' value='yes'<?php if ($nospouse == "yes") {echo " checked";} ?>>
          <?php echo uiTextSnippet('nospouse'); ?>
        </label>
      </div>
      <div class='row'>
        <div class='col-md-offset-6 col-md-6'>
          <button class='btn btn-outline-primary' name='submit' type='submit'><?php echo uiTextSnippet('search'); ?></button>
          <button class='btn btn-outline-warning' name='submit' type='submit' onclick="resetPeople();"><?php echo uiTextSnippet('reset'); ?></button>
        </div>
      </div>
    </div>
  </section> <!-- .card -->
  <input name='findperson' type='hidden' value='1'>
  <input name='newsearch' type='hidden' value='1'>
</form>
