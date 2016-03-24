<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

if ($type == "map") {
  $firstfield = "personID";
  $subtitle = uiTextSnippet('enternamepart2');
} else {
  $firstfield = "mylastname";
  $subtitle = uiTextSnippet('enternamepart');
}
header("Content-type:text/html; charset=" . $session_charset);
?>
<div id='finddiv'>
  <h4><?php echo uiTextSnippet('findpersonid'); ?></h4>

  <form id='findform1' name='findform1' action='' onsubmit="return openFind(this,'findperson2.php');">
    <span>(<?php echo $subtitle; ?>)</span><br>

    <input name='tree' type='hidden' value="<?php echo $tree; ?>">
    <?php if ($formname == "") {
      $formname = "form1";
    } ?>
    <input name='formname' type='hidden' value="<?php echo $formname; ?>">
    <?php if ($field == "") {
      $field = "personID";
    } ?>
    <input name='field' type='hidden' value="<?php echo $field; ?>">
    <?php if ($type == "") {
      $type = "text";
    } ?>
    <input name='type' type='hidden' value="<?php echo $type; ?>">
    <?php
    if ($nameplusid) {
      echo "<input name='nameplusid' type='hidden' value=\"$nameplusid\">";
    }
    ?>
    <table>
      <tr>
        <td><?php echo uiTextSnippet('lastname'); ?>:</td>
        <td><input id='mylastname' name='mylastname' type='text'></td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('firstname'); ?>:</td>
        <td><input id='myfirstname' name='myfirstname' type='text'></td>
      </tr>
      <tr>
        <td><?php echo uiTextSnippet('personid'); ?>:</td>
        <td><input name='personID' type='text'></td>
      </tr>
    </table>
    <br>
    <input type='submit' value="<?php echo uiTextSnippet('search'); ?>"> 
    <img id='findspin' src="img/spinner.gif" style="display: none">
  </form>

</div>

<div style="display:none;border:0" id="findresults">
</div>