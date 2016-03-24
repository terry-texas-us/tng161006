<?php

function buildRepositoryMenu($currpage, $repositoryId) {
  global $tree;
  global $allowEdit;
  global $rightbranch;
  global $emailaddr;
  
  $menu = '';
  if ($allowEdit && $rightbranch) {
    $menu .= "<a id='a0' href='repositoriesEdit.php?repoID=$repositoryId&amp;tree=$tree&amp;cw=1' title='" . uiTextSnippet('edit') . "'>\n";
      $menu .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
    $menu .= "</a>\n";
  } elseif ($emailaddr && $currpage != 'suggest') {
    $menu .= "<a id='a0' href='repositorySuggest.php?ID=$repositoryId&amp;tree=$tree' title='" . uiTextSnippet('suggest') . "'>\n";
      $menu .= "<img class='icon-sm' src='svg/new-message.svg'>\n";
    $menu .= "</a>\n";
  }
  return $menu;
}