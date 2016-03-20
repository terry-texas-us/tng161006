<?php
ini_set("auto_detect_line_endings", "1");
$umfs = substr(ini_get('upload_max_filesize'), 0, -1);
if ($umfs < 10) {
  ini_set('upload_max_filesize', '10M');
}

include("begin.php");
include("adminlib.php");

$admin_login = 1;
include("checklogin.php");
include("version.php");

if (!$allow_add || !$allow_add || !$allow_edit || $assignedbranch) {
  $message = uiTextSnippet('norights');
  header("Location: admin_login.php?message=" . urlencode($message));
  exit;
}
include($subroot . "importconfig.php");
require("adminlog.php");
$today = date("Y-m-d H:i:s");

global $prefix;

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('datamaint'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build('', 'admin', $session_charset); ?>
<body>
  <section class='container'>
    <?php
    echo $adminHeaderSection->build('datamaint-gedimport', $message);
    $navList = new navList('');
    $navList->appendItem([true, "dataImportGedcom.php", uiTextSnippet('import'), "import"]);
    $navList->appendItem([true, "dataExportGedcom.php", uiTextSnippet('export'), "export"]);
    $navList->appendItem([true, "dataSecondaryProcesses.php", uiTextSnippet('secondarymaint'), "second"]);
    echo $navList->build("import");

    $pciteevents = array("NAME", "BIRT", "CHR", "SEX", "DEAT", "BURI", "BAPL", "CONL", "INIT", "ENDL", "SLGC", "NICK", "NSFX", "TITL", "CHAN", "NPFX", "NSFX", "FAMC", "FAMS", "OBJE", "IMAGE", "SOUR", "ASSO", "_LIVING");
    $fciteevents = array("HUSB", "WIFE", "MARR", "DIV", "SLGS", "CHAN", "CHIL", "OBJE", "SOUR", "ASSO", "_LIVING");
    $sciteevents = array("ABBR", "AUTH", "CALN", "PUBL", "TITL", "CHAN", "DATA", "TEXT", "OBJE", "REPO");
    $rciteevents = array("NAME", "ADDR", "CHAN", "OBJE");

    set_time_limit(0);
    if ($remotefile && $remotefile != "none") {
      $fp = fopen($remotefile, "r");
      if ($fp === false) {
        die(uiTextSnippet('cannotopen') . " $remotefile");
      }
      echo "$remotefile " . uiTextSnippet('opened') . "<br>\n";
      $savestate['filename'] = $remotefile;
    } else {
      if ($database) {
        $localfile = $gedpath == "admin" || $gedpath == "" ? $database : "$rootpath$gedpath/$database";
        $fp = fopen($localfile, "r");
        if (!$fp) {
          die(uiTextSnippet('cannotopen') . " r=$rootpath, g=$gedpath, l=$localfile");
        }
        echo "$database " . uiTextSnippet('opened') . "<br>\n";
        $savestate['filename'] = $localfile;
      }
    }
    if ($savestate['filename']) {
      $tree = $tree1; //selected
    }
    ?>
    <p><strong><?php echo uiTextSnippet('importinggedcom'); ?></strong></p>
    <?php
    //get custom event types
    $query = "SELECT eventtypeID, tag, description, keep, type, display FROM $eventtypes_table";
    $result = tng_query($query);
    $custeventlist = array();
    while ($row = tng_fetch_assoc($result)) {
      $eventtype = $row['type'] . "_" . $row['tag'] . "_" . $row['description'];
      if ($row['keep'] && !in_array($eventtype, $custeventlist)) {
        array_push($custeventlist, $eventtype); //used to be $row['tag']
      }
    }
    tng_free_result($result);

    $eventctr = 0;

    function getLine() {
      global $fp, $lineending;

      $lineinfo = array();
      if ($line = ltrim(fgets($fp, 1024))) {
        $patterns = array("/��.*��/", "/��.*/", "/.*��/", "/@@/");
        $replacements = array("", "", "", "@");
        $line = preg_replace($patterns, $replacements, $line);

        preg_match("/^(\d+)\s+(\S+) ?(.*)$/", $line, $matches);

        $lineinfo['level'] = trim($matches[1]);
        $lineinfo['tag'] = trim($matches[2]);
        $lineinfo['rest'] = trim($matches[3], $lineending);
      } else {
        $lineinfo['level'] = "";
        $lineinfo['tag'] = "";
        $lineinfo['rest'] = "";
      }
      if (!$lineinfo['tag'] && !feof($fp)) {
        $lineinfo = getLine();
      }

      return $lineinfo;
    }

    function getContinued() {
      global $lineinfo;

      $continued = "";
      $notdone = 1;

      while ($notdone) {
        $lineinfo = getLine();
        if ($lineinfo['tag'] == "CONC") {
          $continued .= addslashes($lineinfo['rest']);
        } elseif ($lineinfo['tag'] == "CONT") {
          //if( $continued ) $lineinfo['rest'] = "\n$lineinfo['rest']";
          $continued .= addslashes("\n" . $lineinfo['rest']);
        } else {
          $notdone = 0;
        }
      }
      return $continued;
    }

    function lookForEvents($prefix, $stdarray) {
      global $lineinfo;
      global $custeventlist;
      global $eventctr;
      global $eventtypes_table;

      $lineinfo = getLine();
      while ($lineinfo['tag'] && $lineinfo['level'] >= 1) {
        if ($lineinfo['level'] == 1) {
          $tag = $lineinfo['tag'];
          if (!in_array($tag, $stdarray)) {
            if ($tag == "EVEN") {
              $fact = addslashes($lineinfo['rest'] . getContinued());
              //next one must be TYPE
              //$lineinfo = getLine();
              if ($lineinfo['tag'] == "TYPE") {
                $type = trim(addslashes($lineinfo['rest']));
              } else {
                if ($fact) {
                  $type = $fact;
                } else {
                  do {
                    $lineinfo = getLine();
                  } while ($lineinfo['tag'] != "TYPE");
                  $type = trim(addslashes($lineinfo['rest']));
                }
              }
              $display = $type;
            } else {
              $type = "";
              $display = "";
            }
            $thisevent = $prefix . "_" . $tag . "_" . $type;

            if (!in_array($thisevent, $custeventlist)) {
              array_push($custeventlist, $thisevent);
              if (!$display) {
                $display = uiTextSnippet($tag) ? uiTextSnippet($tag) : $tag;
              }
              $query = "INSERT IGNORE INTO $eventtypes_table (tag, description, display, keep, type)  VALUES(\"$tag\", \"$type\", \"$display\", \"0\", \"$prefix\")";
              $result = @tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query");

              $eventctr++;
              echo "<strong>$eventctr</strong> ";
            }
          }
        }
        $lineinfo = getLine();
      }
    }

    $lineinfo = getLine();
    while ($lineinfo['tag']) {
      if ($lineinfo['level'] == 0) {
        preg_match("/^@(\S+)@/", $lineinfo['tag'], $matches);
        $id = $matches[1];
        switch ($lineinfo['rest']) {
          case "FAM":
            lookForEvents('F', $fciteevents);
            break;
          case "INDI":
            lookForEvents('I', $pciteevents);
            break;
          case "SOUR":
            lookForEvents('S', $sciteevents);
            break;
          case "REPO":
            lookForEvents("REPO", $rciteevents);
            break;
          default:
            $lineinfo = getLine();
            break;
        }
      } else {
        $lineinfo = getLine();
      }
    }
    @fclose($fp);
    ?>
    <span>
      <br><br>
      <?php
      adminwritelog(uiTextSnippet('datamaint') . ": $eventctr " . uiTextSnippet('eventtypes'));
      echo uiTextSnippet('finishedimporting') . "<br>$eventctr " . uiTextSnippet('eventtypes');
      ?>
      <br>
    </span>

    <?php
    echo "<p><a href=\"dataImportGedcom.php\">" . uiTextSnippet('backtodataimport') . "</a></p>";

    echo "<div align=\"right\"><span>$tng_title, v.$tng_version</span></div>";
    ?>
  </section> <!-- .container -->
</body>
</html>

