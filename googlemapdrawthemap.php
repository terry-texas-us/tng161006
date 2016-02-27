<?php

echo "<input type='button' onclick=\"return divbox('mapcontainer');\" value=\"" . uiTextSnippet('showhidemap') . "\"> <span>" . uiTextSnippet('getcoords') . "</span>\n";

echo "<div id='mapcontainer' style=\"display: none; width:{$map['admw']};\" class='mappad5 rounded10'>\n";
$searchstring = $row['place'] ? $row['place'] : uiTextSnippet('searchstring');
echo "<span>" . uiTextSnippet('googleplace') . '.';

echo "<input id='location' name='address' type='text' size='60' onkeypress=\"return keyHandlerEnter(this,event);\" value=\"$searchstring\"";
if (!$row['place']) {
  echo " onfocus=\"if(this.value=='$searchstring'){this.value='';}\"";
}
echo ">\n";
echo "<input type='button' value=\"" . uiTextSnippet('gobutton') . "\" onclick=\"showAddress(document.form1.address.value); return false\" /><br><br></span>\n";

echo "<div id='map' style=\"width: {$map['admw']}; height: {$map['admh']}\" class='rounded10'></div>\n";
$maphelplang = findhelp("places_googlemap_help.php");
echo "<span><br><a href=\"javascript:newwindow=window.open('https://maps.google.com/maps?f=q" . uiTextSnippet('glang') .
        "$mcharsetstr&q=" . $row['place'] . "', 'googlehelp'); newwindow.focus();\"> " .
        uiTextSnippet('difficultmap') . "</a> | <a href=\"javascript:newwindow=window.open('$maphelplang/places_googlemap_help.php', 'newwindow', 'height=500,width=600,resizable=yes,scrollbars=yes'); newwindow.focus();\">" .
        uiTextSnippet('maphelp') . "</a></span>\n";
echo "</div>\n";
