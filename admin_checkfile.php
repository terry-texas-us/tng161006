<?php

require 'begin.php';

require 'checklogin.php';

initMediaTypes();

$usefolder = $usecollfolder ? $mediatypes_assoc[$mediatypeID] : $mediapath;

$rval = file_exists("$rootpath$usefolder/$path") ? "true" : "false";

echo $rval;
