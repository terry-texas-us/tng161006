<?php
$database_host = "localhost";
$database_name = "tgn";
$database_username = "root";
$database_password = 'tas1223';
$tngconfig['maint'] = "";

$people_table = "people";
$families_table = "families";
$children_table = "children";
$albums_table = "albums";
$album2entities_table = "albumplinks";
$albumlinks_table = "albumlinks";
$media_table = "media";
$medialinks_table = "medialinks";
$mediatypes_table = "mediatypes";
$address_table = "addresses";
$languages_table = "languages";
$cemeteries_table = "cemeteries";
$states_table = "states";
$countries_table = "countries";
$places_table = "places";
$sources_table = "sources";
$repositories_table = "repositories";
$citations_table = "citations";
$events_table = "events";
$eventtypes_table = "eventtypes";
$rectangles_table = "";
$reports_table = "reports";
$trees_table = "trees";
$notelinks_table = "notelinks";
$xnotes_table = "xnotes";
$saveimport_table = "saveimport";
$users_table = "users";
$temp_events_table = "temp_events";
$tlevents_table = "timelineevents";
$branches_table = "branches";
$branchlinks_table = "branchlinks";
$assoc_table = "associations";
$mostwanted_table = "mostwanted";

$rootpath = "C:/apache/htdocs/tng/";
$homepage = "index.php";
$tngdomain = "http://localhost/tng";
$sitename = "";
$site_desc = "";
$tngconfig['doctype'] = "";
$target = "_self";
$language = "English-UTF8";
$charset = "UTF-8";
$maxsearchresults = "50";
$lineendingdisplay = "\\r\\n";
$lineending = "\r\n";
$gendexfile = "gendex";
$mediapath = "media";
$headstonepath = "headstones";
$historypath = "histories";
$backuppath = "backups-ss";
$documentpath = "documents";
$photopath = "photos";
$photosext = "jpg";
$showextended = "1";
$tngconfig['imgmaxh'] = "";
$tngconfig['imgmaxw'] = "";
$thumbprefix = "";
$thumbsuffix = "_tn";
$thumbmaxh = "100";
$thumbmaxw = "80";
$tngconfig['usedefthumbs'] = "1";
$tngconfig['thumbcols'] = "10";
$tngconfig['maxnoteprev'] = "";
$tngconfig['ssdisabled'] = "0";
$tngconfig['ssrepeat'] = "0";
$tngconfig['imgviewer'] = "0";
$tngconfig['imgvheight'] = "0";
$tngconfig['hidemedia'] = "0";
$customheader = "";
$custommeta = "";
$tngconfig['tabs'] = "";
$tngconfig['menu'] = "0";
$tngconfig['istart'] = "0";
$tngconfig['showhome'] = "1";
$tngconfig['showsearch'] = "1";
$tngconfig['searchchoice'] = "1";
$tngconfig['showlogin'] = "0";
$tngconfig['showshare'] = "0";
$tngconfig['showprint'] = "0";
$tngconfig['showbmarks'] = "0";
$tngconfig['hidechr'] = "0";
$tngconfig['password_type'] = "md5";
$tngconfig['places1tree'] = "0";
$tngconfig['autogeo'] = "0";
$dbowner = "Terry";
$time_offset = "0";
$tngconfig['edit_timeout'] = "";
$requirelogin = "0";
$treerestrict = "0";
$livedefault = "0";
$ldsdefault = "0";
$chooselang = "1";
$nonames = "0";
$tngconfig['nnpriv'] = "0";
$notestogether = "2";
$tngconfig['scrollcite'] = "0";
$nameorder = "";
$tngconfig['ucsurnames'] = "0";
$lnprefixes = "0";
$lnpfxnum = "";
$specpfx = "";
$tngconfig['cemrows'] = "";
$tngconfig['cemblanks'] = "0";
$emailaddr = "t.smith@swbell.net";
$tngconfig['fromadmin'] = "0";
$tngconfig['disallowreg'] = "0";
$tngconfig['revmail'] = "1";
$tngconfig['autoapp'] = "0";
$tngconfig['autotree'] = "0";
$tngconfig['ackemail'] = "0";
$tngconfig['omitpwd'] = "0";
$tngconfig['usesmtp'] = "0";
$tngconfig['mailhost'] = "";
$tngconfig['mailuser'] = "";
$tngconfig['mailpass'] = "";
$tngconfig['mailport'] = "";
$tngconfig['mailenc'] = "";
$maxgedcom = "8";
$change_cutoff = "0";
$change_limit = "10";
$tngconfig['preferEuro'] = "false";
$tngconfig['calstart'] = "0";
$tngconfig['pardata'] = "2";
$tngconfig['oldids'] = "";
$tngconfig['lastimport'] = "";
$defaulttree = "twall";
$tng_notinstalled = "";


@include($subroot . "customconfig.php");
?>