<?php

function buildSourceMenu($currpage, $sourceId) {
  global $allowEdit;
  global $rightbranch;
  global $emailaddr;

  $menu = '';
  if ($allowEdit && $rightbranch) {
      $menu .= "<a id='a0' href='sourcesEdit.php?sourceID=$sourceId&amp;cw=1' title='" . uiTextSnippet('edit') . "'>\n";
        $menu .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
      $menu .= "</a>\n";
  } elseif ($emailaddr && $currpage != 'suggest') {
      $menu .= "<a id='a0' href='sourceSuggest.php?ID=$sourceId' title='" . uiTextSnippet('suggest') . "'>\n";
        $menu .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
      $menu .= "</a>\n";
  }
  return $menu;
}