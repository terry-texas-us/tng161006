<?php

// [ts] this file is likely unused

require 'begin.php';
require 'adminlib.php';

require 'checklogin.php';

$tng_search_cemeteries = $_SESSION['tng_search_cemeteries'];
$tng_search_cemeteries_post = $_SESSION['tng_search_cemeteries_post'];
if ($findcemetery) {
  $tng_search_cemeteries = $_SESSION['tng_search_cemeteries'] = 1;
  $tng_search_cemeteries_post = $_SESSION['tng_search_cemeteries_post'] = $_POST;
} else {
  if ($tng_search_cemeteries) {
    foreach ($_SESSION['tng_search_cemeteries_post'] as $key => $value) {
      ${$key} = $value;
    }
  }
}

function addCriteria($field, $value, $operator) {
  $criteria = '';

  if ($operator == '=') {
    $criteria = " OR $field $operator \"$value\"";
  } else {
    $innercriteria = '';
    $terms = explode(' ', $value);
    foreach ($terms as $term) {
      if ($innercriteria) {
        $innercriteria .= ' AND ';
      }
      $innercriteria .= "$field $operator \"%$term%\"";
    }
    if ($innercriteria) {
      $criteria = " OR ($innercriteria)";
    }
  }
  return $criteria;
}

if ($exactmatch == 'yes') {
  $frontmod = '=';
} else {
  $frontmod = 'LIKE';
}

$allwhere = 'WHERE 1=0';

if ($cemeteryID == 'yes') {
  $allwhere .= addCriteria('cemeteries.cemeteryID', $searchstring, $frontmod);
}

if ($maplink == 'yes') {
  $allwhere .= addCriteria('maplink', $searchstring, $frontmod);
}

if ($cemname == 'yes') {
  $allwhere .= addCriteria('cemname', $searchstring, $frontmod);
}

if ($city == 'yes') {
  $allwhere .= addCriteria('city', $searchstring, $frontmod);
}

if ($state == 'yes') {
  $allwhere .= addCriteria('state', $searchstring, $frontmod);
}

if ($county == 'yes') {
  $allwhere .= addCriteria('county', $searchstring, $frontmod);
}

if ($country == 'yes') {
  $allwhere .= addCriteria('country', $searchstring, $frontmod);
}
$query = "SELECT cemeteryID,cemname,city,county,state,country FROM cemeteries $allwhere ORDER BY cemname, city, county, state, country";
$result = tng_query($query);

$numrows = tng_num_rows($result);

if (!$numrows) {
  $message = uiTextSnippet('noresults');
  header('Location: cemeteriesShow.php?message=' . urlencode($message));
  exit;
}
$helplang = findhelp('cemeteries_help.html');

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('modifycemetery'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
<div class="center">
  <table class='table table-sm'>
    <tr>
      <td>
        <img class='icon-md' src='svg/headstone.svg'>
        <h4 class="white small"><?php echo uiTextSnippet('modifycemetery'); ?></h4>
      </td>
    </tr>
    <?php
    if ($message) {
      ?>
      <tr>
        <td>
          <span style="color: red; "><em><?php echo urldecode($message); ?></em></span>
        </td>
      </tr>
      <?php
    }
    ?>
    <tr>
      <td>
        <span class='h4'><?php echo uiTextSnippet('selectcemaction'); ?> | <a href="#"
            onclick="return openHelp('<?php echo $helplang; ?>/cemeteries_help.html#find', 'newwindow', 'height=500,width=600,resizable=yes,scrollbars=yes'); newwindow.focus();"><?php echo uiTextSnippet('help'); ?></a></span><br><br>
          <span>
            <img class='icon-sm' src='svg/new-message.svg' alt="<?php echo uiTextSnippet('edit'); ?>"> = <?php echo uiTextSnippet('edit'); ?>
            <img class='icon-sm' src='svg/trash.svg' alt="<?php echo uiTextSnippet('delete'); ?>"> = <?php echo uiTextSnippet('delete'); ?>
            <br>
            <?php echo '<p>' . uiTextSnippet('matches') . ": $numrows</p>"; ?>
          </span>
        <table class="table table-sm table-striped">
          <tr>
            <th><?php echo uiTextSnippet('action'); ?></th>
            <th><?php echo uiTextSnippet('id'); ?></th>
            <th><?php echo uiTextSnippet('cemetery'); ?></th>
            <th><?php echo uiTextSnippet('location'); ?></th>
          </tr>

          <?php
          $rowcount = 0;
          $actionstr = '';
          if ($allowEdit) {
            $actionstr .= "<a href=\"cemeteriesEdit.php?cemeteryID=xxx\">\n";
              $actionstr .= "<img class='icon-sm' src='svg/new-message.svg alt=\"" . uiTextSnippet('edit') . "\">\n";
            $actionstr .= "</a>\n";
          }
          if ($allowDelete) {
            $actionstr .= "<a href=\"deletecemetery.php?cemeteryID=xxx\" onClick=\"return confirm('" . uiTextSnippet('confdeletecem') . "' );\">\n";
              $actionstr .= "<img class='icon-sm' src='svg/trash.svg' alt=\"" . uiTextSnippet('delete') . "\">\n";
            $actionstr .= '</a>';
          }

          while ($rowcount < $numrows && $row = tng_fetch_assoc($result)) {
            $rowcount++;
            $location = $row['city'];
            if ($row['county']) {
              if ($location) {
                $location .= ', ';
              }
              $location .= $row['county'];
            }
            if ($row['state']) {
              if ($location) {
                $location .= ', ';
              }
              $location .= $row['state'];
            }
            if ($row['country']) {
              if ($location) {
                $location .= ', ';
              }
              $location .= $row['country'];
            }
            $newactionstr = str_replace('xxx', $row['cemeteryID'], $actionstr);
            echo "<tr>\n";
            echo "<td><span>$newactionstr</span></td>\n";
            echo "<td><span>{$row['cemeteryID']}</span></td>\n";
            echo "<td><span>{$row['cemname']}</span></td>\n";
            echo "<td><span>$location</span></td>\n";
            echo "</tr>\n";
          }
          tng_free_result($result);
          ?>
        </table>
      </td>
    </tr>

  </table>
</div>
<?php
echo $adminFooterSection->build();
echo scriptsManager::buildScriptElements($flags, 'admin');
?>
</body>
</html>
