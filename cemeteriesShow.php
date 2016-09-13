<?php
require 'tng_begin.php';

$query = "SELECT * FROM $cemeteries_table ORDER BY country, state, county, city, cemname";
$cemresult = tng_query($query);
$numcems = $tngconfig['cemrows'] ? $tngconfig['cemrows'] : max(floor(tng_num_rows($cemresult) / 2), 10);

$query = "SELECT $medialinks_table.personID AS personID FROM $medialinks_table, $media_table WHERE $media_table.mediaID = $medialinks_table.mediaID AND mediatypeID = 'headstones' AND cemeteryID = ''";
$hsresult = tng_query($query);
$numhs = tng_num_rows($hsresult);
tng_free_result($hsresult);

$logstring = "<a href='cemeteriesShow.php'>" . uiTextSnippet('cemeteriesheadstones') . '</a>';
writelog($logstring);
preparebookmark($logstring);

$flags['styles'] = "<link href=\"css/cemeteries.css\" rel=\"stylesheet\" type=\"text/css\" />\n";

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('cemeteriesheadstones'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/headstone.svg'><?php echo uiTextSnippet('cemeteriesheadstones'); ?></h2>
    <br clear='all'>
    <?php
    define('DUMMYPLACE', '@@@@@@');
    define('NUMCOLS', 2);           //set as number of columns-1
    define('DEFAULT_COLUMN_LENGTH', $numcems);
    $numrows = tng_num_rows($cemresult);
    $colsize = DEFAULT_COLUMN_LENGTH;
    $lastcountry = DUMMYPLACE;
    $divctr = $linectr = $colctr = $i = 0;

    echo "<div id=\"cemwrapper\">\n";
    echo "<p>&nbsp;&nbsp;<a href=\"mediaShow.php?mediatypeID=headstones\">&raquo; " . uiTextSnippet('showallhsr') . "</a></p>\n";
    echo "<div id=\"cemcontainer\">\n";
    echo "<div id=\"col$colctr\">\n";

    $cemetery = tng_fetch_assoc($cemresult);
    $orphan = false;
    $hiding = false;
    while ($i < $numrows) {
      if ($cemetery['country'] == $lastcountry) {
        if ($cemetery['state'] == $laststate) {
          if ($cemetery['county'] == $lastcounty) {
            $lastcity = DUMMYPLACE;
            $cityctr = 0;
            while (($i < $numrows) && ($cemetery['county'] == $lastcounty) && ($cemetery['state'] == $laststate) && ($cemetery['country'] == $lastcountry)) { // display all cemeteries in the current county
              if ($cemetery['city'] != $lastcity) {
                //end last city if $lastcity != dummy
                if ($lastcity != DUMMYPLACE) {
                  echo "</div>\n";
                }
                //start a new city
                $lastcity = $cemetery['city'];
                $divctr++;
                if (!$hiding) {
                  $linectr++;
                }
                $divname = "city$divctr";
                if ($cemetery['city'] || !$tngconfig['cemblanks']) {
                  $txt = $cemetery['city'] ? htmlspecialchars($cemetery['city'], ENT_QUOTES, $session_charset) : uiTextSnippet('nocity');
                  echo "<div class=\"pad3\">\n";
                    echo "<img src=\"" . "img/tng_expand.gif\" class=\"expandicon\" title='" . uiTextSnippet('expand') . "' id='plusminus$divname' onclick=\"return toggleSection('$divname');\" alt=''>\n";
                    echo "<a href=\"headstones.php?country=" . urlencode($cemetery['country']) . "&amp;state=" . urlencode($cemetery['state']) . "&amp;county=" . urlencode($cemetery['county']) . "&amp;city=" . urlencode($cemetery['city']) . "\">$txt</a>\n";
                  echo "</div>\n";
                  echo "<div id=\"$divname\" class=\"cemblock\" style=\"display:none;\">\n";
                } else {
                  echo "<div id=\"$divname\">\n";
                }
              }
              $txt = $cemetery['cemname'] ? $cemetery['cemname'] : uiTextSnippet('nocemname');
              $txt = htmlspecialchars($txt, ENT_QUOTES, $session_charset);
              echo "- <a href=\"cemeteriesShowCemetery.php?cemeteryID={$cemetery['cemeteryID']}\">$txt</a><br>\n";
              $cemetery = tng_fetch_assoc($cemresult);
              $i++;
            }
            if ($lastcity != DUMMYPLACE) {
              echo "</div>\n";
            }
            echo "</div>\n";                    // displayed all cemeteries in the county

          } else {                                // display the county
            $divname = "county$divctr";
            $divctr++;
            $lastcounty = $cemetery['county'];
            if ($cemetery['county'] || !$tngconfig['cemblanks']) {
              $linectr++;
              $txt = $cemetery['county'] ? htmlspecialchars($cemetery['county'], ENT_QUOTES, $session_charset) : uiTextSnippet('nocounty');
              echo "<div class=\"pad3\">\n";
                echo "<img src=\"" . "img/tng_expand.gif\" class=\"expandicon\" title='" . uiTextSnippet('expand') . "' id='plusminus$divname' onclick=\"return toggleSection('$divname');\" alt=''>\n";
                echo "<a href=\"headstones.php?country=" . urlencode($cemetery['country']) . "&amp;state=" . urlencode($cemetery['state']) . "&amp;county=" . urlencode($cemetery['county']) . "\">$txt</a>\n";
              echo "</div>\n";
              echo "<div id=\"$divname\" class='cemblock' style='display:none;'>\n";
              $hiding = true;
            } else {
              echo "<div id=\"$divname\">\n";
              $hiding = false;
            }
          }
        } else {                                // display the State
          if (($colctr < NUMCOLS) && ($linectr > $colsize) && !$orphan) {    // end of a column
            $linectr = 0;
            $colctr++;
            echo "</div>\n";
            echo "<div id=\"col$colctr\">\n<em>{$cemetery['country']} " . uiTextSnippet('cont') . "</em>\n";
          }
          $orphan = false;

          $laststate = $cemetery['state'];
          $lastcounty = DUMMYPLACE;
          $hiding = false;
          $txt = $cemetery['state'] ? htmlspecialchars($cemetery['state'], ENT_QUOTES, $session_charset) : uiTextSnippet('nostate');
          if ($cemetery['state'] || !$tngconfig['cemblanks']) {
            $linectr += 2;        //Add extra line to allow for the <br> at the end
            echo "<br><strong><a href=\"headstones.php?country=" . urlencode($cemetery['country']) . '&amp;state=' . urlencode($cemetery['state']) . "\">$txt</a></strong><br>\n";
          } else {
            $linectr++;
            echo "<br>\n";
          }
        }
      } else {                                    // display the Country
        if (($colctr < NUMCOLS) && ($linectr > $colsize)) {    // end of a column
          $linectr = 0;
          $colctr++;
          echo "</div>\n<div id=\"col$colctr\">\n";
        }
        $lastcountry = $cemetery['country'];
        $laststate = DUMMYPLACE;
        $lastcounty = DUMMYPLACE;
        $hiding = false;
        if ($linectr) {
          echo "<br>";
        }
        $linectr++;     //Add extra line to allow for the <br> at the end
        $txt = $cemetery['country'] ? htmlspecialchars($cemetery['country'], ENT_QUOTES, $session_charset) : uiTextSnippet('nocountry');
        echo "<div class=\"cemcountry h4\"><a href=\"headstones.php?country=" . urlencode($cemetery['country']) . "\">$txt</a></div>\n";
        $orphan = true;
      }
    }
    tng_free_result($cemresult);

    if ($numhs) {
      echo "<br>\n";
      echo "<div class=\"cemcountry h4\">\n";
        echo "<a href='headstones.php'>" . uiTextSnippet('nocemetery') . "</a>\n";
      echo "</div>\n";
    }
    echo "</div>\n";    //colx
    echo "</div>\n";    //container
    echo "</div>\n<br clear='all'>";    // #cemwrapper
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
<script>
  function toggleSection(key) {
    'use strict';
    var section = $('#' + key);
    if (section.css('display') === 'none') {
      $('#' + key).fadeIn(200);

      swap("plusminus" + key, "minus");
    }
    else {
      $('#' + key).fadeOut(200);
      swap("plusminus" + key, "plus");
    }
    return false;
  }

  plus = new Image;
  plus.src = "img/tng_expand.gif";
  minus = new Image;
  minus.src = "img/tng_collapse.gif";

  function swap(x, y) {
    $('#' + x).attr('title', y === "minus" ? textSnippet('collapse') : textSnippet('expand'));
    document.images[x].src = eval(y + '.src');
  }
</script>
</body>
</html>
