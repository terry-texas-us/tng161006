<?php
require '../../helplib.php';
echo help_header("Help: Events");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Events</h2>
    <h4>Standard vs. Custom Events</h4>
    <p>The more common events, such as Birth, Death, Marriage and a few others, are entered on the main People, Families, Sources and Repositories pages
    and are stored in their respective database tables.	TNG documentation refers to those events as "Standard" events. All other events are called "Custom" events
    and are managed in the <strong>Other Events</strong> sections of the People, Families, Sources and Repositories pages. Those events are stored in a separate
    Events table. This Help topic refers to the management of those <em>Custom</em> events.</p>

    <h4>Adding Events</h4>

    <p>To add a new event, click on the "Add New" button in the Other Events section, then fill out the form. When events already exist, they will
      be displayed in a table in the Other Events section. For an explanation on the available fields, see the next section below.</p>

    <h4>Editing Events</h4>

    <p>To edit an existing event, click on the Edit icon next to that event in the Other Events section (to edit the data for a "standard" event
      like Birth or Death, simply change the text).</p>

    <p>While adding or editing a note, please take note of the following:</p>

    <span>Event Type</span>
    <p>Select the type of event (you cannot change the event type for an existing event). If the Event Type you want is not in the Event Type selection box,
      first go to Admin/Custom Event Types and set up that Event Type, then return to this screen to select it.</p>

    <span>Event Date</span>
    <p>The actual or approximated date associated with the event.</p>

    <span>Event Place</span>
    <p>The place where the event occurred. Enter the place name or click the Find icon (the magnifying glass) to locate the event as you entered it previously.</p>

    <span>Detail</span>
    <p>Any additional explanation of the event, if necessary. If no date or place is associated with the event, the Detail field should contain some defining information.</p>

    <span>More</span><br />
    <p>More less commonly used information can be added for each event by clicking on the "More" heading or the arrow next to it. Doing so will cause these fields
      to appear. The fields can be hidden by again clicking on the heading or arrow. Hiding the fields does not remove any information entered there. Those fields include:</p>

    <p><span>Age</span>: The age of the individual at the time of the event.</p>

    <p><span>Agency</span>: The institution or individual having authority and/or responsibility at the time of the event.</p>

    <p><span>Cause</span>: The cause of the event (most often used with Death).</p>

    <p><span>Address 1/Address 2/City/State/Province/Zip/Postal Code/Country/Phone/E-mail/Web Site</span>: The address and other contact information associated with the event.</p>

    <span>Required fields:</span>
    <p>You must choose an Event Type, and you must enter something in at least one of the following fields: <strong>Event Date</strong>, <strong>Event Place</strong>,
      or <strong>Detail</strong>. All other information is optional.</p>

    <h4>Deleting Events</h4>

    <p>To edit an existing event, click on the Edit icon next to that event in the Other Events section (to edit the data for a "standard" event
      like Birth or Death, simply change the text). The event will be deleted, regardless of whether the surrounding page is saved.</p>

    <h4>Notes and Citations</h4>
    <p>To add or edit notes or citations for an event, first save the event, then click the appropriate icon next to that event record in the current list of events.
      For more information on notes, please see <a href="notes_help.php">Help: Notes</a>. 
      For more information on citations, please see <a href="citations_help.php">Help: Citations</a>.</p>

</section> <!-- .container -->
</body>
</html>
