<?php
require '../../helplib.php';
echo help_header("Help: Repositories");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Repositories</h2>
    <h4>Search</h4>
    <p>Locate existing repositories by searching for all or part of the <strong>Repository ID</strong> or <strong>Repository name</strong>.
      Select a Tree or check "Exact match only" to further narrow your search.
      Searching with no options selected and no value in the search box will find all repositories in your database.</p>

    <p>Your search criteria for this page will be remembered until you click the <strong>Reset</strong> button, which restores all default values and searches again.</p>

    <span>Actions</span>
    <p>The Action buttons next to each search result allow you to edit, delete or preview that result. To delete more than one record at a time, click the box in the
      <strong>Select</strong> column for each record to be deleted, then click the "Delete Selected" button at the top of the list. Use the <strong>Select All</strong> or <strong>Clear All</strong>
      buttons to toggle all select boxes at once.</p>

    <h4>Adding New Repositories</h4>
    <p>A <strong>Repository</strong> is any collection of sources, physical or otherwise.</p>

    <p>To add a new repository, click on the <strong>Add New</strong> tab, then fill out the form. Some information (notes and
      additional events) can be added after saving or locking the record. Take note of the following:</p>

    <span>Tree</span>
    <p>If you have only one Tree, that tree will already be selected. Otherwise, please select the desired tree for the new repository.</p>

    <span>Repository ID</span>
    <p>The Repository ID must be unique within the selected Tree and should consist of an upper case <strong>REPO</strong> or <strong>R</strong> followed by a number (no more than 22 total characters).
      An available, unique ID will be supplied when the page is first displayed and whenever a different tree is selected, but you may enter your own ID if desired.
      To check if the ID you have entered is unique, click the <strong>Check</strong> button. A message will appear to tell you if the ID is in use or not.
      To generate the next sequential unique ID, click <strong>Generate</strong>. This will locate the highest number in your database and add 1.
      To ensure that the displayed ID is not claimed by another user while you're entering the data, click the <strong>Lock</strong> button.</p>

    <p><strong>NOTE</strong>: If you are using this software in conjunction with a PC/Mac-based genealogy program which also creates IDs for new sources,
      it is HIGHLY RECOMMENDED that keep all IDs in sync between the two programs at all times. Failure to do this may result in collisions and may also cause
      your media links to become unusable. If your desktop program creates IDs that do not conform to traditional standards (for example, the
      <strong>R</strong> is at the end, not the beginning), you can edit the prefixes.php file that came with TNG to change the convention TNG uses.</p>

    <span>Name</span>
    <p>An short title for the repository.</p>

    <span>Address 1, Address 2, City, State/Province, Zip/Postal Code, Country</span><br />
    <p>The repository's location (if applicable; all parts are optional).</p>

    <h4>Editing Existing Repositories</h4>
    <p>To make changes to an existing repository, use the <a href="#search">Search</a> tab to locate the repository, then click on the Edit icon next to that repository.</p>

    <span>Notes</span>
    <p>Notes may be linked to events or to the repository in general by clicking on the Notes icon at the top of the page
      or next to each event under "Other Events". When notes already exist for an event, the Notes icon will feature a green dot in the upper right corner.
      For more information on Notes, see the <a href="notes_help.php">Help</a> link visible in the Notes area.</p>

    <span>Other Events</span>
    <p>To add or manage additional events, click on the "Add New" button next to <strong>Other Events</strong>. See the <a href="events_help.php">Help</a> link there for more
      information on adding new events. Once an event has been added, a short summary will be displayed in a table under the "Add New" button. Action buttons for
      each event allow you to edit or delete the event, or add notes. The order in which the events are displayed is determined by date (if applicable),
      and by the event types' assigned priority (when no date is associated). This priority may be changed when editing the event types.

    <p><strong>Note</strong>: Notes and changes to "Other" events are all saved automatically. Other changes (e.g., to
      standard events) can be saved by clicking on the Save button at the bottom of the page, or by clicking on the Save icon at the top of the page. The Tree and
      Repository ID cannot be changed.</p>

    <h4>Deleting Repositories</h4>
    <p>To delete one repository, use the <a href="#search">Search</a> tab to locate the repository, then click on the Delete icon next to that repository. The row will
      change color and then vanish as the repository is deleted.
      To delete more than one repository at a time, check the box in the Select column next to each repository to be
      deleted, then click the "Delete Selected" button	at the top of the page.</p>

    <h4>Merge</h4>
    <p>Click on this tab to review and merge repositories that may be slightly different but refer to the same material.
      You will decide whether multiple records are the same or not.</p>

    <span>Find Matches</span>
    <p>First, select a tree. You cannot merge repositories from different trees, so only one tree can be selected. After that, you have the option of
      selecting a repository as the starting point for your search (Repository ID 1), or letting TNG find the first match for you. If you'd rather let TNG
      find all matches, leave the Repository ID 1 field blank</p>

    <p>If you selected a repository as Repository ID 1, you may also decide to manually select Repository ID 2. If you'd rather let TNG find duplicates
      for Repository ID 1, leave Repository ID 2 blank.</p>

    <span>Other Options</span>
    <p><em>Combine Notes</em> means that notes from Repository 2 will be added to the notes
      from Repository 1 for all merged fields. If this option is not selected and a field from Repository 2 is checked, the notes from Repository 2 for that field will overwrite those
      for the corresponding field from Repository 1.</p>

    <p><em>Combine Media</em> means that media from Repository 2 will be kept and added to the media already existing for
      Repository 1 if the two are merged. If this option is not selected, all media links for Repository 2 will be deleted after the merge.</p>

    <p><strong>Warning!</strong> Once a merge has taken place, it cannot be undone! Please consider backing up your database tables before performing any merge operations, just
      in case you merge two individuals unintentionally.</p>

    <span>Next Match</span>
    <p>Find the next possible match that does not involve Repository 1. TNG traverses the list of possible repositories as ordered by Repository ID in string format.
      This means that "10" comes after "1" but before "2".</p>

    <span>Next Duplicate</span>
    <p>Find the next possible duplicate for Repository 1. If this results in no record being displayed for Repository 2, it means that a duplicate was not found.</p>

    <span>Compare/Refresh</span>
    <p>Compare Repository 1 and Repository 2. If that comparison is already displayed, clicking this button will cause the page to refresh.</p>

    <span>Switch</span>
    <p>Repository 1 becomes Repository 2 and vice versa.</p>

    <span>Merge</span>
    <p>Repository 2 is merged into Repository 1. The ID for Repository 1 will be retained, as will all other data for Repository 1 unless the corresponding box(es) for
      are checked for Repository 2. For example, if the box next to Author is checked for Repository 2, this data in this field will be copied from Repository 2's record to Repository 1's record
      during the merge. Corresponding data for Repository 1 will then be deleted. Boxes for Repository 2 are checked automatically when no corresponding data exists for Repository 1. If
      a data field is not displayed for either Repository 1 or Repository 2, then no data exists in that field for either individual.</p>

    <span>Edit</span>
    <p>Edit the record for that repository in a new window. If changes are made, you must click "Compare/Refresh" in order to see the changes
      in on the Merge screen.</p>

</section> <!-- .container -->
</body>
</html>