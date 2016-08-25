<div class='row'>
  <div class='col-md-3'>
    <?php echo uiTextSnippet('shorttitle'); ?>:
  </div>
  <div class='col-md-9'>
    <input class='form-control' name='shorttitle' type='text' size='40' placeholder="<?php echo uiTextSnippet('required'); ?>">
  </div>
</div>
<div class='row'>
  <div class='col-md-3'>
    <?php echo uiTextSnippet('longtitle'); ?>:
  </div>
  <div class='col-md-9'>
    <input class='form-control' name='title' type='text' size='50'>
  </div>
</div>
<div class='row'>
  <div class='col-md-3'>
    <?php echo uiTextSnippet('author'); ?>:
  </div>
  <div class='col-md-9'>
    <input class='form-control' name='author' type='text' size='40'>
  </div>  
</div>
<div class='row'>
  <div class='col-md-3'>
    <?php echo uiTextSnippet('callnumber'); ?>:
  </div>
  <div class='col-md-9'>
    <input class='form-control' name='callnum' type='text' size='40'>
  </div>    
</div>
<div class='row'>
  <div class='col-md-3'>
    <?php echo uiTextSnippet('publisher'); ?>:
  </div>
  <div class='col-md-9'>
    <input class='form-control' name='publisher' type='text' size='40'>
  </div>    
</div>
<div class='row'>
  <div class='col-md-3'>
    <?php echo uiTextSnippet('repository'); ?>:
  </div>
  <div class='col-md-9'>
    <select class='form-control' name='repoID'>
      <option value=''></option>
      <?php
      $query = "SELECT repoID, reponame FROM $repositories_table $wherestr ORDER BY reponame";
      $reporesult = tng_query($query);
      while ($reporow = tng_fetch_assoc($reporesult)) {
        echo "  <option value='{$reporow['repoID']}'>{$reporow['reponame']}</option>\n";
      }
      tng_free_result($reporesult);
      ?>
    </select>
  </div>
</div>
<div class='row'>
  <div class='col-md-3'>
    <?php echo uiTextSnippet('actualtext'); ?>:
  </div>
  <div class='col-md-9'>
    <textarea name='actualtext' cols='50' rows='5'></textarea>
  </div>    
</div>
