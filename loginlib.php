<?php

function injectLoginForm() {
?>    
  <form action='processlogin.php' method='post' name='form1'>
    <div class='form-login'>
      <div class='form-login-heading'>
        <h4><img class='icon-sm' src='svg/login.svg'><?php echo uiTextSnippet('login'); ?></h4>
      </div>
        <?php $label = uiTextSnippet('username'); ?>
        <label class='sr-only' for='tngusername'><?php echo $label; ?></label>
        <input class='form-control' id='username' name='tngusername' type='text' placeholder='<?php echo $label; ?>'>
        <?php $label = uiTextSnippet('password'); ?>
        <label class='sr-only' for='tngpassword'><?php echo $label; ?></label>
        <input class='form-control' name='tngpassword' type="password" placeholder='<?php echo $label; ?>'>
        <div id='resetrow' style='display: none'>
          <?php $label = uiTextSnippet('newpassword'); ?>
          <label class='sr-only' for='newpassword'><?php echo $label; ?></label>
          <input class='form-control' name='newpassword' type="password" placeholder='<?php echo $label; ?>'>
        </div>
        <div>
        <div class='checkbox'>
          <label>
            <input name='remember' type='checkbox' value='1'> <?php echo uiTextSnippet('rempass'); ?>
          </label>
        </div>
        <div class='checkbox'>
          <label>
            <input id='resetpass' name='resetpass' type='checkbox' value='1'> <?php echo uiTextSnippet('resetpass'); ?>
          </label>
        </div>
        <button class='btn btn-primary btn-block' type='submit'><?php echo uiTextSnippet('login'); ?></button>
      </div>
    </div>
  </form>
<?php  
}

function injectForgotCredentialsForm() {
?>  
  <form action='' method='post' name='form2' onsubmit="return sendLogin(this, 'sendlogin.php')">
    <div id="forgot" style="clear:both">
      <?php echo uiTextSnippet('forgot1'); ?>
      <div class="input-group">
        <input class='form-control' name='email' type='text' placeholder='<?php echo uiTextSnippet('email'); ?>'>
        <span class="input-group-btn">
          <button class='btn btn-secondary' type='submit'><?php echo uiTextSnippet('go'); ?></button>
        </span>
      </div>      
      <div id="usnmsg" class="small"></div>
      <br>
      <?php echo uiTextSnippet('forgot2'); ?>
      <div class="input-group">
        <input class='form-control' name='username' type='text' placeholder='<?php echo uiTextSnippet('username'); ?>'> 
        <span class='input-group-btn'>
          <button class='btn btn-secondary' type='submit'><?php echo uiTextSnippet('go'); ?></button>
        </span>
      </div>
      <div id="pwdmsg" class="small"></div>
    </div>
  </form>
<?php
}
