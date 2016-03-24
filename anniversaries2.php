<?php
require 'begin.php';
include("genlib.php");
include("getlang.php");

require 'checklogin.php';

$tngyear = ereg_replace("[^0-9]", "", $tngyear);
$tngkeywords = ereg_replace("[^A-Za-z0-9]", "", $tngkeywords);

header("Location: anniversaries.php?tngevent=$tngevent&tngdaymonth=$tngdaymonth&tngmonth=$tngmonth&tngyear=$tngyear&tngkeywords=$tngkeywords&tngneedresults=$tngneedresults&offset=$offset&tree=$tree&tngpage=$tngpage");