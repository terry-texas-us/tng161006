<?php
require 'begin.php';
require 'genlib.php';
require 'getlang.php';

require 'checklogin.php';

$tngyear = preg_replace('[^0-9]', '', $tngyear);
$tngkeywords = preg_replace('[^A-Za-z0-9]', '', $tngkeywords);

header("Location: anniversaries.php?tngevent=$tngevent&tngdaymonth=$tngdaymonth&tngmonth=$tngmonth&tngyear=$tngyear&tngkeywords=$tngkeywords&tngneedresults=$tngneedresults&offset=$offset&tngpage=$tngpage");