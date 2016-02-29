<?php

function buildFamilyMenu($currpage, $familyId) {
  global $tree;
  global $allow_edit;
  global $rightbranch;
  global $emailaddr;
  
  $menu = '';
  if ($allow_edit && $rightbranch) {
    $menu .= "<a id='a0' href='admin_editfamily.php?familyID=$familyId&amp;tree=$tree&amp;cw=1' title='" . uiTextSnippet('edit') . "'>\n";
      $menu .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
    $menu .= "</a>\n";
  } elseif ($emailaddr && $currpage != 'suggest') {
    $menu .= "<a id='a0' href='familySuggest.php?ID=$familyId&amp;tree=$tree' title='" . uiTextSnippet('suggest') . "'>\n";
      $menu .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
    $menu .= "</a>\n";
  }
  return $menu;
}


