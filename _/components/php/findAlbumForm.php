 <!-- [ts] testing different approaches to bootstrap-forms -->
 
<form id='form1' name='form1' action='#'>
  <section class="card">
    <div class='card-block'>
      <div class="row form-group">
        <label for='searchstring' class='sr-only'><?php echo uiTextSnippet('searchfor'); ?></label>
        <div class='input-group'>
          <input  class='form-control' id='searchstring' name='searchstring' type='text' value="<?php echo $searchstring_noquotes; ?>" placeholder="<?php echo uiTextSnippet('searchfor'); ?>" autofocus>
          <span class="input-group-btn">
            <button class="btn btn-secondary" type="button" onclick="resetFamilies();"><?php echo uiTextSnippet('reset'); ?></button>
          </span>
        </div>
      </div>
      <div class="row form-group">
        <input class='btn btn-secondary-outline' name='submit' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
        <input class='btn btn-secondary-outline' name='submit' type='submit' value="<?php echo uiTextSnippet('reset'); ?>" onclick="resetFamilies();">
      </div>
    </div>
  </section> <!-- .card -->
  <input name='findalbum' type='hidden' value='1'>
  <input name='newsearch' type='hidden' value='1'>
</form>
