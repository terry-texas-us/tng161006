<?php
require '../../helplib.php';
echo help_header("Help: Timeline Events");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Timeline Events</h2>
    <h4>Search</h4>
    <p>Locate existing timeline events by searching for all or part of the <strong>Event Year</strong> or <strong>Event Detail</strong>.
      Searching with no value in the search box will find all timeline events in your database.</p>

    <p>Your search criteria for this page will be remembered until you click the <strong>Reset</strong> button, which restores all default values and searches again.</p>

    <span>Actions</span>
    <p>The Action buttons next to each search result allow you to edit or delete that result. To delete more than one timeline event at a time, click the box in the
      <strong>Select</strong> column for each event to be deleted, then click the "Delete Selected" button at the top of the list. Use the <strong>Select All</strong> or <strong>Clear All</strong>
      buttons to toggle all select boxes at once.</p>

    <h4>Add New / Edit Existing Timeline Events</h4>
    <p>TNG allows you to display timeline charts to compare the lifespans of people in your database.
      You can also create Timeline Events to provide additional context for these charts. When the years
      covered by a timeline chart include the dates associated with these events, they are displayed as
      footnotes on chart. These events are for use within TNG only, as they cannot be exported in a GEDCOM file.</p>

    <p>To add a new timeline event, click on the <strong>Add New</strong> tab, then fill out the form. To make changes to an existing event, use
      the <a href="#search">Search</a> tab to locate the event, then click on the Edit icon next to that line.
      When adding or editing a timeline event, take note of the following:</p>

    <span>Start Date / End Date</span>
    <p>Select all known components (day, month, year) of the event Start and End Dates. Only the year of the Start Date is required.
      If any part of the End Date is entered, the year is required there as well.</p>

    <span>Event Title</span><br />
    <p>Enter a very short title for the event. For example, <em>Sinking of the Titanic</em> or <em>World War I</em>. This field was added in TNG 9.0. Timeline
      events added prior to that version will not have a title. In those cases the EVent Detail will be used as the title.</p>

    <span>Event Detail</span><br />
    <p>Enter a brief description of the event. It should not be more than few sentences long.</p>

    <h4>Deleting Timeline Events</h4>
    <p>To delete one timeline event, use the <a href="#search">Search</a> tab to locate the event, then click on the Delete icon next to that event record. The row will
      change color and then vanish as the event is deleted. To delete more than one event at a time, check the box in the Select column next to each event to be
      deleted, then click the "Delete Selected" button at the top of the page.</p>

</section> <!-- .container -->
</body>
</html>