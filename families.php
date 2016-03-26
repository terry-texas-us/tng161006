<?php

function buildFamilyMenu($currpage, $familyId)
{
  global $tree;
  global $allowEdit;
  global $rightbranch;
  global $emailaddr;
  
  $menu = '';
  if ($allowEdit && $rightbranch) {
    $menu .= "<a id='a0' href='familiesEdit.php?familyID=$familyId&amp;tree=$tree&amp;cw=1' title='" . uiTextSnippet('edit') . "'>\n";
      $menu .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
    $menu .= "</a>\n";
  } elseif ($emailaddr && $currpage != 'suggest') {
    $menu .= "<a id='a0' href='familySuggest.php?ID=$familyId&amp;tree=$tree' title='" . uiTextSnippet('suggest') . "'>\n";
      $menu .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
    $menu .= "</a>\n";
  }
  return $menu;
}


