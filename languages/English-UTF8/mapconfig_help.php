<?php
require '../../helplib.php';
echo help_header("Help: Map Settings");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Map Settings</h2>
    <span>Enable Maps</span>
    <p>Set this option to "Yes" to show Google Maps on your pages when latitude/longitude coordinates are present (in other words, even if you've enabled
      this option, you still won't see any maps if none of your Place records has been geocoded).</p>

    <span>Map Type</span>
    <p>Choose which type of map will be displayed first: Terrain, Road Map, Satellite or Hybrid (a satellite image with streets laid out
      on top).</p>

    <span>Starting Latitude, Starting Longitude</span>
    <p>These coordinates determine where the default "center" of the map is for any place that does not yet have any assigned coordinates. The pin
      will start at that location.</p>

    <span>Starting Zoom</span>
    <p>This number indicates how close up or far away new Google Maps in the Admin area should be displayed to begin with. Lower numbers mean that the
      view is farther away, while higher numbers mean the view is closer. Once the zoom is saved for a particular map, it will be saved with that map.</p>

    <span>Location Zoom</span>
    <p>This number indicates how close up or far away a Google Map in the Admin area should be displayed after a location is searched for and located.</p>

    <span>Dimensions, Individual Page</span>
    <p>Enter the dimensions (width must be in pixels with "px" at the end, or as a percentage; height must be in pixels with "px" at the end) for the map
      displayed on each person's individual page.	For example, to make the map be 500 pixels high, set the <strong>Height</strong> to 500px. To make the map reach 80 percent
      of the way across the allotted area, set the <strong>Width</strong> to 80%.</p>

    <span>Dimensions, Headstones Pages</span>
    <p>Enter the dimensions for the maps displayed on all headstone-related pages (width must be in pixels with "px" at the end, or as a percentage;
      height must be in pixels with "px" at the end)</p>

    <span>Dimensions, Admin Pages</span>
    <p>Enter the dimensions for the maps displayed on all Admin pages (width must be in pixels with "px" at the end, or as a percentage; height
      must be in pixels with "px" at the end).</p>

    <span>Hide Admin Maps to Start</span>
    <p>To hide the maps on the Admin pages until the <span class="emphasis">Show/Hide</span> button is clicked, select <span class="choice">Yes</span> here. To
      have the maps displayed by default when the pages are displayed, select <span class="choice">No</span>.</p>

    <span>Hide Public Maps to Start</span>
    <p>To delay loading the map on the individual person pages until the user calls for it, select <span class="choice">Yes</span> here. This will allow
      the page to load more quickly. The map will be loaded as soon as the <span class="emphasis">Show the map</span> button is clicked.  
      If you select <span class="choice">No</span>, then the map on the person page will always load be shown when the page loads.</p>

    <span>Consolidate Duplicate Pins</span>
    <p>If multiple events for an individual occurred at the same location, setting this option to <span class="emphasis">Yes</span> will prevent duplicate pins from being
      created for non-unique place names. Note: Setting this option to <span class="emphasis">No</span> will cause duplicate pins to obstruct each other.</p>

    <span>Place Levels Pins: Labels and Colors</span>
    <p>Each geocode location can be associated with one of six <strong>Place Levels</strong> (e.g., Location, Town/City, County/Shire, etc.). The labels for these
      levels can be found in the "alltext.php" file in each language folder, and you may override them in your "cust_text.php" file (also in each language folder).</p>

    <p>The pin colors are determined by values set in mapconfig.php. If you would like to change the pin colors, go to the TNG downloads page
      and download the full palette of 216 different pin colors, then open your mapconfig.php file in a text editor and enter the number of the
      new pin color next to the corresponding place level variable. Finally, upload the new pin image file(s) to the <span class="emphasis">googlemaps</span> folder on your site.</p>

  </section> <!-- .container -->
</body>
</html>