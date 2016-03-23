<?php
include($subroot . "mapconfig.php");

$base_url = "https://maps.googleapis.com/maps/api/geocode/xml";
$phpversion = phpversion();
$foundzoom = $map['foundzoom'] ? $map['foundzoom'] : 13;

function geocode($address, $multiples, $id) {
  global $base_url;
  global $phpversion;
  global $foundzoom;
  global $places_table;

  $geocode_pending = true;
  $message = "";

  while ($geocode_pending) {
    $request_url = $base_url . "&address=" . urlencode($address);
    if (ini_get('allow_url_fopen')) {
      $xml = simplexml_load_file($request_url);
    } else {
      $ch = curl_init($request_url);
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $xml_raw = curl_exec($ch);
      $xml = simplexml_load_string($xml_raw);
    }

    if ($xml) {
      $status = $xml->status;
      if ($status == "OK") {
        // Successful geocode

        $geocode_pending = false;
        if ($phpversion >= "5.3.0") {
          $placecount = $xml->result->count();
        } else {
          $doc = new DOMDocument();
          $str = $xml->asXML();
          $doc->loadXML($str);
          $placemarks = $doc->getElementsByTagName("result");
          $placecount = $placemarks->length;
        }
        if ($placecount == 1 || $multiples) {
          $lat = $xml->result->geometry->location->lat;
          $lng = $xml->result->geometry->location->lng;

          $query = "UPDATE $places_table SET latitude = \"$lat\", longitude = \"$lng\", zoom = \"$foundzoom\" WHERE ID = \"$id\"";
          $result2 = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query " . tng_error());

          $message = "$lat, $lng  &mdash; <a href=\"admin_editplace.php?ID=$id&amp;cw=1\" target='_blank'>" . uiTextSnippet('edit') . "</a>";
        } else {
          $query = "UPDATE $places_table SET geoignore = \"1\" WHERE ID = \"$id\"";
          $result2 = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query " . tng_error());

          $message = "<strong>" . uiTextSnippet('toomany') . "</strong> &mdash; <a href=\"admin_editplace.php?ID=$id&amp;cw=1\" target='_blank'>" . uiTextSnippet('edit') . "</a>";
        }
        if ($delay) {
          $delay -= 20000;
        }
      } else {
        if (strcmp($status, "OVER_QUERY_LIMIT") == 0 && $delay < 400000) {
          // sent geocodes too fast
          $delay += 100000;
        } else {
          // failure to geocode
          $geocode_pending = false;
          $query = "UPDATE $places_table SET geoignore = \"1\" WHERE ID = \"$id\"";
          $result2 = tng_query($query) or die(uiTextSnippet('cannotexecutequery') . ": $query" . tng_error());

          $message = "<strong>" . uiTextSnippet('nogeocode') . " ($status)</strong> &mdash; <a href=\"admin_editplace.php?ID=$id&amp;cw=1\" target='_blank'>" . uiTextSnippet('edit') . "</a>";
        }
      }
      if ($delay) {
        usleep($delay);
      }
    } else {
      $geocode_pending = false;
      $message = "<strong>Communication failed</strong>";
    }
  }
  return $message;
}
