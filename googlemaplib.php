<?php
$locations2map = [];
$l2mCount = 0;
$map['pins'] = 0;
if (!$map['displaytype']) {
  $map['displaytype'] = "TERRAIN";
}
// these two lines used to remove or replace characters that cause problems with opening new Google maps
$banish = ["(", ")", "#", "&", " from ", " to ", " van ", " naar ", " von ", " bis ", " da ", " a ", " de ", " ? ", " vers ", " till "];
$banreplace = ["[", "]", "", "and", " from%A0", " to%A0", " van%A0", " naar%A0", " von%A0", " bis%A0", " da%A0", " a%A0", " de%A0", "�%A0", "vers%A0", "till%A0"];

function tng_map_pins() {
  global $locations2map;
  global $pinplacelevel0;
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
    $pinplacelevel = $val['pinplacelevel'];
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
      reset($locations2map);
      $usedplaces = [];
      $zoom = 10;
      while (list($key, $val) = each($locations2map)) {
        $lat = $val['lat'];
        $long = $val['long'];
        $pinplacelevel = $val['pinplacelevel'];

        if (!$pinplacelevel) {
          $pinplacelevel = $pinplacelevel0;
        }
        $zoom = $val['zoom'] ? $val['zoom'] : $zoom;
        $uniqueplace = $val['place'] . " " . $lat . $long;

        if ($lat && $long && ($map['showallpins'] || !in_array($uniqueplace, $usedplaces))) {
          $usedplaces[] = $uniqueplace;
          $htmlcontent = $val['htmlcontent'];
          ?>
          lat = <?php echo $lat; ?>;
          long = <?php echo $long; ?>;
          uniquePlace = '<?php echo htmlspecialchars($uniqueplace, ENT_QUOTES, $session_charset); ?>';
          pinPlaceLevel = '<?php echo $pinplacelevel; ?>';
          contentString = '<?php echo $htmlcontent; ?>';
          
          locationLatLng = new google.maps.LatLng(lat, long);
          markerNum += 1;
          icon = 'google_marker.php?image=' + pinPlaceLevel + '.png&text=' + markerNum;
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

function stri_replace($find, $replace, $string) {
  if (!is_array($find)) {
    $find = [$find];
  }
  if (!is_array($replace)) {
    if (!is_array($find)) {
      $replace = [$replace];
    } else {
      // this will duplicate the string into an array the size of $find
      $c = count($find);
      $rString = $replace;
      unset($replace);
      for ($i = 0; $i < $c; $i++) {
        $replace[$i] = $rString;
      }
    }
  }
  foreach ($find as $fKey => $fItem) {
    $between = explode(strtolower($fItem), strtolower($string));
    $pos = 0;
    foreach ($between as $bKey => $bItem) {
      $between[$bKey] = substr($string, $pos, strlen($bItem));
      $pos += strlen($bItem) + strlen($fItem);
    }
    $string = implode($replace[$fKey], $between);
  }
  return ($string);
}
