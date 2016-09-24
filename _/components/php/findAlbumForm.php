<form class='form-inline' id='form1' name='form1' action='#'>
  <section class='card'>
    <div class='card-block'>
      <div class='form-group'>
        <label for='searchstring' class='sr-only'><?php echo uiTextSnippet('searchfor'); ?></label>
        <div class='input-group'>
          <input  class='form-control' id='searchstring' name='searchstring' type='text' value="<?php echo $searchstring_noquotes; ?>" placeholder="<?php echo uiTextSnippet('searchfor'); ?>" autofocus>
          <span class='input-group-btn'>
            <button class='btn btn-secondary' type='button' onclick="resetFamilies();"><?php echo uiTextSnippet('reset'); ?></button>
          </span>
        </div>
      </div>
      <div class='form-group'>
        <button class='btn btn-secondary' name='submit' type='submit'><?php echo uiTextSnippet('search'); ?></button>
        <button class='btn btn-secondary' name='submit' type='submit' onclick="resetFamilies();"><?php echo uiTextSnippet('reset'); ?></button>
      </div>
    </div>
  </section> <!-- .card -->
  <input name='findalbum' type='hidden' value='1'>
  <input name='newsearch' type='hidden' value='1'>
</form>
