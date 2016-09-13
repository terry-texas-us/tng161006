<form id='trees-add' name='treeform' action='treesAddFormAction.php' method='post' role='form'>
  <section class='card'>
    <div class='card-header'>
      <span><?php echo uiTextSnippet('newtreeinfo'); ?></span>
    </div>
    <div class='card-block'>
      <div class='row form-group'>
        <div class='col col-sm-6'>
          <label for='gedcom' class='sr-only'><?php echo uiTextSnippet('treeid'); ?></label>
          <input class='form-control' name='gedcom' type='text' placeholder="<?php echo uiTextSnippet('treeid'); ?>" required autofocus>
        </div>
        <div class='col col-sm-6'>
          <label for='treename' class='sr-only'><?php echo uiTextSnippet('treename'); ?></label>
          <input class='form-control' name='treename' type='text' placeholder="<?php echo uiTextSnippet('treename'); ?>" required>
        </div>
      </div>         
      <div class='row form-group'>
        <div class='col col-sm-12'>
          <label for='description' class='sr-only'><?php echo uiTextSnippet('description'); ?></label>
          <input class='form-control' name='description' type='text' placeholder="<?php echo uiTextSnippet('description'); ?>">
        </div>
      </div>
    </div>
  </section> <!-- .card -->
  
  <section class='card'>
    <div class='card-header'>
      <span><?php echo uiTextSnippet('ownerofthistree'); ?></span>
    </div>
    <div class='card-block'>
      <div class='row'>
        <div class='col col-sm-6'>
          <label for='owner' class='sr-only'><?php echo uiTextSnippet('owner'); ?></label>
          <input class='form-control' name='owner' type='text' placeholder="<?php echo uiTextSnippet('owner'); ?>">
          <br>
          <label for='phone' class='sr-only'><?php echo uiTextSnippet('phone'); ?></label>
          <input class='form-control' name='phone' type='tel' placeholder="<?php echo uiTextSnippet('phone'); ?>">
          <label for='email' class='sr-only'><?php echo uiTextSnippet('email'); ?></label>
          <input class='form-control' name='email' type='email' placeholder="<?php echo uiTextSnippet('email'); ?>">
        </div>
        <div class='col col-sm-6'>
          <label for='address' class='sr-only'><?php echo uiTextSnippet('address'); ?></label>
          <input class='form-control' name='address' type='text' placeholder="<?php echo uiTextSnippet('address'); ?>">
          <label for='city' class='sr-only'><?php echo uiTextSnippet('city'); ?></label>
          <input class='form-control' name='city' type='text' placeholder="<?php echo uiTextSnippet('city'); ?>">
          <label for='stateprov' class='sr-only'><?php echo uiTextSnippet('stateprov'); ?></label>
          <input class='form-control' name='stateprov' type='text' placeholder="<?php echo uiTextSnippet('stateprov'); ?>">
          <label for='zip' class='sr-only'><?php echo uiTextSnippet('zip'); ?></label>
          <input class='form-control' name='zip' type='text' placeholder="<?php echo uiTextSnippet('zip'); ?>">
          <label for='cap_country' class='sr-only'><?php echo uiTextSnippet('cap_country'); ?></label>
          <input class='form-control' name='cap_country' type='text' placeholder="<?php echo uiTextSnippet('cap_country'); ?>">
        </div>
      </div>
      <div class='footer'>
        <label>
          <input name='private' type='checkbox' value='1'<?php if ($private) {echo ' checked';} ?>> <?php echo uiTextSnippet('keepprivate'); ?>
        </label>
      </div>
    </div>
  </section> <!-- .card -->
  <div class='footer'>
    <button class='btn btn-primary btn-block' name='submit' type='submit'><?php echo uiTextSnippet('save'); ?></button> 
    <span class='msgapproved' id='treemsg'></span>
  </div>
  <input name='beforeimport' type='hidden' value="<?php echo $beforeimport; ?>">
</form>