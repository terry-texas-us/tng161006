<?php
$locations2map = [];
$l2mCount = 0;
$map['pins'] = 0;

if (!$map['displaytype']) {
  $map['displaytype'] = 'TERRAIN';
}
$pins = ["006.png", "009.png", "023.png", "038.png", "074.png", "122.png", "155.png"];

// these two lines used to remove or replace characters that cause problems with opening new Google maps
$banish = ['(', ')', '#', '&', ' from ', ' to ', ' van ', ' naar ', ' von ', ' bis ', ' da ', ' a ', ' de ', ' ? ', ' vers ', ' till '];
$banreplace = ['[', ']', '', 'and', ' from%A0', ' to%A0', ' van%A0', ' naar%A0', ' von%A0', ' bis%A0', ' da%A0', ' a%A0', ' de%A0', 'à%A0', 'vers%A0', 'till%A0'];

function buildGoogleMapCardHtml($map, $place = '') {
  global $session_charset;
  $html = "<input class='btn btn-sm' type='button' onclick=\"return divbox('mapcontainer');\" value=\"" . uiTextSnippet('showhidemap') . '"> <span>' . uiTextSnippet('getcoords') . "</span>\n";

  $html .= "<div class='card card-block' id='mapcontainer' style='display: none; width:{$map['admw']};'>\n";

  $maphelplang = findhelp('places_googlemap_help.php');
  $html .= "<div class='clearfix'><span class='close'><a href=\"javascript:newwindow=window.open('$maphelplang/places_googlemap_help.php', 'newwindow', 'height=500, width=600, resizable=yes, scrollbars=yes'); newwindow.focus();\">?</a></span></div>\n";

  $html .= '<span>' . uiTextSnippet('googleplace') . '.</span>';
  
  $searchstring = $place ? $place : uiTextSnippet('searchstring');
  $html .= "<input class='form-control form-control-sm' id='location' name='address' type='text' size='60' onkeypress=\"return keyHandlerEnter(this,event);\" value=\"$searchstring\"";
  if (!$place) {
    $html .= " onfocus=\"if(this.value=='$searchstring'){this.value='';}\"";
  }
  $html .= ">\n";
  $html .= "<input class='btn btn-secondary btn-sm' type='button' value=\"" . uiTextSnippet('search') . "\" onclick=\"showAddress(document.form1.address.value); return false\" /><br><br>\n";

  $html .= "<div id='map' style='width: {$map['admw']}; height: {$map['admh']}'></div>\n";
  
  if ($map['externallink'] === true) { // [ts] always false but may use later
    $html .= "<a href=\"javascript:newwindow=window.open('https://maps.google.com/maps?f=q&amp;" . uiTextSnippet('localize') . "&amp;oe=$session_charset&amp;q=" . $place . "', 'googlehelp'); newwindow.focus();\"> " . uiTextSnippet('difficultmap') . "</a>\n";
  }
  
  $html .= "</div>\n";

  return $html;
}

function tng_map_pins() {
  global $locations2map;
  global $map;
  global $defermap;
  global $session_charset;

  $minLat = 500;
  $maxLat = -500;
  $minLong = 500;
  $maxLong = -500;

  reset($locations2map);
  while (list($key, $val) = each($locations2map)) {
    $lat = $val['lat'];
    $long = $val['long'];
    $zoom = $val['zoom'] ? $val['zoom'] : 10;
    if ($lat && $long) {
      if ($lat < $minLat) {
        $minLat = $lat;
      }
      if ($long < $minLong) {
        $minLong = $long;
      }
      if ($lat > $maxLat) {
        $maxLat = $lat;
      }
      if ($long > $maxLong) {
        $maxLong = $long;
      }
    }
  }
  $centLat = $minLat + (($maxLat - $minLat) / 2);
  $centLong = $minLong + ((abs($minLong) - abs($maxLong)) / 2);
  ?>
  <script>
    var maploaded = false;
    <?php if ($minLat == 500) { ?>
      $('#map').hide();
    <?php } ?>

    function attachInfoLocation(locationMarker, locationInfoContent) {
      'use strict';
      var locationInfoWindow = new google.maps.InfoWindow({
        content: locationInfoContent
      });
      
      locationMarker.addListener('click', function() {
        locationInfoWindow.open(locationMarker.get('map'), locationMarker);
      });
    }
  
    function ShowTheMap() {
      'use strict';
      var myOptions = {
        scaleControl: true,
        zoom: <?php echo $zoom; ?>,
        center: new google.maps.LatLng(<?php echo "$centLat, $centLong"; ?>),
        mapTypeId: google.maps.MapTypeId.<?php echo $map['displaytype']; ?>
      };
      var map = new google.maps.Map(document.getElementById('map'), myOptions),
        bounds = new google.maps.LatLngBounds(),
        contentString,
        icon,
        lat,
        long,
        locationLatLng,
        uniquePlace,
        pinPlaceLevel,
        locationMarker,
        markerNum = 0,
        zoom = 10;
      
      <?php
      //do the points
      global $pins;
      reset($locations2map);
      $usedplaces = [];
      $zoom = 10;
      while (list($key, $val) = each($locations2map)) {
        $lat = $val['lat'];
        $long = $val['long'];
        $placelevel = $val['placelevel'];
        if (!placelevel) {
          $placelevel = 0;
        }

        $zoom = $val['zoom'] ? $val['zoom'] : $zoom;
        $uniqueplace = $val['place'] . ' ' . $lat . $long;

        if ($lat && $long && ($map['showallpins'] || !in_array($uniqueplace, $usedplaces))) {
          $usedplaces[] = $uniqueplace;
          $htmlcontent = $val['htmlcontent'];
          ?>
          lat = <?php echo $lat; ?>;
          long = <?php echo $long; ?>;
          uniquePlace = '<?php echo htmlspecialchars($uniqueplace, ENT_QUOTES, $session_charset); ?>';
          pinPlaceLevel = '<?php echo $pins[$placelevel]; ?>';
          contentString = '<?php echo $htmlcontent; ?>';
          
          locationLatLng = new google.maps.LatLng(lat, long);
          markerNum += 1;
          icon = 'google_marker.php?image=' + pinPlaceLevel + '&text=' + markerNum;
          locationMarker = new google.maps.Marker({
            position: locationLatLng, 
            map: map, 
            icon: icon, 
            title: uniquePlace
          });
          attachInfoLocation(locationMarker, contentString);
          bounds.extend(locationLatLng);
        <?php
        }
      }
      ?>
      if (markerNum > 1) {
        zoom = <?php echo $zoom; ?>;
        map.fitBounds(bounds);
        google.maps.event.addListenerOnce(map, 'bounds_changed', function(event) {
          if (map.getZoom() > zoom) {
            map.setZoom(zoom);
          }
        });
      } else {
        map.setCenter(bounds.getCenter());
        map.setZoom(zoom);
      }
      maploaded = true;
    }
    <?php if (!isset($defermap) || !$defermap) { ?>
      function displayMap() {
        if ($('#map').length) {
          ShowTheMap();
        }
      }
      window.onload=displayMap;
    <?php } ?>
  </script>
  <?php
}
