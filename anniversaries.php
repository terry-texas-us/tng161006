<?php
require 'tng_begin.php';

require 'functions.php';

$tngyear = preg_replace("/[^0-9]/", "", $tngyear);
$tngkeywords = preg_replace("/[^A-Za-z0-9]/", "", $tngkeywords);

set_time_limit(0);

if (!$tngneedresults) {
  //get today's date
  $tngdaymonth = date("d", time() + (3600 * $time_offset));
  $tngmonth = date("m", time() + (3600 * $time_offset));
  $tngneedresults = 1;
}

$treestr = $tree ? " (" . uiTextSnippet('tree') . ": $tree)" : "";
$logstring = "<a href=\"anniversaries.php?tngevent=$tngevent&amp;tngdaymonth=$tngdaymonth&amp;tngmonth=$tngmonth&amp;tngyear=$tngyear&amp;tngkeywords=$tngkeywords&amp;tngneedresults=$tngneedresults&amp;offset=$offset&amp;tree=$tree&amp;tngpage=$tngpage\">" . xmlcharacters(uiTextSnippet('anniversaries') . " $treestr") . "</a>";
writelog($logstring);
preparebookmark($logstring);

//compute $allwhere from submitted criteria

$ldsOK = determineLDSRights();

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
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
      <?php echo treeDropdown(array('startform' => false, 'endform' => false, 'name' => 'form1')); ?>
      <p><?php echo uiTextSnippet('explain'); ?></p>
      <div class="annfield">
        <label for="tngevent"><?php echo uiTextSnippet('event'); ?>:</label><br>
        <select name="tngevent" id="tngevent" style="max-width:335px">
          <?php
          echo "<option value=''>&nbsp;</option>\n";
          echo "<option value=\"birth\"";
          if ($tngevent == "birth") {
            echo " selected";
          }
          echo ">" . uiTextSnippet('born') . "</option>\n";

          echo "<option value=\"altbirth\"";
          if ($tngevent == "altbirth") {
            echo " selected";
          }
          echo ">" . uiTextSnippet('christened') . "</option>\n";

          echo "<option value=\"death\"";
          if ($tngevent == "death") {
            echo " selected";
          }
          echo ">" . uiTextSnippet('died') . "</option>\n";

          echo "<option value=\"burial\"";
          if ($tngevent == "burial") {
            echo " selected";
          }
          echo ">" . uiTextSnippet('buried') . "</option>\n";

          echo "<option value=\"marr\"";
          if ($tngevent == "marr") {
            echo " selected";
          }
          echo ">" . uiTextSnippet('married') . "</option>\n";

          echo "<option value=\"div\"";
          if ($tngevent == "div") {
            echo " selected";
          }
          echo ">" . uiTextSnippet('divorced') . "</option>\n";

          if ($ldsOK) {
            echo "<option value=\"bapt\"";
            if ($tngevent == "bapt") {
              echo " selected";
            }
            echo ">" . uiTextSnippet('baptizedlds') . "</option>\n";

            echo "<option value=\"conf\"";
            if ($tngevent == "conf") {
              echo " selected";
            }
            echo ">" . uiTextSnippet('conflds') . "</option>\n";

            echo "<option value=\"init\"";
            if ($tngevent == "init") {
              echo " selected";
            }
            echo ">" . uiTextSnippet('initlds') . "</option>\n";

            echo "<option value=\"endl\"";
            if ($tngevent == "endl") {
              echo " selected";
            }
            echo ">" . uiTextSnippet('endowedlds') . "</option>\n";

            echo "<option value=\"seal\"";
            if ($tngevent == "seal") {
              echo " selected";
            }
            echo ">" . uiTextSnippet('sealedslds') . "</option>\n";
          }

          //loop through custom event types where keep=1, not a standard event
          $query = "SELECT eventtypeID, tag, display FROM $eventtypes_table
        WHERE keep=\"1\" AND type=\"I\" ORDER BY display";
          $result = tng_query($query);
          $dontdo = array("ADDR", "BIRT", "CHR", "DEAT", "BURI", "NAME", "NICK", "TITL", "NSFX", "DIV", "MARR");
          while ($row = tng_fetch_assoc($result)) {
            if (!in_array($row['tag'], $dontdo)) {
              echo "<option value=\"{$row['eventtypeID']}\"";
              if ($tngevent == $row['eventtypeID']) {
                echo " selected";
              }
              echo ">" . getEventDisplay($row['display']) . "</option>\n";
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
              echo " selected";
            }
            echo ">$i</option>\n";
          }
          $tngkeywordsclean = preg_replace("/\"/", "&#34;", stripslashes($tngkeywords));
          ?>
        </select>
      </div>
      <div class="annfield">
        <label for="tngmonth" class="annlabel"><?php echo uiTextSnippet('month'); ?>:</label><br>
        <select name="tngmonth" id="tngmonth">
          <option value=''>&nbsp;</option>
          <option value='1'<?php if ($tngmonth == 1) {echo " selected";} ?>><?php echo uiTextSnippet('JANUARY'); ?></option>
          <option value="2"<?php if ($tngmonth == 2) {echo " selected";} ?>><?php echo uiTextSnippet('FEBRUARY'); ?></option>
          <option value="3"<?php if ($tngmonth == 3) {echo " selected";} ?>><?php echo uiTextSnippet('MARCH'); ?></option>
          <option value="4"<?php if ($tngmonth == 4) {echo " selected";} ?>><?php echo uiTextSnippet('APRIL'); ?></option>
          <option value="5"<?php if ($tngmonth == 5) {echo " selected";} ?>><?php echo uiTextSnippet('MAY'); ?></option>
          <option value="6"<?php if ($tngmonth == 6) {echo " selected";} ?>><?php echo uiTextSnippet('JUNE'); ?></option>
          <option value="7"<?php if ($tngmonth == 7) {echo " selected";} ?>><?php echo uiTextSnippet('JULY'); ?></option>
          <option value="8"<?php if ($tngmonth == 8) {echo " selected";} ?>><?php echo uiTextSnippet('AUGUST'); ?></option>
          <option value="9"<?php if ($tngmonth == 9) {echo " selected";} ?>><?php echo uiTextSnippet('SEPTEMBER'); ?></option>
          <option value="10"<?php if ($tngmonth == 10) {echo " selected";} ?>><?php echo uiTextSnippet('OCTOBER'); ?></option>
          <option value="11"<?php if ($tngmonth == 11) {echo " selected";} ?>><?php echo uiTextSnippet('NOVEMBER'); ?></option>
          <option value="12"<?php if ($tngmonth == 12) {echo " selected";} ?>><?php echo uiTextSnippet('DECEMBER'); ?></option>
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
        <input type='button' value="<?php echo uiTextSnippet('tng_reset'); ?>" onclick="resetForm();"/>
        | <input type='button' value="<?php echo uiTextSnippet('calendar'); ?>"
                 onclick="window.location.href='<?php echo "calendar.php?m=$tngmonth&amp;year=$tngyear&amp;tree=$tree"; ?>';"/>
      </div>
    </form>
    <br clear='all'>
    <br>
    <?php
    if ($tngneedresults) {
      $successcount = 0;
      if ($tngevent) {
        $tngevents = array($tngevent);
      } else {
        $tngevents = array("birth", "altbirth", "death", "burial", "marr", "div");
        if ($ldsOK) {
          $ldsevents = array("seal", "endl", "bapt", "conf", "init");
          $tngevents = array_merge($tngevents, $ldsevents);
        }
        $query = "SELECT tag, eventtypeID FROM $eventtypes_table
          WHERE keep=\"1\" AND type=\"I\" ORDER BY display";
        $result = tng_query($query);
        $dontdo = array("ADDR", "BIRT", "CHR", "DEAT", "BURI", "NAME", "NICK", "TITL", "NSFX", "DIV", "MARR");
        while ($row = tng_fetch_assoc($result)) {
          if (!in_array($row['tag'], $dontdo)) {
            array_push($tngevents, $row['eventtypeID']);
          }
        }
        tng_free_result($result);
      }
      foreach ($tngevents as $tngevent) {
        $allwhere = "";

        $eventsjoin = "";
        $eventsfields = "";
        $needfamilies = "";
        $tngsaveevent = $tngevent;
        switch ($tngevent) {
          case "birth":
            $datetxt = uiTextSnippet('born');
            break;
          case "altbirth":
            $datetxt = uiTextSnippet('christened');
            break;
          case "death":
            $datetxt = uiTextSnippet('died');
            break;
          case "burial":
            $datetxt = uiTextSnippet('buried');
            break;
          case "marr":
            $datetxt = uiTextSnippet('married');
            $needfamilies = 1;
            break;
          case "div":
            $datetxt = uiTextSnippet('divorced');
            $needfamilies = 1;
            break;
          case "seal":
            $datetxt = uiTextSnippet('sealedslds');
            $needfamilies = 1;
            break;
          case "endl":
            $datetxt = uiTextSnippet('endowedlds');
            break;
          case "bapt":
            $datetxt = uiTextSnippet('baptizedlds');
            break;
          case "conf":
            $datetxt = uiTextSnippet('conflds');
            break;
          case "init":
            $datetxt = uiTextSnippet('initlds');
            break;
          default:
            //look up display
            $query = "SELECT display FROM $eventtypes_table
              WHERE eventtypeID=\"$tngevent\" ORDER BY display";
            $evresult = tng_query($query);
            $event = tng_fetch_assoc($evresult);
            $datetxt = getEventDisplay($event['display']);
            tng_free_result($evresult);

            $eventsjoin = ", $events_table";
            $eventsfields = ",info";
            $allwhere .= " AND $people_table.personID = $events_table.persfamID AND $people_table.gedcom = $events_table.gedcom AND eventtypeID = \"$tngevent\"";
            $tngevent = "event";
            break;
        }
        if ($needfamilies) {
          $familiesjoin = " LEFT JOIN $families_table ON ($people_table.gedcom = $families_table.gedcom AND $people_table.personID = $families_table.husband)";
          $familiesjoinw = " LEFT JOIN $families_table ON ($people_table.gedcom = $families_table.gedcom AND $people_table.personID = $families_table.wife)";
          $familiessortdate = ", " . $tngevent . "datetr";
        } else {
          $familiesjoin = "";
          $familiesjoinw = "";
          $familiessortdate = "";
        }

        $datefield = $tngevent . "date";
        $datefieldtr = $tngevent . "datetr";
        $place = $tngevent . "place";

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

        if ($tree) {
          if ($urlstring) {
            $urlstring .= "&amp;";
          }
          $urlstring .= "tree=$tree";

          if ($allwhere) {
            $allwhere = " AND (1=1 $allwhere)";
          }
          $allwhere .= " AND $people_table.gedcom=\"$tree\"";
        }

        $more = getLivingPrivateRestrictions($people_table, false, true);
        if ($more) {
          $allwhere .= " AND " . $more;
        }

        $max_browsesearch_pages = 5;
        if ($offset) {
          $offsetplus = $offset + 1;
          $newoffset = "$offset, ";
        } else {
          $offsetplus = 1;
          $newoffset = "";
          $tngpage = 1;
        }

        //if one event was selected, just do that one
        //if no event was selected, do them each in turn

        $query = "SELECT $people_table.ID, $people_table.personID, lastname, lnprefix, firstname, $people_table.living, $people_table.branch, prefix, suffix, nameorder, $place, $datefield, $people_table.gedcom, treename $familiessortdate $eventsfields
          FROM ($people_table, $trees_table $eventsjoin) $familiesjoin
          WHERE $people_table.gedcom = $trees_table.gedcom $allwhere ";
        if ($needfamilies) {
          $query .= "UNION ALL SELECT $people_table.ID, $people_table.personID, lastname, lnprefix, firstname, $people_table.living, $people_table.branch, prefix, suffix, nameorder, $place, $datefield, $people_table.gedcom, treename $familiessortdate $eventsfields
            FROM ($people_table, $trees_table $eventsjoin) $familiesjoinw
            WHERE $people_table.gedcom = $trees_table.gedcom $allwhere ";
        }
        $query .= " ORDER BY DAY($datefieldtr), MONTH($datefieldtr), YEAR($datefieldtr), lastname, firstname LIMIT $newoffset" . $maxsearchresults;

        //echo "debug: $query<br>\n";
        $result = tng_query($query);
        $numrows = tng_num_rows($result);

        if ($numrows == $maxsearchresults || $offsetplus > 1) {
          if ($needfamilies) {
            $query = "SELECT (SELECT count(personID)
              FROM ($people_table, $trees_table $eventsjoin) $familiesjoin
              WHERE $people_table.gedcom = $trees_table.gedcom $allwhere) +
              (SELECT count(personID)
              FROM ($people_table, $trees_table $eventsjoin) $familiesjoinw
              WHERE $people_table.gedcom = $trees_table.gedcom $allwhere) as pcount";
          } else {
            $query = "SELECT count(personID) as pcount
              FROM ($people_table, $trees_table $eventsjoin) $familiesjoin
              WHERE $people_table.gedcom = $trees_table.gedcom $allwhere";
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

          echo "<p>" . uiTextSnippet('matches') . " $offsetplus " . uiTextSnippet('to') . " $numrowsplus " . uiTextSnippet('of') . " $totrows</p>";
          ?>
          <table class="table table-sm table-striped">
            <tr>
              <td></td>
              <th><?php echo uiTextSnippet('lastfirst'); ?></th>
              <th colspan='2'><?php echo $datetxt; ?></th>
              <th><?php echo uiTextSnippet('personid'); ?></th>
              <?php if ($numtrees > 1) { ?>
                <th><?php echo uiTextSnippet('tree'); ?></th>
              <?php } ?>
            </tr>

            <?php
            $i = $offsetplus;
            $chartlinkimg = getimagesize("img/Chart.gif");
            $chartlink = "<img src=\"img/Chart.gif\" alt='' $chartlinkimg[3]>";
            $treestr = $tngconfig['places1tree'] ? "" : "tree=$tree&amp;";
            while ($row = tng_fetch_assoc($result)) {
              $rights = determineLivingPrivateRights($row);
              $row['allow_living'] = $rights['living'];
              $row['allow_private'] = $rights['private'];
              if ($rights['both']) {
                $placetxt = $row[$place] ? $row[$place] . " <a href='placesearch.php?{$treestr}psearch=" . urlencode($row[$place]) . "' title='" . uiTextSnippet('findplaces') . "'>"
                  . "<img class='icon-xs-inline' src='svg/magnifying-glass.svg' alt='" . uiTextSnippet('findplaces') . "'></a>" : truncateIt($row['info'], 75);
                $dateval = $row[$datefield];
              } else {
                $dateval = $placetxt = $prefix = $suffix = $title = $nickname = $birthdate = $birthplace = $deathdate = $deathplace = $livingOK = "";
              }
              echo "<tr>";
              $name = getNameRev($row);

              echo "<td>$i</td>\n";
              $i++;
              echo "<td>\n";
                echo "<div class='person-img' id=\"mi{$row['gedcom']}_{$row['personID']}_$tngevent\">\n";
                  echo "<div class='person-prev' id=\"prev{$row['gedcom']}_{$row['personID']}_$tngevent\"></div>\n";
                echo "</div>\n";
                echo "<a href=\"pedigree.php?personID={$row['personID']}&amp;tree={$row['gedcom']}\">$chartlink</a> <a href=\"peopleShowPerson.php?personID={$row['personID']}&amp;tree={$row['gedcom']}\" class=\"pers\" id=\"p{$row['personID']}_t{$row['gedcom']}:$tngevent\">$name</a>&nbsp;</td>\n";
              echo "<td>" . displayDate($dateval) . "</td><td>$placetxt</td>";
              echo "<td>{$row['personID']} </td>";
              if ($numtrees > 1) {
                echo "<td><a href=\"showtree.php?tree={$row['gedcom']}\">{$row['treename']}</a></td>";
              }
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
        echo "<p>" . uiTextSnippet('noresults') . ".</p>";
      }
    } //end of $tng_needresults
    ?>
    <?php echo $publicFooterSection->build(); ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
<script src="js/search.js"></script>
<script>
  function resetForm() {
    var myform = document.form1;

    myform.tngevent.selectedIndex = 0;
    myform.tngdaymonth.value = "";
    myform.tngmonth.selectedIndex = 0;
    myform.tngyear.value = "";
    myform.tngkeywords.value = "";
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