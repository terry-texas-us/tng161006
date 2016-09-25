<?php
require 'tng_begin.php';

require 'functions.php';

$tngyear = preg_replace('/[^0-9]/', '', $tngyear);
$tngkeywords = preg_replace('/[^A-Za-z0-9]/', '', $tngkeywords);

set_time_limit(0);

if (!$tngneedresults) {
  //get today's date
  $tngdaymonth = date('d', time() + (3600 * $timeOffset));
  $tngmonth = date('m', time() + (3600 * $timeOffset));
  $tngneedresults = 1;
}
$logstring = "<a href=\"anniversaries.php?tngevent=$tngevent&amp;tngdaymonth=$tngdaymonth&amp;tngmonth=$tngmonth&amp;tngyear=$tngyear&amp;tngkeywords=$tngkeywords&amp;tngneedresults=$tngneedresults&amp;offset=$offset&amp;tngpage=$tngpage\">" . xmlcharacters(uiTextSnippet('anniversaries')) . '</a>';
writelog($logstring);
preparebookmark($logstring);

//compute $allwhere from submitted criteria

$ldsOK = determineLDSRights();

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle(uiTextSnippet('anniversaries'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
    <h2><img class='icon-md' src='svg/calendar.svg'><?php echo uiTextSnippet('anniversaries'); ?></h2>
    <br clear='left'>
    <form action="anniversaries2.php" method="get" name="form1" id="form1" onsubmit="return validateForm(this);">
      <p><?php echo uiTextSnippet('explain'); ?></p>
      <div class="annfield">
        <label for="tngevent"><?php echo uiTextSnippet('event'); ?>:</label><br>
        <select name="tngevent" id="tngevent" style="max-width:335px">
          <?php
          echo "<option value=''>&nbsp;</option>\n";
          echo '<option value="birth"';
          if ($tngevent == 'birth') {
            echo ' selected';
          }
          echo '>' . uiTextSnippet('born') . "</option>\n";

          echo '<option value="altbirth"';
          if ($tngevent == 'altbirth') {
            echo ' selected';
          }
          echo '>' . uiTextSnippet('christened') . "</option>\n";

          echo '<option value="death"';
          if ($tngevent == 'death') {
            echo ' selected';
          }
          echo '>' . uiTextSnippet('died') . "</option>\n";

          echo '<option value="burial"';
          if ($tngevent == 'burial') {
            echo ' selected';
          }
          echo '>' . uiTextSnippet('buried') . "</option>\n";

          echo '<option value="marr"';
          if ($tngevent == 'marr') {
            echo ' selected';
          }
          echo '>' . uiTextSnippet('married') . "</option>\n";

          echo '<option value="div"';
          if ($tngevent == 'div') {
            echo ' selected';
          }
          echo '>' . uiTextSnippet('divorced') . "</option>\n";

          if ($ldsOK) {
            echo '<option value="bapt"';
            if ($tngevent == 'bapt') {
              echo ' selected';
            }
            echo '>' . uiTextSnippet('baptizedlds') . "</option>\n";

            echo '<option value="conf"';
            if ($tngevent == 'conf') {
              echo ' selected';
            }
            echo '>' . uiTextSnippet('conflds') . "</option>\n";

            echo '<option value="init"';
            if ($tngevent == 'init') {
              echo ' selected';
            }
            echo '>' . uiTextSnippet('initlds') . "</option>\n";

            echo '<option value="endl"';
            if ($tngevent == 'endl') {
              echo ' selected';
            }
            echo '>' . uiTextSnippet('endowedlds') . "</option>\n";

            echo '<option value="seal"';
            if ($tngevent == 'seal') {
              echo ' selected';
            }
            echo '>' . uiTextSnippet('sealedslds') . "</option>\n";
          }

          //loop through custom event types where keep=1, not a standard event
          $query = "SELECT eventtypeID, tag, display FROM eventtypes WHERE keep = '1' AND type = 'I' ORDER BY display";
          $result = tng_query($query);
          $dontdo = ['ADDR', 'BIRT', 'CHR', 'DEAT', 'BURI', 'NAME', 'NICK', 'TITL', 'NSFX', 'DIV', 'MARR'];
          while ($row = tng_fetch_assoc($result)) {
            if (!in_array($row['tag'], $dontdo)) {
              echo "<option value=\"{$row['eventtypeID']}\"";
              if ($tngevent == $row['eventtypeID']) {
                echo ' selected';
              }
              echo '>' . getEventDisplay($row['display']) . "</option>\n";
            }
          }
          tng_free_result($result);
          ?>
        </select>
      </div>
      <div class="annfield">
        <label for="tngdaymonth"><?php echo uiTextSnippet('day'); ?>:</label><br>
        <select name="tngdaymonth" id="tngdaymonth">
          <option value=''>&nbsp;</option>
          <?php
          for ($i = 1; $i <= 31; $i++) {
            echo "<option value=\"$i\"";
            if ($i == $tngdaymonth) {
              echo ' selected';
            }
            echo ">$i</option>\n";
          }
          $tngkeywordsclean = preg_replace('/\"/', '&#34;', stripslashes($tngkeywords));
          ?>
        </select>
      </div>
      <div class="annfield">
        <label for="tngmonth" class="annlabel"><?php echo uiTextSnippet('month'); ?>:</label><br>
        <select name="tngmonth" id="tngmonth">
          <option value=''>&nbsp;</option>
          <option value='1'<?php if ($tngmonth == 1) {echo ' selected';} ?>><?php echo uiTextSnippet('JANUARY'); ?></option>
          <option value='2'<?php if ($tngmonth == 2) {echo ' selected';} ?>><?php echo uiTextSnippet('FEBRUARY'); ?></option>
          <option value='3'<?php if ($tngmonth == 3) {echo ' selected';} ?>><?php echo uiTextSnippet('MARCH'); ?></option>
          <option value='4'<?php if ($tngmonth == 4) {echo ' selected';} ?>><?php echo uiTextSnippet('APRIL'); ?></option>
          <option value='5'<?php if ($tngmonth == 5) {echo ' selected';} ?>><?php echo uiTextSnippet('MAY'); ?></option>
          <option value="6"<?php if ($tngmonth == 6) {echo ' selected';} ?>><?php echo uiTextSnippet('JUNE'); ?></option>
          <option value="7"<?php if ($tngmonth == 7) {echo ' selected';} ?>><?php echo uiTextSnippet('JULY'); ?></option>
          <option value="8"<?php if ($tngmonth == 8) {echo ' selected';} ?>><?php echo uiTextSnippet('AUGUST'); ?></option>
          <option value="9"<?php if ($tngmonth == 9) {echo ' selected';} ?>><?php echo uiTextSnippet('SEPTEMBER'); ?></option>
          <option value="10"<?php if ($tngmonth == 10) {echo ' selected';} ?>><?php echo uiTextSnippet('OCTOBER'); ?></option>
          <option value="11"<?php if ($tngmonth == 11) {echo ' selected';} ?>><?php echo uiTextSnippet('NOVEMBER'); ?></option>
          <option value="12"<?php if ($tngmonth == 12) {echo ' selected';} ?>><?php echo uiTextSnippet('DECEMBER'); ?></option>
        </select>
      </div>
      <div class="annfield">
        <label for='tngyear'><?php echo uiTextSnippet('year'); ?>:</label><br>
        <input id='tngyear' name='tngyear' type='text' size='6' maxlength='4' value="<?php echo $tngyear; ?>"/>
      </div>
      <div class="annfield">
        <label for="tngkeywords"><?php echo uiTextSnippet('keyword'); ?>:</label><br>
        <input id='tngkeywords' name='tngkeywords' type='text' value="<?php echo stripslashes($tngkeywordsclean); ?>"/>
      </div>
      <div class="annfield">
        <br>
        <input name='tngneedresults' type='hidden' value='1'/>
        <input type='submit' value="<?php echo uiTextSnippet('search'); ?>"/>
        <input type='button' value="<?php echo uiTextSnippet('reset'); ?>" onclick="resetForm();"/>
        | <input type='button' value="<?php echo uiTextSnippet('calendar'); ?>"
                 onclick="window.location.href='<?php echo "calendar.php?m=$tngmonth&amp;year=$tngyear"; ?>';"/>
      </div>
    </form>
    <br clear='all'>
    <br>
    <?php
    if ($tngneedresults) {
      $successcount = 0;
      if ($tngevent) {
        $tngevents = [$tngevent];
      } else {
        $tngevents = ['birth', 'altbirth', 'death', 'burial', 'marr', 'div'];
        if ($ldsOK) {
          $ldsevents = ['seal', 'endl', 'bapt', 'conf', 'init'];
          $tngevents = array_merge($tngevents, $ldsevents);
        }
        $query = 'SELECT tag, eventtypeID FROM eventtypes WHERE keep="1" AND type="I" ORDER BY display';
        $result = tng_query($query);
        $dontdo = ['ADDR', 'BIRT', 'CHR', 'DEAT', 'BURI', 'NAME', 'NICK', 'TITL', 'NSFX', 'DIV', 'MARR'];
        while ($row = tng_fetch_assoc($result)) {
          if (!in_array($row['tag'], $dontdo)) {
            array_push($tngevents, $row['eventtypeID']);
          }
        }
        tng_free_result($result);
      }
      foreach ($tngevents as $tngevent) {
        $allwhere = '';

        $eventsjoin = '';
        $eventsfields = '';
        $needfamilies = '';
        $tngsaveevent = $tngevent;
        switch ($tngevent) {
          case 'birth':
            $datetxt = uiTextSnippet('born');
            break;
          case 'altbirth':
            $datetxt = uiTextSnippet('christened');
            break;
          case 'death':
            $datetxt = uiTextSnippet('died');
            break;
          case 'burial':
            $datetxt = uiTextSnippet('buried');
            break;
          case 'marr':
            $datetxt = uiTextSnippet('married');
            $needfamilies = 1;
            break;
          case 'div':
            $datetxt = uiTextSnippet('divorced');
            $needfamilies = 1;
            break;
          case 'seal':
            $datetxt = uiTextSnippet('sealedslds');
            $needfamilies = 1;
            break;
          case 'endl':
            $datetxt = uiTextSnippet('endowedlds');
            break;
          case 'bapt':
            $datetxt = uiTextSnippet('baptizedlds');
            break;
          case 'conf':
            $datetxt = uiTextSnippet('conflds');
            break;
          case 'init':
            $datetxt = uiTextSnippet('initlds');
            break;
          default:
            //look up display
            $query = "SELECT display FROM eventtypes WHERE eventtypeID=\"$tngevent\" ORDER BY display";
            $evresult = tng_query($query);
            $event = tng_fetch_assoc($evresult);
            $datetxt = getEventDisplay($event['display']);
            tng_free_result($evresult);

            $eventsjoin = ', events';
            $eventsfields = ', info';
            $allwhere .= " AND people.personID = events.persfamID AND eventtypeID = '$tngevent'";
            $tngevent = 'event';
            break;
        }
        if ($needfamilies) {
          $familiesjoin = ' LEFT JOIN families ON (people.personID = families.husband)';
          $familiesjoinw = ' LEFT JOIN families ON (people.personID = families.wife)';
          $familiessortdate = ', ' . $tngevent . 'datetr';
        } else {
          $familiesjoin = '';
          $familiesjoinw = '';
          $familiessortdate = '';
        }

        $datefield = $tngevent . 'date';
        $datefieldtr = $tngevent . 'datetr';
        $place = $tngevent . 'place';

        if ($tngdaymonth) {
          $allwhere .= " AND DAYOFMONTH($datefieldtr) = '$tngdaymonth'";
        }
        if ($tngmonth) {
          $allwhere .= " AND MONTH($datefieldtr) = '$tngmonth'";
        }
        if ($tngyear) {
          $allwhere .= " AND YEAR($datefieldtr) = '$tngyear'";
        }
        if ($tngkeywords) {
          $allwhere .= " AND $datefield LIKE '%$tngkeywords%'";
        }
        if ($tngdaymonth || $tngmonth || $tngyear) {
          $allwhere .= " AND $datefieldtr != '0000-00-00'";
        }
        $livingPrivateCondition = getLivingPrivateRestrictions('people', false, true);
        if ($livingPrivateCondition) {
          $allwhere .= ' AND ' . $livingPrivateCondition;
        }

        $max_browsesearch_pages = 5;
        if ($offset) {
          $offsetplus = $offset + 1;
          $newoffset = "$offset, ";
        } else {
          $offsetplus = 1;
          $newoffset = '';
          $tngpage = 1;
        }

        //if one event was selected, just do that one
        //if no event was selected, do them each in turn

        $query = "SELECT people.ID, people.personID, lastname, lnprefix, firstname, people.living, people.branch, prefix, suffix, nameorder, $place, $datefield $familiessortdate $eventsfields
          FROM (people $eventsjoin) $familiesjoin
          WHERE 1 $allwhere ";
        if ($needfamilies) {
          $query .= "UNION ALL SELECT people.ID, people.personID, lastname, lnprefix, firstname, people.living, people.branch, prefix, suffix, nameorder, $place, $datefield $familiessortdate $eventsfields
            FROM (people $eventsjoin) $familiesjoinw
            WHERE 1=1 $allwhere ";
        }
        $query .= " ORDER BY DAY($datefieldtr), MONTH($datefieldtr), YEAR($datefieldtr), lastname, firstname LIMIT $newoffset" . $maxsearchresults;

        $result = tng_query($query);
        $numrows = tng_num_rows($result);

        if ($numrows == $maxsearchresults || $offsetplus > 1) {
          if ($needfamilies) {
            $query = "SELECT (SELECT count(personID)
              FROM (people $eventsjoin) $familiesjoin
              WHERE 1=1 $allwhere) +
              (SELECT count(personID)
              FROM (people $eventsjoin) $familiesjoinw
              WHERE 1=1 $allwhere) as pcount";
          } else {
            $query = "SELECT count(personID) AS pcount FROM (people $eventsjoin) $familiesjoin WHERE 1=1 $allwhere";
          }
          $result2 = tng_query($query);
          $countrow = tng_fetch_assoc($result2);
          $totrows = $countrow['pcount'];
        } else {
          $totrows = $numrows;
        }
        if ($numrows) {
          echo "<div>\n";
          echo "<h4>$datetxt</h4>";
          $numrowsplus = $numrows + $offset;
          $successcount++;

          echo '<p>' . uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows</p>";
          ?>
          <table class="table table-sm table-striped">
            <tr>
              <td></td>
              <th><?php echo uiTextSnippet('lastfirst'); ?></th>
              <th colspan='2'><?php echo $datetxt; ?></th>
            </tr>

            <?php
            $i = $offsetplus;
            while ($row = tng_fetch_assoc($result)) {
              $rights = determineLivingPrivateRights($row);
              $row['allow_living'] = $rights['living'];
              $row['allow_private'] = $rights['private'];
              if ($rights['both']) {
                $placetxt = $row[$place] ? buildSilentPlaceLink($row[$place]) : truncateIt($row['info'], 75);
                $dateval = $row[$datefield];
              } else {
                $dateval = $placetxt = $prefix = $suffix = $title = $nickname = $birthdate = $birthplace = $deathdate = $deathplace = $livingOK = '';
              }
              echo '<tr>';
              $name = getNameRev($row);
              echo "<td>$i</td>\n";
              $i++;
              echo "<td>\n";
              echo "<a tabindex='0' class='btn btn-sm btn-outline-primary person-popover' role='button' data-toggle='popover' data-placement='bottom' data-person-id={$row['personID']}>$name</a>\n";
              echo "</td>\n";
              echo '<td>' . displayDate($dateval) . "</td><td>$placetxt</td>";
              echo "</tr>\n";
            }
            tng_free_result($result);
            ?>

          </table>

          <?php
          $url = "anniversaries2.php?$urlstring&amp;tngevent=$tngsaveevent&amp;tngdaymonth=$tngdaymonth&amp;tngmonth=$tngmonth&amp;tngyear=$tngyear&amp;tngkeywords=$tngkeywords&amp;tngneedresults=1&amp;offset"; 
          echo buildSearchResultPagination($totrows, $url, $maxsearchresults, $max_browsesearch_pages);
          echo "</div>\n";
        }
      }
      if (!$successcount) {
        echo '<p>' . uiTextSnippet('noresults') . '.</p>';
      }
    } //end of $tng_needresults
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
<script src="js/search.js"></script>
<script>
   $(function () {
        $('[data-toggle="popover"]').popover();
    });

  function resetForm() {
    var myform = document.form1;

    myform.tngevent.selectedIndex = 0;
    myform.tngdaymonth.value = '';
    myform.tngmonth.selectedIndex = 0;
    myform.tngyear.value = '';
    myform.tngkeywords.value = '';
  }

  function validateForm(form) {
    var rval = true;

    if (form.tngdaymonth.selectedIndex === 0 && form.tngmonth.selectedIndex === 0 && form.tngyear.value.length === 0 && form.tngkeywords.value.length === 0) {
      alert(textSnippet('enterdate'));
      rval = false;
    }
    return rval;
  }
</script>
</body>
</html>