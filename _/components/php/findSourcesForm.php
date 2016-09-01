<form action="sourcesBrowse.php" name='form1' id='form1'>
  <section class='card'>
    <div class='card-block'>
      <div class='row form-group'>
        <div class='input-group col-md-6'>
          <input class='form-control' name='searchstring' type='text' value="<?php echo $searchstring_noquotes; ?>">
          <span class='input-group-addon'>
            <input name='exactmatch' type='checkbox' value='yes' title='<?php echo uiTextSnippet('exactmatch'); ?>' <?php if ($exactmatch == "yes") {echo "checked";} ?>>
          </span>        
        </div>
      </div>
      <div class='row'>
        <div class='offset-md-6 col-md-6'>
          <button class='btn btn-outline-primary' name='submit' type='submit'><?php echo uiTextSnippet('search'); ?></button>
          <button class='btn btn-outline-warning' name='submit' type='submit' onclick="resetSourcesSearch();"><?php echo uiTextSnippet('reset'); ?></button>
        </div>        
      </div>
    </div>
  </section> <!-- .card -->
  <input name='findsource' type='hidden' value='1'>
  <input name='newsearch' type='hidden' value='1'>
</form>
