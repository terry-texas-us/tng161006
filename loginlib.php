<h2><img class='icon-md' src='svg/login.svg'><?php echo uiTextSnippet('login'); ?></h2>

<?php if ($message) { ?>
  <span style="color: red; "><em><?php echo uiTextSnippet($message); ?></em></span>
  <?php
}
beginFormElement('processlogin', 'post', 'form1');
?>
<div>
  <div id="loginblock">
    <div class='loginprompt'><?php echo uiTextSnippet('username'); ?>:</div>
    <input id='tngusername' name='tngusername' type='text'><br>

    <div class="loginprompt"><?php echo uiTextSnippet('password'); ?>:</div>
    <input id='tngpassword' name='tngpassword' type='password'>

    <div id="resetrow" style="display:none">
      <div class='loginprompt'><?php echo uiTextSnippet('newpassword'); ?>:</div>
      <input id='newpassword' name='newpassword' type='password'>
    </div>
  </div>
  <div style="float:left">
    <input name='remember' type='checkbox' value='1'/> <?php echo uiTextSnippet('rempass'); ?><br>
    <input name='resetpass' type='checkbox' value='1' 
           onclick="if (this.checked) {
             document.getElementById('resetrow').style.display = '';
           } else {
             document.getElementById('resetrow').style.display = 'none';
           }"/> <?php echo uiTextSnippet('resetpass'); ?>
  </div>
  <div style="float:left">
    <input class='bigsave' type='submit' style="margin-left:10px" value="<?php echo uiTextSnippet('login'); ?>"/>
  </div>
</div>
<?php endFormElement(); ?>
<br clear='both'><br>
<?php
beginFormElement("", "post", "form2", "", "return sendLogin(this, 'sendlogin.php');");
?>
<div id="forgot" style="clear:both">
  <span><?php echo uiTextSnippet('forgot1'); ?></span><br>
  <label class='loginprompt' for='email'><?php echo uiTextSnippet('email'); ?>: </label>
  <input id='email' name='email' type='text'> 
  <input type='submit' value="<?php echo uiTextSnippet('go'); ?>"/>
  <div id="usnmsg" class="small"></div>

  <span><br><?php echo uiTextSnippet('forgot2'); ?></span><br>
  <label class='loginprompt' for='username'><?php echo uiTextSnippet('username'); ?>: </label>
  <input id='username' name='username' type='text'> 
  <input type='submit' value="<?php echo uiTextSnippet('go'); ?>">
  <div id="pwdmsg" class="small"></div>
</div>
<?php
if (!$tngconfig['disallowreg']) {
  echo "<p>" . uiTextSnippet('nologin') . " <a href=\"newacctform.php\">" . uiTextSnippet('regnewacct') . "</a></p>";
}
endFormElement();
