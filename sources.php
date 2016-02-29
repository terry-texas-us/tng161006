<?php

function buildSourceMenu($currpage, $sourceId) {
  global $tree;
  global $allow_edit;
  global $rightbranch;
  global $emailaddr;
  
  $menu = '';
  if ($allow_edit && $rightbranch) {
    $menu .= "<a id='a0' href='admin_editsource.php?sourceID=$sourceId&amp;tree=$tree&amp;cw=1' title='" . uiTextSnippet('edit') . "'>\n";
      $menu .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
    $menu .= "</a>\n";
  } elseif ($emailaddr && $currpage != 'suggest') {
    $menu .= "<a id='a0' href='sourceSuggest.php?ID=$sourceId&amp;tree=$tree' title='" . uiTextSnippet('suggest') . "'>\n";
      $menu .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
    $menu .= "</a>\n";
  }
  return $menu;
}

