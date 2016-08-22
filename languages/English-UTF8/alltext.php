<?php
$alltextloaded = 1;

$dates['JAN'] = "Jan";
$dates['JANUARY'] = "January";
$dates['FEB'] = "Feb";
$dates['FEBRUARY'] = "February";
$dates['MAR'] = "Mar";
$dates['MARCH'] = "March";
$dates['APR'] = "Apr";
$dates['APRIL'] = "April";
$dates['MAY'] = "May";
$dates['JUN'] = "Jun";
$dates['JUNE'] = "June";
$dates['JUL'] = "Jul";
$dates['JULY'] = "July";
$dates['AUG'] = "Aug";
$dates['AUGUST'] = "August";
$dates['SEP'] = "Sep";
$dates['SEPTEMBER'] = "September";
$dates['OCT'] = "Oct";
$dates['OCTOBER'] = "October";
$dates['NOV'] = "Nov";
$dates['NOVEMBER'] = "November";
$dates['DEC'] = "Dec";
$dates['DECEMBER'] = "December";
$dates['ABT'] = "Abt";
$dates['ABOUT'] = "About";
$dates['BEF'] = "Bef";
$dates['BEFORE'] = "Before";
$dates['AFT'] = "Aft";
$dates['AFTER'] = "After";
$dates['BET'] = "Between";
$dates['BETWEEN'] = "Between";
$dates['TEXT_AND'] = "and";
$dates['FROM'] = "From";
$dates['TO'] = "to";
$dates['Y'] = "Yes, date unknown";
$dates['CAL'] = "Cal";
$dates['EST'] = "Est";

//global messages
$text['cannotexecutequery'] = "Cannot execute query";
$text['to'] = "to";
$text['of'] = "of";
$text['text_next'] = "Next";
$text['text_prev'] = "Prev";
$text['clickdisplay'] = "Click to display";
$text['clickhide'] = "Click to hide";
$text['forgot1'] = "<strong>Forgot your username or password?</strong><br />Enter your e-mail address below to have your username sent to you.";
$text['forgot2'] = "Enter your e-mail above and your username below to have your password reset (a temporary password will be sent to you).";
$text['newpass'] = "Your new temporary password";
$text['usersent'] = "Your username has been sent to your e-mail address.";
$text['pwdsent'] = "Your new temporary password has been sent to your e-mail address.";
$text['loginnotsent2'] = "The e-mail address you provided does not match any user account currently on record. No information has been sent.";
$text['loginnotsent3'] = "The e-mail address and username you provided do not match any user account currently on record. No information has been sent.";
$text['logininfo'] = "Your login information";
$text['collapseall'] = "Collapse all";
$text['expandall'] = "Expand all";

//media types
$text['photos'] = "Photos";
$text['documents'] = "Documents";
$text['headstones'] = "Headstones";
$text['histories'] = "Histories";
$text['recordings'] = "Recordings";
$text['videos'] = "Videos";

//For Google maps use - admin and public pages
$admtext['placelevel'] = "Place Level";
$admtext['level1'] = "Address";
$admtext['level2'] = "Location";
$admtext['level3'] = "City/Town";
$admtext['level4'] = "County/Shire";
$admtext['level5'] = "State/Province";
$admtext['level6'] = "Country";
$admtext['level0'] = "Not Set";

$text['male'] = "Male";
$text['female'] = "Female";
$text['closewindow'] = "Close Window";
$text['loading'] = "Loading...";

$text['cancel'] = "Cancel";
$text['none'] = "None";
$text['mainton'] = "Maintenance Mode is ON";

//moved here in 8.0.0
$text['living'] = "Living";
$admtext['text_private'] = "Private";
$admtext['confunlink'] = "Are you sure you want to unlink this individual as a spouse in this family?";
$admtext['confunlinkc'] = "Are you sure you want to unlink this individual as a child in this family?";
$admtext['confremchild'] = "Are you sure you want to remove this child from this family? The individual will not be deleted from the database.";
$admtext['enterfamilyid'] = "Please enter a Family ID.";
$admtext['yes'] = "Yes";
$admtext['no'] = "No";
$admtext['BIRT'] = "Birth";
$admtext['DEAT'] = "Death";
$admtext['CHR'] = "Christening";
$admtext['BURI'] = "Burial";
$admtext['BAPL'] = "Baptism (LDS)";
$admtext['ENDL'] = "Endowment (LDS)";
$admtext['NICK'] = "Nickname";
$admtext['TITL'] = "Title";
$admtext['NSFX'] = "Suffix";
$admtext['NAME'] = "Name";
$admtext['SLGC'] = "Sealed to Parents (LDS)";
$admtext['MARR'] = "Married";
$admtext['SLGS'] = "Sealed to Spouse (LDS)";
$admtext['hello'] = "Hello";
$admtext['activated'] = "Your genealogy user account has been activated.";
$admtext['infois'] = "Your login information is";
$admtext['subjectline'] = "Your genealogy user account has been activated.";

//moved here in 8.1.0
$admtext['adopted'] = "Adopted";
$admtext['birth'] = "Birth";
$admtext['foster'] = "Foster";
$admtext['sealing'] = "Sealing";

//added in 9.0.0
$text['editprofile'] = "Edit Profile";

//moved here in 9.0.0
$text['letter'] = "Letter";
$text['legal'] = "Legal";
$text['sunday'] = "Sunday";
$text['monday'] = "Monday";
$text['tuesday'] = "Tuesday";
$text['wednesday'] = "Wednesday";
$text['thursday'] = "Thursday";
$text['friday'] = "Friday";
$text['saturday'] = "Saturday";
$text['contains'] = "contains";
$text['startswith'] = "starts with";

//moved here in 9.0.4
$text['top'] = "Top";

//moved here in 10.0.0
$text['startingind'] = "Starting Individual";
$text['enteremail'] = "Please enter a valid e-mail address.";
$text['page'] = "Page";
$text['go'] = "Go";
$text['years'] = "years";

//added in 10.0.0
$admtext['CONL'] = "Confirmation (LDS)";
$admtext['INIT'] = "Initiatory (LDS)";
$admtext['step'] = "Stepchild";
$text['switchm'] = "Switch to mobile site";
$text['switchs'] = "Switch to standard site";

//$english = $session_charset == "UTF-8" ? "English-UTF8" : "English";
//@include($rootpath . $endrootpath . "languages/$english/cust_text.php");
include("cust_text.php");