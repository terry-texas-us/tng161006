<?php
require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

header('Content-type:text/html; charset=' . $sessionCharset);
?>
<div id='finddiv'>
  <h4><?php echo uiTextSnippet('addlinks'); ?></h4><br>
  <form name='find2' id='find2' onsubmit="return getPotentialLinks('<?php echo $linktype; ?>');">
    <?php if ($linktype == 'I') { ?>
      <table class='table table-sm' id='findformI'>
        <tr>
          <td colspan='2'>
            <strong><?php echo uiTextSnippet('findpersonid'); ?></strong>
            <span class='small'>(<?php echo uiTextSnippet('enterinamepart'); ?>)</span>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('lastname'); ?></td>
          <td><?php echo uiTextSnippet('firstname'); ?></td>
        </tr>
        <tr>
          <td><input id='mylastname' name='mylastname' type='text'></td>
          <td>
            <input id='myfirstname' name='myfirstname' type='text'>
            <input name='searchbutton' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
            <span id='spinnerfind' style='display: none'><img src='img/spinner.gif'></span>
          </td>
        </tr>
      </table>
    <?php } elseif ($linktype == 'F') { ?>
      <table class='table table-sm' id='findformF'>
        <tr>
          <td colspan='2'>
            <strong><?php echo uiTextSnippet('findfamilyid'); ?></strong>
            <span class='small'>(<?php echo uiTextSnippet('enterfnamepart'); ?>)</span>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('husbname'); ?></td>
          <td><?php echo uiTextSnippet('wifename'); ?></td>
        </tr>
        <tr>
          <td><input id='myhusbname' name='myhusbname' type='text'></td>
          <td>
            <input id='mywifename' name='mywifename' type='text'>
            <input name='searchbutton' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
            <span id='spinnerfind' style='display: none'><img src='img/spinner.gif'></span>
          </td>
        </tr>
      </table>
      <?php
    } elseif ($linktype == 'S') {
      ?>
      <table class='table table-sm' id='findformS'>
        <tr>
          <td colspan='2'>
            <strong><?php echo uiTextSnippet('findsourceid'); ?></strong> 
            <span class='small'>(<?php echo uiTextSnippet('entersourcepart'); ?>)</span>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('title'); ?></td>
        </tr>
        <tr>
          <td>
            <input id='mysourcetitle' name='mysourcetitle' type='text'>
            <input name='searchbutton' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
            <span id='spinnerfind' style='display: none'><img src='img/spinner.gif'></span>
          </td>
        </tr>
      </table>
    <?php } elseif ($linktype == 'R') { ?>
      <table class='table table-sm' id='findformR'>
        <tr>
          <td colspan='2'>
            <strong><?php echo uiTextSnippet('findrepoid'); ?></strong> 
            <span class='small'>(<?php echo uiTextSnippet('enterrepopart'); ?>)</span>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('title'); ?></td>
        </tr>
        <tr>
          <td>
            <input id='myrepotitle' name='myrepotitle' type='text'>
            <input name='searchbutton' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
            <span id='spinnerfind' style='display: none'><img src='img/spinner.gif'></span>
          </td>
        </tr>
      </table>
    <?php } elseif ($linktype == 'L') { ?>
      <table class='table table-sm' id='findformL'>
        <tr>
          <td colspan='2'>
            <strong><?php echo uiTextSnippet('findplace'); ?></strong> 
            <span class='small'>(<?php echo uiTextSnippet('enterplacepart'); ?>)</span>
          </td>
        </tr>
        <tr>
          <td><?php echo uiTextSnippet('place'); ?></td>
        </tr>
        <tr>
          <td>
            <input id='myplace' name='myplace' type='text'>
            <input name='searchbutton' type='submit' value="<?php echo uiTextSnippet('search'); ?>">
            <span id='spinnerfind' style='display:none'><img src='img/spinner.gif' width='16' height='16'></span>
          </td>
        </tr>
      </table>
      <?php
    }
    ?>
  </form>
  <div id='newlines' style='width: 605px; height: 390px; overflow: auto'></div>
</div>
